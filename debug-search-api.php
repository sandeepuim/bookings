<?php
// Debug Search API response structure  
require_once 'wp-load.php';

echo "Debugging Search API Response Structure\n";
echo "=======================================\n\n";

// Use actual hotel codes from the HotelCodeList API
echo "First, getting actual hotel codes from HotelCodeList...\n";
$hotel_codes = tbo_hotels_get_hotel_codes('150184'); // Dubai

if (is_wp_error($hotel_codes)) {
    echo "ERROR getting hotel codes: " . $hotel_codes->get_error_message() . "\n";
    exit;
}

echo "Got " . count($hotel_codes) . " hotel codes\n";
$test_hotel_codes = array_slice($hotel_codes, 0, 3); // Use first 3
echo "Using first 3 hotel codes: " . implode(', ', $test_hotel_codes) . "\n\n";

$search_data = array(
    'CheckIn' => date('Y-m-d', strtotime('+7 days')),
    'CheckOut' => date('Y-m-d', strtotime('+9 days')),
    'HotelCodes' => implode(',', $test_hotel_codes),
    'GuestNationality' => 'IN',
    'PaxRooms' => array(
        array(
            'Adults' => 2,
            'Children' => 0,
            'ChildrenAges' => array()
        )
    ),
    'ResponseTime' => 25,
    'IsDetailedResponse' => true
);

echo "Making Search API request with 3 test hotel codes...\n";
echo "Hotel codes: " . implode(', ', $test_hotel_codes) . "\n\n";

$response = tbo_hotels_api_request('Search', $search_data, 'POST');

if (is_wp_error($response)) {
    echo "ERROR: " . $response->get_error_message() . "\n";
} else {
    echo "SUCCESS: Got Search API response\n\n";
    
    echo "Response keys: " . implode(', ', array_keys($response)) . "\n\n";
    
    if (isset($response['HotelResult']) && is_array($response['HotelResult'])) {
        echo "HotelResult found: " . count($response['HotelResult']) . " hotels\n\n";
        
        if (count($response['HotelResult']) > 0) {
            echo "Structure of first hotel:\n";
            echo "=========================\n";
            $firstHotel = $response['HotelResult'][0];
            
            foreach ($firstHotel as $key => $value) {
                if (is_array($value)) {
                    echo "$key: [Array with " . count($value) . " items]\n";
                } else {
                    echo "$key: " . substr(strval($value), 0, 100) . "\n";
                }
            }
            
            echo "\n\nFull hotel data (first hotel):\n";
            echo "==============================\n";
            echo json_encode($firstHotel, JSON_PRETTY_PRINT);
        }
    } else {
        echo "No HotelResult found in response\n";
        echo "Full response: " . print_r($response, true) . "\n";
    }
}
?>