<?php
/**
 * @package Bookmarks
 * @version 1.0.0
 */
/*
Plugin Name: Bookmarks
Plugin URI: https://www.alexseifert.com
Description: The plugin for bookmarks and links
Author: Alex Seifert
Version: 1.0.0
Author URI: https://www.alexseifert.com
*/

include_once 'meta-boxes.php';
include_once 'title-fetcher.php';

function asbm_plugin_init() {
    register_taxonomy( 'bookmark_tag', 'bookmark', array(
        'labels' => array(
            'name' => __( 'Bookmark Tags', 'as-bookmarks' ),
            'singular_name' => __( 'Bookmark Tag', 'as-bookmarks' ),
        ),
        'public' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'hierarchical' => false,
        'show_in_rest' => true,
        'rewrite' => array( 'slug' => 'bookmark-tag' ),
    ) );

    register_post_type( 'bookmark',
        array(
            'labels' => array(
                'name' => __( 'Bookmarks', 'as-bookmarks' ),
                'singular_name' => __( 'Bookmark', 'as-bookmarks' ),
                'add_new_item' => __( 'Add New Bookmark', 'as-bookmarks' ),
                'edit_item' => __( 'Edit Bookmark', 'as-bookmarks' ),
            ),
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'has_archive' => true,
            'show_in_rest' => true,
            'taxonomies' => array( 'bookmark_tag' ),
            'menu_icon' => 'dashicons-admin-links',
            'register_meta_box_cb' => 'asbm_add_bookmark_meta_boxes',
            'supports' => array( 'title', 'editor', 'comments' ),
        )
    );
}
add_action( 'init', 'asbm_plugin_init' );

// A dedicated feed at /feed/bookmarks/ (or ?feed=bookmarks) containing only bookmarks.
function asbm_register_bookmarks_feed() {
    add_feed( 'bookmarks', 'asbm_render_bookmarks_feed' );
}
add_action( 'init', 'asbm_register_bookmarks_feed' );

function asbm_render_bookmarks_feed() {
    load_template( ABSPATH . WPINC . '/feed-rss2.php' );
}

function asbm_bookmarks_feed_query( $query ) {
    if ( $query->is_feed( 'bookmarks' ) && $query->is_main_query() ) {
        $query->set( 'post_type', 'bookmark' );
    }
}
add_action( 'pre_get_posts', 'asbm_bookmarks_feed_query' );

// Add bookmarks to the main site feed without displacing whatever post types are already queried there.
function asbm_add_to_main_feed( $query ) {
    if ( $query->is_feed() && ! $query->is_feed( 'bookmarks' ) && $query->is_main_query() ) {
        $post_types = $query->get( 'post_type' );
        if ( empty( $post_types ) ) {
            $post_types = array( 'post' );
        } elseif ( ! is_array( $post_types ) ) {
            $post_types = array( $post_types );
        }

        if ( ! in_array( 'bookmark', $post_types, true ) ) {
            $post_types[] = 'bookmark';
            $query->set( 'post_type', $post_types );
        }
    }
    return $query;
}
add_action( 'pre_get_posts', 'asbm_add_to_main_feed' );

function asbm_flush_rewrite_rules() {
    asbm_plugin_init();
    asbm_register_bookmarks_feed();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'asbm_flush_rewrite_rules' );

function asbm_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'asbm_deactivate' );

// Admin list table columns
function asbm_bookmark_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $label ) {
        $new_columns[ $key ] = $label;
        if ( 'title' === $key ) {
            $new_columns['asbm_url'] = __( 'URL', 'as-bookmarks' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_bookmark_posts_columns', 'asbm_bookmark_columns' );

function asbm_bookmark_column_content( $column, $post_id ) {
    if ( 'asbm_url' === $column ) {
        $url = get_post_meta( $post_id, 'asbm_url', true );
        if ( $url ) {
            echo '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a>';
        }
    }
}
add_action( 'manage_bookmark_posts_custom_column', 'asbm_bookmark_column_content', 10, 2 );

// Show the target URL and tags around the commentary on the single bookmark view, using the theme's normal single template.
function asbm_bookmark_content( $content ) {
    if ( ! is_singular( 'bookmark' ) || ! in_the_loop() || ! is_main_query() ) {
        return $content;
    }

    $post_id = get_the_ID();
    $url = get_post_meta( $post_id, 'asbm_url', true );
    $output = '';

    if ( $url ) {
        $output .= '<p class="asbm-bookmark-link"><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $url ) . '</a></p>';
    }

    $output .= $content;

    $tags = get_the_terms( $post_id, 'bookmark_tag' );
    if ( $tags && ! is_wp_error( $tags ) ) {
        $tag_links = array();
        foreach ( $tags as $tag ) {
            $tag_links[] = '<a href="' . esc_url( get_term_link( $tag ) ) . '">' . esc_html( $tag->name ) . '</a>';
        }
        $output .= '<p class="asbm-bookmark-tags">' . esc_html__( 'Tags:', 'as-bookmarks' ) . ' ' . implode( ', ', $tag_links ) . '</p>';
    }

    return $output;
}
add_filter( 'the_content', 'asbm_bookmark_content' );

// [as_bookmarks] shortcode: list bookmarks, optionally filtered by tag.
function asbm_bookmarks_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'tag' => '',
        'count' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ), $atts, 'as_bookmarks' );

    $args = array(
        'post_type' => 'bookmark',
        'posts_per_page' => (int) $atts['count'],
        'orderby' => sanitize_key( $atts['orderby'] ),
        'order' => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC',
    );

    if ( ! empty( $atts['tag'] ) ) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'bookmark_tag',
                'field' => 'slug',
                'terms' => array_map( 'sanitize_title', explode( ',', $atts['tag'] ) ),
            ),
        );
    }

    $bookmarks = get_posts( $args );

    if ( empty( $bookmarks ) ) {
        return '';
    }

    $output = '<ul class="asbm-bookmark-list">';
    foreach ( $bookmarks as $bookmark ) {
        $url = get_post_meta( $bookmark->ID, 'asbm_url', true );
        $commentary = wp_strip_all_tags( $bookmark->post_content );
        $tags = get_the_terms( $bookmark->ID, 'bookmark_tag' );

        $output .= '<li class="asbm-bookmark">';
        $output .= '<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( get_the_title( $bookmark ) ) . '</a>';
        if ( $commentary ) {
            $output .= '<p class="asbm-bookmark-commentary">' . esc_html( $commentary ) . '</p>';
        }
        if ( $tags && ! is_wp_error( $tags ) ) {
            $tag_names = wp_list_pluck( $tags, 'name' );
            $output .= '<p class="asbm-bookmark-tags">' . esc_html( implode( ', ', $tag_names ) ) . '</p>';
        }
        $output .= '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode( 'as_bookmarks', 'asbm_bookmarks_shortcode' );
