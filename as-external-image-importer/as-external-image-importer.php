<?php
/**
 * @package External Image Importer
 * @version 1.0.0
 */
/*
Plugin Name: External Image Importer
Plugin URI: https://www.alexseifert.com
Description: Imports external images from posts into the WordPress media library
Author: Alex Seifert
Version: 1.0.0
Author URI: https://www.alexseifert.com
*/

if (!defined('ABSPATH')) {
    exit;
}

class ExternalImageImporter {
    
    private $processed_count = 0;
    private $imported_count = 0;
    private $errors = array();
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_import_external_images', array($this, 'ajax_import_images'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    public function add_admin_menu() {
        add_management_page(
            'External Image Importer',
            'Image Importer',
            'manage_options',
            'external-image-importer',
            array($this, 'admin_page')
        );
    }
    
    public function enqueue_scripts($hook) {
        if ($hook !== 'tools_page_external-image-importer') {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'eii-admin',
            plugin_dir_url(__FILE__) . 'admin.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('eii-admin', 'eii_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eii_nonce')
        ));
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>External Image Importer</h1>
            <div id="eii-status"></div>
            <div id="eii-progress" style="display: none;">
                <div style="background: #f1f1f1; border: 1px solid #ddd; height: 20px; border-radius: 3px; overflow: hidden;">
                    <div id="eii-progress-bar" style="background: #0073aa; height: 100%; width: 0%; transition: width 0.3s;"></div>
                </div>
                <p id="eii-progress-text">Starting...</p>
            </div>
            <div id="eii-results" style="display: none;">
                <h3>Import Results</h3>
                <p><strong>Posts processed:</strong> <span id="eii-processed">0</span></p>
                <p><strong>Images imported:</strong> <span id="eii-imported">0</span></p>
                <div id="eii-errors"></div>
            </div>
            <button id="eii-start-import" class="button button-primary">Start Import</button>
            <button id="eii-stop-import" class="button" style="display: none;">Stop Import</button>
        </div>
        <style>
            .eii-error {
                background: #ffebe8;
                border-left: 4px solid #cc0000;
                padding: 10px;
                margin: 10px 0;
            }
        </style>
        <?php
    }
    
    public function ajax_import_images() {
        check_ajax_referer('eii_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized');
        }
        
        $batch_size = 5; // Process 5 posts per batch
        $offset = intval($_POST['offset'] ?? 0);
        
        $posts = get_posts(array(
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => $batch_size,
            'offset' => $offset,
            'fields' => 'ids'
        ));
        
        $results = array(
            'processed' => 0,
            'imported' => 0,
            'errors' => array(),
            'has_more' => count($posts) === $batch_size,
            'next_offset' => $offset + $batch_size
        );
        
        foreach ($posts as $post_id) {
            $result = $this->process_post($post_id);
            $results['processed']++;
            $results['imported'] += $result['imported'];
            if (!empty($result['errors'])) {
                $results['errors'] = array_merge($results['errors'], $result['errors']);
            }
        }
        
        wp_send_json_success($results);
    }
    
    private function process_post($post_id) {
        $post = get_post($post_id);
        $content = $post->post_content;
        $imported = 0;
        $errors = array();
        
        // Find all img tags with external URLs
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        
        if (!empty($matches[1])) {
            foreach ($matches[1] as $index => $image_url) {
                if ($this->is_external_url($image_url)) {
                    $result = $this->import_image($image_url, $post_id);
                    if ($result['success']) {
                        // Replace the URL in content
                        $new_url = wp_get_attachment_url($result['attachment_id']);
                        $content = str_replace($image_url, $new_url, $content);
                        $imported++;
                    } else {
                        $errors[] = "Failed to import {$image_url}: " . $result['error'];
                    }
                }
            }
            
            // Update post content if any images were imported
            if ($imported > 0) {
                wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $content
                ));
            }
        }
        
        return array(
            'imported' => $imported,
            'errors' => $errors
        );
    }
    
    private function is_external_url($url) {
        $site_url = get_site_url();
        $upload_dir = wp_get_upload_dir();
        
        // Check if URL is external (not from current site)
        return !empty($url) && 
               strpos($url, 'http') === 0 && 
               strpos($url, $site_url) === false &&
               strpos($url, $upload_dir['baseurl']) === false;
    }
    
    private function import_image($image_url, $post_id) {
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        
        // Check if image already exists by URL
        $existing_attachment = $this->get_attachment_by_url($image_url);
        if ($existing_attachment) {
            return array(
                'success' => true,
                'attachment_id' => $existing_attachment,
                'message' => 'Image already exists'
            );
        }
        
        try {
            // Download the image
            $temp_file = download_url($image_url, 300); // 5 minute timeout
            
            if (is_wp_error($temp_file)) {
                return array(
                    'success' => false,
                    'error' => $temp_file->get_error_message()
                );
            }
            
            // Get file info
            $file_array = array(
                'name' => basename(parse_url($image_url, PHP_URL_PATH)),
                'tmp_name' => $temp_file
            );
            
            // Handle the upload
            $attachment_id = media_handle_sideload($file_array, $post_id);
            
            // Clean up temp file
            @unlink($temp_file);
            
            if (is_wp_error($attachment_id)) {
                return array(
                    'success' => false,
                    'error' => $attachment_id->get_error_message()
                );
            }
            
            // Store original URL as meta for reference
            update_post_meta($attachment_id, '_original_external_url', $image_url);
            
            return array(
                'success' => true,
                'attachment_id' => $attachment_id
            );
            
        } catch (Exception $e) {
            return array(
                'success' => false,
                'error' => $e->getMessage()
            );
        }
    }
    
    private function get_attachment_by_url($url) {
        global $wpdb;
        
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_original_external_url' AND meta_value = %s LIMIT 1",
            $url
        ));
        
        return $attachment_id ? intval($attachment_id) : false;
    }
}

// Initialize the plugin
new ExternalImageImporter();