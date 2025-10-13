<?php
/**
 * Test Page for TBO Hotels Theme
 * 
 * Simple test to verify the complete integration is working
 */

require_once('./wp-config.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Test</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5; 
        }
        .test-container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .test-section { 
            margin: 20px 0; 
            padding: 15px; 
            border: 1px solid #ddd; 
            border-radius: 5px; 
        }
        .success { 
            background: #d4edda; 
            border-color: #c3e6cb; 
            color: #155724; 
        }
        .error { 
            background: #f8d7da; 
            border-color: #f5c6cb; 
            color: #721c24; 
        }
        .info { 
            background: #d1ecf1; 
            border-color: #bee5eb; 
            color: #0c5460; 
        }
        pre { 
            background: #f8f9fa; 
            padding: 10px; 
            border-radius: 3px; 
            overflow-x: auto; 
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>TBO Hotels Theme Integration Test</h1>
        
        <?php
        // Test 1: Check if theme functions are loaded
        echo '<div class="test-section">';
        echo '<h2>Test 1: Theme Functions</h2>';
        
        if (function_exists('tbo_hotels_get_countries')) {
            echo '<div class="success">✅ Theme functions loaded successfully</div>';
        } else {
            echo '<div class="error">❌ Theme functions not found</div>';
        }
        echo '</div>';
        
        // Test 2: Test Countries API
        echo '<div class="test-section">';
        echo '<h2>Test 2: Countries API</h2>';
        
        try {
            $countries = tbo_hotels_get_countries();
            if ($countries && is_array($countries) && count($countries) > 0) {
                echo '<div class="success">✅ Countries API working - Found ' . count($countries) . ' countries</div>';
                echo '<pre>Sample: ' . json_encode(array_slice($countries, 0, 3), JSON_PRETTY_PRINT) . '</pre>';
            } else {
                echo '<div class="error">❌ Countries API returned no data</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Countries API error: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';
        
        // Test 3: Test Cities API (using a common country code like IN)
        echo '<div class="test-section">';
        echo '<h2>Test 3: Cities API (India)</h2>';
        
        try {
            $cities = tbo_hotels_get_cities('IN');
            if ($cities && is_array($cities) && count($cities) > 0) {
                echo '<div class="success">✅ Cities API working - Found ' . count($cities) . ' cities for India</div>';
                echo '<pre>Sample: ' . json_encode(array_slice($cities, 0, 3), JSON_PRETTY_PRINT) . '</pre>';
            } else {
                echo '<div class="error">❌ Cities API returned no data for India</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Cities API error: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';
        
        // Test 4: Test Hotel Codes API (using a common city code)
        echo '<div class="test-section">';
        echo '<h2>Test 4: Hotel Codes API (Mumbai)</h2>';
        
        try {
            $hotel_codes = tbo_hotels_get_hotel_codes('130443');
            if ($hotel_codes && is_array($hotel_codes) && count($hotel_codes) > 0) {
                echo '<div class="success">✅ Hotel Codes API working - Found ' . count($hotel_codes) . ' hotels</div>';
                echo '<pre>Sample: ' . json_encode(array_slice($hotel_codes, 0, 5), JSON_PRETTY_PRINT) . '</pre>';
            } else {
                echo '<div class="error">❌ Hotel Codes API returned no data for Mumbai</div>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Hotel Codes API error: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';
        
        // Test 5: Test Hotel Search API (sample search)
        echo '<div class="test-section">';
        echo '<h2>Test 5: Hotel Search API (Sample Search)</h2>';
        
        try {
            $search_params = array(
                'city_code' => '130443', // Mumbai
                'check_in' => date('Y-m-d', strtotime('+1 day')),
                'check_out' => date('Y-m-d', strtotime('+2 days')),
                'rooms' => 1,
                'adults' => 2,
                'children' => 0
            );
            
            $search_results = tbo_hotels_search_hotels($search_params);
            if ($search_results && isset($search_results['Hotels']) && count($search_results['Hotels']) > 0) {
                echo '<div class="success">✅ Hotel Search API working - Found ' . count($search_results['Hotels']) . ' hotels</div>';
                echo '<div class="info">Search Parameters: ' . json_encode($search_params, JSON_PRETTY_PRINT) . '</div>';
                echo '<pre>Sample Result: ' . json_encode($search_results['Hotels'][0], JSON_PRETTY_PRINT) . '</pre>';
            } else {
                echo '<div class="error">❌ Hotel Search API returned no results</div>';
                echo '<pre>Response: ' . json_encode($search_results, JSON_PRETTY_PRINT) . '</pre>';
            }
        } catch (Exception $e) {
            echo '<div class="error">❌ Hotel Search API error: ' . $e->getMessage() . '</div>';
        }
        echo '</div>';
        
        // Test 6: File Structure Check
        echo '<div class="test-section">';
        echo '<h2>Test 6: File Structure</h2>';
        
        $required_files = array(
            'functions.php' => WP_CONTENT_DIR . '/themes/tbo-hotels/functions.php',
            'hotel-search.js' => WP_CONTENT_DIR . '/themes/tbo-hotels/assets/js/hotel-search.js',
            'hotel-search.css' => WP_CONTENT_DIR . '/themes/tbo-hotels/assets/css/hotel-search.css',
            'hotel-search.php' => WP_CONTENT_DIR . '/themes/tbo-hotels/templates/hotel-search.php'
        );
        
        $all_files_exist = true;
        foreach ($required_files as $name => $path) {
            if (file_exists($path)) {
                echo '<div class="success">✅ ' . $name . ' exists</div>';
            } else {
                echo '<div class="error">❌ ' . $name . ' missing at: ' . $path . '</div>';
                $all_files_exist = false;
            }
        }
        
        if ($all_files_exist) {
            echo '<div class="success">✅ All required files are present</div>';
        }
        echo '</div>';
        ?>
        
        <div class="test-section info">
            <h2>Next Steps</h2>
            <p>If all tests are passing, you can now:</p>
            <ul>
                <li>Visit your WordPress admin and create a new page using the "Hotel Search" template</li>
                <li>Test the live search functionality</li>
                <li>Customize the styling as needed</li>
                <li>Implement booking functionality</li>
            </ul>
        </div>
    </div>
</body>
</html>