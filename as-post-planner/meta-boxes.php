<?php

// Add meta boxes for regular posts
function aspp_add_planned_post_meta_boxes() {
    add_meta_box(
        'aspp_change_post_type',
        'Post Type',
        'aspp_change_post_type_callback',
        'planned_post',
        'side',
        'default'
    );
}

function aspp_add_post_meta_boxes() {
    add_meta_box(
        'aspp_change_post_type',
        'Post Type',
        'aspp_change_post_type_callback',
        'post',
        'side',
        'default'
    );
}

function aspp_change_post_type_callback($post) {
    // Get all public post types
    $post_types = get_post_types(array(), 'objects');
    
    // Add nonce for security
    wp_nonce_field('aspp_change_post_type_nonce', 'aspp_change_post_type_nonce_field');
    
    echo '<label for="aspp_new_post_type">Change to:</label><br>';
    echo '<select id="aspp_new_post_type" name="aspp_new_post_type" style="margin: 5px 0; display: block;">';
    echo '<option value="">-- Select new post type --</option>';
    
    foreach ($post_types as $post_type) {
        $selected = '';

        if ($post_type->name === $post->post_type) {
            $selected = 'selected';
        }
        
        echo '<option value="' . esc_attr($post_type->name) . '" ' . $selected . '>' . esc_html($post_type->label) . '</option>';
    }
    
    echo '</select>';
    echo '<p><small><strong>Warning:</strong> Changing the post type may result in loss of custom fields or formatting that are specific to the current post type.</small></p>';
}

add_action('add_meta_boxes_post', 'aspp_add_post_meta_boxes');
