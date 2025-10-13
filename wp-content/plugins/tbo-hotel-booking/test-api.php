<?php
/**
 * TBO API Test Page
 * 
 * This file is for testing the TBO API connection and functionality.
 * Please access this page directly to test the API connection.
 */

// Find and load WordPress - more reliable than relative paths
$wp_load_file = dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-load.php';
if (file_exists($wp_load_file)) {
    require_once($wp_load_file);
} else {
    die('Could not find WordPress. Please run this file through the WordPress admin.');
}

// Check if user is an admin
if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
}

// Make sure error reporting is on for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if we're viewing logs
if (isset($_GET['action']) && $_GET['action'] === 'view_logs') {
    // Show logs page
    include_once(plugin_dir_path(__FILE__) . 'includes/class-tbo-hotel-booking-logger.php');
    $logger = new TBO_Hotel_Booking_Logger();
    $log_contents = $logger->get_log_contents();
    
    // Display logs with formatting
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>TBO API Debug Logs</title>
        <style>
            body {
                font-family: monospace;
                background: #f8f8f8;
                padding: 20px;
                font-size: 13px;
                line-height: 1.5;
            }
            h1 {
                font-family: Arial, sans-serif;
                margin-bottom: 20px;
            }
            .log-container {
                background: #fff;
                border: 1px solid #ddd;
                padding: 20px;
                overflow: auto;
                max-height: 800px;
                white-space: pre-wrap;
                word-wrap: break-word;
            }
            .log-actions {
                margin-bottom: 20px;
            }
            .log-actions a {
                display: inline-block;
                padding: 8px 16px;
                background: #0073aa;
                color: #fff;
                text-decoration: none;
                border-radius: 3px;
                margin-right: 10px;
            }
            .log-actions a:hover {
                background: #005177;
            }
            .back {
                margin-top: 20px;
                display: inline-block;
            }
            .error {
                color: #dc3232;
            }
            .debug {
                color: #007bff;
            }
            .info {
                color: #46b450;
            }
        </style>
    </head>
    <body>
        <h1>TBO API Debug Logs</h1>
        
        <div class="log-actions">
            <a href="' . admin_url('admin.php?page=tbo-hotel-booking-settings') . '">Back to Settings</a>
            <a href="' . admin_url('admin.php?page=tbo-hotel-booking-settings&action=clear_logs&_wpnonce=' . wp_create_nonce('clear_logs')) . '" onclick="return confirm(\'Are you sure you want to clear the logs?\');">Clear Logs</a>
            <a href="' . admin_url('admin.php?page=tbo-hotel-booking-api-test') . '">Run API Tests</a>
        </div>
        
        <div class="log-container">';
        
        if (empty($log_contents)) {
            echo 'No logs found.';
        } else {
            // Highlight log levels
            $log_contents = preg_replace('/\[ERROR\]/', '<span class="error">[ERROR]</span>', $log_contents);
            $log_contents = preg_replace('/\[DEBUG\]/', '<span class="debug">[DEBUG]</span>', $log_contents);
            $log_contents = preg_replace('/\[INFO\]/', '<span class="info">[INFO]</span>', $log_contents);
            
            echo $log_contents;
        }
        
        echo '</div>
        
        <a href="' . admin_url('admin.php?page=tbo-hotel-booking-settings') . '" class="back">Back to Settings</a>
    </body>
    </html>';
    
    exit;
}

// Process clear logs action
if (isset($_GET['action']) && $_GET['action'] === 'clear_logs' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'clear_logs')) {
    include_once(plugin_dir_path(__FILE__) . 'includes/class-tbo-hotel-booking-logger.php');
    $logger = new TBO_Hotel_Booking_Logger();
    $logger->clear_log();
    
    // Redirect back
    $redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : admin_url('admin.php?page=tbo-hotel-booking-settings');
    wp_redirect($redirect_url);
    exit;
}

// Set up page
?>
<!DOCTYPE html>
<html>
<head>
    <title>TBO API Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            color: #0073aa;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 4px solid #0073aa;
        }
        .test-section h2 {
            margin-top: 0;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .test-params {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-family: monospace;
        }
        .test-result {
            background: #fff;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 15px;
            max-height: 500px;
            overflow: auto;
        }
        pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .success {
            color: green;
            font-weight: bold;
        }
        .error {
            color: red;
            font-weight: bold;
        }
        .warning {
            color: #f90;
            font-weight: bold;
        }
        .info {
            color: #0073aa;
        }
        button {
            background: #0073aa;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        button:hover {
            background: #005177;
        }
        .controls {
            margin-bottom: 20px;
        }
        .api-config {
            background: #e9f7ff;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            border-collapse: collapse;
            width: 100%;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>TBO API Connection Test</h1>
    <p>This page tests the connection to the TBO API using your configured credentials.</p>
    
    <?php
    // Load the TBO API class
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/api/class-tbo-hotel-booking-api.php')) {
        require_once(plugin_dir_path(__FILE__) . 'includes/api/class-tbo-hotel-booking-api.php');
    } else {
        echo '<div class="error">Error: API class file not found!</div>';
        die();
    }
    
    // Load the logger class
    if (file_exists(plugin_dir_path(__FILE__) . 'includes/class-tbo-hotel-booking-logger.php')) {
        require_once(plugin_dir_path(__FILE__) . 'includes/class-tbo-hotel-booking-logger.php');
    }
    
    // Display API configuration
    echo '<div class="api-config">';
    echo '<h2>API Configuration</h2>';
    echo '<table>';
    echo '<tr><th>Setting</th><th>Value</th></tr>';
    
    // Create an instance of the API class
    $tbo_api = new TBO_Hotel_Booking_API();
    
    // Use reflection to access private properties
    $reflection = new ReflectionClass($tbo_api);
    
    $api_base_url = $reflection->getProperty('api_base_url');
    $api_base_url->setAccessible(true);
    $api_username = $reflection->getProperty('api_username');
    $api_username->setAccessible(true);
    $api_password = $reflection->getProperty('api_password');
    $api_password->setAccessible(true);
    
    echo '<tr><td>API Base URL</td><td>' . $api_base_url->getValue($tbo_api) . '</td></tr>';
    echo '<tr><td>API Username</td><td>' . $api_username->getValue($tbo_api) . '</td></tr>';
    echo '<tr><td>API Password</td><td>' . str_repeat('*', strlen($api_password->getValue($tbo_api))) . '</td></tr>';
    
    // Get debug mode status
    $settings = get_option('tbo_hotel_booking_settings', array());
    $debug_mode = isset($settings['debug_mode']) ? (bool)$settings['debug_mode'] : false;
    
    echo '<tr><td>Debug Mode</td><td>' . ($debug_mode ? '<span class="success">Enabled</span>' : '<span class="error">Disabled</span>') . '</td></tr>';
    
    // Get cache settings
    $cache_duration = isset($settings['cache_duration']) ? (int)$settings['cache_duration'] : 3600;
    echo '<tr><td>Cache Duration</td><td>' . ($cache_duration > 0 ? $cache_duration . ' seconds' : '<span class="warning">Disabled</span>') . '</td></tr>';
    
    echo '</table>';
    
    // Show debug actions if debug mode is enabled
    if ($debug_mode) {
        echo '<div style="margin-top: 10px;">';
        echo '<a href="' . esc_url(admin_url('admin.php?page=tbo-hotel-booking-api-test&action=view_logs')) . '" class="button button-secondary">View Debug Logs</a>';
        echo '</div>';
    }
    
    echo '</div>';
    
    // Test 1: Test Authentication
    echo '<div class="test-section">';
    echo '<h2>1. Authentication Test</h2>';
    
    try {
        // Get the token using reflection to access the private method
        $method = $reflection->getMethod('authenticate');
        $method->setAccessible(true);
        $token = $method->invoke($tbo_api);
        
        if (!empty($token)) {
            echo '<p class="success">Authentication successful!</p>';
            echo '<p>Token: ' . substr($token, 0, 10) . '...[truncated for security]</p>';
        } else {
            echo '<p class="error">Authentication failed: Empty token returned.</p>';
        }
    } catch (Exception $e) {
        echo '<p class="error">Authentication error: ' . $e->getMessage() . '</p>';
    }
    
    echo '</div>';
    
    // Test 2: Search Hotels
    echo '<div class="test-section">';
    echo '<h2>2. Hotel Search Test</h2>';
    
    // Get parameters from request or use defaults
    $destination = isset($_REQUEST['destination']) ? sanitize_text_field($_REQUEST['destination']) : 'Bangkok';
    $check_in = isset($_REQUEST['check_in']) ? sanitize_text_field($_REQUEST['check_in']) : date('Y-m-d', strtotime('+7 days'));
    $check_out = isset($_REQUEST['check_out']) ? sanitize_text_field($_REQUEST['check_out']) : date('Y-m-d', strtotime('+10 days'));
    $adults = isset($_REQUEST['adults']) ? intval($_REQUEST['adults']) : 2;
    $children = isset($_REQUEST['children']) ? intval($_REQUEST['children']) : 0;
    
    // Display search form
    echo '<form method="get">';
    echo '<div class="test-params">';
    echo '<label>Destination: <input type="text" name="destination" value="' . esc_attr($destination) . '" required></label><br>';
    echo '<label>Check-in: <input type="date" name="check_in" value="' . esc_attr($check_in) . '" required></label><br>';
    echo '<label>Check-out: <input type="date" name="check_out" value="' . esc_attr($check_out) . '" required></label><br>';
    echo '<label>Adults: <input type="number" name="adults" value="' . esc_attr($adults) . '" min="1" max="6" required></label><br>';
    echo '<label>Children: <input type="number" name="children" value="' . esc_attr($children) . '" min="0" max="4"></label><br>';
    echo '<button type="submit">Search Hotels</button>';
    echo '</div>';
    echo '</form>';
    
    echo '<p>Searching for hotels in <strong>' . esc_html($destination) . '</strong> from <strong>' . esc_html($check_in) . '</strong> to <strong>' . esc_html($check_out) . '</strong> for <strong>' . esc_html($adults) . ' adults</strong> and <strong>' . esc_html($children) . ' children</strong>.</p>';
    
    // Only run the search if the form was submitted
    if (isset($_REQUEST['destination'])) {
        try {
            // Search for hotels
            $hotels = $tbo_api->search_hotels($destination, $check_in, $check_out, $adults, $children);
            
            echo '<p class="success">Search request successful!</p>';
            echo '<div class="test-result">';
            
            // Check if we have hotels in the response
            if (isset($hotels['HotelSearchResponse']) && isset($hotels['HotelSearchResponse']['HotelResults'])) {
                $hotel_results = $hotels['HotelSearchResponse']['HotelResults'];
                
                echo '<p class="info">Found ' . count($hotel_results) . ' hotels</p>';
                
                // Display hotel summary table
                echo '<table>';
                echo '<tr>';
                echo '<th>Hotel Name</th>';
                echo '<th>Category</th>';
                echo '<th>Location</th>';
                echo '<th>Price From</th>';
                echo '</tr>';
                
                foreach ($hotel_results as $hotel) {
                    echo '<tr>';
                    echo '<td>' . esc_html($hotel['HotelInfo']['HotelName']) . '</td>';
                    echo '<td>' . esc_html($hotel['HotelInfo']['HotelRating']) . ' Star</td>';
                    echo '<td>' . esc_html($hotel['HotelInfo']['HotelAddress']) . '</td>';
                    
                    // Get minimum price if available
                    $price = '';
                    if (isset($hotel['MinHotelPrice'])) {
                        $price = $hotel['MinHotelPrice']['Currency'] . ' ' . $hotel['MinHotelPrice']['TotalPrice'];
                    }
                    echo '<td>' . esc_html($price) . '</td>';
                    
                    echo '</tr>';
                }
                
                echo '</table>';
                
                // Show raw response data (collapsed)
                echo '<details>';
                echo '<summary>View Raw Response Data</summary>';
                echo '<pre>';
                print_r($hotels);
                echo '</pre>';
                echo '</details>';
            } else {
                echo '<p class="warning">No hotels found or unexpected response format.</p>';
                echo '<pre>';
                print_r($hotels);
                echo '</pre>';
            }
            
            echo '</div>';
        } catch (Exception $e) {
            echo '<p class="error">Search error: ' . $e->getMessage() . '</p>';
            
            // Display debug logs if debug mode is enabled
            if ($debug_mode && class_exists('TBO_Hotel_Booking_Logger')) {
                $logger = new TBO_Hotel_Booking_Logger();
                $log_contents = $logger->get_log_contents(50); // Get last 50 lines
                
                if (!empty($log_contents)) {
                    echo '<div class="test-result">';
                    echo '<h3>Recent Debug Log</h3>';
                    echo '<pre>';
                    echo esc_html($log_contents);
                    echo '</pre>';
                    echo '</div>';
                }
            }
        }
    }
    
    echo '</div>';
    
    // Additional API endpoints that could be tested in the future
    echo '<div class="test-section">';
    echo '<h2>3. Additional API Tests</h2>';
    echo '<p class="info">The following API endpoints are available for testing but are not implemented in this test page yet:</p>';
    echo '<ul>';
    echo '<li>Hotel Details - Get detailed information about a specific hotel</li>';
    echo '<li>Room Availability - Check availability and pricing for rooms</li>';
    echo '<li>Booking Creation - Create a test booking</li>';
    echo '<li>Booking Details - Retrieve details of an existing booking</li>';
    echo '<li>Cancellation Policy - Get cancellation policy for a room</li>';
    echo '</ul>';
    echo '</div>';
    ?>
    
    <p><a href="<?php echo admin_url('admin.php?page=tbo-hotel-booking'); ?>">Return to Dashboard</a></p>
    
    <script>
    // Add JavaScript for interactive features if needed
    </script>
</body>
</html>
<?php
// End of file
