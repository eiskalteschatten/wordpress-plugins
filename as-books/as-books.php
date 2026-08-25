<?php
/**
 * @package Books
 * @version 1.0.0
 */
/*
Plugin Name: Books
Plugin URI: https://www.alexseifert.com
Description: The plugin for book reviews
Author: Alex Seifert
Version: 1.0.0
Author URI: https://www.alexseifert.com
*/

include_once 'meta-boxes.php';

// Status slugs shared by the meta box UI, save logic and frontend/admin rendering.
function asbk_get_statuses() {
    return array(
        'read' => __( 'Read', 'as-books' ),
        'currently_reading' => __( 'Currently Reading', 'as-books' ),
        'want_to_read' => __( 'Want to Read', 'as-books' ),
    );
}

function asbk_plugin_init() {
    register_taxonomy( 'book_genre', 'book', array(
        'labels' => array(
            'name' => __( 'Genres', 'as-books' ),
            'singular_name' => __( 'Genre', 'as-books' ),
        ),
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => array( 'slug' => 'genre' ),
    ) );

    register_taxonomy( 'book_tag', 'book', array(
        'labels' => array(
            'name' => __( 'Book Tags', 'as-books' ),
            'singular_name' => __( 'Book Tag', 'as-books' ),
        ),
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => array( 'slug' => 'book-tag' ),
    ) );

    register_taxonomy( 'book_category', 'book', array(
        'labels' => array(
            'name' => __( 'Book Categories', 'as-books' ),
            'singular_name' => __( 'Book Category', 'as-books' ),
        ),
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'hierarchical' => true,
        'show_in_rest' => true,
        'rewrite' => array( 'slug' => 'book-category' ),
    ) );

    register_post_type( 'book',
        array(
            'labels' => array(
                'name' => __( 'Books', 'as-books' ),
                'singular_name' => __( 'Book', 'as-books' ),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'taxonomies' => array( 'book_genre', 'book_tag', 'book_category' ),
            'menu_icon' => 'dashicons-book-alt',
            'register_meta_box_cb' => 'asbk_add_book_meta_boxes',
            'supports' => array( 'title', 'editor', 'thumbnail', 'comments' ),
        )
    );
}
add_action( 'init', 'asbk_plugin_init' );

// Add books to the main site feed without displacing whatever post types are already queried there.
function asbk_add_to_main_feed( $query ) {
    if ( $query->is_feed() && $query->is_main_query() ) {
        $post_types = $query->get( 'post_type' );
        if ( empty( $post_types ) ) {
            $post_types = array( 'post' );
        } elseif ( ! is_array( $post_types ) ) {
            $post_types = array( $post_types );
        }

        if ( ! in_array( 'book', $post_types, true ) ) {
            $post_types[] = 'book';
            $query->set( 'post_type', $post_types );
        }
    }
    return $query;
}
add_action( 'pre_get_posts', 'asbk_add_to_main_feed' );

function asbk_flush_rewrite_rules() {
    asbk_plugin_init();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'asbk_flush_rewrite_rules' );

function asbk_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'asbk_deactivate' );

function asbk_enqueue_admin_list_styles( $hook ) {
    global $typenow;

    if ( 'edit.php' === $hook && 'book' === $typenow ) {
        wp_enqueue_style( 'asbk-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css', array(), '1.0.0' );
    }
}
add_action( 'admin_enqueue_scripts', 'asbk_enqueue_admin_list_styles' );

// Admin list table columns
function asbk_book_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;
        if ( 'title' === $key ) {
            $new_columns['asbk_cover'] = __( 'Cover', 'as-books' );
        }
    }
    $new_columns['asbk_authors'] = __( 'Author(s)', 'as-books' );
    $new_columns['asbk_rating'] = __( 'Rating', 'as-books' );
    $new_columns['asbk_status'] = __( 'Status', 'as-books' );
    $new_columns['book_genre'] = __( 'Genres', 'as-books' );
    $new_columns['book_category'] = __( 'Categories', 'as-books' );

    // Cover column is more useful right after the checkbox, before the title.
    if ( isset( $new_columns['asbk_cover'] ) ) {
        $cover = $new_columns['asbk_cover'];
        unset( $new_columns['asbk_cover'] );
        $reordered = array();
        foreach ( $new_columns as $key => $label ) {
            if ( 'title' === $key ) {
                $reordered['asbk_cover'] = $cover;
            }
            $reordered[ $key ] = $label;
        }
        $new_columns = $reordered;
    }

    return $new_columns;
}
add_filter( 'manage_book_posts_columns', 'asbk_book_columns' );

function asbk_book_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'asbk_cover':
            echo get_the_post_thumbnail( $post_id, array( 40, 60 ) );
            break;
        case 'asbk_authors':
            echo esc_html( get_post_meta( $post_id, 'asbk_authors', true ) );
            break;
        case 'asbk_rating':
            echo wp_kses_post( asbk_format_rating_stars( get_post_meta( $post_id, 'asbk_rating', true ) ) );
            break;
        case 'asbk_status':
            $statuses = asbk_get_statuses();
            $status = get_post_meta( $post_id, 'asbk_status', true );
            echo esc_html( isset( $statuses[ $status ] ) ? $statuses[ $status ] : '' );
            break;
    }
}
add_action( 'manage_book_posts_custom_column', 'asbk_book_column_content', 10, 2 );

function asbk_format_rating_stars( $rating ) {
    $full_stars = (int) min( 5, max( 0, (int) $rating ) );
    $empty_stars = 5 - $full_stars;

    $stars = str_repeat( '<span class="asbk-star asbk-star--full">&#9733;</span>', $full_stars );
    $stars .= str_repeat( '<span class="asbk-star asbk-star--empty">&#9734;</span>', $empty_stars );

    return '<span class="asbk-stars">' . $stars . '</span>';
}

// Status filter dropdown on the book admin list
function asbk_status_filter_dropdown() {
    global $typenow;

    if ( 'book' !== $typenow ) {
        return;
    }

    $statuses = asbk_get_statuses();
    $selected = isset( $_GET['asbk_status'] ) ? sanitize_text_field( wp_unslash( $_GET['asbk_status'] ) ) : '';
    ?>
    <select name="asbk_status">
        <option value=""><?php esc_html_e( 'All statuses', 'as-books' ); ?></option>
        <?php foreach ( $statuses as $slug => $label ) : ?>
            <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $selected, $slug ); ?>><?php echo esc_html( $label ); ?></option>
        <?php endforeach; ?>
    </select>
    <?php
}
add_action( 'restrict_manage_posts', 'asbk_status_filter_dropdown' );

function asbk_filter_books_by_status( $query ) {
    global $pagenow, $typenow;

    if ( is_admin() && 'edit.php' === $pagenow && 'book' === $typenow
        && ! empty( $_GET['asbk_status'] ) ) {
        $query->query_vars['meta_key'] = 'asbk_status';
        $query->query_vars['meta_value'] = sanitize_text_field( wp_unslash( $_GET['asbk_status'] ) );
    }
}
add_action( 'parse_query', 'asbk_filter_books_by_status' );

// Frontend display
function asbk_render_book_meta( $post_id ) {
    $statuses = asbk_get_statuses();
    $authors = get_post_meta( $post_id, 'asbk_authors', true );
    $description = get_post_meta( $post_id, 'asbk_description', true );
    $rating = get_post_meta( $post_id, 'asbk_rating', true );
    $status = get_post_meta( $post_id, 'asbk_status', true );
    $finished_on = get_post_meta( $post_id, 'asbk_finished_on', true );
    $page_count = get_post_meta( $post_id, 'asbk_page_count', true );
    $original_language = get_post_meta( $post_id, 'asbk_original_language', true );
    $language_read_in = get_post_meta( $post_id, 'asbk_language_read_in', true );
    $publisher = get_post_meta( $post_id, 'asbk_publisher', true );
    $isbn = get_post_meta( $post_id, 'asbk_isbn', true );

    ob_start();
    ?>
    <div class="asbk-book-meta">
        <?php if ( $authors ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Author(s):', 'as-books' ); ?></strong> <?php echo esc_html( $authors ); ?></p>
        <?php endif; ?>
        <?php if ( '' !== $rating ) : ?>
            <p class="asbk-book-meta__row asbk-book-meta__rating"><strong><?php esc_html_e( 'Rating:', 'as-books' ); ?></strong> <?php echo wp_kses_post( asbk_format_rating_stars( $rating ) ); ?></p>
        <?php endif; ?>
        <?php if ( $status && isset( $statuses[ $status ] ) ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Status:', 'as-books' ); ?></strong> <?php echo esc_html( $statuses[ $status ] ); ?></p>
        <?php endif; ?>
        <?php if ( $finished_on ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Finished On:', 'as-books' ); ?></strong> <?php echo esc_html( mysql2date( get_option( 'date_format' ), $finished_on ) ); ?></p>
        <?php endif; ?>
        <?php if ( $publisher ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Publisher:', 'as-books' ); ?></strong> <?php echo esc_html( $publisher ); ?></p>
        <?php endif; ?>
        <?php if ( $isbn ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'ISBN:', 'as-books' ); ?></strong> <?php echo esc_html( $isbn ); ?></p>
        <?php endif; ?>
        <?php if ( $original_language ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Original Language:', 'as-books' ); ?></strong> <?php echo esc_html( $original_language ); ?></p>
        <?php endif; ?>
        <?php if ( $language_read_in ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Language Read In:', 'as-books' ); ?></strong> <?php echo esc_html( $language_read_in ); ?></p>
        <?php endif; ?>
        <?php if ( $page_count ) : ?>
            <p class="asbk-book-meta__row"><strong><?php esc_html_e( 'Page Count:', 'as-books' ); ?></strong> <?php echo esc_html( $page_count ); ?></p>
        <?php endif; ?>
        <?php foreach ( array( 'book_genre' => __( 'Genres:', 'as-books' ), 'book_category' => __( 'Categories:', 'as-books' ), 'book_tag' => __( 'Tags:', 'as-books' ) ) as $taxonomy => $label ) : ?>
            <?php $term_links = get_the_term_list( $post_id, $taxonomy, '', ', ' ); ?>
            <?php if ( $term_links && ! is_wp_error( $term_links ) ) : ?>
                <p class="asbk-book-meta__row"><strong><?php echo esc_html( $label ); ?></strong> <?php echo wp_kses_post( $term_links ); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ( $description ) : ?>
            <p class="asbk-book-meta__description"><?php echo nl2br( esc_html( $description ) ); ?></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function asbk_prepend_book_meta( $content ) {
    if ( is_singular( 'book' ) && in_the_loop() && is_main_query() ) {
        $content = asbk_render_book_meta( get_the_ID() ) . $content;
    }

    return $content;
}
add_filter( 'the_content', 'asbk_prepend_book_meta' );

function asbk_enqueue_frontend_styles() {
    if ( is_singular( 'book' ) ) {
        wp_enqueue_style( 'asbk-frontend', plugin_dir_url( __FILE__ ) . 'assets/css/frontend.css', array(), '1.0.0' );
    }
}
add_action( 'wp_enqueue_scripts', 'asbk_enqueue_frontend_styles' );
