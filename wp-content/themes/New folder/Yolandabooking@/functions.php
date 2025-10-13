<?php

/**
 * Book Your Travel functions and definitions.
 *
 * Sets up the theme and provides some helper functions, which are used
 * in the theme as custom template tags. Others are attached to action and
 * filter hooks in WordPress to change core functionality.
 *
 * @package WordPress
 * @subpackage BookYourTravel
 * @since 1.0
 * @version 8.18.1
 *
 */

function bookyourtravel_enqueue_scripts()
{
    // Enqueue Styles
    wp_enqueue_style('newhome-style', get_template_directory_uri() . '/assets/css/newhome.css', array(), '1.0.0', 'all');
    wp_enqueue_style('style', get_template_directory_uri() . '/style.css', array(), '1.0.0', 'all');
    wp_enqueue_style('booking-style', get_template_directory_uri() . '/assets/css/booking.css', array(), '1.0.0', 'all');
    wp_enqueue_style('responsive-style', get_template_directory_uri() . '/assets/css/responsive.css', array(), '1.0.0', 'all');
    
    // Hotel search form styles (front page and results page)
    if (is_front_page() || is_page_template('templates/front-page.php') || 
        is_page_template('templates/hotel-results.php') || is_page('hotel-results')) {
        wp_enqueue_style('hotel-search-style', get_template_directory_uri() . '/assets/css/hotel-search.css', array(), '1.0.1', 'all');
    }
    
    // Enqueue hotel results CSS if on the results page
    if (is_page_template('templates/hotel-results.php') || is_page('hotel-results')) {
        wp_enqueue_style('hotel-results-style', get_template_directory_uri() . '/assets/css/hotel-results-new.css', array(), '1.0.1', 'all');
    }

    // Enqueue JavaScript
    wp_enqueue_script('jquery');
    wp_enqueue_script('main-script', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), '3.7.1', true);
    
    // Add hotel search script for front page
    if (is_front_page() || is_page_template('templates/front-page.php')) {
        wp_enqueue_script('hotel-search', get_template_directory_uri() . '/assets/js/hotel-search.js', array('jquery'), '1.0.0', true);
        
        // Pass AJAX URL to script
        wp_localize_script('hotel-search', 'hotel_search_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('hotel_search_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'bookyourtravel_enqueue_scripts');

// Include hotel search functionality
require_once get_template_directory() . '/inc/hotel-search.php';
add_action('wp_enqueue_scripts', 'bookyourtravel_enqueue_scripts');



function allow_svg_upload($mimes)
{
    // SVG image supported
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

function custom_theme_setup()
{
    // Nav Menu
    register_nav_menus(array(
        'primary-menu' => __('Primary Menu', 'header'),
    ));
}
add_action('after_setup_theme', 'custom_theme_setup');

/**
 * TBO API Integration Functions
 */

// Get TBO API instance
function get_tbo_api() {
    // Make sure the plugin class exists
    if (!class_exists('TBO_Hotel_Booking_API')) {
        require_once(WP_PLUGIN_DIR . '/tbo-hotel-booking/includes/api/class-tbo-hotel-booking-api.php');
    }
    return new TBO_Hotel_Booking_API();
}

// AJAX handler for getting countries
function tbo_get_countries_handler() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    try {
        $tbo_api = get_tbo_api();
        
        // Use reflection to call the API request method directly
        $reflection = new ReflectionClass($tbo_api);
        $method = $reflection->getMethod('api_request');
        $method->setAccessible(true);
        
        $countries = $method->invokeArgs($tbo_api, ['CountryList', [], 'GET']);
        
        if (isset($countries['CountryList']) && is_array($countries['CountryList'])) {
            wp_send_json_success($countries['CountryList']);
        } else {
            wp_send_json_error('Invalid response format');
        }
    } catch (Exception $e) {
        wp_send_json_error('API Error: ' . $e->getMessage());
    }
    
    wp_die();
}
add_action('wp_ajax_tbo_get_countries', 'tbo_get_countries_handler');
add_action('wp_ajax_nopriv_tbo_get_countries', 'tbo_get_countries_handler');

// AJAX handler for getting cities
function tbo_get_cities_handler() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    // Check country code
    if (!isset($_POST['country_code']) || empty($_POST['country_code'])) {
        wp_send_json_error('Country code is required');
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    
    try {
        $tbo_api = get_tbo_api();
        
        // Use reflection to call the API request method directly
        $reflection = new ReflectionClass($tbo_api);
        $method = $reflection->getMethod('api_request');
        $method->setAccessible(true);
        
        $data = [
            'CountryCode' => $country_code
        ];
        
        $cities = $method->invokeArgs($tbo_api, ['CityList', $data, 'POST']);
        
        if (isset($cities['CityList']) && is_array($cities['CityList'])) {
            wp_send_json_success($cities['CityList']);
        } else {
            wp_send_json_error('Invalid response format');
        }
    } catch (Exception $e) {
        wp_send_json_error('API Error: ' . $e->getMessage());
    }
    
    wp_die();
}
add_action('wp_ajax_tbo_get_cities', 'tbo_get_cities_handler');
add_action('wp_ajax_nopriv_tbo_get_cities', 'tbo_get_cities_handler');

// Create hotel results page template
function create_hotel_results_page() {
    // Check if the page already exists
    $page_exists = get_page_by_path('hotel-results');
    
    if (!$page_exists) {
        // Create the page
        $page_id = wp_insert_post(array(
            'post_title' => 'Hotel Results',
            'post_content' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_name' => 'hotel-results'
        ));
    }
}
add_action('after_switch_theme', 'create_hotel_results_page');

function enable_post_templates()
{
    add_theme_support('block-templates');
}
add_action('after_setup_theme', 'enable_post_templates');

function custom_posts_per_page($query)
{
    if (!is_admin() && $query->is_main_query()) {
        if ($query->is_home() || $query->is_archive() || $query->is_search()) {
            $query->set('posts_per_page', 8);
        }
    }
}
add_action('pre_get_posts', 'custom_posts_per_page');

add_theme_support('post-thumbnails');
