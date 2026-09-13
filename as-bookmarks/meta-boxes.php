<?php

function asbm_add_bookmark_meta_boxes() {
    add_meta_box(
        'asbm_bookmark_details',
        __( 'Bookmark Details', 'as-bookmarks' ),
        'asbm_bookmark_details_callback',
        'bookmark',
        'side',
        'default'
    );
}

function asbm_bookmark_details_callback( $post ) {
    wp_nonce_field( 'asbm_bookmark_meta_nonce', 'asbm_bookmark_meta_nonce_field' );

    $url = get_post_meta( $post->ID, 'asbm_url', true );
    ?>
    <p>
        <label for="asbm_url"><strong><?php esc_html_e( 'URL', 'as-bookmarks' ); ?></strong></label><br>
        <input type="url" id="asbm_url" name="asbm_url" class="widefat" placeholder="https://" value="<?php echo esc_attr( $url ); ?>">
    </p>
    <?php
}

function asbm_save_bookmark_meta( $post_id ) {
    if ( ! isset( $_POST['asbm_bookmark_meta_nonce_field'] )
        || ! wp_verify_nonce( $_POST['asbm_bookmark_meta_nonce_field'], 'asbm_bookmark_meta_nonce' ) ) {
        return;
    }

    if ( wp_is_post_autosave( $post_id ) || wp_is_post_revision( $post_id ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['asbm_url'] ) ) {
        update_post_meta( $post_id, 'asbm_url', esc_url_raw( wp_unslash( $_POST['asbm_url'] ) ) );
    }
}
add_action( 'save_post_bookmark', 'asbm_save_bookmark_meta' );
