<?php
/**
 * Debug TBO API - Test the API endpoints
 * Access this file directly to test API connectivity
 */

// Include WordPress functionality (if testing outside WP)
if (!function_exists('wp_remote_post')) {
    require_once('../../../wp-load.php');
}

// Include the TBO API class
require_once('hotel-search.php');

echo "<h1>TBO API Debug Test</h1>";

$tbo_api = new TBO_Hotel_API();

// Test 1: Get cities for India
echo "<h2>Test 1: Get Cities for India (IN)</h2>";
$cities_response = $tbo_api->get_cities('IN');
echo "<pre>";
print_r($cities_response);
echo "</pre>";

// Test 2: Get hotel codes for a city (using first city from response)
if (isset($cities_response['Cities']) && !empty($cities_response['Cities'])) {
    $first_city = $cities_response['Cities'][0];
    echo "<h2>Test 2: Get Hotel Codes for " . $first_city['CityName'] . " (Code: " . $first_city['CityCode'] . ")</h2>";
    
    $hotel_codes_response = $tbo_api->get_hotel_codes($first_city['CityCode']);
    echo "<pre>";
    print_r($hotel_codes_response);
    echo "</pre>";
    
    // Test 3: Search hotels (if we have hotel codes)
    if (isset($hotel_codes_response['HotelCodes']) && !empty($hotel_codes_response['HotelCodes'])) {
        echo "<h2>Test 3: Search Hotels</h2>";
        
        // Take first 5 hotel codes for testing
        $hotel_codes = array_slice($hotel_codes_response['HotelCodes'], 0, 5);
        $hotel_codes_str = implode(',', array_column($hotel_codes, 'HotelCode'));
        
        $search_params = array(
            'check_in' => date('Y-m-d', strtotime('+1 day')),
            'check_out' => date('Y-m-d', strtotime('+3 days')),
            'hotel_codes' => $hotel_codes_str,
            'pax_rooms' => array(array('Adults' => 2))
        );
        
        echo "Search Parameters:<br>";
        echo "Check-in: " . $search_params['check_in'] . "<br>";
        echo "Check-out: " . $search_params['check_out'] . "<br>";
        echo "Hotel Codes: " . $hotel_codes_str . "<br>";
        echo "Guests: 2 Adults<br><br>";
        
        $search_results = $tbo_api->search_hotels($search_params);
        echo "<pre>";
        print_r($search_results);
        echo "</pre>";
    }
}

echo "<h2>API Test Complete</h2>";
?>
