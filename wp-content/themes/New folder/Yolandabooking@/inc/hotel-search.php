<?php
/**
 * Hotel Search functionality using TBO API
 */

class TBO_Hotel_API {
    
    private $base_url = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/';
    private $auth_header = 'Basic WU9MQU5EQVRIVGVzdDpZb2xANDAzNjA3NDY=';
    
    /**
     * Get list of cities by country code
     */
    public function get_cities($country_code) {
        $url = $this->base_url . 'CityList';
        
        $data = array(
            'CountryCode' => $country_code
        );
        
        return $this->make_request($url, $data);
    }
    
    /**
     * Get hotel codes by city code
     */
    public function get_hotel_codes($city_code) {
        $url = $this->base_url . 'HotelCodeList';
        
        $data = array(
            'CityCode' => $city_code
        );
        
        return $this->make_request($url, $data, 'GET');
    }
    
    /**
     * Search hotels with availability
     */
    public function search_hotels($params) {
        $url = $this->base_url . 'Search';
        
        $data = array(
            'CheckIn' => $params['check_in'],
            'CheckOut' => $params['check_out'],
            'HotelCodes' => $params['hotel_codes'], // Max 200 hotel codes
            'GuestNationality' => $params['guest_nationality'] ?? 'IN',
            'PaxRooms' => $params['pax_rooms'],
            'ResponseTime' => 20,
            'IsDetailedResponse' => true
        );
        
        return $this->make_request($url, $data);
    }
    
    /**
     * Make cURL request to TBO API
     */
    private function make_request($url, $data, $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Authorization: ' . $this->auth_header
            ),
        ));
        
        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return array('error' => 'cURL Error: ' . $error);
        }
        
        return json_decode($response, true);
    }
}

// AJAX handlers for frontend
add_action('wp_ajax_get_cities', 'ajax_get_cities');
add_action('wp_ajax_nopriv_get_cities', 'ajax_get_cities');

add_action('wp_ajax_search_hotels', 'ajax_search_hotels');
add_action('wp_ajax_nopriv_search_hotels', 'ajax_search_hotels');

function ajax_get_cities() {
    // Temporarily disable nonce check for debugging
    // check_ajax_referer('hotel_search_nonce', 'nonce');
    
    if (!isset($_POST['country_code'])) {
        wp_send_json_error('Country code is required');
        return;
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    
    if (empty($country_code)) {
        wp_send_json_error('Invalid country code');
        return;
    }
    
    error_log('Getting cities for country: ' . $country_code);
    
    $tbo_api = new TBO_Hotel_API();
    $cities = $tbo_api->get_cities($country_code);
    
    // Log for debugging
    error_log('TBO API Cities Response: ' . print_r($cities, true));
    
    // Check if we got an error
    if (isset($cities['error'])) {
        error_log('TBO API Error: ' . $cities['error']);
        wp_send_json_error($cities['error']);
        return;
    }
    
    // Send the raw response to frontend for debugging
    wp_send_json($cities);
}

function ajax_search_hotels() {
    // Temporarily disable nonce check for debugging
    // check_ajax_referer('hotel_search_nonce', 'nonce');
    
    error_log('ajax_search_hotels called with POST data: ' . print_r($_POST, true));
    
    // Validate required fields
    if (!isset($_POST['city_code']) || empty($_POST['city_code'])) {
        wp_send_json_error('City code is required');
        return;
    }
    
    if (!isset($_POST['check_in']) || empty($_POST['check_in'])) {
        wp_send_json_error('Check-in date is required');
        return;
    }
    
    if (!isset($_POST['check_out']) || empty($_POST['check_out'])) {
        wp_send_json_error('Check-out date is required');
        return;
    }
    
    $city_code = sanitize_text_field($_POST['city_code']);
    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $rooms = intval($_POST['rooms']) ?: 1;
    $adults = intval($_POST['adults']) ?: 2;
    $children = intval($_POST['children']) ?: 0;
    
    error_log("Search parameters - City: $city_code, Check-in: $check_in, Check-out: $check_out, Rooms: $rooms, Adults: $adults, Children: $children");
    
    $tbo_api = new TBO_Hotel_API();
    
    // First, get hotel codes for the city
    error_log("Getting hotel codes for city: $city_code");
    $hotel_codes_response = $tbo_api->get_hotel_codes($city_code);
    
    error_log('Hotel codes response: ' . print_r($hotel_codes_response, true));
    
    if (isset($hotel_codes_response['error'])) {
        error_log('Error getting hotel codes: ' . $hotel_codes_response['error']);
        wp_send_json_error($hotel_codes_response['error']);
        return;
    }
    
    // Extract hotel codes (limit to 200)
    $hotel_codes = array();
    if (isset($hotel_codes_response['HotelCodes']) && is_array($hotel_codes_response['HotelCodes'])) {
        $hotel_codes = array_slice($hotel_codes_response['HotelCodes'], 0, 200);
        $hotel_codes = array_column($hotel_codes, 'HotelCode');
    } else {
        wp_send_json_error('No hotels found in this city');
    }
    
    // Prepare room configuration
    $pax_rooms = array();
    for ($i = 0; $i < $rooms; $i++) {
        $room = array('Adults' => $adults);
        if ($children > 0) {
            $room['Children'] = $children;
        }
        $pax_rooms[] = $room;
    }
    
    // Search hotels
    $search_params = array(
        'check_in' => $check_in,
        'check_out' => $check_out,
        'hotel_codes' => implode(',', $hotel_codes),
        'pax_rooms' => $pax_rooms
    );
    
    $search_results = $tbo_api->search_hotels($search_params);
    
    wp_send_json($search_results);
}

// Enqueue scripts and styles
function enqueue_hotel_search_scripts() {
    if (is_front_page()) {
        wp_enqueue_script('hotel-search-js', get_template_directory_uri() . '/assets/js/hotel-search.js', array('jquery'), '1.0.0', true);
        
        wp_localize_script('hotel-search-js', 'hotel_search_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hotel_search_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'enqueue_hotel_search_scripts');
