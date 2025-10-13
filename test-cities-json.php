<?php
// This file simulates the WordPress AJAX response for cities
require_once('wp-load.php');

// Set content type to JSON
header('Content-Type: application/json');

// Check if country code is provided
if (empty($_POST['country_code'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Country code is required'
    ]);
    exit;
}

$country_code = sanitize_text_field($_POST['country_code']);

// Clear cache for testing
delete_transient('tbo_hotels_cities_' . $country_code);

// Get cities from the theme function
$cities = tbo_hotels_get_cities($country_code);

if (is_wp_error($cities)) {
    echo json_encode([
        'success' => false,
        'message' => $cities->get_error_message(),
        'code' => $cities->get_error_code()
    ]);
} else {
    echo json_encode([
        'success' => true,
        'data' => $cities
    ]);
}
?>