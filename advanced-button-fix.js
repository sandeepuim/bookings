/**
 * Advanced Button Fix for TBO Hotels
 * 
 * This script fixes the "Choose Room" buttons on the hotel results page.
 * It uses multiple approaches to ensure buttons work even with different HTML structures.
 */

(function($) {
    'use strict';
    
    // Configuration
    var config = {
        // Selectors for finding buttons
        buttonSelectors: [
            '.choose-room-btn', 
            '[data-hotel-code]', 
            'button:contains("Choose Room")',
            'a:contains("Choose Room")',
            '.btn:contains("Room")',
            '.button:contains("Room")'
        ],
        
        // Default URL for room selection
        roomSelectionUrl: '/bookings/simple-room-selection.php',
        
        // Default parameters
        defaultParams: {
            checkIn: '2025-09-20',
            checkOut: '2025-09-25',
            adults: '2',
            children: '0',
            rooms: '1'
        },
        
        // Debug mode
        debug: true,
        
        // Log clicks to server
        logClicks: true,
        
        // Log endpoint
        logEndpoint: '/bookings/log-button-click.php'
    };
    
    // Utility functions
    var utils = {
        /**
         * Log message to console if debug is enabled
         */
        log: function(message, data) {
            if (config.debug) {
                if (data) {
                    console.log('TBO Button Fix: ' + message, data);
                } else {
                    console.log('TBO Button Fix: ' + message);
                }
            }
        },
        
        /**
         * Log error to console
         */
        error: function(message, data) {
            if (data) {
                console.error('TBO Button Fix ERROR: ' + message, data);
            } else {
                console.error('TBO Button Fix ERROR: ' + message);
            }
        },
        
        /**
         * Log button click to server
         */
        logClick: function(buttonData, event) {
            if (!config.logClicks) return;
            
            try {
                $.ajax({
                    url: config.logEndpoint,
                    type: 'POST',
                    data: JSON.stringify({
                        buttonData: buttonData,
                        pageUrl: window.location.href,
                        eventType: event.type,
                        timestamp: new Date().toISOString()
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        utils.log('Click logged successfully', response);
                    },
                    error: function(xhr, status, error) {
                        utils.error('Failed to log click', {
                            status: status,
                            error: error
                        });
                    }
                });
            } catch (e) {
                utils.error('Error logging click', e);
            }
        },
        
        /**
         * Get the root URL of the site
         */
        getSiteUrl: function() {
            return window.location.protocol + '//' + window.location.host;
        },
        
        /**
         * Get room selection URL
         */
        getRoomSelectionUrl: function() {
            return utils.getSiteUrl() + config.roomSelectionUrl;
        },
        
        /**
         * Get data attribute or fallback to default
         */
        getDataAttr: function(element, attr, defaultValue) {
            var value = $(element).data(attr);
            if (value === undefined || value === '') {
                value = $(element).attr('data-' + attr);
            }
            return (value === undefined || value === '') ? defaultValue : value;
        },
        
        /**
         * Build URL for room selection
         */
        buildRoomSelectionUrl: function(data) {
            var url = utils.getRoomSelectionUrl();
            var params = [];
            
            // Add hotel code
            if (data.hotelCode) {
                params.push('hotel_code=' + encodeURIComponent(data.hotelCode));
            }
            
            // Add city code
            if (data.cityCode) {
                params.push('city_code=' + encodeURIComponent(data.cityCode));
            }
            
            // Add check-in date
            if (data.checkIn) {
                params.push('check_in=' + encodeURIComponent(data.checkIn));
            }
            
            // Add check-out date
            if (data.checkOut) {
                params.push('check_out=' + encodeURIComponent(data.checkOut));
            }
            
            // Add adults
            if (data.adults) {
                params.push('adults=' + encodeURIComponent(data.adults));
            }
            
            // Add children
            if (data.children) {
                params.push('children=' + encodeURIComponent(data.children));
            }
            
            // Add rooms
            if (data.rooms) {
                params.push('rooms=' + encodeURIComponent(data.rooms));
            }
            
            // Add query string to URL
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            return url;
        }
    };
    
    // Main button fix functionality
    var buttonFix = {
        /**
         * Initialize the button fix
         */
        init: function() {
            utils.log('Initializing button fix');
            
            try {
                this.findButtons();
                this.setupButtons();
                this.setupFallbackLinks();
                utils.log('Button fix initialized successfully');
            } catch (e) {
                utils.error('Error initializing button fix', e);
                this.applyEmergencyFix();
            }
        },
        
        /**
         * Find all buttons that need fixing
         */
        findButtons: function() {
            this.buttons = $();
            
            // Try each selector
            $.each(config.buttonSelectors, function(i, selector) {
                try {
                    var found = $(selector);
                    if (found.length > 0) {
                        utils.log('Found ' + found.length + ' buttons using selector: ' + selector);
                        this.buttons = this.buttons.add(found);
                    }
                } catch (e) {
                    utils.error('Error with selector: ' + selector, e);
                }
            }.bind(this));
            
            utils.log('Found a total of ' + this.buttons.length + ' buttons');
            
            // If no buttons found, try more aggressive approach
            if (this.buttons.length === 0) {
                utils.log('No buttons found with standard selectors. Trying fallback approach...');
                this.findButtonsByContent();
            }
        },
        
        /**
         * Find buttons by their text content
         */
        findButtonsByContent: function() {
            try {
                var potentialButtons = $('button, .btn, .button, a.btn, a.button, a[href="#"]');
                var matchingButtons = potentialButtons.filter(function() {
                    var text = $(this).text().toLowerCase();
                    return text.includes('choose') || text.includes('room') || 
                           text.includes('select') || text.includes('book');
                });
                
                utils.log('Found ' + matchingButtons.length + ' potential buttons by text content');
                this.buttons = this.buttons.add(matchingButtons);
            } catch (e) {
                utils.error('Error finding buttons by content', e);
            }
        },
        
        /**
         * Set up the button click handlers
         */
        setupButtons: function() {
            if (this.buttons.length === 0) {
                utils.error('No buttons found to fix');
                return;
            }
            
            utils.log('Setting up ' + this.buttons.length + ' buttons');
            
            this.buttons.each(function(i, button) {
                try {
                    // Remove existing click handlers
                    $(button).off('click.tboFix');
                    
                    // Add new click handler
                    $(button).on('click.tboFix', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        
                        // Get data from button or parent elements
                        var $button = $(this);
                        var $container = $button.closest('[data-hotel-code], [data-hotel-id]');
                        
                        var buttonData = {
                            hotelCode: utils.getDataAttr($button, 'hotelCode', '') || 
                                      utils.getDataAttr($button, 'hotel-code', '') || 
                                      utils.getDataAttr($container, 'hotelCode', '') || 
                                      utils.getDataAttr($container, 'hotel-code', ''),
                                      
                            cityCode: utils.getDataAttr($button, 'cityCode', '') || 
                                     utils.getDataAttr($button, 'city-code', '') || 
                                     utils.getDataAttr($container, 'cityCode', '') || 
                                     utils.getDataAttr($container, 'city-code', '') || 
                                     '150184',  // Default city code
                                     
                            checkIn: utils.getDataAttr($button, 'checkIn', '') || 
                                    utils.getDataAttr($button, 'check-in', '') || 
                                    config.defaultParams.checkIn,
                                    
                            checkOut: utils.getDataAttr($button, 'checkOut', '') || 
                                     utils.getDataAttr($button, 'check-out', '') || 
                                     config.defaultParams.checkOut,
                                     
                            adults: utils.getDataAttr($button, 'adults', '') || 
                                   config.defaultParams.adults,
                                   
                            children: utils.getDataAttr($button, 'children', '') || 
                                     config.defaultParams.children,
                                     
                            rooms: utils.getDataAttr($button, 'rooms', '') || 
                                  config.defaultParams.rooms
                        };
                        
                        utils.log('Button clicked with data', buttonData);
                        
                        // Check if hotel code is available
                        if (!buttonData.hotelCode) {
                            utils.error('No hotel code found for button', button);
                            alert('Error: Could not determine hotel code. Please contact support.');
                            return;
                        }
                        
                        // Log the click
                        utils.logClick(buttonData, e);
                        
                        // Build URL and navigate
                        var url = utils.buildRoomSelectionUrl(buttonData);
                        utils.log('Navigating to', url);
                        window.location.href = url;
                    });
                    
                    utils.log('Setup button ' + (i+1), button);
                } catch (e) {
                    utils.error('Error setting up button ' + (i+1), e);
                }
            });
        },
        
        /**
         * Set up fallback direct links for when JavaScript fails
         */
        setupFallbackLinks: function() {
            try {
                this.buttons.each(function(i, button) {
                    var $button = $(button);
                    
                    // Skip if it's already an anchor with href
                    if (button.tagName.toLowerCase() === 'a' && $button.attr('href') && 
                        $button.attr('href') !== '#' && $button.attr('href') !== 'javascript:void(0)') {
                        return;
                    }
                    
                    // Get button data
                    var $container = $button.closest('[data-hotel-code], [data-hotel-id]');
                    
                    var buttonData = {
                        hotelCode: utils.getDataAttr($button, 'hotelCode', '') || 
                                  utils.getDataAttr($button, 'hotel-code', '') || 
                                  utils.getDataAttr($container, 'hotelCode', '') || 
                                  utils.getDataAttr($container, 'hotel-code', ''),
                                  
                        cityCode: utils.getDataAttr($button, 'cityCode', '') || 
                                 utils.getDataAttr($button, 'city-code', '') || 
                                 utils.getDataAttr($container, 'cityCode', '') || 
                                 utils.getDataAttr($container, 'city-code', '') || 
                                 '150184',
                                 
                        checkIn: utils.getDataAttr($button, 'checkIn', '') || 
                                utils.getDataAttr($button, 'check-in', '') || 
                                config.defaultParams.checkIn,
                                
                        checkOut: utils.getDataAttr($button, 'checkOut', '') || 
                                 utils.getDataAttr($button, 'check-out', '') || 
                                 config.defaultParams.checkOut
                    };
                    
                    // Skip if no hotel code
                    if (!buttonData.hotelCode) {
                        return;
                    }
                    
                    // Build fallback URL
                    var url = utils.buildRoomSelectionUrl(buttonData);
                    
                    // Set href attribute for non-anchor elements
                    if (button.tagName.toLowerCase() !== 'a') {
                        $button.attr('data-href', url);
                    } 
                    // Update href for anchor elements with # or javascript:void(0)
                    else if ($button.attr('href') === '#' || $button.attr('href') === 'javascript:void(0)') {
                        $button.attr('href', url);
                    }
                });
                
                utils.log('Set up fallback links for buttons');
            } catch (e) {
                utils.error('Error setting up fallback links', e);
            }
        },
        
        /**
         * Apply emergency fix when everything else fails
         */
        applyEmergencyFix: function() {
            utils.log('Applying emergency button fix');
            
            try {
                // Find all buttons and links that might be Choose Room buttons
                var potentialButtons = $('button, .btn, .button, a.btn, a.button, a[href="#"], a:contains("Room")');
                
                potentialButtons.each(function(i, button) {
                    var $button = $(button);
                    var text = $button.text().toLowerCase();
                    
                    if (text.includes('choose') || text.includes('room') || 
                        text.includes('select') || text.includes('book')) {
                        
                        // Try to find hotel code in the button or its parents
                        var $container = $button.closest('[data-hotel-code], [data-hotel-id], [id*="hotel"]');
                        var hotelCode = utils.getDataAttr($button, 'hotelCode', '') || 
                                       utils.getDataAttr($button, 'hotel-code', '') || 
                                       utils.getDataAttr($container, 'hotelCode', '') || 
                                       utils.getDataAttr($container, 'hotel-code', '');
                        
                        // If no hotel code found, try to extract it from element IDs
                        if (!hotelCode) {
                            var buttonId = $button.attr('id') || '';
                            var containerId = $container.attr('id') || '';
                            
                            var idMatch = buttonId.match(/hotel[-_]?(\d+)/) || containerId.match(/hotel[-_]?(\d+)/);
                            if (idMatch && idMatch[1]) {
                                hotelCode = idMatch[1];
                            }
                        }
                        
                        // If still no hotel code, use a fallback
                        if (!hotelCode) {
                            utils.log('No hotel code found for button, using fallback mechanism');
                            
                            // Add click handler for manual entry
                            $button.on('click.tboEmergency', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                // Ask for hotel code
                                var promptedHotelCode = prompt('Please enter the hotel code (usually a number):');
                                if (!promptedHotelCode) return;
                                
                                // Ask for city code
                                var promptedCityCode = prompt('Please enter the city code (default: 150184):', '150184');
                                if (!promptedCityCode) promptedCityCode = '150184';
                                
                                // Build URL and navigate
                                var url = utils.getRoomSelectionUrl() + 
                                         '?hotel_code=' + encodeURIComponent(promptedHotelCode) + 
                                         '&city_code=' + encodeURIComponent(promptedCityCode) + 
                                         '&check_in=' + encodeURIComponent(config.defaultParams.checkIn) + 
                                         '&check_out=' + encodeURIComponent(config.defaultParams.checkOut);
                                
                                utils.log('Emergency navigation to', url);
                                window.location.href = url;
                            });
                        } else {
                            // Add click handler with found hotel code
                            $button.on('click.tboEmergency', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                
                                var url = utils.getRoomSelectionUrl() + 
                                         '?hotel_code=' + encodeURIComponent(hotelCode) + 
                                         '&city_code=150184' + 
                                         '&check_in=' + encodeURIComponent(config.defaultParams.checkIn) + 
                                         '&check_out=' + encodeURIComponent(config.defaultParams.checkOut);
                                
                                utils.log('Emergency navigation to', url);
                                window.location.href = url;
                            });
                        }
                    }
                });
                
                utils.log('Emergency fix applied to ' + potentialButtons.length + ' potential buttons');
                
                // Show notification to user
                var notification = $('<div>').css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    backgroundColor: '#ff9800',
                    color: 'white',
                    padding: '15px',
                    borderRadius: '5px',
                    boxShadow: '0 2px 10px rgba(0,0,0,0.2)',
                    zIndex: 9999
                }).text('TBO Hotels: Emergency button fix applied. Some functionality may be limited.');
                
                $('body').append(notification);
                
                setTimeout(function() {
                    notification.fadeOut(500, function() {
                        notification.remove();
                    });
                }, 5000);
            } catch (e) {
                utils.error('Error applying emergency fix', e);
            }
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        utils.log('Document ready, initializing button fix');
        buttonFix.init();
    });
    
    // Re-initialize on AJAX content load
    $(document).on('ajaxComplete', function() {
        utils.log('AJAX completed, re-initializing button fix');
        buttonFix.init();
    });
    
    // Make the button fix accessible globally
    window.TBOButtonFix = buttonFix;
    
})(jQuery);