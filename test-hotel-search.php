<?php
// Test specifically for hotel codes retrieval and hotel search

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h1>Hotel Codes and Search Test</h1>";

// Test city code for Delhi
$city_code = '100589';
echo "<h2>Testing Hotel Codes for City: " . $city_code . "</h2>";

// Clear the cache to get fresh data
delete_transient('tbo_hotels_codes_' . $city_code);

// Get hotel codes
echo "<p>Retrieving hotel codes...</p>";
$hotel_codes = tbo_hotels_get_hotel_codes($city_code);

if (is_wp_error($hotel_codes)) {
    echo "<p style='color: red;'>Error: " . $hotel_codes->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>Success! Found " . count($hotel_codes) . " hotel codes</p>";
    
    echo "<h3>First 20 Hotel Codes:</h3>";
    echo "<pre>" . print_r(array_slice($hotel_codes, 0, 20), true) . "</pre>";
    
    // Now try a hotel search with these codes
    echo "<h2>Testing Hotel Search</h2>";
    
    $search_params = [
        'city_code' => $city_code,
        'check_in' => '2025-10-01',
        'check_out' => '2025-10-03',
        'adults' => 2,
        'rooms' => 1,
        'children' => 0,
        'max_codes' => 10,  // Limit to 10 for testing
        'refresh_codes' => false  // Use cached codes
    ];
    
    echo "<p>Performing hotel search with these parameters:</p>";
    echo "<pre>" . print_r($search_params, true) . "</pre>";
    
    // Clear search cache
    $cache_key = 'tbo_hotels_search_' . md5(serialize($search_params));
    delete_transient($cache_key);
    
    // Perform search
    $search_results = tbo_hotels_search_hotels($search_params);
    
    if (is_wp_error($search_results)) {
        echo "<p style='color: red;'>Search Error: " . $search_results->get_error_message() . "</p>";
    } else {
        echo "<p style='color: green;'>Search Success!</p>";
        
        if (isset($search_results['Hotels']) && is_array($search_results['Hotels'])) {
            echo "<p>Found " . count($search_results['Hotels']) . " hotels</p>";
            
            echo "<h3>First 3 Hotels:</h3>";
            $hotels = array_slice($search_results['Hotels'], 0, 3);
            foreach ($hotels as $index => $hotel) {
                echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
                echo "<h4>" . ($index + 1) . ". " . ($hotel['HotelName'] ?? 'Unknown') . "</h4>";
                echo "<p><strong>Hotel Code:</strong> " . ($hotel['HotelCode'] ?? 'N/A') . "</p>";
                echo "<p><strong>Address:</strong> " . ($hotel['HotelAddress'] ?? 'N/A') . "</p>";
                echo "<p><strong>Rating:</strong> " . ($hotel['StarRating'] ?? 'N/A') . " stars</p>";
                echo "<p><strong>Price:</strong> " . ($hotel['MinHotelPrice'] ?? 'N/A') . " " . 
                     ($hotel['Price']['CurrencyCode'] ?? 'USD') . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No hotels found or unexpected format</p>";
            echo "<pre>" . print_r(array_keys($search_results), true) . "</pre>";
        }
    }
}

// Test with another city
$city_code = '111647'; // Mumbai
echo "<h2>Testing Hotel Codes for City: " . $city_code . " (Mumbai)</h2>";

// Clear the cache
delete_transient('tbo_hotels_codes_' . $city_code);

// Get hotel codes
$hotel_codes = tbo_hotels_get_hotel_codes($city_code);

if (is_wp_error($hotel_codes)) {
    echo "<p style='color: red;'>Error: " . $hotel_codes->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>Success! Found " . count($hotel_codes) . " hotel codes</p>";
    
    echo "<h3>First 10 Hotel Codes:</h3>";
    echo "<pre>" . print_r(array_slice($hotel_codes, 0, 10), true) . "</pre>";
}

// Test direct AJAX call simulation
echo "<h2>Testing AJAX Hotel Search</h2>";

// Simulate AJAX parameters
$_POST = [
    'action' => 'tbo_hotels_search_hotels',
    'nonce' => wp_create_nonce('tbo_hotels_nonce'),
    'country_code' => 'IN',
    'city_code' => '100589',
    'check_in' => '2025-10-01',
    'check_out' => '2025-10-03',
    'rooms' => 1,
    'adults' => 2,
    'children' => 0
];

echo "<p>Simulating AJAX request with these parameters:</p>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Buffer output to capture JSON response
ob_start();
try {
    do_action('wp_ajax_tbo_hotels_search_hotels');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();

echo "<p>AJAX Response (first 1000 chars):</p>";
echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";

echo "<p><a href='tbo-api-diagnostic.php'>Run full diagnostic test</a></p>";
?>