<?php
/**
 *        wp_enqueue_script(
            'eii-admin',
            plugin_dir_url(__FILE__) . 'admin.js',
            array('jquery'),
            '1.0.5', // Updated version for cache busting
            true
        );ge External Image Impor    public function enqueue_scrip    public function enqueue_scripts($hook) {
        $this->debug_log("EII: enqueue_scripts called on hook: $hook");

        if ($hook !== 'tools_page_external-image-importer') {
            $this->debug_log("EII: Wrong hook, not enqueuing scripts");
            return;
        }

        $this->debug_log            // Store original URL as meta for reference
            update_post_meta($attachment_id, '_original_external_url', $image_url);

            error_log("EII Debug: Successfully imported image: $image_url -> attachment ID: $attachment_id");

            return array(
                'success' => true,
                'attachment_id' => $attachment_id
            );

        } catch (Exception $e) {
            error_log("EII Debug: Exception importing $image_url: " . $e->getMessage());
            return array(
                'success' => false,
                'error' => 'Exception: ' . $e->getMessage()
            );
        }
    }g scripts");
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'eii-admin',
            plugin_dir_url(__FILE__) . 'admin.js',
            array('jquery'),
            '1.0.2', // Updated version to bust cache
            true
        );

        wp_localize_script('eii-admin', 'eii_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eii_nonce')
        ));

        $this->debug_log("EII: Scripts enqueued, AJAX URL: " . admin_url('admin-ajax.php'));
    } error_log("EII: enqueue_scripts called on hook: $hook");

        if ($hook !== 'tools_page_external-image-importer') {
            error_log("EII: Wrong hook, not enqueuing scripts");
            return;
        }

        error_log("EII: Enqueuing scripts");
        wp_enqueue_script('jquery');
        wp_enqueue_script(
            'eii-admin',
            plugin_dir_url(__FILE__) . 'admin.js',
            array('jquery'),
            '1.0.1', // Updated version to bust cache
            true
        );

        wp_localize_script('eii-admin', 'eii_ajax', array(
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('eii_nonce')
        ));

        error_log("EII: Scripts enqueued, AJAX URL: " . admin_url('admin-ajax.php'));
    }.5
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
        $this->debug_log("EII: Plugin constructor called");
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_import_external_images', array($this, 'ajax_import_images'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
        $this->debug_log("EII: All actions registered");
    }

    private function debug_log($message) {
        // Write to both error_log and a custom file
        error_log($message);

        // Also write to a custom log file in the plugin directory
        // $log_file = plugin_dir_path(__FILE__) . 'debug.log';
        // $timestamp = date('Y-m-d H:i:s');
        // file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND | LOCK_EX);
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
            '1.0.5', // Updated version to bust cache
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
        $this->debug_log("=== EII AJAX HANDLER CALLED ===");
        $this->debug_log("POST data: " . print_r($_POST, true));

        check_ajax_referer('eii_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            $this->debug_log("EII Error: User lacks manage_options capability");
            wp_die('Unauthorized');
        }

        // Increase execution time for this request
        set_time_limit(0); // No time limit
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '512M'); // Increase memory limit

        $this->debug_log("EII: Starting ajax_import_images function");        $batch_size = 5; // Process 5 posts per batch
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

        // Debug the continuation logic in detail
        error_log("EII Debug: Continuation analysis:");
        error_log("EII Debug: - Posts found: " . count($posts));
        error_log("EII Debug: - Batch size: $batch_size");
        error_log("EII Debug: - Posts found === batch_size: " . (count($posts) === $batch_size ? 'true' : 'false'));
        error_log("EII Debug: - new_last_id: $new_last_id (old: $last_id)");
        error_log("EII Debug: - has_more: " . ($has_more ? 'true' : 'false'));

        // If we're stopping, let's see why
        if (!$has_more) {
            error_log("EII Debug: STOPPING - Reason: " .
                (empty($posts) ? "No posts found" : "Incomplete batch (found " . count($posts) . " of $batch_size)")
            );

            // Additional diagnostic: check if there are actually more posts beyond this point
            if (!empty($posts)) {
                $diagnostic_query = $wpdb->prepare("
                    SELECT COUNT(ID) as remaining_count, MIN(ID) as next_min_id
                    FROM {$wpdb->posts}
                    WHERE post_type IN ($post_types_placeholders)
                    AND post_status = 'publish'
                    AND ID > %d
                ", array_merge($post_types, [$new_last_id]));

                $diagnostic_result = $wpdb->get_row($diagnostic_query);
                error_log("EII Debug: Diagnostic check - Posts remaining after ID $new_last_id: {$diagnostic_result->remaining_count}, Next ID: {$diagnostic_result->next_min_id}");
            }
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

            // Set a per-post timeout to prevent hanging
            $start_time = time();
            $max_post_time = 30; // 30 seconds max per post

            $result = $this->process_post($post_id);

            $processing_time = time() - $start_time;
            error_log("EII Debug: Post $post_id result - imported: {$result['imported']}, errors: " . count($result['errors']) . ", time: {$processing_time}s");

            $results['processed']++;
            $results['imported'] += $result['imported'];
            if (!empty($result['errors'])) {
                $results['errors'] = array_merge($results['errors'], $result['errors']);
            }

            // If this post took too long, we might be hitting issues - but continue anyway
            if ($processing_time > $max_post_time) {
                error_log("EII Warning: Post $post_id took {$processing_time}s to process (>$max_post_time limit)");
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
            $processed_images = 0;
            $post_start_time = time();
            $max_post_processing_time = 20; // Reduced from 60 to 20 seconds max per post
            $content_modified = false; // Track if content was actually changed

            foreach ($image_urls as $image_url) {
                if ($this->is_external_url($image_url)) {
                    // Check if we've spent too much time on this post
                    if (time() - $post_start_time > $max_post_processing_time) {
                        $remaining_count = count($image_urls) - $processed_images;
                        error_log("EII Debug: Stopping image processing for post $post_id after {$max_post_processing_time}s - $remaining_count images remaining");
                        break;
                    }

                    error_log("EII Debug: Importing external image: $image_url");
                    $result = $this->import_image($image_url, $post_id);
                    $processed_images++;

                    if ($result['success']) {
                        // Replace the URL in content
                        $new_url = wp_get_attachment_url($result['attachment_id']);
                        $old_content = $content;

                        // Try multiple replacement strategies for better matching
                        $content = str_replace($image_url, $new_url, $content);

                        // If that didn't work, try with URL encoding/decoding variations
                        if ($old_content === $content) {
                            $content = str_replace(urlencode($image_url), $new_url, $content);
                        }
                        if ($old_content === $content) {
                            $content = str_replace(urldecode($image_url), $new_url, $content);
                        }
                        // Try with HTML entity encoding
                        if ($old_content === $content) {
                            $content = str_replace(htmlspecialchars($image_url), $new_url, $content);
                        }

                        error_log("EII Debug: URL replacement - Old: $image_url -> New: $new_url");
                        if ($old_content === $content) {
                            error_log("EII Debug: WARNING - URL replacement had no effect for $image_url in post $post_id");
                            // Let's see what the content actually contains around this URL
                            if (strpos($content, $image_url) !== false) {
                                error_log("EII Debug: URL found in content but replacement failed");
                            } else {
                                error_log("EII Debug: URL not found in content - may be in different format");
                            }
                        } else {
                            error_log("EII Debug: Successfully replaced URL in post content");
                            $content_modified = true; // Mark content as changed
                        }

                        // If this was the featured image, update it
                        if ($image_url === $featured_image_url) {
                            set_post_thumbnail($post_id, $result['attachment_id']);
                            error_log("EII Debug: Updated featured image for post $post_id");
                        }

                        $imported++;
                    } else {
                        $errors[] = "Failed to import {$image_url}: " . $result['error'];
                        error_log("EII Debug: Failed to import {$image_url} for post {$post_id}: " . $result['error']);
                    }
                }
            }

            // Update post content if any URLs were actually replaced
            if ($content_modified) {
                $update_result = wp_update_post(array(
                    'ID' => $post_id,
                    'post_content' => $content
                ));

                if (is_wp_error($update_result)) {
                    error_log("EII Debug: ERROR - Failed to update post $post_id content: " . $update_result->get_error_message());
                } else {
                    error_log("EII Debug: Successfully updated post $post_id content (content was modified)");
                }
            } else {
                error_log("EII Debug: No content changes for post $post_id, skipping content update");
            }
        }

        error_log("EII Debug: Post $post_id result - imported: $imported, errors: " . count($errors) . " (out of " . count($image_urls) . " total images, $external_count external)");

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

        // Check if domain is known to be unreachable (fail fast)
        $unreachable_domains = ['alexseifertmusic.com', 'www.alexseifertmusic.com', 'thoughts.alexseifert.com'];
        $domain = parse_url($image_url, PHP_URL_HOST);
        error_log("EII Debug: Checking domain: '$domain' against blacklist: " . implode(', ', $unreachable_domains));
        if (in_array($domain, $unreachable_domains)) {
            error_log("EII Debug: Skipping known unreachable domain: $domain");
            return array(
                'success' => false,
                'error' => 'Domain marked as unreachable: ' . $domain
            );
        }
        error_log("EII Debug: Domain '$domain' not in blacklist, proceeding with download");

        // Check if image already exists by URL
        $existing_attachment = $this->get_attachment_by_url($image_url);
        if ($existing_attachment) {
            error_log("EII Debug: Image already exists as attachment ID: $existing_attachment");
            return array(
                'success' => true,
                'attachment_id' => $existing_attachment,
                'message' => 'Image already exists'
            );
        }

        try {
            // Single fast timeout attempt only
            $timeout = 3; // Very short timeout
            error_log("EII Debug: Trying download with {$timeout}s timeout: $image_url");

            // Set timeout filters
            add_filter('http_request_timeout', function() use ($timeout) { return $timeout; });
            add_filter('http_request_args', function($args) use ($timeout) {
                $args['timeout'] = $timeout;
                return $args;
            });

            $temp_file = download_url($image_url, $timeout);

            // Remove filters after attempt
            remove_all_filters('http_request_timeout');
            remove_all_filters('http_request_args');

            if (is_wp_error($temp_file)) {
                $error_msg = $temp_file->get_error_message();
                error_log("EII Debug: Failed with {$timeout}s timeout: $error_msg");
                return array(
                    'success' => false,
                    'error' => $error_msg
                );
            }            // Get file info and generate a proper filename
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
error_log("EII: About to initialize External Image Importer plugin");
$eii_instance = new ExternalImageImporter();
error_log("EII: Plugin initialized");

// Also write to custom log
// $log_file = plugin_dir_path(__FILE__) . 'debug.log';
// $timestamp = date('Y-m-d H:i:s');
// file_put_contents($log_file, "[$timestamp] EII: Plugin file loaded and initialized\n", FILE_APPEND | LOCK_EX);

?>
