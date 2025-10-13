<?php
// Test different TBO API endpoints to find hotel details
require_once 'wp-load.php';

echo "Testing Different TBO API Endpoints for Hotel Details\n";
echo "======================================================\n\n";

$test_hotel_code = '1000000'; // We know this hotel exists from search results

// Test 1: HotelDetails endpoint
echo "Test 1: HotelDetails endpoint\n";
echo "-----------------------------\n";
$data = array('HotelCode' => $test_hotel_code);
$response = tbo_hotels_api_request('HotelDetails', $data, 'GET');

if (is_wp_error($response)) {
    echo "ERROR: " . $response->get_error_message() . "\n\n";
} else {
    echo "SUCCESS: Got response\n";
    echo "Keys: " . implode(', ', array_keys($response)) . "\n\n";
}

// Test 2: HotelInfo endpoint  
echo "Test 2: HotelInfo endpoint\n";
echo "--------------------------\n";
$response2 = tbo_hotels_api_request('HotelInfo', $data, 'GET');

if (is_wp_error($response2)) {
    echo "ERROR: " . $response2->get_error_message() . "\n\n";
} else {
    echo "SUCCESS: Got response\n";
    echo "Keys: " . implode(', ', array_keys($response2)) . "\n\n";
}

// Test 3: Check what endpoints are available
echo "Test 3: Available API endpoints\n";
echo "-------------------------------\n";

$common_endpoints = array(
    'HotelDetails',
    'HotelInfo', 
    'HotelDescription',
    'GetHotelDetails',
    'HotelByCode',
    'HotelData'
);

foreach ($common_endpoints as $endpoint) {
    echo "Testing $endpoint... ";
    $test_response = tbo_hotels_api_request($endpoint, $data, 'GET');
    
    if (is_wp_error($test_response)) {
        echo "FAILED (" . $test_response->get_error_message() . ")\n";
    } else {
        echo "SUCCESS - Keys: " . implode(', ', array_keys($test_response)) . "\n";
        
        // If successful, show some detail
        if (isset($test_response['HotelName']) || isset($test_response['Name'])) {
            echo "  → Found hotel name!\n";
        }
    }
}
?>