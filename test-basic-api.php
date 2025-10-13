<?php
// Test basic TBO API connectivity
require_once 'wp-config.php';
require_once 'wp-load.php';

echo "Testing TBO API connectivity...\n";

// Test getting countries (should be fast)
echo "Testing country list...\n";
$countries = tbo_hotels_get_countries();

if (is_wp_error($countries)) {
    echo "Error getting countries: " . $countries->get_error_message() . "\n";
} else {
    echo "Success! Got " . count($countries) . " countries\n";
    if (!empty($countries)) {
        $first_country = $countries[0];
        echo "First country: " . $first_country['Name'] . " (" . $first_country['Code'] . ")\n";
    }
}

// Test getting cities for a country
echo "\nTesting city list for India...\n";
$cities = tbo_hotels_get_cities('IN');

if (is_wp_error($cities)) {
    echo "Error getting cities: " . $cities->get_error_message() . "\n";
} else {
    echo "Success! Got " . count($cities) . " cities\n";
    
    // Find Bangkok
    foreach ($cities as $city) {
        if ($city['Code'] == '418069') {
            echo "Found Bangkok: " . $city['Name'] . "\n";
            break;
        }
    }
}

// Test getting hotel codes for Bangkok (limited)
echo "\nTesting hotel codes for Bangkok (limited to 5)...\n";
$hotel_codes = tbo_hotels_get_hotel_codes('418069');

if (is_wp_error($hotel_codes)) {
    echo "Error getting hotel codes: " . $hotel_codes->get_error_message() . "\n";
} else {
    $limited_codes = array_slice($hotel_codes, 0, 5);
    echo "Success! Got " . count($hotel_codes) . " total hotel codes\n";
    echo "First 5 hotel codes: " . implode(', ', $limited_codes) . "\n";
}

echo "\nBasic connectivity test completed!\n";