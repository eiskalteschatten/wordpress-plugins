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
    // Add nonce for security
    wp_nonce_field('aspp_change_post_type_nonce', 'aspp_change_post_type_nonce_field');
    
    echo '<p><strong>Current post type:</strong> ' . esc_html($post->post_type) . '</p>';
    
    if ($post->post_type === 'planned_post') {
        // Show button to convert to regular post
        echo '<button type="button" onclick="asppConvertPostTypeAjax(event, ' . $post->ID . ', \'post\')" class="button button-primary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Regular Post';
        echo '</button>';
    } 
    elseif ($post->post_type === 'post') {
        // Show button to convert to planned post
        echo '<button type="button" onclick="asppConvertPostTypeAjax(event, ' . $post->ID . ', \'planned_post\')" class="button button-primary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Planned Post';
        echo '</button>';
    } 
    else {
        // For other post types, show both options
        echo '<button type="button" onclick="asppConvertPostTypeAjax(event, ' . $post->ID . ', \'post\')" class="button button-secondary" style="width: 100%; margin-bottom: 5px;">';
        echo 'Convert to Regular Post';
        echo '</button>';
        echo '<button type="button" onclick="asppConvertPostTypeAjax(event, ' . $post->ID . ', \'planned_post\')" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Planned Post';
        echo '</button>';
    }
    
    echo '<p><small><strong>Warning:</strong> Changing the post type may result in loss of custom fields or formatting that are specific to the current post type.</small></p>';
}

add_action('add_meta_boxes_post', 'aspp_add_post_meta_boxes');
