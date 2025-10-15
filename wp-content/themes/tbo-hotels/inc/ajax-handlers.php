<?php
/**
 * TBO Hotels AJAX handlers for location search
 */

// Hook up AJAX actions for both logged in and non-logged in users
add_action('wp_ajax_search_locations', 'tbo_hotels_search_locations');
add_action('wp_ajax_nopriv_search_locations', 'tbo_hotels_search_locations');

/**
 * Search for cities and hotels based on user input
 */
function tbo_hotels_search_locations() {
    // Debug log
    error_log('Search locations AJAX called with POST data: ' . print_r($_POST, true));
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        error_log('Nonce verification failed');
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    // Get search query
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    error_log('Sanitized search query: ' . $query);
    if (empty($query)) {
        wp_send_json_error(['message' => 'Search query is required']);
    }

    try {
        // Initialize API client
        $api = new TBO_Hotels_API();
        
        // Search for cities
        $cities = $api->search_cities($query);
        
        // Search for hotels if query is longer than 3 characters
        $hotels = [];
        if (strlen($query) >= 3) {
            $hotels = $api->search_hotels_by_name($query);
        }
        
        // Format response data
        $response_data = [
            'cities' => array_map(function($city) {
                return [
                    'code' => $city->DestinationId,
                    'name' => $city->Destination,
                    'country' => $city->Country
                ];
            }, $cities),
            'hotels' => array_map(function($hotel) {
                return [
                    'code' => $hotel->HotelCode,
                    'name' => $hotel->HotelName,
                    'city' => $hotel->CityName,
                    'cityCode' => $hotel->CityId
                ];
            }, $hotels)
        ];
        
        wp_send_json_success($response_data);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_search_locations', 'tbo_hotels_search_locations');
add_action('wp_ajax_nopriv_search_locations', 'tbo_hotels_search_locations');

/**
 * Search for hotels based on selected criteria
 */
function tbo_hotels_search() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error(['message' => 'Invalid security token']);
    }

    // Get search data
    $search_data = isset($_POST['searchData']) ? $_POST['searchData'] : '';
    if (empty($search_data)) {
        wp_send_json_error(['message' => 'Search data is required']);
    }

    try {
        // Parse search data
        parse_str($search_data, $params);
        
        // Initialize API client
        $api = new TBO_Hotels_API();
        
        // Prepare search parameters
        $search_params = [
            'cityCode' => isset($params['city_code']) ? $params['city_code'] : '',
            'hotelCode' => isset($params['hotel_code']) ? $params['hotel_code'] : '',
            'checkIn' => isset($params['check_in']) ? $params['check_in'] : '',
            'checkOut' => isset($params['check_out']) ? $params['check_out'] : '',
            'rooms' => isset($params['rooms']) ? intval($params['rooms']) : 1,
            'adults' => isset($params['adults']) ? intval($params['adults']) : 2,
            'children' => isset($params['children']) ? intval($params['children']) : 0
        ];
        
        // Perform hotel search
        $results = $api->search_hotels($search_params);
        
        wp_send_json_success($results);
        
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_search_hotels', 'tbo_hotels_search');
add_action('wp_ajax_nopriv_search_hotels', 'tbo_hotels_search');