/**
 * Quick Button Fix for TBO Hotels
 * 
 * This JavaScript file fixes the "Choose Room" buttons by redirecting them
 * to the fixed room selection page.
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('TBO Hotels - Quick Button Fix loaded');
    
    // Find all "Choose Room" buttons
    var buttons = document.querySelectorAll('.choose-room-btn');
    console.log('Found ' + buttons.length + ' "Choose Room" buttons');
    
    // Process each button
    buttons.forEach(function(button) {
        // Add click event listener
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Get data attributes
            var hotelCode = this.getAttribute('data-hotel-code');
            var cityCode = this.getAttribute('data-city-code');
            var checkIn = this.getAttribute('data-check-in');
            var checkOut = this.getAttribute('data-check-out');
            var adults = this.getAttribute('data-adults');
            var children = this.getAttribute('data-children');
            var rooms = this.getAttribute('data-rooms');
            
            console.log('Button clicked with data:', {
                hotelCode: hotelCode,
                cityCode: cityCode,
                checkIn: checkIn,
                checkOut: checkOut,
                adults: adults,
                children: children,
                rooms: rooms
            });
            
            // Build URL for the simple room selection page
            var url = 'simple-room-selection.php';
            url += '?hotel_code=' + encodeURIComponent(hotelCode || '');
            url += '&city_code=' + encodeURIComponent(cityCode || '');
            url += '&check_in=' + encodeURIComponent(checkIn || '');
            url += '&check_out=' + encodeURIComponent(checkOut || '');
            url += '&adults=' + encodeURIComponent(adults || '2');
            url += '&children=' + encodeURIComponent(children || '0');
            url += '&rooms=' + encodeURIComponent(rooms || '1');
            
            console.log('Redirecting to:', url);
            window.location.href = url;
        });
    });
});