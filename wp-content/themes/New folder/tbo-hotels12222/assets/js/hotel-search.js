/**
 * TBO Hotels Search JavaScript
 * Handles the hotel search form functionality and AJAX requests
 */

// Use IIFE to prevent $ conflicts
(function($, window, document) {
    // Ensure $ works in this scope
    'use strict';
    
    // Global error handler for uncaught errors
    window.addEventListener('error', function(event) {
        console.error('[TBO Error Caught]:', event.error);
        
        // If the error contains 'tbo_hotels_params is not defined'
        if (event.error && event.error.message && event.error.message.indexOf('tbo_hotels_params is not defined') !== -1) {
            // Show friendly message to users
            if (typeof $ === 'function') {
                $('body').append('<div class="tbo-error-global">Error loading hotel search. Please refresh the page or contact support.</div>');
            }
            // Log additional debug info for developers
            console.error('WordPress script localization failed. Make sure wp_localize_script is properly set up for tbo-hotels-search.');
        }
        
        // Don't suppress the error
        return false;
    });
    
    // Check if tbo_hotels_params is defined
    if (typeof tbo_hotels_params === 'undefined') {
        console.error('[TBO Hotels] tbo_hotels_params is not defined. Script localization may have failed.');
        tbo_hotels_params = {
            ajax_url: '/wp-admin/admin-ajax.php',
            nonce: '',
            placeholder_image: '/wp-content/themes/tbo-hotels/assets/img/placeholder.jpg'
        };
    }
    
    // Error handling utility
    var errorHandler = {
        logError: function(message, error) {
            console.error('[TBO Hotels]', message, error);
        },
        showUserError: function(message) {
            // Check if the notification element exists, create if not
            var $notification = $('#tbo-notification');
            if (!$notification.length) {
                $('body').append('<div id="tbo-notification" class="tbo-notification"></div>');
                $notification = $('#tbo-notification');
            }
            
            // Add error message
            $notification.html('<div class="tbo-error-message"><i class="fa fa-exclamation-circle"></i> ' + message + '</div>')
                .addClass('show');
                
            // Hide after 5 seconds
            setTimeout(function() {
                $notification.removeClass('show');
            }, 5000);
        },
        handleAjaxError: function(xhr, status, error, userMessage) {
            var defaultMessage = 'An error occurred while processing your request. Please try again.';
            var displayMessage = userMessage || defaultMessage;
            
            this.logError('AJAX Error: ' + status, {
                error: error, 
                status: status,
                response: xhr.responseText
            });
            
            this.showUserError(displayMessage);
        }
    };
    
    // Run once the document is ready
    $(document).ready(function() {
        var searchState = {
            countries: [],
            cities: [],
            searchResults: []
        };
    
        // Cache DOM elements
        var $form = $('#hotel-search-form');
        var $countrySelect = $('#country_code');
        var $citySelect = $('#city_code');
        var $resultsContainer = $('#search-results');
        var $searchButton = $('.search-button-inline');
        
        // Initialize single-row form functionality
        initializeSingleRowForm();
        
        // Load countries on page load
        loadCountries();
        
        // Set default to India
        setTimeout(function() {
            $countrySelect.val('IN').trigger('change');
        }, 1000);
        
        // Country selector change
        $countrySelect.on('change', function() {
            var countryCode = $(this).val();
            if (countryCode) {
                loadCities(countryCode);
            } else {
                $citySelect.html('<option value="">Select a city</option>');
            }
        });
        
        // Form submission handler
        $form.on('submit', function(e) {
            e.preventDefault();
            performSearch();
        });
        
        /**
         * Load countries from the API
         */
        function loadCountries() {
            try {
                $.ajax({
                    url: tbo_hotels_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'tbo_hotels_get_countries',
                        nonce: tbo_hotels_params.nonce
                    },
                success: function(response) {
                    console.log('Countries response:', response);
                    
                    if (response.success && response.data) {
                        populateCountries(response.data);
                        searchState.countries = response.data;
                    } else {
                        errorHandler.showUserError('Failed to load countries. Please try again.');
                    }
                },
                error: function(xhr, status, error) {
                    errorHandler.handleAjaxError(xhr, status, error, 'Failed to load countries. Please try again.');
                },
                complete: function() {
                    // Additional completion handling if needed
                }
            });
            } catch (err) {
                errorHandler.logError('Error in loadCountries function', err);
                errorHandler.showUserError('Failed to initialize hotel search. Please refresh the page or contact support.');
            }
        }
        
        /**
         * Load cities for a selected country
         */
        function loadCities(countryCode) {
            try {
                // Show loading indicator for city dropdown
                $citySelect.html('<option value="">Loading cities...</option>');
                
                $.ajax({
                url: tbo_hotels_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbo_hotels_get_cities',
                    nonce: tbo_hotels_params.nonce,
                    country_code: countryCode
                },
                success: function(response) {
                    console.log('Cities response:', response);
                    
                    if (response.success && response.data) {
                        populateCities(response.data);
                        searchState.cities = response.data;
                    } else {
                        errorHandler.showUserError('Failed to load cities. Please try again.');
                        $citySelect.html('<option value="">Select a city</option>');
                    }
                },
                error: function(xhr, status, error) {
                    errorHandler.handleAjaxError(xhr, status, error, 'Failed to load cities. Please try again.');
                    $citySelect.html('<option value="">Select a city</option>');
                }
            });
            } catch (err) {
                errorHandler.logError('Error in loadCities function', err);
                errorHandler.showUserError('Failed to load cities. Please try again.');
                $citySelect.html('<option value="">Select a city</option>');
            }
        }
        
        /**
         * Populate country dropdown
         */
        function populateCountries(countries) {
            var options = '<option value="">Select a country</option>';
            
            countries.forEach(function(country) {
                options += '<option value="' + country.code + '">' + country.name + '</option>';
            });
            
            $countrySelect.html(options);
        }
        
        /**
         * Populate cities dropdown
         */
        function populateCities(cities) {
            var options = '<option value="">Select a city</option>';
            
            cities.forEach(function(city) {
                options += '<option value="' + city.id + '">' + city.name + '</option>';
            });
            
            $citySelect.html(options);
        }
        
        /**
         * Perform hotel search
         */
        function performSearch() {
            try {
                // Show loading state
                $searchButton.addClass('loading').attr('disabled', true);
                $searchButton.find('.search-text').text('Searching...');
                $resultsContainer.html('<div class="loading-indicator"><i class="fa fa-spinner fa-spin"></i> Searching for hotels...</div>');
                
                // Get form data
                var formData = $form.serialize();
                
                $.ajax({
                url: tbo_hotels_params.ajax_url,
                type: 'POST',
                data: formData + '&action=tbo_hotels_search_hotels&nonce=' + tbo_hotels_params.nonce,
                success: function(response) {
                    console.log('Search response:', response);
                    
                    if (response.success && response.data) {
                        displaySearchResults(response.data);
                        searchState.searchResults = response.data;
                    } else {
                        var errorMessage = response.data && response.data.message ? 
                            response.data.message : 'No hotels found matching your criteria. Please try different search parameters.';
                        $resultsContainer.html('<div class="no-results">' + errorMessage + '</div>');
                    }
                },
                error: function(xhr, status, error) {
                    errorHandler.handleAjaxError(xhr, status, error, 'Hotel search failed. Please try again.');
                    $resultsContainer.html('<div class="error-message">Search failed. Please try again.</div>');
                },
                complete: function() {
                    // Reset button state
                    $searchButton.removeClass('loading').attr('disabled', false);
                    $searchButton.find('.search-text').text('Search');
                }
            });
            } catch (err) {
                errorHandler.logError('Error in performSearch function', err);
                errorHandler.showUserError('Hotel search failed. Please try again.');
                $resultsContainer.html('<div class="error-message">Search failed. Please try again.</div>');
                $searchButton.removeClass('loading').attr('disabled', false);
                $searchButton.find('.search-text').text('Search');
            }
        }
        
        /**
         * Display search results
         */
        function displaySearchResults(results) {
            try {
            if (!results.hotels || results.hotels.length === 0) {
                $resultsContainer.html('<div class="no-results">No hotels found matching your criteria. Please try different search parameters.</div>');
                return;
            }
            
            var html = '';
            
            // Add search summary
            html += '<div class="search-summary">';
            html += '<h3>' + results.hotels.length + ' hotels found in ' + results.cityName + '</h3>';
            html += '</div>';
            
            // Add filter controls
            html += '<div class="result-controls">';
            html += '<div class="filters">';
            html += '<label>Sort by: </label>';
            html += '<select class="sort-select">';
            html += '<option value="price-asc">Price: Low to High</option>';
            html += '<option value="price-desc">Price: High to Low</option>';
            html += '<option value="rating-desc">Rating: High to Low</option>';
            html += '<option value="name-asc">Name: A to Z</option>';
            html += '</select>';
            html += '</div>';
            html += '</div>';
            
            // Add hotel cards
            html += '<div class="hotel-results-grid">';
            
            results.hotels.forEach(function(hotel) {
                var hotelPrice = hotel.price || 'Price not available';
                var hotelRating = hotel.rating || 0;
                var ratingStars = '';
                
                for (var i = 0; i < 5; i++) {
                    if (i < hotelRating) {
                        ratingStars += '<i class="fa fa-star"></i>';
                    } else {
                        ratingStars += '<i class="fa fa-star-o"></i>';
                    }
                }
                
                // Hotel card
                html += '<div class="yatra-hotel-card" data-hotel-code="' + hotel.code + '" data-price="' + hotel.price + '" data-rating="' + hotel.rating + '">';
                html += '<div class="hotel-image">';
                
                if (hotel.image) {
                    html += '<img src="' + hotel.image + '" alt="' + hotel.name + '">';
                } else {
                    html += '<img src="' + tbo_hotels_params.placeholder_image + '" alt="' + hotel.name + '">';
                }
                
                html += '</div>';
                html += '<div class="hotel-details">';
                html += '<h3 class="hotel-name">' + hotel.name + '</h3>';
                html += '<div class="hotel-rating">' + ratingStars + '</div>';
                html += '<div class="hotel-location"><i class="fa fa-map-marker"></i> ' + hotel.location + '</div>';
                
                if (hotel.amenities && hotel.amenities.length) {
                    html += '<div class="hotel-amenities">';
                    hotel.amenities.slice(0, 4).forEach(function(amenity) {
                        html += '<span class="amenity">' + amenity + '</span>';
                    });
                    html += '</div>';
                }
                
                html += '</div>';
                html += '<div class="hotel-price-box">';
                html += '<div class="price-wrapper">';
                html += '<span class="price-label">Starting from</span>';
                html += '<span class="price-value">' + hotelPrice + '</span>';
                html += '<span class="price-unit">per night</span>';
                html += '</div>';
                html += '<button class="choose-room-btn" data-hotel-code="' + hotel.code + '">Select Room</button>';
                html += '</div>';
                html += '</div>';
            });
            
            html += '</div>';
            
            $resultsContainer.html(html);
            
            // Scroll to results
            $('html, body').animate({
                scrollTop: $resultsContainer.offset().top - 50
            }, 500);
            
            // Initialize sorting
            initializeSorting();
            } catch (err) {
                errorHandler.logError('Error displaying search results', err);
                $resultsContainer.html('<div class="error-message">Error displaying search results. Please try again.</div>');
            }
        }
        
        /**
         * Initialize sorting functionality
         */
        function initializeSorting() {
            $('.sort-select').on('change', function() {
                var sortValue = $(this).val();
                var $hotels = $('.yatra-hotel-card');
                
                var sortedHotels = $hotels.toArray().sort(function(a, b) {
                    var $a = $(a);
                    var $b = $(b);
                    
                    switch (sortValue) {
                        case 'price-asc':
                            return parseFloat($a.data('price')) - parseFloat($b.data('price'));
                        case 'price-desc':
                            return parseFloat($b.data('price')) - parseFloat($a.data('price'));
                        case 'rating-desc':
                            return parseFloat($b.data('rating')) - parseFloat($a.data('rating'));
                        case 'name-asc':
                            return $a.find('.hotel-name').text().localeCompare($b.find('.hotel-name').text());
                        default:
                            return 0;
                    }
                });
                
                var $container = $('.hotel-results-grid');
                $container.empty();
                
                sortedHotels.forEach(function(hotel) {
                    $container.append(hotel);
                });
            });
        }
        
        /**
         * Initialize single-row form functionality
         */
        function initializeSingleRowForm() {
            // Date picker handlers - make date fields clickable
            $('.date-display').on('click', function() {
                var dateInput = $(this).siblings('input[type="date"]');
                if (dateInput.length) {
                    dateInput[0].showPicker();
                }
            });
            
            // Date change handlers
            $('#check_in, #check_out').on('change', function() {
                updateDateDisplay();
            });
            
            // City selector change
            $('#city_code').on('change', function() {
                var selectedCity = $(this).find('option:selected').text();
                console.log('City selected:', selectedCity);
            });
            
            // Initialize displays
            updateDateDisplay();
            updateGuestsDisplay();
        }
        
        /**
         * Update date display based on selected dates
         */
        function updateDateDisplay() {
            var checkInDate = new Date($('#check_in').val());
            var checkOutDate = new Date($('#check_out').val());
            
            if (!isNaN(checkInDate.getTime())) {
                var checkInDisplay = $('.date-group').first().find('.date-display');
                checkInDisplay.find('.date-num').text(checkInDate.getDate());
                checkInDisplay.find('.date-text').html(
                    getMonthShort(checkInDate) + "' " + checkInDate.getFullYear().toString().substr(-2) + 
                    "<br>" + getDayName(checkInDate)
                );
            }
            
            if (!isNaN(checkOutDate.getTime())) {
                var checkOutDisplay = $('.date-group').last().find('.date-display');
                checkOutDisplay.find('.date-num').text(checkOutDate.getDate());
                checkOutDisplay.find('.date-text').html(
                    getMonthShort(checkOutDate) + "' " + checkOutDate.getFullYear().toString().substr(-2) + 
                    "<br>" + getDayName(checkOutDate)
                );
            }
        }
        
        /**
         * Update guests display based on selected values
         */
        function updateGuestsDisplay() {
            var rooms = $('#rooms').val() || 1;
            var adults = $('#adults').val() || 2;
            var children = $('#children').val() || 0;
            
            var roomText = rooms + ' Room' + (rooms > 1 ? 's' : '');
            var guestTotal = parseInt(adults) + parseInt(children);
            var guestText = guestTotal + ' Guest' + (guestTotal > 1 ? 's' : '');
            
            $('.room-count').text(rooms);
            $('.guest-count').text(guestTotal);
            
            var detailText = adults + ' Adult' + (adults > 1 ? 's' : '');
            if (children > 0) {
                detailText += ', ' + children + ' Child' + (children > 1 ? 'ren' : '');
            }
            $('.guest-details').text(detailText);
        }
        
        /**
         * Get short month name
         */
        function getMonthShort(date) {
            var months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                         'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            return months[date.getMonth()];
        }
        
        /**
         * Get day name
         */
        function getDayName(date) {
            var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            return days[date.getDay()];
        }
    
        // Initialize default dates
        setDefaultDates();
        
        // Choose room button handler (delegate event)
        $(document).on('click', '.choose-room-btn', function() {
            try {
                var hotelCode = $(this).closest('.yatra-hotel-card').data('hotel-code');
                //  var hotelCode = $(this).data('hotel-code');
                console.log('Choose room clicked for hotel code:', hotelCode);
                
                // Get all form parameters using a safer method
                var $form = $('#hotel-search-form');
                var searchParams = new URLSearchParams();
                
                // Add form fields
                if ($form.length) {
                    var formData = $form.serializeArray();
                    formData.forEach(function(item) {
                        searchParams.append(item.name, item.value);
                    });
                }
                
                // Add hotel code
                searchParams.append('hotel_code', hotelCode);
                
                // Create a URL to the simple room selection page
                var roomsUrl = '/bookings/simple-room-selection.php?hotel_code=' + hotelCode;
            
                // Add all other search parameters
                searchParams.forEach(function(value, key) {
                    if (key !== 'hotel_code') { // Don't duplicate hotel_code
                        roomsUrl += '&' + key + '=' + value;
                    }
                });
                
                console.log('Redirecting to room selection URL:', roomsUrl);
                
                // Redirect to the room selection page
                window.location.href = roomsUrl;
            } catch (err) {
                errorHandler.logError('Error in choose room button handler', err);
                
                // Fallback to a simpler redirect with just the hotel code
                var fallbackUrl = '/bookings/simple-room-selection.php?hotel_code=' + hotelCode;
                
                // Try to add some basic parameters if we can get them
                var checkIn = $('#check_in').val();
                var checkOut = $('#check_out').val();
                var adults = $('#adults').val();
                var children = $('#children').val();
                var rooms = $('#rooms').val();
                
                if (checkIn) fallbackUrl += '&check_in=' + checkIn;
                if (checkOut) fallbackUrl += '&check_out=' + checkOut;
                if (adults) fallbackUrl += '&adults=' + adults;
                if (children) fallbackUrl += '&children=' + children;
                if (rooms) fallbackUrl += '&rooms=' + rooms;
                
                console.log('Redirecting to fallback room selection URL:', fallbackUrl);
                window.location.href = fallbackUrl;
            }
        });    }); // End of document ready
    
    /**
     * Set default dates (today and tomorrow)
     */
    function setDefaultDates() {
        var today = new Date();
        var tomorrow = new Date();
        tomorrow.setDate(today.getDate() + 1);
        
        var checkIn = document.getElementById('check_in');
        var checkOut = document.getElementById('check_out');
        
        if (checkIn && !checkIn.value) {
            checkIn.valueAsDate = today;
        }
        
        if (checkOut && !checkOut.value) {
            checkOut.valueAsDate = tomorrow;
        }
    }

})(jQuery, window, document);