jQuery(document).ready(function($) {
    console.log('Hotel search script loaded');
    
    // Debug - check if AJAX object exists
    if (typeof hotel_search_ajax === 'undefined') {
        console.error('hotel_search_ajax is not defined! Check if script is properly localized.');
        return;
    }
    
    console.log('AJAX URL:', hotel_search_ajax.ajax_url);
    console.log('Nonce:', hotel_search_ajax.nonce);
    
    // Handle country change - load cities
    $('#country').on('change', function() {
        var countryCode = $(this).val();
        var citySelect = $('#city_code');
        
        console.log('Country changed to:', countryCode);
        console.log('City select element found:', citySelect.length);
        
        if (countryCode) {
            // Show loading state
            citySelect.html('<option value="">Loading cities...</option>');
            citySelect.prop('disabled', true);
            
            console.log('Set loading state, dropdown disabled');
            
            $.ajax({
                url: hotel_search_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cities',
                    country_code: countryCode,
                    nonce: hotel_search_ajax.nonce
                },
                success: function(response) {
                    console.log('Cities response:', response);
                    console.log('Response type:', typeof response);
                    console.log('Response keys:', Object.keys(response || {}));
                    
                    // Clear the dropdown first
                    citySelect.empty();
                    citySelect.append('<option value="">Select City</option>');
                    
                    console.log('Cleared dropdown and added default option');
                    
                    // Check different possible response formats from TBO API
                    var cities = null;
                    
                    console.log('Raw response object:', response);
                    
                    // Try to find cities in various response formats
                    if (response) {
                        // Method 1: Direct Cities array
                        if (response.Cities && Array.isArray(response.Cities)) {
                            cities = response.Cities;
                            console.log('Found cities in response.Cities (array)');
                        }
                        // Method 2: Cities as object values
                        else if (response.Cities && typeof response.Cities === 'object') {
                            cities = Object.values(response.Cities);
                            console.log('Found cities in response.Cities (object converted to array)');
                        }
                        // Method 3: CityList array
                        else if (response.CityList && Array.isArray(response.CityList)) {
                            cities = response.CityList;
                            console.log('Found cities in response.CityList (array)');
                        }
                        // Method 4: CityList as object
                        else if (response.CityList && typeof response.CityList === 'object') {
                            cities = Object.values(response.CityList);
                            console.log('Found cities in response.CityList (object converted to array)');
                        }
                        // Method 5: Check if response itself is an array of cities
                        else if (Array.isArray(response)) {
                            cities = response;
                            console.log('Response itself is an array of cities');
                        }
                        // Method 6: Look for any property that looks like cities
                        else {
                            for (var key in response) {
                                if (response.hasOwnProperty(key)) {
                                    var value = response[key];
                                    if (Array.isArray(value) && value.length > 0) {
                                        // Check if first item looks like a city object
                                        var firstItem = value[0];
                                        if (firstItem && (firstItem.CityCode || firstItem.cityCode || firstItem.CityName || firstItem.cityName)) {
                                            cities = value;
                                            console.log('Found cities in response.' + key + ' (detected as city array)');
                                            break;
                                        }
                                    }
                                    else if (typeof value === 'object' && value !== null) {
                                        var objectValues = Object.values(value);
                                        if (objectValues.length > 0) {
                                            var firstItem = objectValues[0];
                                            if (firstItem && (firstItem.CityCode || firstItem.cityCode || firstItem.CityName || firstItem.cityName)) {
                                                cities = objectValues;
                                                console.log('Found cities in response.' + key + ' (object converted to array)');
                                                break;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                    
                    console.log('Extracted cities:', cities);
                    console.log('Cities array length:', cities ? cities.length : 0);
                    
                    if (cities && cities.length > 0) {
                        var addedCount = 0;
                        console.log('Sample city object:', cities[0]);
                        
                        $.each(cities, function(index, city) {
                            // Handle different city object formats with more variations
                            var cityCode = city.CityCode || city.cityCode || city.Id || city.id || 
                                         city.CityId || city.cityId || city.CODE || city.code;
                            var cityName = city.CityName || city.cityName || city.Name || city.name || 
                                         city.City || city.city || city.CITY_NAME || city.CityDisplayName;
                            
                            // Debug first few cities
                            if (index < 3) {
                                console.log('Processing city ' + index + ':', city);
                                console.log('Extracted - Code:', cityCode, 'Name:', cityName);
                            }
                            
                            if (cityCode && cityName) {
                                // Escape HTML to prevent issues
                                var safeCode = $('<div>').text(cityCode).html();
                                var safeName = $('<div>').text(cityName).html();
                                citySelect.append('<option value="' + safeCode + '">' + safeName + '</option>');
                                addedCount++;
                            } else if (index < 10) {
                                console.log('Skipped city ' + index + ' - missing code or name:', city);
                            }
                        });
                        
                        console.log('Added', addedCount, 'cities to dropdown');
                        
                        if (addedCount > 0) {
                            // Enable the dropdown after adding cities
                            citySelect.prop('disabled', false);
                            console.log('Enabled city dropdown');
                            
                            // Force refresh the dropdown (for some browsers)
                            citySelect.trigger('refresh');
                            
                            // Double-check dropdown contents
                            console.log('Final dropdown options count:', citySelect.find('option').length);
                            console.log('Dropdown HTML sample:', citySelect.html().substring(0, 200) + '...');
                        } else {
                            console.log('No valid cities could be processed');
                            citySelect.html('<option value="">No valid cities found</option>');
                            citySelect.prop('disabled', false);
                        }
                        
                    } else if (response && response.ResponseStatus && response.ResponseStatus.ErrorCode) {
                        // Handle TBO API error response
                        console.error('TBO API Error:', response.ResponseStatus.ErrorMessage);
                        citySelect.html('<option value="">API Error: ' + response.ResponseStatus.ErrorMessage + '</option>');
                        citySelect.prop('disabled', false);
                    } else {
                        citySelect.html('<option value="">No cities found</option>');
                        citySelect.prop('disabled', false);
                        console.log('No cities found in response. Full response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    citySelect.html('<option value="">Error loading cities</option>');
                    citySelect.prop('disabled', false);
                }
            });
        } else {
            // Reset city dropdown when no country is selected
            citySelect.html('<option value="">Select City</option>');
            citySelect.prop('disabled', true);
        }
    });
    
    // Handle form submission with multiple prevention methods
    $(document).on('submit', '.hotel-search-form', function(e) {
        e.preventDefault();
        e.stopPropagation();
        console.log('Form submit event captured and prevented');
        performHotelSearch();
        return false;
    });
    
    // Handle button click as backup
    $(document).on('click', '.search-btn', function(e) {
        e.preventDefault();
        console.log('Search button clicked');
        performHotelSearch();
        return false;
    });
    
    // Main search function
    function performHotelSearch() {
        console.log('performHotelSearch called');
        
        var formData = {
            action: 'search_hotels',
            city_code: $('#city_code').val(),
            check_in: $('#check_in').val(),
            check_out: $('#check_out').val(),
            rooms: $('#rooms').val(),
            adults: $('#adults').val(),
            children: $('#children').val(),
            nonce: hotel_search_ajax.nonce
        };
        
        console.log('Form data collected:', formData);
        
        // Validate form
        if (!formData.city_code) {
            alert('Please select a city');
            return false;
        }
        
        if (!formData.check_in || !formData.check_out) {
            alert('Please select check-in and check-out dates');
            return false;
        }
        
        // Check if dates are valid
        var checkInDate = new Date(formData.check_in);
        var checkOutDate = new Date(formData.check_out);
        var today = new Date();
        
        if (checkInDate < today) {
            alert('Check-in date cannot be in the past');
            return false;
        }
        
        if (checkOutDate <= checkInDate) {
            alert('Check-out date must be after check-in date');
            return false;
        }
        
        console.log('Form validation passed, starting search...');
        
        // Show loading
        var searchBtn = $('.search-btn');
        var originalText = searchBtn.text();
        searchBtn.text('Searching...').prop('disabled', true);
        
        // Hide any existing results
        $('#search-results').hide();
        
        console.log('Making AJAX request to:', hotel_search_ajax.ajax_url);
        
        $.ajax({
            url: hotel_search_ajax.ajax_url,
            type: 'POST',
            data: formData,
            timeout: 60000, // 60 second timeout
            success: function(response) {
                console.log('Search response received:', response);
                searchBtn.text(originalText).prop('disabled', false);
                
                if (response.success === false) {
                    console.error('Search failed:', response.data);
                    alert('Error: ' + response.data);
                    return;
                }
                
                // Check for TBO API error
                if (response && response.ResponseStatus && response.ResponseStatus.ErrorCode !== "0") {
                    console.error('TBO API Error:', response.ResponseStatus.ErrorMessage);
                    alert('API Error: ' + response.ResponseStatus.ErrorMessage);
                    return;
                }
                
                if (response && response.HotelResult && response.HotelResult.length > 0) {
                    console.log('Found', response.HotelResult.length, 'hotels');
                    displaySearchResults(response.HotelResult);
                } else {
                    console.log('No hotels found in response');
                    $('#search-results').html('<div class="no-results">No hotels found for your search criteria. Please try different dates or location.</div>').show();
                }
            },
            error: function(xhr, status, error) {
                console.error('Search AJAX Error:', error);
                console.error('Status:', status);
                console.error('Response:', xhr.responseText);
                searchBtn.text(originalText).prop('disabled', false);
                alert('An error occurred while searching. Please try again.');
            }
        });
    }
    
    // Display search results
    function displaySearchResults(hotels) {
        var resultsHtml = '<div class="search-results-container"><h3>Available Hotels (' + hotels.length + ' found)</h3><div class="hotels-grid">';
        
        $.each(hotels, function(index, hotel) {
            var minPrice = 'Price on request';
            if (hotel.RoomDetails && hotel.RoomDetails.length > 0) {
                var prices = hotel.RoomDetails.map(function(room) {
                    return parseFloat(room.Price || 0);
                });
                var min = Math.min.apply(Math, prices);
                if (min > 0) {
                    minPrice = '$' + min.toFixed(2);
                }
            }
            
            var starRating = '';
            if (hotel.StarRating) {
                for (var i = 0; i < parseInt(hotel.StarRating); i++) {
                    starRating += '★';
                }
            } else {
                starRating = 'Not Rated';
            }
            
            resultsHtml += '<div class="hotel-card">' +
                '<div class="hotel-info">' +
                '<h4 class="hotel-name">' + (hotel.HotelName || 'Hotel Name Not Available') + '</h4>' +
                '<p class="hotel-location">' + (hotel.HotelLocation || '') + '</p>' +
                '<div class="hotel-rating">' + starRating + '</div>' +
                '</div>' +
                '<div class="hotel-price">' +
                '<span class="price">' + minPrice + '</span>' +
                '<span class="per-night">per night</span>' +
                '</div>' +
                '<div class="hotel-actions">' +
                '<button class="view-details-btn" data-hotel-code="' + hotel.HotelCode + '">View Details</button>' +
                '</div>' +
                '</div>';
        });
        
        resultsHtml += '</div></div>';
        
        if ($('#search-results').length === 0) {
            $('.hotel-search-section').after('<div id="search-results"></div>');
        }
        
        $('#search-results').html(resultsHtml).show();
        
        // Scroll to results
        $('html, body').animate({
            scrollTop: $('#search-results').offset().top - 50
        }, 500);
    }
    
    // Set initial state - city dropdown should be disabled until country is selected
    $('#city_code').prop('disabled', true);
    
    // Test dropdown functionality on page load
    console.log('Testing dropdown elements:');
    console.log('Country dropdown found:', $('#country').length);
    console.log('City dropdown found:', $('#city_code').length);
    
    // Add a test button for manual testing (temporary)
    if ($('#test-cities-btn').length === 0) {
        $('.search-btn').after('<button type="button" id="test-cities-btn" style="margin-left: 10px;">Test Cities</button>');
        
        $('#test-cities-btn').on('click', function() {
            console.log('Testing city dropdown population...');
            var citySelect = $('#city_code');
            citySelect.empty();
            citySelect.append('<option value="">Test City 1</option>');
            citySelect.append('<option value="test2">Test City 2</option>');
            citySelect.prop('disabled', false);
            console.log('Test cities added');
        });
    }
});
