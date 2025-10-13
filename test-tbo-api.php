<?php
// Simple TBO API Test Script
// Run this directly to test TBO API endpoints

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>TBO API Direct Test</h2>\n";

// Test 1: Static API for Hotel Codes
echo "<h3>Test 1: Hotel Code List API</h3>\n";
$url1 = 'http://api.tbotechnology.in/hotelapi_v10/HotelCodeList';
$data1 = json_encode(['CityCode' => '418069', 'IsDetailedResponse' => true]);
$auth1 = base64_encode('TBOStaticAPITest:Tbo@11530818');

$ch1 = curl_init();
curl_setopt($ch1, CURLOPT_URL, $url1);
curl_setopt($ch1, CURLOPT_POST, 1);
curl_setopt($ch1, CURLOPT_POSTFIELDS, $data1);
curl_setopt($ch1, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth1
]);
curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch1, CURLOPT_TIMEOUT, 30);

$response1 = curl_exec($ch1);
$httpCode1 = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
curl_close($ch1);

echo "HTTP Code: $httpCode1<br>\n";
if ($httpCode1 == 200) {
    $result1 = json_decode($response1, true);
    if ($result1) {
        echo "Success! Found " . (isset($result1['Hotels']) ? count($result1['Hotels']) : 'unknown') . " hotels<br>\n";
        echo "Response keys: " . implode(', ', array_keys($result1)) . "<br>\n";
    } else {
        echo "Failed to decode JSON response<br>\n";
    }
} else {
    echo "API Error: " . substr($response1, 0, 500) . "<br>\n";
}

echo "<hr>\n";

// Test 2: Alternative Static API URL
echo "<h3>Test 2: Alternative Static API URL</h3>\n";
$url2 = 'http://api.tbo.com/hotelapi_v10/HotelCodeList';
echo "Trying alternative URL: $url2<br>\n";

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $url2);
curl_setopt($ch2, CURLOPT_POST, 1);
curl_setopt($ch2, CURLOPT_POSTFIELDS, $data1);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth1
]);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);

$response2 = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "HTTP Code: $httpCode2<br>\n";
if ($httpCode2 == 200) {
    echo "Alternative URL works!<br>\n";
} else {
    echo "Alternative URL also failed<br>\n";
}

echo "<hr>\n";

// Test 3: Search API with Basic Auth (like your Node.js)
echo "<h3>Test 3: Search API with Basic Auth</h3>\n";
$url3 = 'http://api.tbotechnology.in/hotelapi_v10/Search';
$auth3 = base64_encode('travelcategory:Tra@59334536');
$data3 = json_encode([
    'CheckIn' => '2025-09-28',
    'CheckOut' => '2025-09-29',
    'HotelCodes' => '12345,12346',
    'GuestNationality' => 'IN',
    'PaxRooms' => [['Adults' => 1, 'Children' => 0]],
    'ResponseTime' => 15.0,
    'IsDetailedResponse' => true
]);

$ch3 = curl_init();
curl_setopt($ch3, CURLOPT_URL, $url3);
curl_setopt($ch3, CURLOPT_POST, 1);
curl_setopt($ch3, CURLOPT_POSTFIELDS, $data3);
curl_setopt($ch3, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . $auth3
]);
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch3, CURLOPT_TIMEOUT, 30);

$response3 = curl_exec($ch3);
$httpCode3 = curl_getinfo($ch3, CURLINFO_HTTP_CODE);
curl_close($ch3);

echo "HTTP Code: $httpCode3<br>\n";
if ($httpCode3 == 200) {
    echo "Search API works!<br>\n";
} else {
    echo "Search API failed: " . substr($response3, 0, 500) . "<br>\n";
}

echo "<hr>\n";
echo "<p><strong>Note:</strong> If all APIs are returning 404 errors, the TBO API endpoints might be different or temporarily unavailable.</p>\n";
echo "<p>Meanwhile, the hotel search page is showing mock data with 25 hotels to demonstrate the batch processing concept.</p>\n";
?>
