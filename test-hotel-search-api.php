<?php
// Hotel Search API Test - Focuses specifically on the search functionality

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h1>Hotel Search API Test</h1>";

// Use known working hotel codes for testing
$test_hotel_codes = [
    '1000000', '1000001', '1000002', '1000003', '1000004',
    '1000005', '1000006', '1000007', '1000008', '1000009'
];

echo "<h2>Test 1: Search with Fixed Hotel Codes</h2>";

// Create search parameters
$search_params = [
    'check_in' => '2025-10-01',
    'check_out' => '2025-10-03',
    'adults' => 2,
    'rooms' => 1,
    'children' => 0,
    'hotel_code' => implode(',', $test_hotel_codes) // Pass multiple codes
];

echo "<p>Search Parameters:</p>";
echo "<pre>" . print_r($search_params, true) . "</pre>";

// Define a direct search function that bypasses the standard function
function direct_hotel_search($hotel_codes, $check_in, $check_out) {
    // Make direct API call to search hotels
    $search_data = [
        'CheckIn' => $check_in,
        'CheckOut' => $check_out,
        'HotelCodes' => $hotel_codes, // Pass as array, not string
        'GuestNationality' => 'IN',
        'PaxRooms' => [
            [
                'Adults' => 2,
                'Children' => 0,
                'ChildrenAges' => []
            ]
        ]
    ];
    
    // Log request
    error_log('Direct hotel search request: ' . json_encode($search_data));
    
    // Make API request
    $response = tbo_hotels_api_request('HotelSearch', $search_data, 'POST');
    
    return $response;
}

// Test with array of hotel codes
$result1 = direct_hotel_search($test_hotel_codes, '2025-10-01', '2025-10-03');

if (is_wp_error($result1)) {
    echo "<p style='color: red;'>Error: " . $result1->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>Search completed successfully</p>";
    
    if (isset($result1['Hotels']) && is_array($result1['Hotels'])) {
        echo "<p>Found " . count($result1['Hotels']) . " hotels</p>";
        
        if (count($result1['Hotels']) > 0) {
            echo "<h3>First Hotel:</h3>";
            echo "<pre>" . print_r($result1['Hotels'][0], true) . "</pre>";
        }
    } else {
        echo "<p>No hotels found in response</p>";
        echo "<p>Response structure:</p>";
        echo "<pre>" . print_r(array_keys($result1), true) . "</pre>";
    }
}

// Test 2: Search with a city code
echo "<h2>Test 2: Search with City Code</h2>";

$city_code = '100589'; // Delhi
echo "<p>Using city code: " . $city_code . "</p>";

// Get hotel codes for this city
$hotel_codes = tbo_hotels_get_hotel_codes($city_code);

if (is_wp_error($hotel_codes)) {
    echo "<p style='color: red;'>Error getting hotel codes: " . $hotel_codes->get_error_message() . "</p>";
} else {
    echo "<p>Found " . count($hotel_codes) . " hotel codes for this city</p>";
    
    // Use just a few codes for testing
    $limited_codes = array_slice($hotel_codes, 0, 5);
    echo "<p>Using first 5 hotel codes:</p>";
    echo "<pre>" . print_r($limited_codes, true) . "</pre>";
    
    // Perform search
    $result2 = direct_hotel_search($limited_codes, '2025-10-01', '2025-10-03');
    
    if (is_wp_error($result2)) {
        echo "<p style='color: red;'>Error: " . $result2->get_error_message() . "</p>";
    } else {
        echo "<p style='color: green;'>Search completed successfully</p>";
        
        if (isset($result2['Hotels']) && is_array($result2['Hotels'])) {
            echo "<p>Found " . count($result2['Hotels']) . " hotels</p>";
            
            if (count($result2['Hotels']) > 0) {
                echo "<h3>First Hotel:</h3>";
                echo "<pre>" . print_r($result2['Hotels'][0], true) . "</pre>";
            }
        } else {
            echo "<p>No hotels found in response</p>";
            echo "<p>Response structure:</p>";
            echo "<pre>" . print_r(array_keys($result2), true) . "</pre>";
        }
    }
}

// Test 3: Using the standard function
echo "<h2>Test 3: Using Standard Search Function</h2>";

$search_params = [
    'city_code' => $city_code,
    'check_in' => '2025-10-01',
    'check_out' => '2025-10-03',
    'adults' => 2,
    'rooms' => 1,
    'children' => 0,
    'max_codes' => 5 // Limit to 5 for testing
];

echo "<p>Search Parameters:</p>";
echo "<pre>" . print_r($search_params, true) . "</pre>";

$result3 = tbo_hotels_search_hotels($search_params);

if (is_wp_error($result3)) {
    echo "<p style='color: red;'>Error: " . $result3->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>Search completed successfully</p>";
    
    if (isset($result3['Hotels']) && is_array($result3['Hotels'])) {
        echo "<p>Found " . count($result3['Hotels']) . " hotels</p>";
        
        if (count($result3['Hotels']) > 0) {
            echo "<h3>First Hotel:</h3>";
            echo "<pre>" . print_r($result3['Hotels'][0], true) . "</pre>";
        }
    } else {
        echo "<p>No hotels found in response or unexpected format</p>";
        echo "<p>Response structure:</p>";
        echo "<pre>" . print_r(array_keys($result3), true) . "</pre>";
    }
}

// Provide link to full diagnostic
echo "<p><a href='tbo-api-diagnostic.php'>Run full diagnostic test</a></p>";
?>