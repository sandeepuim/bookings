/**
 * TBO Hotels - Selector Fix
 * 
 * This script adds support for jQuery-style :contains() selector in standard querySelectorAll
 */

(function() {
    'use strict';
    
    console.log('TBO Hotels: Selector Fix loaded');
    
    // Store original querySelectorAll method
    var originalQuerySelectorAll = Document.prototype.querySelectorAll;
    var originalElementQuerySelectorAll = Element.prototype.querySelectorAll;
    
    // Polyfill for :contains() selector
    function containsPolyfill(selector) {
        // If selector contains the :contains() pseudo-class
        if (selector && typeof selector === 'string' && selector.includes(':contains(')) {
            // Extract the text from :contains()
            var matches = selector.match(/:contains\(["']?(.*?)["']?\)/);
            if (matches && matches[1]) {
                var containsText = matches[1];
                
                // Remove the :contains() part to get a valid selector
                var baseSelector = selector.replace(/:contains\(["']?(.*?)["']?\)/, '');
                
                try {
                    // First get elements matching the base selector
                    var elements = Array.from(originalQuerySelectorAll.call(document, baseSelector));
                    
                    // Then filter for those containing the text
                    return elements.filter(function(el) {
                        return el.textContent.includes(containsText);
                    });
                } catch (e) {
                    console.warn('Error in containsPolyfill:', e);
                    return [];
                }
            }
        }
        
        // If no :contains or couldn't parse it, return null to use original method
        return null;
    }
    
    // Override Document.prototype.querySelectorAll
    Document.prototype.querySelectorAll = function(selector) {
        try {
            // Try to use the original method first
            return originalQuerySelectorAll.apply(this, arguments);
        } catch (e) {
            console.warn('TBO Hotels: querySelectorAll error caught:', e.message);
            
            // If the selector contains :contains(), use our polyfill
            var result = containsPolyfill(selector);
            if (result !== null) {
                return result;
            }
            
            // For other invalid selectors, return empty NodeList
            console.warn('TBO Hotels: Invalid selector, returning empty result:', selector);
            return document.createDocumentFragment().childNodes;
        }
    };
    
    // Also override Element.prototype.querySelectorAll for consistency
    Element.prototype.querySelectorAll = function(selector) {
        try {
            // Try original method first
            return originalElementQuerySelectorAll.apply(this, arguments);
        } catch (e) {
            console.warn('TBO Hotels: Element.querySelectorAll error caught:', e.message);
            
            // For invalid selectors, return empty NodeList
            return document.createDocumentFragment().childNodes;
        }
    };
    
    // Fix specific "button:contains()" selector issue on hotel results page
    function fixChooseRoomButtonSelectors() {
        // Try to find Choose Room buttons using various methods
        var buttons = [];
        
        try {
            // Method 1: By class
            var classBased = document.querySelectorAll('.choose-room-btn, .btn-choose-room, .select-room-btn');
            buttons = Array.from(classBased);
        } catch (e) {
            console.warn('Error finding buttons by class:', e);
        }
        
        if (buttons.length === 0) {
            try {
                // Method 2: By attribute
                var attrBased = document.querySelectorAll('button[data-action="choose-room"], a[data-action="choose-room"]');
                buttons = Array.from(attrBased);
            } catch (e) {
                console.warn('Error finding buttons by attribute:', e);
            }
        }
        
        if (buttons.length === 0) {
            try {
                // Method 3: Text search (manual implementation of :contains)
                var allButtons = document.querySelectorAll('button, .btn, a.button, a.btn');
                buttons = Array.from(allButtons).filter(function(btn) {
                    return btn.textContent.includes('Choose Room');
                });
            } catch (e) {
                console.warn('Error finding buttons by text content:', e);
            }
        }
        
        console.log('Found ' + buttons.length + ' "Choose Room" buttons');
        
        // Apply any fixes to the buttons if needed
        buttons.forEach(function(btn, index) {
            // Make sure it has proper attributes and event handlers
            if (!btn.hasAttribute('data-hotel-id') && btn.closest('.hotel-card')) {
                var hotelCard = btn.closest('.hotel-card');
                if (hotelCard.hasAttribute('data-hotel-id')) {
                    btn.setAttribute('data-hotel-id', hotelCard.getAttribute('data-hotel-id'));
                }
            }
            
            console.log('Fixed Choose Room button #' + (index + 1));
        });
    }
    
    // Run when DOM is ready
    if (document.readyState !== 'loading') {
        fixChooseRoomButtonSelectors();
    } else {
        document.addEventListener('DOMContentLoaded', fixChooseRoomButtonSelectors);
    }
    
    // Also run after window load to catch dynamically added elements
    window.addEventListener('load', function() {
        setTimeout(fixChooseRoomButtonSelectors, 500);
    });
})();