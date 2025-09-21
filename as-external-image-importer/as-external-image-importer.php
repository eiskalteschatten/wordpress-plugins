<?php
/**
 * @package External Image Importer
 * @version 1.0.5
 */
/*
Plugin Name: External Image Importer
Plugin URI: https://www.alexseifert.com
Description: Imports external images from posts into the WordPress media library
Author: Alex Seifert
Version: 1.0.5
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
        $last_id = intval($_POST['last_id'] ?? 0);
        $processed_count = intval($_POST['processed_count'] ?? 0);
        $post_types = isset($_POST['post_types']) ? $_POST['post_types'] : array('post', 'page');

        // Ensure post_types is an array
        if (!is_array($post_types)) {
            $post_types = array('post', 'page');
        }

        // Use direct database query to avoid WordPress query limitations
        global $wpdb;

        // Get total count first (only on first request)
        $total_posts = intval($_POST['total_posts'] ?? 0);
        if ($last_id === 0 || $total_posts === 0) {
            $post_types_placeholders = implode(',', array_fill(0, count($post_types), '%s'));
            $total_query = $wpdb->prepare("
                SELECT COUNT(ID)
                FROM {$wpdb->posts}
                WHERE post_type IN ($post_types_placeholders)
                AND post_status = 'publish'
            ", $post_types);

            $total_posts = $wpdb->get_var($total_query);
            error_log("EII Debug: Total posts query: " . str_replace(array("\n", "\t"), ' ', $total_query));
            error_log("EII Debug: Total posts found: $total_posts");

            if ($wpdb->last_error) {
                error_log("EII Database Error - Total Count: " . $wpdb->last_error);
            }

            // Additional diagnostic - let's see some sample post IDs
            if ($total_posts > 0) {
                $sample_query = $wpdb->prepare("
                    SELECT ID, post_title
                    FROM {$wpdb->posts}
                    WHERE post_type IN ($post_types_placeholders)
                    AND post_status = 'publish'
                    ORDER BY ID ASC
                    LIMIT 5
                ", $post_types);
                $sample_posts = $wpdb->get_results($sample_query);
                error_log("EII Debug: Sample posts: " . print_r($sample_posts, true));
            }
        }

        // Get current batch using cursor-based pagination (more efficient than OFFSET)
        $post_types_placeholders = implode(',', array_fill(0, count($post_types), '%s'));
        $batch_query = $wpdb->prepare("
            SELECT ID
            FROM {$wpdb->posts}
            WHERE post_type IN ($post_types_placeholders)
            AND post_status = 'publish'
            AND ID > %d
            ORDER BY ID ASC
            LIMIT %d
        ", array_merge($post_types, [$last_id, $batch_size]));

        $start_time = microtime(true);
        $posts = $wpdb->get_col($batch_query);
        $query_time = round((microtime(true) - $start_time) * 1000, 2);

        if ($wpdb->last_error) {
            error_log("EII Database Error - Batch Query: " . $wpdb->last_error);
            wp_send_json_error('Database error: ' . $wpdb->last_error);
            return;
        }

        if ($posts === false) {
            error_log("EII Database Error - Query returned false");
            wp_send_json_error('Database query failed');
            return;
        }

        // Debug logging
        error_log("=== EII BATCH START ===");
        error_log("EII Debug: Last ID: $last_id, Batch size: $batch_size, Posts found in batch: " . count($posts) . ", Total posts: $total_posts, Query time: {$query_time}ms");
        error_log("EII Debug: Post IDs in batch: " . implode(', ', $posts));
        error_log("EII Debug: SQL Query: " . str_replace(array("\n", "\t"), ' ', $batch_query));

        // Calculate new last_id and determine if there are more posts
        $new_last_id = !empty($posts) ? max($posts) : $last_id;

        // Determine if there are more posts to process
        // If we got fewer posts than requested, we've likely reached the end
        $has_more = count($posts) === $batch_size;

        // Additional safety check: if no posts were found and last_id > 0, we're done
        if (empty($posts) && $last_id > 0) {
            $has_more = false;
            error_log("EII Debug: No posts found with last_id > 0, setting has_more to false");
        }

        error_log("EII Debug: new_last_id = $new_last_id, has_more = " . ($has_more ? 'true' : 'false'));

        $results = array(
            'processed' => 0,
            'imported' => 0,
            'errors' => array(),
            'has_more' => $has_more,
            'last_id' => $new_last_id,
            'total_posts' => $total_posts,
            'processed_count' => $processed_count,
            'posts_in_batch' => count($posts),
            'query_time' => $query_time
        );

        foreach ($posts as $post_id) {
            error_log("EII Debug: Processing post ID $post_id");
            $result = $this->process_post($post_id);
            error_log("EII Debug: Post $post_id result - imported: {$result['imported']}, errors: " . count($result['errors']));
            $results['processed']++;
            $results['imported'] += $result['imported'];
            if (!empty($result['errors'])) {
                $results['errors'] = array_merge($results['errors'], $result['errors']);
            }
        }

        // Update the total processed count
        $results['processed_count'] = $processed_count + $results['processed'];

        error_log("EII Debug: Batch complete - processed: {$results['processed']}, imported: {$results['imported']}, total_processed: {$results['processed_count']}");
        error_log("=== EII BATCH END ===");

        wp_send_json_success($results);
    }

    private function process_post($post_id) {
        $post = get_post($post_id);
        if (!$post) {
            error_log("EII Debug: Post $post_id not found");
            return array('imported' => 0, 'errors' => array("Post $post_id not found"));
        }

        $content = $post->post_content;
        $imported = 0;
        $errors = array();
        $image_urls = array();

        error_log("EII Debug: Processing post $post_id ('{$post->post_title}'), content length: " . strlen($content));

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

        error_log("EII Debug: Found " . count($image_urls) . " total image URLs in post $post_id: " . implode(', ', array_slice($image_urls, 0, 5)) . (count($image_urls) > 5 ? '...' : ''));

        $external_count = 0;
        foreach ($image_urls as $url) {
            if ($this->is_external_url($url)) {
                $external_count++;
            }
        }
        error_log("EII Debug: Found $external_count external images in post $post_id");

        if (!empty($image_urls)) {
            foreach ($image_urls as $image_url) {
                if ($this->is_external_url($image_url)) {
                    error_log("EII Debug: Importing external image: $image_url");
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
