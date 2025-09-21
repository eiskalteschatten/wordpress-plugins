<?php
/**
 * Plugin Name: AS External Image Importer
 * Description: Import external images into WordPress media library and update post content
 * Version: 1.0.7
 * Author: Alex Seifert
 */

// Prevent direct access
if (!defined("ABSPATH")) {
    exit;
}

class ASExternalImageImporter {

    private $unreachable_domains = [
        "alexseifertmusic.com",
        "www.alexseifertmusic.com",
        "thoughts.alexseifert.com",
        "www.undesregnet.com",
        "seifertalex.googlepages.com",
        "www.alexseifert.com"
    ];

    public function __construct() {
        add_action("admin_menu", array($this, "admin_menu"));
        add_action("admin_enqueue_scripts", array($this, "enqueue_scripts"));
        add_action("wp_ajax_eii_import_images", array($this, "ajax_import_images"));
        add_action("wp_ajax_eii_get_stats", array($this, "ajax_get_stats"));
    }

    public function admin_menu() {
        add_management_page(
            "External Image Importer",
            "External Image Importer",
            "manage_options",
            "external-image-importer",
            array($this, "admin_page")
        );
    }

    public function enqueue_scripts($hook) {
        if ($hook != "tools_page_external-image-importer") {
            return;
        }

        wp_enqueue_script(
            "eii-admin",
            plugin_dir_url(__FILE__) . "admin.js",
            array("jquery"),
            "1.0.6",
            true
        );

        wp_localize_script("eii-admin", "eii_ajax", array(
            "ajaxurl" => admin_url("admin-ajax.php"),
            "nonce" => wp_create_nonce("eii_nonce")
        ));
    }

    public function admin_page() {
        echo "<div class=\"wrap\">
            <h1>External Image Importer</h1>
            <div id=\"eii-progress\" style=\"margin: 20px 0;\">
                <div class=\"progress-bar\" style=\"width: 100%; height: 30px; background: #f0f0f0; border: 1px solid #ccc;\">
                    <div id=\"progress-fill\" style=\"height: 100%; background: #0073aa; width: 0%; transition: width 0.3s;\"></div>
                </div>
                <p id=\"progress-text\">Ready to start import</p>
                <div id=\"stats\"></div>
            </div>
            <button id=\"start-import\" class=\"button button-primary\">Start Import</button>
            <button id=\"stop-import\" class=\"button\" style=\"display:none;\">Stop Import</button>
            <div id=\"debug-log\" style=\"margin-top: 20px; padding: 10px; background: #f9f9f9; border: 1px solid #ddd; height: 300px; overflow-y: scroll; font-family: monospace; font-size: 12px;\"></div>
        </div>";
    }

    public function ajax_get_stats() {
        check_ajax_referer("eii_nonce", "nonce");

        global $wpdb;

        $total_posts = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'");

        wp_send_json_success(array(
            "total_posts" => $total_posts
        ));
    }

    public function ajax_import_images() {
        // Set unlimited execution time for this operation
        set_time_limit(0);

        check_ajax_referer("eii_nonce", "nonce");

        $batch_size = intval($_POST["batch_size"]) ?: 10;
        $last_id = intval($_POST["last_id"]) ?: 0;

        $this->debug_log("EII: Starting batch import - batch_size: $batch_size, last_id: $last_id");

        global $wpdb;

        // Use cursor-based pagination instead of OFFSET
        $posts = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, post_title, post_content
             FROM {$wpdb->posts}
             WHERE post_status = 'publish'
             AND post_type = 'post'
             AND ID > %d
             ORDER BY ID ASC
             LIMIT %d",
            $last_id,
            $batch_size
        ));

        $this->debug_log("EII: Found " . count($posts) . " posts to process");

        $imported = 0;
        $skipped = 0;
        $errors = 0;
        $content_modified = 0;
        $new_last_id = $last_id;

        foreach ($posts as $post) {
            $new_last_id = $post->ID;
            $result = $this->process_post($post);

            if ($result["imported"] > 0) {
                $imported += $result["imported"];
            }
            if ($result["skipped"] > 0) {
                $skipped += $result["skipped"];
            }
            if ($result["errors"] > 0) {
                $errors += $result["errors"];
            }
            if ($result["content_modified"]) {
                $content_modified++;
            }
        }

        $has_more = count($posts) == $batch_size;

        $this->debug_log("EII: Batch complete - imported: $imported, skipped: $skipped, errors: $errors, content_modified: $content_modified, has_more: " . ($has_more ? "yes" : "no"));

        wp_send_json_success(array(
            "imported" => $imported,
            "skipped" => $skipped,
            "errors" => $errors,
            "content_modified" => $content_modified,
            "has_more" => $has_more,
            "last_id" => $new_last_id
        ));
    }

    private function process_post($post) {
        $this->debug_log("EII: Processing post {$post->ID}: {$post->post_title}");

        $content = $post->post_content;
        $original_content = $content;

        // Find all image tags
        preg_match_all("/<img[^>]+src=[\"']([^\"']+)[\"'][^>]*>/i", $content, $matches);

        $imported = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($matches[1] as $index => $image_url) {
            // Skip if already in media library
            if (strpos($image_url, wp_upload_dir()["baseurl"]) !== false) {
                $skipped++;
                continue;
            }

            // Skip relative URLs
            if (strpos($image_url, "http") !== 0) {
                $skipped++;
                continue;
            }

            // Check if domain is blacklisted
            $domain = parse_url($image_url, PHP_URL_HOST);
            if (in_array($domain, $this->unreachable_domains)) {
                $this->debug_log("EII: Skipping blacklisted domain: $domain for URL: $image_url");
                $skipped++;
                continue;
            }

            $result = $this->import_image($image_url, $post->ID);

            if ($result["success"]) {
                $content = str_replace($image_url, $result["new_url"], $content);
                $imported++;
                $this->debug_log("EII: Successfully imported image: $image_url -> {$result["new_url"]}");
            } else {
                $errors++;
                $this->debug_log("EII: Failed to import image: $image_url - Error: {$result["error"]}");
            }
        }

        // Update post content if it was modified
        $content_modified = false;
        if ($content !== $original_content) {
            wp_update_post(array(
                "ID" => $post->ID,
                "post_content" => $content
            ));
            $content_modified = true;
            $this->debug_log("EII: Updated post content for post {$post->ID}");
        }

        return array(
            "imported" => $imported,
            "skipped" => $skipped,
            "errors" => $errors,
            "content_modified" => $content_modified
        );
    }

    private function import_image($url, $post_id) {
        $this->debug_log("EII: Attempting to import image: $url for post $post_id");

        // Set a reasonable timeout for external requests
        $context = stream_context_create([
            "http" => [
                "timeout" => 3,
                "user_agent" => "WordPress External Image Importer"
            ]
        ]);

        $image_data = @file_get_contents($url, false, $context);

        if ($image_data === false) {
            return array(
                "success" => false,
                "error" => "Failed to fetch image data"
            );
        }

        // Get filename from URL
        $filename = basename(parse_url($url, PHP_URL_PATH));
        if (empty($filename) || strpos($filename, ".") === false) {
            $filename = "image_" . time() . ".jpg";
        }

        // Upload to WordPress
        $upload = wp_upload_bits($filename, null, $image_data);

        if ($upload["error"]) {
            return array(
                "success" => false,
                "error" => $upload["error"]
            );
        }

        // Create attachment
        $attachment = array(
            "post_mime_type" => wp_check_filetype($filename)["type"],
            "post_title" => sanitize_file_name($filename),
            "post_content" => "",
            "post_status" => "inherit",
            "post_parent" => $post_id
        );

        $attach_id = wp_insert_attachment($attachment, $upload["file"], $post_id);

        if (is_wp_error($attach_id)) {
            return array(
                "success" => false,
                "error" => $attach_id->get_error_message()
            );
        }

        // Generate metadata
        require_once(ABSPATH . "wp-admin/includes/image.php");
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload["file"]);
        wp_update_attachment_metadata($attach_id, $attach_data);

        return array(
            "success" => true,
            "new_url" => $upload["url"],
            "attachment_id" => $attach_id
        );
    }

    private function debug_log($message) {
        error_log($message);

        $log_file = plugin_dir_path(__FILE__) . "debug.log";
        $timestamp = date("Y-m-d H:i:s");
        file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    }
}

new ASExternalImageImporter();
