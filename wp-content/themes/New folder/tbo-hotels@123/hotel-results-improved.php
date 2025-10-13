<?php
/**
 * Template Name: Hotel Results Improved
 * 
 * An optimized version of the hotel results page with improved performance:
 * 1. Progressive loading of hotel results
 * 2. Optimized API calls with caching
 * 3. Better error handling
 * 4. Improved UI feedback
 */

// Get search parameters from URL
$city_id = isset($_GET['city_id']) ? sanitize_text_field($_GET['city_id']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$child_ages = isset($_GET['child_ages']) ? explode(',', sanitize_text_field($_GET['child_ages'])) : array();

// Validate required parameters
$valid_search = !empty($city_id) && !empty($check_in) && !empty($check_out);

get_header();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1 class="page-title">Hotel Search Results</h1>
            
            <?php if (!$valid_search): ?>
                <div class="alert alert-warning">
                    <p>Invalid search parameters. Please try searching again.</p>
                    <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">Return to Search</a>
                </div>
            <?php else: ?>
                <!-- Search Summary -->
                <div class="search-summary card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Destination:</strong> 
                                <span id="destination-name">Loading...</span>
                            </div>
                            <div class="col-md-3">
                                <strong>Check-in:</strong> 
                                <?php echo date('d M Y', strtotime($check_in)); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Check-out:</strong> 
                                <?php echo date('d M Y', strtotime($check_out)); ?>
                            </div>
                            <div class="col-md-3">
                                <strong>Guests:</strong> 
                                <?php echo $adults; ?> Adults<?php echo $children > 0 ? ', ' . $children . ' Children' : ''; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter and Sort Options -->
                <div class="filters-container card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="star-rating-filter">Star Rating</label>
                                <select id="star-rating-filter" class="form-control">
                                    <option value="all">All Stars</option>
                                    <option value="5">5 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="2">2 Stars</option>
                                    <option value="1">1 Star</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="price-range-filter">Price Range</label>
                                <select id="price-range-filter" class="form-control">
                                    <option value="all">All Prices</option>
                                    <option value="budget">Budget</option>
                                    <option value="moderate">Moderate</option>
                                    <option value="luxury">Luxury</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sort-options">Sort By</label>
                                <select id="sort-options" class="form-control">
                                    <option value="price-asc">Price: Low to High</option>
                                    <option value="price-desc">Price: High to Low</option>
                                    <option value="star-desc">Star Rating</option>
                                    <option value="name-asc">Hotel Name</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Results Container -->
                <div id="hotel-results-container" 
                     data-city-id="<?php echo esc_attr($city_id); ?>"
                     data-check-in="<?php echo esc_attr($check_in); ?>"
                     data-check-out="<?php echo esc_attr($check_out); ?>"
                     data-rooms="<?php echo esc_attr(json_encode(array(array('adults' => $adults, 'children' => $children, 'child_ages' => $child_ages)))); ?>">
                    
                    <div class="loading-container text-center p-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-3">Searching for the best hotel deals...</p>
                    </div>
                </div>
                
                <!-- No Results Message (Hidden by default) -->
                <div id="no-results-message" class="alert alert-info" style="display: none;">
                    <p>No hotels found matching your search criteria. Please try different dates or destination.</p>
                    <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">New Search</a>
                </div>
                
                <!-- Error Message (Hidden by default) -->
                <div id="error-message" class="alert alert-danger" style="display: none;">
                    <p>There was an error processing your search. Please try again.</p>
                    <p id="error-details"></p>
                    <a href="<?php echo home_url('/'); ?>" class="btn btn-primary">Return to Search</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="tbo-loading-overlay" style="display: none;">
    <div class="tbo-loading-spinner"></div>
    <div class="tbo-loading-text">Loading Hotels...</div>
</div>

<!-- Hotel Details Modal -->
<div class="modal fade" id="hotel-details-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hotel Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="hotel-details-content">
                    <div class="text-center p-3">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Loading hotel details...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <a href="#" class="btn btn-primary" id="view-rooms-btn">View Rooms</a>
            </div>
        </div>
    </div>
</div>

<style>
/* Loading Overlay */
#tbo-loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 9999;
}

.tbo-loading-spinner {
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3498db;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

.tbo-loading-text {
    margin-top: 15px;
    font-size: 18px;
    color: #333;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Hotel Card Styles */
.hotel-item {
    transition: transform 0.2s, box-shadow 0.2s;
}

.hotel-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

.hotel-stars {
    color: #ffc107;
    margin-bottom: 10px;
}

.hotel-price {
    display: flex;
    flex-direction: column;
    margin: 15px 0;
}

.price-label {
    font-size: 14px;
    color: #666;
}

.price-amount {
    font-size: 22px;
    font-weight: bold;
    color: #28a745;
}

.price-nights {
    font-size: 14px;
    color: #666;
}

.hotel-details-btn {
    margin-top: 10px;
}

/* Filter animations */
.hotel-item.filtered-out {
    animation: fadeOut 0.5s forwards;
}

.hotel-item.filtered-in {
    animation: fadeIn 0.5s forwards;
}

@keyframes fadeOut {
    from { opacity: 1; }
    to { opacity: 0; height: 0; margin: 0; padding: 0; overflow: hidden; }
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Show loading overlay
    function showLoading() {
        $('#tbo-loading-overlay').show();
    }
    
    // Hide loading overlay
    function hideLoading() {
        $('#tbo-loading-overlay').hide();
    }
    
    // Show error message
    function showError(message, details = '') {
        $('#hotel-results-container').hide();
        $('#error-message').show();
        $('#error-details').text(details);
    }
    
    // Format price
    function formatPrice(price, currency = 'USD') {
        return currency + ' ' + parseFloat(price).toFixed(2);
    }
    
    // Get star rating HTML
    function getStarRating(stars) {
        let html = '<div class="hotel-stars">';
        for (let i = 0; i < stars; i++) {
            html += '<i class="fa fa-star"></i>';
        }
        html += '</div>';
        return html;
    }
    
    // Create hotel card
    function createHotelCard(hotel) {
        let html = '<div class="hotel-item card mb-4" data-hotel-code="' + hotel.HotelCode + '" ' +
                  'data-star-rating="' + hotel.StarRating + '" ' +
                  'data-price="' + (hotel.Price ? hotel.Price.OfferedPrice : 0) + '">';
        
        html += '<div class="row no-gutters">';
        
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
            html += getStarRating(hotel.StarRating);
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
        
        // Buttons
        html += '<div class="hotel-actions">' +
                '<button class="btn btn-outline-primary quick-view-btn mr-2" data-hotel-code="' + hotel.HotelCode + '">' +
                '<i class="fa fa-search"></i> Quick View</button>' +
                '<a href="/hotel-details/?hotel_code=' + hotel.HotelCode + '" class="btn btn-primary">View Details</a>' +
                '</div>';
        
        html += '</div></div></div></div>';
        
        return html;
    }
    
    // Load hotels
    function loadHotels() {
        showLoading();
        
        const cityId = $('#hotel-results-container').data('city-id');
        const checkIn = $('#hotel-results-container').data('check-in');
        const checkOut = $('#hotel-results-container').data('check-out');
        const rooms = $('#hotel-results-container').data('rooms');
        
        $.ajax({
            url: tboData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tbo_load_more_hotels',
                city_id: cityId,
                check_in: checkIn,
                check_out: checkOut,
                rooms: JSON.stringify(rooms),
                nonce: tboData.nonce
            },
            success: function(response) {
                hideLoading();
                
                if (response.success) {
                    // Resolve city name
                    if (response.city_name) {
                        $('#destination-name').text(response.city_name);
                    } else {
                        $('#destination-name').text('City ID: ' + cityId);
                    }
                    
                    // Display hotels
                    displayHotels(response);
                } else {
                    showError('Error loading hotels', response.error || 'Unknown error');
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                showError('Error communicating with server', status + ': ' + error);
            }
        });
    }
    
    // Display hotels with progressive rendering
    function displayHotels(response) {
        const $container = $('#hotel-results-container');
        $container.empty();
        
        // Add hotel count and summary
        $container.append(
            '<div class="hotel-results-summary mb-4">' +
            '<h3>' + response.total + ' Hotels Found</h3>' +
            '<p>Showing hotels sorted by best value.</p>' +
            '</div>'
        );
        
        if (response.hotels.length === 0) {
            $('#no-results-message').show();
            return;
        }
        
        // Render hotels
        response.hotels.forEach(function(hotel) {
            $container.append(createHotelCard(hotel));
        });
        
        // Add load more button if needed
        if (response.has_more) {
            $container.append(
                '<div class="load-more-container text-center mt-4 mb-4">' +
                '<button class="load-more-hotels btn btn-primary">Load More Hotels</button>' +
                '</div>'
            );
            
            // Attach click handler to load more button
            $('.load-more-hotels').on('click', function() {
                $(this).text('Loading...').prop('disabled', true);
                loadMoreHotels();
            });
        }
        
        // Initialize quick view buttons
        initQuickView();
    }
    
    // Load more hotels
    function loadMoreHotels() {
        const cityId = $('#hotel-results-container').data('city-id');
        const checkIn = $('#hotel-results-container').data('check-in');
        const checkOut = $('#hotel-results-container').data('check-out');
        const rooms = $('#hotel-results-container').data('rooms');
        const offset = $('.hotel-item').length;
        
        $.ajax({
            url: tboData.ajaxUrl,
            type: 'POST',
            data: {
                action: 'tbo_load_more_hotels',
                city_id: cityId,
                check_in: checkIn,
                check_out: checkOut,
                rooms: JSON.stringify(rooms),
                offset: offset,
                nonce: tboData.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Remove load more button
                    $('.load-more-container').remove();
                    
                    // Append hotels
                    response.hotels.forEach(function(hotel) {
                        $('#hotel-results-container').append(createHotelCard(hotel));
                    });
                    
                    // Add load more button if needed
                    if (response.has_more) {
                        $('#hotel-results-container').append(
                            '<div class="load-more-container text-center mt-4 mb-4">' +
                            '<button class="load-more-hotels btn btn-primary">Load More Hotels</button>' +
                            '</div>'
                        );
                        
                        // Attach click handler to load more button
                        $('.load-more-hotels').on('click', function() {
                            $(this).text('Loading...').prop('disabled', true);
                            loadMoreHotels();
                        });
                    }
                    
                    // Initialize quick view buttons for new hotels
                    initQuickView();
                } else {
                    console.error('Error loading more hotels:', response.error);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                $('.load-more-hotels').text('Load More Hotels').prop('disabled', false);
            }
        });
    }
    
    // Quick view functionality
    function initQuickView() {
        $('.quick-view-btn').off('click').on('click', function() {
            const hotelCode = $(this).data('hotel-code');
            
            // Show modal
            $('#hotel-details-modal').modal('show');
            
            // Set hotel details content to loading state
            $('.hotel-details-content').html(
                '<div class="text-center p-3">' +
                '<div class="spinner-border text-primary" role="status">' +
                '<span class="sr-only">Loading...</span>' +
                '</div>' +
                '<p class="mt-2">Loading hotel details...</p>' +
                '</div>'
            );
            
            // Set view rooms button href
            $('#view-rooms-btn').attr('href', '/hotel-details/?hotel_code=' + hotelCode);
            
            // Load hotel details
            $.ajax({
                url: tboData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'tbo_get_hotel_details',
                    hotel_code: hotelCode,
                    nonce: tboData.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayHotelDetails(response.hotel);
                    } else {
                        $('.hotel-details-content').html(
                            '<div class="alert alert-danger">' +
                            '<p>Error loading hotel details. Please try again.</p>' +
                            '</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    $('.hotel-details-content').html(
                        '<div class="alert alert-danger">' +
                        '<p>Error communicating with server. Please try again.</p>' +
                        '</div>'
                    );
                }
            });
        });
    }
    
    // Display hotel details in modal
    function displayHotelDetails(hotel) {
        let html = '<div class="hotel-details">';
        
        // Hotel image
        if (hotel.HotelPicture && hotel.HotelPicture.length > 0) {
            html += '<div class="hotel-image mb-3">' +
                    '<img src="' + hotel.HotelPicture + '" class="img-fluid" alt="' + hotel.HotelName + '">' +
                    '</div>';
        }
        
        // Hotel info
        html += '<div class="hotel-info">' +
                '<h4>' + hotel.HotelName + '</h4>';
        
        // Star rating
        if (hotel.StarRating) {
            html += getStarRating(hotel.StarRating);
        }
        
        // Address
        if (hotel.Address) {
            html += '<p><i class="fa fa-map-marker"></i> ' + hotel.Address + '</p>';
        }
        
        // Description
        if (hotel.Description) {
            html += '<div class="hotel-description mb-3">' +
                    '<h5>Description</h5>' +
                    '<p>' + hotel.Description + '</p>' +
                    '</div>';
        }
        
        // Amenities
        if (hotel.Facilities && hotel.Facilities.length > 0) {
            html += '<div class="hotel-amenities mb-3">' +
                    '<h5>Amenities</h5>' +
                    '<ul class="list-unstyled row">';
            
            hotel.Facilities.forEach(function(facility) {
                html += '<li class="col-md-4 mb-2"><i class="fa fa-check text-success"></i> ' + facility + '</li>';
            });
            
            html += '</ul></div>';
        }
        
        html += '</div></div>';
        
        $('.hotel-details-content').html(html);
    }
    
    // Filter and sort functionality
    function applyFilters() {
        const starFilter = $('#star-rating-filter').val();
        const priceFilter = $('#price-range-filter').val();
        const sortOption = $('#sort-options').val();
        
        // Filter hotels
        $('.hotel-item').each(function() {
            let show = true;
            
            // Star rating filter
            if (starFilter !== 'all') {
                const starRating = parseInt($(this).data('star-rating'));
                if (starRating !== parseInt(starFilter)) {
                    show = false;
                }
            }
            
            // Price filter
            if (priceFilter !== 'all') {
                const price = parseFloat($(this).data('price'));
                
                switch(priceFilter) {
                    case 'budget':
                        if (price > 100) show = false;
                        break;
                    case 'moderate':
                        if (price < 100 || price > 300) show = false;
                        break;
                    case 'luxury':
                        if (price < 300) show = false;
                        break;
                }
            }
            
            // Show or hide
            if (show) {
                $(this).removeClass('filtered-out').addClass('filtered-in');
            } else {
                $(this).removeClass('filtered-in').addClass('filtered-out');
            }
        });
        
        // Sort hotels
        const $container = $('#hotel-results-container');
        const $hotels = $('.hotel-item').not('.filtered-out').detach();
        
        $hotels.sort(function(a, b) {
            switch(sortOption) {
                case 'price-asc':
                    return parseFloat($(a).data('price')) - parseFloat($(b).data('price'));
                case 'price-desc':
                    return parseFloat($(b).data('price')) - parseFloat($(a).data('price'));
                case 'star-desc':
                    return parseInt($(b).data('star-rating')) - parseInt($(a).data('star-rating'));
                case 'name-asc':
                    return $(a).find('.card-title').text().localeCompare($(b).find('.card-title').text());
                default:
                    return 0;
            }
        });
        
        // Re-append sorted hotels after hotel summary
        $hotels.insertAfter($container.find('.hotel-results-summary'));
        
        // Show no results message if all filtered out
        if ($('.hotel-item').not('.filtered-out').length === 0) {
            if ($('#no-filtered-results').length === 0) {
                $container.append(
                    '<div id="no-filtered-results" class="alert alert-info mt-3">' +
                    '<p>No hotels match your current filters. Please try different filter options.</p>' +
                    '</div>'
                );
            }
        } else {
            $('#no-filtered-results').remove();
        }
    }
    
    // Attach filter change handlers
    $('#star-rating-filter, #price-range-filter, #sort-options').on('change', function() {
        applyFilters();
    });
    
    // Load hotels on page load
    loadHotels();
});
</script>

<?php get_footer(); ?>