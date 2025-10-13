<?php
// Test hotel search after database packet fix
require_once 'wp-config.php';
require_once 'wp-load.php';

echo "Testing TBO Hotel Search API...\n";

// Test parameters
$params = array(
    'city_code' => '418069',
    'check_in' => '2025-09-23',
    'check_out' => '2025-09-24',
    'rooms' => 1,
    'adults' => 2,
    'children' => 0
);

echo "Search parameters:\n";
print_r($params);

// Call the search function directly
$results = tbo_hotels_search_hotels($params);

if (is_wp_error($results)) {
    echo "Error: " . $results->get_error_message() . "\n";
} else {
    echo "Success! Found " . $results['TotalHotels'] . " hotels\n";
    echo "Status: " . print_r($results['Status'], true) . "\n";
    
    // Show first hotel as sample
    if (!empty($results['Hotels'])) {
        $first_hotel = $results['Hotels'][0];
        echo "\nFirst hotel sample:\n";
        echo "Hotel Code: " . $first_hotel['HotelCode'] . "\n";
        echo "Currency: " . $first_hotel['Currency'] . "\n";
        echo "Number of rooms: " . count($first_hotel['Rooms']) . "\n";
        
        if (!empty($first_hotel['Rooms'])) {
            $first_room = $first_hotel['Rooms'][0];
            echo "First room: " . implode(', ', $first_room['Name']) . "\n";
            echo "Total Fare: " . $first_room['TotalFare'] . "\n";
        }
    }
}

echo "\nTest completed!\n";