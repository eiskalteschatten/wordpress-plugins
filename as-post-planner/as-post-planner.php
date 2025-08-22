<?php
/**
 * @package Post Planner
 * @version 1.0.0
 */
/*
Plugin Name: Post Planner
Plugin URI: https://www.alexseifert.com
Description: The plugin for planning posts
Author: Alex Seifert
Version: 1.0.0
Author URI: https://www.alexseifert.com
*/

include_once 'meta-boxes.php';

function aspp_plugin_init() {
    register_taxonomy( 'planned_post_category', 'planned_post', array(
        'labels' => array(
            'name' => __( 'Planned Post Categories', 'textdomain' ),
            'singular_name' => __( 'Planned Post Category', 'textdomain' ),
        ),
        'public' => true,
        'hierarchical' => true,
        'show_in_rest' => false,
        'rewrite' => array( 'slug' => 'planned-post-category' ),
    ) );

    register_taxonomy( 'planned_post_tag', 'planned_post', array(
        'labels' => array(
            'name' => __( 'Planned Post Tags', 'textdomain' ),
            'singular_name' => __( 'Planned Post Tag', 'textdomain' ),
        ),
        'public' => true,
        'hierarchical' => false,
        'show_in_rest' => false,
        'rewrite' => array( 'slug' => 'planned-post-tag' ),
    ) );

	register_post_type('planned_post',
		array(
			'labels' => array(
				'name' => __('Planned Posts', 'textdomain'),
				'singular_name' => __('Planned Post', 'textdomain'),
			),
            'public' => false,
            'has_archive' => false,
            'show_in_rest' => false,
            'taxonomies' => array( 'planned_post_category', 'planned_post_tag' ),
            'menu_icon' => 'dashicons-index-card',
            'register_meta_box_cb' => 'aspp_add_post_meta_boxes',
            'supports' => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes', 'custom-fields' )
		)
	);
}
add_action( 'init', 'aspp_plugin_init' );

function aspp_save_post_meta($post_id) {
    // Handle post type change
    if ( array_key_exists( 'aspp_change_post_type_nonce_field', $_POST ) &&
         wp_verify_nonce( $_POST['aspp_change_post_type_nonce_field'], 'aspp_change_post_type_nonce' ) &&
         array_key_exists( 'aspp_new_post_type', $_POST ) &&
         !empty( $_POST['aspp_new_post_type'] ) ) {
        
        $new_post_type = sanitize_text_field( $_POST['aspp_new_post_type'] );
        $valid_post_types = get_post_types( array( 'public' => true ) );
        
        if ( in_array( $new_post_type, $valid_post_types ) ) {
            // Remove the save_post hook temporarily to prevent infinite loop
            remove_action( 'save_post', 'aspp_save_post_meta' );
            
            // Update the post type
            wp_update_post( array(
                'ID' => $post_id,
                'post_type' => $new_post_type
            ) );
            
            // Re-add the save_post hook
            add_action( 'save_post', 'aspp_save_post_meta' );
            
            // Redirect to the edit screen of the new post type
            $redirect_url = admin_url( 'post.php?post=' . $post_id . '&action=edit&post_type_changed=1' );
            wp_redirect( $redirect_url );
            exit;
        }
    }
}
add_action('save_post', 'aspp_save_post_meta');

function aspp_flush_rewrite_rules() {
    aspp_plugin_init();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'aspp_flush_rewrite_rules');

// Add custom taxonomy filter to admin post list
function aspp_add_planned_post_category_filter() {
    global $typenow;

    if ($typenow == 'planned_post') {
        $taxonomy = 'planned_post_category';
        $selected = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';

        $info_taxonomy = get_taxonomy($taxonomy);
        wp_dropdown_categories(array(
            'show_option_all' => __("All {$info_taxonomy->label}"),
            'taxonomy' => $taxonomy,
            'name' => $taxonomy,
            'orderby' => 'name',
            'selected' => $selected,
            'show_count' => true,
            'hide_empty' => true,
            'value_field' => 'slug',
        ));
    }
}
add_action('restrict_manage_posts', 'aspp_add_planned_post_category_filter');

// Filter the posts based on the selected category
function aspp_filter_stories_by_category($query) {
    global $pagenow;

    if (is_admin() && $pagenow == 'edit.php' && isset($_GET['post_type']) && $_GET['post_type'] == 'planned_post') {
        if (isset($_GET['planned_post_category']) && $_GET['planned_post_category'] != '') {
            $query->query_vars['tax_query'] = array(
                array(
                    'taxonomy' => 'planned_post_category',
                    'field' => 'slug',
                    'terms' => $_GET['planned_post_category']
                )
            );
        }
    }
}
add_filter('parse_query', 'aspp_filter_stories_by_category');

// Show admin notice when post type is changed
function aspp_post_type_change_notice() {
    if ( isset( $_GET['post_type_changed'] ) && $_GET['post_type_changed'] == '1' ) {
        echo '<div class="notice notice-success is-dismissible">';
        echo '<p><strong>Post type changed successfully!</strong> The post has been converted to the new post type.</p>';
        echo '</div>';
    }
}
add_action( 'admin_notices', 'aspp_post_type_change_notice' );
