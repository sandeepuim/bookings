/**
 * TBO Hotels Enhanced JavaScript
 * 
 * Handles AJAX communication with the enhanced TBO API implementation
 * and provides better error handling and user experience.
 */

(function($) {
    'use strict';
    
    // Object to hold all TBO enhanced functionality
    window.TBOEnhanced = {
        
        /**
         * Initialize the enhanced TBO functionality
         */
        init: function() {
            // Initialize search form handler
            this.initSearchForm();
            
            // Initialize load more handler
            this.initLoadMore();
            
            // Initialize hotel details handler
            this.initHotelDetails();
            
            // Override the default search if it exists
            if (typeof window.performHotelSearch === 'function') {
                window.originalPerformHotelSearch = window.performHotelSearch;
                window.performHotelSearch = this.performHotelSearch;
            }
            
            console.log('TBO Enhanced initialized');
        },
        
        /**
         * Initialize the search form handler
         */
        initSearchForm: function() {
            // Find all hotel search forms
            $('.hotel-search-form, #hotel-search-form, .tbo-search-form').on('submit', function(e) {
                e.preventDefault();
                
                // Show loading indicator
                TBOEnhanced.showLoading('Searching for hotels...');
                
                // Get form data
                const formData = $(this).serialize();
                
                // Get form values for enhanced API
                const cityId = $(this).find('[name="city_id"]').val();
                const checkIn = $(this).find('[name="check_in"]').val();
                const checkOut = $(this).find('[name="check_out"]').val();
                
                // Perform search
                TBOEnhanced.performHotelSearch(cityId, checkIn, checkOut, 0, 20, formData);
                
                return false;
            });
        },
        
        /**
         * Initialize the load more handler
         */
        initLoadMore: function() {
            // Use event delegation for load more button
            $(document).on('click', '.load-more-hotels, #load-more-hotels', function(e) {
                e.preventDefault();
                
                // Show loading indicator
                TBOEnhanced.showLoading('Loading more hotels...');
                
                // Get data attributes
                const cityId = $(this).data('city-id');
                const checkIn = $(this).data('check-in');
                const checkOut = $(this).data('check-out');
                const offset = $(this).data('offset') || 0;
                const limit = $(this).data('limit') || 20;
                
                // Get additional data if available
                const adults = $(this).data('adults');
                const children = $(this).data('children');
                const rooms = $(this).data('rooms');
                
                // Create form data
                let formData = {
                    action: 'tbo_enhanced_hotel_search',
                    city_id: cityId,
                    check_in: checkIn,
                    check_out: checkOut,
                    offset: offset,
                    limit: limit
                };
                
                // Add additional data if available
                if (adults) formData.adults = adults;
                if (children) formData.children = children;
                if (rooms) formData.rooms = rooms;
                
                // Load more hotels
                TBOEnhanced.loadMoreHotels(formData);
                
                return false;
            });
        },
        
        /**
         * Initialize the hotel details handler
         */
        initHotelDetails: function() {
            // Use event delegation for hotel detail links
            $(document).on('click', '.hotel-detail-link, .tbo-hotel-details', function(e) {
                e.preventDefault();
                
                // Show loading indicator
                TBOEnhanced.showLoading('Loading hotel details...');
                
                // Get hotel code
                const hotelCode = $(this).data('hotel-code');
                
                // Load hotel details
                TBOEnhanced.loadHotelDetails(hotelCode);
                
                return false;
            });
        },
        
        /**
         * Perform a hotel search
         * 
         * @param {string} cityId The city ID
         * @param {string} checkIn Check-in date
         * @param {string} checkOut Check-out date
         * @param {number} offset Pagination offset
         * @param {number} limit Number of results to return
         * @param {object|string} additionalData Additional form data
         */
        performHotelSearch: function(cityId, checkIn, checkOut, offset, limit, additionalData) {
            // Format the form data
            let formData = {
                action: 'tbo_enhanced_hotel_search',
                city_id: cityId,
                check_in: checkIn,
                check_out: checkOut,
                offset: offset || 0,
                limit: limit || 20
            };
            
            // Add additional data if provided
            if (additionalData) {
                // If additionalData is a string, parse it
                if (typeof additionalData === 'string') {
                    const params = new URLSearchParams(additionalData);
                    params.forEach(function(value, key) {
                        formData[key] = value;
                    });
                } else {
                    // Otherwise, merge objects
                    formData = $.extend(formData, additionalData);
                }
            }
            
            // Perform AJAX request
            $.ajax({
                url: tbo_ajax_obj.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 60000, // 60 second timeout
                success: function(response) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    if (response.success) {
                        // Update hotels list
                        TBOEnhanced.updateHotelsList(response.data, offset > 0);
                    } else {
                        // Show error message
                        TBOEnhanced.showError('Search Error', response.data || 'Failed to search for hotels');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    // Show error message
                    let errorMessage = 'An error occurred while searching for hotels';
                    
                    if (status === 'timeout') {
                        errorMessage = 'The search request timed out. Please try again.';
                    }
                    
                    TBOEnhanced.showError('Search Error', errorMessage);
                    
                    console.error('TBO Enhanced API Error:', status, error);
                }
            });
        },
        
        /**
         * Load more hotels
         * 
         * @param {object} formData Form data for the request
         */
        loadMoreHotels: function(formData) {
            // Ensure action is set
            formData.action = 'tbo_enhanced_hotel_search';
            
            // Perform AJAX request
            $.ajax({
                url: tbo_ajax_obj.ajax_url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                timeout: 60000, // 60 second timeout
                success: function(response) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    if (response.success) {
                        // Update hotels list
                        TBOEnhanced.updateHotelsList(response.data, true);
                    } else {
                        // Show error message
                        TBOEnhanced.showError('Load More Error', response.data || 'Failed to load more hotels');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    // Show error message
                    let errorMessage = 'An error occurred while loading more hotels';
                    
                    if (status === 'timeout') {
                        errorMessage = 'The request timed out. Please try again.';
                    }
                    
                    TBOEnhanced.showError('Load More Error', errorMessage);
                    
                    console.error('TBO Enhanced API Error:', status, error);
                }
            });
        },
        
        /**
         * Load hotel details
         * 
         * @param {string} hotelCode The hotel code
         */
        loadHotelDetails: function(hotelCode) {
            // Perform AJAX request
            $.ajax({
                url: tbo_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbo_enhanced_hotel_details',
                    hotel_code: hotelCode
                },
                dataType: 'json',
                timeout: 30000, // 30 second timeout
                success: function(response) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    if (response.success) {
                        // Update hotel details
                        TBOEnhanced.displayHotelDetails(response.data);
                    } else {
                        // Show error message
                        TBOEnhanced.showError('Hotel Details Error', response.data || 'Failed to load hotel details');
                    }
                },
                error: function(xhr, status, error) {
                    // Hide loading indicator
                    TBOEnhanced.hideLoading();
                    
                    // Show error message
                    let errorMessage = 'An error occurred while loading hotel details';
                    
                    if (status === 'timeout') {
                        errorMessage = 'The request timed out. Please try again.';
                    }
                    
                    TBOEnhanced.showError('Hotel Details Error', errorMessage);
                    
                    console.error('TBO Enhanced API Error:', status, error);
                }
            });
        },
        
        /**
         * Update the hotels list with search results
         * 
         * @param {object} data Response data
         * @param {boolean} append Whether to append or replace the list
         */
        updateHotelsList: function(data, append) {
            // Try to find the hotels container
            const $container = $('#hotels-list, .hotels-list, .tbo-hotels-list');
            
            if (!$container.length) {
                console.error('TBO Enhanced: Hotels container not found');
                return;
            }
            
            // Clear container if not appending
            if (!append) {
                $container.empty();
            }
            
            // Check if we have hotels
            if (!data.hotels || !data.hotels.length) {
                // Show no results message
                if (!append) {
                    $container.html('<div class="no-hotels-found">No hotels found. Please try different search criteria.</div>');
                }
                return;
            }
            
            // Update page title if city name is available
            if (data.city_name && !append) {
                const pageTitle = 'Hotels in ' + data.city_name;
                $('h1.page-title, .entry-title').text(pageTitle);
                document.title = pageTitle + ' - ' + document.title.split(' - ')[1];
            }
            
            // Process each hotel
            let hotelsHtml = '';
            
            $.each(data.hotels, function(index, hotel) {
                // Check if we have the hotel template function
                if (typeof TBOEnhanced.renderHotelTemplate === 'function') {
                    hotelsHtml += TBOEnhanced.renderHotelTemplate(hotel);
                } else {
                    // Use default template
                    hotelsHtml += TBOEnhanced.defaultHotelTemplate(hotel);
                }
            });
            
            // Append or replace the hotels
            if (append) {
                $container.append(hotelsHtml);
            } else {
                $container.html(hotelsHtml);
            }
            
            // Update load more button if it exists
            const $loadMore = $('.load-more-hotels, #load-more-hotels');
            
            if ($loadMore.length) {
                // Update offset
                const newOffset = (parseInt($loadMore.data('offset') || 0) + data.hotels.length);
                $loadMore.data('offset', newOffset);
                
                // Hide load more if no more results
                if (!data.has_more) {
                    $loadMore.hide();
                } else {
                    $loadMore.show();
                }
            } else if (data.has_more && data.total > data.hotels.length) {
                // Create load more button if it doesn't exist
                const loadMoreHtml = '<div class="load-more-container">' +
                    '<button class="load-more-hotels" id="load-more-hotels" ' +
                    'data-city-id="' + data.city_id + '" ' +
                    'data-check-in="' + data.check_in + '" ' +
                    'data-check-out="' + data.check_out + '" ' +
                    'data-offset="' + data.hotels.length + '" ' +
                    'data-limit="20">Load More Hotels</button>' +
                    '<div class="results-info">Showing ' + data.hotels.length + ' of ' + data.total + ' hotels</div>' +
                    '</div>';
                
                $container.after(loadMoreHtml);
            }
            
            // Trigger hotel list updated event
            $(document).trigger('tbo_hotels_updated', [data, append]);
        },
        
        /**
         * Default hotel template
         * 
         * @param {object} hotel Hotel data
         * @return {string} Hotel HTML
         */
        defaultHotelTemplate: function(hotel) {
            // Get hotel image
            let hotelImage = '';
            if (hotel.HotelPicture && hotel.HotelPicture.length) {
                hotelImage = hotel.HotelPicture;
            } else {
                hotelImage = 'https://via.placeholder.com/300x200?text=No+Image';
            }
            
            // Format hotel price
            let priceHtml = '';
            if (hotel.Price && hotel.Price.OfferedPrice) {
                const price = parseFloat(hotel.Price.OfferedPrice);
                const currency = hotel.Price.CurrencyCode || 'USD';
                
                priceHtml = '<div class="hotel-price">' +
                    '<span class="price-label">From</span> ' +
                    '<span class="price-amount">' + currency + ' ' + price.toFixed(2) + '</span>' +
                    '</div>';
            }
            
            // Create star rating HTML
            let ratingHtml = '';
            if (hotel.StarRating) {
                const rating = parseInt(hotel.StarRating);
                ratingHtml = '<div class="hotel-rating">';
                
                for (let i = 0; i < 5; i++) {
                    if (i < rating) {
                        ratingHtml += '<span class="star filled">★</span>';
                    } else {
                        ratingHtml += '<span class="star">☆</span>';
                    }
                }
                
                ratingHtml += '</div>';
            }
            
            // Create hotel HTML
            return '<div class="hotel-item" data-hotel-code="' + hotel.HotelCode + '">' +
                '<div class="hotel-image">' +
                '<img src="' + hotelImage + '" alt="' + hotel.HotelName + '">' +
                '</div>' +
                '<div class="hotel-details">' +
                '<h3 class="hotel-name">' + hotel.HotelName + '</h3>' +
                ratingHtml +
                '<div class="hotel-location">' + (hotel.HotelAddress || '') + '</div>' +
                '<div class="hotel-description">' + (hotel.HotelDescription || '').substring(0, 150) + '...</div>' +
                priceHtml +
                '<div class="hotel-actions">' +
                '<a href="#" class="hotel-detail-link button" data-hotel-code="' + hotel.HotelCode + '">View Details</a>' +
                '</div>' +
                '</div>' +
                '</div>';
        },
        
        /**
         * Display hotel details
         * 
         * @param {object} data Hotel details data
         */
        displayHotelDetails: function(data) {
            // Try to find the hotel details container
            const $container = $('#hotel-details, .hotel-details, .tbo-hotel-details-container');
            
            if (!$container.length) {
                console.error('TBO Enhanced: Hotel details container not found');
                return;
            }
            
            // Get hotel data
            const hotel = data.hotel;
            
            if (!hotel) {
                $container.html('<div class="no-hotel-found">Hotel details not found</div>');
                return;
            }
            
            // Update page title
            const pageTitle = hotel.HotelName;
            $('h1.page-title, .entry-title').text(pageTitle);
            document.title = pageTitle + ' - ' + document.title.split(' - ')[1];
            
            // Check if we have the hotel details template function
            if (typeof TBOEnhanced.renderHotelDetailsTemplate === 'function') {
                $container.html(TBOEnhanced.renderHotelDetailsTemplate(hotel));
            } else {
                // Use default template
                $container.html(TBOEnhanced.defaultHotelDetailsTemplate(hotel));
            }
            
            // Trigger hotel details updated event
            $(document).trigger('tbo_hotel_details_updated', [data]);
            
            // Scroll to container
            $('html, body').animate({
                scrollTop: $container.offset().top - 100
            }, 500);
        },
        
        /**
         * Default hotel details template
         * 
         * @param {object} hotel Hotel data
         * @return {string} Hotel details HTML
         */
        defaultHotelDetailsTemplate: function(hotel) {
            // Create image gallery HTML
            let galleryHtml = '<div class="hotel-gallery">';
            
            if (hotel.HotelImages && hotel.HotelImages.length) {
                galleryHtml += '<div class="gallery-main">' +
                    '<img src="' + hotel.HotelImages[0] + '" alt="' + hotel.HotelName + '">' +
                    '</div>' +
                    '<div class="gallery-thumbnails">';
                
                $.each(hotel.HotelImages.slice(0, 5), function(index, image) {
                    galleryHtml += '<div class="thumbnail" data-image="' + image + '">' +
                        '<img src="' + image + '" alt="' + hotel.HotelName + ' ' + (index + 1) + '">' +
                        '</div>';
                });
                
                galleryHtml += '</div>';
            } else if (hotel.HotelPicture) {
                galleryHtml += '<div class="gallery-main">' +
                    '<img src="' + hotel.HotelPicture + '" alt="' + hotel.HotelName + '">' +
                    '</div>';
            } else {
                galleryHtml += '<div class="gallery-main">' +
                    '<img src="https://via.placeholder.com/800x400?text=No+Image" alt="' + hotel.HotelName + '">' +
                    '</div>';
            }
            
            galleryHtml += '</div>';
            
            // Create star rating HTML
            let ratingHtml = '';
            if (hotel.StarRating) {
                const rating = parseInt(hotel.StarRating);
                ratingHtml = '<div class="hotel-rating">';
                
                for (let i = 0; i < 5; i++) {
                    if (i < rating) {
                        ratingHtml += '<span class="star filled">★</span>';
                    } else {
                        ratingHtml += '<span class="star">☆</span>';
                    }
                }
                
                ratingHtml += '</div>';
            }
            
            // Create amenities HTML
            let amenitiesHtml = '';
            if (hotel.HotelFacilities && hotel.HotelFacilities.length) {
                amenitiesHtml = '<div class="hotel-amenities">' +
                    '<h3>Amenities</h3>' +
                    '<ul class="amenities-list">';
                
                $.each(hotel.HotelFacilities, function(index, facility) {
                    amenitiesHtml += '<li>' + facility + '</li>';
                });
                
                amenitiesHtml += '</ul>' +
                    '</div>';
            }
            
            // Create room types HTML
            let roomTypesHtml = '';
            if (hotel.HotelRooms && hotel.HotelRooms.length) {
                roomTypesHtml = '<div class="hotel-room-types">' +
                    '<h3>Room Types</h3>' +
                    '<ul class="room-types-list">';
                
                $.each(hotel.HotelRooms, function(index, room) {
                    roomTypesHtml += '<li>' + room.RoomName + '</li>';
                });
                
                roomTypesHtml += '</ul>' +
                    '</div>';
            }
            
            // Create hotel details HTML
            return '<div class="hotel-details-inner">' +
                '<div class="hotel-header">' +
                '<h2 class="hotel-name">' + hotel.HotelName + '</h2>' +
                ratingHtml +
                '<div class="hotel-location">' +
                '<i class="fas fa-map-marker-alt"></i> ' + (hotel.HotelAddress || '') +
                '</div>' +
                '</div>' +
                galleryHtml +
                '<div class="hotel-info">' +
                '<div class="hotel-description">' +
                '<h3>Description</h3>' +
                '<p>' + (hotel.HotelDescription || 'No description available') + '</p>' +
                '</div>' +
                amenitiesHtml +
                roomTypesHtml +
                '<div class="hotel-map">' +
                '<h3>Map</h3>' +
                '<div id="hotel-map-canvas" class="map-canvas" ' +
                'data-lat="' + (hotel.Latitude || 0) + '" ' +
                'data-lng="' + (hotel.Longitude || 0) + '" ' +
                'data-title="' + hotel.HotelName + '">' +
                '</div>' +
                '</div>' +
                '</div>' +
                '</div>';
        },
        
        /**
         * Show loading indicator
         * 
         * @param {string} message Loading message
         */
        showLoading: function(message) {
            // Remove existing loading overlay
            this.hideLoading();
            
            // Create loading overlay
            const loadingHtml = '<div id="tbo-loading-overlay">' +
                '<div class="loading-content">' +
                '<div class="spinner"></div>' +
                '<div class="loading-message">' + (message || 'Loading...') + '</div>' +
                '</div>' +
                '</div>';
            
            // Append to body
            $('body').append(loadingHtml);
            
            // Show loading overlay with fade in
            $('#tbo-loading-overlay').fadeIn(200);
        },
        
        /**
         * Hide loading indicator
         */
        hideLoading: function() {
            // Find loading overlay
            const $overlay = $('#tbo-loading-overlay');
            
            if ($overlay.length) {
                // Fade out and remove
                $overlay.fadeOut(200, function() {
                    $overlay.remove();
                });
            }
        },
        
        /**
         * Show error message
         * 
         * @param {string} title Error title
         * @param {string} message Error message
         */
        showError: function(title, message) {
            // Remove existing error overlay
            this.hideError();
            
            // Create error overlay
            const errorHtml = '<div id="tbo-error-overlay">' +
                '<div class="error-content">' +
                '<div class="error-header">' +
                '<h3>' + (title || 'Error') + '</h3>' +
                '<button class="close-error">&times;</button>' +
                '</div>' +
                '<div class="error-message">' + (message || 'An error occurred') + '</div>' +
                '<div class="error-actions">' +
                '<button class="close-error-btn">Close</button>' +
                '</div>' +
                '</div>' +
                '</div>';
            
            // Append to body
            $('body').append(errorHtml);
            
            // Show error overlay with fade in
            $('#tbo-error-overlay').fadeIn(200);
            
            // Bind close button events
            $('.close-error, .close-error-btn').on('click', function() {
                TBOEnhanced.hideError();
            });
        },
        
        /**
         * Hide error message
         */
        hideError: function() {
            // Find error overlay
            const $overlay = $('#tbo-error-overlay');
            
            if ($overlay.length) {
                // Fade out and remove
                $overlay.fadeOut(200, function() {
                    $overlay.remove();
                });
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        TBOEnhanced.init();
    });
    
})(jQuery);