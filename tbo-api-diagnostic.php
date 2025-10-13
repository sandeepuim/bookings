<?php
// TBO API Integration Diagnostic Tool
// This script performs comprehensive testing of all TBO API integration points

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress
require_once('wp-load.php');

echo "<h1>TBO API Integration Diagnostic Tool</h1>";

// Define test country codes
$testCountries = ['IN', 'US', 'GB'];

// 1. Test Authentication
echo "<h2>1. Testing API Authentication</h2>";
$auth_header = tbo_hotels_get_auth_header();
echo "<p>Auth Header: " . substr($auth_header, 0, 10) . "..." . "</p>";

// 2. Test Country List API
echo "<h2>2. Testing Country List API</h2>";
$countries = tbo_hotels_get_countries();

if (is_wp_error($countries)) {
    echo "<p style='color: red;'>❌ Error loading countries: " . $countries->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>✅ Success! Found " . count($countries) . " countries</p>";
    
    echo "<h3>Sample Countries:</h3>";
    echo "<ul>";
    $count = 0;
    foreach ($countries as $country) {
        if ($count < 5) {
            echo "<li>" . $country['Name'] . " (" . $country['Code'] . ")</li>";
        }
        $count++;
    }
    echo "</ul>";
}

// 3. Test City List API
echo "<h2>3. Testing City List API</h2>";

foreach ($testCountries as $countryCode) {
    echo "<h3>Testing Cities for: " . $countryCode . "</h3>";
    
    // Clear cache to ensure fresh test
    delete_transient('tbo_hotels_cities_' . $countryCode);
    
    // Get cities
    $start_time = microtime(true);
    $cities = tbo_hotels_get_cities($countryCode);
    $time_taken = round(microtime(true) - $start_time, 2);
    
    if (is_wp_error($cities)) {
        echo "<p style='color: red;'>❌ Error loading cities: " . $cities->get_error_message() . "</p>";
        
        // Try direct API call as fallback
        echo "<p>Attempting direct cURL call...</p>";
        $direct_url = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList';
        $direct_response = tbo_hotels_direct_curl_request(
            $direct_url,
            ['CountryCode' => $countryCode],
            'POST'
        );
        
        if (is_wp_error($direct_response)) {
            echo "<p style='color: red;'>❌ Direct API call also failed: " . $direct_response->get_error_message() . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Direct API call succeeded!</p>";
            echo "<p>First few cities from direct call:</p>";
            echo "<ul>";
            $count = 0;
            foreach ($direct_response['CityList'] as $city) {
                if ($count < 5) {
                    echo "<li>" . $city['Name'] . " (" . $city['Code'] . ")</li>";
                }
                $count++;
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color: green;'>✅ Success! Found " . count($cities) . " cities (took " . $time_taken . " seconds)</p>";
        
        echo "<h4>Sample Cities:</h4>";
        echo "<ul>";
        $count = 0;
        foreach ($cities as $city) {
            if ($count < 5) {
                echo "<li>" . $city['Destination']['DestinationName'] . " (" . $city['Destination']['DestinationCode'] . ")</li>";
            }
            $count++;
        }
        echo "</ul>";
    }
}

// 4. Test Hotel Codes API
echo "<h2>4. Testing Hotel Codes API</h2>";

// Test with a few city codes
$testCityCodes = ['100589', '111647', '418069']; // City codes for major cities in India
$cityNames = ['Delhi', 'Mumbai', 'Bangalore'];

for ($i = 0; $i < count($testCityCodes); $i++) {
    $cityCode = $testCityCodes[$i];
    $cityName = $cityNames[$i];
    
    echo "<h3>Testing Hotel Codes for: " . $cityName . " (Code: " . $cityCode . ")</h3>";
    
    // Clear cache
    delete_transient('tbo_hotels_codes_' . $cityCode);
    
    // Get hotel codes
    $start_time = microtime(true);
    $hotelCodes = tbo_hotels_get_hotel_codes($cityCode);
    $time_taken = round(microtime(true) - $start_time, 2);
    
    if (is_wp_error($hotelCodes)) {
        echo "<p style='color: red;'>❌ Error loading hotel codes: " . $hotelCodes->get_error_message() . "</p>";
        
        // Try direct API call
        echo "<p>Attempting direct cURL call...</p>";
        $direct_url = TBO_API_BASE_URL . '/HotelCodeList';
        $direct_response = tbo_hotels_direct_curl_request(
            $direct_url,
            ['CityCode' => $cityCode],
            'GET'
        );
        
        if (is_wp_error($direct_response)) {
            echo "<p style='color: red;'>❌ Direct API call also failed: " . $direct_response->get_error_message() . "</p>";
        } else {
            echo "<p style='color: green;'>✅ Direct API call succeeded!</p>";
            
            // Try to identify hotel codes in response
            $direct_codes = [];
            
            if (isset($direct_response['Hotels']) && is_array($direct_response['Hotels'])) {
                foreach ($direct_response['Hotels'] as $hotel) {
                    if (isset($hotel['HotelCode'])) {
                        $direct_codes[] = $hotel['HotelCode'];
                    }
                }
            } elseif (isset($direct_response['HotelCodes']) && is_array($direct_response['HotelCodes'])) {
                $direct_codes = $direct_response['HotelCodes'];
            }
            
            echo "<p>Found " . count($direct_codes) . " hotel codes via direct call</p>";
            echo "<p>First few hotel codes:</p>";
            echo "<pre>" . json_encode(array_slice($direct_codes, 0, 5), JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "<p style='color: green;'>✅ Success! Found " . count($hotelCodes) . " hotel codes (took " . $time_taken . " seconds)</p>";
        
        echo "<h4>Sample Hotel Codes:</h4>";
        echo "<pre>" . json_encode(array_slice($hotelCodes, 0, 10), JSON_PRETTY_PRINT) . "</pre>";
    }
}

// 5. Test Hotel Search API
echo "<h2>5. Testing Hotel Search API</h2>";

// Define test search parameters
$searchParams = [
    'city_code' => '100589', // Delhi
    'check_in' => '2025-10-01',
    'check_out' => '2025-10-03',
    'adults' => 2,
    'rooms' => 1,
    'children' => 0,
    'max_codes' => 10 // Limit to just a few codes for testing
];

echo "<h3>Search Parameters:</h3>";
echo "<pre>" . json_encode($searchParams, JSON_PRETTY_PRINT) . "</pre>";

// Clear search cache
$cache_key = 'tbo_hotels_search_' . md5(serialize($searchParams));
delete_transient($cache_key);

// Perform search
$start_time = microtime(true);
$searchResults = tbo_hotels_search_hotels($searchParams);
$time_taken = round(microtime(true) - $start_time, 2);

if (is_wp_error($searchResults)) {
    echo "<p style='color: red;'>❌ Error performing hotel search: " . $searchResults->get_error_message() . "</p>";
} else {
    echo "<p style='color: green;'>✅ Success! Search completed in " . $time_taken . " seconds</p>";
    
    if (isset($searchResults['Hotels']) && is_array($searchResults['Hotels'])) {
        $hotels = $searchResults['Hotels'];
        echo "<p>Found " . count($hotels) . " hotels</p>";
        
        echo "<h4>Sample Hotels:</h4>";
        echo "<ul>";
        $count = 0;
        foreach ($hotels as $hotel) {
            if ($count < 5) {
                $hotelName = isset($hotel['HotelName']) ? $hotel['HotelName'] : 'Unknown';
                $hotelCode = isset($hotel['HotelCode']) ? $hotel['HotelCode'] : 'Unknown';
                echo "<li>" . $hotelName . " (Code: " . $hotelCode . ")</li>";
            }
            $count++;
        }
        echo "</ul>";
        
        // Show first hotel details
        if (!empty($hotels)) {
            echo "<h4>First Hotel Details:</h4>";
            echo "<pre>" . json_encode($hotels[0], JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "<p style='color: orange;'>⚠️ No hotels found in search results or unexpected format</p>";
        echo "<p>Search results structure:</p>";
        echo "<pre>" . json_encode(array_keys($searchResults), JSON_PRETTY_PRINT) . "</pre>";
    }
}

// 6. Test AJAX handlers
echo "<h2>6. Testing AJAX Handlers</h2>";

// Test country AJAX handler
echo "<h3>Testing Countries AJAX Handler</h3>";
$_POST = [
    'nonce' => wp_create_nonce('tbo_hotels_nonce')
];
ob_start();
try {
    do_action('wp_ajax_tbo_hotels_get_countries');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();
echo "<p>AJAX Response (first 200 chars):</p>";
echo "<pre>" . htmlspecialchars(substr($output, 0, 200)) . "...</pre>";

// Test cities AJAX handler
echo "<h3>Testing Cities AJAX Handler</h3>";
$_POST = [
    'country_code' => 'IN',
    'nonce' => wp_create_nonce('tbo_hotels_nonce')
];
ob_start();
try {
    do_action('wp_ajax_tbo_hotels_get_cities');
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage();
}
$output = ob_get_clean();
echo "<p>AJAX Response (first 200 chars):</p>";
echo "<pre>" . htmlspecialchars(substr($output, 0, 200)) . "...</pre>";

// 7. Test API constants and functions
echo "<h2>7. Verifying API Setup</h2>";

echo "<p>Base URL: " . TBO_API_BASE_URL . "</p>";
echo "<p>Username: " . TBO_API_USERNAME . "</p>";
echo "<p>Password: " . str_repeat('*', strlen(TBO_API_PASSWORD)) . "</p>";

// Check if we're using the mock data
echo "<h3>Mock Data Status:</h3>";
$source_code = file_get_contents(__FILE__);
if (strpos($source_code, 'if ($is_local && false)') !== false) {
    echo "<p>Mock data is disabled (configured to use real API)</p>";
} else {
    echo "<p>Mock data may be enabled (check functions.php)</p>";
}

// 8. Provide solution recommendations
echo "<h2>8. Recommendations</h2>";

echo "<ul>";
echo "<li>Check that TBO API credentials are correct and account is active</li>";
echo "<li>Verify the API base URL is correct: current = " . TBO_API_BASE_URL . "</li>";
echo "<li>Ensure proper URL path is used for each endpoint (hotelapi_v10 vs TBOHolidays_HotelAPI)</li>";
echo "<li>Confirm the hotel search parameters match TBO API requirements</li>";
echo "<li>Check for any network or firewall issues that might block API calls</li>";
echo "<li>Review WordPress error logs for PHP errors</li>";
echo "<li>Consider disabling SSL verification temporarily if SSL issues are suspected</li>";
echo "</ul>";

echo "<p><em>End of diagnostic report</em></p>";
?>