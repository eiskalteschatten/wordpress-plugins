<?php

// Empty result shape shared by both providers so the merge step can rely on consistent keys.
function asbk_empty_book_data() {
    return array(
        'title' => null,
        'authors' => null,
        'description' => null,
        'page_count' => null,
        'publisher' => null,
        'original_language' => null,
        'cover_url' => null,
    );
}

function asbk_extract_openlibrary_description( $data ) {
    foreach ( array( 'description', 'notes' ) as $key ) {
        if ( ! empty( $data[ $key ] ) ) {
            return is_array( $data[ $key ] ) ? ( $data[ $key ]['value'] ?? null ) : $data[ $key ];
        }
    }

    if ( ! empty( $data['excerpts'][0]['text'] ) ) {
        return $data['excerpts'][0]['text'];
    }

    return null;
}

function asbk_fetch_openlibrary_data( $isbn ) {
    $result = asbk_empty_book_data();

    $url = 'https://openlibrary.org/api/books?bibkeys=ISBN:' . rawurlencode( $isbn ) . '&format=json&jscmd=data';
    $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $result;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $data = $body[ 'ISBN:' . $isbn ] ?? null;

    if ( empty( $data ) ) {
        return $result;
    }

    $result['title'] = $data['title'] ?? null;

    if ( ! empty( $data['authors'] ) ) {
        $result['authors'] = implode( ', ', wp_list_pluck( $data['authors'], 'name' ) );
    }

    $result['description'] = asbk_extract_openlibrary_description( $data );
    $result['page_count'] = $data['number_of_pages'] ?? null;
    $result['publisher'] = $data['publishers'][0]['name'] ?? null;
    $result['cover_url'] = $data['cover']['large'] ?? $data['cover']['medium'] ?? $data['cover']['small'] ?? null;

    return $result;
}

function asbk_fetch_googlebooks_data( $isbn ) {
    $result = asbk_empty_book_data();

    $url = 'https://www.googleapis.com/books/v1/volumes?q=isbn:' . rawurlencode( $isbn );
    $response = wp_remote_get( $url, array( 'timeout' => 10 ) );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return $result;
    }

    $body = json_decode( wp_remote_retrieve_body( $response ), true );
    $info = $body['items'][0]['volumeInfo'] ?? null;

    if ( empty( $info ) ) {
        return $result;
    }

    $result['title'] = $info['title'] ?? null;

    if ( ! empty( $info['authors'] ) ) {
        $result['authors'] = implode( ', ', $info['authors'] );
    }

    $result['description'] = $info['description'] ?? null;
    $result['page_count'] = $info['pageCount'] ?? null;
    $result['publisher'] = $info['publisher'] ?? null;
    $result['original_language'] = $info['language'] ?? null;

    foreach ( array( 'extraLarge', 'large', 'medium', 'small', 'thumbnail', 'smallThumbnail' ) as $size ) {
        if ( ! empty( $info['imageLinks'][ $size ] ) ) {
            $result['cover_url'] = str_replace( 'http://', 'https://', $info['imageLinks'][ $size ] );
            break;
        }
    }

    return $result;
}

// Open Library wins per field; Google Books only fills in whatever Open Library didn't provide.
function asbk_merge_book_data( $ol_data, $gb_data ) {
    $merged = array();

    foreach ( asbk_empty_book_data() as $field => $value ) {
        $merged[ $field ] = ! empty( $ol_data[ $field ] ) ? $ol_data[ $field ] : ( $gb_data[ $field ] ?? null );
    }

    return $merged;
}

function asbk_attach_book_cover( $post_id, $cover_url, $title, $authors ) {
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $temp_file = download_url( $cover_url, 15 );
    if ( is_wp_error( $temp_file ) ) {
        return $temp_file;
    }

    $file_array = array(
        'name' => sanitize_file_name( ( $title ? $title : 'book-cover' ) . '-cover.jpg' ),
        'tmp_name' => $temp_file,
    );

    $attachment_id = media_handle_sideload( $file_array, $post_id );
    if ( is_wp_error( $attachment_id ) ) {
        if ( file_exists( $temp_file ) ) {
            wp_delete_file( $temp_file );
        }
        return $attachment_id;
    }

    $alt_text = sanitize_text_field( $authors ? sprintf( '%s by %s', $title, $authors ) : $title );

    wp_update_post( array(
        'ID' => $attachment_id,
        'post_title' => $alt_text,
        'post_excerpt' => $alt_text,
        'post_content' => $alt_text,
    ) );
    update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
    set_post_thumbnail( $post_id, $attachment_id );

    return $attachment_id;
}

function asbk_enqueue_book_fetch_script( $hook ) {
    global $typenow;

    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'book' !== $typenow ) {
        return;
    }

    wp_enqueue_style( 'asbk-admin', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', array(), '1.0.0' );
    wp_enqueue_script( 'asbk-book-fetch', plugin_dir_url( __FILE__ ) . 'assets/js/admin-book-fetch.js', array(), '1.0.0', true );

    wp_localize_script( 'asbk-book-fetch', 'asbkFetch', array(
        'nonce' => wp_create_nonce( 'asbk_fetch_book_nonce' ),
        'i18n' => array(
            'emptyIsbn' => __( 'Please enter an ISBN first.', 'as-books' ),
            'fetching' => __( 'Fetching…', 'as-books' ),
            'genericError' => __( 'Something went wrong. Please try again.', 'as-books' ),
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'asbk_enqueue_book_fetch_script' );

function asbk_ajax_fetch_book_data() {
    check_ajax_referer( 'asbk_fetch_book_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this book.', 'as-books' ) ) );
    }

    $isbn = isset( $_POST['isbn'] ) ? sanitize_text_field( wp_unslash( $_POST['isbn'] ) ) : '';
    $isbn = preg_replace( '/[^0-9Xx-]/', '', $isbn );
    if ( ! $isbn ) {
        wp_send_json_error( array( 'message' => __( 'Please enter an ISBN first.', 'as-books' ) ) );
    }

    $merged = asbk_merge_book_data( asbk_fetch_openlibrary_data( $isbn ), asbk_fetch_googlebooks_data( $isbn ) );

    if ( ! array_filter( $merged ) ) {
        wp_send_json_error( array( 'message' => __( 'No book data found for this ISBN.', 'as-books' ) ) );
    }

    if ( ! empty( $merged['title'] ) ) {
        wp_update_post( array(
            'ID' => $post_id,
            'post_title' => sanitize_text_field( $merged['title'] ),
        ) );
    }

    update_post_meta( $post_id, 'asbk_isbn', $isbn );

    if ( ! empty( $merged['authors'] ) ) {
        update_post_meta( $post_id, 'asbk_authors', sanitize_text_field( $merged['authors'] ) );
    }

    if ( ! empty( $merged['description'] ) ) {
        update_post_meta( $post_id, 'asbk_description', sanitize_textarea_field( $merged['description'] ) );
    }

    if ( ! empty( $merged['page_count'] ) ) {
        update_post_meta( $post_id, 'asbk_page_count', absint( $merged['page_count'] ) );
    }

    if ( ! empty( $merged['publisher'] ) ) {
        update_post_meta( $post_id, 'asbk_publisher', sanitize_text_field( $merged['publisher'] ) );
    }

    if ( ! empty( $merged['original_language'] ) ) {
        update_post_meta( $post_id, 'asbk_original_language', sanitize_text_field( $merged['original_language'] ) );
    }

    $warning = '';
    if ( ! empty( $merged['cover_url'] ) && ! has_post_thumbnail( $post_id ) ) {
        $attached = asbk_attach_book_cover( $post_id, $merged['cover_url'], $merged['title'], $merged['authors'] );
        if ( is_wp_error( $attached ) ) {
            $warning = $attached->get_error_message();
        }
    }

    wp_send_json_success( array( 'warning' => $warning ) );
}
add_action( 'wp_ajax_asbk_fetch_book_data', 'asbk_ajax_fetch_book_data' );
