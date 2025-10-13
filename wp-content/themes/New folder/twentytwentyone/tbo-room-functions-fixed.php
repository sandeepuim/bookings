<?php
/**
 * TBO Room Functions - Fixed
 *
 * This file provides supplementary functions for getting room details
 * without leaking PHP code to the browser.
 */

// Make sure this file is called through WordPress
defined('ABSPATH') || exit;

/**
 * Get mock hotel room details when the main API function is not available
 *
 * @param array $params Parameters for the API request
 * @return array Room details
 */
function tbo_get_mock_room_details($params) {
    // Log that this function was called
    error_log('tbo_get_mock_room_details called with params: ' . json_encode($params));
    
    // Mock hotel information
    $hotel_info = array(
        'HotelName' => 'Grand Hotel Plaza (Mock)',
        'HotelCode' => isset($params['hotel_code']) ? $params['hotel_code'] : '12345',
        'StarRating' => 4,
        'HotelAddress' => 'Via del Corso 126, Rome, Italy',
        'HotelFacilities' => array(
            'Free WiFi', 'Restaurant', 'Room Service', 'Fitness Center', 
            'Swimming Pool', 'Spa', 'Parking', 'Airport Shuttle', 
            'Conference Rooms', 'Business Center'
        )
    );
    
    // Mock room data - create multiple room types
    $rooms = array();
    
    // Room type 1 - Standard Room
    $rooms[] = array(
        'RoomIndex' => 1,
        'RoomName' => 'Standard Room',
        'RoomTypeCode' => 'STD',
        'RoomDescription' => 'Comfortable standard room with modern amenities',
        'Inclusions' => array('Free WiFi', 'Breakfast Included'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 5200,
                    'Tax' => 520,
                    'TotalPrice' => 5720
                )
            )
        )
    );
    
    // Room type 2 - Deluxe Room
    $rooms[] = array(
        'RoomIndex' => 2,
        'RoomName' => 'Deluxe Room',
        'RoomTypeCode' => 'DLX',
        'RoomDescription' => 'Spacious deluxe room with city view',
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 7500,
                    'Tax' => 750,
                    'TotalPrice' => 8250
                )
            )
        )
    );
    
    // Room type 3 - Executive Suite
    $rooms[] = array(
        'RoomIndex' => 3,
        'RoomName' => 'Executive Suite',
        'RoomTypeCode' => 'EXSUITE',
        'RoomDescription' => 'Luxurious suite with separate living area',
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar', 'Airport Transfer'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 12000,
                    'Tax' => 1200,
                    'TotalPrice' => 13200
                )
            )
        )
    );
    
    return array(
        'HotelInfo' => $hotel_info,
        'Rooms' => $rooms
    );
}

/**
 * Safe function to get room details - uses the original function if available,
 * otherwise falls back to the mock implementation
 * 
 * @param array $params Search parameters
 * @return array Room details
 */
function tbo_safe_get_room_details($params) {
    if (function_exists('tbo_hotels_get_room_details')) {
        return tbo_hotels_get_room_details($params);
    } else {
        return tbo_get_mock_room_details($params);
    }
}

// Hide this file's output if accessed directly
if (!defined('ABSPATH')) {
    // If this file is accessed directly, show a blank page
    exit;
}