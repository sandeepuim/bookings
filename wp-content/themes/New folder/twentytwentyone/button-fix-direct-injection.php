<?php
/**
 * TBO Hotels Button Fix Direct Injection
 * 
 * This file provides a direct injection method for fixing button issues
 * even if WordPress is not loading scripts correctly.
 */
?>
<script>
// Use an immediately invoked function expression to avoid conflicts
(function() {
    // Run when DOM is fully loaded
    function onDOMLoaded() {
        console.log('TBO Hotels - Direct Injection Button Fix loaded');
        
        // Try to find and fix buttons repeatedly
        findAndFixButtons();
        setTimeout(findAndFixButtons, 1000);
        setTimeout(findAndFixButtons, 3000);
    }
    
    // Function to find and fix all button variations
    function findAndFixButtons() {
        console.log('Looking for Choose Room buttons...');
        var totalFixed = 0;
        
        // Try by class
        var buttonsByClass = document.querySelectorAll('.choose-room-btn, .btn-choose-room, .room-select-btn');
        if (buttonsByClass.length > 0) {
            fixButtonCollection(buttonsByClass);
            totalFixed += buttonsByClass.length;
        }
        
        // Try by text content
        var allButtons = document.querySelectorAll('button, a.button, a.btn, input[type="button"], input[type="submit"]');
        var chooseRoomButtons = [];
        
        for (var i = 0; i < allButtons.length; i++) {
            var btn = allButtons[i];
            var text = btn.textContent || btn.value || '';
            
            if (text.trim().toLowerCase().indexOf('choose room') !== -1) {
                chooseRoomButtons.push(btn);
            }
        }
        
        if (chooseRoomButtons.length > 0) {
            fixButtonCollection(chooseRoomButtons);
            totalFixed += chooseRoomButtons.length;
        }
        
        console.log('Total buttons fixed: ' + totalFixed);
        
        // If we didn't find any buttons, add a global click handler
        if (totalFixed === 0) {
            addGlobalClickHandler();
        }
    }
    
    // Fix a collection of buttons
    function fixButtonCollection(buttons) {
        for (var i = 0; i < buttons.length; i++) {
            try {
                fixSingleButton(buttons[i]);
            } catch (err) {
                console.error('Error fixing button:', err);
            }
        }
    }
    
    // Fix a single button
    function fixSingleButton(button) {
        // Extract URL parameters
        var urlParams = extractURLParameters();
        
        // Get parameters from button data attributes
        var hotelCode = button.getAttribute('data-hotel-code') || button.getAttribute('data-hotel-id') || '12345';
        var cityCode = button.getAttribute('data-city-code') || button.getAttribute('data-city-id') || urlParams.cityCode || '150184';
        var checkIn = button.getAttribute('data-check-in') || urlParams.checkIn || getCurrentDate();
        var checkOut = button.getAttribute('data-check-out') || urlParams.checkOut || getNextDayDate();
        var adults = button.getAttribute('data-adults') || urlParams.adults || '2';
        var children = button.getAttribute('data-children') || urlParams.children || '0';
        var rooms = button.getAttribute('data-rooms') || urlParams.rooms || '1';
        
        // Add new click handler
        var newButton = button.cloneNode(true);
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Build URL
            var url = '<?php echo site_url("/simple-room-selection.php"); ?>';
            url += '?hotel_code=' + encodeURIComponent(hotelCode);
            url += '&city_code=' + encodeURIComponent(cityCode);
            url += '&check_in=' + encodeURIComponent(checkIn);
            url += '&check_out=' + encodeURIComponent(checkOut);
            url += '&adults=' + encodeURIComponent(adults);
            url += '&children=' + encodeURIComponent(children);
            url += '&rooms=' + encodeURIComponent(rooms);
            
            console.log('Navigating to:', url);
            window.location.href = url;
            return false;
        });
        
        // Replace old button
        if (button.parentNode) {
            button.parentNode.replaceChild(newButton, button);
            console.log('Button fixed:', hotelCode);
        }
    }
    
    // Extract parameters from URL
    function extractURLParameters() {
        var params = {};
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            var key = decodeURIComponent(pair[0]);
            var value = decodeURIComponent(pair[1] || '');
            
            // Map URL parameters
            if (key === 'check_in') params.checkIn = value;
            else if (key === 'check_out') params.checkOut = value;
            else if (key === 'city_code') params.cityCode = value;
            else if (key === 'adults') params.adults = value;
            else if (key === 'children') params.children = value;
            else if (key === 'rooms') params.rooms = value;
        }
        
        return params;
    }
    
    // Get current date in YYYY-MM-DD format
    function getCurrentDate() {
        var date = new Date();
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    }
    
    // Get next day date in YYYY-MM-DD format
    function getNextDayDate() {
        var date = new Date();
        date.setDate(date.getDate() + 1);
        
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    }
    
    // Add a global click handler for fallback
    function addGlobalClickHandler() {
        document.addEventListener('click', function(e) {
            var target = e.target;
            var text = target.textContent || target.value || '';
            
            if (text.trim().toLowerCase().indexOf('choose room') !== -1) {
                console.log('Global handler caught a Choose Room button click');
                
                e.preventDefault();
                e.stopPropagation();
                
                // Extract URL parameters
                var urlParams = extractURLParameters();
                
                // Build URL with default values
                var url = '<?php echo site_url("/simple-room-selection.php"); ?>';
                url += '?hotel_code=12345';
                url += '&city_code=' + encodeURIComponent(urlParams.cityCode || '150184');
                url += '&check_in=' + encodeURIComponent(urlParams.checkIn || getCurrentDate());
                url += '&check_out=' + encodeURIComponent(urlParams.checkOut || getNextDayDate());
                url += '&adults=' + encodeURIComponent(urlParams.adults || '2');
                url += '&children=' + encodeURIComponent(urlParams.children || '0');
                url += '&rooms=' + encodeURIComponent(urlParams.rooms || '1');
                
                console.log('Navigating to:', url);
                window.location.href = url;
                return false;
            }
        }, true);
        
        console.log('Global click handler installed');
    }
    
    // Run when DOM is loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', onDOMLoaded);
    } else {
        // DOM already loaded
        onDOMLoaded();
    }
})();
</script>
<?php
// Add direct action hook for WordPress integration
function tbo_hotels_add_direct_injection() {
    include_once(dirname(__FILE__) . '/button-fix-direct-injection.php');
}

// If this file is included in WordPress, add the action
if (function_exists('add_action')) {
    add_action('wp_footer', 'tbo_hotels_add_direct_injection', 999);
}
?>