/**
 * TBO Hotel Booking Public JS
 * 
 * This is a fixed version of the file that had syntax errors.
 */
(function($) {
    'use strict';
    
    // Create global object for TBO Hotel Booking
    window.tboHotelBooking = window.tboHotelBooking || {};
    
    // Main initialization function
    window.tboHotelBooking.init = function() {
        console.log('TBO Hotel Booking initialized properly');
        
        // Initialize room selection if on room selection page
        if ($('.tbo-room-selection').length) {
            tboHotelBooking.initRoomSelection();
        }
        
        // Initialize hotel search if on search page
        if ($('#hotel-search-form').length) {
            tboHotelBooking.initHotelSearch();
        }
        
        // Initialize hotel results if on results page
        if ($('.hotel-results-container').length) {
            tboHotelBooking.initHotelResults();
        }
    };
    
    // Room selection initialization
    window.tboHotelBooking.initRoomSelection = function() {
        console.log('Room selection initialized');
        
        // Handle room selection click
        $('.select-room-button').on('click', function(e) {
            try {
                e.preventDefault();
                var roomId = $(this).data('room-id');
                console.log('Room selected:', roomId);
                
                // Add room selection logic here
            } catch(err) {
                console.warn('Error in room selection handling:', err);
            }
        });
    };
    
    // Hotel search initialization
    window.tboHotelBooking.initHotelSearch = function() {
        console.log('Hotel search initialized');
        
        // Handle search form submission
        $('#hotel-search-form').on('submit', function(e) {
            try {
                // Form validation logic here
                var valid = true;
                
                // If not valid, prevent default
                if (!valid) {
                    e.preventDefault();
                    return false;
                }
                
                return true;
            } catch(err) {
                console.warn('Error in search form handling:', err);
                return true; // Allow form submission even if our JS fails
            }
        });
    };
    
    // Hotel results initialization with proper error handling
    window.tboHotelBooking.initHotelResults = function() {
        try {
            console.log('Hotel results initialized');
            
            // Safely add event handlers to hotel cards
            $('.hotel-card').each(function() {
                try {
                    var card = $(this);
                    
                    // Make entire card clickable if not clicking a button or link
                    card.on('click', function(e) {
                        try {
                            // Don't intercept clicks on buttons or links
                            if ($(e.target).is('a, button, .btn, [role="button"]') || 
                                $(e.target).parents('a, button, .btn, [role="button"]').length) {
                                return;
                            }
                            
                            // Find the main hotel details link
                            var link = card.find('.hotel-details-link, .hotel-title a').first();
                            if (link.length) {
                                e.preventDefault();
                                window.location.href = link.attr('href');
                            }
                        } catch(err) {
                            console.warn('Error in card click handler:', err);
                        }
                    });
                    
                    // Add proper event handlers to "Choose Room" buttons
                    card.find('.choose-room-btn, button:contains("Choose Room")').on('click', function(e) {
                        try {
                            e.preventDefault();
                            
                            var hotelId = $(this).data('hotel-id') || card.data('hotel-id');
                            var hotelName = card.find('.hotel-title').text().trim();
                            
                            console.log('Choose Room clicked for hotel:', hotelId, hotelName);
                            
                            // Get the button's href or data-url
                            var url = $(this).attr('href') || 
                                     $(this).data('url') || 
                                     '/bookings/room-selection/?hotel_id=' + hotelId;
                                     
                            // Navigate to room selection page
                            window.location.href = url;
                        } catch(err) {
                            console.warn('Error in Choose Room button handler:', err);
                        }
                    });
                } catch(err) {
                    console.warn('Error setting up hotel card:', err);
                }
            });
            
            // Initialize filter functionality if present
            if ($('.hotel-filter').length) {
                try {
                    $('.hotel-filter-item').on('change', function() {
                        // Filter logic here
                    });
                } catch(err) {
                    console.warn('Error in filter handling:', err);
                }
            }
            
            // Initialize sorting functionality if present
            if ($('.hotel-sort').length) {
                try {
                    $('.hotel-sort-option').on('click', function(e) {
                        e.preventDefault();
                        // Sorting logic here
                    });
                } catch(err) {
                    console.warn('Error in sort handling:', err);
                }
            }
        } catch(err) {
            console.warn('Error initializing hotel results:', err);
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        try {
            // Initialize the plugin
            if (typeof window.tboHotelBooking.init === 'function') {
                window.tboHotelBooking.init();
            }
        } catch(err) {
            console.warn('Error initializing TBO Hotel Booking:', err);
        }
    });
    
})(jQuery);