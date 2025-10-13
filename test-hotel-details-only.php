<?php
// Simple test for hotel details function
require_once 'wp-load.php';

echo "Testing Hotel Details Function\n";
echo "===============================\n\n";

$city_code = '150184'; // Dubai
echo "City Code: $city_code (Dubai)\n\n";

echo "Fetching hotel details...\n";
$hotel_details = tbo_hotels_get_hotel_details($city_code);

if (is_wp_error($hotel_details)) {
    echo "ERROR: " . $hotel_details->get_error_message() . "\n";
} else {
    echo "SUCCESS: Found " . count($hotel_details) . " hotel details\n\n";
    
    if (count($hotel_details) > 0) {
        echo "Sample hotel details (first 3):\n";
        echo "================================\n";
        
        $count = 0;
        foreach ($hotel_details as $hotelCode => $details) {
            if ($count >= 3) break;
            
            echo "Hotel Code: $hotelCode\n";
            echo "Name: " . $details['HotelName'] . "\n";
            echo "Address: " . $details['HotelAddress'] . "\n";
            echo "Rating: " . $details['StarRating'] . " stars\n";
            echo "---\n";
            
            $count++;
        }
    }
}
?>