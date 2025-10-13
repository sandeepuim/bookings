<?php
// Simple test of TBOHotelCodeList API
require_once 'wp-load.php';

echo "Simple TBOHotelCodeList API Test\n";
echo "===============================\n\n";

$city_code = '130443'; // Delhi
echo "Testing City Code: $city_code\n\n";

$data = array(
    'CityCode' => $city_code,
    'IsDetailedResponse' => 'true'
);

echo "Making API request to TBOHotelCodeList...\n";
$response = tbo_hotels_api_request('TBOHotelCodeList', $data, 'POST');

if (is_wp_error($response)) {
    echo "ERROR: " . $response->get_error_message() . "\n";
} else {
    echo "SUCCESS: Got API response\n";
    echo "Response keys: " . implode(', ', array_keys($response)) . "\n\n";
    
    if (isset($response['Hotels'])) {
        echo "Hotels found: " . count($response['Hotels']) . "\n\n";
        
        if (count($response['Hotels']) > 0) {
            $hotel = $response['Hotels'][0];
            echo "First Hotel Details:\n";
            echo "==================\n";
            echo "Hotel Code: " . ($hotel['HotelCode'] ?? 'N/A') . "\n";
            echo "Hotel Name: " . ($hotel['HotelName'] ?? 'N/A') . "\n";
            echo "Address: " . substr($hotel['Address'] ?? '', 0, 100) . "...\n";
            echo "Rating: " . ($hotel['HotelRating'] ?? 'N/A') . "\n";
            echo "City: " . ($hotel['CityName'] ?? 'N/A') . "\n";
            echo "Country: " . ($hotel['CountryName'] ?? 'N/A') . "\n";
            echo "Facilities: " . count($hotel['HotelFacilities'] ?? []) . " items\n";
            echo "Images: " . count($hotel['ImageUrls'] ?? []) . " photos\n";
        }
    } else {
        echo "No 'Hotels' key in response\n";
        echo "Full response structure:\n";
        echo print_r($response, true);
    }
}
?>