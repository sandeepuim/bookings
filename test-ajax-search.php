<?php
// Test the AJAX search endpoint directly
require_once 'wp-config.php';
require_once 'wp-load.php';

echo "Testing AJAX hotel search...\n";

// Simulate the AJAX request
$_POST = array(
    'action' => 'tbo_hotels_search_hotels',
    'city_code' => '418069',
    'check_in' => '2025-09-23',
    'check_out' => '2025-09-24',
    'rooms' => '1',
    'adults' => '2',
    'children' => '0',
    'nonce' => wp_create_nonce('tbo_hotels_nonce')
);

// Set timeout for the script
set_time_limit(60);

echo "Making search request...\n";

// Start output buffering to capture any output
ob_start();

try {
    // Call the AJAX handler directly
    tbo_hotels_ajax_search_hotels();
    $response = ob_get_contents();
} catch (Exception $e) {
    $response = "Exception: " . $e->getMessage();
} finally {
    ob_end_clean();
}

echo "Response received:\n";
echo substr($response, 0, 500) . (strlen($response) > 500 ? "...[truncated]" : "") . "\n";
echo "Response length: " . strlen($response) . " characters\n";

// Check if response is JSON
$json_data = json_decode($response, true);
if ($json_data) {
    echo "JSON parsed successfully\n";
    if (isset($json_data['success'])) {
        echo "AJAX Success: " . ($json_data['success'] ? 'true' : 'false') . "\n";
        if ($json_data['success'] && isset($json_data['data']['TotalHotels'])) {
            echo "Hotels found: " . $json_data['data']['TotalHotels'] . "\n";
        }
    }
} else {
    echo "Response is not valid JSON\n";
}

echo "Test completed!\n";