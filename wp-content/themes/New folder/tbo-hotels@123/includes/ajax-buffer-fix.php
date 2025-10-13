<?php
/**
 * AJAX Output Buffer Fix
 * Prevents premature output before AJAX responses
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Fix for output buffering issues in AJAX responses
 * This ensures proper JSON output without HTML contamination
 */
function tbo_hotels_ajax_output_buffer_fix() {
    // Only apply to our AJAX actions
    $our_actions = array(
        'tbo_hotels_get_countries',
        'tbo_hotels_get_cities',
        'tbo_hotels_search_hotels'
    );
    
    if (
        defined('DOING_AJAX') && 
        DOING_AJAX && 
        isset($_REQUEST['action']) && 
        in_array($_REQUEST['action'], $our_actions)
    ) {
        // Start output buffering
        ob_start();
        
        // Add a callback to clean the buffer before JSON output
        add_action('wp_die_ajax_handler', 'tbo_hotels_clean_output_buffer', 1);
    }
}
add_action('init', 'tbo_hotels_ajax_output_buffer_fix', 1);

/**
 * Clean the output buffer before sending JSON response
 */
function tbo_hotels_clean_output_buffer() {
    // Discard any output that occurred before the JSON response
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
}

/**
 * Enhanced AJAX handler for getting cities
 * This version includes more robust error handling and debugging
 */
function tbo_hotels_enhanced_ajax_get_cities() {
    // Make sure we have clean output
    tbo_hotels_clean_output_buffer();
    
    // Log the request for debugging
    error_log('TBO Hotels: City request received for country: ' . ($_POST['country_code'] ?? 'Not specified'));
    
    // Validate input
    if (empty($_POST['country_code'])) {
        wp_send_json_error(array(
            'message' => 'Country code is required',
            'code' => 'missing_country'
        ));
    }
    
    $country_code = sanitize_text_field($_POST['country_code']);
    
    // Try to get cities with error handling
    try {
        $cities = tbo_hotels_get_cities($country_code);
        
        if (is_wp_error($cities)) {
            // Log the error
            error_log('TBO Hotels Error: ' . $cities->get_error_message());
            
            // Try fallback cities
            $fallback_cities = tbo_hotels_get_fallback_cities($country_code);
            
            if (!empty($fallback_cities)) {
                // Return fallback cities
                wp_send_json_success($fallback_cities);
            } else {
                // No fallback available
                wp_send_json_error(array(
                    'message' => $cities->get_error_message(),
                    'code' => $cities->get_error_code(),
                    'fallback_attempted' => true
                ));
            }
        } else {
            // Success! Return the cities
            wp_send_json_success($cities);
        }
    } catch (Exception $e) {
        // Catch any unexpected errors
        error_log('TBO Hotels Exception: ' . $e->getMessage());
        wp_send_json_error(array(
            'message' => 'An unexpected error occurred',
            'code' => 'exception'
        ));
    }
}

/**
 * Get fallback cities for common countries
 */
function tbo_hotels_get_fallback_cities($country_code) {
    $fallback_cities = array();
    
    // India
    if ($country_code === 'IN') {
        $fallback_cities = array(
            array('Code' => '150184', 'Name' => 'Mumbai'),
            array('Code' => '150489', 'Name' => 'New Delhi'),
            array('Code' => '150089', 'Name' => 'Bangalore'),
            array('Code' => '151145', 'Name' => 'Kolkata'),
            array('Code' => '150787', 'Name' => 'Chennai'),
            array('Code' => '150186', 'Name' => 'Goa')
        );
    }
    // United States
    else if ($country_code === 'US') {
        $fallback_cities = array(
            array('Code' => '150642', 'Name' => 'New York'),
            array('Code' => '150157', 'Name' => 'Los Angeles'),
            array('Code' => '150201', 'Name' => 'Chicago'),
            array('Code' => '150152', 'Name' => 'Miami'),
            array('Code' => '150161', 'Name' => 'Las Vegas')
        );
    }
    // United Kingdom
    else if ($country_code === 'GB') {
        $fallback_cities = array(
            array('Code' => '150351', 'Name' => 'London'),
            array('Code' => '150447', 'Name' => 'Manchester'),
            array('Code' => '150093', 'Name' => 'Birmingham'),
            array('Code' => '150193', 'Name' => 'Edinburgh'),
            array('Code' => '150223', 'Name' => 'Glasgow')
        );
    }
    
    return $fallback_cities;
}

// Replace the original AJAX handler with our enhanced version
remove_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');
remove_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_ajax_get_cities');
add_action('wp_ajax_tbo_hotels_get_cities', 'tbo_hotels_enhanced_ajax_get_cities');
add_action('wp_ajax_nopriv_tbo_hotels_get_cities', 'tbo_hotels_enhanced_ajax_get_cities');