<?php
/**
 * Simple TBO API Test - Check City List Response Format
 */

// Test the TBO API directly
$url = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList';
$auth_header = 'Basic WU9MQU5EQVRIVGVzdDpZb2xANDAzNjA3NDY=';

$data = array(
    'CountryCode' => 'IN' // Test with India
);

$ch = curl_init();

curl_setopt_array($ch, array(
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 30,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Authorization: ' . $auth_header
    ),
));

$response = curl_exec($ch);
$error = curl_error($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<h1>TBO API City List Test</h1>";
echo "<h2>Request Details:</h2>";
echo "<p><strong>URL:</strong> " . $url . "</p>";
echo "<p><strong>Country Code:</strong> IN</p>";
echo "<p><strong>HTTP Code:</strong> " . $http_code . "</p>";

if ($error) {
    echo "<h2>cURL Error:</h2>";
    echo "<pre>" . $error . "</pre>";
} else {
    echo "<h2>Raw Response:</h2>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
    
    echo "<h2>Parsed Response:</h2>";
    $parsed = json_decode($response, true);
    echo "<pre>" . print_r($parsed, true) . "</pre>";
    
    echo "<h2>Response Structure Analysis:</h2>";
    if ($parsed) {
        echo "<p><strong>Top-level keys:</strong> " . implode(', ', array_keys($parsed)) . "</p>";
        
        // Look for cities in different possible locations
        if (isset($parsed['Cities'])) {
            echo "<p><strong>Cities found in 'Cities' key:</strong> " . count($parsed['Cities']) . " cities</p>";
        }
        if (isset($parsed['CityList'])) {
            echo "<p><strong>Cities found in 'CityList' key:</strong> " . count($parsed['CityList']) . " cities</p>";
        }
        if (isset($parsed['HotelSearchResult']) && isset($parsed['HotelSearchResult']['Cities'])) {
            echo "<p><strong>Cities found in 'HotelSearchResult.Cities':</strong> " . count($parsed['HotelSearchResult']['Cities']) . " cities</p>";
        }
        
        // Show first city as example
        $cities = null;
        if (isset($parsed['Cities'])) $cities = $parsed['Cities'];
        elseif (isset($parsed['CityList'])) $cities = $parsed['CityList'];
        elseif (isset($parsed['HotelSearchResult']['Cities'])) $cities = $parsed['HotelSearchResult']['Cities'];
        
        if ($cities && count($cities) > 0) {
            echo "<h3>First City Example:</h3>";
            echo "<pre>" . print_r($cities[0], true) . "</pre>";
        }
    }
}
?>
