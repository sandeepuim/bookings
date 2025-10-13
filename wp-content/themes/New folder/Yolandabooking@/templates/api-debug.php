<?php
/**
 * Template Name: API Debug
 */

get_header();

require_once get_template_directory() . '/inc/TboApiClient.php';

// Create API client
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI',
    'YOLANDATHTest',
    'Yol@40360746'
);

echo '<div class="container" style="padding: 20px; margin-top: 100px;">';
echo '<h1>TBO API Debug Test</h1>';

try {
    // Step 1: Test getting cities
    echo '<h2>Step 1: Testing City List</h2>';
    $cities = $tbo->getCities('IN');
    if (!empty($cities['CityList'])) {
        echo '<p style="color: green;">✓ Successfully retrieved ' . count($cities['CityList']) . ' cities</p>';
        
        // Look for Delhi specifically
        $delhiFound = false;
        foreach ($cities['CityList'] as $city) {
            if ($city['Code'] == '130443') {
                $delhiFound = true;
                echo '<p>Found Delhi: ' . print_r($city, true) . '</p>';
                break;
            }
        }
        if (!$delhiFound) {
            echo '<p style="color: red;">✗ Delhi (130443) not found in city list!</p>';
        }
    } else {
        echo '<p style="color: red;">✗ No cities found!</p>';
    }

    // Step 2: Get Hotel Codes for Delhi
    echo '<h2>Step 2: Testing Hotel Codes for Delhi</h2>';
    $hotelCodes = $tbo->getHotelCodes('130443');
    if (!empty($hotelCodes['HotelCodes'])) {
        echo '<p style="color: green;">✓ Found ' . count($hotelCodes['HotelCodes']) . ' hotel codes</p>';
        echo '<p>First 10 codes: ' . implode(', ', array_slice($hotelCodes['HotelCodes'], 0, 10)) . '</p>';
    } else {
        echo '<p style="color: red;">✗ No hotel codes found!</p>';
        echo '<pre>' . print_r($hotelCodes, true) . '</pre>';
    }

    // Step 3: Test hotel search with known working parameters
    echo '<h2>Step 3: Testing Hotel Search</h2>';
    
    // Test parameters
    $params = [
        'CheckIn' => '2025-09-29',
        'CheckOut' => '2025-10-01',
        'HotelCodes' => implode(',', ['1616850','1616856','1656043','1656064','1656100']),
        'GuestNationality' => 'IN',
        'PaxRooms' => [
            [
                'Adults' => 1
            ]
        ],
        'ResponseTime' => 20,
        'IsDetailedResponse' => true
    ];
    
    echo '<p>Testing with parameters:</p>';
    echo '<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>';
    
    // Make direct API call
    $auth = base64_encode('YOLANDATHTest:Yol@40360746');
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/Search');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Basic ' . $auth,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    echo '<p>API Response Code: ' . $httpCode . '</p>';
    
    if ($response === false) {
        echo '<p style="color: red;">✗ API call failed: ' . curl_error($ch) . '</p>';
    } else {
        $result = json_decode($response, true);
        if (isset($result['Hotels']) && !empty($result['Hotels'])) {
            echo '<p style="color: green;">✓ Found ' . count($result['Hotels']) . ' hotels</p>';
            foreach ($result['Hotels'] as $hotel) {
                echo '<div style="margin: 10px 0; padding: 10px; border: 1px solid #ccc;">';
                echo '<p><strong>Hotel Name:</strong> ' . ($hotel['HotelName'] ?? 'Unknown') . '</p>';
                echo '<p><strong>Hotel Code:</strong> ' . ($hotel['HotelCode'] ?? 'Unknown') . '</p>';
                if (isset($hotel['Rooms']) && is_array($hotel['Rooms'])) {
                    echo '<p><strong>Available Rooms:</strong> ' . count($hotel['Rooms']) . '</p>';
                }
                echo '</div>';
            }
        } else {
            echo '<p style="color: red;">✗ No hotels found in response</p>';
            echo '<p>Raw Response:</p>';
            echo '<pre>' . htmlspecialchars($response) . '</pre>';
        }
    }
    curl_close($ch);

} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; margin: 10px 0; border: 1px solid red;">';
    echo '<h3>Error Occurred:</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '<pre>' . $e->getTraceAsString() . '</pre>';
    echo '</div>';
}

echo '</div>';

get_footer();
