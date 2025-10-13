<?php
// Test script to determine the correct URL for CityList endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>TBO API CityList URL Test</h2>";

// Test URLs
$urls = [
    'http://api.tbotechnology.in/hotelapi_v10/CityList',
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList'
];

$data = json_encode(['CountryCode' => 'US']);
$auth = base64_encode('TBOStaticAPITest:Tbo@11530818');

foreach ($urls as $index => $url) {
    echo "<h3>Test " . ($index + 1) . ": " . $url . "</h3>";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Basic ' . $auth
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    echo "<p>HTTP Status Code: " . $httpCode . "</p>";
    
    if ($httpCode == 200) {
        $result = json_decode($response, true);
        if ($result) {
            echo "<p style='color: green;'><strong>Success!</strong></p>";
            echo "<p>Response has keys: " . implode(', ', array_keys($result)) . "</p>";
            
            if (isset($result['CityList'])) {
                echo "<p>Found " . count($result['CityList']) . " cities</p>";
                echo "<p>First few cities:</p>";
                echo "<ul>";
                $count = 0;
                foreach ($result['CityList'] as $city) {
                    if ($count < 5) {
                        echo "<li>" . $city['Name'] . " (" . $city['Code'] . ")</li>";
                    }
                    $count++;
                }
                echo "</ul>";
            }
        } else {
            echo "<p style='color: red;'>Failed to parse JSON response</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
        }
    } else {
        echo "<p style='color: red;'>API request failed with status code: " . $httpCode . "</p>";
        if ($error) {
            echo "<p>cURL Error: " . $error . "</p>";
        }
        echo "<pre>" . htmlspecialchars(substr($response, 0, 500)) . "...</pre>";
    }
    
    echo "<hr>";
}
?>