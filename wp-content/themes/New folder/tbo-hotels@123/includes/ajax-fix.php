<?php
/**
 * TBO Hotels AJAX Output Buffer Fix
 * 
 * This file prevents PHP output from contaminating AJAX responses
 * by using output buffering.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include JS error fix
require_once(dirname(__FILE__) . '/js-error-fix.php');

/**
 * Fix for AJAX output buffering issues
 * This ensures that no PHP code or HTML is output before our JSON responses
 */
function tbo_hotels_ajax_buffer_fix() {
    // List of our AJAX actions
    $tbo_ajax_actions = array(
        'tbo_hotels_get_countries',
        'tbo_hotels_get_cities',
        'tbo_hotels_search_hotels'
    );
    
    // Check if we're handling one of our AJAX actions
    if (
        defined('DOING_AJAX') && 
        DOING_AJAX && 
        isset($_REQUEST['action']) && 
        in_array($_REQUEST['action'], $tbo_ajax_actions)
    ) {
        // Start output buffering to capture any unwanted output
        ob_start();
        
        // Register a function to clean up before wp_die()
        add_action('wp_die_ajax_handler', 'tbo_hotels_clean_output_buffer', 1);
    }
}
add_action('init', 'tbo_hotels_ajax_buffer_fix', 1); // Priority 1 makes it run early

/**
 * Clean output buffer before sending JSON response
 */
function tbo_hotels_clean_output_buffer() {
    // Clean all levels of output buffering
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

/**
 * Enhanced AJAX handlers with proper error handling and response formatting
 */

// Enhanced country AJAX handler
function tbo_hotels_enhanced_get_countries() {
    // Clean output buffer
    tbo_hotels_clean_output_buffer();
    
    // Log the request
    error_log('TBO Hotels: Countries request received');
    
    try {
        // Get countries
        $countries = tbo_hotels_get_countries();
        
        if (is_wp_error($countries)) {
            // Log the error
            error_log('TBO Hotels Error: ' . $countries->get_error_message());
            
            // Provide fallback countries
            $fallback_countries = array(
                array('Code' => 'IN', 'Name' => 'India'),
                array('Code' => 'US', 'Name' => 'United States'),
                array('Code' => 'GB', 'Name' => 'United Kingdom'),
                array('Code' => 'AE', 'Name' => 'United Arab Emirates'),
                array('Code' => 'TH', 'Name' => 'Thailand'),
                array('Code' => 'SG', 'Name' => 'Singapore'),
                array('Code' => 'MY', 'Name' => 'Malaysia')
            );
            
            // Return fallback countries
            wp_send_json_success($fallback_countries);
        } else {
            // Return countries from API
            wp_send_json_success($countries);
        }
    } catch (Exception $e) {
        // Log the exception
        error_log('TBO Hotels Exception: ' . $e->getMessage());
        
        // Return error
        wp_send_json_error(array(
            'message' => 'An unexpected error occurred',
            'code' => 'exception'
        ));
    }
}

// Enhanced city AJAX handler
function tbo_hotels_enhanced_get_cities() {
    // Clean output buffer
    tbo_hotels_clean_output_buffer();
    
    // Validate input
    if (empty($_POST['country_code'])) {
        wp_send_json_error(array(
            'message' => 'Country code is required',
            'code' => 'missing_country'
        ));
        exit;
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    
    // Log the request
    error_log("TBO Hotels: Cities request received for country: $country_code");
    
    try {
        // Get cities
        $cities = tbo_hotels_get_cities($country_code);
        
        if (is_wp_error($cities)) {
            // Log the error
            error_log('TBO Hotels Error: ' . $cities->get_error_message());
            
            // Get fallback cities based on country code
            $fallback_cities = array();
            
            if ($country_code === 'IN') {
                $fallback_cities = array(
                    array('Code' => '150184', 'Name' => 'Mumbai'),
                    array('Code' => '150489', 'Name' => 'New Delhi'),
                    array('Code' => '150089', 'Name' => 'Bangalore'),
                    array('Code' => '151145', 'Name' => 'Kolkata'),
                    array('Code' => '150787', 'Name' => 'Chennai'),
                    array('Code' => '150186', 'Name' => 'Goa')
                );
            } else if ($country_code === 'US') {
                $fallback_cities = array(
                    array('Code' => '150642', 'Name' => 'New York'),
                    array('Code' => '150157', 'Name' => 'Los Angeles'),
                    array('Code' => '150201', 'Name' => 'Chicago'),
                    array('Code' => '150152', 'Name' => 'Miami'),
                    array('Code' => '150161', 'Name' => 'Las Vegas')
                );
            } else if ($country_code === 'GB') {
                $fallback_cities = array(
                    array('Code' => '150351', 'Name' => 'London'),
                    array('Code' => '150447', 'Name' => 'Manchester'),
                    array('Code' => '150093', 'Name' => 'Birmingham'),
                    array('Code' => '150193', 'Name' => 'Edinburgh'),
                    array('Code' => '150223', 'Name' => 'Glasgow')
                );
            } else {
                $fallback_cities = array(
                    array('Code' => '0', 'Name' => 'Direct Search - Enter hotel name')
                );
            }
            
            // Return fallback cities
            wp_send_json_success($fallback_cities);
        } else {
            // Return cities from API
            wp_send_json_success($cities);
        }
    } catch (Exception $e) {
        // Log the exception
        error_log('TBO Hotels Exception: ' . $e->getMessage());
        
        // Return error
        wp_send_json_error(array(
            'message' => 'An unexpected error occurred',
            'code' => 'exception'
        ));
    }
}

// Replace original AJAX handlers with enhanced versions
remove_action('wp_ajax_tbo_hotels_get_countries', 'tbo_hotels_ajax_get_countries');
remove_action('wp_ajax_nopriv_tbo_hotels_get_countries', 'tbo_hotels_ajax_get_countries');
remove_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');
remove_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');

add_action('wp_ajax_tbo_hotels_get_countries', 'tbo_hotels_enhanced_get_countries');
add_action('wp_ajax_nopriv_tbo_hotels_get_countries', 'tbo_hotels_enhanced_get_countries');
add_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_enhanced_get_cities');
add_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_enhanced_get_cities');