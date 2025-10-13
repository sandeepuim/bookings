<?php
// Standalone TBO Hotel Search Test
// This script directly tests the TBO API without using theme functions

// Basic setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Standalone TBO Hotel Search Test</h1>";

// API credentials
$username = 'YOLANDATHTest';
$password = 'Yol@40360746';
$auth_header = 'Basic ' . base64_encode($username . ':' . $password);

echo "<h2>Test Configuration</h2>";
echo "<p>Username: $username</p>";
echo "<p>Password: " . str_repeat('*', strlen($password)) . "</p>";
echo "<p>Auth Header: " . substr($auth_header, 0, 10) . "...</p>";

// Test parameters
$check_in = '2025-10-01';
$check_out = '2025-10-03';
$city_code = '100589'; // Delhi
$sample_hotel_codes = ['1000000', '1000001', '1000002', '1000003', '1000004'];

// 1. First get hotel codes for city
echo "<h2>Step 1: Get Hotel Codes for City $city_code</h2>";

function make_api_request($endpoint, $data, $method = 'GET') {
    global $auth_header;
    
    // Determine base URL based on endpoint
    $base_url = 'http://api.tbotechnology.in/';
    if ($endpoint === 'CityList') {
        $base_url .= 'TBOHolidays_HotelAPI/';
    } else {
        $base_url .= 'hotelapi_v10/';
    }
    
    $url = $base_url . $endpoint;
    
    echo "<p>Making " . $method . " request to: " . $url . "</p>";
    echo "<p>Request data:</p>";
    echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre>";
    
    // Initialize cURL
    $curl = curl_init();
    
    // Set headers
    $headers = [
        'Authorization: ' . $auth_header,
        'Content-Type: application/json',
        'Accept: application/json'
    ];
    
    // Common cURL options
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    
    // Method-specific options
    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    } else {
        // For GET, add parameters to URL
        $url_params = http_build_query($data);
        if (!empty($url_params)) {
            curl_setopt($curl, CURLOPT_URL, $url . '?' . $url_params);
        }
    }
    
    // Execute request
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    
    // Handle errors
    if ($err) {
        echo "<p style='color: red;'>cURL Error: " . $err . "</p>";
        return null;
    }
    
    echo "<p>Response HTTP Code: " . $info['http_code'] . "</p>";
    
    // Decode JSON response
    $result = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "<p style='color: red;'>JSON Error: " . json_last_error_msg() . "</p>";
        echo "<p>Raw response:</p>";
        echo "<pre>" . htmlspecialchars(substr($response, 0, 1000)) . "...</pre>";
        return null;
    }
    
    return $result;
}

// Get hotel codes
$hotel_codes_data = [
    'CityCode' => $city_code,
    'IsDetailedResponse' => true
];

$hotel_codes_response = make_api_request('HotelCodeList', $hotel_codes_data, 'GET');

if ($hotel_codes_response) {
    echo "<p style='color: green;'>Received response</p>";
    
    // Try to extract hotel codes
    $hotel_codes = [];
    
    if (isset($hotel_codes_response['Hotels']) && is_array($hotel_codes_response['Hotels'])) {
        foreach ($hotel_codes_response['Hotels'] as $hotel) {
            if (isset($hotel['HotelCode'])) {
                $hotel_codes[] = $hotel['HotelCode'];
            }
        }
        echo "<p>Found " . count($hotel_codes) . " hotel codes in 'Hotels' property</p>";
    } elseif (isset($hotel_codes_response['HotelCodes']) && is_array($hotel_codes_response['HotelCodes'])) {
        $hotel_codes = $hotel_codes_response['HotelCodes'];
        echo "<p>Found " . count($hotel_codes) . " hotel codes in 'HotelCodes' property</p>";
    } else {
        echo "<p style='color: orange;'>Could not find hotel codes in standard properties</p>";
        echo "<p>Response keys: " . implode(', ', array_keys($hotel_codes_response)) . "</p>";
    }
    
    if (!empty($hotel_codes)) {
        echo "<p>First 5 hotel codes:</p>";
        echo "<pre>" . implode(", ", array_slice($hotel_codes, 0, 5)) . "</pre>";
        
        // Use these for the search
        $codes_to_use = array_slice($hotel_codes, 0, 5);
    } else {
        echo "<p>Using sample hotel codes for testing</p>";
        $codes_to_use = $sample_hotel_codes;
    }
} else {
    echo "<p>Using sample hotel codes for testing</p>";
    $codes_to_use = $sample_hotel_codes;
}

// 2. Now search for hotels
echo "<h2>Step 2: Search for Hotels</h2>";

$search_data = [
    'CheckIn' => $check_in,
    'CheckOut' => $check_out,
    'HotelCodes' => $codes_to_use, // Pass as array
    'GuestNationality' => 'IN',
    'PaxRooms' => [
        [
            'Adults' => 2,
            'Children' => 0,
            'ChildrenAges' => []
        ]
    ]
];

$search_response = make_api_request('HotelSearch', $search_data, 'POST');

if ($search_response) {
    echo "<p style='color: green;'>Received search response</p>";
    
    // Try to extract hotels
    $hotels = [];
    
    if (isset($search_response['Hotels']) && is_array($search_response['Hotels'])) {
        $hotels = $search_response['Hotels'];
        echo "<p>Found " . count($hotels) . " hotels in 'Hotels' property</p>";
    } elseif (isset($search_response['HotelResult']) && is_array($search_response['HotelResult'])) {
        $hotels = $search_response['HotelResult'];
        echo "<p>Found " . count($hotels) . " hotels in 'HotelResult' property</p>";
    } else {
        echo "<p style='color: orange;'>Could not find hotels in standard properties</p>";
        echo "<p>Response keys: " . implode(', ', array_keys($search_response)) . "</p>";
    }
    
    if (!empty($hotels)) {
        echo "<h3>First Hotel:</h3>";
        echo "<pre>" . print_r($hotels[0], true) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>Failed to get search response</p>";
}

// 3. Test with different hotel codes format (comma-separated string)
echo "<h2>Step 3: Test with Comma-Separated String Format</h2>";

$search_data_string = [
    'CheckIn' => $check_in,
    'CheckOut' => $check_out,
    'HotelCodes' => implode(',', $codes_to_use), // Pass as comma-separated string
    'GuestNationality' => 'IN',
    'PaxRooms' => [
        [
            'Adults' => 2,
            'Children' => 0,
            'ChildrenAges' => []
        ]
    ]
];

$search_response_string = make_api_request('HotelSearch', $search_data_string, 'POST');

if ($search_response_string) {
    echo "<p style='color: green;'>Received search response for comma-separated format</p>";
    
    // Try to extract hotels
    $hotels = [];
    
    if (isset($search_response_string['Hotels']) && is_array($search_response_string['Hotels'])) {
        $hotels = $search_response_string['Hotels'];
        echo "<p>Found " . count($hotels) . " hotels in 'Hotels' property</p>";
    } elseif (isset($search_response_string['HotelResult']) && is_array($search_response_string['HotelResult'])) {
        $hotels = $search_response_string['HotelResult'];
        echo "<p>Found " . count($hotels) . " hotels in 'HotelResult' property</p>";
    } else {
        echo "<p style='color: orange;'>Could not find hotels in standard properties</p>";
        echo "<p>Response keys: " . implode(', ', array_keys($search_response_string)) . "</p>";
    }
    
    if (!empty($hotels)) {
        echo "<h3>First Hotel:</h3>";
        echo "<pre>" . print_r($hotels[0], true) . "</pre>";
    }
} else {
    echo "<p style='color: red;'>Failed to get search response for comma-separated format</p>";
}

echo "<h2>Conclusion</h2>";
echo "<p>Based on these tests, we can determine the correct format for hotel codes in search requests.</p>";
?>