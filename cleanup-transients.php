<?php
// Cleanup script for TBO Hotels transients
require_once 'wp-config.php';
require_once 'wp-load.php';

global $wpdb;

// Delete TBO-related transients
$deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_tbo_%' OR option_name LIKE '_transient_timeout_tbo_%'");
echo "Deleted $deleted TBO transients\n";

// Also check for any very large transients that might cause issues
$large_transients = $wpdb->get_results("
    SELECT option_name, CHAR_LENGTH(option_value) as size 
    FROM {$wpdb->options} 
    WHERE option_name LIKE '_transient_%' 
    AND CHAR_LENGTH(option_value) > 100000
    LIMIT 10
");

if ($large_transients) {
    echo "Found large transients:\n";
    foreach ($large_transients as $transient) {
        echo "- {$transient->option_name}: {$transient->size} chars\n";
        // Delete this large transient
        $key = str_replace('_transient_', '', $transient->option_name);
        delete_transient($key);
        echo "  Deleted: $key\n";
    }
} else {
    echo "No large transients found.\n";
}

echo "Cleanup complete!\n";