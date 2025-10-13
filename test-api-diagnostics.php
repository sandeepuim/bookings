<?php
// Enhanced test script with improved error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>TBO Hotels API Troubleshooting</h1>";

// Function to create a section header
function section($title) {
    echo "<h2 style='margin-top:30px;background:#f0f0f0;padding:10px;border-left:4px solid #4CAF50;'>{$title}</h2>";
}

// Function to log test result
function test_result($test, $result, $details = null) {
    $status = $result ? 'success' : 'error';
    $color = $result ? '#4CAF50' : '#f44336';
    
    echo "<div style='margin:10px 0;padding:10px;border-left:4px solid {$color};'>";
    echo "<strong style='color:{$color};'>{$status}:</strong> {$test}<br>";
    
    if ($details) {
        if (is_array($details) || is_object($details)) {
            echo "<pre style='background:#f8f8f8;padding:10px;overflow:auto;max-height:300px;'>";
            print_r($details);
            echo "</pre>";
        } else {
            echo "<div style='background:#f8f8f8;padding:10px;'>{$details}</div>";
        }
    }
    
    echo "</div>";
}

// Load WordPress
require_once('wp-load.php');

// 1. Verify WordPress is loaded correctly
section("WordPress Environment");
test_result(
    "WordPress Version", 
    defined('ABSPATH'), 
    "WP Version: " . (defined('ABSPATH') ? get_bloginfo('version') : 'Not loaded')
);

// 2. Check TBO theme constants
section("TBO Theme Constants");
$constants = [
    'TBO_HOTELS_VERSION',
    'TBO_HOTELS_DIR',
    'TBO_HOTELS_URI',
    'TBO_API_BASE_URL',
    'TBO_API_USERNAME',
    'TBO_API_PASSWORD'
];

foreach ($constants as $constant) {
    test_result(
        "Constant: {$constant}", 
        defined($constant), 
        defined($constant) ? constant($constant) : 'Not defined'
    );
}

// 3. Test authentication header generation
section("API Authentication");
if (function_exists('tbo_hotels_get_auth_header')) {
    $auth_header = tbo_hotels_get_auth_header();
    test_result(
        "Auth Header Generation", 
        !empty($auth_header),
        "Auth Header: " . $auth_header
    );
} else {
    test_result("Auth Header Function", false, "Function tbo_hotels_get_auth_header not found");
}

// 4. Test countries API
section("Countries API Test");
if (function_exists('tbo_hotels_get_countries')) {
    // Clear cache first
    delete_transient('tbo_hotels_countries');
    
    $countries = tbo_hotels_get_countries();
    $success = !is_wp_error($countries) && !empty($countries);
    
    test_result(
        "Get Countries", 
        $success,
        $success ? "Found " . count($countries) . " countries" : $countries->get_error_message()
    );
    
    if ($success) {
        // Show first few countries
        echo "<div style='margin:10px 0;'>";
        echo "<strong>First 5 Countries:</strong>";
        echo "<ul>";
        $count = 0;
        foreach ($countries as $country) {
            if ($count < 5) {
                echo "<li>{$country['Name']} ({$country['Code']})</li>";
            }
            $count++;
        }
        echo "</ul>";
        echo "</div>";
    }
} else {
    test_result("Countries API Function", false, "Function tbo_hotels_get_countries not found");
}

// 5. Test cities API with several country codes
section("Cities API Test");
if (function_exists('tbo_hotels_get_cities')) {
    $testCountries = ['IN', 'US', 'GB'];
    
    foreach ($testCountries as $countryCode) {
        echo "<h3>Testing Country: {$countryCode}</h3>";
        
        // Clear cache first
        delete_transient('tbo_hotels_cities_' . $countryCode);
        
        // Test with modified URL (TBOHolidays_HotelAPI)
        $cities = tbo_hotels_get_cities($countryCode);
        $success = !is_wp_error($cities) && !empty($cities);
        
        test_result(
            "Get Cities for {$countryCode}", 
            $success,
            $success ? "Found " . count($cities) . " cities" : 
                      (is_wp_error($cities) ? "Error: " . $cities->get_error_message() : "No cities found")
        );
        
        if ($success) {
            // Show first few cities
            echo "<div style='margin:10px 0;'>";
            echo "<strong>First 5 Cities:</strong>";
            echo "<ul>";
            $count = 0;
            foreach ($cities as $city) {
                if ($count < 5 && isset($city['Destination'])) {
                    echo "<li>{$city['Destination']['DestinationName']} ({$city['Destination']['DestinationCode']})</li>";
                }
                $count++;
            }
            echo "</ul>";
            echo "</div>";
            
            // Create test dropdown
            echo "<div style='margin:20px 0;'>";
            echo "<strong>Test Dropdown:</strong><br>";
            echo "<select style='width:300px;padding:5px;'>";
            echo "<option value=''>Select a city</option>";
            $count = 0;
            foreach ($cities as $city) {
                if ($count < 10 && isset($city['Destination'])) {
                    echo "<option value='{$city['Destination']['DestinationCode']}'>{$city['Destination']['DestinationName']}</option>";
                }
                $count++;
            }
            echo "</select>";
            echo "</div>";
        }
    }
} else {
    test_result("Cities API Function", false, "Function tbo_hotels_get_cities not found");
}

// 6. Direct cURL Test for Cities
section("Direct cURL Test for Cities");
if (function_exists('tbo_hotels_direct_curl_request')) {
    $testCountry = 'IN';
    
    // Test TBOHolidays_HotelAPI URL path
    $result = tbo_hotels_direct_curl_request(
        'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList',
        ['CountryCode' => $testCountry],
        'POST'
    );
    
    $success = !is_wp_error($result) && isset($result['CityList']) && !empty($result['CityList']);
    
    test_result(
        "Direct cURL with TBOHolidays_HotelAPI path", 
        $success,
        $success ? "Found " . count($result['CityList']) . " cities" : 
                  (is_wp_error($result) ? "Error: " . $result->get_error_message() : "No cities found or unexpected response format")
    );
    
    // Test hotelapi_v10 URL path
    $result2 = tbo_hotels_direct_curl_request(
        'http://api.tbotechnology.in/hotelapi_v10/CityList',
        ['CountryCode' => $testCountry],
        'POST'
    );
    
    $success2 = !is_wp_error($result2) && isset($result2['CityList']) && !empty($result2['CityList']);
    
    test_result(
        "Direct cURL with hotelapi_v10 path", 
        $success2,
        $success2 ? "Found " . count($result2['CityList']) . " cities" : 
                   (is_wp_error($result2) ? "Error: " . $result2->get_error_message() : "No cities found or unexpected response format")
    );
} else {
    test_result("Direct cURL Function", false, "Function tbo_hotels_direct_curl_request not found");
}

// 7. Network connectivity test
section("Network Connectivity Test");
$test_urls = [
    'http://api.tbotechnology.in' => 'TBO API Base URL',
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList' => 'TBO Cities API',
    'http://api.tbotechnology.in/hotelapi_v10/HotelCodeList' => 'TBO Hotel Codes API'
];

foreach ($test_urls as $url => $desc) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    test_result(
        "Connection to {$desc}", 
        $http_code > 0 && $http_code < 500,
        "HTTP Status: " . $http_code . ($http_code == 0 ? " (Connection failed)" : "")
    );
}

// 8. Test AJAX handlers
section("AJAX Handlers Test");
$ajax_functions = [
    'tbo_hotels_ajax_get_countries',
    'tbo_hotels_ajax_get_cities',
    'tbo_hotels_ajax_get_hotel_codes',
    'tbo_hotels_ajax_search_hotels'
];

foreach ($ajax_functions as $function) {
    test_result(
        "AJAX Handler: {$function}", 
        function_exists($function),
        function_exists($function) ? "Function exists" : "Function not found"
    );
}

// 9. Check for any active PHP errors
section("PHP Error Status");
$error_log_file = ini_get('error_log');
$error_log_exists = file_exists($error_log_file);

test_result(
    "PHP Error Log", 
    $error_log_exists,
    $error_log_exists ? "Error log exists at: " . $error_log_file : "Error log not found"
);

if ($error_log_exists) {
    $last_errors = shell_exec('tail -n 20 ' . escapeshellarg($error_log_file));
    echo "<div style='margin:10px 0;'>";
    echo "<strong>Last 20 lines of error log:</strong>";
    echo "<pre style='background:#f8f8f8;padding:10px;overflow:auto;max-height:300px;'>";
    echo htmlspecialchars($last_errors ?: "No recent errors");
    echo "</pre>";
    echo "</div>";
}

// 10. Summary and Recommendations
section("Summary and Recommendations");
echo "<div style='background:#e8f5e9;padding:15px;border-radius:5px;margin-top:20px;'>";
echo "<h3>Troubleshooting Summary:</h3>";
echo "<p>Based on the tests performed:</p>";
echo "<ul>";
echo "<li>CityList API requires the <strong>TBOHolidays_HotelAPI</strong> URL path</li>";
echo "<li>HotelCodeList API requires the <strong>hotelapi_v10</strong> URL path</li>";
echo "<li>Clearing transient cache can help resolve stale error responses</li>";
echo "<li>Authentication header format must be correct (Basic + base64)</li>";
echo "</ul>";
echo "<p><strong>Next Steps:</strong></p>";
echo "<ol>";
echo "<li>Ensure all API calls use the correct URL path for each endpoint</li>";
echo "<li>Clear all transient caches if you continue to see errors</li>";
echo "<li>Check network connectivity from your server to TBO API endpoints</li>";
echo "<li>Verify credentials are correct in the theme constants</li>";
echo "</ol>";
echo "</div>";
?>