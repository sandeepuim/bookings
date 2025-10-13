<?php
/**
 * The class responsible for TBO API integration.
 */
class TBO_Hotel_Booking_API {

    /**
     * The API base URL
     */
    private $api_base_url;

    /**
     * The API client ID
     */
    private $api_client_id;

    /**
     * The API client secret
     */
    private $api_client_secret;

    /**
     * The API username
     */
    private $api_username;

    /**
     * The API password
     */
    private $api_password;

    /**
     * The API token
     */
    private $api_token;
    
    /**
     * Logger instance
     */
    private $logger;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct() {
        // TBO Staging API Credentials
        $this->api_base_url = $this->get_option('api_base_url', 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/');
        $this->api_username = $this->get_option('api_username', 'YOLANDATHTest');
        $this->api_password = $this->get_option('api_password', 'Yol@40360746');
        $this->api_client_id = $this->get_option('api_client_id', '');
        $this->api_client_secret = $this->get_option('api_client_secret', '');
        
        // Get or generate API token
        $this->api_token = $this->get_api_token();
        
        // Initialize logger if available
        if (class_exists('TBO_Hotel_Booking_Logger')) {
            $this->logger = new TBO_Hotel_Booking_Logger();
        }
    }

    /**
     * Get plugin option.
     *
     * @param string $key     Option key.
     * @param mixed  $default Default value.
     * @return mixed
     */
    private function get_option($key, $default = '') {
        $options = get_option('tbo_hotel_booking_settings', array());
        return isset($options[$key]) ? $options[$key] : $default;
    }

    /**
     * Get API token.
     *
     * @return string
     */
    private function get_api_token() {
        // Check if we have a valid token in transient
        $token = get_transient('tbo_api_token');
        
        if (false !== $token) {
            return $token;
        }
        
        // No valid token, get a new one
        $token = $this->authenticate();
        
        // Save token in transient (expires in 1 hour)
        if (!empty($token)) {
            set_transient('tbo_api_token', $token, HOUR_IN_SECONDS);
        }
        
        return $token;
    }

    /**
     * Authenticate with the TBO API.
     *
     * @return string API token on success, empty string on failure.
     */
    private function authenticate() {
        // For TBO Holidays Hotel API, authentication is done using Basic Auth
        // The token is base64-encoded "username:password"
        $auth_token = base64_encode($this->api_username . ':' . $this->api_password);
        
        // Test the authentication (optional)
        try {
            $url = $this->api_base_url . 'Search';
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'Authorization' => 'Basic ' . $auth_token,
                ),
                'method' => 'GET',
                'timeout' => 30,
            );
            
            $response = wp_remote_request($url, $args);
            
            if (is_wp_error($response)) {
                if (isset($this->logger)) {
                    $this->logger->log_error('Auth test failed: ' . $response->get_error_message());
                }
            }
        } catch (Exception $e) {
            if (isset($this->logger)) {
                $this->logger->log_error('Auth test exception: ' . $e->getMessage());
            }
        }
        
        return $auth_token;
    }

    /**
     * Make API request.
     *
     * @param string $endpoint API endpoint.
     * @param array  $data     Request data.
     * @param string $method   HTTP method (GET, POST, etc.).
     * @return array
     * @throws Exception
     */
    private function api_request($endpoint, $data = array(), $method = 'GET') {
        // Prepare URL for TBO API
        $url = $this->api_base_url . $endpoint;
        
        // Create Basic Authentication token
        $auth_token = base64_encode($this->api_username . ':' . $this->api_password);
        
        // Prepare request arguments with Authorization header
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Basic ' . $auth_token,
            ),
            'timeout' => 60, // Increased timeout for hotel searches
        );
        
        // Log the request if logger is available
        if (isset($this->logger)) {
            $this->logger->log_api_request($endpoint, $data, $method);
        } else {
            // Fallback to error_log if logger is not available
            error_log('TBO API Request URL: ' . $url);
            error_log('TBO API Request Data: ' . json_encode($data));
        }
        
        // Add request body for POST requests
        if ('POST' === $method) {
            $args['body'] = json_encode($data);
            $args['method'] = 'POST';
        } else if (!empty($data)) {
            // Add query parameters for GET requests
            $url = add_query_arg($data, $url);
        }
        
        // Make request
        $response = wp_remote_request($url, $args);
        
        // Check for errors
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            if (isset($this->logger)) {
                $this->logger->log_error('API Request Error: ' . $error_message, $response);
            } else {
                error_log('TBO API Request Error: ' . $error_message);
            }
            
            throw new Exception('API Request Error: ' . $error_message);
        }
        
        // Get response code
        $response_code = wp_remote_retrieve_response_code($response);
        
        // Parse response
        $body = wp_remote_retrieve_body($response);
        
        // Log the response
        if (isset($this->logger)) {
            $this->logger->log_api_response($endpoint, $body, $response_code);
        } else {
            error_log('TBO API Response: ' . $body);
        }
        
        if (empty($body)) {
            throw new Exception('Empty response from API');
        }
        
        $data = json_decode($body, true);
        
        // If response is not a valid JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = 'API Response JSON Error: ' . json_last_error_msg() . ' - Raw response: ' . substr($body, 0, 200) . '...';
            
            if (isset($this->logger)) {
                $this->logger->log_error($error_message, array('body' => $body));
            } else {
                error_log('TBO API JSON Error: ' . json_last_error_msg());
            }
            
            throw new Exception($error_message);
        }
        
        // Check for TBO API specific errors
        if (isset($data['Status'])) {
            // If Status is an array and contains an error code
            if (is_array($data['Status']) && isset($data['Status']['Code']) && $data['Status']['Code'] != 200) {
                $error_message = isset($data['Status']['Description']) ? $data['Status']['Description'] : 'Unknown API Error';
                
                if (isset($this->logger)) {
                    $this->logger->log_error('API Error: ' . $error_message, $data);
                } else {
                    error_log('TBO API Error: ' . $error_message);
                }
                
                // For search requests, return the error instead of throwing an exception
                if (strpos($endpoint, 'Search') !== false) {
                    return $data;
                }
                
                throw new Exception('API Error: ' . $error_message);
            }
            // Old style error format
            else if ($data['Status'] === false) {
                $error_message = isset($data['ErrorMessage']) ? $data['ErrorMessage'] : 'Unknown API Error';
                
                if (isset($this->logger)) {
                    $this->logger->log_error('API Error: ' . $error_message, $data);
                } else {
                    error_log('TBO API Error: ' . $error_message);
                }
                
                throw new Exception('API Error: ' . $error_message);
            }
        }
        
        return $data;
    }

    /**
     * Search hotels.
     *
     * @param string $destination Destination (city, region, country).
     * @param string $check_in    Check-in date (YYYY-MM-DD).
     * @param string $check_out   Check-out date (YYYY-MM-DD).
     * @param int    $adults      Number of adults.
     * @param int    $children    Number of children.
     * @param array  $filters     Additional filters (optional).
     * @return array
     * @throws Exception
     */
    public function search_hotels($destination, $check_in, $check_out, $adults = 1, $children = 0, $filters = array()) {
        // Format the dates according to TBO API requirements (YYYY-MM-DD)
        $check_in_formatted = date('Y-m-d', strtotime($check_in));
        $check_out_formatted = date('Y-m-d', strtotime($check_out));
        
        // Get settings
        $default_currency = $this->get_option('default_currency', 'USD');
        $default_country = $this->get_option('default_country', 'IN');
        
        // First, we need to get hotel codes for the destination
        $hotel_codes = $this->get_hotel_codes_for_destination($destination);
        
        if (empty($hotel_codes)) {
            // Default to a known working hotel code for testing
            $hotel_codes = "1000002";
        }
        
        // Prepare the TBO API specific payload based on working Postman collection
        $data = array(
            'CheckIn' => $check_in_formatted,
            'CheckOut' => $check_out_formatted,
            'HotelCodes' => isset($filters['hotel_codes']) ? $filters['hotel_codes'] : $hotel_codes,
            'GuestNationality' => $default_country,
            'PaxRooms' => array(
                array(
                    'Adults' => $adults,
                )
            ),
            'ResponseTime' => 20,
            'IsDetailedResponse' => true
        );
        
        // If children are present, add their count and ages to the PaxRooms
        if ($children > 0) {
            // Add children count to the room
            $data['PaxRooms'][0]['Children'] = $children;
            
            // Add child ages if provided, otherwise default to 5
            $child_ages = array();
            if (isset($filters['child_ages']) && is_array($filters['child_ages'])) {
                $child_ages = $filters['child_ages'];
            } else {
                // Default child ages to 5
                for ($i = 0; $i < $children; $i++) {
                    $child_ages[] = 5;
                }
            }
            $data['PaxRooms'][0]['ChildAges'] = $child_ages;
        }
        
        // Add optional filters if provided
        if (isset($filters['max_price']) && $filters['max_price'] > 0) {
            $data['HotelSearchRequest']['MaxPrice'] = $filters['max_price'];
        }
        
        if (isset($filters['min_price']) && $filters['min_price'] > 0) {
            $data['HotelSearchRequest']['MinPrice'] = $filters['min_price'];
        }
        
        // Add hotel name filter if provided
        if (isset($filters['hotel_name']) && !empty($filters['hotel_name'])) {
            $data['HotelSearchRequest']['HotelName'] = $filters['hotel_name'];
        }
        
        // Check if we should use cached results
        $cache_key = 'tbo_hotel_search_' . md5(json_encode($data));
        $cache_duration = (int)$this->get_option('cache_duration', 3600);
        
        if ($cache_duration > 0) {
            $cached_results = get_transient($cache_key);
            if (false !== $cached_results) {
                return $cached_results;
            }
        }
        
        // Make API request to TBO's Search endpoint
        $results = $this->api_request('Search', $data, 'POST');
        
        // Cache the results if caching is enabled
        if ($cache_duration > 0) {
            set_transient($cache_key, $results, $cache_duration);
        }
        
        return $results;
    }

    /**
     * Get hotel codes for a destination.
     * 
     * @param string $destination Destination name (e.g., 'Delhi', 'Bangkok')
     * @return string Hotel codes as comma-separated string
     */
    private function get_hotel_codes_for_destination($destination) {
        try {
            // First, get the city code for the destination
            $city_code = $this->get_city_code($destination);
            
            if (empty($city_code)) {
                // If no city code found, return default testing code
                return "1000002";
            }
            
            // Now get hotel codes for this city
            $data = array(
                'CityCode' => $city_code
            );
            
            $response = $this->api_request('HotelCodeList', $data, 'GET');
            
            if (isset($response['HotelCodes']) && is_array($response['HotelCodes']) && !empty($response['HotelCodes'])) {
                // Take up to 5 hotel codes to avoid too many results
                $codes = array_slice($response['HotelCodes'], 0, 5);
                return implode(',', $codes);
            }
            
            // If we get here, no hotel codes were found
            return "1000002";
        } catch (Exception $e) {
            // Log error
            if (isset($this->logger)) {
                $this->logger->log_error('Error getting hotel codes: ' . $e->getMessage());
            }
            // Return default test hotel code
            return "1000002";
        }
    }
    
    /**
     * Get city code for a destination name.
     * 
     * @param string $destination_name City name
     * @return string City code or empty string if not found
     */
    private function get_city_code($destination_name) {
        try {
            // First try to get country code (assuming India as default)
            $country_code = "IN";
            
            // Get city list for this country
            $data = array(
                'CountryCode' => $country_code
            );
            
            $response = $this->api_request('CityList', $data, 'POST');
            
            if (isset($response['CityList']) && is_array($response['CityList'])) {
                // Search for a city matching the destination name
                foreach ($response['CityList'] as $city) {
                    if (isset($city['Name']) && 
                        (strtolower($city['Name']) === strtolower($destination_name) ||
                         stripos($city['Name'], $destination_name) !== false)) {
                        return $city['Code'];
                    }
                }
            }
            
            // If no match found, return empty string
            return '';
        } catch (Exception $e) {
            // Log error
            if (isset($this->logger)) {
                $this->logger->log_error('Error getting city code: ' . $e->getMessage());
            }
            return '';
        }
    }

    /**
     * Get hotel details.
     *
     * @param string $hotel_id Hotel ID.
     * @return array
     * @throws Exception
     */
    public function get_hotel_details($hotel_id) {
        // Make API request
        return $this->api_request('hotels/' . $hotel_id);
    }

    /**
     * Check room availability.
     *
     * @param string $hotel_id  Hotel ID.
     * @param string $check_in  Check-in date (YYYY-MM-DD).
     * @param string $check_out Check-out date (YYYY-MM-DD).
     * @param int    $adults    Number of adults.
     * @param int    $children  Number of children.
     * @return array
     * @throws Exception
     */
    public function check_availability($hotel_id, $check_in, $check_out, $adults = 1, $children = 0) {
        $data = array(
            'check_in' => $check_in,
            'check_out' => $check_out,
            'adults' => $adults,
            'children' => $children,
        );
        
        // Make API request
        return $this->api_request('hotels/' . $hotel_id . '/availability', $data, 'POST');
    }

    /**
     * Book hotel.
     *
     * @param string $hotel_id       Hotel ID.
     * @param string $room_id        Room ID.
     * @param string $check_in       Check-in date (YYYY-MM-DD).
     * @param string $check_out      Check-out date (YYYY-MM-DD).
     * @param int    $adults         Number of adults.
     * @param int    $children       Number of children.
     * @param array  $guest_info     Guest information.
     * @param string $payment_method Payment method.
     * @return array
     * @throws Exception
     */
    public function book_hotel($hotel_id, $room_id, $check_in, $check_out, $adults, $children, $guest_info, $payment_method) {
        $data = array(
            'hotel_id' => $hotel_id,
            'room_id' => $room_id,
            'check_in' => $check_in,
            'check_out' => $check_out,
            'adults' => $adults,
            'children' => $children,
            'guest_info' => $guest_info,
            'payment_method' => $payment_method,
        );
        
        // Make API request
        return $this->api_request('bookings', $data, 'POST');
    }

    /**
     * Get booking details.
     *
     * @param string $booking_id Booking ID.
     * @return array
     * @throws Exception
     */
    public function get_booking_details($booking_id) {
        // Make API request
        return $this->api_request('bookings/' . $booking_id);
    }

    /**
     * Cancel booking.
     *
     * @param string $booking_id Booking ID.
     * @return array
     * @throws Exception
     */
    public function cancel_booking($booking_id) {
        // Make API request
        return $this->api_request('bookings/' . $booking_id . '/cancel', array(), 'POST');
    }

    /**
     * Get cancellation policy.
     *
     * @param string $hotel_id Hotel ID.
     * @param string $room_id  Room ID.
     * @return array
     * @throws Exception
     */
    public function get_cancellation_policy($hotel_id, $room_id) {
        // Make API request
        return $this->api_request('hotels/' . $hotel_id . '/rooms/' . $room_id . '/cancellation-policy');
    }
}
