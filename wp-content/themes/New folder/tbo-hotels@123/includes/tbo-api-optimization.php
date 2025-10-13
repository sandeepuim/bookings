<?php
/**
 * TBO API Performance Optimization
 * 
 * This file improves API performance by:
 * 1. Adding better caching mechanisms
 * 2. Implementing progressive loading
 * 3. Optimizing API requests
 * 4. Adding fallback mechanisms
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced API request function with improved caching and error handling
 */
function tbo_enhanced_api_request($endpoint, $request_data, $cache_time = 3600) {
    // Generate a unique cache key based on the endpoint and request data
    $cache_key = 'tbo_api_' . md5($endpoint . json_encode($request_data));
    
    // Try to get cached response
    $cached_response = get_transient($cache_key);
    
    // Return cached response if available and not expired
    if ($cached_response !== false) {
        return $cached_response;
    }
    
    // Define API credentials and base URL
    $api_username = get_option('tbo_api_username');
    $api_password = get_option('tbo_api_password');
    $api_url = 'https://api.tbotechnology.in/TBOHotelAPI/HotelService.svc/' . $endpoint;
    
    // Generate authentication details
    $auth_timestamp = time();
    $auth_signature = base64_encode(hash_hmac('sha256', $api_username . $auth_timestamp, $api_password, true));
    
    // Set request headers
    $headers = array(
        'Content-Type' => 'application/json',
        'X-Username' => $api_username,
        'X-Timestamp' => $auth_timestamp,
        'X-Signature' => $auth_signature
    );
    
    // Make API request with increased timeout for reliability
    $response = wp_remote_post($api_url, array(
        'headers' => $headers,
        'body' => json_encode($request_data),
        'timeout' => 30, // Increased timeout for reliability
        'sslverify' => false
    ));
    
    // Check for errors
    if (is_wp_error($response)) {
        // Log the error
        error_log('TBO API Error: ' . $response->get_error_message());
        
        // Try to get stale cached data as fallback
        $stale_cache = get_option($cache_key . '_stale');
        if ($stale_cache !== false) {
            return $stale_cache;
        }
        
        return array(
            'success' => false,
            'error' => $response->get_error_message()
        );
    }
    
    // Parse response
    $response_body = wp_remote_retrieve_body($response);
    $response_code = wp_remote_retrieve_response_code($response);
    
    // Check for valid response
    if ($response_code != 200) {
        error_log('TBO API Error: Non-200 response code - ' . $response_code);
        
        // Try to get stale cached data as fallback
        $stale_cache = get_option($cache_key . '_stale');
        if ($stale_cache !== false) {
            return $stale_cache;
        }
        
        return array(
            'success' => false,
            'error' => 'API returned status code ' . $response_code
        );
    }
    
    // Decode JSON response
    $data = json_decode($response_body, true);
    
    // Store in cache
    set_transient($cache_key, $data, $cache_time);
    
    // Also store as stale cache for fallback (stores indefinitely until replaced)
    update_option($cache_key . '_stale', $data);
    
    return $data;
}

/**
 * Get hotel search results with progressive loading support
 */
function tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms, $nationality = 'IN', $offset = 0, $limit = 20) {
    // Format dates properly
    $check_in_formatted = date('Y-m-d', strtotime($check_in));
    $check_out_formatted = date('Y-m-d', strtotime($check_out));
    
    // Build room configurations
    $room_configs = array();
    
    foreach ($rooms as $room) {
        $room_config = array(
            'AdultCount' => isset($room['adults']) ? intval($room['adults']) : 2,
            'ChildCount' => isset($room['children']) ? intval($room['children']) : 0
        );
        
        // Add child ages if any
        if (isset($room['child_ages']) && is_array($room['child_ages']) && $room_config['ChildCount'] > 0) {
            $room_config['ChildAge'] = array_map('intval', $room['child_ages']);
        }
        
        $room_configs[] = $room_config;
    }
    
    // Build API request data
    $request_data = array(
        'CheckIn' => $check_in_formatted,
        'CheckOut' => $check_out_formatted,
        'DestinationCode' => $city_id,
        'Nationality' => $nationality,
        'RoomGuests' => $room_configs,
        'ResultCount' => min(50, $limit), // Limit to maximum 50 hotels per request for better performance
        'Filters' => array(
            'HotelType' => array('All'),
            'StarRating' => array('All')
        ),
        'Offset' => $offset,
        'OrderBy' => 'PriceAsc' // Order by price ascending for user-friendly results
    );
    
    // Set cache time based on search proximity (shorter cache for near-term searches)
    $days_until_checkin = ceil((strtotime($check_in) - time()) / 86400);
    $cache_time = 3600; // Default 1 hour
    
    if ($days_until_checkin < 3) {
        $cache_time = 1800; // 30 minutes for searches within next 3 days
    } else if ($days_until_checkin < 7) {
        $cache_time = 3600; // 1 hour for searches within next week
    } else if ($days_until_checkin < 30) {
        $cache_time = 7200; // 2 hours for searches within next month
    } else {
        $cache_time = 86400; // 24 hours for searches beyond a month
    }
    
    // Make API request with optimized caching
    $response = tbo_enhanced_api_request('HotelSearch', $request_data, $cache_time);
    
    // Format results for easier frontend consumption
    if (isset($response['Hotels']) && is_array($response['Hotels'])) {
        // Return subset for progressive loading if needed
        return array(
            'success' => true,
            'hotels' => $response['Hotels'],
            'total' => isset($response['TotalHotels']) ? $response['TotalHotels'] : count($response['Hotels']),
            'offset' => $offset,
            'has_more' => isset($response['TotalHotels']) && ($offset + count($response['Hotels']) < $response['TotalHotels'])
        );
    }
    
    // Return error or empty result
    return array(
        'success' => false,
        'hotels' => array(),
        'total' => 0,
        'offset' => $offset,
        'has_more' => false,
        'error' => isset($response['Error']) ? $response['Error'] : 'No hotels found'
    );
}

/**
 * Get detailed hotel information with fallback
 */
function tbo_enhanced_get_hotel_details($hotel_code) {
    // Check cache first
    $cache_key = 'tbo_hotel_' . $hotel_code;
    $cached_data = get_transient($cache_key);
    
    if ($cached_data !== false) {
        return $cached_data;
    }
    
    // Prepare API request
    $request_data = array(
        'HotelCode' => $hotel_code
    );
    
    // Make API request with longer cache time (hotel details change less frequently)
    $response = tbo_enhanced_api_request('HotelDetails', $request_data, 86400); // Cache for 24 hours
    
    if (isset($response['Hotel'])) {
        // Cache the response
        set_transient($cache_key, $response['Hotel'], 86400);
        return $response['Hotel'];
    }
    
    // Try to get stale data from options as fallback
    $stale_data = get_option($cache_key . '_stale');
    if ($stale_data !== false) {
        return $stale_data;
    }
    
    // Return error if no data available
    return array(
        'success' => false,
        'error' => isset($response['Error']) ? $response['Error'] : 'Hotel details not found'
    );
}

/**
 * Add AJAX endpoints for progressive loading
 */
function tbo_register_ajax_endpoints() {
    add_action('wp_ajax_tbo_load_more_hotels', 'tbo_ajax_load_more_hotels');
    add_action('wp_ajax_nopriv_tbo_load_more_hotels', 'tbo_ajax_load_more_hotels');
}
add_action('init', 'tbo_register_ajax_endpoints');

/**
 * AJAX handler for progressive hotel loading
 */
function tbo_ajax_load_more_hotels() {
    // Validate nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_ajax_nonce')) {
        wp_send_json_error('Invalid security token');
        exit;
    }
    
    // Get parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $rooms = isset($_POST['rooms']) ? json_decode(stripslashes($_POST['rooms']), true) : array(array('adults' => 2, 'children' => 0));
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    
    // Validate required parameters
    if (empty($city_id) || empty($check_in) || empty($check_out)) {
        wp_send_json_error('Missing required parameters');
        exit;
    }
    
    // Get hotels with pagination
    $hotels = tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms, 'IN', $offset, $limit);
    
    // Return results
    wp_send_json($hotels);
    exit;
}

/**
 * Add the script to enqueue
 */
function tbo_enqueue_optimization_scripts() {
    wp_enqueue_script(
        'tbo-hotels-optimization', 
        get_template_directory_uri() . '/assets/js/tbo-optimization.js',
        array('jquery'),
        '1.0.0',
        true
    );
    
    // Add localized data
    wp_localize_script('tbo-hotels-optimization', 'tboData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbo_ajax_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'tbo_enqueue_optimization_scripts');