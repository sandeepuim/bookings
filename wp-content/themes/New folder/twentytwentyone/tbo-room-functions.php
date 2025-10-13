/**
 * Get hotel room details from TBO API
 *
 * @param array $params Parameters for the API request
 * @return array|WP_Error Room details or error
 */
function tbo_hotels_get_room_details($params) {
    // In a real implementation, this would call the TBO API
    // For now, let's return mock data
    
    // Log that this function was called
    error_log('tbo_hotels_get_room_details called with params: ' . json_encode($params));
    
    // Mock hotel information
    $hotel_info = array(
        'HotelName' => 'Grand Hotel Plaza',
        'HotelCode' => $params['hotel_code'],
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