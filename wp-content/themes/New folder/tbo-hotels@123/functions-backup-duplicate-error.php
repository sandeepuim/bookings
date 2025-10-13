<?php
/**
 * TBO Hotels Theme functions and definitions
 *
 * @package TBO_Hotels
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define theme constants
define('TBO_HOTELS_VERSION', '1.0.0');
define('TBO_HOTELS_DIR', get_template_directory());
define('TBO_HOTELS_URI', get_template_directory_uri());

// Define TBO API constants - Updated to match your exact API URLs
define('TBO_API_BASE_URL', 'http://api.tbotechnology.in/TBOHolidays_HotelAPI');
define('TBO_API_USERNAME', 'YOLANDATHTest');
define('TBO_API_PASSWORD', 'Yol@40360746');

/**
 * Get TBO API authorization header
 */
function tbo_hotels_get_auth_header() {
    return 'Basic ' . base64_encode(TBO_API_USERNAME . ':' . TBO_API_PASSWORD);
}

/**
 * Make API request to TBO Hotels API
 */
function tbo_hotels_api_request($endpoint, $data = array(), $method = 'GET') {
    $url = TBO_API_BASE_URL . '/' . $endpoint;
    
    $headers = array(
        'Authorization' => tbo_hotels_get_auth_header(),
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    );
    
    $args = array(
        'headers' => $headers,
        'timeout' => 30,
        'sslverify' => false,
    );
    
    if ($method === 'POST') {
        $args['method'] = 'POST';
        $args['body'] = json_encode($data);
        $response = wp_remote_post($url, $args);
    } else {
        if (!empty($data)) {
            $url = add_query_arg($data, $url);
        }
        $response = wp_remote_get($url, $args);
    }
    
    if (is_wp_error($response)) {
        error_log('TBO API Error: ' . $response->get_error_message());
        return $response;
    }
    
    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        error_log('TBO API HTTP Error: ' . $response_code);
        return new WP_Error('api_error', 'API request failed with status: ' . $response_code);
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('TBO API JSON Error: ' . json_last_error_msg());
        return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
    }
    
    return $data;
}

/**
 * Get Countries from TBO API
 */
function tbo_hotels_get_countries() {
    $cache_key = 'tbo_hotels_countries';
    $countries = get_transient($cache_key);
    
    if (false !== $countries) {
        return $countries;
    }
    
    $response = tbo_hotels_api_request('CountryList', array(), 'GET');
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    if (isset($response['CountryList']) && is_array($response['CountryList'])) {
        set_transient($cache_key, $response['CountryList'], 24 * HOUR_IN_SECONDS);
        return $response['CountryList'];
    }
    
    return new WP_Error('missing_countries', 'Countries not found in API response');
}

/**
 * Get Cities from TBO API
 */
function tbo_hotels_get_cities($country_code) {
    if (empty($country_code)) {
        return new WP_Error('invalid_country', 'Country code is required');
    }
    
    $cache_key = 'tbo_hotels_cities_' . $country_code;
    $cities = get_transient($cache_key);
    
    if (false !== $cities) {
        return $cities;
    }
    
    $data = array('CountryCode' => $country_code);
    $response = tbo_hotels_api_request('CityList', $data, 'POST');
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    if (isset($response['CityList']) && is_array($response['CityList'])) {
        set_transient($cache_key, $response['CityList'], 12 * HOUR_IN_SECONDS);
        return $response['CityList'];
    }
    
    return new WP_Error('missing_cities', 'Cities not found in API response');
}

/**
 * Get Hotel Codes from TBO API
 */
function tbo_hotels_get_hotel_codes($city_code) {
    if (empty($city_code)) {
        return new WP_Error('invalid_city', 'City code is required');
    }
    
    $cache_key = 'tbo_hotels_codes_' . $city_code;
    $hotel_codes = get_transient($cache_key);
    
    if (false !== $hotel_codes) {
        return $hotel_codes;
    }
    
    $data = array('CityCode' => $city_code);
    $response = tbo_hotels_api_request('HotelCodeList', $data, 'GET');
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    if (isset($response['HotelCodes']) && is_array($response['HotelCodes'])) {
        set_transient($cache_key, $response['HotelCodes'], 6 * HOUR_IN_SECONDS);
        return $response['HotelCodes'];
    }
    
    return new WP_Error('missing_hotel_codes', 'Hotel codes not found in API response');
}

/**
 * Search Hotels using TBO API
 */
function tbo_hotels_search_hotels($params) {
    // Validate required parameters
    $required = array('city_code', 'check_in', 'check_out');
    foreach ($required as $field) {
        if (empty($params[$field])) {
            return new WP_Error('missing_param', "Required parameter '$field' is missing");
        }
    }
    
    // Get hotel codes for the city
    $hotel_codes = tbo_hotels_get_hotel_codes($params['city_code']);
    if (is_wp_error($hotel_codes)) {
        return $hotel_codes;
    }
    
    // Limit to 100 hotel codes as per your requirement
    $hotel_codes = array_slice($hotel_codes, 0, 100);
    
    // Convert to comma-separated string
    $hotel_codes_string = implode(',', $hotel_codes);
    
    // Prepare search data according to your API structure
    $search_data = array(
        'CheckIn' => $params['check_in'],
        'CheckOut' => $params['check_out'],
        'HotelCodes' => $hotel_codes_string,
        'GuestNationality' => 'IN',
        'PaxRooms' => array(),
        'ResponseTime' => 20,
        'IsDetailedResponse' => true
    );
    
    // Add room information
    for ($i = 0; $i < intval($params['rooms']); $i++) {
        $room = array('Adults' => intval($params['adults']));
        if (!empty($params['children']) && intval($params['children']) > 0) {
            $room['Children'] = intval($params['children']);
            $room['ChildrenAges'] = array_fill(0, intval($params['children']), 5);
        }
        $search_data['PaxRooms'][] = $room;
    }
    
    // Make API request to Search endpoint
    $response = tbo_hotels_api_request('Search', $search_data, 'POST');
    
    if (is_wp_error($response)) {
        return $response;
    }
    
    if (isset($response['HotelResult']) && is_array($response['HotelResult'])) {
        return array(
            'Status' => $response['Status'],
            'Hotels' => $response['HotelResult'],
            'TotalHotels' => count($response['HotelResult'])
        );
    }
    
    return new WP_Error('no_hotels', 'No hotels found for the given search criteria');
}

/**
 * Mock API responses for local development
 * 
 * @param string $endpoint API endpoint
 * @param array $data Request data
 * @param string $method Request method
 * @return array Mock response data
 */
function tbo_hotels_mock_api_response($endpoint, $data = array(), $method = 'GET') {
    error_log('Using mock API response for: ' . $endpoint);
    
    // Mock response for country list
    if ($endpoint === 'CountryList') {
        return array(
            'Status' => array(
                'Code' => 200,
                'Description' => 'Success'
            ),
            'CountryList' => array(
                array('Code' => 'US', 'Name' => 'United States'),
                array('Code' => 'GB', 'Name' => 'United Kingdom'),
                array('Code' => 'FR', 'Name' => 'France'),
                array('Code' => 'DE', 'Name' => 'Germany'),
                array('Code' => 'ES', 'Name' => 'Spain'),
                array('Code' => 'IT', 'Name' => 'Italy'),
                array('Code' => 'CA', 'Name' => 'Canada'),
                array('Code' => 'IN', 'Name' => 'India'),
                array('Code' => 'AU', 'Name' => 'Australia'),
                array('Code' => 'JP', 'Name' => 'Japan'),
            )
        );
    }
    
    // Mock response for city list
    if ($endpoint === 'CityList' && isset($data['CountryCode'])) {
        $cities = array();
        
        switch ($data['CountryCode']) {
            case 'US':
                $cities = array(
                    array('Code' => 'NYC', 'Name' => 'New York'),
                    array('Code' => 'LAX', 'Name' => 'Los Angeles'),
                    array('Code' => 'CHI', 'Name' => 'Chicago'),
                    array('Code' => 'MIA', 'Name' => 'Miami'),
                );
                break;
            case 'GB':
                $cities = array(
                    array('Code' => 'LON', 'Name' => 'London'),
                    array('Code' => 'MAN', 'Name' => 'Manchester'),
                    array('Code' => 'EDI', 'Name' => 'Edinburgh'),
                );
                break;
            case 'IN':
                $cities = array(
                    array('Code' => 'DEL', 'Name' => 'Delhi'),
                    array('Code' => 'MUM', 'Name' => 'Mumbai'),
                    array('Code' => 'BLR', 'Name' => 'Bangalore'),
                    array('Code' => 'CCU', 'Name' => 'Kolkata'),
                    array('Code' => 'MAA', 'Name' => 'Chennai'),
                );
                break;
            default:
                $cities = array(
                    array('Code' => 'CITY1', 'Name' => 'City 1'),
                    array('Code' => 'CITY2', 'Name' => 'City 2'),
                    array('Code' => 'CITY3', 'Name' => 'City 3'),
                );
        }
        
        return array(
            'Status' => array(
                'Code' => 200,
                'Description' => 'Success'
            ),
            'CityList' => $cities
        );
    }
    
    // Mock response for hotel search
    if ($endpoint === 'HotelSearch') {
        // Generate some random hotels
        $hotels = array();
        $count = rand(5, 15);
        
        for ($i = 1; $i <= $count; $i++) {
            $starRating = rand(3, 5);
            $price = rand(80, 500);
            
            $hotels[] = array(
                'HotelCode' => 'HTL' . $i,
                'HotelName' => 'Hotel Sample ' . $i,
                'HotelAddress' => 'Sample Address, City',
                'HotelDescription' => 'This is a mock hotel description for testing purposes. This hotel offers comfortable rooms and great amenities.',
                'StarRating' => $starRating,
                'HotelFacilities' => array('WiFi', 'Swimming Pool', 'Restaurant', 'Parking', 'Gym'),
                'HotelPicture' => '',
                'MinHotelPrice' => $price,
                'Price' => array('CurrencyCode' => 'USD'),
                'OriginalPrice' => ($i % 3 === 0) ? $price * 1.2 : $price,
            );
        }
        
        return array(
            'Status' => array(
                'Code' => 200,
                'Description' => 'Success'
            ),
            'HotelResult' => $hotels
        );
    }
    
    // Mock response for hotel code list
    if ($endpoint === 'HotelCodeList' && isset($data['DestinationCode'])) {
        // Generate mock hotel codes
        $codes = array();
        $count = rand(20, 50);
        
        for ($i = 1; $i <= $count; $i++) {
            $codes[] = 'HTL' . $i;
        }
        
        return array(
            'Status' => array(
                'Code' => 200,
                'Description' => 'Success'
            ),
            'HotelCodes' => $codes
        );
    }
    
    // Default empty response
    return array();
}

/**
 * Theme Setup
 */
function tbo_hotels_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('customize-selective-refresh-widgets');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'tbo-hotels'),
        'footer' => __('Footer Menu', 'tbo-hotels'),
    ));
}
add_action('after_setup_theme', 'tbo_hotels_setup');

/**
 * Enqueue scripts and styles
 */
function tbo_hotels_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('tbo-hotels-style', get_stylesheet_uri(), array(), TBO_HOTELS_VERSION);
    
    // Enqueue hotel search styles
    wp_enqueue_style('tbo-hotels-search', TBO_HOTELS_URI . '/assets/css/hotel-search.css', array(), TBO_HOTELS_VERSION);
    
    // Enqueue hotel results styles
    wp_enqueue_style('tbo-hotels-results', TBO_HOTELS_URI . '/assets/css/hotel-results.css', array(), TBO_HOTELS_VERSION);
    
    // Enqueue hotel details styles
    wp_enqueue_style('tbo-hotels-details', TBO_HOTELS_URI . '/assets/css/hotel-details.css', array(), TBO_HOTELS_VERSION);
    
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue hotel search script
    wp_enqueue_script('tbo-hotels-search', TBO_HOTELS_URI . '/assets/js/hotel-search.js', array('jquery'), TBO_HOTELS_VERSION, true);
    
    // Localize script with AJAX URL and placeholder image
    wp_localize_script('tbo-hotels-search', 'tbo_hotels_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbo_hotels_nonce'),
        'placeholder_image' => TBO_HOTELS_URI . '/assets/img/placeholder.jpg'
    ));
}
add_action('wp_enqueue_scripts', 'tbo_hotels_scripts');

/**
 * Include required files
 */
require_once TBO_HOTELS_DIR . '/inc/template-functions.php';

/**
 * TBO API Functions
 */

/**
 * Direct cURL request as a fallback when wp_remote_post/get fails
 * 
 * @param string $url API URL
 * @param array $data Request data
 * @param string $method Request method
 * @return array|WP_Error Response data or WP_Error
 */
function tbo_hotels_direct_curl_request($url, $data = array(), $method = 'GET') {
    if (!function_exists('curl_init')) {
        return new WP_Error('curl_not_available', 'cURL is not available on this server.');
    }
    
    error_log('Executing direct cURL request to: ' . $url);
    
    $curl = curl_init();
    
    $headers = array(
        'Authorization: ' . tbo_hotels_get_auth_header(),
        'Content-Type: application/json',
        'Accept: application/json'
    );
    
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // Disable SSL verification
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false); // Disable SSL host verification
    
    if ($method === 'POST') {
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $info = curl_getinfo($curl);
    curl_close($curl);
    
    if ($err) {
        error_log('cURL Error: ' . $err);
        return new WP_Error('curl_error', 'cURL Error: ' . $err);
    }
    
    error_log('cURL Response Code: ' . $info['http_code']);
    error_log('cURL Response Body: ' . $response);
    
    if ($info['http_code'] !== 200) {
        return new WP_Error('api_error', 'API request failed with status: ' . $info['http_code']);
    }
    
    $data = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
    }
    
    return $data;
}

/**
 * Make API request to TBO API
 * 
 * @param string $endpoint API endpoint
 * @param array $data Request data
 * @param string $method Request method (GET or POST)
 * @return array|WP_Error Response data or WP_Error
 */
function tbo_hotels_api_request($endpoint, $data = array(), $method = 'GET') {
    $url = TBO_API_BASE_URL . '/' . $endpoint;
    
    // Check if we're in a development environment (localhost)
    $is_local = strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
               strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false;
    
    // For local development, use mock data instead of actual API calls
    // Comment out this section if you want to test with the real API
    if ($is_local && false) { // Set to true to use mock data, false to use real API
        return tbo_hotels_mock_api_response($endpoint, $data, $method);
    }
    
    // Add enhanced debug information to help troubleshoot
    error_log('TBO API Request to: ' . $url);
    error_log('Request Method: ' . $method);
    error_log('Request Data: ' . json_encode($data));
    error_log('Auth Header: ' . tbo_hotels_get_auth_header());
    
    // For debugging: Try to mimic the exact Postman request structure
    $args = array(
        'headers' => array(
            'Authorization' => tbo_hotels_get_auth_header(),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ),
        'timeout' => 30,
        'sslverify' => false, // Try disabling SSL verification if you're having SSL issues
    );
    
    if ($method === 'POST') {
        $args['method'] = 'POST';
        $args['body'] = json_encode($data);
        
        // Log the exact request we're sending
        error_log('Making POST request with args: ' . json_encode($args));
        
        $response = wp_remote_post($url, $args);
    } else {
        // For GET requests, add parameters to URL if present
        if (!empty($data)) {
            $url = add_query_arg($data, $url);
        }
        
        // Log the exact request we're sending
        error_log('Making GET request to: ' . $url);
        
        $response = wp_remote_get($url, $args);
    }
    
    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('TBO API Error: ' . $error_message);
        error_log('Request URL: ' . $url);
        error_log('Request Args: ' . json_encode($args));
        
        // Try using cURL directly as a last resort
        if (strpos($error_message, 'Could not resolve host') !== false || 
            strpos($error_message, 'connection failed') !== false) {
            error_log('Trying with direct cURL as fallback...');
            return tbo_hotels_direct_curl_request($url, $data, $method);
        }
        
        return $response;
    }
    
    // Get response code
    $response_code = wp_remote_retrieve_response_code($response);
    
    // Check if response is successful
    if ($response_code !== 200) {
        error_log('TBO API HTTP Error: ' . $response_code);
        error_log('Response: ' . wp_remote_retrieve_body($response));
        return new WP_Error('api_error', 'API request failed with status: ' . $response_code);
    }
    
    // Get response body
    $body = wp_remote_retrieve_body($response);
    
    // Decode JSON response
    $data = json_decode($body, true);
    
    // Check if JSON was decoded successfully
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('TBO API JSON Error: ' . json_last_error_msg());
        error_log('Response Body: ' . $body);
        return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
    }
    
    // Log success
    error_log('TBO API Success for: ' . $endpoint);
    
    return $data;
}

/**
 * Get Countries from TBO API
 * Cached for 24 hours
 * 
 * @return array|WP_Error Array of countries or WP_Error
 */
function tbo_hotels_get_countries() {
    // Check for cached data
    $cache_key = 'tbo_hotels_countries';
    $countries = get_transient($cache_key);
    
    if (false !== $countries) {
        return $countries;
    }
    
    // Make API request
    $response = tbo_hotels_api_request('CountryList', array(), 'POST');
    
    // Check for errors
    if (is_wp_error($response)) {
        return $response;
    }
    
    // Extract countries from response
    if (isset($response['CountryList']) && is_array($response['CountryList'])) {
        // Format the response to match what our code expects
        $formatted_countries = array();
        foreach ($response['CountryList'] as $country) {
            $formatted_countries[] = array(
                'Code' => $country['Code'],
                'Name' => $country['Name']
            );
        }
        
        // Cache countries for 24 hours
        set_transient($cache_key, $formatted_countries, 24 * HOUR_IN_SECONDS);
        
        return $formatted_countries;
    }
    
    // Return error if countries not found
    return new WP_Error('missing_countries', 'Countries not found in API response');
}

/**
 * Get Cities for a Country from TBO API
 * Cached for 12 hours
 * 
 * @param string $country_code Country code
 * @return array|WP_Error Array of cities or WP_Error
 */
function tbo_hotels_get_cities($country_code) {
    // Validate country code
    if (empty($country_code)) {
        return new WP_Error('invalid_country', 'Country code is required');
    }
    
    // Check for cached data
    $cache_key = 'tbo_hotels_cities_' . $country_code;
    $cities = get_transient($cache_key);
    
    if (false !== $cities) {
        return $cities;
    }
    
    // Prepare request data
    $data = array(
        'CountryCode' => $country_code
    );
    
    // Make API request (Note: CityList requires POST and TBOHolidays_HotelAPI URL path)
    // CityList specifically uses the TBOHolidays_HotelAPI path, not hotelapi_v10
    $alt_url = str_replace('hotelapi_v10', 'TBOHolidays_HotelAPI', TBO_API_BASE_URL);
    $response = wp_remote_post(
        $alt_url . '/CityList',
        array(
            'headers' => array(
                'Authorization' => tbo_hotels_get_auth_header(),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ),
            'body' => json_encode($data),
            'timeout' => 30,
            'sslverify' => false,
        )
    );
    
    // Check for errors
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        error_log('CityList API Error: ' . $error_message);
        
        // Try direct cURL as fallback
        $direct_response = tbo_hotels_direct_curl_request(
            'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList', 
            $data, 
            'POST'
        );
        
        if (is_wp_error($direct_response)) {
            return $response; // Return original error
        }
        
        $response = $direct_response;
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);
        
        if ($response_code !== 200) {
            error_log('CityList API Error: Received status code ' . $response_code);
            error_log('Response body: ' . $response_body);
            return new WP_Error('api_error', 'API returned status code: ' . $response_code);
        }
        
        $response = json_decode($response_body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new WP_Error('json_error', 'Failed to parse API response: ' . json_last_error_msg());
        }
    }
    
    // Extract cities from response
    if (isset($response['CityList']) && is_array($response['CityList'])) {
        // Format the response to match what our JavaScript expects
        $formatted_cities = array();
        foreach ($response['CityList'] as $city) {
            $formatted_cities[] = array(
                'Destination' => array(
                    'DestinationCode' => $city['Code'],
                    'DestinationName' => $city['Name']
                )
            );
        }
        
        // Cache cities for 12 hours
        set_transient($cache_key, $formatted_cities, 12 * HOUR_IN_SECONDS);
        
        return $formatted_cities;
    }
    
    // Return error if cities not found
    return new WP_Error('missing_cities', 'Cities not found in API response');
}

/**
 * Get Hotel Codes for a City from TBO API
 * Cached for 6 hours
 * 
 * @param string $city_code City code
 * @return array|WP_Error Array of hotel codes or WP_Error
 */
function tbo_hotels_get_hotel_codes($city_code) {
    // Validate city code
    if (empty($city_code)) {
        error_log('ERROR: Empty city code provided to tbo_hotels_get_hotel_codes');
        return new WP_Error('invalid_city', 'City code is required');
    }
    
    // Check for cached data
    $cache_key = 'tbo_hotels_codes_' . $city_code;
    $hotel_codes = get_transient($cache_key);
    
    if (false !== $hotel_codes) {
        error_log('Using cached hotel codes for city: ' . $city_code . ' (count: ' . count($hotel_codes) . ')');
        return $hotel_codes;
    }
    
    // Prepare request data - note: API expects CityCode, not DestinationCode
    $data = array(
        'CityCode' => $city_code,
        'IsDetailedResponse' => true
    );
    
    // Log debug information
    error_log('HotelCodeList Request for city code: ' . $city_code);
    error_log('HotelCodeList Request data: ' . json_encode($data));
    
    // Make API request (using GET method)
    $response = tbo_hotels_api_request('HotelCodeList', $data, 'GET');
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('HotelCodeList Error with GET: ' . $response->get_error_message());
        
        // Try direct cURL request as last resort
        error_log('Trying direct cURL request for HotelCodeList...');
        $direct_response = tbo_hotels_direct_curl_request(TBO_API_BASE_URL . '/HotelCodeList', $data, 'GET');
        
        if (is_wp_error($direct_response)) {
            error_log('Direct cURL request also failed: ' . $direct_response->get_error_message());
            return $direct_response;
        } else {
            error_log('Direct cURL request worked! Processing response...');
            $response = $direct_response;
        }
    } else {
        error_log('HotelCodeList API response received: ' . json_encode(array_slice($response, 0, 5)));
    }
    
    // Extract hotel codes from response
    $hotel_codes = array();
    
    // Check for hotel codes in different response formats
    if (isset($response['Hotels']) && is_array($response['Hotels'])) {
        // Format from /hotelapi_v10/HotelCodeList endpoint
        foreach ($response['Hotels'] as $hotel) {
            if (isset($hotel['HotelCode'])) {
                $hotel_codes[] = $hotel['HotelCode'];
            }
        }
        error_log('Found hotel codes in Hotels property: ' . count($hotel_codes));
    } elseif (isset($response['HotelCodes']) && is_array($response['HotelCodes'])) {
        $hotel_codes = $response['HotelCodes'];
        error_log('Found hotel codes in HotelCodes property: ' . count($hotel_codes));
    } elseif (isset($response['Result']) && is_array($response['Result'])) {
        $hotel_codes = $response['Result'];
        error_log('Found hotel codes in Result property: ' . count($hotel_codes));
    } elseif (isset($response['HotelCodesArray']) && is_array($response['HotelCodesArray'])) {
        $hotel_codes = $response['HotelCodesArray'];
        error_log('Found hotel codes in HotelCodesArray property: ' . count($hotel_codes));
    } else {
        // Log the entire response structure to see what we're dealing with
        error_log('Could not find hotel codes in standard properties. Response structure:');
        error_log('Response keys: ' . json_encode(array_keys($response)));
        error_log('Full response (truncated): ' . substr(json_encode($response), 0, 1000) . '...');
        
        // Check if response is a non-standard format
        foreach ($response as $key => $value) {
            if (is_array($value) && !empty($value)) {
                error_log('Possible hotel codes in key: ' . $key . ' (count: ' . count($value) . ')');
                
                // Try to use this if we can't find anything else
                if (empty($hotel_codes)) {
                    $hotel_codes = $value;
                }
            }
        }
    }
    
    // If no hotel codes found, return error
    if (empty($hotel_codes)) {
        error_log('No hotel codes found in API response');
        return new WP_Error('missing_hotel_codes', 'Hotel codes not found in API response');
    }
    
    // Log first few hotel codes for debugging
    error_log('First 5 hotel codes: ' . json_encode(array_slice($hotel_codes, 0, 5)));
    
    // Filter out non-numeric codes
    $original_count = count($hotel_codes);
    $hotel_codes = array_filter($hotel_codes, function($code) {
        $is_valid = is_numeric($code) && strlen($code) >= 5;
        if (!$is_valid && !empty($code)) {
            error_log('Filtering out invalid hotel code: ' . json_encode($code));
        }
        return $is_valid;
    });
    
    // Check if we filtered out too many codes
    $filtered_count = count($hotel_codes);
    error_log("Hotel codes: original count = $original_count, after filtering = $filtered_count");
    
    // If all codes were filtered out, log more details and try a more lenient approach
    if ($filtered_count === 0 && $original_count > 0) {
        error_log('Warning: All hotel codes were filtered out. Using original codes without filtering.');
        
        // Just use the original codes without filtering
        $hotel_codes = array_values($response['Hotels'] ?? $response['HotelCodes'] ?? $response['Result'] ?? $response['HotelCodesArray'] ?? []);
    }
    
    // Cache hotel codes for 6 hours
    set_transient($cache_key, $hotel_codes, 6 * HOUR_IN_SECONDS);
    
    return $hotel_codes;
}

/**
 * Search Hotels from TBO API
 * Implement chunking logic for large number of hotel codes
 * 
 * @param array $params Search parameters
 * @return array|WP_Error Search results or WP_Error
 */
function tbo_hotels_search_hotels($params) {
    // Validate required parameters
    $required = array('check_in', 'check_out');
    foreach ($required as $field) {
        if (empty($params[$field])) {
            return new WP_Error('missing_param', "Required parameter '$field' is missing");
        }
    }
    
    // Validate that either city_code or hotel_code is provided
    if (empty($params['city_code']) && empty($params['hotel_code'])) {
        return new WP_Error('missing_param', "Either 'city_code' or 'hotel_code' is required");
    }
    
    // Set default values for optional parameters
    $params['adults'] = !empty($params['adults']) ? intval($params['adults']) : 1;
    $params['rooms'] = !empty($params['rooms']) ? intval($params['rooms']) : 1;
    $params['children'] = !empty($params['children']) ? intval($params['children']) : 0;
    $params['max_codes'] = !empty($params['max_codes']) ? intval($params['max_codes']) : 10; // Default to 10 for performance
    
    // Determine hotel codes to use
    $hotel_codes = array();
    
    // If specific hotel_code is provided, use it directly
    if (!empty($params['hotel_code'])) {
        error_log('Using directly provided hotel code: ' . $params['hotel_code']);
        $hotel_codes = array($params['hotel_code']);
    } 
    // Otherwise get hotel codes for the city
    elseif (!empty($params['city_code'])) {
        error_log('Getting hotel codes for city: ' . $params['city_code']);
        $city_code = $params['city_code'];
        
        // Force refresh of hotel codes for debugging if needed
        if (isset($params['refresh_codes']) && $params['refresh_codes']) {
            error_log('Forcing refresh of hotel codes cache for city: ' . $city_code);
            delete_transient('tbo_hotels_codes_' . $city_code);
        }
        
        $hotel_codes = tbo_hotels_get_hotel_codes($city_code);
        
        // Check for errors
        if (is_wp_error($hotel_codes)) {
            error_log('Error getting hotel codes: ' . $hotel_codes->get_error_message());
            return $hotel_codes;
        }
        
        // Log the number of hotel codes found
        error_log('Found ' . count($hotel_codes) . ' hotel codes for city: ' . $city_code);
        error_log('First 5 hotel codes: ' . json_encode(array_slice($hotel_codes, 0, 5)));
    }
    
    // If no hotel codes found, return error
    if (empty($hotel_codes)) {
        error_log('No hotel codes found for search');
        return new WP_Error('no_hotel_codes', 'No hotel codes found for the given search criteria');
    }
    
    // Limit the number of hotel codes for better performance
    if (count($hotel_codes) > $params['max_codes']) {
        error_log('Limiting hotel codes from ' . count($hotel_codes) . ' to ' . $params['max_codes']);
        $hotel_codes = array_slice($hotel_codes, 0, $params['max_codes']);
    }
    
    // Define chunk size (TBO API recommends max 200 hotel codes per request)
    $chunk_size = 200;
    
    // Split hotel codes into chunks
    $chunks = array_chunk($hotel_codes, $chunk_size);
    
    // Prepare for storing results
    $all_results = array();
    $combined_hotels = array();
    
    // Process each chunk
    foreach ($chunks as $chunk_index => $chunk) {
        // Prepare search request data
        $search_data = array(
            'CheckIn' => $params['check_in'],
            'CheckOut' => $params['check_out'],
            'HotelCodes' => implode(',', $chunk), // Convert array to comma-separated string
            'GuestNationality' => 'IN', // Default to Indian nationality
            'PaxRooms' => array()
        );
        
        // Log the request data for debugging
        error_log('Processing chunk ' . ($chunk_index + 1) . ' of ' . count($chunks) . ' with ' . count($chunk) . ' hotel codes');
        if (!empty($chunk)) {
            error_log('Sample hotel codes: ' . implode(',', array_slice($chunk, 0, 5)));
        }
        
        // Add room information
        for ($i = 0; $i < $params['rooms']; $i++) {
            $search_data['PaxRooms'][] = array(
                'Adults' => $params['adults'],
                'Children' => $params['children'],
                'ChildrenAges' => array_fill(0, $params['children'], 5) // Default age 5 for all children
            );
        }
        
        // Add room information
        for ($i = 0; $i < $params['rooms']; $i++) {
            $search_data['PaxRooms'][] = array(
                'Adults' => $params['adults'],
                'Children' => $params['children'],
                'ChildrenAges' => array_fill(0, $params['children'], 5) // Default age 5 for all children
            );
        }
        
        // Make API request
    $response = tbo_hotels_api_request('HotelSearch', $search_data, 'POST');
    
    // Check for errors
    if (is_wp_error($response)) {
        error_log('Hotel search error: ' . $response->get_error_message());
        error_log('Trying direct cURL as fallback');
        
        // Try direct cURL as fallback
        $direct_response = tbo_hotels_direct_curl_request(
            TBO_API_BASE_URL . '/HotelSearch',
            $search_data,
            'POST'
        );
        
        if (is_wp_error($direct_response)) {
            error_log('Direct cURL also failed: ' . $direct_response->get_error_message());
            continue; // Skip this chunk and continue with others
        } else {
            error_log('Direct cURL succeeded!');
            $response = $direct_response;
        }
    }
    
    error_log('Hotel search response received. Response keys: ' . json_encode(array_keys($response)));
        
        // Store chunk results
        $all_results[] = $response;
        
        // Extract hotels from response
        $hotels = array();
        
        if (isset($response['Hotels']) && is_array($response['Hotels'])) {
            $hotels = $response['Hotels'];
            error_log('Found hotels in Hotels property: ' . count($hotels));
        } elseif (isset($response['HotelResult']) && is_array($response['HotelResult'])) {
            $hotels = $response['HotelResult'];
            error_log('Found hotels in HotelResult property: ' . count($hotels));
        } elseif (isset($response['Result']) && is_array($response['Result'])) {
            $hotels = $response['Result'];
            error_log('Found hotels in Result property: ' . count($hotels));
        } else {
            error_log('Could not find hotels in standard properties. Response keys: ' . json_encode(array_keys($response)));
            
            // Check for nested properties where hotels might be
            foreach ($response as $key => $value) {
                if (is_array($value) && !empty($value)) {
                    error_log('Checking potential hotels in key: ' . $key);
                    // Try to identify if this array contains hotel objects
                    $first_item = reset($value);
                    if (is_array($first_item) && (
                        isset($first_item['HotelCode']) || 
                        isset($first_item['HotelName']) || 
                        isset($first_item['HotelDetails']))) {
                        error_log('Found what appears to be hotels in key: ' . $key);
                        $hotels = $value;
                        break;
                    }
                }
            }
        }
        
        // Add hotels to combined results
        if (!empty($hotels)) {
            error_log('Adding ' . count($hotels) . ' hotels to results');
            if (isset($hotels[0]) && is_array($hotels)) {
                // Array of hotels
                $combined_hotels = array_merge($combined_hotels, $hotels);
            } else {
                // Single hotel object
                $combined_hotels[] = $hotels;
            }
        } else {
            error_log('No hotels found in this response chunk');
        }
    }

    // Return error if no hotels found
    if (empty($combined_hotels)) {
        error_log('No hotels found across all chunks');
        return new WP_Error('no_hotels', 'No hotels found for the given search criteria');
    }
    
    error_log('Total hotels found: ' . count($combined_hotels));
    
    // Prepare final results
    $results = array(
        'Hotels' => $combined_hotels,
        '_meta' => array(
            'chunks_processed' => count($all_results),
            'total_hotels_found' => count($combined_hotels),
            'search_params' => $params,
        )
    );
    
    // Store in a short-lived transient (5 minutes)
    $cache_key = 'tbo_hotels_search_' . md5(serialize($params));
    set_transient($cache_key, $results, 5 * MINUTE_IN_SECONDS);
    
    return $results;
}

/**
 * AJAX handler for getting countries
 */
function tbo_hotels_ajax_get_countries() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    $countries = tbo_hotels_get_countries();
    
    if (is_wp_error($countries)) {
        wp_send_json_error($countries->get_error_message());
    } else {
        wp_send_json_success($countries);
    }
}
add_action('wp_ajax_tbo_hotels_get_countries', 'tbo_hotels_ajax_get_countries');
add_action('wp_ajax_nopriv_tbo_hotels_get_countries', 'tbo_hotels_ajax_get_countries');

/**
 * AJAX handler for getting cities
 */
function tbo_hotels_ajax_get_cities() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        error_log('Security check failed for cities request');
        wp_send_json_error('Security check failed');
    }
    
    // Check country code
    if (empty($_POST['country_code'])) {
        error_log('Country code is missing in request');
        wp_send_json_error('Country code is required');
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    
    // Log the request for debugging
    error_log('Getting cities for country: ' . $country_code);
    
    try {
        // Always clear the cache for now to avoid stale error responses
        // This can be removed once you confirm everything is working correctly
        delete_transient('tbo_hotels_cities_' . $country_code);
        
        // Get cities from API
        $cities = tbo_hotels_get_cities($country_code);
        
        if (is_wp_error($cities)) {
            $error_message = $cities->get_error_message();
            $error_code = $cities->get_error_code();
            
            error_log('Error getting cities: [' . $error_code . '] ' . $error_message);
            
            // Try direct API call as a last resort
            error_log('Trying direct API call as fallback...');
            $test_result = tbo_hotels_direct_curl_request(
                'http://api.tbotechnology.in/TBOHolidays_HotelAPI/CityList',
                array('CountryCode' => $country_code),
                'POST'
            );
            
            if (is_wp_error($test_result)) {
                wp_send_json_error(array(
                    'message' => 'API Error: ' . $error_message,
                    'code' => $error_code,
                    'fallback_error' => $test_result->get_error_message()
                ));
            } else {
                // Format the direct response
                $formatted_cities = array();
                if (isset($test_result['CityList']) && is_array($test_result['CityList'])) {
                    foreach ($test_result['CityList'] as $city) {
                        $formatted_cities[] = array(
                            'Destination' => array(
                                'DestinationCode' => $city['Code'],
                                'DestinationName' => $city['Name']
                            )
                        );
                    }
                    error_log('Fallback successful! Found ' . count($formatted_cities) . ' cities');
                    wp_send_json_success($formatted_cities);
                } else {
                    wp_send_json_error(array(
                        'message' => 'Fallback API returned invalid format',
                        'raw_response' => $test_result
                    ));
                }
            }
        } else {
            // Log success
            error_log('Found ' . count($cities) . ' cities for country ' . $country_code);
            wp_send_json_success($cities);
        }
    } catch (Exception $e) {
        error_log('Exception in city request: ' . $e->getMessage());
        wp_send_json_error('Server error: ' . $e->getMessage());
    }
}
add_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');
add_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');

/**
 * AJAX handler for searching hotels
 */
function tbo_hotels_ajax_search_hotels() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotels_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Collect search parameters
    $params = array(
        'city_code' => sanitize_text_field($_POST['city_code'] ?? ''),
        'hotel_code' => sanitize_text_field($_POST['hotel_code'] ?? ''),
        'check_in' => sanitize_text_field($_POST['check_in'] ?? ''),
        'check_out' => sanitize_text_field($_POST['check_out'] ?? ''),
        'adults' => intval($_POST['adults'] ?? 1),
        'rooms' => intval($_POST['rooms'] ?? 1),
        'children' => intval($_POST['children'] ?? 0),
        'max_codes' => intval($_POST['max_codes'] ?? 50),
        'refresh_codes' => true // Force refresh during testing
    );
    
    // Log the search parameters for debugging
    error_log('Hotel search AJAX request parameters: ' . json_encode($params));
    
    // Log country code if present
    if (!empty($_POST['country_code'])) {
        error_log('Country code in request: ' . sanitize_text_field($_POST['country_code']));
    }
    
    // If city code provided, get hotel codes first to verify
    if (!empty($params['city_code']) && empty($params['hotel_code'])) {
        $hotel_codes = tbo_hotels_get_hotel_codes($params['city_code']);
        if (is_wp_error($hotel_codes)) {
            error_log('Error getting hotel codes: ' . $hotel_codes->get_error_message());
        } else {
            error_log('Found ' . count($hotel_codes) . ' hotel codes for city ' . $params['city_code']);
            error_log('Sample hotel codes: ' . json_encode(array_slice($hotel_codes, 0, 5)));
        }
    }
    
    // Perform hotel search
    $results = tbo_hotels_search_hotels($params);
    
    if (is_wp_error($results)) {
        error_log('Hotel search error: ' . $results->get_error_message());
        wp_send_json_error($results->get_error_message());
    } else {
        error_log('Hotel search success. Found hotels: ' . 
            (isset($results['Hotels']) ? count($results['Hotels']) : 'unknown'));
        wp_send_json_success($results);
    }
}
add_action('wp_ajax_tbo_hotels_search_hotels', 'tbo_hotels_ajax_search_hotels');
add_action('wp_ajax_nopriv_tbo_hotels_search_hotels', 'tbo_hotels_ajax_search_hotels');

/**
 * Register widget areas
 */
function tbo_hotels_widgets_init() {
    register_sidebar(array(
        'name'          => __('Sidebar', 'tbo-hotels'),
        'id'            => 'sidebar-1',
        'description'   => __('Add widgets here to appear in your sidebar.', 'tbo-hotels'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ));
}
add_action('widgets_init', 'tbo_hotels_widgets_init');