<?php
/**
 * City Dropdown Test Script
 * 
 * Tests the city dropdown functionality directly by making an API request
 * for city data without going through WordPress AJAX.
 */

// Load WordPress core
require_once('../../../../wp-load.php');

// Set the content type to JSON
header('Content-Type: application/json');

// Get the country code from the request
$country_code = isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : 'IN';

// Test mode - to force using either API or fallback
$mode = isset($_GET['mode']) ? sanitize_text_field($_GET['mode']) : 'auto';

// Define a function to get fallback cities if needed
function get_test_fallback_cities($country_code) {
    $fallback_cities = array();
    
    if ($country_code === 'IN') {
        $fallback_cities = array(
            array('Code' => '150184', 'Name' => 'Mumbai'),
            array('Code' => '150489', 'Name' => 'New Delhi'),
            array('Code' => '150089', 'Name' => 'Bangalore'),
            array('Code' => '151145', 'Name' => 'Kolkata'),
            array('Code' => '150787', 'Name' => 'Chennai'),
            array('Code' => '150186', 'Name' => 'Goa')
        );
    } else if ($country_code === 'US') {
        $fallback_cities = array(
            array('Code' => '150642', 'Name' => 'New York'),
            array('Code' => '150157', 'Name' => 'Los Angeles'),
            array('Code' => '150201', 'Name' => 'Chicago'),
            array('Code' => '150152', 'Name' => 'Miami'),
            array('Code' => '150161', 'Name' => 'Las Vegas')
        );
    }
    
    return $fallback_cities;
}

// Get cities based on the requested mode
$result = array();

if ($mode === 'fallback') {
    // Force use of fallback
    $cities = get_test_fallback_cities($country_code);
    $result = array(
        'success' => true,
        'data' => $cities,
        'mode' => 'fallback',
        'source' => 'forced_fallback'
    );
} else if ($mode === 'api') {
    // Force use of API
    $cities = tbo_hotels_get_cities($country_code);
    
    if (is_wp_error($cities)) {
        $result = array(
            'success' => false,
            'error' => $cities->get_error_message(),
            'error_code' => $cities->get_error_code(),
            'mode' => 'api',
            'source' => 'forced_api'
        );
    } else {
        $result = array(
            'success' => true,
            'data' => $cities,
            'mode' => 'api',
            'source' => 'forced_api'
        );
    }
} else {
    // Auto mode - try API first, then fallback
    $cities = tbo_hotels_get_cities($country_code);
    
    if (is_wp_error($cities)) {
        // API failed, use fallback
        $fallback_cities = get_test_fallback_cities($country_code);
        
        $result = array(
            'success' => true,
            'data' => $fallback_cities,
            'mode' => 'fallback',
            'source' => 'auto_fallback',
            'api_error' => $cities->get_error_message(),
            'api_error_code' => $cities->get_error_code()
        );
    } else {
        // API succeeded
        $result = array(
            'success' => true,
            'data' => $cities,
            'mode' => 'api',
            'source' => 'auto_api'
        );
    }
}

// Add diagnostic information
$result['diagnostic'] = array(
    'timestamp' => current_time('mysql'),
    'country_code' => $country_code,
    'php_version' => phpversion(),
    'wp_version' => get_bloginfo('version')
);

// Output the result
echo json_encode($result);
exit;