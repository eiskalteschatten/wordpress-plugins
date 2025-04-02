<?php
/**
 * @package Alex's Plugin
 * @version 1.0.3
 */
/*
Plugin Name: Alex's Plugin
Plugin URI: https://www.alexseifert.com
Description: Miscellaneous things.
Author: Alex Seifert
Version: 1.0.0
Author URI: https://www.alexseifert.com
*/

// require_once( 'hr-maps-admin.php' );

// function historyrhymes_add_scripts_styles() {
//     // Leaflet
//     $leaflet_version = '1.9.4';
//     wp_register_style( 'hr-maps-leaflet', plugin_dir_url( __FILE__ ) . 'lib/leaflet/leaflet.css', array(), $leaflet_version );
//     wp_enqueue_style( 'hr-maps-leaflet' );
//     wp_register_script( 'hr-maps-leaflet', plugin_dir_url( __FILE__ ) . 'lib/leaflet/leaflet.js', array(), $leaflet_version, array( 'strategy'  => 'defer' ));
//     wp_enqueue_script( 'hr-maps-leaflet' );

//     // Custom scripts and styles
//     wp_register_script( 'hr-maps-scripts', plugin_dir_url( __FILE__ ) . 'js/scripts.js', ['hr-maps-leaflet'], '1.0.0', array( 'strategy'  => 'defer' ));
//     wp_enqueue_script( 'hr-maps-scripts' );

//     wp_register_script( 'hr-maps-web-components', plugin_dir_url( __FILE__ ) . 'js/web-components/index.js', ['hr-maps-leaflet', 'hr-maps-scripts'], '1.0.1', array( 'strategy'  => 'defer' ));
//     wp_enqueue_script( 'hr-maps-web-components' );

//     wp_register_style( 'hr-maps-main', plugin_dir_url( __FILE__ ) . 'css/main.css', array(), '1.0.0' );
//     wp_enqueue_style( 'hr-maps-main' );
// }

// function historyrhymes_full_map( $atts = [], $content = '' ) {
//     historyrhymes_add_scripts_styles();

//     $the_query = new WP_Query( array (
//         'post_type'      => [ 'post', 'page' ],
//         'posts_per_page' => -1,
//         'post_status'    => 'publish',
//         'meta_query'     => array(
//             array(
//                 'key'     => 'hr_maps_coordinates',
//                 'value'   => '',
//                 'compare' => '!=',
//             )
//          )
//     ) );

//     $coordinate_data = [];
//     $all_coordinates = [];

//     foreach( $the_query->posts as $post ) {
//         $post_id  = $post->ID;
//         $coordinates = get_post_meta( $post_id, 'hr_maps_coordinates', true );
//         $decoded_data = json_decode( $coordinates );


//         if ($decoded_data === null) {
//             continue;
//         }

//         $coordinate_data[] = [
//             'postId' => $post_id,
//             'data' => $decoded_data
//         ];

//         foreach( $decoded_data as $data ) {
//             $all_coordinates[] = [ $data->lat, $data->lng ];
//         }
//     }

//     wp_reset_postdata();

//     $content .= '<script>';
//     $content .= 'const hrMapMarkerCoordinateData = ' . json_encode( $coordinate_data ) . ';';
//     $content .= 'const hrMapMarkerAllCoordinates = ' . json_encode( $all_coordinates ) . ';';
//     $content .= '</script>';

//     $content .= '<div class="hr-full-map-wrapper">';
//     $content .= '<hr-full-map></hr-full-map>';
//     $content .= '<hr-full-map-sidebar></hr-full-map-sidebar>';
//     $content .= '</div>';

//     return $content;
// }
// add_shortcode( 'hrfullmap', 'historyrhymes_full_map' );

// function historyrhymes_post_map( $atts = [], $content = '' ) {
//     $post_id = get_the_ID();
//     $coordinates = get_post_meta( $post_id, 'hr_maps_coordinates', true );

//     if ( !isset($coordinates) || $coordinates === '' ) {
//         return $content;
//     }

//     historyrhymes_add_scripts_styles();

//     $coordinate_data = [];
//     $all_coordinates = [];
//     $decoded_data = json_decode( $coordinates );
//     $coordinate_data[] = [
//         'postId' => $post_id,
//         'data' => $decoded_data
//     ];

//     foreach( $decoded_data as $data ) {
//         $all_coordinates[] = [ $data->lat, $data->lng ];
//     }

//     $content .= '<script>';
//     $content .= 'const hrMapMarkerCoordinateData = ' . json_encode( $coordinate_data ) . ';';
//     $content .= 'const hrMapMarkerAllCoordinates = ' . json_encode( $all_coordinates ) . ';';
//     $content .= '</script>';

//     $content .= '<div class="hr-post-map-wrapper">';
//     $content .= '<hr-map></hr-map>';
//     $content .= '</div>';

//     return $content;
// }
// add_shortcode( 'hrpostmap', 'historyrhymes_post_map' );

// function historyrhymes_make_scripts_modules( $tag, $handle, $src ) {
//     if ( 'hr-maps-web-components' !== $handle ) {
//         return $tag;
//     }

//     $id = $handle . '-js';

//     $parts = explode( '</script>', $tag ); // Break up our string

//     foreach ( $parts as $key => $part ) {
//         if ( false !== strpos( $part, $src ) ) { // Make sure we're only altering the tag for our module script.
//             $parts[ $key ] = '<script type="module" src="' . esc_url( $src ) . '" id="' . esc_attr( $id ) . '" defer data-wp-strategy=\'defer\'>';
//         }
//     }

//     $tags = implode( '</script>', $parts ); // Bring everything back together

//     return $tags;
// }
// add_filter( 'script_loader_tag', 'historyrhymes_make_scripts_modules' , 10, 3 );
