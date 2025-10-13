<?php
// Direct TBO API Test for HotelCodeList
// Updated to use the correct API endpoint and parameters
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>TBO API HotelCodeList Test</h2>\n";

// Test GET request to the correct endpoint
echo "<h3>Testing GET request to hotelapi_v10/HotelCodeList</h3>\n";

$url = 'http://api.tbotechnology.in/hotelapi_v10/HotelCodeList';
$city_code = '418069'; // Use a valid city code
$params = [
    'CityCode' => $city_code,
    'IsDetailedResponse' => true
];

// Use GET request with URL parameters
$query_string = http_build_query($params);
$request_url = $url . '?' . $query_string;

echo "Making GET request to: $request_url<br>\n";

// Basic authentication 
$auth = base64_encode('TBOStaticAPITest:Tbo@11530818');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $request_url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Basic ' . $auth,
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$info = curl_getinfo($ch);
$http_code = $info['http_code'];
$error = curl_error($ch);
curl_close($ch);

echo "HTTP Status Code: $http_code<br>\n";

if ($http_code == 200) {
    $data = json_decode($response, true);
    
    if ($data) {
        echo "<div style='background-color: #dff0d8; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "Success! Response decoded.<br>\n";
        
        if (isset($data['Hotels']) && is_array($data['Hotels'])) {
            $hotel_count = count($data['Hotels']);
            echo "Found $hotel_count hotels.<br>\n";
            
            if ($hotel_count > 0) {
                echo "<h4>Sample Hotel Data:</h4>\n";
                echo "<pre>";
                print_r(array_slice($data['Hotels'], 0, 3));
                echo "</pre>";
                
                echo "<h4>Hotel Codes:</h4>\n";
                $codes = [];
                foreach (array_slice($data['Hotels'], 0, 10) as $hotel) {
                    if (isset($hotel['HotelCode'])) {
                        $codes[] = $hotel['HotelCode'];
                    }
                }
                echo implode(', ', $codes);
            }
        } else {
            echo "No hotels found in response. Response structure:<br>\n";
            echo "<pre>";
            print_r(array_keys($data));
            echo "</pre>";
        }
        echo "</div>";
    } else {
        echo "<div style='background-color: #f2dede; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
        echo "Failed to decode JSON response.<br>\n";
        echo "Raw response: " . htmlspecialchars(substr($response, 0, 500)) . "...<br>\n";
        echo "</div>";
    }
} else {
    echo "<div style='background-color: #f2dede; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "API request failed with status code: $http_code<br>\n";
    if ($error) {
        echo "cURL Error: $error<br>\n";
    }
    echo "Response: " . htmlspecialchars(substr($response, 0, 500)) . "...<br>\n";
    echo "</div>";
}

echo "<hr>\n";
echo "<p><a href='javascript:history.back()'>Back</a></p>\n";
?>