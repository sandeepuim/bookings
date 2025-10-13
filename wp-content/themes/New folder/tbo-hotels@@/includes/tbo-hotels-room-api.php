<?php
/**
 * TBO Hotels Room Rates API Functions
 * 
 * Functions for hotel room rates API integration
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Ajax handler for getting hotel details
 */
function tbo_hotels_get_hotel_details_ajax() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Get parameters
    $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
    $city_code = isset($_POST['city_code']) ? sanitize_text_field($_POST['city_code']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $rooms = isset($_POST['rooms']) ? intval($_POST['rooms']) : 1;
    
    if (empty($hotel_code)) {
        wp_send_json_error('Hotel code is required');
        return;
    }
    
    // Call the API function
    $hotel_details = tbo_hotels_get_hotel_details($hotel_code, $city_code, $check_in, $check_out, $adults, $children, $rooms);
    
    if (is_wp_error($hotel_details)) {
        wp_send_json_error($hotel_details->get_error_message());
        return;
    }
    
    wp_send_json_success($hotel_details);
}
add_action('wp_ajax_tbo_hotels_get_hotel_details', 'tbo_hotels_get_hotel_details_ajax');
add_action('wp_ajax_nopriv_tbo_hotels_get_hotel_details', 'tbo_hotels_get_hotel_details_ajax');

/**
 * Get hotel details from API
 */
function tbo_hotels_get_hotel_details($hotel_code, $city_code = '', $check_in = '', $check_out = '', $adults = 2, $children = 0, $rooms = 1) {
    // For now, use sample data
    // In production, make an API request to TBO API
    
    // Sample hotel data
    $sample_hotel = (object) array(
        'HotelCode' => $hotel_code,
        'HotelName' => 'Grand Hotel & Suites',
        'HotelRating' => 4.5,
        'Address' => '123 Main Street, Mumbai, Maharashtra, India',
        'CityName' => 'Mumbai', // Added city name
        'CityCode' => $city_code ? $city_code : '150184', // Added city code
        'CountryName' => 'India', // Added country name
        'Description' => 'Experience the perfect blend of luxury and comfort at Grand Hotel & Suites. Our 5-star accommodations offer breathtaking views of the city skyline, complemented by world-class amenities including a rooftop infinity pool, full-service spa, fitness center, and multiple dining options. The hotel features elegantly appointed rooms with premium bedding, modern bathrooms, and state-of-the-art technology. Located in the heart of the city, we are within walking distance to major attractions, shopping, and entertainment venues. Whether you\'re traveling for business or leisure, our dedicated staff ensures personalized service to exceed your expectations.',
        'Facilities' => [
            'Free WiFi', 'Swimming Pool', 'Restaurant', 'Spa', 'Fitness Center', 
            'Conference Room', 'Parking', '24-hour Front Desk', 'Room Service'
        ],
        'Policies' => [
            'Check-in: 2:00 PM', 
            'Check-out: 12:00 PM', 
            'Pets not allowed', 
            'Cancellation policy: 24 hours before check-in'
        ],
        'ImageUrls' => [
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,luxury'],
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,room'],
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,pool'],
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,lobby'],
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,restaurant'],
            (object)['ImageUrl' => 'https://source.unsplash.com/featured/800x500/?hotel,spa']
        ],
        'Location' => (object)[
            'Latitude' => 18.9256,
            'Longitude' => 72.8246,
            'Landmarks' => [
                (object)['Name' => 'City Center', 'Distance' => '0.5 km'],
                (object)['Name' => 'Beach', 'Distance' => '2 km'],
                (object)['Name' => 'Airport', 'Distance' => '15 km']
            ]
        ]
    );
    
    return $sample_hotel;
}

/**
 * Ajax handler for getting room rates
 */
function tbo_hotels_get_room_rates_ajax() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Invalid nonce');
        return;
    }
    
    // Get parameters
    $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
    $city_code = isset($_POST['city_code']) ? sanitize_text_field($_POST['city_code']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
    $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $rooms = isset($_POST['rooms']) ? intval($_POST['rooms']) : 1;
    
    if (empty($hotel_code)) {
        wp_send_json_error('Hotel code is required');
        return;
    }
    
    // Call the API function
    $room_rates = tbo_hotels_get_room_rates($hotel_code, $city_code, $check_in, $check_out, $adults, $children, $rooms);
    
    if (is_wp_error($room_rates)) {
        wp_send_json_error($room_rates->get_error_message());
        return;
    }
    
    wp_send_json_success($room_rates);
}
add_action('wp_ajax_tbo_hotels_get_room_rates', 'tbo_hotels_get_room_rates_ajax');
add_action('wp_ajax_nopriv_tbo_hotels_get_room_rates', 'tbo_hotels_get_room_rates_ajax');

/**
 * Get room rates from API
 */
function tbo_hotels_get_room_rates($hotel_code, $city_code = '', $check_in = '', $check_out = '', $adults = 2, $children = 0, $rooms = 1) {
    // For now, use sample data
    // In production, make an API request to TBO API
    
    // Calculate nights
    $nights = 1;
    if (!empty($check_in) && !empty($check_out)) {
        $check_in_obj = new DateTime($check_in);
        $check_out_obj = new DateTime($check_out);
        $interval = $check_in_obj->diff($check_out_obj);
        $nights = $interval->days > 0 ? $interval->days : 1;
    }
    
    // Sample room types
    $room_types = [
        (object)[
            'RoomTypeCode' => 'RT001',
            'RoomTypeName' => 'Deluxe Room with Breakfast',
            'Inclusions' => ['Breakfast', 'Complimentary Wifi', 'Free Parking'],
            'Amenities' => ['King/Twin Bed', '400 Sq.Ft', 'Attached Bathroom', 'Hot/cold Water', 'Smart TV'],
            'Price' => (object)[
                'OfferedPrice' => 5084 * $nights,
                'OriginalPrice' => 6200 * $nights
            ],
            'ImageUrls' => [
                (object)['ImageUrl' => 'https://source.unsplash.com/featured/600x400/?hotel,room&sig=1']
            ],
            'CancellationPolicy' => 'Free cancellation before 24 hours of check-in'
        ],
        (object)[
            'RoomTypeCode' => 'RT002',
            'RoomTypeName' => 'Premium Pool View with Balcony and Bathtub',
            'Inclusions' => ['Breakfast', 'Complimentary Wifi', 'Welcome Drink', 'Free Parking'],
            'Amenities' => ['King Bed', '565 Sq.Ft', 'Pool View', 'Wooden Flooring', 'Attached Bathroom', 'Hot/cold Water', 'Smart TV', 'Mini Bar'],
            'Price' => (object)[
                'OfferedPrice' => 6314 * $nights,
                'OriginalPrice' => 7700 * $nights
            ],
            'ImageUrls' => [
                (object)['ImageUrl' => 'https://source.unsplash.com/featured/600x400/?hotel,luxury,room&sig=2']
            ],
            'CancellationPolicy' => 'Free cancellation before 24 hours of check-in'
        ],
        (object)[
            'RoomTypeCode' => 'RT003',
            'RoomTypeName' => 'Premium Pool View with Balcony and Breakfast + Lunch/Dinner',
            'Inclusions' => ['Breakfast and Dinner', 'Complimentary Wifi', 'Welcome Drink', 'Free Parking', 'Airport Transfer'],
            'Amenities' => ['King Bed', '565 Sq.Ft', 'Pool View', 'Wooden Flooring', 'Attached Bathroom', 'Hot/cold Water', 'Smart TV', 'Mini Bar', 'Bath Robes', 'Slippers'],
            'Price' => (object)[
                'OfferedPrice' => 8118 * $nights,
                'OriginalPrice' => 9900 * $nights
            ],
            'ImageUrls' => [
                (object)['ImageUrl' => 'https://source.unsplash.com/featured/600x400/?hotel,suite&sig=3']
            ],
            'CancellationPolicy' => 'Free cancellation before 24 hours of check-in'
        ],
        (object)[
            'RoomTypeCode' => 'RT004',
            'RoomTypeName' => 'Executive Suite with Lounge Access',
            'Inclusions' => ['All Meals Included', 'Complimentary Wifi', 'Welcome Drink', 'Free Parking', 'Airport Transfer', 'Lounge Access', 'Evening Cocktails'],
            'Amenities' => ['King Bed', '780 Sq.Ft', 'City View', 'Separate Living Area', 'Wooden Flooring', 'Attached Bathroom', 'Bathtub', 'Hot/cold Water', 'Smart TV', 'Mini Bar', 'Bath Robes', 'Slippers', 'Premium Toiletries'],
            'Price' => (object)[
                'OfferedPrice' => 12500 * $nights,
                'OriginalPrice' => 15200 * $nights
            ],
            'ImageUrls' => [
                (object)['ImageUrl' => 'https://source.unsplash.com/featured/600x400/?hotel,executive,suite&sig=4']
            ],
            'CancellationPolicy' => 'Free cancellation before 48 hours of check-in'
        ]
    ];
    
    // Return room data
    return (object) [
        'HotelCode' => $hotel_code,
        'CityCode' => $city_code,
        'CheckIn' => $check_in,
        'CheckOut' => $check_out,
        'RoomTypes' => $room_types,
        'TotalRooms' => count($room_types)
    ];
}