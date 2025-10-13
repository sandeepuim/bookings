<?php
/**
 * Template Name: Hotel Test
 */

get_header();

// Initialize TBO client
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI',
    'YOLANDATHTest',
    'Yol@40360746'
);

echo '<div style="margin-top: 100px; padding: 20px;">';
echo '<h1>Hotel Search Test</h1>';

try {
    // Test parameters
    $params = [
        'CheckIn' => '2025-09-29',
        'CheckOut' => '2025-10-01',
        'HotelCodes' => '1616850,1616856,1656043',
        'GuestNationality' => 'IN',
        'PaxRooms' => [
            [
                'Adults' => 1
            ]
        ],
        'ResponseTime' => 20,
        'IsDetailedResponse' => true
    ];

    echo '<h2>Making direct API request with these parameters:</h2>';
    echo '<pre>' . json_encode($params, JSON_PRETTY_PRINT) . '</pre>';

    // Make direct API request
    $result = $tbo->apiRequest('Search', $params, 'POST');

    echo '<h2>API Response:</h2>';
    echo '<pre>' . json_encode($result, JSON_PRETTY_PRINT) . '</pre>';

} catch (Exception $e) {
    echo '<div style="color: red; padding: 10px; border: 1px solid red;">';
    echo '<h3>Error:</h3>';
    echo '<p>' . $e->getMessage() . '</p>';
    echo '</div>';
}

echo '</div>';

get_footer();
