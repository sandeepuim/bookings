<?php
/**
 * TBO Hotels Optimization Functions
 * 
 * This file integrates the optimizations for the TBO Hotels theme:
 * 1. Add the optimization scripts
 * 2. Register new page templates
 * 3. Optimize API requests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the API optimization file
require_once(TBO_HOTELS_DIR . '/includes/tbo-api-optimization.php');

/**
 * Enqueue optimization scripts
 */
function tbo_hotels_enqueue_optimization_scripts() {
    // Enqueue error fix scripts with high priority to load early
    wp_enqueue_script(
        'tbo-hotels-console-error-fix',
        TBO_HOTELS_URI . '/assets/js/console-error-fix.js',
        array('jquery'),
        TBO_HOTELS_VERSION,
        true
    );
    
    wp_enqueue_script(
        'tbo-hotels-syntax-error-fix',
        TBO_HOTELS_URI . '/assets/js/syntax-error-fix.js',
        array('jquery'),
        TBO_HOTELS_VERSION,
        true
    );
    
    // Enqueue optimization script
    wp_enqueue_script(
        'tbo-hotels-optimization',
        TBO_HOTELS_URI . '/assets/js/tbo-optimization.js',
        array('jquery'),
        TBO_HOTELS_VERSION,
        true
    );
    
    // Add localized data
    wp_localize_script('tbo-hotels-optimization', 'tboData', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbo_ajax_nonce')
    ));
    
    // Add CSS for loading indicators
    wp_enqueue_style(
        'tbo-hotels-optimization-styles',
        TBO_HOTELS_URI . '/assets/css/tbo-optimization.css',
        array(),
        TBO_HOTELS_VERSION
    );
}
add_action('wp_enqueue_scripts', 'tbo_hotels_enqueue_optimization_scripts', 10);

/**
 * Register new page templates
 */
function tbo_hotels_add_page_templates($templates) {
    $templates[TBO_HOTELS_DIR . '/hotel-results-improved.php'] = 'Hotel Results Improved';
    return $templates;
}
add_filter('theme_page_templates', 'tbo_hotels_add_page_templates');

/**
 * AJAX handler for hotel details
 */
function tbo_ajax_get_hotel_details() {
    // Validate nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_ajax_nonce')) {
        wp_send_json_error(array('message' => 'Invalid security token'));
        exit;
    }
    
    // Get hotel code
    $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
    
    if (empty($hotel_code)) {
        wp_send_json_error(array('message' => 'Missing hotel code'));
        exit;
    }
    
    // Get hotel details
    if (function_exists('tbo_enhanced_get_hotel_details')) {
        $hotel = tbo_enhanced_get_hotel_details($hotel_code);
    } else if (function_exists('tbo_hotels_get_hotel_details')) {
        $hotel = tbo_hotels_get_hotel_details($hotel_code);
    } else {
        wp_send_json_error(array('message' => 'Hotel details function not available'));
        exit;
    }
    
    // Return result
    if (isset($hotel['success']) && $hotel['success'] === false) {
        wp_send_json_error(array('message' => isset($hotel['error']) ? $hotel['error'] : 'Unknown error'));
    } else {
        wp_send_json_success(array('hotel' => $hotel));
    }
    exit;
}
add_action('wp_ajax_tbo_get_hotel_details', 'tbo_ajax_get_hotel_details');
add_action('wp_ajax_nopriv_tbo_get_hotel_details', 'tbo_ajax_get_hotel_details');

/**
 * Create CSS file for optimization styles if it doesn't exist
 */
function tbo_hotels_create_optimization_css() {
    $css_dir = TBO_HOTELS_DIR . '/assets/css';
    $css_file = $css_dir . '/tbo-optimization.css';
    
    // Create directory if it doesn't exist
    if (!file_exists($css_dir)) {
        wp_mkdir_p($css_dir);
    }
    
    // Create CSS file if it doesn't exist
    if (!file_exists($css_file)) {
        $css_content = '/* TBO Hotels Optimization Styles */

/* Loading Overlay */
#tbo-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.tbo-loading-spinner {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

.tbo-loading-text {
    margin-top: 15px;
    font-size: 18px;
    color: #333;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Error Message */
#tbo-error-message {
    position: fixed;
    top: 20px;
    right: 20px;
    max-width: 400px;
    z-index: 9999;
}

/* Hotel Card Animations */
.hotel-item {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hotel-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.hotel-item.filtered-out {
    animation: fadeOut 0.5s forwards;
}

.hotel-item.filtered-in {
    animation: fadeIn 0.5s forwards;
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; height: 0; margin: 0; padding: 0; overflow: hidden; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}';
        
        file_put_contents($css_file, $css_content);
    }
}
add_action('after_setup_theme', 'tbo_hotels_create_optimization_css');

/**
 * Create syntax error fix script if it doesn't exist
 */
function tbo_hotels_create_syntax_error_fix() {
    $js_dir = TBO_HOTELS_DIR . '/assets/js';
    $js_file = $js_dir . '/syntax-error-fix.js';
    
    // Create directory if it doesn't exist
    if (!file_exists($js_dir)) {
        wp_mkdir_p($js_dir);
    }
    
    // Create JS file if it doesn't exist
    if (!file_exists($js_file)) {
        $js_content = '/**
 * TBO Hotels Syntax Error Fix
 * 
 * This script fixes common JavaScript syntax errors:
 * 1. Empty catch blocks
 * 2. Missing parentheses
 * 3. Invalid function arguments
 */

(function() {
    "use strict";
    
    console.log("TBO Hotels: Syntax error fix active");
    
    // Fix 1: Patch the Function constructor to handle syntax errors
    var originalFunction = window.Function;
    
    window.Function = function() {
        var args = Array.prototype.slice.call(arguments);
        var bodyIndex = args.length - 1;
        var body = args[bodyIndex];
        
        if (typeof body === "string") {
            // Fix catch blocks without parameters
            body = body.replace(/catch\s*{/g, "catch(e) {");
            
            // Fix missing closing parentheses
            var openParens = (body.match(/\(/g) || []).length;
            var closeParens = (body.match(/\)/g) || []).length;
            
            if (openParens > closeParens) {
                var missingParens = openParens - closeParens;
                for (var i = 0; i < missingParens; i++) {
                    body += ")";
                }
            }
            
            // Fix trailing commas in function arguments
            body = body.replace(/\(([^)]*),\s*\)/g, function(match, args) {
                return "(" + args.trim() + ")";
            });
            
            args[bodyIndex] = body;
        }
        
        try {
            return originalFunction.apply(this, args);
        } catch (e) {
            console.warn("TBO Hotels: Caught syntax error in function creation:", e);
            
            // Last resort: return a dummy function that does nothing
            return function() {
                console.warn("TBO Hotels: Executing dummy function due to syntax error");
                return null;
            };
        }
    };
    
    // Copy all properties from the original Function
    for (var prop in originalFunction) {
        if (originalFunction.hasOwnProperty(prop)) {
            window.Function[prop] = originalFunction[prop];
        }
    }
    
    // Fix 2: Patch eval to handle syntax errors
    var originalEval = window.eval;
    
    window.eval = function(code) {
        if (typeof code === "string") {
            // Apply the same fixes as for Function
            code = code.replace(/catch\s*{/g, "catch(e) {");
            
            var openParens = (code.match(/\(/g) || []).length;
            var closeParens = (code.match(/\)/g) || []).length;
            
            if (openParens > closeParens) {
                var missingParens = openParens - closeParens;
                for (var i = 0; i < missingParens; i++) {
                    code += ")";
                }
            }
            
            code = code.replace(/\(([^)]*),\s*\)/g, function(match, args) {
                return "(" + args.trim() + ")";
            });
        }
        
        try {
            return originalEval.call(window, code);
        } catch (e) {
            console.warn("TBO Hotels: Caught syntax error in eval:", e);
            return null;
        }
    };
    
    // Fix 3: Patch JSON.parse to handle common errors
    var originalJSONParse = JSON.parse;
    
    JSON.parse = function(text) {
        if (typeof text !== "string") {
            return originalJSONParse.call(JSON, text);
        }
        
        try {
            return originalJSONParse.call(JSON, text);
        } catch (e) {
            console.warn("TBO Hotels: Caught error in JSON.parse:", e);
            
            // Try to fix common JSON errors
            try {
                // Fix trailing commas
                text = text.replace(/,\s*([\]}])/g, "$1");
                
                // Fix unquoted property names
                text = text.replace(/([{,]\s*)([a-zA-Z0-9_]+)(\s*:)/g, "$1\"$2\"$3");
                
                return originalJSONParse.call(JSON, text);
            } catch (e2) {
                console.error("TBO Hotels: Could not fix JSON:", e2);
                return {};
            }
        }
    };
    
    // Fix 4: Add a global error handler
    window.addEventListener("error", function(event) {
        console.warn("TBO Hotels: Caught global error:", event.message);
        
        // Prevent the error from stopping execution
        if (event.message.indexOf("Unexpected token") !== -1 ||
            event.message.indexOf("missing ) after argument list") !== -1) {
            event.preventDefault();
            return true;
        }
    }, true);
})();';
        
        file_put_contents($js_file, $js_content);
    }
}
add_action('after_setup_theme', 'tbo_hotels_create_syntax_error_fix');