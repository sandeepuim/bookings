<?php
/**
 * Template functions for TBO Hotels theme
 *
 * @package TBO_Hotels
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Format price
 * 
 * @param float $price Price to format
 * @param string $currency Currency code
 * @return string Formatted price
 */
function tbo_hotels_format_price($price, $currency = 'INR') {
    if ($currency === 'INR') {
        return '₹' . number_format($price, 2);
    } elseif ($currency === 'USD') {
        return '$' . number_format($price, 2);
    } elseif ($currency === 'EUR') {
        return '€' . number_format($price, 2);
    } elseif ($currency === 'GBP') {
        return '£' . number_format($price, 2);
    } else {
        return $currency . ' ' . number_format($price, 2);
    }
}

/**
 * Calculate number of nights between two dates
 * 
 * @param string $checkin Check-in date (Y-m-d format)
 * @param string $checkout Check-out date (Y-m-d format)
 * @return int Number of nights
 */
function tbo_hotels_calculate_nights($checkin, $checkout) {
    $checkin_date = new DateTime($checkin);
    $checkout_date = new DateTime($checkout);
    $interval = $checkin_date->diff($checkout_date);
    return $interval->days;
}

/**
 * Get rating text based on rating value
 * 
 * @param float $rating Rating value (0-10)
 * @return string Rating text
 */
function tbo_hotels_get_rating_text($rating) {
    if ($rating >= 9) {
        return 'Exceptional';
    } elseif ($rating >= 8) {
        return 'Excellent';
    } elseif ($rating >= 7) {
        return 'Very Good';
    } elseif ($rating >= 6) {
        return 'Good';
    } elseif ($rating >= 5) {
        return 'Average';
    } else {
        return 'Below Average';
    }
}

/**
 * Get hotel details from API
 * 
 * @param array $request_data Request data
 * @return array|WP_Error Hotel details or error
 */
function tbo_hotels_get_hotel_details($request_data) {
    // Check if we have required parameters
    if (empty($request_data['hotel_code']) || empty($request_data['city_code']) || 
        empty($request_data['checkin']) || empty($request_data['checkout'])) {
        return new WP_Error('missing_params', 'Missing required parameters for hotel details');
    }
    
    // Extract request data
    $hotel_code = $request_data['hotel_code'];
    $city_code = $request_data['city_code'];
    $checkin = $request_data['checkin'];
    $checkout = $request_data['checkout'];
    $adults = isset($request_data['adults']) ? intval($request_data['adults']) : 1;
    $children = isset($request_data['children']) ? intval($request_data['children']) : 0;
    $rooms = isset($request_data['rooms']) ? intval($request_data['rooms']) : 1;
    
    // Build cache key
    $cache_key = 'tbo_hotel_details_' . md5($hotel_code . $city_code . $checkin . $checkout . $adults . $children . $rooms);
    
    // Check if we have cached data
    $cached_data = get_transient($cache_key);
    if (false !== $cached_data) {
        return $cached_data;
    }
    
    // Prepare API request
    $endpoint = 'Hotels/HotelDetails';
    $payload = array(
        'CheckIn' => $checkin,
        'CheckOut' => $checkout,
        'HotelCodes' => array($hotel_code),
        'CityCode' => $city_code,
        'GuestNationality' => 'IN',
        'PaxRooms' => array(
            array(
                'Adults' => $adults,
                'Children' => $children,
                'ChildrenAges' => array_fill(0, $children, 10) // Default age 10 for all children
            )
        )
    );
    
    // Make API request
    $response = tbo_hotels_api_request($endpoint, $payload, 'POST');
    
    // Check for errors
    if (is_wp_error($response)) {
        return $response;
    }
    
    // Extract hotel details
    if (!empty($response['HotelDetails']) && !empty($response['HotelDetails'][0])) {
        $hotel_data = $response['HotelDetails'][0];
        
        // Cache the hotel details for 30 minutes
        set_transient($cache_key, $hotel_data, 30 * MINUTE_IN_SECONDS);
        
        return $hotel_data;
    }
    
    return new WP_Error('no_hotel_data', 'No hotel details found');
}

/**
 * Get star rating HTML
 * 
 * @param int $rating Rating (1-5)
 * @param string $size Size of stars (normal or small)
 * @return string HTML for star rating
 */
function tbo_hotels_get_star_rating($rating, $size = 'normal') {
    $rating = max(0, min(5, intval($rating)));
    $size_class = ($size === 'small') ? ' small' : '';
    
    $html = '<div class="hotel-rating">';
    
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<span class="star' . $size_class . ' filled">★</span>';
        } else {
            $html .= '<span class="star' . $size_class . '">☆</span>';
        }
    }
    
    $html .= '</div>';
    
    return $html;
}

/**
 * Get hotel amenities list
 * 
 * @param array $amenities Array of amenities
 * @param int $limit Max number of amenities to show
 * @return string HTML for amenities list
 */
function tbo_hotels_get_amenities($amenities, $limit = 3) {
    if (empty($amenities) || !is_array($amenities)) {
        return '';
    }
    
    // Limit number of amenities
    $amenities = array_slice($amenities, 0, $limit);
    
    $html = '<div class="hotel-amenities">';
    
    foreach ($amenities as $amenity) {
        $html .= '<span class="amenity">' . esc_html($amenity) . '</span>';
    }
    
    $html .= '</div>';
    
    return $html;
}