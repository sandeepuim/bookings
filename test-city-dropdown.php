<?php
// Test to verify the city dropdown functionality
// This simulates the AJAX request
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h2>TBO Hotels City Dropdown Test</h2>";

// Get countries first
$countries = tbo_hotels_get_countries();

if (is_wp_error($countries)) {
    echo "<p style='color: red;'>Error loading countries: " . $countries->get_error_message() . "</p>";
    exit;
}

echo "<p>Found " . count($countries) . " countries</p>";

// Get the first few country codes
$countryCodes = [];
$count = 0;
foreach ($countries as $country) {
    if ($count < 3) {
        $countryCodes[] = $country['Code'];
    }
    $count++;
}

// Now test city loading for each country
foreach ($countryCodes as $countryCode) {
    echo "<h3>Testing Cities for Country Code: " . $countryCode . "</h3>";
    
    // Clear cache to force fresh request
    delete_transient('tbo_hotels_cities_' . $countryCode);
    
    // Get cities
    $cities = tbo_hotels_get_cities($countryCode);
    
    if (is_wp_error($cities)) {
        echo "<p style='color: red;'>Error loading cities: " . $cities->get_error_message() . "</p>";
        // Debug the request
        echo "<p>Trying direct API call to debug...</p>";
        
        $direct_response = tbo_hotels_direct_curl_request(
            'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList', 
            ['CountryCode' => $countryCode], 
            'POST'
        );
        
        if (is_wp_error($direct_response)) {
            echo "<p style='color: red;'>Direct API call also failed: " . $direct_response->get_error_message() . "</p>";
        } else {
            echo "<p style='color: green;'>Direct API call succeeded!</p>";
            echo "<pre>" . print_r($direct_response, true) . "</pre>";
        }
    } else {
        echo "<p style='color: green;'>Success! Found " . count($cities) . " cities</p>";
        
        // Show first few cities
        echo "<h4>First 5 Cities:</h4>";
        echo "<ul>";
        $count = 0;
        foreach ($cities as $city) {
            if ($count < 5) {
                echo "<li>" . $city['Destination']['DestinationName'] . " (" . $city['Destination']['DestinationCode'] . ")</li>";
            }
            $count++;
        }
        echo "</ul>";
        
        // Show HTML that would be generated for dropdown
        echo "<h4>Dropdown HTML Preview:</h4>";
        echo "<select>";
        echo "<option value=''>Select a city</option>";
        $count = 0;
        foreach ($cities as $city) {
            if ($count < 10) {
                echo "<option value='" . $city['Destination']['DestinationCode'] . "'>" . 
                     $city['Destination']['DestinationName'] . "</option>";
            }
            $count++;
        }
        echo "</select>";
    }
    
    echo "<hr>";
}
?>