<?php
/**
 * TBO Hotels Test Script
 * 
 * This file is a standalone test script that checks:
 * 1. API connectivity
 * 2. JavaScript error handling
 * 3. Hotel results display
 */

// Define ABSPATH to allow WordPress functions
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(dirname(__FILE__)) . '/');
}

// Load WordPress core without themes
define('WP_USE_THEMES', false);
require_once(ABSPATH . 'wp-load.php');

// Security - only allow access from localhost
if (!in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1'))) {
    die('Access denied for security reasons.');
}

// Set headers
header('Content-Type: text/html; charset=utf-8');

// Get test parameters
$city_id = isset($_GET['city_id']) ? sanitize_text_field($_GET['city_id']) : '150184'; // Default: New York
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : date('Y-m-d', strtotime('+7 days'));
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : date('Y-m-d', strtotime('+10 days'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

// API credentials
$api_username = get_option('tbo_api_username', 'YOLANDATHTest');
$api_password = get_option('tbo_api_password', 'Yol@40360746');
$api_url = 'https://api.tbotechnology.in/TBOHotelAPI/HotelService.svc/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        .card {
            margin-bottom: 20px;
        }
        pre {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            overflow: auto;
        }
        .hotel-item {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            padding: 15px;
        }
        .hotel-image img {
            max-width: 100%;
            border-radius: 5px;
        }
        .test-controls {
            position: sticky;
            top: 0;
            background: white;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
            z-index: 100;
        }
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #3498db;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    
    <!-- Error fix script - inline for fastest execution -->
    <script>
    // TBO Hotels Test Error Fix
    (function() {
        console.log('TBO Hotels Test Error Fix: Active');
        
        // Prevent syntax errors from breaking execution
        window.addEventListener('error', function(event) {
            if (event && event.message) {
                console.warn('TBO Error Caught:', event.message);
                
                if (event.message.includes('Unexpected token') || 
                    event.message.includes('missing ) after argument list')) {
                    event.preventDefault();
                    return true;
                }
            }
        }, true);
        
        // Fix try/catch without parameter
        function fixScript(script) {
            if (!script || !script.textContent) return;
            
            var content = script.textContent;
            
            // Fix catch blocks without parameters
            if (content.includes('try') && content.includes('catch')) {
                var fixedContent = content.replace(/catch\s*{/g, 'catch(e) {');
                
                if (fixedContent !== content) {
                    var newScript = document.createElement('script');
                    newScript.textContent = fixedContent;
                    script.parentNode.replaceChild(newScript, script);
                }
            }
        }
        
        // Run when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('script:not([src])').forEach(fixScript);
        });
    })();
    </script>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">TBO Hotels Test Script</h1>
        
        <div class="test-controls card">
            <div class="card-body">
                <h5 class="card-title">Test Controls</h5>
                
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <label for="city_id" class="form-label">City ID</label>
                        <input type="text" class="form-control" id="city_id" name="city_id" value="<?php echo esc_attr($city_id); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="check_in" class="form-label">Check In</label>
                        <input type="date" class="form-control" id="check_in" name="check_in" value="<?php echo esc_attr($check_in); ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="check_out" class="form-label">Check Out</label>
                        <input type="date" class="form-control" id="check_out" name="check_out" value="<?php echo esc_attr($check_out); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="adults" class="form-label">Adults</label>
                        <input type="number" class="form-control" id="adults" name="adults" value="<?php echo esc_attr($adults); ?>">
                    </div>
                    <div class="col-md-2">
                        <label for="children" class="form-label">Children</label>
                        <input type="number" class="form-control" id="children" name="children" value="<?php echo esc_attr($children); ?>">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="debug" name="debug" value="1" <?php checked($debug); ?>>
                            <label class="form-check-label" for="debug">Debug Mode</label>
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Run Test</button>
                        <button type="button" id="runJsTest" class="btn btn-secondary ms-2">Run JS Test</button>
                        <button type="button" id="checkCache" class="btn btn-info ms-2">Check Cache</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>API Test</h5>
                    </div>
                    <div class="card-body">
                        <div id="api-results">
                            <div class="loading">
                                <div class="spinner"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5>JavaScript Test</h5>
                    </div>
                    <div class="card-body">
                        <div id="js-results">
                            <p>Click "Run JS Test" to check for JavaScript errors.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h5>Hotel Results Test</h5>
            </div>
            <div class="card-body">
                <div id="hotel-results">
                    <div class="loading">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($debug): ?>
        <div class="card">
            <div class="card-header">
                <h5>Debug Information</h5>
            </div>
            <div class="card-body">
                <h6>PHP Version</h6>
                <pre><?php echo phpversion(); ?></pre>
                
                <h6>WordPress Version</h6>
                <pre><?php echo get_bloginfo('version'); ?></pre>
                
                <h6>Installed Plugins</h6>
                <pre><?php 
                    $active_plugins = get_option('active_plugins');
                    foreach($active_plugins as $plugin) {
                        echo $plugin . "\n";
                    }
                ?></pre>
                
                <h6>Theme Information</h6>
                <pre><?php 
                    $theme = wp_get_theme();
                    echo "Theme Name: " . $theme->get('Name') . "\n";
                    echo "Theme Version: " . $theme->get('Version') . "\n";
                    echo "Theme Author: " . $theme->get('Author') . "\n";
                ?></pre>
                
                <h6>Server Information</h6>
                <pre><?php echo $_SERVER['SERVER_SOFTWARE']; ?></pre>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Track JavaScript errors
        var jsErrors = [];
        var originalErrorHandler = window.onerror;
        
        window.onerror = function(message, file, line, col, error) {
            jsErrors.push({
                message: message,
                file: file,
                line: line,
                col: col
            });
            
            if (originalErrorHandler) {
                return originalErrorHandler(message, file, line, col, error);
            }
            
            return true;
        };
        
        // Run API test
        function runApiTest() {
            $('#api-results').html('<div class="loading"><div class="spinner"></div></div>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'tbo_test_api_connection',
                    city_id: $('#city_id').val(),
                    check_in: $('#check_in').val(),
                    check_out: $('#check_out').val(),
                    adults: $('#adults').val(),
                    children: $('#children').val()
                },
                success: function(response) {
                    var html = '<div class="alert ' + (response.success ? 'alert-success' : 'alert-danger') + '">' +
                               '<strong>' + (response.success ? 'Success' : 'Error') + ':</strong> ' + 
                               response.message + '</div>';
                    
                    if (response.data) {
                        html += '<h6>API Response</h6>' +
                                '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                    }
                    
                    $('#api-results').html(html);
                },
                error: function(xhr, status, error) {
                    $('#api-results').html(
                        '<div class="alert alert-danger">' +
                        '<strong>AJAX Error:</strong> ' + status + ' - ' + error +
                        '</div>'
                    );
                }
            });
        }
        
        // Run JavaScript test
        function runJsTest() {
            $('#js-results').html('<div class="loading"><div class="spinner"></div></div>');
            
            // Clear previous errors
            jsErrors = [];
            
            // Run tests that might cause errors
            try {
                // Test 1: Empty catch block
                try {
                    throw new Error('Test error');
                } catch {
                    console.log('Caught test error');
                }
                
                // Test 2: Missing parenthesis
                (function(a, b) {
                    return a + b;
                }(1, 2);
                
                // Test 3: Function with trailing comma
                (function(a, b,) {
                    return a + b;
                })(3, 4);
                
            } catch (e) {
                jsErrors.push({
                    message: 'Test execution error: ' + e.message,
                    file: 'internal',
                    line: 0
                });
            }
            
            // Display results
            setTimeout(function() {
                var html = '';
                
                if (jsErrors.length === 0) {
                    html = '<div class="alert alert-success">' +
                           '<strong>Success:</strong> No JavaScript errors detected.' +
                           '</div>';
                } else {
                    html = '<div class="alert alert-danger">' +
                           '<strong>Error:</strong> ' + jsErrors.length + ' JavaScript errors detected.' +
                           '</div>' +
                           '<h6>Errors</h6>' +
                           '<pre>';
                    
                    jsErrors.forEach(function(error, index) {
                        html += (index + 1) + '. ' + error.message + 
                                ' (at ' + error.file + ':' + error.line + ')\n';
                    });
                    
                    html += '</pre>';
                }
                
                $('#js-results').html(html);
            }, 1000);
        }
        
        // Run hotel results test
        function runHotelResultsTest() {
            $('#hotel-results').html('<div class="loading"><div class="spinner"></div></div>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'tbo_test_hotel_results',
                    city_id: $('#city_id').val(),
                    check_in: $('#check_in').val(),
                    check_out: $('#check_out').val(),
                    adults: $('#adults').val(),
                    children: $('#children').val()
                },
                success: function(response) {
                    if (response.success) {
                        var html = '<div class="alert alert-success">' +
                                   '<strong>Success:</strong> Found ' + response.data.hotels.length + ' hotels.' +
                                   '</div>';
                        
                        // Display hotels
                        response.data.hotels.slice(0, 5).forEach(function(hotel) {
                            html += createHotelCard(hotel);
                        });
                        
                        if (response.data.hotels.length > 5) {
                            html += '<div class="alert alert-info">Showing 5 of ' + response.data.hotels.length + ' hotels.</div>';
                        }
                        
                        $('#hotel-results').html(html);
                    } else {
                        $('#hotel-results').html(
                            '<div class="alert alert-danger">' +
                            '<strong>Error:</strong> ' + response.message +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('#hotel-results').html(
                        '<div class="alert alert-danger">' +
                        '<strong>AJAX Error:</strong> ' + status + ' - ' + error +
                        '</div>'
                    );
                }
            });
        }
        
        // Check cache status
        function checkCache() {
            $('#api-results').html('<div class="loading"><div class="spinner"></div></div>');
            
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'tbo_test_check_cache'
                },
                success: function(response) {
                    var html = '<div class="alert ' + (response.success ? 'alert-success' : 'alert-warning') + '">' +
                               '<strong>' + (response.success ? 'Success' : 'Warning') + ':</strong> ' + 
                               response.message + '</div>';
                    
                    if (response.data) {
                        html += '<h6>Cache Status</h6>' +
                                '<pre>' + JSON.stringify(response.data, null, 2) + '</pre>';
                    }
                    
                    $('#api-results').html(html);
                },
                error: function(xhr, status, error) {
                    $('#api-results').html(
                        '<div class="alert alert-danger">' +
                        '<strong>AJAX Error:</strong> ' + status + ' - ' + error +
                        '</div>'
                    );
                }
            });
        }
        
        // Create hotel card HTML
        function createHotelCard(hotel) {
            var html = '<div class="hotel-item">' +
                       '<div class="row">';
            
            // Hotel image
            html += '<div class="col-md-4 hotel-image">';
            if (hotel.HotelPicture) {
                html += '<img src="' + hotel.HotelPicture + '" alt="' + hotel.HotelName + '" class="img-fluid">';
            } else {
                html += '<div class="no-image">No Image Available</div>';
            }
            html += '</div>';
            
            // Hotel details
            html += '<div class="col-md-8">' +
                    '<h5>' + hotel.HotelName + '</h5>';
            
            // Star rating
            if (hotel.StarRating) {
                html += '<div class="stars">';
                for (var i = 0; i < hotel.StarRating; i++) {
                    html += '⭐';
                }
                html += '</div>';
            }
            
            // Location
            if (hotel.Address) {
                html += '<p><strong>Address:</strong> ' + hotel.Address + '</p>';
            }
            
            // Price
            if (hotel.Price) {
                html += '<p><strong>Price:</strong> ' + hotel.Price.CurrencyCode + ' ' + 
                        hotel.Price.OfferedPrice.toFixed(2) + ' for ' + hotel.Price.Nights + ' night(s)</p>';
            }
            
            html += '</div></div></div>';
            
            return html;
        }
        
        // Event handlers
        $('#runJsTest').click(runJsTest);
        $('#checkCache').click(checkCache);
        
        // Run tests on page load
        runApiTest();
        runHotelResultsTest();
    });
    </script>
</body>
</html>
<?php

// Add AJAX handlers for tests
add_action('wp_ajax_tbo_test_api_connection', 'tbo_test_api_connection');
add_action('wp_ajax_nopriv_tbo_test_api_connection', 'tbo_test_api_connection');

add_action('wp_ajax_tbo_test_hotel_results', 'tbo_test_hotel_results');
add_action('wp_ajax_nopriv_tbo_test_hotel_results', 'tbo_test_hotel_results');

add_action('wp_ajax_tbo_test_check_cache', 'tbo_test_check_cache');
add_action('wp_ajax_nopriv_tbo_test_check_cache', 'tbo_test_check_cache');

/**
 * Test API connection
 */
function tbo_test_api_connection() {
    // Get parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : '150184';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : date('Y-m-d', strtotime('+7 days'));
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : date('Y-m-d', strtotime('+10 days'));
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    
    // API credentials
    $api_username = get_option('tbo_api_username', 'YOLANDATHTest');
    $api_password = get_option('tbo_api_password', 'Yol@40360746');
    $api_url = 'https://api.tbotechnology.in/TBOHotelAPI/HotelService.svc/HotelSearch';
    
    // Authentication
    $auth_timestamp = time();
    $auth_signature = base64_encode(hash_hmac('sha256', $api_username . $auth_timestamp, $api_password, true));
    
    // Request data
    $request_data = array(
        'CheckIn' => $check_in,
        'CheckOut' => $check_out,
        'DestinationCode' => $city_id,
        'Nationality' => 'IN',
        'RoomGuests' => array(
            array(
                'AdultCount' => $adults,
                'ChildCount' => $children
            )
        ),
        'ResultCount' => 10,
        'Filters' => array(
            'HotelType' => array('All'),
            'StarRating' => array('All')
        )
    );
    
    // Headers
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Username' => $api_username,
        'X-Timestamp' => $auth_timestamp,
        'X-Signature' => $auth_signature
    );
    
    // Make API request
    $start_time = microtime(true);
    
    $response = wp_remote_post($api_url, array(
        'headers' => $headers,
        'body' => json_encode($request_data),
        'timeout' => 30,
        'sslverify' => false
    ));
    
    $end_time = microtime(true);
    $response_time = round(($end_time - $start_time) * 1000); // in milliseconds
    
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(
            array(
                'message' => 'API Error: ' . $response->get_error_message(),
                'time' => $response_time
            )
        );
        exit;
    }
    
    // Parse response
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    // Check response code
    if ($response_code != 200) {
        wp_send_json_error(
            array(
                'message' => 'API returned status code ' . $response_code,
                'time' => $response_time,
                'data' => $data
            )
        );
        exit;
    }
    
    // Check for hotels
    if (!isset($data['Hotels']) || !is_array($data['Hotels'])) {
        wp_send_json_error(
            array(
                'message' => 'No hotels found or invalid response format',
                'time' => $response_time,
                'data' => $data
            )
        );
        exit;
    }
    
    // Success
    wp_send_json_success(
        array(
            'message' => 'API request successful. Found ' . count($data['Hotels']) . ' hotels in ' . $response_time . 'ms',
            'time' => $response_time,
            'data' => array(
                'total' => isset($data['TotalHotels']) ? $data['TotalHotels'] : count($data['Hotels']),
                'hotel_count' => count($data['Hotels']),
                'first_hotel' => !empty($data['Hotels']) ? $data['Hotels'][0] : null
            )
        )
    );
    exit;
}

/**
 * Test hotel results
 */
function tbo_test_hotel_results() {
    // Get parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : '150184';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : date('Y-m-d', strtotime('+7 days'));
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : date('Y-m-d', strtotime('+10 days'));
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    
    // Check for enhanced API function
    if (function_exists('tbo_enhanced_get_hotels')) {
        $rooms = array(array('adults' => $adults, 'children' => $children));
        $result = tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms);
        
        if ($result['success']) {
            wp_send_json_success(array(
                'hotels' => $result['hotels'],
                'total' => $result['total'],
                'using_enhanced' => true
            ));
        } else {
            wp_send_json_error(array(
                'message' => isset($result['error']) ? $result['error'] : 'Unknown error',
                'using_enhanced' => true
            ));
        }
        exit;
    }
    
    // Fall back to regular API request
    $api_username = get_option('tbo_api_username', 'YOLANDATHTest');
    $api_password = get_option('tbo_api_password', 'Yol@40360746');
    $api_url = 'https://api.tbotechnology.in/TBOHotelAPI/HotelService.svc/HotelSearch';
    
    // Authentication
    $auth_timestamp = time();
    $auth_signature = base64_encode(hash_hmac('sha256', $api_username . $auth_timestamp, $api_password, true));
    
    // Request data
    $request_data = array(
        'CheckIn' => $check_in,
        'CheckOut' => $check_out,
        'DestinationCode' => $city_id,
        'Nationality' => 'IN',
        'RoomGuests' => array(
            array(
                'AdultCount' => $adults,
                'ChildCount' => $children
            )
        ),
        'ResultCount' => 10,
        'Filters' => array(
            'HotelType' => array('All'),
            'StarRating' => array('All')
        )
    );
    
    // Headers
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Username' => $api_username,
        'X-Timestamp' => $auth_timestamp,
        'X-Signature' => $auth_signature
    );
    
    // Make API request
    $response = wp_remote_post($api_url, array(
        'headers' => $headers,
        'body' => json_encode($request_data),
        'timeout' => 30,
        'sslverify' => false
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        wp_send_json_error(array(
            'message' => 'API Error: ' . $response->get_error_message(),
            'using_enhanced' => false
        ));
        exit;
    }
    
    // Parse response
    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    $data = json_decode($response_body, true);
    
    // Check response code
    if ($response_code != 200) {
        wp_send_json_error(array(
            'message' => 'API returned status code ' . $response_code,
            'using_enhanced' => false
        ));
        exit;
    }
    
    // Check for hotels
    if (!isset($data['Hotels']) || !is_array($data['Hotels'])) {
        wp_send_json_error(array(
            'message' => 'No hotels found or invalid response format',
            'using_enhanced' => false
        ));
        exit;
    }
    
    // Success
    wp_send_json_success(array(
        'hotels' => $data['Hotels'],
        'total' => isset($data['TotalHotels']) ? $data['TotalHotels'] : count($data['Hotels']),
        'using_enhanced' => false
    ));
    exit;
}

/**
 * Check cache status
 */
function tbo_test_check_cache() {
    global $wpdb;
    
    // Check for transients
    $transients = $wpdb->get_results(
        "SELECT option_name, option_value 
        FROM $wpdb->options 
        WHERE option_name LIKE '_transient_tbo_api_%' 
        ORDER BY option_id DESC 
        LIMIT 20"
    );
    
    // Check for stale cache
    $stale_cache = $wpdb->get_results(
        "SELECT option_name, option_value 
        FROM $wpdb->options 
        WHERE option_name LIKE '%tbo_api_%_stale' 
        ORDER BY option_id DESC 
        LIMIT 10"
    );
    
    if (empty($transients) && empty($stale_cache)) {
        wp_send_json_error(array(
            'message' => 'No TBO API cache found',
            'data' => null
        ));
        exit;
    }
    
    // Format data
    $cache_data = array(
        'transient_count' => count($transients),
        'stale_count' => count($stale_cache),
        'transients' => array(),
        'stale_cache' => array()
    );
    
    foreach ($transients as $transient) {
        $key = str_replace('_transient_', '', $transient->option_name);
        $size = strlen($transient->option_value);
        
        $cache_data['transients'][] = array(
            'key' => $key,
            'size' => $size,
            'size_formatted' => size_format($size, 2)
        );
    }
    
    foreach ($stale_cache as $cache) {
        $size = strlen($cache->option_value);
        
        $cache_data['stale_cache'][] = array(
            'key' => $cache->option_name,
            'size' => $size,
            'size_formatted' => size_format($size, 2)
        );
    }
    
    wp_send_json_success(array(
        'message' => 'Found ' . count($transients) . ' transients and ' . count($stale_cache) . ' stale cache entries',
        'data' => $cache_data
    ));
    exit;
}