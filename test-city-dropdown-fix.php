<?php
// Test to verify the city dropdown functionality with error monitoring
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h1>TBO Hotels City Dropdown Debug</h1>";

// Set specific country codes to test
$testCountryCodes = ['IN', 'US', 'GB'];

echo "<h2>Step 1: Clear Transient Cache</h2>";

foreach ($testCountryCodes as $countryCode) {
    $cacheKey = 'tbo_hotels_cities_' . $countryCode;
    $deleted = delete_transient($cacheKey);
    echo "<p>Cleared cache for {$countryCode}: " . ($deleted ? 'Yes' : 'No (was not cached)') . "</p>";
}

echo "<h2>Step 2: Test City API Endpoints</h2>";

// Test both URL formats to confirm which one works
$urls = [
    'hotelapi_v10' => 'http://api.tbotechnology.in/hotelapi_v10/CityList',
    'TBOHolidays_HotelAPI' => 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList'
];

foreach ($testCountryCodes as $countryCode) {
    echo "<h3>Testing Country: {$countryCode}</h3>";
    
    foreach ($urls as $urlType => $url) {
        echo "<h4>Testing URL: {$urlType}</h4>";
        
        $result = tbo_hotels_direct_curl_request(
            $url,
            ['CountryCode' => $countryCode],
            'POST'
        );
        
        if (is_wp_error($result)) {
            echo "<p style='color: red;'>Error: " . $result->get_error_message() . "</p>";
        } else {
            echo "<p style='color: green;'>Success! ";
            if (isset($result['CityList']) && is_array($result['CityList'])) {
                echo "Found " . count($result['CityList']) . " cities.</p>";
                
                // Show first 5 cities
                echo "<ul>";
                $count = 0;
                foreach ($result['CityList'] as $city) {
                    if ($count < 5) {
                        echo "<li>{$city['Name']} ({$city['Code']})</li>";
                    }
                    $count++;
                }
                echo "</ul>";
            } else {
                echo "Response did not contain expected CityList array.</p>";
                echo "<pre>" . print_r($result, true) . "</pre>";
            }
        }
    }
}

echo "<h2>Step 3: Test Our Theme Function</h2>";

foreach ($testCountryCodes as $countryCode) {
    echo "<h3>Testing Country: {$countryCode}</h3>";
    
    $cities = tbo_hotels_get_cities($countryCode);
    
    if (is_wp_error($cities)) {
        echo "<p style='color: red;'>Error: " . $cities->get_error_message() . "</p>";
    } else {
        echo "<p style='color: green;'>Success! Found " . count($cities) . " cities</p>";
        
        // Show first 5 cities
        echo "<ul>";
        $count = 0;
        foreach ($cities as $city) {
            if ($count < 5) {
                echo "<li>" . $city['Destination']['DestinationName'] . " (" . 
                     $city['Destination']['DestinationCode'] . ")</li>";
            }
            $count++;
        }
        echo "</ul>";
    }
}

echo "<h2>Step 4: Test AJAX Handler (Simulated)</h2>";

foreach ($testCountryCodes as $countryCode) {
    echo "<h3>Testing AJAX for Country: {$countryCode}</h3>";
    
    // Simulate the AJAX request but capture output instead of sending JSON
    $_POST = [
        'country_code' => $countryCode,
        'nonce' => wp_create_nonce('tbo_hotels_nonce')
    ];
    
    // Buffer the output that would normally be sent as JSON
    ob_start();
    try {
        // Call the function directly instead of through AJAX
        $cities = tbo_hotels_get_cities($countryCode);
        
        if (is_wp_error($cities)) {
            echo "Error: " . $cities->get_error_message();
        } else {
            echo "Success! Found " . count($cities) . " cities";
        }
    } catch (Exception $e) {
        echo "Exception: " . $e->getMessage();
    }
    $output = ob_get_clean();
    
    echo "<p>Result: {$output}</p>";
}

echo "<h2>Final Test: Browser-Ready Dropdown</h2>";

echo "<p>This simulates what would appear in the browser:</p>";

// Get one country's cities for the dropdown demo
$demoCountry = 'IN';
$cities = tbo_hotels_get_cities($demoCountry);

if (is_wp_error($cities)) {
    echo "<p style='color: red;'>Error loading cities: " . $cities->get_error_message() . "</p>";
} else {
    echo "<select id='demo-city-select' style='width: 300px; padding: 5px;'>";
    echo "<option value=''>Select a city</option>";
    foreach ($cities as $city) {
        echo "<option value='" . $city['Destination']['DestinationCode'] . "'>" . 
             $city['Destination']['DestinationName'] . "</option>";
    }
    echo "</select>";
    
    echo "<p><strong>Total cities loaded: " . count($cities) . "</strong></p>";
}
?>