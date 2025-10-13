<?php
// Debug HotelCodeList API response structure
require_once 'wp-load.php';

echo "Debugging HotelCodeList API Response\n";
echo "====================================\n\n";

$city_code = '150184'; // Dubai
echo "City Code: $city_code (Dubai)\n\n";

$data = array('CityCode' => $city_code);
echo "Making API request to HotelCodeList...\n";
$response = tbo_hotels_api_request('HotelCodeList', $data, 'GET');

if (is_wp_error($response)) {
    echo "ERROR: " . $response->get_error_message() . "\n";
} else {
    echo "SUCCESS: Got API response\n\n";
    
    echo "Response keys: " . implode(', ', array_keys($response)) . "\n\n";
    
    if (isset($response['HotelCodes'])) {
        echo "HotelCodes found: " . count($response['HotelCodes']) . " items\n\n";
        
        if (count($response['HotelCodes']) > 0) {
            echo "Structure of first 5 hotels:\n";
            echo "============================\n";
            
            for ($i = 0; $i < min(5, count($response['HotelCodes'])); $i++) {
                $hotel = $response['HotelCodes'][$i];
                echo "Hotel $i: ";
                
                if (is_array($hotel)) {
                    echo "[Array] Keys: " . implode(', ', array_keys($hotel)) . "\n";
                } else {
                    echo "[" . gettype($hotel) . "] Value: " . $hotel . "\n";
                }
            }
        }
    } else {
        echo "No HotelCodes key found in response\n";
        echo "Full response: " . print_r($response, true) . "\n";
    }
}
?>