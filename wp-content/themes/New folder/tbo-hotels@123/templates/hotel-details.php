<?php
/**
 * Template Name: Hotel Details
 * 
 * Template for displaying hotel details
 */

// Get the header
get_header();

// Get hotel code from URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$checkin = isset($_GET['checkin']) ? sanitize_text_field($_GET['checkin']) : '';
$checkout = isset($_GET['checkout']) ? sanitize_text_field($_GET['checkout']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Check if we have required parameters
$can_fetch_details = !empty($hotel_code) && !empty($city_code) && !empty($checkin) && !empty($checkout);

// Initialize hotel data
$hotel_data = null;

// Fetch hotel details if we have required parameters
if ($can_fetch_details) {
    // Prepare request data
    $request_data = array(
        'hotel_code' => $hotel_code,
        'city_code' => $city_code,
        'checkin' => $checkin,
        'checkout' => $checkout,
        'adults' => $adults,
        'children' => $children,
        'rooms' => $rooms
    );
    
    // Get hotel details
    $hotel_data = tbo_hotels_get_hotel_details($request_data);
}
?>

<div class="container">
    <div class="hotel-details-container">
        <?php if ($can_fetch_details && $hotel_data && !is_wp_error($hotel_data)): ?>
            
            <div class="hotel-details-header">
                <div class="hotel-details-images">
                    <?php if (!empty($hotel_data['HotelImages']) && count($hotel_data['HotelImages']) > 0): ?>
                        <div class="hotel-main-image">
                            <img src="<?php echo esc_url($hotel_data['HotelImages'][0]); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>">
                        </div>
                        <?php if (count($hotel_data['HotelImages']) > 1): ?>
                            <div class="hotel-thumbnail-images">
                                <?php foreach (array_slice($hotel_data['HotelImages'], 1, 5) as $image): ?>
                                    <div class="hotel-thumbnail">
                                        <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>">
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($hotel_data['HotelImages']) > 6): ?>
                                    <div class="hotel-thumbnail more-photos">
                                        <span>+<?php echo count($hotel_data['HotelImages']) - 6; ?> more</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="hotel-main-image">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/placeholder.jpg'); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>">
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="hotel-details-summary">
                    <h1 class="hotel-name"><?php echo esc_html($hotel_data['HotelName']); ?></h1>
                    
                    <div class="hotel-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo esc_html($hotel_data['HotelAddress'] ?? 'Address not available'); ?>
                    </div>
                    
                    <div class="hotel-rating">
                        <?php echo tbo_hotels_get_star_rating($hotel_data['StarRating'] ?? 0); ?>
                    </div>
                    
                    <?php if (!empty($hotel_data['HotelFacilities']) && count($hotel_data['HotelFacilities']) > 0): ?>
                        <div class="hotel-amenities">
                            <h3>Top Amenities</h3>
                            <div class="amenities-list">
                                <?php foreach (array_slice($hotel_data['HotelFacilities'], 0, 8) as $facility): ?>
                                    <span class="amenity"><?php echo esc_html($facility); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="booking-info">
                        <div class="booking-dates">
                            <div class="check-date">
                                <span class="label">Check-in</span>
                                <span class="date"><?php echo date('D, M j, Y', strtotime($checkin)); ?></span>
                            </div>
                            <div class="check-date">
                                <span class="label">Check-out</span>
                                <span class="date"><?php echo date('D, M j, Y', strtotime($checkout)); ?></span>
                            </div>
                        </div>
                        <div class="booking-guests">
                            <span class="guests-info">
                                <?php 
                                $total_guests = $adults + $children;
                                echo sprintf(
                                    _n('%d Guest', '%d Guests', $total_guests, 'tbo-hotels'), 
                                    $total_guests
                                ); 
                                ?>, 
                                <?php 
                                echo sprintf(
                                    _n('%d Room', '%d Rooms', $rooms, 'tbo-hotels'), 
                                    $rooms
                                ); 
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="hotel-details-content">
                <div class="hotel-details-main">
                    <?php if (!empty($hotel_data['HotelDescription'])): ?>
                        <section class="hotel-description">
                            <h2>About This Hotel</h2>
                            <div class="description-content">
                                <?php echo wpautop(esc_html($hotel_data['HotelDescription'])); ?>
                            </div>
                        </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($hotel_data['HotelFacilities']) && count($hotel_data['HotelFacilities']) > 0): ?>
                        <section class="hotel-facilities">
                            <h2>Hotel Facilities</h2>
                            <div class="facilities-content">
                                <div class="facilities-list">
                                    <?php 
                                    $facilities = $hotel_data['HotelFacilities'];
                                    $half = ceil(count($facilities) / 2);
                                    $first_half = array_slice($facilities, 0, $half);
                                    $second_half = array_slice($facilities, $half);
                                    ?>
                                    
                                    <div class="facilities-column">
                                        <ul>
                                            <?php foreach ($first_half as $facility): ?>
                                                <li><?php echo esc_html($facility); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                    
                                    <div class="facilities-column">
                                        <ul>
                                            <?php foreach ($second_half as $facility): ?>
                                                <li><?php echo esc_html($facility); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($hotel_data['HotelLocation'])): ?>
                        <section class="hotel-location-section">
                            <h2>Location</h2>
                            <div class="location-content">
                                <div class="location-map">
                                    <?php if (!empty($hotel_data['Latitude']) && !empty($hotel_data['Longitude'])): ?>
                                        <div class="map-container">
                                            <iframe
                                                width="100%"
                                                height="400"
                                                frameborder="0"
                                                style="border:0"
                                                src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=<?php echo esc_attr($hotel_data['Latitude']); ?>,<?php echo esc_attr($hotel_data['Longitude']); ?>"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    <?php else: ?>
                                        <div class="map-placeholder">
                                            <p>Map location not available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="location-description">
                                    <?php echo wpautop(esc_html($hotel_data['HotelLocation'])); ?>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>
                    
                    <?php if (!empty($hotel_data['HotelPolicy'])): ?>
                        <section class="hotel-policies">
                            <h2>Hotel Policies</h2>
                            <div class="policies-content">
                                <?php echo wpautop(esc_html($hotel_data['HotelPolicy'])); ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
                
                <div class="hotel-details-sidebar">
                    <div class="booking-widget">
                        <h3>Book This Hotel</h3>
                        
                        <div class="price-summary">
                            <div class="price-label">Price for <?php echo esc_html($rooms); ?> room<?php echo $rooms > 1 ? 's' : ''; ?>, <?php echo esc_html(tbo_hotels_calculate_nights($checkin, $checkout)); ?> night<?php echo tbo_hotels_calculate_nights($checkin, $checkout) > 1 ? 's' : ''; ?></div>
                            
                            <?php if (!empty($hotel_data['OriginalPrice']) && $hotel_data['OriginalPrice'] > $hotel_data['MinHotelPrice']): ?>
                                <div class="original-price"><?php echo tbo_hotels_format_price($hotel_data['OriginalPrice'], $hotel_data['Price']['CurrencyCode']); ?></div>
                            <?php endif; ?>
                            
                            <div class="current-price"><?php echo tbo_hotels_format_price($hotel_data['MinHotelPrice'], $hotel_data['Price']['CurrencyCode']); ?></div>
                            
                            <div class="price-includes">Includes taxes & fees</div>
                        </div>
                        
                        <div class="booking-form">
                            <form id="booking-form" action="<?php echo esc_url(home_url('/hotel-booking/')); ?>" method="get">
                                <input type="hidden" name="hotel_code" value="<?php echo esc_attr($hotel_code); ?>">
                                <input type="hidden" name="city_code" value="<?php echo esc_attr($city_code); ?>">
                                <input type="hidden" name="checkin" value="<?php echo esc_attr($checkin); ?>">
                                <input type="hidden" name="checkout" value="<?php echo esc_attr($checkout); ?>">
                                <input type="hidden" name="adults" value="<?php echo esc_attr($adults); ?>">
                                <input type="hidden" name="children" value="<?php echo esc_attr($children); ?>">
                                <input type="hidden" name="rooms" value="<?php echo esc_attr($rooms); ?>">
                                
                                <button type="submit" class="book-now-button">Book Now</button>
                            </form>
                        </div>
                        
                        <div class="booking-info-notes">
                            <p><strong>Reservation Policy:</strong> Free cancellation up to 24 hours before check-in</p>
                            <p><strong>Payment:</strong> Pay at the hotel</p>
                        </div>
                    </div>
                    
                    <?php if (!empty($hotel_data['HotelReviews'])): ?>
                        <div class="hotel-reviews-summary">
                            <h3>Guest Reviews</h3>
                            
                            <div class="reviews-average">
                                <div class="average-score"><?php echo number_format($hotel_data['ReviewRating'], 1); ?></div>
                                <div class="average-label">
                                    <?php echo tbo_hotels_get_rating_text($hotel_data['ReviewRating']); ?>
                                    <div class="review-count"><?php echo count($hotel_data['HotelReviews']); ?> reviews</div>
                                </div>
                            </div>
                            
                            <div class="reviews-preview">
                                <?php foreach (array_slice($hotel_data['HotelReviews'], 0, 2) as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-name"><?php echo esc_html($review['GuestName']); ?></div>
                                            <div class="review-date"><?php echo date('M Y', strtotime($review['ReviewDate'])); ?></div>
                                        </div>
                                        <div class="review-rating"><?php echo tbo_hotels_get_star_rating($review['Rating'], 'small'); ?></div>
                                        <div class="review-content"><?php echo esc_html($review['Comments']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($hotel_data['HotelReviews']) > 2): ?>
                                    <a href="#reviews" class="view-all-reviews">View all <?php echo count($hotel_data['HotelReviews']); ?> reviews</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($hotel_data['HotelReviews']) && count($hotel_data['HotelReviews']) > 2): ?>
                <section id="reviews" class="hotel-reviews-section">
                    <h2>Guest Reviews</h2>
                    
                    <div class="reviews-list">
                        <?php foreach ($hotel_data['HotelReviews'] as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-name"><?php echo esc_html($review['GuestName']); ?></div>
                                    <div class="review-date"><?php echo date('M Y', strtotime($review['ReviewDate'])); ?></div>
                                </div>
                                <div class="review-rating"><?php echo tbo_hotels_get_star_rating($review['Rating'], 'small'); ?></div>
                                <div class="review-content"><?php echo esc_html($review['Comments']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="hotel-details-error">
                <h2>Hotel Not Found</h2>
                <p>Sorry, we couldn't find the hotel you're looking for. Please try searching again.</p>
                <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="button">Back to Search</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>