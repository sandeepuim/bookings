/**
 * TBO Hotels Optimization Script
 * 
 * This script improves hotel search and display performance:
 * 1. Progressive loading of hotel results
 * 2. Error handling and recovery
 * 3. UI performance improvements
 * 4. Loading indicators and user feedback
 */

(function($) {
    'use strict';
    
    // Configuration
    var config = {
        batchSize: 10,        // Number of hotels to render at once
        loadingDelay: 100,    // Milliseconds between batches
        scrollThreshold: 300, // Pixels from bottom to trigger loading
        maxHotels: 50         // Maximum hotels to load initially
    };
    
    // State tracking
    var state = {
        isLoading: false,
        offset: 0,
        hasMore: true,
        hotels: [],
        renderedCount: 0,
        searchParams: null
    };
    
    /**
     * Initialize optimization features
     */
    function init() {
        console.log('TBO Hotels: Optimization active');
        
        // Attach scroll handler for infinite scroll
        $(window).on('scroll', handleScroll);
        
        // Intercept search form submission
        $('#hotel-search-form').on('submit', function(e) {
            // Don't intercept if it's already using our optimized code
            if (!$(this).hasClass('tbo-optimized')) {
                e.preventDefault();
                
                // Show loading indicator
                showLoading();
                
                // Get search parameters
                var formData = $(this).serialize();
                
                // Store search parameters for pagination
                state.searchParams = formData;
                state.offset = 0;
                state.hasMore = true;
                state.hotels = [];
                state.renderedCount = 0;
                
                // Perform search with our optimized code
                loadHotels(formData, function(response) {
                    displayHotels(response);
                });
            }
        });
        
        // Check if we're on a results page and should initialize results
        if ($('#hotel-results-container').length > 0 && $('#hotel-results-container').data('city-id')) {
            initializeExistingResults();
        }
    }
    
    /**
     * Initialize from existing results page
     */
    function initializeExistingResults() {
        var $container = $('#hotel-results-container');
        
        // Get initial parameters from data attributes
        state.searchParams = {
            city_id: $container.data('city-id'),
            check_in: $container.data('check-in'),
            check_out: $container.data('check-out'),
            rooms: $container.data('rooms') || JSON.stringify([{adults: 2, children: 0}])
        };
        
        // Set up initial state
        state.offset = $container.find('.hotel-item').length;
        state.hasMore = $container.data('has-more') === true;
        
        // Add load more button if needed
        if (state.hasMore) {
            addLoadMoreButton($container);
        }
    }
    
    /**
     * Handle scroll event for infinite loading
     */
    function handleScroll() {
        if (state.isLoading || !state.hasMore) return;
        
        var scrollHeight = $(document).height();
        var scrollPosition = $(window).height() + $(window).scrollTop();
        
        // If near bottom of page, load more
        if ((scrollHeight - scrollPosition) < config.scrollThreshold) {
            loadMore();
        }
    }
    
    /**
     * Add load more button
     */
    function addLoadMoreButton($container) {
        // Remove any existing button
        $('.load-more-hotels').remove();
        
        // Add button at end of container
        $container.append(
            '<div class="load-more-container text-center mt-4 mb-4">' +
            '<button class="load-more-hotels btn btn-primary">Load More Hotels</button>' +
            '</div>'
        );
        
        // Attach click handler
        $('.load-more-hotels').on('click', function() {
            $(this).text('Loading...').prop('disabled', true);
            loadMore();
        });
    }
    
    /**
     * Show loading indicator
     */
    function showLoading() {
        // Create loading overlay if it doesn't exist
        if ($('#tbo-loading-overlay').length === 0) {
            $('body').append(
                '<div id="tbo-loading-overlay">' +
                '<div class="tbo-loading-spinner"></div>' +
                '<div class="tbo-loading-text">Loading Hotels...</div>' +
                '</div>'
            );
        }
        
        // Show the loading overlay
        $('#tbo-loading-overlay').show();
    }
    
    /**
     * Hide loading indicator
     */
    function hideLoading() {
        $('#tbo-loading-overlay').hide();
    }
    
    /**
     * Load hotels with AJAX
     */
    function loadHotels(params, callback) {
        state.isLoading = true;
        showLoading();
        
        // Add offset and limit to parameters
        var requestData = typeof params === 'string' ? 
            params + '&offset=' + state.offset + '&limit=' + config.maxHotels :
            $.extend({}, params, { offset: state.offset, limit: config.maxHotels });
            
        // Add nonce if not already present
        if (typeof requestData === 'object' && !requestData.nonce && typeof tboData !== 'undefined') {
            requestData.nonce = tboData.nonce;
        } else if (typeof requestData === 'string' && !requestData.includes('nonce=') && typeof tboData !== 'undefined') {
            requestData += '&nonce=' + tboData.nonce;
        }
        
        // Make AJAX request
        $.ajax({
            url: typeof tboData !== 'undefined' ? tboData.ajaxUrl : ajaxurl,
            type: 'POST',
            data: $.extend({
                action: 'tbo_load_more_hotels'
            }, requestData),
            success: function(response) {
                state.isLoading = false;
                hideLoading();
                
                if (response.success) {
                    // Update state
                    state.offset += response.hotels.length;
                    state.hasMore = response.has_more;
                    state.hotels = state.hotels.concat(response.hotels);
                    
                    // Call callback with response
                    if (typeof callback === 'function') {
                        callback(response);
                    }
                } else {
                    console.error('Error loading hotels:', response.error);
                    showErrorMessage('Could not load hotels. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                state.isLoading = false;
                hideLoading();
                console.error('AJAX error:', status, error);
                showErrorMessage('Error communicating with server. Please try again.');
            }
        });
    }
    
    /**
     * Load more hotels (for pagination)
     */
    function loadMore() {
        if (!state.hasMore || state.isLoading) return;
        
        loadHotels(state.searchParams, function(response) {
            appendHotels(response);
        });
    }
    
    /**
     * Display hotels with progressive rendering
     */
    function displayHotels(response) {
        var $container = $('#hotel-results-container');
        
        // Clear existing results
        $container.empty();
        
        // Add hotel count and summary
        $container.append(
            '<div class="hotel-results-summary">' +
            '<h3>' + response.total + ' Hotels Found</h3>' +
            '<p>Showing hotels sorted by best value.</p>' +
            '</div>'
        );
        
        // Store the response hotels
        state.hotels = response.hotels;
        state.renderedCount = 0;
        
        // Render hotels progressively
        renderNextBatch();
        
        // Add load more button if there are more results
        if (response.has_more) {
            addLoadMoreButton($container);
        }
    }
    
    /**
     * Append more hotels to the existing results
     */
    function appendHotels(response) {
        // Store the new hotels
        state.hotels = state.hotels.concat(response.hotels);
        
        // Render the new hotels
        renderNextBatch();
        
        // Update load more button
        if (response.has_more) {
            $('.load-more-hotels').text('Load More Hotels').prop('disabled', false);
        } else {
            $('.load-more-hotels').remove();
            $('#hotel-results-container').append('<p class="text-center">No more hotels to load.</p>');
        }
    }
    
    /**
     * Render the next batch of hotels
     */
    function renderNextBatch() {
        var $container = $('#hotel-results-container');
        var startIdx = state.renderedCount;
        var endIdx = Math.min(startIdx + config.batchSize, state.hotels.length);
        
        // Render this batch
        for (var i = startIdx; i < endIdx; i++) {
            $container.append(createHotelCard(state.hotels[i]));
        }
        
        // Update rendered count
        state.renderedCount = endIdx;
        
        // If more to render, schedule next batch
        if (state.renderedCount < state.hotels.length) {
            setTimeout(renderNextBatch, config.loadingDelay);
        }
    }
    
    /**
     * Create a hotel card HTML
     */
    function createHotelCard(hotel) {
        var html = '<div class="hotel-item card mb-4">' +
            '<div class="row no-gutters">';
            
        // Hotel image
        html += '<div class="col-md-4">';
        if (hotel.HotelPicture && hotel.HotelPicture.length > 0) {
            html += '<img src="' + hotel.HotelPicture + '" class="card-img" alt="' + hotel.HotelName + '">';
        } else {
            html += '<img src="/wp-content/themes/tbo-hotels/assets/images/no-image.jpg" class="card-img" alt="No Image Available">';
        }
        html += '</div>';
        
        // Hotel details
        html += '<div class="col-md-8">' +
                '<div class="card-body">' +
                '<h5 class="card-title">' + hotel.HotelName + '</h5>';
        
        // Star rating
        if (hotel.StarRating) {
            html += '<div class="hotel-stars">';
            for (var i = 0; i < hotel.StarRating; i++) {
                html += '<i class="fa fa-star"></i>';
            }
            html += '</div>';
        }
        
        // Location
        html += '<p class="card-text"><i class="fa fa-map-marker"></i> ' + 
                (hotel.Address || 'Location information not available') + '</p>';
        
        // Price
        if (hotel.Price) {
            html += '<div class="hotel-price">' +
                    '<span class="price-label">Price from</span>' +
                    '<span class="price-amount">' + hotel.Price.CurrencyCode + ' ' + 
                    hotel.Price.OfferedPrice.toFixed(2) + '</span>' +
                    '<span class="price-nights">for ' + hotel.Price.Nights + ' night(s)</span>' +
                    '</div>';
        }
        
        // Button
        html += '<a href="/hotel-details/?hotel_code=' + hotel.HotelCode + '" ' + 
                'class="btn btn-primary hotel-details-btn">View Details</a>';
        
        html += '</div></div></div></div>';
        
        return html;
    }
    
    /**
     * Show error message
     */
    function showErrorMessage(message) {
        // Create error message container if it doesn't exist
        if ($('#tbo-error-message').length === 0) {
            $('body').append('<div id="tbo-error-message"></div>');
        }
        
        // Show error message
        $('#tbo-error-message').html('<div class="alert alert-danger">' + message + '</div>')
            .fadeIn()
            .delay(5000)
            .fadeOut();
    }
    
    // Initialize when document is ready
    $(document).ready(init);
    
})(jQuery);