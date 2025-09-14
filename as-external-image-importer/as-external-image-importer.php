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
        $post_types = get_post_types(array('public' => true), 'objects');
        ?>
        <div class="wrap">
            <h1>External Image Importer</h1>
            <div id="eii-options" style="margin-bottom: 20px;">
                <h3>Import Options</h3>
                <div style="background: #fff; padding: 15px; border: 1px solid #ddd;">
                    <label><strong>Post Types to Process:</strong></label><br>
                    <?php foreach ($post_types as $post_type): ?>
                        <label style="display: inline-block; margin-right: 15px;">
                            <input type="checkbox" name="post_types[]" value="<?php echo esc_attr($post_type->name); ?>"
                                   <?php echo in_array($post_type->name, array('post', 'page')) ? 'checked' : ''; ?>>
                            <?php echo esc_html($post_type->label); ?>
                        </label>
                    <?php endforeach; ?>
                    <br><br>
                    <p><em>Select which post types to scan for external images. Posts and Pages are selected by default.</em></p>
                </div>
            </div>
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
        $post_types = isset($_POST['post_types']) ? $_POST['post_types'] : array('post', 'page');

        // Ensure post_types is an array
        if (!is_array($post_types)) {
            $post_types = array('post', 'page');
        }

        $posts = get_posts(array(
            'post_type' => $post_types,
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
        $image_urls = array();

        // Find all img tags with various src attributes
        $patterns = array(
            // Standard src attribute
            '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i',
            // data-src for lazy loading
            '/<img[^>]+data-src=["\']([^"\']+)["\'][^>]*>/i',
            // data-lazy-src for lazy loading
            '/<img[^>]+data-lazy-src=["\']([^"\']+)["\'][^>]*>/i',
            // Extract URLs from srcset attributes
            '/<img[^>]+srcset=["\']([^"\']+)["\'][^>]*>/i'
        );

        foreach ($patterns as $pattern) {
            preg_match_all($pattern, $content, $matches);
            if (!empty($matches[1])) {
                foreach ($matches[1] as $match) {
                    if (strpos($pattern, 'srcset') !== false) {
                        // Handle srcset - extract individual URLs
                        $srcset_urls = $this->extract_srcset_urls($match);
                        $image_urls = array_merge($image_urls, $srcset_urls);
                    } else {
                        $image_urls[] = $match;
                    }
                }
            }
        }

        // Process featured image
        $featured_image_url = $this->get_featured_image_url($post_id);
        if ($featured_image_url) {
            $image_urls[] = $featured_image_url;
        }

        // Remove duplicates
        $image_urls = array_unique($image_urls);

        if (!empty($image_urls)) {
            foreach ($image_urls as $image_url) {
                if ($this->is_external_url($image_url)) {
                    $result = $this->import_image($image_url, $post_id);
                    if ($result['success']) {
                        // Replace the URL in content
                        $new_url = wp_get_attachment_url($result['attachment_id']);
                        $content = str_replace($image_url, $new_url, $content);

                        // If this was the featured image, update it
                        if ($image_url === $featured_image_url) {
                            set_post_thumbnail($post_id, $result['attachment_id']);
                        }

                        $imported++;
                    } else {
                        $errors[] = "Failed to import {$image_url}: " . $result['error'];
                        error_log("External Image Importer: Failed to import {$image_url} for post {$post_id}: " . $result['error']);
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

    private function extract_srcset_urls($srcset) {
        $urls = array();
        // Split by commas and extract URLs
        $sources = explode(',', $srcset);
        foreach ($sources as $source) {
            $source = trim($source);
            // Extract URL (everything before the first space)
            if (preg_match('/^([^\s]+)/', $source, $matches)) {
                $urls[] = $matches[1];
            }
        }
        return $urls;
    }

    private function get_featured_image_url($post_id) {
        $thumbnail_id = get_post_thumbnail_id($post_id);
        if (!$thumbnail_id) {
            return false;
        }

        $url = wp_get_attachment_url($thumbnail_id);
        return $url ? $url : false;
    }

    private function is_external_url($url) {
        if (empty($url)) {
            return false;
        }

        // Handle protocol-relative URLs
        if (strpos($url, '//') === 0) {
            $url = 'https:' . $url;
        }

        // Must be a valid URL with http/https protocol
        if (!filter_var($url, FILTER_VALIDATE_URL) || !preg_match('/^https?:\/\//', $url)) {
            return false;
        }

        $site_url = get_site_url();
        $upload_dir = wp_get_upload_dir();

        // Parse URLs for better comparison
        $url_host = parse_url($url, PHP_URL_HOST);
        $site_host = parse_url($site_url, PHP_URL_HOST);
        $upload_host = parse_url($upload_dir['baseurl'], PHP_URL_HOST);

        // Check if URL is external (not from current site or uploads)
        return $url_host !== $site_host && $url_host !== $upload_host;
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
            // Download the image with shorter timeout
            $temp_file = download_url($image_url, 60); // 1 minute timeout

            if (is_wp_error($temp_file)) {
                return array(
                    'success' => false,
                    'error' => $temp_file->get_error_message()
                );
            }

            // Get file info and generate a proper filename
            $parsed_url = parse_url($image_url);
            $path = $parsed_url['path'] ?? '';
            $filename = basename($path);

            // If no extension, try to detect from content type
            if (!pathinfo($filename, PATHINFO_EXTENSION)) {
                $file_info = wp_check_filetype($temp_file);
                if ($file_info['ext']) {
                    $filename .= '.' . $file_info['ext'];
                } else {
                    $filename .= '.jpg'; // Default fallback
                }
            }

            // Sanitize filename
            $filename = sanitize_file_name($filename);
            if (empty($filename)) {
                $filename = 'imported-image-' . time() . '.jpg';
            }

            $file_array = array(
                'name' => $filename,
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
