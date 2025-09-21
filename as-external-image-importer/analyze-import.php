<?php
/**
 * Simple script to analyze import results from debug.log
 * Run this script to get a summary of what happened during import
 */

$debug_log = __DIR__ . '/debug.log';

if (!file_exists($debug_log)) {
    echo "Debug log not found at: $debug_log\n";
    exit;
}

$log_content = file_get_contents($debug_log);
$lines = explode("\n", $log_content);

$stats = [
    'total_posts_processed' => 0,
    'total_images_found' => 0,
    'total_external_images' => 0,
    'total_imported' => 0,
    'total_failed' => 0,
    'posts_with_images' => 0,
    'posts_with_external_images' => 0,
    'common_errors' => [],
    'skipped_due_to_limit' => 0
];

$current_post = null;

foreach ($lines as $line) {
    // Track posts being processed
    if (preg_match('/Processing post ID (\d+)/', $line, $matches)) {
        $stats['total_posts_processed']++;
        $current_post = $matches[1];
    }

    // Track images found
    if (preg_match('/Found (\d+) total image URLs in post (\d+)/', $line, $matches)) {
        $count = intval($matches[1]);
        $stats['total_images_found'] += $count;
        if ($count > 0) {
            $stats['posts_with_images']++;
        }
    }

    // Track external images
    if (preg_match('/Found (\d+) external images in post (\d+)/', $line, $matches)) {
        $count = intval($matches[1]);
        $stats['total_external_images'] += $count;
        if ($count > 0) {
            $stats['posts_with_external_images']++;
        }
    }

    // Track successful imports
    if (strpos($line, 'Successfully imported image') !== false) {
        $stats['total_imported']++;
    }

    // Track failures
    if (strpos($line, 'Failed to import') !== false) {
        $stats['total_failed']++;

        // Extract error type
        if (preg_match('/Failed to import .+?: (.+)$/', $line, $matches)) {
            $error = trim($matches[1]);
            if (!isset($stats['common_errors'][$error])) {
                $stats['common_errors'][$error] = 0;
            }
            $stats['common_errors'][$error]++;
        }
    }

    // Track skipped images due to limit
    if (strpos($line, 'Skipping remaining images') !== false) {
        $stats['skipped_due_to_limit']++;
        if (preg_match('/(\d+) images skipped/', $line, $matches)) {
            $stats['skipped_due_to_limit'] += intval($matches[1]) - 1; // -1 because we already counted the increment above
        }
    }
}

// Sort errors by frequency
arsort($stats['common_errors']);

echo "=== IMPORT ANALYSIS ===\n";
echo "Posts processed: {$stats['total_posts_processed']}\n";
echo "Posts with images: {$stats['posts_with_images']}\n";
echo "Posts with external images: {$stats['posts_with_external_images']}\n";
echo "Total images found: {$stats['total_images_found']}\n";
echo "Total external images: {$stats['total_external_images']}\n";
echo "Successfully imported: {$stats['total_imported']}\n";
echo "Failed imports: {$stats['total_failed']}\n";
echo "Skipped due to limit: {$stats['skipped_due_to_limit']}\n";

if ($stats['total_external_images'] > 0) {
    $success_rate = round(($stats['total_imported'] / $stats['total_external_images']) * 100, 1);
    echo "Success rate: {$success_rate}%\n";
}

if (!empty($stats['common_errors'])) {
    echo "\n=== COMMON ERRORS ===\n";
    foreach ($stats['common_errors'] as $error => $count) {
        echo "$count x $error\n";
    }
}

echo "\n";
