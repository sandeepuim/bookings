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
 * AJAX handler for getting countries
 */
function tbo_hotels_ajax_get_countries() {
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
    if (empty($_POST['country_code'])) {
        wp_send_json_error('Country code is required');
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    $cities = tbo_hotels_get_cities($country_code);
    
    if (is_wp_error($cities)) {
        wp_send_json_error($cities->get_error_message());
    } else {
        wp_send_json_success($cities);
    }
}
add_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');
add_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');

/**
 * AJAX handler for searching hotels
 */
function tbo_hotels_ajax_search_hotels() {
    // Collect search parameters
    $params = array(
        'city_code' => sanitize_text_field($_POST['city_code'] ?? ''),
        'check_in' => sanitize_text_field($_POST['check_in'] ?? ''),
        'check_out' => sanitize_text_field($_POST['check_out'] ?? ''),
        'adults' => intval($_POST['adults'] ?? 1),
        'rooms' => intval($_POST['rooms'] ?? 1),
        'children' => intval($_POST['children'] ?? 0),
    );
    
    // Validate required parameters
    if (empty($params['city_code']) || empty($params['check_in']) || empty($params['check_out'])) {
        wp_send_json_error('Please fill in all required fields');
    }
    
    // Perform hotel search
    $results = tbo_hotels_search_hotels($params);
    
    if (is_wp_error($results)) {
        wp_send_json_error($results->get_error_message());
    } else {
        wp_send_json_success($results);
    }
}
add_action('wp_ajax_tbo_hotels_search_hotels', 'tbo_hotels_ajax_search_hotels');
add_action('wp_ajax_nopriv_tbo_hotels_search_hotels', 'tbo_hotels_ajax_search_hotels');

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
    
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue hotel search script
    wp_enqueue_script('tbo-hotels-search', TBO_HOTELS_URI . '/assets/js/hotel-search.js', array('jquery'), TBO_HOTELS_VERSION, true);
    
    // Localize script with AJAX URL
    wp_localize_script('tbo-hotels-search', 'tbo_hotels_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbo_hotels_nonce'),
        'placeholder_image' => TBO_HOTELS_URI . '/assets/img/placeholder.jpg'
    ));
}
add_action('wp_enqueue_scripts', 'tbo_hotels_scripts');

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
?>