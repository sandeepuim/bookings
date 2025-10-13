<?php
/**
 * TBO Hotels Error Handling Enhancement
 * Adds better error handling to the TBO Hotels theme
 */

// Enqueue the error handling CSS
function tbo_enqueue_error_handling_css() {
    wp_enqueue_style(
        'tbo-error-handling',
        get_template_directory_uri() . '/assets/css/tbo-error-handling.css',
        array(),
        '1.0.0'
    );
}
add_action('wp_enqueue_scripts', 'tbo_enqueue_error_handling_css');

// Add error handling script initialization
function tbo_add_error_handling_init() {
    ?>
    <script>
    // Initialize the TBO error notification container
    document.addEventListener('DOMContentLoaded', function() {
        if (!document.getElementById('tbo-notification')) {
            var notification = document.createElement('div');
            notification.id = 'tbo-notification';
            notification.className = 'tbo-notification';
            document.body.appendChild(notification);
        }
        
        // Add general error handler for JS errors
        window.addEventListener('error', function(event) {
            console.error('[TBO Error Caught]:', event.error);
            
            // Only show user-facing errors for certain types of errors
            if (event.error && (
                event.error.toString().includes('hotel') || 
                event.error.toString().includes('room') ||
                event.error.toString().includes('booking') ||
                event.error.toString().includes('ajax')
            )) {
                var notification = document.getElementById('tbo-notification');
                if (notification) {
                    notification.innerHTML = '<div class="tbo-error-message"><i class="fa fa-exclamation-circle"></i> Something went wrong. Please try again or contact support if the problem persists.</div>';
                    notification.className = 'tbo-notification show';
                    
                    setTimeout(function() {
                        notification.className = 'tbo-notification';
                    }, 5000);
                }
            }
            
            // Don't prevent default error handling
            return false;
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'tbo_add_error_handling_init');