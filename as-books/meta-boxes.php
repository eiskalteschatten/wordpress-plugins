<?php

function asbk_add_book_meta_boxes() {
    add_meta_box(
        'asbk_book_details',
        __( 'Book Details', 'as-books' ),
        'asbk_book_details_callback',
        'book',
        'side',
        'default'
    );
}

function asbk_book_details_callback( $post ) {
    wp_nonce_field( 'asbk_book_meta_nonce', 'asbk_book_meta_nonce_field' );

    $authors = get_post_meta( $post->ID, 'asbk_authors', true );
    $description = get_post_meta( $post->ID, 'asbk_description', true );
    $rating = get_post_meta( $post->ID, 'asbk_rating', true );
    $status = get_post_meta( $post->ID, 'asbk_status', true );
    $finished_on = get_post_meta( $post->ID, 'asbk_finished_on', true );
    $page_count = get_post_meta( $post->ID, 'asbk_page_count', true );
    $original_language = get_post_meta( $post->ID, 'asbk_original_language', true );
    $language_read_in = get_post_meta( $post->ID, 'asbk_language_read_in', true );
    $publisher = get_post_meta( $post->ID, 'asbk_publisher', true );
    $isbn = get_post_meta( $post->ID, 'asbk_isbn', true );
    $statuses = asbk_get_statuses();
    ?>
    <p>
        <label for="asbk_authors"><strong><?php esc_html_e( 'Author(s)', 'as-books' ); ?></strong></label><br>
        <input type="text" id="asbk_authors" name="asbk_authors" class="widefat" value="<?php echo esc_attr( $authors ); ?>">
    </p>
    <p>
        <label for="asbk_description"><strong><?php esc_html_e( 'Back-of-the-Book Description', 'as-books' ); ?></strong></label><br>
        <textarea id="asbk_description" name="asbk_description" class="widefat" rows="5"><?php echo esc_textarea( $description ); ?></textarea>
    </p>
    <p>
        <label for="asbk_rating"><strong><?php esc_html_e( 'Rating', 'as-books' ); ?></strong></label><br>
        <select id="asbk_rating" name="asbk_rating">
            <?php for ( $value = 0; $value <= 5; $value++ ) : ?>
                <option value="<?php echo esc_attr( $value ); ?>" <?php selected( (int) $rating, $value ); ?>><?php echo esc_html( $value ); ?></option>
            <?php endfor; ?>
        </select>
    </p>
    <p>
        <label for="asbk_status"><strong><?php esc_html_e( 'Status', 'as-books' ); ?></strong></label><br>
        <select id="asbk_status" name="asbk_status">
            <?php foreach ( $statuses as $slug => $label ) : ?>
                <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $status, $slug ); ?>><?php echo esc_html( $label ); ?></option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="asbk_finished_on"><strong><?php esc_html_e( 'Finished On', 'as-books' ); ?></strong></label><br>
        <input type="date" id="asbk_finished_on" name="asbk_finished_on" value="<?php echo esc_attr( $finished_on ); ?>">
    </p>
    <p>
        <label for="asbk_page_count"><strong><?php esc_html_e( 'Page Count', 'as-books' ); ?></strong></label><br>
        <input type="number" id="asbk_page_count" name="asbk_page_count" class="widefat" min="0" step="1" value="<?php echo esc_attr( $page_count ); ?>">
    </p>
    <p>
        <label for="asbk_original_language"><strong><?php esc_html_e( 'Original Language', 'as-books' ); ?></strong></label><br>
        <input type="text" id="asbk_original_language" name="asbk_original_language" class="widefat" value="<?php echo esc_attr( $original_language ); ?>">
    </p>
    <p>
        <label for="asbk_language_read_in"><strong><?php esc_html_e( 'Language Read In', 'as-books' ); ?></strong></label><br>
        <input type="text" id="asbk_language_read_in" name="asbk_language_read_in" class="widefat" value="<?php echo esc_attr( $language_read_in ); ?>">
    </p>
    <p>
        <label for="asbk_publisher"><strong><?php esc_html_e( 'Publisher', 'as-books' ); ?></strong></label><br>
        <input type="text" id="asbk_publisher" name="asbk_publisher" class="widefat" value="<?php echo esc_attr( $publisher ); ?>">
    </p>
    <p>
        <label for="asbk_isbn"><strong><?php esc_html_e( 'ISBN', 'as-books' ); ?></strong></label><br>
        <input type="text" id="asbk_isbn" name="asbk_isbn" class="widefat" value="<?php echo esc_attr( $isbn ); ?>">
    </p>
    <p>
        <button type="button" id="asbk_fetch_book_data" class="button" data-post-id="<?php echo esc_attr( $post->ID ); ?>"><?php esc_html_e( 'Fetch Data', 'as-books' ); ?></button>
        <span id="asbk_fetch_status" class="asbk-fetch-status" aria-live="polite"></span>
    </p>
    <?php
}

function asbk_save_book_meta( $post_id ) {
    if ( ! isset( $_POST['asbk_book_meta_nonce_field'] )
        || ! wp_verify_nonce( $_POST['asbk_book_meta_nonce_field'], 'asbk_book_meta_nonce' ) ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['asbk_authors'] ) ) {
        update_post_meta( $post_id, 'asbk_authors', sanitize_text_field( wp_unslash( $_POST['asbk_authors'] ) ) );
    }

    if ( isset( $_POST['asbk_description'] ) ) {
        update_post_meta( $post_id, 'asbk_description', sanitize_textarea_field( wp_unslash( $_POST['asbk_description'] ) ) );
    }

    if ( isset( $_POST['asbk_rating'] ) ) {
        $rating = min( 5, max( 0, (int) $_POST['asbk_rating'] ) );
        update_post_meta( $post_id, 'asbk_rating', $rating );
    }

    if ( isset( $_POST['asbk_status'] ) ) {
        $valid_statuses = array_keys( asbk_get_statuses() );
        $status = sanitize_text_field( wp_unslash( $_POST['asbk_status'] ) );
        if ( ! in_array( $status, $valid_statuses, true ) ) {
            $status = $valid_statuses[0];
        }
        update_post_meta( $post_id, 'asbk_status', $status );
    }

    if ( isset( $_POST['asbk_finished_on'] ) ) {
        $finished_on = sanitize_text_field( wp_unslash( $_POST['asbk_finished_on'] ) );
        $is_valid_date = $finished_on && DateTime::createFromFormat( 'Y-m-d', $finished_on ) !== false;
        update_post_meta( $post_id, 'asbk_finished_on', $is_valid_date ? $finished_on : '' );
    }

    if ( isset( $_POST['asbk_page_count'] ) ) {
        $page_count = max( 0, (int) $_POST['asbk_page_count'] );
        update_post_meta( $post_id, 'asbk_page_count', $page_count );
    }

    if ( isset( $_POST['asbk_original_language'] ) ) {
        update_post_meta( $post_id, 'asbk_original_language', sanitize_text_field( wp_unslash( $_POST['asbk_original_language'] ) ) );
    }

    if ( isset( $_POST['asbk_language_read_in'] ) ) {
        update_post_meta( $post_id, 'asbk_language_read_in', sanitize_text_field( wp_unslash( $_POST['asbk_language_read_in'] ) ) );
    }

    if ( isset( $_POST['asbk_publisher'] ) ) {
        update_post_meta( $post_id, 'asbk_publisher', sanitize_text_field( wp_unslash( $_POST['asbk_publisher'] ) ) );
    }

    if ( isset( $_POST['asbk_isbn'] ) ) {
        $isbn = sanitize_text_field( wp_unslash( $_POST['asbk_isbn'] ) );
        $isbn = preg_replace( '/[^0-9Xx-]/', '', $isbn );
        update_post_meta( $post_id, 'asbk_isbn', $isbn );
    }
}
add_action( 'save_post_book', 'asbk_save_book_meta' );
