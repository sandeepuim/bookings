<?php
/**
 * Integration File for TBO Hotels AJAX Fixes
 * 
 * This file integrates all the AJAX fix components:
 * 1. Output Buffer Fix
 * 2. Enhanced AJAX Handlers
 * 3. Frontend JavaScript Fixes
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the AJAX buffer fix
require_once(get_template_directory() . '/includes/ajax-buffer-fix.php');

// Add JavaScript AJAX fix to frontend
function tbo_hotels_enqueue_ajax_fix() {
    wp_enqueue_script(
        'tbo-hotels-ajax-fix',
        get_template_directory_uri() . '/assets/js/ajax-fix.js',
        array('jquery'),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'tbo_hotels_enqueue_ajax_fix');

// Register a diagnostic page in admin
function tbo_hotels_register_diagnostic_page() {
    add_submenu_page(
        'tools.php',
        'TBO Hotels AJAX Diagnostic',
        'TBO Hotels AJAX Diagnostic',
        'manage_options',
        'tbo-hotels-ajax-diagnostic',
        'tbo_hotels_diagnostic_page_callback'
    );
}
add_action('admin_menu', 'tbo_hotels_register_diagnostic_page');

// Callback for the diagnostic page
function tbo_hotels_diagnostic_page_callback() {
    ?>
    <div class="wrap">
        <h1>TBO Hotels AJAX Diagnostic</h1>
        
        <div class="card">
            <h2>AJAX Endpoint Tests</h2>
            
            <h3>Country Endpoint Test</h3>
            <div id="country-test-result">Running test...</div>
            
            <h3>City Endpoint Test (India)</h3>
            <div id="city-test-result">Running test...</div>
        </div>
        
        <div class="card">
            <h2>Manual Test Tools</h2>
            
            <p>Use these links to test the direct endpoints:</p>
            
            <ul>
                <li><a href="<?php echo esc_url(home_url('/wp-content/themes/tbo-hotels/includes/city-dropdown-test.php?country_code=IN')); ?>" target="_blank">Test City Endpoint (India)</a></li>
                <li><a href="<?php echo esc_url(home_url('/wp-content/themes/tbo-hotels/includes/city-dropdown-test.php?country_code=US')); ?>" target="_blank">Test City Endpoint (USA)</a></li>
                <li><a href="<?php echo esc_url(home_url('/wp-content/themes/tbo-hotels/includes/ajax-diagnostic.php')); ?>" target="_blank">Run AJAX Diagnostic Tool</a></li>
            </ul>
        </div>
        
        <div class="card">
            <h2>Implemented Fixes</h2>
            
            <p><strong>1. Output Buffer Fix:</strong> Prevents PHP output before JSON responses</p>
            <p><strong>2. Enhanced AJAX Handlers:</strong> Adds better error handling and fallbacks</p>
            <p><strong>3. Frontend JavaScript Fixes:</strong> Adds client-side fallbacks for API failures</p>
            <p><strong>4. Diagnostic Tools:</strong> Added tools to help debug AJAX issues</p>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Test country endpoint
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'tbo_hotels_get_countries',
                nonce: '<?php echo wp_create_nonce('tbo_hotels_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#country-test-result').html('<div class="notice notice-success"><p>Success! Received ' + response.data.length + ' countries.</p></div>');
                } else {
                    $('#country-test-result').html('<div class="notice notice-error"><p>Error: ' + (response.data || 'Unknown error') + '</p></div>');
                }
            },
            error: function() {
                $('#country-test-result').html('<div class="notice notice-error"><p>AJAX request failed</p></div>');
            }
        });
        
        // Test city endpoint
        $.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'tbo_hotels_get_cities',
                country_code: 'IN',
                nonce: '<?php echo wp_create_nonce('tbo_hotels_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    $('#city-test-result').html('<div class="notice notice-success"><p>Success! Received ' + response.data.length + ' cities.</p></div>');
                } else {
                    $('#city-test-result').html('<div class="notice notice-error"><p>Error: ' + (response.data || 'Unknown error') + '</p></div>');
                }
            },
            error: function() {
                $('#city-test-result').html('<div class="notice notice-error"><p>AJAX request failed</p></div>');
            }
        });
    });
    </script>
    <?php
}