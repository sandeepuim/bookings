<?php
// Final TBO Hotels Integration Test
// This script simulates the exact AJAX request from the curl command

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h1>TBO Hotels Search Test - Final Check</h1>";

// Set up the exact data from the CURL command
$_POST = [
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

echo "<h2>Test Parameters</h2>";
echo "<pre>" . print_r($_POST, true) . "</pre>";

// Clear any cached data
delete_transient('tbo_hotels_codes_' . $_POST['city_code']);
$cache_key = 'tbo_hotels_search_' . md5(serialize([
    'city_code' => $_POST['city_code'],
    'check_in' => $_POST['check_in'],
    'check_out' => $_POST['check_out'],
    'adults' => intval($_POST['adults']),
    'rooms' => intval($_POST['rooms']),
    'children' => intval($_POST['children']),
]));
delete_transient($cache_key);

echo "<h2>Simulating AJAX Request</h2>";

// Buffer output to capture the JSON
ob_start();
try {
    // This will call tbo_hotels_ajax_search_hotels()
    do_action('wp_ajax_tbo_hotels_search_hotels');
} catch (Exception $e) {
    echo "Exception occurred: " . $e->getMessage();
}
$output = ob_get_clean();

// Parse the JSON response
$response = json_decode($output, true);

if ($response === null) {
    echo "<p style='color: red;'>Error parsing JSON response</p>";
    echo "<pre>" . htmlspecialchars(substr($output, 0, 1000)) . "...</pre>";
} else {
    if ($response['success']) {
        echo "<p style='color: green;'>✅ Search completed successfully!</p>";
        
        if (isset($response['data']['Hotels'])) {
            $hotels = $response['data']['Hotels'];
            echo "<p>Found " . count($hotels) . " hotels</p>";
            
            if (count($hotels) > 0) {
                echo "<h3>First Few Hotels:</h3>";
                
                foreach (array_slice($hotels, 0, 3) as $index => $hotel) {
                    echo "<div style='margin-bottom: 20px; padding: 10px; border: 1px solid #ccc;'>";
                    echo "<h4>" . ($index + 1) . ". " . ($hotel['HotelName'] ?? 'Unknown') . "</h4>";
                    echo "<p><strong>Hotel Code:</strong> " . ($hotel['HotelCode'] ?? 'N/A') . "</p>";
                    echo "<p><strong>Address:</strong> " . ($hotel['HotelAddress'] ?? 'N/A') . "</p>";
                    echo "<p><strong>Rating:</strong> " . ($hotel['StarRating'] ?? 'N/A') . " stars</p>";
                    
                    // Show price if available
                    if (isset($hotel['MinHotelPrice'])) {
                        echo "<p><strong>Price:</strong> " . $hotel['MinHotelPrice'] . " " . 
                             ($hotel['Price']['CurrencyCode'] ?? 'USD') . "</p>";
                    }
                    
                    echo "</div>";
                }
                
                echo "<h3>Hotels Found - Log Output</h3>";
                echo "<p>The following hotel codes should now appear in your log:</p>";
                echo "<ul>";
                foreach (array_slice($hotels, 0, 10) as $hotel) {
                    echo "<li>" . ($hotel['HotelCode'] ?? 'Unknown') . " - " . ($hotel['HotelName'] ?? 'N/A') . "</li>";
                }
                echo "</ul>";
            } else {
                echo "<p>No hotels found in the response</p>";
            }
        } else {
            echo "<p>No hotels data in response</p>";
            echo "<pre>" . print_r($response['data'], true) . "</pre>";
        }
    } else {
        echo "<p style='color: red;'>❌ Error: " . ($response['data'] ?? 'Unknown error') . "</p>";
    }
}

echo "<h3>Next Steps</h3>";
echo "<ol>";
echo "<li>Check your PHP error log to verify hotel codes are being logged</li>";
echo "<li>Try the actual hotel search form on your site</li>";
echo "<li>If you still have issues, run the <a href='tbo-api-diagnostic.php'>full diagnostic test</a></li>";
echo "</ol>";
?>