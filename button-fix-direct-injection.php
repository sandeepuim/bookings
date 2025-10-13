<?php
/**
 * TBO Hotels Button Fix - Direct Injection for functions.php
 * 
 * This file provides code that can be added to the theme's functions.php to ensure
 * the button fix is always included, even if the WordPress enqueue system is not working.
 */

/**
 * Enqueue the TBO Hotels button fix script using WordPress enqueue system
 */
function tbo_hotels_enqueue_room_selection_scripts() {
    // Register and enqueue jQuery if not already included
    if (!wp_script_is('jquery', 'enqueued')) {
        wp_enqueue_script('jquery');
    }
    
    // Enqueue the advanced button fix script
    wp_enqueue_script(
        'tbo-advanced-button-fix', 
        get_template_directory_uri() . '/../twentytwentyone/advanced-button-fix.js', 
        array('jquery'), 
        '1.0.0', 
        true
    );
}
add_action('wp_enqueue_scripts', 'tbo_hotels_enqueue_room_selection_scripts');

/**
 * Alternative method: Direct script injection in case WordPress enqueue is not working
 */
function tbo_hotels_direct_script_injection() {
    // Only inject on frontend
    if (is_admin()) {
        return;
    }
    
    // Path to the script file
    $script_path = get_template_directory_uri() . '/../twentytwentyone/advanced-button-fix.js';
    
    // Fallback path if the theme directory is not accessible
    $fallback_path = '/bookings/advanced-button-fix.js';
    
    // Output the script tag
    echo '<script type="text/javascript" src="' . esc_url($script_path) . '"></script>' . "\n";
    echo '<script type="text/javascript">
        // Fallback script loader in case the main script fails to load
        (function() {
            var scriptLoaded = false;
            
            // Check if the script has loaded
            document.querySelectorAll("script").forEach(function(script) {
                if (script.src.includes("advanced-button-fix.js")) {
                    scriptLoaded = true;
                }
            });
            
            // If not loaded, try the fallback path
            if (!scriptLoaded) {
                var fallbackScript = document.createElement("script");
                fallbackScript.src = "' . esc_url($fallback_path) . '";
                fallbackScript.type = "text/javascript";
                document.head.appendChild(fallbackScript);
                console.log("TBO Hotels: Using fallback script path");
            }
        })();
    </script>' . "\n";
    
    // Inline emergency fix in case both scripts fail to load
    echo '<script type="text/javascript">
        // Emergency button fix in case both scripts fail to load
        document.addEventListener("DOMContentLoaded", function() {
            // Check if the main button fix is loaded
            if (typeof TBOButtonFix === "undefined") {
                console.log("TBO Hotels: Emergency inline fix activated");
                
                // Find all potential Choose Room buttons
                var buttons = document.querySelectorAll(".choose-room-btn, [data-hotel-code], button, .btn, .button, a.btn, a.button");
                
                buttons.forEach(function(button) {
                    var buttonText = button.textContent.toLowerCase();
                    if (buttonText.includes("choose") || buttonText.includes("room") || 
                        buttonText.includes("select") || buttonText.includes("book")) {
                        
                        button.addEventListener("click", function(e) {
                            e.preventDefault();
                            
                            var hotelCode = this.getAttribute("data-hotel-code") || 
                                           this.closest("[data-hotel-code]")?.getAttribute("data-hotel-code");
                            
                            var cityCode = this.getAttribute("data-city-code") || 
                                          this.closest("[data-city-code]")?.getAttribute("data-city-code") || 
                                          "150184";
                            
                            if (!hotelCode) {
                                hotelCode = prompt("Please enter the hotel code (usually a number):");
                                if (!hotelCode) return;
                            }
                            
                            var url = window.location.origin + "/bookings/simple-room-selection.php" + 
                                    "?hotel_code=" + encodeURIComponent(hotelCode) + 
                                    "&city_code=" + encodeURIComponent(cityCode) + 
                                    "&check_in=2025-09-20&check_out=2025-09-25";
                            
                            window.location.href = url;
                        });
                    }
                });
            }
        });
    </script>' . "\n";
}
add_action('wp_head', 'tbo_hotels_direct_script_injection', 999);

/**
 * Add a notice to the admin dashboard about the button fix
 */
function tbo_hotels_admin_notice() {
    // Only show to administrators
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if advanced-button-fix.js exists
    $theme_dir = get_template_directory();
    $script_exists = file_exists($theme_dir . '/../twentytwentyone/advanced-button-fix.js');
    
    if (!$script_exists) {
        echo '<div class="notice notice-warning is-dismissible">
            <p><strong>TBO Hotels Button Fix:</strong> The advanced button fix script is not found in the expected location. 
            Please make sure the file exists at: ' . esc_html($theme_dir . '/../twentytwentyone/advanced-button-fix.js') . '</p>
        </div>';
    }
}
add_action('admin_notices', 'tbo_hotels_admin_notice');
?>