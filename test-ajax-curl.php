<?php
// Test script for simulating the exact cURL command

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing the Hotel Search AJAX Request</h1>";

// Define the data from the cURL command
$data = [
    'country_code' => 'IN',
    'city_code' => '100589',
    'check_in' => '2025-09-30',
    'check_out' => '2025-10-01',
    'rooms' => 1,
    'adults' => 2,
    'children' => 0,
    'action' => 'tbo_hotels_search_hotels',
    'nonce' => wp_create_nonce('tbo_hotels_nonce')
];

echo "<h2>Request Data</h2>";
echo "<pre>" . print_r($data, true) . "</pre>";

// Load WordPress
require_once('wp-load.php');

// Manually call the function with the same parameters
echo "<h2>Direct API Call Results</h2>";

// Create search parameters
$search_params = [
    'city_code' => $data['city_code'],
    'check_in' => $data['check_in'],
    'check_out' => $data['check_out'],
    'adults' => intval($data['adults']),
    'rooms' => intval($data['rooms']),
    'children' => intval($data['children']),
    'max_codes' => 10, // Limit to 10 for testing
    'refresh_codes' => true // Force refresh of cached codes
];

echo "<p>Search Parameters:</p>";
echo "<pre>" . print_r($search_params, true) . "</pre>";

// Clear search cache
$cache_key = 'tbo_hotels_search_' . md5(serialize($search_params));
delete_transient($cache_key);

// Get hotel codes first
echo "<h3>Step 1: Getting Hotel Codes for City " . $data['city_code'] . "</h3>";
delete_transient('tbo_hotels_codes_' . $data['city_code']);
$hotel_codes = tbo_hotels_get_hotel_codes($data['city_code']);

if (is_wp_error($hotel_codes)) {
    echo "<p style='color: red;'>Error getting hotel codes: " . $hotel_codes->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>Success! Found " . count($hotel_codes) . " hotel codes</p>";
    echo "<p>Sample codes: " . json_encode(array_slice($hotel_codes, 0, 5)) . "</p>";
    
    // Perform search
    echo "<h3>Step 2: Performing Hotel Search</h3>";
    $search_results = tbo_hotels_search_hotels($search_params);
    
    if (is_wp_error($search_results)) {
        echo "<p style='color: red;'>Search Error: " . $search_results->get_error_message() . "</p>";
    } else {
        echo "<p style='color: green;'>Search Success!</p>";
        
        if (isset($search_results['Hotels']) && is_array($search_results['Hotels'])) {
            echo "<p>Found " . count($search_results['Hotels']) . " hotels</p>";
            
            echo "<h4>First Hotel:</h4>";
            if (!empty($search_results['Hotels'])) {
                echo "<pre>" . print_r($search_results['Hotels'][0], true) . "</pre>";
            } else {
                echo "<p>No hotels in results array</p>";
            }
            
            echo "<h4>Meta Information:</h4>";
            if (isset($search_results['_meta'])) {
                echo "<pre>" . print_r($search_results['_meta'], true) . "</pre>";
            }
        } else {
            echo "<p>No hotels found or unexpected format</p>";
            echo "<p>Response keys: " . implode(', ', array_keys($search_results)) . "</p>";
        }
    }
}

// Now simulate the AJAX request
echo "<h2>Simulated AJAX Request</h2>";

// Set up the POST data
$_POST = $data;

// Buffer output to capture the JSON response
ob_start();
try {
    do_action('wp_ajax_tbo_hotels_search_hotels');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();

echo "<p>AJAX Response (first 1000 chars):</p>";
echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";

// Try to decode the JSON response
$json_response = json_decode($output, true);
if ($json_response) {
    echo "<h3>Decoded JSON Response:</h3>";
    
    if ($json_response['success']) {
        echo "<p style='color: green;'>Success! Response contains data</p>";
        
        if (isset($json_response['data']['Hotels'])) {
            echo "<p>Found " . count($json_response['data']['Hotels']) . " hotels</p>";
            
            if (!empty($json_response['data']['Hotels'])) {
                echo "<h4>First Hotel:</h4>";
                echo "<pre>" . print_r($json_response['data']['Hotels'][0], true) . "</pre>";
            }
        } else {
            echo "<p>No hotels found in AJAX response data</p>";
        }
    } else {
        echo "<p style='color: red;'>Error: " . ($json_response['data'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'>Failed to decode JSON response</p>";
}
?>