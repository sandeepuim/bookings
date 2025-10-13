<?php
/**
 * TBO Hotels API Implementation
 * 
 * Improved implementation of the TBO Hotels API with proper error handling
 * and parameter validation.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced hotel search function with better error handling
 * 
 * @param string $city_id The city ID
 * @param string $check_in Check-in date (YYYY-MM-DD)
 * @param string $check_out Check-out date (YYYY-MM-DD)
 * @param array $rooms Room configuration array
 * @param int $offset Pagination offset
 * @param int $limit Number of results to return
 * @return array Result array with success status and data
 */
function tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms = null, $offset = 0, $limit = 20) {
    // Validate required parameters
    if (empty($city_id)) {
        return array(
            'success' => false,
            'error' => 'City ID is required'
        );
    }
    
    if (empty($check_in) || !validate_date($check_in)) {
        return array(
            'success' => false,
            'error' => 'Valid check-in date is required (YYYY-MM-DD)'
        );
    }
    
    if (empty($check_out) || !validate_date($check_out)) {
        return array(
            'success' => false,
            'error' => 'Valid check-out date is required (YYYY-MM-DD)'
        );
    }
    
    // Validate date range
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    
    if ($check_in_date >= $check_out_date) {
        return array(
            'success' => false,
            'error' => 'Check-out date must be after check-in date'
        );
    }
    
    // Default room configuration if not provided
    if (empty($rooms) || !is_array($rooms)) {
        $rooms = array(
            array(
                'adults' => 2,
                'children' => 0,
                'child_ages' => array()
            )
        );
    }
    
    // Check cache first
    $cache_key = 'tbo_api_hotels_' . $city_id . '_' . $check_in . '_' . $check_out . '_' . md5(json_encode($rooms)) . '_' . $offset . '_' . $limit;
    $cached_result = get_transient($cache_key);
    
    if ($cached_result !== false) {
        // Return cached result
        return array(
            'success' => true,
            'hotels' => $cached_result['hotels'],
            'total' => $cached_result['total'],
            'has_more' => $cached_result['has_more'],
            'from_cache' => true,
            'city_name' => $cached_result['city_name']
        );
    }
    
    // Check for stale cache
    $stale_cache_key = $cache_key . '_stale';
    $stale_result = get_option($stale_cache_key);
    
    // Format room guests for API request
    $room_guests = array();
    
    foreach ($rooms as $room) {
        $adult_count = isset($room['adults']) ? intval($room['adults']) : 2;
        $child_count = isset($room['children']) ? intval($room['children']) : 0;
        $child_ages = isset($room['child_ages']) ? $room['child_ages'] : array();
        
        // Ensure child_ages array has enough elements
        while (count($child_ages) < $child_count) {
            $child_ages[] = 8; // Default age
        }
        
        $room_guests[] = array(
            'AdultCount' => $adult_count,
            'ChildCount' => $child_count,
            'ChildAge' => array_slice($child_ages, 0, $child_count)
        );
    }
    
    // Calculate cache expiration based on search date proximity
    $now = new DateTime();
    $days_until_checkin = $check_in_date->diff($now)->days;
    
    if ($days_until_checkin < 3) {
        $cache_expiration = HOUR_IN_SECONDS / 2; // 30 minutes for near dates
    } else if ($days_until_checkin < 7) {
        $cache_expiration = HOUR_IN_SECONDS; // 1 hour for dates within a week
    } else if ($days_until_checkin < 30) {
        $cache_expiration = 2 * HOUR_IN_SECONDS; // 2 hours for dates within a month
    } else {
        $cache_expiration = DAY_IN_SECONDS; // 24 hours for far future dates
    }
    
    // API credentials
    $api_username = get_option('tbo_api_username', TBO_API_USERNAME);
    $api_password = get_option('tbo_api_password', TBO_API_PASSWORD);
    $api_url = TBO_API_BASE_URL . '/HotelService.svc/HotelSearch';
    
    // Fallback constants if not defined
    if (!defined('TBO_API_USERNAME')) define('TBO_API_USERNAME', 'YOLANDATHTest');
    if (!defined('TBO_API_PASSWORD')) define('TBO_API_PASSWORD', 'Yol@40360746');
    if (!defined('TBO_API_BASE_URL')) define('TBO_API_BASE_URL', 'https://api.tbotechnology.in/TBOHotelAPI');
    
    // Authentication
    $auth_timestamp = time();
    $auth_signature = base64_encode(hash_hmac('sha256', $api_username . $auth_timestamp, $api_password, true));
    
    // Request data
    $request_data = array(
        'CheckIn' => $check_in,
        'CheckOut' => $check_out,
        'DestinationCode' => $city_id,
        'Nationality' => 'IN',
        'RoomGuests' => $room_guests,
        'ResultCount' => $limit,
        'Filters' => array(
            'HotelType' => array('All'),
            'StarRating' => array('All')
        )
    );
    
    // Only add offset if greater than 0
    if ($offset > 0) {
        $request_data['Offset'] = $offset;
    }
    
    // Headers
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Username' => $api_username,
        'X-Timestamp' => $auth_timestamp,
        'X-Signature' => $auth_signature
    );
    
    try {
        // Make API request with increased timeout
        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => json_encode($request_data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotels' => $stale_result['hotels'],
                    'total' => $stale_result['total'],
                    'has_more' => $stale_result['has_more'],
                    'from_stale_cache' => true,
                    'error_info' => $response->get_error_message(),
                    'city_name' => $stale_result['city_name']
                );
            }
            
            return array(
                'success' => false,
                'error' => 'API Error: ' . $response->get_error_message()
            );
        }
        
        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Validate response code
        if ($response_code !== 200) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotels' => $stale_result['hotels'],
                    'total' => $stale_result['total'],
                    'has_more' => $stale_result['has_more'],
                    'from_stale_cache' => true,
                    'error_info' => 'API returned status code ' . $response_code,
                    'city_name' => $stale_result['city_name']
                );
            }
            
            return array(
                'success' => false,
                'error' => 'API returned status code ' . $response_code
            );
        }
        
        // Parse JSON
        $data = json_decode($response_body, true);
        
        // Check for valid response format
        if (!isset($data['Hotels']) || !is_array($data['Hotels'])) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotels' => $stale_result['hotels'],
                    'total' => $stale_result['total'],
                    'has_more' => $stale_result['has_more'],
                    'from_stale_cache' => true,
                    'error_info' => 'Invalid API response format',
                    'city_name' => $stale_result['city_name']
                );
            }
            
            return array(
                'success' => false,
                'error' => 'Invalid API response format'
            );
        }
        
        // Get city name
        $city_name = isset($data['City']) ? $data['City'] : '';
        
        // Format hotels data
        $hotels = $data['Hotels'];
        $total = isset($data['TotalHotels']) ? intval($data['TotalHotels']) : count($hotels);
        $has_more = ($offset + count($hotels) < $total);
        
        // Cache the result
        $cache_data = array(
            'hotels' => $hotels,
            'total' => $total,
            'has_more' => $has_more,
            'city_name' => $city_name
        );
        
        set_transient($cache_key, $cache_data, $cache_expiration);
        
        // Also save as stale cache for fallback
        update_option($stale_cache_key, $cache_data);
        
        // Return the result
        return array(
            'success' => true,
            'hotels' => $hotels,
            'total' => $total,
            'has_more' => $has_more,
            'city_name' => $city_name
        );
        
    } catch (Exception $e) {
        // Catch any exceptions and return error
        // Return stale cache if available
        if ($stale_result !== false) {
            return array(
                'success' => true,
                'hotels' => $stale_result['hotels'],
                'total' => $stale_result['total'],
                'has_more' => $stale_result['has_more'],
                'from_stale_cache' => true,
                'error_info' => $e->getMessage(),
                'city_name' => $stale_result['city_name']
            );
        }
        
        return array(
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        );
    }
}

/**
 * Enhanced hotel details function
 * 
 * @param string $hotel_code The hotel code
 * @return array Result array with hotel details
 */
function tbo_enhanced_get_hotel_details($hotel_code) {
    // Validate required parameters
    if (empty($hotel_code)) {
        return array(
            'success' => false,
            'error' => 'Hotel code is required'
        );
    }
    
    // Check cache first
    $cache_key = 'tbo_api_hotel_' . $hotel_code;
    $cached_result = get_transient($cache_key);
    
    if ($cached_result !== false) {
        return $cached_result;
    }
    
    // Check for stale cache
    $stale_cache_key = $cache_key . '_stale';
    $stale_result = get_option($stale_cache_key);
    
    // API credentials
    $api_username = get_option('tbo_api_username', TBO_API_USERNAME);
    $api_password = get_option('tbo_api_password', TBO_API_PASSWORD);
    $api_url = TBO_API_BASE_URL . '/HotelService.svc/HotelDetails';
    
    // Authentication
    $auth_timestamp = time();
    $auth_signature = base64_encode(hash_hmac('sha256', $api_username . $auth_timestamp, $api_password, true));
    
    // Request data
    $request_data = array(
        'HotelCode' => $hotel_code,
        'Nationality' => 'IN'
    );
    
    // Headers
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Username' => $api_username,
        'X-Timestamp' => $auth_timestamp,
        'X-Signature' => $auth_signature
    );
    
    try {
        // Make API request
        $response = wp_remote_post($api_url, array(
            'headers' => $headers,
            'body' => json_encode($request_data),
            'timeout' => 30,
            'sslverify' => false
        ));
        
        // Check for errors
        if (is_wp_error($response)) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotel' => $stale_result['hotel'],
                    'from_stale_cache' => true,
                    'error_info' => $response->get_error_message()
                );
            }
            
            return array(
                'success' => false,
                'error' => 'API Error: ' . $response->get_error_message()
            );
        }
        
        // Parse response
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        // Validate response code
        if ($response_code !== 200) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotel' => $stale_result['hotel'],
                    'from_stale_cache' => true,
                    'error_info' => 'API returned status code ' . $response_code
                );
            }
            
            return array(
                'success' => false,
                'error' => 'API returned status code ' . $response_code
            );
        }
        
        // Parse JSON
        $data = json_decode($response_body, true);
        
        // Check for valid response format
        if (!isset($data['Hotel'])) {
            // Return stale cache if available
            if ($stale_result !== false) {
                return array(
                    'success' => true,
                    'hotel' => $stale_result['hotel'],
                    'from_stale_cache' => true,
                    'error_info' => 'Invalid API response format'
                );
            }
            
            return array(
                'success' => false,
                'error' => 'Invalid API response format'
            );
        }
        
        $hotel = $data['Hotel'];
        
        // Cache the result
        $cache_data = array(
            'success' => true,
            'hotel' => $hotel
        );
        
        set_transient($cache_key, $cache_data, 12 * HOUR_IN_SECONDS);
        
        // Also save as stale cache for fallback
        update_option($stale_cache_key, $cache_data);
        
        // Return the result
        return $cache_data;
        
    } catch (Exception $e) {
        // Catch any exceptions and return error
        // Return stale cache if available
        if ($stale_result !== false) {
            return array(
                'success' => true,
                'hotel' => $stale_result['hotel'],
                'from_stale_cache' => true,
                'error_info' => $e->getMessage()
            );
        }
        
        return array(
            'success' => false,
            'error' => 'Exception: ' . $e->getMessage()
        );
    }
}

/**
 * Helper function to validate date format
 * 
 * @param string $date Date string to validate
 * @param string $format Expected date format
 * @return bool True if date is valid, false otherwise
 */
function validate_date($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Add AJAX handler for hotel search
 */
add_action('wp_ajax_tbo_enhanced_hotel_search', 'tbo_enhanced_hotel_search_handler');
add_action('wp_ajax_nopriv_tbo_enhanced_hotel_search', 'tbo_enhanced_hotel_search_handler');

/**
 * AJAX handler for enhanced hotel search
 */
function tbo_enhanced_hotel_search_handler() {
    // Make sure we have clean output
    ob_clean();
    
    // Validate required parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : null;
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : null;
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : null;
    
    // Check for essential parameters
    if (empty($city_id) || empty($check_in) || empty($check_out)) {
        wp_send_json_error('Missing required parameters');
        exit;
    }
    
    // Get rooms data
    $rooms_json = isset($_POST['rooms']) ? $_POST['rooms'] : null;
    $rooms = null;
    
    if ($rooms_json) {
        // Try to decode rooms data
        try {
            $rooms = json_decode(stripslashes($rooms_json), true);
        } catch (Exception $e) {
            // Failed to decode
            error_log('TBO Enhanced API: Failed to decode rooms data: ' . $e->getMessage());
        }
    }
    
    // If rooms data is invalid, create default
    if (empty($rooms) || !is_array($rooms)) {
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        
        $rooms = array(
            array(
                'adults' => $adults,
                'children' => $children,
                'child_ages' => array()
            )
        );
    }
    
    // Offset and limit
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    
    // Call the search function
    $result = tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms, $offset, $limit);
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result['error']);
    }
    
    exit;
}

/**
 * Add AJAX handler for hotel details
 */
add_action('wp_ajax_tbo_enhanced_hotel_details', 'tbo_enhanced_hotel_details_handler');
add_action('wp_ajax_nopriv_tbo_enhanced_hotel_details', 'tbo_enhanced_hotel_details_handler');

/**
 * AJAX handler for enhanced hotel details
 */
function tbo_enhanced_hotel_details_handler() {
    // Make sure we have clean output
    ob_clean();
    
    // Validate required parameters
    $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : null;
    
    // Check for essential parameters
    if (empty($hotel_code)) {
        wp_send_json_error('Hotel code is required');
        exit;
    }
    
    // Call the details function
    $result = tbo_enhanced_get_hotel_details($hotel_code);
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result['error']);
    }
    
    exit;
}

/**
 * Replace the original tbo_load_more_hotels AJAX handler with the enhanced version
 */
add_action('init', function() {
    // Remove the original handler if it exists
    if (has_action('wp_ajax_tbo_load_more_hotels')) {
        remove_action('wp_ajax_tbo_load_more_hotels', 'tbo_load_more_hotels');
        remove_action('wp_ajax_nopriv_tbo_load_more_hotels', 'tbo_load_more_hotels');
        
        // Add our enhanced handler
        add_action('wp_ajax_tbo_load_more_hotels', 'tbo_enhanced_load_more_hotels');
        add_action('wp_ajax_nopriv_tbo_load_more_hotels', 'tbo_enhanced_load_more_hotels');
    }
});

/**
 * Enhanced handler for tbo_load_more_hotels
 */
function tbo_enhanced_load_more_hotels() {
    // Make sure we have clean output
    ob_clean();
    
    // Validate required parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : null;
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : null;
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : null;
    
    // Check for essential parameters
    if (empty($city_id) || empty($check_in) || empty($check_out)) {
        wp_send_json_error('Missing required parameters');
        exit;
    }
    
    // Get rooms data
    $rooms_json = isset($_POST['rooms']) ? $_POST['rooms'] : null;
    $rooms = null;
    
    if ($rooms_json) {
        // Try to decode rooms data
        try {
            $rooms = json_decode(stripslashes($rooms_json), true);
        } catch (Exception $e) {
            // Failed to decode
            error_log('TBO Enhanced API: Failed to decode rooms data: ' . $e->getMessage());
        }
    }
    
    // If rooms data is invalid, create default
    if (empty($rooms) || !is_array($rooms)) {
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        
        $rooms = array(
            array(
                'adults' => $adults,
                'children' => $children,
                'child_ages' => array()
            )
        );
    }
    
    // Offset and limit
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    
    // Call the search function
    $result = tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms, $offset, $limit);
    
    if ($result['success']) {
        wp_send_json_success($result);
    } else {
        wp_send_json_error($result['error']);
    }
    
    exit;
}