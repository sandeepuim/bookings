<?php
/**
 * Direct Button Fix Script 
 * 
 * This script directly adds click handlers to all "Choose Room" buttons
 * after the page has fully loaded.
 */

// Function to add script to the footer
function tbo_hotels_add_direct_button_fix() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels - Direct Button Fix Script Loaded');
        
        // Function to fix all buttons on the page
        function fixAllChooseRoomButtons() {
            // Select all buttons with class 'choose-room-btn'
            var buttons = document.querySelectorAll('.choose-room-btn');
            console.log('Found ' + buttons.length + ' "Choose Room" buttons');
            
            // Loop through each button and add a direct click handler
            buttons.forEach(function(button) {
                // Get data attributes
                var hotelCode = button.getAttribute('data-hotel-code');
                var cityCode = button.getAttribute('data-city-code');
                var checkIn = button.getAttribute('data-check-in');
                var checkOut = button.getAttribute('data-check-out');
                var adults = button.getAttribute('data-adults');
                var children = button.getAttribute('data-children');
                var rooms = button.getAttribute('data-rooms');
                
                console.log('Fixing button for hotel: ' + hotelCode);
                
                // Remove any existing click listeners
                button.replaceWith(button.cloneNode(true));
                
                // Get the new button reference after cloning
                var newButton = document.querySelector('[data-hotel-code="' + hotelCode + '"]');
                
                if (newButton) {
                    // Add inline onclick attribute for maximum compatibility
                    newButton.setAttribute('onclick', 'window.location.href="<?php echo esc_js(site_url("/hotel-room-selection.php")); ?>?hotel_code=' + hotelCode + '&city_code=' + cityCode + '&check_in=' + checkIn + '&check_out=' + checkOut + '&adults=' + adults + '&children=' + children + '&rooms=' + rooms + '"');
                    
                    // Also add event listener as backup
                    newButton.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        console.log('Button clicked for hotel: ' + hotelCode);
                        
                        // Construct the URL
                        var url = '<?php echo esc_js(site_url("/hotel-room-selection.php")); ?>';
                        url += '?hotel_code=' + encodeURIComponent(hotelCode);
                        url += '&city_code=' + encodeURIComponent(cityCode);
                        url += '&check_in=' + encodeURIComponent(checkIn);
                        url += '&check_out=' + encodeURIComponent(checkOut);
                        url += '&adults=' + encodeURIComponent(adults);
                        url += '&children=' + encodeURIComponent(children);
                        url += '&rooms=' + encodeURIComponent(rooms);
                        
                        console.log('Redirecting to: ' + url);
                        
                        // Navigate to the room selection page
                        window.location.href = url;
                        
                        return false;
                    });
                }
            });
            
            // Also check for jQuery-generated buttons if jQuery is available
            if (typeof jQuery !== 'undefined') {
                jQuery('.choose-room-btn').each(function() {
                    var $button = jQuery(this);
                    var hotelCode = $button.data('hotel-code');
                    var cityCode = $button.data('city-code');
                    var checkIn = $button.data('check-in');
                    var checkOut = $button.data('check-out');
                    var adults = $button.data('adults');
                    var children = $button.data('children');
                    var rooms = $button.data('rooms');
                    
                    console.log('jQuery fix for hotel: ' + hotelCode);
                    
                    // Remove existing click handlers and add new one
                    $button.off('click').on('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Construct URL
                        var url = '<?php echo esc_js(site_url("/hotel-room-selection.php")); ?>';
                        url += '?hotel_code=' + encodeURIComponent(hotelCode);
                        url += '&city_code=' + encodeURIComponent(cityCode);
                        url += '&check_in=' + encodeURIComponent(checkIn);
                        url += '&check_out=' + encodeURIComponent(checkOut);
                        url += '&adults=' + encodeURIComponent(adults);
                        url += '&children=' + encodeURIComponent(children);
                        url += '&rooms=' + encodeURIComponent(rooms);
                        
                        console.log('jQuery redirecting to: ' + url);
                        
                        // Navigate to room selection page
                        window.location.href = url;
                        
                        return false;
                    });
                });
            }
        }
        
        // Fix buttons on page load
        fixAllChooseRoomButtons();
        
        // Also fix buttons after a short delay (for dynamically added buttons)
        setTimeout(fixAllChooseRoomButtons, 1000);
        
        // If MutationObserver is supported, use it to watch for new buttons
        if (window.MutationObserver) {
            var observer = new MutationObserver(function(mutations) {
                console.log('DOM changed, checking for new buttons');
                fixAllChooseRoomButtons();
            });
            
            // Start observing the document body for changes
            observer.observe(document.body, { 
                childList: true, 
                subtree: true 
            });
        }
        
        // Additional check for buttons after AJAX calls if jQuery is available
        if (typeof jQuery !== 'undefined') {
            var originalAjax = jQuery.ajax;
            jQuery.ajax = function() {
                var promise = originalAjax.apply(this, arguments);
                promise.always(function() {
                    console.log('AJAX completed, checking for new buttons');
                    setTimeout(fixAllChooseRoomButtons, 200);
                });
                return promise;
            };
        }
    });
    </script>
    <?php
}

// Add the script to wp_footer
add_action('wp_footer', 'tbo_hotels_add_direct_button_fix', 100);