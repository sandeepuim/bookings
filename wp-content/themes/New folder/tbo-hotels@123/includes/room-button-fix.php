<?php
/**
 * Fix for Hotel Room Selection Button
 * 
 * This file adds a direct script to the footer to make the Choose Room button work correctly
 */

// Don't allow direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add the fix script to the footer
 */
function tbo_hotels_add_room_selection_fix() {
    ?>
    <script type="text/javascript">
    // Fix for Choose Room buttons - runs after page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels: Room Selection Fix running');
        
        // Find all Choose Room buttons on the page
        var buttons = document.querySelectorAll('button.choose-room-btn, button:contains("Choose Room")');
        console.log('Found buttons:', buttons.length);
        
        // Process each button
        buttons.forEach(function(button) {
            console.log('Processing button:', button);
            
            // Get hotel code from data attribute or parent element
            var hotelCode = button.getAttribute('data-hotel-code');
            if (!hotelCode) {
                // Try to find it from the parent element's data attribute
                var parentCard = button.closest('.yatra-hotel-card');
                if (parentCard) {
                    hotelCode = parentCard.getAttribute('data-hotel-code');
                }
            }
            
            console.log('Hotel code for button:', hotelCode);
            
            if (hotelCode) {
                // Create direct onclick handler
                button.onclick = function(e) {
                    e.preventDefault();
                    console.log('Button clicked for hotel:', hotelCode);
                    
                    // Get current search parameters
                    var searchParams = new URLSearchParams(window.location.search);
                    
                    // Create URL with all parameters
                    var redirectUrl = '/bookings/room-redirect.php?hotel_code=' + hotelCode;
                    
                    // Add all other search parameters
                    searchParams.forEach(function(value, key) {
                        if (key !== 'hotel_code') { // Don't duplicate hotel_code
                            redirectUrl += '&' + key + '=' + value;
                        }
                    });
                    
                    console.log('Redirecting to:', redirectUrl);
                    
                    // Redirect directly
                    window.location.href = redirectUrl;
                    return false;
                };
                
                // Add a clear indication that the button has been processed
                button.classList.add('room-btn-fixed');
                button.setAttribute('title', 'Click to view room options');
                
                // Make sure button is clearly clickable
                button.style.cursor = 'pointer';
                button.style.backgroundColor = '#ff6b35';
                button.style.color = 'white';
                button.style.border = 'none';
                button.style.padding = '12px 24px';
                button.style.borderRadius = '6px';
                button.style.fontSize = '14px';
                button.style.fontWeight = '600';
                button.style.width = '100%';
                
                console.log('Button has been processed and fixed');
            } else {
                console.warn('Could not find hotel code for button:', button);
            }
        });
        
        // Add a direct jQuery handler as backup (late binding)
        if (typeof jQuery !== 'undefined') {
            jQuery(document).off('click', '.choose-room-btn').on('click', '.choose-room-btn', function(e) {
                e.preventDefault();
                
                var hotelCode = jQuery(this).data('hotel-code');
                if (!hotelCode) {
                    hotelCode = jQuery(this).closest('.yatra-hotel-card').data('hotel-code');
                }
                
                if (hotelCode) {
                    console.log('jQuery handler: Button clicked for hotel:', hotelCode);
                    
                    // Get current search parameters
                    var searchParams = new URLSearchParams(window.location.search);
                    
                    // Create URL with all parameters
                    var redirectUrl = '/bookings/room-redirect.php?hotel_code=' + hotelCode;
                    
                    // Add all other search parameters
                    searchParams.forEach(function(value, key) {
                        if (key !== 'hotel_code') { // Don't duplicate hotel_code
                            redirectUrl += '&' + key + '=' + value;
                        }
                    });
                    
                    console.log('jQuery handler: Redirecting to:', redirectUrl);
                    
                    // Redirect directly
                    window.location.href = redirectUrl;
                    return false;
                } else {
                    console.warn('jQuery handler: Could not find hotel code');
                }
            });
            
            console.log('jQuery backup handler installed');
        }
    });
    </script>
    <?php
}
add_action('wp_footer', 'tbo_hotels_add_room_selection_fix', 99);