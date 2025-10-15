<?php
/**
 * TBO Hotels API Class
 */
class TBO_Hotels_API {
    /**
     * Search for cities based on query
     */
    public function search_cities($query) {
        $endpoint = 'GetDestinationSearchStateless';
        $data = [
            "BookingType" => "hotel",
            "SearchParameter" => $query,
            "SearchType" => "All"
        ];
        
        // Debug: Print request details
        echo '<pre>';
        echo "=== API Request Debug ===\n";
        echo "Endpoint: " . $endpoint . "\n";
        echo "Request Data:\n";
        print_r($data);
        echo '</pre>';
        
        $response = tbo_hotels_api_request($endpoint, $data, 'POST');
        
        // Debug: Print raw response
        echo '<pre>';
        echo "=== API Response Debug ===\n";
        echo "Raw Response:\n";
        print_r($response);
        
        if (is_wp_error($response)) {
            echo "\nWP Error:\n";
            print_r($response->get_error_messages());
            echo '</pre>';
            throw new Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        echo "\nParsed Response Body:\n";
        print_r($body);
        echo '</pre>';
        
        if (!$body || !isset($body->CityList)) {
            throw new Exception('Invalid response from API - CityList not found');
        }
        
        return $body->CityList;
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
        
        // Debug: Print request details
        echo '<pre>';
        echo "=== Hotel Search API Request Debug ===\n";
        echo "Endpoint: " . $endpoint . "\n";
        echo "Request Data:\n";
        print_r($data);
        echo '</pre>';
        
        $response = tbo_hotels_api_request($endpoint, $data, 'POST');
        
        // Debug: Print raw response
        echo '<pre>';
        echo "=== Hotel Search API Response Debug ===\n";
        echo "Raw Response:\n";
        print_r($response);
        
        if (is_wp_error($response)) {
            echo "\nWP Error:\n";
            print_r($response->get_error_messages());
            echo '</pre>';
            throw new Exception($response->get_error_message());
        }
        
        $body = json_decode(wp_remote_retrieve_body($response));
        echo "\nParsed Response Body:\n";
        print_r($body);
        echo '</pre>';
        
        if (!$body || !isset($body->HotelResults)) {
            throw new Exception('Invalid response from API - HotelResults not found');
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
        echo '<pre>';
        echo "=== API Debug Info ===\n";
        echo "Endpoint: " . esc_html($endpoint) . "\n";
        echo "Payload:\n";
        print_r($data);
        echo '</pre>';die();

        if (is_wp_error($response)) {
            throw new Exception($response->get_error_message());
        }
        // Debug: print raw response
        echo '<pre>';
        echo "Raw Response:\n";
        print_r($response);
        echo '</pre>';die();
        $body = json_decode(wp_remote_retrieve_body($response));
        if (!$body || !isset($body->HotelResults)) {
            throw new Exception('Invalid response from API');
        }
        
        return $body->HotelResults;
    }
}