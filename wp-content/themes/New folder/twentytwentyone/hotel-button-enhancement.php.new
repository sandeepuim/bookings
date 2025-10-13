<?php
/**
 * Hotel Results Button Enhancement
 * 
 * This code enhances the hotel results page with proper button attributes
 */

// Function to add the enhanced button to hotel results
function tbo_hotels_enhance_result_buttons() {
    ?>
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels - Button Enhancement Script Loaded');
        
        // Function to enhance hotel buttons
        function enhanceHotelButtons() {
            // Target the container with hotel results
            var hotelContainer = document.querySelector('.hotel-results-container');
            
            if (!hotelContainer) {
                console.log('Hotel results container not found');
                return;
            }
            
            // Get search parameters from hidden fields or data attributes
            var searchParams = {
                cityCode: document.querySelector('[name="city_code"]') ? document.querySelector('[name="city_code"]').value : '',
                checkIn: document.querySelector('[name="check_in"]') ? document.querySelector('[name="check_in"]').value : '',
                checkOut: document.querySelector('[name="check_out"]') ? document.querySelector('[name="check_out"]').value : '',
                adults: document.querySelector('[name="adults"]') ? document.querySelector('[name="adults"]').value : '2',
                children: document.querySelector('[name="children"]') ? document.querySelector('[name="children"]').value : '0',
                rooms: document.querySelector('[name="rooms"]') ? document.querySelector('[name="rooms"]').value : '1'
            };
            
            console.log('Search parameters:', searchParams);
            
            // Find all hotel cards
            var hotelCards = hotelContainer.querySelectorAll('.hotel-card');
            console.log('Found ' + hotelCards.length + ' hotel cards');
            
            // Enhance each hotel card
            hotelCards.forEach(function(card) {
                // Get hotel code from the card
                var hotelCode = card.getAttribute('data-hotel-code');
                
                if (!hotelCode) {
                    console.log('Hotel card missing hotel code');
                    return;
                }
                
                // Find or create the button
                var chooseRoomBtn = card.querySelector('.choose-room-btn');
                
                if (!chooseRoomBtn) {
                    console.log('Creating new button for hotel ' + hotelCode);
                    // Create new button if it doesn't exist
                    chooseRoomBtn = document.createElement('button');
                    chooseRoomBtn.classList.add('choose-room-btn');
                    chooseRoomBtn.textContent = 'Choose Room';
                    
                    // Find pricing section to append button
                    var pricingSection = card.querySelector('.hotel-pricing');
                    if (pricingSection) {
                        pricingSection.appendChild(chooseRoomBtn);
                    } else {
                        card.appendChild(chooseRoomBtn);
                    }
                }
                
                // Set all necessary data attributes
                chooseRoomBtn.setAttribute('data-hotel-code', hotelCode);
                chooseRoomBtn.setAttribute('data-city-code', searchParams.cityCode);
                chooseRoomBtn.setAttribute('data-check-in', searchParams.checkIn);
                chooseRoomBtn.setAttribute('data-check-out', searchParams.checkOut);
                chooseRoomBtn.setAttribute('data-adults', searchParams.adults);
                chooseRoomBtn.setAttribute('data-children', searchParams.children);
                chooseRoomBtn.setAttribute('data-rooms', searchParams.rooms);
                
                // Set direct URL
                var directUrl = '<?php echo esc_js(site_url("/hotel-room-selection.php")); ?>';
                directUrl += '?hotel_code=' + encodeURIComponent(hotelCode);
                directUrl += '&city_code=' + encodeURIComponent(searchParams.cityCode);
                directUrl += '&check_in=' + encodeURIComponent(searchParams.checkIn);
                directUrl += '&check_out=' + encodeURIComponent(searchParams.checkOut);
                directUrl += '&adults=' + encodeURIComponent(searchParams.adults);
                directUrl += '&children=' + encodeURIComponent(searchParams.children);
                directUrl += '&rooms=' + encodeURIComponent(searchParams.rooms);
                
                // Set direct onclick attribute
                chooseRoomBtn.setAttribute('onclick', 'window.location.href="' + directUrl + '"; return false;');
                
                console.log('Enhanced button for hotel ' + hotelCode + ' with URL: ' + directUrl);
            });
        }
        
        // Run enhancement on page load
        enhanceHotelButtons();
        
        // Also run after a short delay to catch dynamically loaded content
        setTimeout(enhanceHotelButtons, 1000);
        
        // If we have jQuery, also watch for any custom events that might signal new hotels loaded
        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('hotelsLoaded hotelSearchComplete resultsUpdated', function() {
                console.log('Hotels loaded event triggered');
                setTimeout(enhanceHotelButtons, 300);
            });
        }
    });
    </script>
    <?php
}

// Add to wp_footer with high priority
add_action('wp_footer', 'tbo_hotels_enhance_result_buttons', 99);