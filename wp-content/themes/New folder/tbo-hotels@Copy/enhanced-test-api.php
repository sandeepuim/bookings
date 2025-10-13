<?php
/**
 * Enhanced TBO API Test Script
 * 
 * This script directly tests the TBO API connection with detailed debugging
 * It bypasses WordPress completely to isolate API connectivity issues
 */

// Define API credentials
$username = 'YOUR_TBO_USERNAME'; // Replace with your actual credentials
$password = 'YOUR_TBO_PASSWORD'; // Replace with your actual credentials

// Set headers for output
header('Content-Type: text/html; charset=utf-8');
echo '<html><head><title>Enhanced TBO API Test</title>';
echo '<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1, h2 { color: #333; }
    pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
    .success { color: green; }
    .error { color: red; }
    .test-section { margin-bottom: 30px; border-bottom: 1px solid #ccc; padding-bottom: 20px; }
    button { padding: 10px; background: #4CAF50; color: white; border: none; cursor: pointer; margin: 5px; }
    select { padding: 8px; margin: 5px; }
</style>';
echo '</head><body>';
echo '<h1>Enhanced TBO API Test</h1>';

// Test function
function test_tbo_api_request($endpoint, $request_data, $method = 'POST') {
    global $username, $password;
    
    echo "<div class='test-section'>";
    echo "<h2>Testing Endpoint: $endpoint</h2>";
    
    // Base URL
    $api_base_url = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/';
    $url = $api_base_url . $endpoint;
    
    echo "<p>API URL: $url</p>";
    echo "<p>Request Method: $method</p>";
    echo "<p>Request Data:</p>";
    echo "<pre>" . htmlspecialchars(json_encode($request_data, JSON_PRETTY_PRINT)) . "</pre>";
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    
    // Set basic authentication
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    
    // Set appropriate method and data
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($request_data))
        ));
    }
    
    // Verbose output for debugging
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    // Execute the request
    $start_time = microtime(true);
    $response = curl_exec($ch);
    $end_time = microtime(true);
    
    // Get information about the request
    $info = curl_getinfo($ch);
    $http_code = $info['http_code'];
    $time_taken = round($end_time - $start_time, 2);
    
    // Get any errors
    $error = curl_error($ch);
    $errno = curl_errno($ch);
    
    // Close the connection
    curl_close($ch);
    
    // Output request timing and status
    echo "<p>HTTP Status Code: <strong>" . $http_code . "</strong></p>";
    echo "<p>Request Time: <strong>" . $time_taken . " seconds</strong></p>";
    
    // Output verbose info
    rewind($verbose);
    $verbose_log = stream_get_contents($verbose);
    echo "<p>Verbose cURL Log:</p>";
    echo "<pre>" . htmlspecialchars($verbose_log) . "</pre>";
    
    // Check for errors
    if ($error) {
        echo "<p class='error'>cURL Error ($errno): $error</p>";
        echo "</div>";
        return false;
    }
    
    // Process and output the response
    if ($response) {
        $json_response = json_decode($response, true);
        
        // Check if JSON decoding was successful
        if ($json_response === null && json_last_error() !== JSON_ERROR_NONE) {
            echo "<p class='error'>JSON Decoding Error: " . json_last_error_msg() . "</p>";
            echo "<p>Raw Response:</p>";
            echo "<pre>" . htmlspecialchars(substr($response, 0, 2000)) . 
                 (strlen($response) > 2000 ? "...[truncated]" : "") . "</pre>";
        } else {
            echo "<p class='success'>API Response (Decoded JSON):</p>";
            echo "<pre>" . htmlspecialchars(json_encode($json_response, JSON_PRETTY_PRINT)) . "</pre>";
            
            // For CountryList and CityList, extract key information
            if ($endpoint === 'CountryList') {
                if (isset($json_response['CountryList']) && is_array($json_response['CountryList'])) {
                    echo "<p>Found " . count($json_response['CountryList']) . " countries</p>";
                }
            } elseif ($endpoint === 'CityList') {
                if (isset($json_response['CityList']) && is_array($json_response['CityList'])) {
                    echo "<p>Found " . count($json_response['CityList']) . " cities</p>";
                    
                    // Display the first 5 cities
                    echo "<p>First 5 cities:</p>";
                    echo "<ul>";
                    $count = 0;
                    foreach ($json_response['CityList'] as $city) {
                        if ($count >= 5) break;
                        echo "<li>" . htmlspecialchars($city['CityName']) . " (Code: " . htmlspecialchars($city['CityCode']) . ")</li>";
                        $count++;
                    }
                    echo "</ul>";
                }
            }
        }
    } else {
        echo "<p class='error'>No response received from API.</p>";
    }
    
    echo "</div>";
    return $response;
}

// Basic connection test
echo "<div class='test-section'>";
echo "<h2>Basic Connection Test</h2>";

$connection_test_url = 'http://api.tbotechnology.in/';
$ch = curl_init($connection_test_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
$response = curl_exec($ch);
$error = curl_error($ch);
$info = curl_getinfo($ch);
curl_close($ch);

if ($error) {
    echo "<p class='error'>Basic Connection Error: $error</p>";
} else {
    echo "<p class='success'>Basic Connection Successful (HTTP Code: " . $info['http_code'] . ")</p>";
}
echo "</div>";

// DNS Resolution Test
echo "<div class='test-section'>";
echo "<h2>DNS Resolution Test</h2>";

$hostname = 'api.tbotechnology.in';
$ip = gethostbyname($hostname);

if ($ip != $hostname) {
    echo "<p class='success'>DNS Resolution Successful: $hostname resolves to $ip</p>";
} else {
    echo "<p class='error'>DNS Resolution Failed: Could not resolve $hostname</p>";
}
echo "</div>";

// Test API requests for different endpoints
$country_list_data = array();
$country_list_response = test_tbo_api_request('CountryList', $country_list_data);

// Only test city list if country list succeeded
if ($country_list_response) {
    $countries = json_decode($country_list_response, true);
    
    if (isset($countries['CountryList']) && !empty($countries['CountryList'])) {
        // Use the first country for testing city list
        $first_country = $countries['CountryList'][0];
        $country_code = $first_country['CountryCode'];
        
        echo "<p>Testing CityList for country: " . htmlspecialchars($first_country['CountryName']) . 
             " (Code: " . htmlspecialchars($country_code) . ")</p>";
        
        $city_list_data = array(
            'CountryCode' => $country_code
        );
        
        test_tbo_api_request('CityList', $city_list_data);
    }
}

// Interactive Test Form
echo "<div class='test-section'>";
echo "<h2>Interactive City List Test</h2>";
echo "<form method='get'>";
echo "<input type='hidden' name='action' value='city_test'>";
echo "<label for='country_code'>Select Country Code:</label>";
echo "<input type='text' name='country_code' id='country_code' value='" . 
     (isset($_GET['country_code']) ? htmlspecialchars($_GET['country_code']) : "IN") . "'>";
echo "<button type='submit'>Get Cities</button>";
echo "</form>";

if (isset($_GET['action']) && $_GET['action'] === 'city_test' && isset($_GET['country_code'])) {
    $country_code = $_GET['country_code'];
    $city_list_data = array(
        'CountryCode' => $country_code
    );
    
    test_tbo_api_request('CityList', $city_list_data);
}
echo "</div>";

// Dump server info for debugging
echo "<div class='test-section'>";
echo "<h2>Server Environment Information</h2>";
echo "<pre>";
echo "PHP Version: " . phpversion() . "\n";
echo "cURL Version: " . (function_exists('curl_version') ? json_encode(curl_version()) : "Not available") . "\n";
echo "Server Software: " . $_SERVER['SERVER_SOFTWARE'] . "\n";
echo "User Agent: " . $_SERVER['HTTP_USER_AGENT'] . "\n";
echo "</pre>";
echo "</div>";

echo '</body></html>';
?>