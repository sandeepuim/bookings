<?php
/*
Template Name: Hotel Details
*/
// Get parameters from URL
 
$hotel_code   = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$country_code = isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : '';
$city_code    = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in     = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out    = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$rooms        = isset($_GET['rooms']) ? intval($_GET['rooms']) : 0;
$adults       = isset($_GET['adults']) ? intval($_GET['adults']) : 0;
$children     = isset($_GET['children']) ? intval($_GET['children']) : 0;

echo "Hotel Code: " . $hotel_code;
 

$hotel_code = $_GET['hotel_code'] ?? '';
$country_code = $_GET['country_code'] ?? '';
$city_code = $_GET['city_code'] ?? '';
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$rooms = $_GET['rooms'] ?? 1;
$adults = $_GET['adults'] ?? 1;
$children = $_GET['children'] ?? 0;
echo '<pre>';
print_r($_GET);
// Prepare API request (example for hotel details)
$api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/HotelCodeList";
$payload = json_encode([
    "CityCode" => $city_code,
    "HotelCodes" => $hotel_code,
    "CheckIn" => $check_in,
    "CheckOut" => $check_out,
    "PaxRooms" => [["Adults" => $adults, "Children" => $children]],
    "ResponseTime" => 20,
    "IsDetailedResponse" => true,
    "Currency" => "INR"
]);

// Basic Auth credentials
$username = "YOLANDATHTest";
$password = "Yol@40360746";
$auth = base64_encode("$username:$password");

// Make API call
$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Basic $auth"
]);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// Display hotel and room details
if (!empty($data['HotelDetails'])) {
    $hotel = $data['HotelDetails'][0];
    echo "<h1>{$hotel['HotelName']}</h1>";
    echo "<p>{$hotel['Address']}</p>";
    // ...display images, rating, reviews, etc...

    // Display room types
    if (!empty($hotel['Rooms'])) {
        foreach ($hotel['Rooms'] as $room) {
            echo "<div class='room'>";
            echo "<h2>{$room['RoomTypeName']}</h2>";
            echo "<p>Price: ₹{$room['Price']}</p>";
            // ...other room details...
            echo "<a href='#' class='book-room-btn'>Book this room</a>";
            echo "</div>";
        }
    }
} else {
    echo "<p>No details found for this hotel.</p>";
}
?>dsdasdasd