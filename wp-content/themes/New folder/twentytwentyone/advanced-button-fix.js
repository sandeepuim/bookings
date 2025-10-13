/**
 * Advanced Button Fix for TBO Hotels
 * 
 * This script fixes the "Choose Room" buttons with a more robust approach
 * that handles different button formats and selectors.
 */

// Use an immediately invoked function expression to avoid conflicts
(function() {
    // Run when DOM is fully loaded
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels - Advanced Button Fix loaded');
        
        // Try different methods to find the buttons
        findAndFixButtons();
        
        // Also try again after a short delay for any dynamically loaded content
        setTimeout(findAndFixButtons, 1000);
        
        // And try one more time after a longer delay
        setTimeout(findAndFixButtons, 3000);
    });
    
    // Function to find and fix all button variations
    function findAndFixButtons() {
        console.log('Looking for Choose Room buttons...');
        
        // Track how many buttons we found
        var totalFixed = 0;
        
        // METHOD 1: Try standard class selector
        var buttonsByClass = document.querySelectorAll('.choose-room-btn');
        if (buttonsByClass.length > 0) {
            console.log('Found ' + buttonsByClass.length + ' buttons by class');
            fixButtonCollection(buttonsByClass);
            totalFixed += buttonsByClass.length;
        }
        
        // METHOD 2: Try text content matching
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
            console.log('Found ' + chooseRoomButtons.length + ' buttons by text content');
            fixButtonCollection(chooseRoomButtons);
            totalFixed += chooseRoomButtons.length;
        }
        
        // METHOD 3: Try to find elements with "choose-room" or similar in their attributes
        var possibleButtons = document.querySelectorAll('[class*="room"], [id*="room"], [class*="book"], [id*="book"]');
        var attrButtons = [];
        
        for (var i = 0; i < possibleButtons.length; i++) {
            var element = possibleButtons[i];
            var tagName = element.tagName.toLowerCase();
            
            // Only consider elements that are likely to be buttons
            if (tagName === 'button' || tagName === 'a' || tagName === 'input' || 
                element.getAttribute('role') === 'button') {
                
                attrButtons.push(element);
            }
        }
        
        if (attrButtons.length > 0) {
            console.log('Found ' + attrButtons.length + ' possible buttons by attributes');
            fixButtonCollection(attrButtons);
            totalFixed += attrButtons.length;
        }
        
        console.log('Total buttons fixed: ' + totalFixed);
        
        // If we didn't find any buttons, try a more aggressive approach
        if (totalFixed === 0) {
            console.log('No buttons found with standard methods, trying fallback approach');
            tryFallbackButtonFix();
        }
    }
    
    // Function to apply fix to a collection of buttons
    function fixButtonCollection(buttons) {
        for (var i = 0; i < buttons.length; i++) {
            try {
                fixSingleButton(buttons[i]);
            } catch (err) {
                console.error('Error fixing button:', err);
            }
        }
    }
    
    // Function to fix a single button
    function fixSingleButton(button) {
        console.log('Fixing button:', button);
        
        // Extract parameters from data attributes or closest hotel container
        var params = extractParameters(button);
        
        // Create a new button to replace the original (to remove any existing handlers)
        var newButton = button.cloneNode(true);
        
        // Add the click handler to the new button
        newButton.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Build the URL
            var url = buildRoomSelectionUrl(params);
            
            // Navigate to the URL
            console.log('Navigating to:', url);
            window.location.href = url;
            
            return false;
        });
        
        // Replace the old button with the new one
        if (button.parentNode) {
            button.parentNode.replaceChild(newButton, button);
            console.log('Button successfully fixed');
        }
    }
    
    // Function to extract parameters from a button or its container
    function extractParameters(button) {
        var params = {};
        
        // First try to get parameters from data attributes on the button itself
        params.hotelCode = button.getAttribute('data-hotel-code') || button.getAttribute('data-hotel-id');
        params.cityCode = button.getAttribute('data-city-code') || button.getAttribute('data-city-id');
        params.checkIn = button.getAttribute('data-check-in');
        params.checkOut = button.getAttribute('data-check-out');
        params.adults = button.getAttribute('data-adults');
        params.children = button.getAttribute('data-children');
        params.rooms = button.getAttribute('data-rooms');
        
        // If we couldn't find parameters on the button, try to find a parent hotel container
        if (!params.hotelCode) {
            var hotelContainer = findHotelContainer(button);
            
            if (hotelContainer) {
                // Try to get parameters from the hotel container
                params.hotelCode = hotelContainer.getAttribute('data-hotel-code') || 
                                 hotelContainer.getAttribute('data-hotel-id');
                params.cityCode = hotelContainer.getAttribute('data-city-code') || 
                               hotelContainer.getAttribute('data-city-id');
            }
        }
        
        // Get search parameters from the URL if they're not found elsewhere
        if (!params.checkIn || !params.checkOut) {
            var urlParams = extractURLParameters();
            params.checkIn = params.checkIn || urlParams.checkIn;
            params.checkOut = params.checkOut || urlParams.checkOut;
            params.adults = params.adults || urlParams.adults;
            params.children = params.children || urlParams.children;
            params.rooms = params.rooms || urlParams.rooms;
            params.cityCode = params.cityCode || urlParams.cityCode;
        }
        
        // Use defaults for any missing parameters
        params.hotelCode = params.hotelCode || '12345';
        params.cityCode = params.cityCode || '150184';
        params.checkIn = params.checkIn || getCurrentDate();
        params.checkOut = params.checkOut || getNextDayDate();
        params.adults = params.adults || '2';
        params.children = params.children || '0';
        params.rooms = params.rooms || '1';
        
        return params;
    }
    
    // Function to find a parent hotel container for a button
    function findHotelContainer(button) {
        // Try to find a parent element that seems to be a hotel container
        var element = button;
        var maxLevelsUp = 5; // Don't go up too many levels
        
        for (var i = 0; i < maxLevelsUp && element; i++) {
            element = element.parentElement;
            
            if (!element) break;
            
            // Check if this looks like a hotel container
            if (element.classList.contains('hotel-card') || 
                element.classList.contains('hotel-item') || 
                element.classList.contains('hotel-result') ||
                element.getAttribute('data-hotel-code') ||
                element.getAttribute('data-hotel-id')) {
                
                return element;
            }
            
            // Also check by ID
            var id = element.id || '';
            if (id.toLowerCase().indexOf('hotel') !== -1) {
                return element;
            }
        }
        
        return null;
    }
    
    // Function to extract parameters from the URL
    function extractURLParameters() {
        var params = {};
        var query = window.location.search.substring(1);
        var vars = query.split('&');
        
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split('=');
            var key = decodeURIComponent(pair[0]);
            var value = decodeURIComponent(pair[1] || '');
            
            // Map URL parameters to our parameter names
            if (key === 'check_in') params.checkIn = value;
            else if (key === 'check_out') params.checkOut = value;
            else if (key === 'city_code') params.cityCode = value;
            else if (key === 'adults') params.adults = value;
            else if (key === 'children') params.children = value;
            else if (key === 'rooms') params.rooms = value;
        }
        
        return params;
    }
    
    // Function to get the current date in YYYY-MM-DD format
    function getCurrentDate() {
        var date = new Date();
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    }
    
    // Function to get tomorrow's date in YYYY-MM-DD format
    function getNextDayDate() {
        var date = new Date();
        date.setDate(date.getDate() + 1);
        
        var year = date.getFullYear();
        var month = (date.getMonth() + 1).toString().padStart(2, '0');
        var day = date.getDate().toString().padStart(2, '0');
        
        return year + '-' + month + '-' + day;
    }
    
    // Function to build the room selection URL
    function buildRoomSelectionUrl(params) {
        var url = 'simple-room-selection.php';
        url += '?hotel_code=' + encodeURIComponent(params.hotelCode || '');
        url += '&city_code=' + encodeURIComponent(params.cityCode || '');
        url += '&check_in=' + encodeURIComponent(params.checkIn || '');
        url += '&check_out=' + encodeURIComponent(params.checkOut || '');
        url += '&adults=' + encodeURIComponent(params.adults || '2');
        url += '&children=' + encodeURIComponent(params.children || '0');
        url += '&rooms=' + encodeURIComponent(params.rooms || '1');
        
        return url;
    }
    
    // Fallback method for fixing buttons
    function tryFallbackButtonFix() {
        // Add a global click handler to catch clicks on anything that might be a "Choose Room" button
        document.addEventListener('click', function(e) {
            var target = e.target;
            var text = target.textContent || target.value || '';
            
            // Check if this might be a Choose Room button
            if (text.trim().toLowerCase().indexOf('choose room') !== -1 ||
                (target.classList && target.classList.contains('choose-room')) ||
                (target.id && target.id.indexOf('choose-room') !== -1)) {
                
                console.log('Fallback handler caught a Choose Room button click:', target);
                
                // Prevent the default action
                e.preventDefault();
                e.stopPropagation();
                
                // Extract parameters and navigate
                var params = extractParameters(target);
                var url = buildRoomSelectionUrl(params);
                
                console.log('Navigating to:', url);
                window.location.href = url;
                
                return false;
            }
        }, true); // Use capture phase to get the event before other handlers
        
        console.log('Fallback global click handler installed');
    }
})();