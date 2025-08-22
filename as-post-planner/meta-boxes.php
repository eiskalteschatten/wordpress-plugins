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
        echo '<button type="button" onclick="asppConvertPostTypeAjax(' . $post->ID . ', \'post\')" class="button button-primary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Regular Post';
        echo '</button>';
    } elseif ($post->post_type === 'post') {
        // Show button to convert to planned post
        echo '<button type="button" onclick="asppConvertPostTypeAjax(' . $post->ID . ', \'planned_post\')" class="button button-primary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Planned Post';
        echo '</button>';
    } else {
        // For other post types, show both options
        echo '<button type="button" onclick="asppConvertPostTypeAjax(' . $post->ID . ', \'post\')" class="button button-secondary" style="width: 100%; margin-bottom: 5px;">';
        echo 'Convert to Regular Post';
        echo '</button>';
        echo '<button type="button" onclick="asppConvertPostTypeAjax(' . $post->ID . ', \'planned_post\')" class="button button-secondary" style="width: 100%; margin-bottom: 10px;">';
        echo 'Convert to Planned Post';
        echo '</button>';
    }
    
    echo '<p><small><strong>Warning:</strong> Changing the post type may result in loss of custom fields or formatting that are specific to the current post type.</small></p>';
    
    // Add JavaScript for AJAX submission
    echo '<script>
    function asppConvertPostTypeAjax(postId, newPostType) {
        if (confirm("Are you sure you want to convert this post type? This action cannot be undone.")) {
            // Show loading state
            event.target.disabled = true;
            var originalText = event.target.textContent;
            event.target.textContent = "Converting...";
            
            // Make AJAX request
            jQuery.ajax({
                url: ajaxurl,
                type: "POST",
                data: {
                    action: "aspp_convert_post_type",
                    post_id: postId,
                    new_post_type: newPostType,
                    nonce: "' . wp_create_nonce('aspp_convert_post_type') . '"
                },
                success: function(response) {
                    window.location.reload();
                },
                error: function() {
                    alert("Error: Could not convert post type. Please try again.");
                    // Reset button
                    event.target.disabled = false;
                    event.target.textContent = originalText;
                }
            });
        }
    }
    </script>';
}

add_action('add_meta_boxes_post', 'aspp_add_post_meta_boxes');
