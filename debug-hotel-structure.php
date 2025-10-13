<?php
// Quick debug script to check hotel data structure
require_once 'wp-config.php';
require_once 'wp-content/themes/tbo-hotels/functions.php';

// Test search with the same parameters as the working search
$params = array(
    'city_code' => '150184', // Dubai
    'check_in' => date('Y-m-d', strtotime('+7 days')), // 7 days from now
    'check_out' => date('Y-m-d', strtotime('+9 days')), // 9 days from now
    'adults' => 2,
    'rooms' => 1,
    'children' => 0
);

echo "Testing hotel search with Dubai city code 150184...\n\n";

// Let's check the raw API response first
$search_data = array(
    'CheckIn' => $params['check_in'],
    'CheckOut' => $params['check_out'],
    'HotelCodes' => tbo_hotels_get_hotel_codes($params['city_code']),
    'GuestNationality' => 'AE',
    'PaxRooms' => array()
);

$room = array(
    'Adults' => intval($params['adults']),
    'Children' => intval($params['children']),
    'ChildrenAges' => array()
);

if (intval($params['children']) > 0) {
    $room['ChildrenAges'] = array_fill(0, intval($params['children']), 5);
}
$search_data['PaxRooms'][] = $room;

echo "Making direct API call...\n";
$raw_response = tbo_hotels_api_request('Search', $search_data, 'POST');

if (is_wp_error($raw_response)) {
    echo "API Error: " . $raw_response->get_error_message() . "\n";
} else {
    echo "Raw API Response Status: " . print_r($raw_response['Status'], true) . "\n";
    echo "Response keys: " . implode(', ', array_keys($raw_response)) . "\n\n";
    
    if (isset($raw_response['HotelResult']) && is_array($raw_response['HotelResult'])) {
        echo "HotelResult found with " . count($raw_response['HotelResult']) . " hotels\n\n";
        
        if (count($raw_response['HotelResult']) > 0) {
            $firstHotel = $raw_response['HotelResult'][0];
            echo "First hotel keys: " . implode(', ', array_keys($firstHotel)) . "\n\n";
            
            // Check hotel name field
            if (isset($firstHotel['HotelName'])) {
                echo "Hotel Name: " . $firstHotel['HotelName'] . "\n";
            } else {
                echo "HotelName field not found!\n";
                echo "Checking for other name fields...\n";
                foreach ($firstHotel as $key => $value) {
                    if (stripos($key, 'name') !== false) {
                        echo "Found name field '{$key}': " . $value . "\n";
                    }
                }
            }
            
            echo "\nFirst 10 fields of first hotel:\n";
            $count = 0;
            foreach ($firstHotel as $key => $value) {
                if ($count >= 10) break;
                if (is_array($value)) {
                    echo "{$key}: [Array with " . count($value) . " items]\n";
                } else {
                    echo "{$key}: " . substr(strval($value), 0, 100) . "\n";
                }
                $count++;
            }
        }
    } else {
        echo "No HotelResult in response or not an array\n";
        if (isset($raw_response['Error'])) {
            echo "API Error: " . print_r($raw_response['Error'], true) . "\n";
        }
    }
}
?>