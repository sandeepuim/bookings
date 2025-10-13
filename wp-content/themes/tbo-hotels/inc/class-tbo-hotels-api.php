<?php
/**
 * TBO Hotels API Class
 */
class TBO_Hotels_API {
    /**
     * Search for cities based on query
     */
    public function search_cities($query) {
        $endpoint = 'SearchCityList';
        $data = [
            'CountryName' => '', // Leave empty to search all countries
            'CityName' => $query
        ];
        
        $response = tbo_hotels_api_request($endpoint, $data, 'POST');
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        if (!$body || !isset($body->Cities)) {
            throw new Exception('Invalid response from API');
        }
        
        return $body->Cities;
    }
    
    /**
     * Search for hotels by name
     */
    public function search_hotels_by_name($query) {
        $endpoint = 'HotelSearch';
        $data = [
            'HotelName' => $query,
            'IsHotelNameSearch' => true
        ];
        
        $response = tbo_hotels_api_request($endpoint, $data, 'POST');
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        if (!$body || !isset($body->HotelResults)) {
            throw new Exception('Invalid response from API');
        }
        
        return $body->HotelResults;
    }
    
    /**
     * Search for hotels based on criteria
     */
    public function search_hotels($params) {
        $endpoint = 'HotelSearch';
        
        // Prepare room guests
        $roomGuests = [];
        for ($i = 0; $i < $params['rooms']; $i++) {
            $roomGuests[] = [
                'AdultCount' => $params['adults'],
                'ChildCount' => $params['children']
            ];
        }
        
        $data = [
            'CheckIn' => $params['checkIn'],
            'CheckOut' => $params['checkOut'],
            'CityCode' => $params['cityCode'],
            'HotelCode' => $params['hotelCode'],
            'RoomGuests' => $roomGuests,
            'PreferredCurrency' => 'USD'
        ];
        
        $response = tbo_hotels_api_request($endpoint, $data, 'POST');
        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        if (!$body || !isset($body->HotelResults)) {
            throw new Exception('Invalid response from API');
        }
        
        return $body->HotelResults;
    }
}