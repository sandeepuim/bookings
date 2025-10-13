<?php
/**
 * Plugin Name: TBO API Response Fix
 * Description: Fixes issues with mixed content in AJAX responses
 * Version: 1.0
 * Author: GitHub Copilot
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Filter AJAX responses to fix mixed content issues
 */
function tbo_api_response_fix() {
    // Only run on AJAX requests
    if (defined('DOING_AJAX') && DOING_AJAX) {
        // Start output buffering to capture any unexpected output
        ob_start(function($buffer) {
            // Check if this is a TBO API action
            $is_tbo_action = isset($_POST['action']) && 
                (strpos($_POST['action'], 'tbo_') === 0 || 
                 strpos($_POST['action'], 'get_hotel_') === 0 ||
                 strpos($_POST['action'], 'load_more_') === 0);
            
            if (!$is_tbo_action) {
                return $buffer; // Not a TBO action, return as is
            }
            
            // Try to detect if this is a mix of script and JSON
            if (strpos($buffer, '<script') !== false && strpos($buffer, '{"success":') !== false) {
                // Extract just the JSON part
                $json_start = strrpos($buffer, '{');
                $json_end = strrpos($buffer, '}') + 1;
                
                if ($json_start !== false && $json_end !== false && $json_end > $json_start) {
                    $json_part = substr($buffer, $json_start, $json_end - $json_start);
                    
                    // Log the issue for debugging
                    error_log('TBO API Response Fix: Fixed mixed content response');
                    
                    // Return just the JSON part
                    return $json_part;
                }
            }
            
            return $buffer;
        });
    }
}
add_action('init', 'tbo_api_response_fix');

/**
 * Add hook to filter admin-ajax.php responses
 */
function tbo_fix_hotel_ajax_responses() {
    // Only run for TBO hotel related actions
    if (isset($_POST['action']) && 
        (strpos($_POST['action'], 'tbo_') === 0 || 
         strpos($_POST['action'], 'get_hotel_') === 0 ||
         strpos($_POST['action'], 'load_more_') === 0)) {
        
        // Add proper JSON headers
        header('Content-Type: application/json');
        
        // Ensure we have required parameters for hotel searches
        if ($_POST['action'] === 'tbo_load_more_hotels') {
            // Check and set default values for required parameters
            if (!isset($_POST['city_id']) && !isset($_POST['check_in']) && !isset($_POST['check_out'])) {
                // Extract from URL referer if possible
                $referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
                $referer_params = [];
                
                if (!empty($referer)) {
                    $query_string = parse_url($referer, PHP_URL_QUERY);
                    if ($query_string) {
                        parse_str($query_string, $referer_params);
                    }
                }
                
                // Set parameters from referer
                if (isset($referer_params['city_code'])) $_POST['city_id'] = $referer_params['city_code'];
                if (isset($referer_params['check_in'])) $_POST['check_in'] = $referer_params['check_in'];
                if (isset($referer_params['check_out'])) $_POST['check_out'] = $referer_params['check_out'];
                if (isset($referer_params['adults'])) $_POST['adults'] = $referer_params['adults'];
                if (isset($referer_params['children'])) $_POST['children'] = $referer_params['children'];
                if (isset($referer_params['rooms'])) $_POST['rooms'] = $referer_params['rooms'];
                
                // Log this fix
                error_log('TBO API Response Fix: Setting parameters from referer URL');
            }
        }
    }
}
add_action('admin_init', 'tbo_fix_hotel_ajax_responses');

/**
 * Fix the tbo_load_more_hotels AJAX handler to properly validate and handle parameters
 */
function tbo_load_more_hotels_fix() {
    add_action('wp_ajax_tbo_load_more_hotels', 'tbo_load_more_hotels_handler');
    add_action('wp_ajax_nopriv_tbo_load_more_hotels', 'tbo_load_more_hotels_handler');
}

/**
 * Improved handler for tbo_load_more_hotels
 */
function tbo_load_more_hotels_handler() {
    // Make sure we have clean output without any script tags
    ob_clean();
    
    // Validate required parameters
    $city_id = isset($_POST['city_id']) ? sanitize_text_field($_POST['city_id']) : null;
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : null;
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : null;
    
    // Check for essential parameters
    if (empty($city_id) || empty($check_in) || empty($check_out)) {
        wp_send_json_error('Missing required parameters');
        exit;
    }
    
    // Get rooms data
    $rooms_json = isset($_POST['rooms']) ? $_POST['rooms'] : null;
    $rooms = null;
    
    if ($rooms_json) {
        // Try to decode rooms data
        try {
            $rooms = json_decode(stripslashes($rooms_json), true);
        } catch (Exception $e) {
            // Failed to decode
            error_log('TBO API Response Fix: Failed to decode rooms data: ' . $e->getMessage());
        }
    }
    
    // If rooms data is invalid, create default
    if (empty($rooms) || !is_array($rooms)) {
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 2;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        
        $rooms = array(
            array(
                'adults' => $adults,
                'children' => $children,
                'child_ages' => array()
            )
        );
    }
    
    // Offset and limit
    $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
    $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
    
    // Call the appropriate function
    if (function_exists('tbo_enhanced_get_hotels')) {
        $result = tbo_enhanced_get_hotels($city_id, $check_in, $check_out, $rooms, $offset, $limit);
        
        if (isset($result['success']) && $result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error(isset($result['error']) ? $result['error'] : 'Error retrieving hotels');
        }
    } else {
        // Fallback if enhanced function doesn't exist
        wp_send_json_error('Hotel search function not available');
    }
    
    exit;
}

// Register the fixed handler
add_action('init', 'tbo_load_more_hotels_fix');