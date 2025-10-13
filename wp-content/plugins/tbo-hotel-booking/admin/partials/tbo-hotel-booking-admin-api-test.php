<?php
/**
 * Provide a admin area view for the API test
 *
 * This file is used to markup the admin-facing API test page.
 *
 * @package    TBO_Hotel_Booking
 * @subpackage TBO_Hotel_Booking/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Include the API class
require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/api/class-tbo-hotel-booking-api.php';

// Make sure error reporting is on for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('This page tests the connection to the TBO API using your configured credentials.', 'tbo-hotel-booking'); ?></p>
    </div>

    <div class="tbo-api-test">
        <style>
            .tbo-api-test {
                margin-top: 20px;
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
        
        <?php
        // Display API configuration
        echo '<div class="api-config">';
        echo '<h2>' . __('API Configuration', 'tbo-hotel-booking') . '</h2>';
        echo '<table>';
        echo '<tr><th>' . __('Setting', 'tbo-hotel-booking') . '</th><th>' . __('Value', 'tbo-hotel-booking') . '</th></tr>';
        
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
        
        echo '<tr><td>' . __('API Base URL', 'tbo-hotel-booking') . '</td><td>' . $api_base_url->getValue($tbo_api) . '</td></tr>';
        echo '<tr><td>' . __('API Username', 'tbo-hotel-booking') . '</td><td>' . $api_username->getValue($tbo_api) . '</td></tr>';
        echo '<tr><td>' . __('API Password', 'tbo-hotel-booking') . '</td><td>' . str_repeat('*', strlen($api_password->getValue($tbo_api))) . '</td></tr>';
        
        echo '</table>';
        echo '</div>';
        
        // Check for view logs action
        if (isset($_GET['action']) && $_GET['action'] === 'view_logs') {
            echo '<div class="test-section">';
            echo '<h2>' . __('API Debug Logs', 'tbo-hotel-booking') . '</h2>';
            
            if (class_exists('TBO_Hotel_Booking_Logger')) {
                $logger = new TBO_Hotel_Booking_Logger();
                $log_contents = $logger->get_log_contents(100); // Get last 100 lines
                
                echo '<div class="test-result">';
                if (!empty($log_contents)) {
                    echo '<pre>' . esc_html($log_contents) . '</pre>';
                } else {
                    echo '<p class="info">' . __('No logs found or logging is disabled.', 'tbo-hotel-booking') . '</p>';
                }
                echo '</div>';
            } else {
                echo '<p class="error">' . __('Logger class not found.', 'tbo-hotel-booking') . '</p>';
            }
            
            echo '<p><a href="' . admin_url('admin.php?page=tbo-hotel-booking-api-test') . '" class="button">' . __('Back to API Tests', 'tbo-hotel-booking') . '</a></p>';
            echo '</div>';
            
            // Stop here if we're just viewing logs
            echo '</div></div>';
            return;
        }
        
        // Check for clear logs action
        if (isset($_GET['action']) && $_GET['action'] === 'clear_logs' && isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'clear_logs')) {
            if (class_exists('TBO_Hotel_Booking_Logger')) {
                $logger = new TBO_Hotel_Booking_Logger();
                if ($logger->clear_log()) {
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Logs cleared successfully.', 'tbo-hotel-booking') . '</p></div>';
                } else {
                    echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to clear logs.', 'tbo-hotel-booking') . '</p></div>';
                }
            }
        }
        
        // Test 1: Test Authentication
        echo '<div class="test-section">';
        echo '<h2>1. ' . __('Authentication Test', 'tbo-hotel-booking') . '</h2>';
        
        try {
            // Get the token using reflection to access the private method
            $method = $reflection->getMethod('authenticate');
            $method->setAccessible(true);
            $token = $method->invoke($tbo_api);
            
            if (!empty($token)) {
                echo '<p class="success">' . __('Authentication successful!', 'tbo-hotel-booking') . '</p>';
                echo '<p>' . __('Token:', 'tbo-hotel-booking') . ' ' . substr($token, 0, 10) . '...[' . __('truncated for security', 'tbo-hotel-booking') . ']</p>';
            } else {
                echo '<p class="error">' . __('Authentication failed: Empty token returned.', 'tbo-hotel-booking') . '</p>';
            }
        } catch (Exception $e) {
            echo '<p class="error">' . __('Authentication error:', 'tbo-hotel-booking') . ' ' . $e->getMessage() . '</p>';
        }
        
        echo '</div>';
        
        // Test 2: Search Hotels
        echo '<div class="test-section">';
        echo '<h2>2. ' . __('Hotel Search Test', 'tbo-hotel-booking') . '</h2>';
        
        // Get parameters from request or use defaults
        $destination = isset($_REQUEST['destination']) ? sanitize_text_field($_REQUEST['destination']) : 'Delhi';
        $hotel_codes = isset($_REQUEST['hotel_codes']) ? sanitize_text_field($_REQUEST['hotel_codes']) : '1000002';
        $check_in = isset($_REQUEST['check_in']) ? sanitize_text_field($_REQUEST['check_in']) : date('Y-m-d', strtotime('+7 days'));
        $check_out = isset($_REQUEST['check_out']) ? sanitize_text_field($_REQUEST['check_out']) : date('Y-m-d', strtotime('+10 days'));
        $adults = isset($_REQUEST['adults']) ? intval($_REQUEST['adults']) : 2;
        $children = isset($_REQUEST['children']) ? intval($_REQUEST['children']) : 0;
        
        // Display search form
        echo '<form method="get">';
        echo '<input type="hidden" name="page" value="tbo-hotel-booking-api-test">';
        echo '<div class="test-params">';
        echo '<label>' . __('Destination:', 'tbo-hotel-booking') . ' <input type="text" name="destination" value="' . esc_attr($destination) . '"></label><br>';
        echo '<label>' . __('Hotel Codes (comma separated):', 'tbo-hotel-booking') . ' <input type="text" name="hotel_codes" value="' . esc_attr($hotel_codes) . '" required></label><br>';
        echo '<label>' . __('Check-in:', 'tbo-hotel-booking') . ' <input type="date" name="check_in" value="' . esc_attr($check_in) . '" required></label><br>';
        echo '<label>' . __('Check-out:', 'tbo-hotel-booking') . ' <input type="date" name="check_out" value="' . esc_attr($check_out) . '" required></label><br>';
        echo '<label>' . __('Adults:', 'tbo-hotel-booking') . ' <input type="number" name="adults" value="' . esc_attr($adults) . '" min="1" max="6" required></label><br>';
        echo '<label>' . __('Children:', 'tbo-hotel-booking') . ' <input type="number" name="children" value="' . esc_attr($children) . '" min="0" max="4"></label><br>';
        echo '<button type="submit" class="button button-primary">' . __('Search Hotels', 'tbo-hotel-booking') . '</button>';
        echo '</div>';
        echo '</form>';
        
        echo '<p>' . sprintf(__('Searching for hotels in %s (Hotel Codes: %s) from %s to %s for %d adults and %d children.', 'tbo-hotel-booking'), 
                '<strong>' . esc_html($destination) . '</strong>',
                '<strong>' . esc_html($hotel_codes) . '</strong>',
                '<strong>' . esc_html($check_in) . '</strong>', 
                '<strong>' . esc_html($check_out) . '</strong>',
                $adults,
                $children) . '</p>';
        
        // Only run the search if the form was submitted
        if (isset($_REQUEST['destination'])) {
            try {
                // Search for hotels with filters including hotel_codes
                $filters = array(
                    'hotel_codes' => $hotel_codes
                );
                $hotels = $tbo_api->search_hotels($destination, $check_in, $check_out, $adults, $children, $filters);
                
                echo '<p class="success">' . __('Search request successful!', 'tbo-hotel-booking') . '</p>';
                echo '<div class="test-result">';
                
                // Check for error response
                if (isset($hotels['Status']) && isset($hotels['Status']['Code']) && $hotels['Status']['Code'] != 200) {
                    echo '<p class="error">' . __('API Error:', 'tbo-hotel-booking') . ' ';
                    echo esc_html($hotels['Status']['Description'] ?? 'Unknown error') . '</p>';
                    echo '<pre>';
                    print_r($hotels);
                    echo '</pre>';
                }
                // Check for hotels in response
                else if (isset($hotels['Hotels']) && !empty($hotels['Hotels'])) {
                    $hotel_results = $hotels['Hotels'];
                    
                    echo '<p class="info">' . sprintf(__('Found %d hotels', 'tbo-hotel-booking'), count($hotel_results)) . '</p>';
                    
                    // Display hotel summary table
                    echo '<table>';
                    echo '<tr>';
                    echo '<th>' . __('Hotel Code', 'tbo-hotel-booking') . '</th>';
                    echo '<th>' . __('Hotel Name', 'tbo-hotel-booking') . '</th>';
                    echo '<th>' . __('Star Rating', 'tbo-hotel-booking') . '</th>';
                    echo '<th>' . __('Price', 'tbo-hotel-booking') . '</th>';
                    echo '</tr>';
                    
                    foreach ($hotel_results as $hotel) {
                        echo '<tr>';
                        echo '<td>' . esc_html($hotel['HotelCode'] ?? 'N/A') . '</td>';
                        echo '<td>' . esc_html($hotel['HotelName'] ?? 'N/A') . '</td>';
                        echo '<td>' . esc_html($hotel['StarRating'] ?? 'N/A') . ' ' . __('Star', 'tbo-hotel-booking') . '</td>';
                        
                        // Get price if available
                        $price = '';
                        if (isset($hotel['Price'])) {
                            $price = $hotel['Price']['CurrencyCode'] . ' ' . $hotel['Price']['TotalAmount'];
                        }
                        echo '<td>' . esc_html($price) . '</td>';
                        
                        echo '</tr>';
                    }
                    
                    echo '</table>';
                    
                    // Show raw response data (collapsed)
                    echo '<details>';
                    echo '<summary>' . __('View Raw Response Data', 'tbo-hotel-booking') . '</summary>';
                    echo '<pre>';
                    print_r($hotels);
                    echo '</pre>';
                    echo '</details>';
                } else {
                    echo '<p class="warning">' . __('No hotels found or unexpected response format.', 'tbo-hotel-booking') . '</p>';
                    echo '<pre>';
                    print_r($hotels);
                    echo '</pre>';
                }
                
                echo '</div>';
            } catch (Exception $e) {
                echo '<p class="error">' . __('Search error:', 'tbo-hotel-booking') . ' ' . $e->getMessage() . '</p>';
                
                // Check error logs
                if (class_exists('TBO_Hotel_Booking_Logger')) {
                    $logger = new TBO_Hotel_Booking_Logger();
                    echo '<div class="test-result">';
                    echo '<h3>' . __('Recent Error Log', 'tbo-hotel-booking') . '</h3>';
                    echo '<pre>';
                    echo esc_html($logger->get_log_contents(20));
                    echo '</pre>';
                    echo '</div>';
                }
            }
        }
        
        echo '</div>';
        
        // Additional API endpoints that could be tested in the future
        echo '<div class="test-section">';
        echo '<h2>3. ' . __('Additional API Tests', 'tbo-hotel-booking') . '</h2>';
        echo '<p class="info">' . __('The following API endpoints are available for testing but are not implemented in this test page yet:', 'tbo-hotel-booking') . '</p>';
        echo '<ul>';
        echo '<li>' . __('Hotel Details - Get detailed information about a specific hotel', 'tbo-hotel-booking') . '</li>';
        echo '<li>' . __('Room Availability - Check availability and pricing for rooms', 'tbo-hotel-booking') . '</li>';
        echo '<li>' . __('Booking Creation - Create a test booking', 'tbo-hotel-booking') . '</li>';
        echo '<li>' . __('Booking Details - Retrieve details of an existing booking', 'tbo-hotel-booking') . '</li>';
        echo '<li>' . __('Cancellation Policy - Get cancellation policy for a room', 'tbo-hotel-booking') . '</li>';
        echo '</ul>';
        echo '</div>';
        ?>
    </div>
</div>
