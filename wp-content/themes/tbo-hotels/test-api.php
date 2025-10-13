<?php
/**
 * TBO API Test Script
 * 
 * This script tests the TBO API connection directly.
 */

// Load WordPress
require_once('../../../wp-load.php');

// Define API credentials
define('TEST_API_URL', 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList');
define('TEST_API_USERNAME', 'YOLANDATHTest');
define('TEST_API_PASSWORD', 'Yol@40360746');

// Function to make the API request
function test_api_request() {
    // Set up cURL
    $curl = curl_init();
    
    // Authorization header
    $auth = 'Basic ' . base64_encode(TEST_API_USERNAME . ':' . TEST_API_PASSWORD);
    
    // Request data
    $data = json_encode(array(
        'CountryCode' => 'IN'
    ));
    
    // Set cURL options
    curl_setopt_array($curl, array(
        CURLOPT_URL => TEST_API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $data,
        CURLOPT_HTTPHEADER => array(
            'Authorization: ' . $auth,
            'Content-Type: application/json',
            'Accept: application/json'
        ),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ));
    
    // Execute the request
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);
    
    curl_close($curl);
    
    echo '<h1>TBO API Test Results</h1>';
    
    // Display error if any
    if ($err) {
        echo '<h2>cURL Error:</h2>';
        echo '<pre>' . $err . '</pre>';
        return;
    }
    
    // Display HTTP status code
    echo '<h2>HTTP Status: ' . $info['http_code'] . '</h2>';
    
    // Display response headers
    echo '<h2>Response Headers:</h2>';
    echo '<pre>' . print_r($info, true) . '</pre>';
    
    // Display raw response
    echo '<h2>Raw Response:</h2>';
    echo '<pre>' . htmlspecialchars($response) . '</pre>';
    
    // Display parsed response
    echo '<h2>Parsed Response:</h2>';
    $parsed = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo '<pre>' . print_r($parsed, true) . '</pre>';
    } else {
        echo '<p>Error parsing JSON: ' . json_last_error_msg() . '</p>';
    }
}

// Run the test
test_api_request();