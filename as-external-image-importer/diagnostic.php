<?php
/**
 * Simple diagnostic script to check plugin status
 */

// Add this to a post or page to check if the plugin is working
echo "<h3>External Image Importer Diagnostic</h3>";

// Check if we can find some posts with images
global $wpdb;

$posts_with_images = $wpdb->get_results("
    SELECT ID, post_title, post_content
    FROM {$wpdb->posts}
    WHERE post_type = 'post'
    AND post_status = 'publish'
    AND post_content LIKE '%<img%'
    LIMIT 5
");

echo "<h4>Sample posts with images:</h4>";
foreach ($posts_with_images as $post) {
    echo "<p><strong>Post {$post->ID}: {$post->post_title}</strong></p>";

    // Find external images in this post
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $post->post_content, $matches);

    if (!empty($matches[1])) {
        echo "<ul>";
        foreach ($matches[1] as $img_url) {
            $is_external = (strpos($img_url, 'http') === 0 &&
                           strpos($img_url, get_site_url()) === false);
            echo "<li>" . esc_html($img_url) . " " . ($is_external ? "(EXTERNAL)" : "(internal)") . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No img tags found in content</p>";
    }
    echo "<hr>";
}

// Check recent debug log entries
$debug_log = '/var/www/blog/wp-content/debug.log';
if (file_exists($debug_log)) {
    echo "<h4>Recent debug log entries (last 20 lines):</h4>";
    $lines = file($debug_log);
    $recent_lines = array_slice($lines, -20);
    echo "<pre>" . esc_html(implode('', $recent_lines)) . "</pre>";
} else {
    echo "<p>Debug log not found at $debug_log</p>";
}
