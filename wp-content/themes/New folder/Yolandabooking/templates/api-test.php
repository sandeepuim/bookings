<?php
/**
 * Template Name: TBO API Test
 * 
 * A test page for TBO API connectivity.
 * Only administrators can view this page.
 */

// Restrict access to admins only
if (!current_user_can('administrator')) {
    wp_redirect(home_url());
    exit;
}

get_header();

require_once get_template_directory() . '/inc/TboApiClient.php';

// Create API client
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI',
    'YOLANDATHTest',
    'Yol@40360746'
);

?>

<div class="container api-test-page">
    <h1>TBO API Test</h1>
    <p>This page tests the connection to the TBO API and displays the results.</p>

    <div class="test-results">
        <h2>API Connection Tests</h2>
        
        <?php
        // Test 1: Country List
        echo '<h3>Test 1: Get Countries</h3>';
        try {
            $countries = $tbo->getCountries();
            echo '<div class="test-success">';
            echo '<p>✅ Successfully connected to TBO API and retrieved country list.</p>';
            echo '<p>Found ' . count($countries) . ' countries.</p>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="test-error">';
            echo '<p>❌ Failed to retrieve countries: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        
        // Test 2: Get Cities for India
        echo '<h3>Test 2: Get Cities for India (IN)</h3>';
        try {
            $cities = $tbo->getCities('IN');
            echo '<div class="test-success">';
            echo '<p>✅ Successfully retrieved cities for India.</p>';
            echo '<p>Found ' . count($cities) . ' cities.</p>';
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="test-error">';
            echo '<p>❌ Failed to retrieve cities: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        
        // Test 3: Get Hotel Codes for Delhi
        echo '<h3>Test 3: Get Hotel Codes for Delhi</h3>';
        try {
            // Try to find Delhi's city code first
            $delhiCode = '130443'; // Default code for Delhi
            
            // If we have cities from Test 2, find Delhi's code
            if (isset($cities) && is_array($cities)) {
                foreach ($cities as $city) {
                    if (isset($city['Name']) && stripos($city['Name'], 'Delhi') !== false) {
                        $delhiCode = $city['Code'];
                        break;
                    }
                }
            }
            
            $hotelCodes = $tbo->getHotelCodes($delhiCode);
            echo '<div class="test-success">';
            echo '<p>✅ Successfully retrieved hotel codes for Delhi (Code: ' . $delhiCode . ').</p>';
            
            if (isset($hotelCodes['HotelCodes']) && is_array($hotelCodes['HotelCodes'])) {
                echo '<p>Found ' . count($hotelCodes['HotelCodes']) . ' hotels.</p>';
            } else {
                echo '<p>No hotels found, but API call was successful.</p>';
            }
            
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="test-error">';
            echo '<p>❌ Failed to retrieve hotel codes: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        
        // Test 4: Search Hotels
        echo '<h3>Test 4: Search Hotels</h3>';
        try {
            // Set up test search parameters
            $checkIn = date('Y-m-d', strtotime('+7 days'));
            $checkOut = date('Y-m-d', strtotime('+10 days'));
            
            echo '<p>Test parameters:</p>';
            echo '<ul>';
            echo '<li>Country: India (IN)</li>';
            echo '<li>City: Delhi (' . ($delhiCode ?? '130443') . ')</li>';
            echo '<li>Check-in: ' . $checkIn . '</li>';
            echo '<li>Check-out: ' . $checkOut . '</li>';
            echo '<li>Rooms: 1</li>';
            echo '<li>Adults: 2</li>';
            echo '<li>Children: 0</li>';
            echo '</ul>';
            
            $searchResults = $tbo->searchHotels('IN', $delhiCode ?? '130443', $checkIn, $checkOut, 1, 2, 0);
            
            echo '<div class="test-success">';
            echo '<p>✅ Successfully searched for hotels.</p>';
            
            if (isset($searchResults['Hotels']) && is_array($searchResults['Hotels'])) {
                echo '<p>Found ' . count($searchResults['Hotels']) . ' hotels.</p>';
                
                // Show first hotel as example
                if (count($searchResults['Hotels']) > 0) {
                    $firstHotel = $searchResults['Hotels'][0];
                    echo '<p>Example hotel: ' . esc_html($firstHotel['HotelName'] ?? 'Unknown') . '</p>';
                }
            } else {
                echo '<p>No hotels found in search results, but API call was successful.</p>';
            }
            
            echo '</div>';
        } catch (Exception $e) {
            echo '<div class="test-error">';
            echo '<p>❌ Failed to search hotels: ' . $e->getMessage() . '</p>';
            echo '</div>';
        }
        ?>
    </div>
    
    <div class="test-summary">
        <h2>Test Summary</h2>
        <?php
        // Count successes and failures
        $totalTests = 4;
        $successCount = 0;
        
        if (isset($countries)) $successCount++;
        if (isset($cities)) $successCount++;
        if (isset($hotelCodes)) $successCount++;
        if (isset($searchResults)) $successCount++;
        
        $failCount = $totalTests - $successCount;
        
        echo '<p>' . $successCount . ' out of ' . $totalTests . ' tests passed.</p>';
        
        if ($successCount === $totalTests) {
            echo '<div class="summary-success">All tests passed! The TBO API integration is working correctly.</div>';
        } else if ($successCount > 0) {
            echo '<div class="summary-partial">Some tests passed. Please check the errors above and fix the issues.</div>';
        } else {
            echo '<div class="summary-failure">All tests failed. There appears to be a problem with the TBO API integration.</div>';
        }
        ?>
    </div>
    
    <div class="debug-info">
        <h2>Debug Information</h2>
        <p>The following information may be helpful for troubleshooting:</p>
        
        <h3>WordPress Info</h3>
        <ul>
            <li>WordPress Version: <?php echo get_bloginfo('version'); ?></li>
            <li>PHP Version: <?php echo phpversion(); ?></li>
            <li>Theme: <?php echo wp_get_theme()->get('Name'); ?> (<?php echo wp_get_theme()->get('Version'); ?>)</li>
        </ul>
        
        <h3>Server Info</h3>
        <ul>
            <li>Server Software: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></li>
            <li>User Agent: <?php echo $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'; ?></li>
        </ul>
        
        <h3>API Configuration</h3>
        <ul>
            <li>API URL: http://api.tbotechnology.in/TBOHolidays_HotelAPI</li>
            <li>Username: YOLANDATHTest</li>
            <li>Password: [Hidden]</li>
        </ul>
    </div>
    
    <div class="back-to-dashboard">
        <a href="<?php echo admin_url(); ?>" class="button">Back to Dashboard</a>
    </div>
</div>

<style>
.api-test-page {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
}

.test-results h3 {
    margin-top: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.test-success {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.test-error {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.summary-success {
    background-color: #d4edda;
    color: #155724;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-weight: bold;
}

.summary-partial {
    background-color: #fff3cd;
    color: #856404;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-weight: bold;
}

.summary-failure {
    background-color: #f8d7da;
    color: #721c24;
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-weight: bold;
}

.debug-info {
    margin-top: 30px;
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
}

.back-to-dashboard {
    margin-top: 30px;
    text-align: center;
}

.button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #0073aa;
    color: #fff;
    text-decoration: none;
    border-radius: 4px;
}

.button:hover {
    background-color: #005177;
    color: #fff;
}
</style>

<?php get_footer(); ?>
