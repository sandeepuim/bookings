<?php
// Clear TBO API caches
delete_transient('tbo_hotels_countries');

// Clear city caches for all countries
global $wpdb;
$all_transients = $wpdb->get_col("
    SELECT option_name
    FROM {$wpdb->options}
    WHERE option_name LIKE '%\_transient\_tbo_hotels_cities\_%'
");

foreach ($all_transients as $transient) {
    $key = str_replace('_transient_', '', $transient);
    delete_transient($key);
}

echo "TBO API caches cleared!";