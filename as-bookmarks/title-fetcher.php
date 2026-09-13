<?php

function asbm_fetch_page_title( $url ) {
    $response = wp_remote_get( $url, array( 'timeout' => 10, 'redirection' => 5 ) );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return null;
    }

    if ( ! preg_match( '/<title[^>]*>(.*?)<\/title>/is', wp_remote_retrieve_body( $response ), $matches ) ) {
        return null;
    }

    $title = trim( html_entity_decode( wp_strip_all_tags( $matches[1] ), ENT_QUOTES, 'UTF-8' ) );
    $title = preg_replace( '/\s+/', ' ', $title );

    return $title ? $title : null;
}

function asbm_enqueue_title_fetch_script( $hook ) {
    global $typenow;

    if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'bookmark' !== $typenow ) {
        return;
    }

    wp_enqueue_script( 'asbm-title-fetch', plugin_dir_url( __FILE__ ) . 'assets/js/admin-title-fetch.js', array(), filemtime( __DIR__ . '/assets/js/admin-title-fetch.js' ), true );

    wp_localize_script( 'asbm-title-fetch', 'asbmFetch', array(
        'nonce' => wp_create_nonce( 'asbm_fetch_title_nonce' ),
        // %d is swapped for the post ID client-side; reloading post-new.php directly would just start a fresh auto-draft.
        'editUrl' => admin_url( 'post.php?post=%d&action=edit' ),
        'i18n' => array(
            'emptyUrl' => __( 'Please enter a URL first.', 'as-bookmarks' ),
            'fetching' => __( 'Fetching…', 'as-bookmarks' ),
            'genericError' => __( 'Something went wrong. Please try again.', 'as-bookmarks' ),
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'asbm_enqueue_title_fetch_script' );

function asbm_ajax_fetch_title() {
    check_ajax_referer( 'asbm_fetch_title_nonce', 'nonce' );

    $post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : 0;
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( array( 'message' => __( 'You are not allowed to edit this bookmark.', 'as-bookmarks' ) ) );
    }

    $url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : '';
    if ( ! $url ) {
        wp_send_json_error( array( 'message' => __( 'Please enter a URL first.', 'as-bookmarks' ) ) );
    }

    $title = asbm_fetch_page_title( $url );
    if ( ! $title ) {
        wp_send_json_error( array( 'message' => __( 'Could not find a title for this URL.', 'as-bookmarks' ) ) );
    }

    update_post_meta( $post_id, 'asbm_url', $url );

    wp_update_post( array(
        'ID' => $post_id,
        'post_title' => sanitize_text_field( $title ),
    ) );

    wp_send_json_success();
}
add_action( 'wp_ajax_asbm_fetch_title', 'asbm_ajax_fetch_title' );
