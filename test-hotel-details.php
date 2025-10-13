<?php
// Simple test to check if hotel details work
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Testing Hotel Details</h1>";

require_once 'wp-content/themes/Yolandabooking/inc/TboApiClient.php';

echo "<h2>Creating TBO API Client...</h2>";

$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI',
    'YOLANDATHTest',
    'Yol@40360746'
);

echo "<h2>Getting hotel details for HTL00004...</h2>";

try {
    $hotelData = $tbo->getHotelWithRooms(
        'HTL00004',
        '2025-09-28',
        '2025-09-29',
        1,
        0,
        'IN'
    );
    
    echo "<h3>Hotel Data Structure:</h3>";
    echo "<pre>" . print_r($hotelData, true) . "</pre>";
    
    if (isset($hotelData['HotelDetails'])) {
        echo "<h3>Hotel Details Found!</h3>";
        echo "<p>Hotel Name: " . $hotelData['HotelDetails']['HotelName'] . "</p>";
        echo "<p>Hotel Address: " . $hotelData['HotelDetails']['HotelAddress'] . "</p>";
        echo "<p>Star Rating: " . $hotelData['HotelDetails']['StarRating'] . "</p>";
        
        if (isset($hotelData['Rooms'])) {
            echo "<h3>Available Rooms (" . count($hotelData['Rooms']) . "):</h3>";
            foreach ($hotelData['Rooms'] as $index => $room) {
                echo "<p>" . ($index + 1) . ". " . $room['RoomTypeName'] . " - ₹" . $room['TotalFare'] . "</p>";
            }
        }
    } else {
        echo "<h3>ERROR: HotelDetails not found in response!</h3>";
    }
    
} catch (Exception $e) {
    echo "<h3>Exception occurred:</h3>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Trace: " . $e->getTraceAsString() . "</p>";
}

echo "<h2>Test completed!</h2>";
?>
