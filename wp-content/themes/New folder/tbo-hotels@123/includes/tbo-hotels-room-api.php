<?php
/**
 * TBO Hotels Room API Functions
 *
 * Handles API requests for fetching hotel room details and pricing
 *
 * @package TBO_Hotels
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get hotel room details from TBO API
 * 
 * @param array $params Search parameters including hotel_code, check_in, check_out, etc.
 * @return array|WP_Error Room details or error
 */
function tbo_hotels_get_room_details($params) {
    // Validate required parameters
    $required = array('hotel_code', 'city_code', 'check_in', 'check_out');
    foreach ($required as $field) {
        if (empty($params[$field])) {
            return new WP_Error('missing_param', "Required parameter '$field' is missing");
        }
    }
    
    // Create a unique cache key for this search
    $cache_key = 'tbo_hotel_rooms_' . md5(serialize($params));
    $cached_rooms = get_transient($cache_key);
    
    // Return cached results if available
    if (false !== $cached_rooms) {
        return $cached_rooms;
    }
    
    // Prepare search data according to TBO API structure
    $search_data = array(
        'CheckIn' => $params['check_in'],
        'CheckOut' => $params['check_out'],
        'HotelCodes' => $params['hotel_code'],
        'GuestNationality' => 'IN',
        'PaxRooms' => array(),
        'ResponseTime' => 15,
        'IsDetailedResponse' => true
    );
    
    // Add room information
    for ($i = 0; $i < intval($params['rooms'] ?? 1); $i++) {
        $room = array('Adults' => intval($params['adults'] ?? 2));
        if (!empty($params['children']) && intval($params['children']) > 0) {
            $room['Children'] = intval($params['children']);
            $room['ChildrenAges'] = array_fill(0, intval($params['children']), 5);
        }
        $search_data['PaxRooms'][] = $room;
    }
    
    // Make API request to Search endpoint to get room details
    $response = tbo_hotels_api_request('Search', $search_data, 'POST');
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    // Extract hotel room details from the response
    if (isset($response['HotelResult']) && is_array($response['HotelResult']) && !empty($response['HotelResult'])) {
        $hotel_result = $response['HotelResult'][0]; // We only searched for one hotel code
        
        // Get hotel details for better display
        $hotel_details = tbo_hotels_get_hotel_details($params['city_code']);
        
        if (is_wp_error($hotel_details)) {
            // If we can't get hotel details, we'll just use what's in the search result
            $hotel_info = array(
                'HotelCode' => $hotel_result['HotelCode'] ?? '',
                'HotelName' => $hotel_result['HotelName'] ?? 'Hotel Name Not Available',
                'StarRating' => $hotel_result['StarRating'] ?? 0,
                'HotelAddress' => $hotel_result['HotelAddress'] ?? '',
                'HotelFacilities' => array(),
                'ImageUrls' => array(),
                'Description' => '',
                'CityName' => '',
                'CountryName' => ''
            );
        } else {
            // Merge hotel details with room information
            $hotel_code = $hotel_result['HotelCode'] ?? '';
            $hotel_info = isset($hotel_details[$hotel_code]) ? $hotel_details[$hotel_code] : array(
                'HotelCode' => $hotel_code,
                'HotelName' => $hotel_result['HotelName'] ?? 'Hotel Name Not Available',
                'StarRating' => $hotel_result['StarRating'] ?? 0,
                'HotelAddress' => $hotel_result['HotelAddress'] ?? '',
                'HotelFacilities' => array(),
                'ImageUrls' => array(),
                'Description' => '',
                'CityName' => '',
                'CountryName' => ''
            );
        }
        
        // Get room information
        $rooms = $hotel_result['Rooms'] ?? array();
        
        // Prepare result data
        $result = array(
            'HotelInfo' => $hotel_info,
            'Rooms' => $rooms,
            'CheckIn' => $params['check_in'],
            'CheckOut' => $params['check_out'],
            'Adults' => $params['adults'] ?? 2,
            'Children' => $params['children'] ?? 0,
            'TotalRooms' => $params['rooms'] ?? 1
        );
        
        // Cache the result for 30 minutes
        set_transient($cache_key, $result, 30 * MINUTE_IN_SECONDS);
        
        return $result;
    }
    
    return new WP_Error('no_rooms', 'No rooms found for the given hotel and search criteria');
}

/**
 * AJAX handler for getting hotel room details
 */
function tbo_hotels_ajax_get_room_details() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'] ?? '', 'tbo_hotels_nonce')) {
        wp_send_json_error('Invalid security token');
        return;
    }
    
    // Collect search parameters
    $params = array(
        'hotel_code' => sanitize_text_field($_POST['hotel_code'] ?? ''),
        'city_code' => sanitize_text_field($_POST['city_code'] ?? ''),
        'check_in' => sanitize_text_field($_POST['check_in'] ?? ''),
        'check_out' => sanitize_text_field($_POST['check_out'] ?? ''),
        'adults' => intval($_POST['adults'] ?? 2),
        'rooms' => intval($_POST['rooms'] ?? 1),
        'children' => intval($_POST['children'] ?? 0),
    );
    
    // Validate required parameters
    if (empty($params['hotel_code']) || empty($params['city_code']) || 
        empty($params['check_in']) || empty($params['check_out'])) {
        wp_send_json_error('Please provide all required parameters');
        return;
    }
    
    // Try to get room details with error handling
    try {
        $results = tbo_hotels_get_room_details($params);
        
        if (is_wp_error($results)) {
            wp_send_json_error($results->get_error_message());
        } else {
            wp_send_json_success($results);
        }
    } catch (Exception $e) {
        wp_send_json_error('Error fetching room details: ' . $e->getMessage());
    }
}
add_action('wp_ajax_tbo_hotels_get_room_details', 'tbo_hotels_ajax_get_room_details');
add_action('wp_ajax_nopriv_tbo_hotels_get_room_details', 'tbo_hotels_ajax_get_room_details');