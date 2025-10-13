<?php
/**
 * Template Name: Hotel Rooms
 * 
 * Template for displaying hotel room selection
 *
 * @package TBO_Hotels
 */

// Get the header
get_header();

// Debugging - Log all request parameters
error_log('TBO Hotels - Room Selection Debug - Request: ' . print_r($_GET, true));

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Check if we have required parameters
$can_fetch_rooms = !empty($hotel_code) && !empty($city_code) && !empty($check_in) && !empty($check_out);

// Initialize room data
$room_data = null;

// Fetch room details if we have required parameters
if ($can_fetch_rooms) {
    // Prepare request data
    $request_data = array(
        'hotel_code' => $hotel_code,
        'city_code' => $city_code,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'adults' => $adults,
        'children' => $children,
        'rooms' => $rooms
    );
    
    // Get room details
    $room_data = tbo_hotels_get_room_details($request_data);
}

// Format check-in and check-out dates for display
$check_in_date = !empty($check_in) ? new DateTime($check_in) : null;
$check_out_date = !empty($check_out) ? new DateTime($check_out) : null;

// Calculate nights
$nights = 0;
if ($check_in_date && $check_out_date) {
    $nights = $check_out_date->diff($check_in_date)->days;
}
?>

<div class="hotel-rooms-page">
    <!-- Compact Search Header -->
    <div class="search-header-compact">
        <div class="container">
            <div class="search-form-compact">
                <div class="search-field">
                    <label>Destination</label>
                    <input type="text" value="<?php echo isset($room_data['HotelInfo']['CityName']) ? esc_attr($room_data['HotelInfo']['CityName']) : 'City'; ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Check-in</label>
                    <input type="text" value="<?php echo $check_in_date ? $check_in_date->format('d M Y') : ''; ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Check-out</label>
                    <input type="text" value="<?php echo $check_out_date ? $check_out_date->format('d M Y') : ''; ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Guests</label>
                    <input type="text" value="<?php echo esc_attr($adults); ?> Adults, <?php echo esc_attr($children); ?> Children" readonly>
                </div>
                <button class="modify-search-btn">Modify Search</button>
            </div>
        </div>
    </div>

    <?php if ($can_fetch_rooms && !is_wp_error($room_data)): ?>
        <div class="container">
            <div class="hotel-rooms-container">
                <!-- Hotel Summary Section -->
                <div class="hotel-summary">
                    <div class="hotel-summary-header">
                        <div class="hotel-summary-main">
                            <h1 class="hotel-name"><?php echo esc_html($room_data['HotelInfo']['HotelName'] ?? 'Hotel Name'); ?></h1>
                            
                            <div class="hotel-rating">
                                <?php 
                                // Display star rating
                                $star_rating = $room_data['HotelInfo']['StarRating'] ?? 0;
                                for ($i = 0; $i < $star_rating; $i++) {
                                    echo '<span class="star">★</span>';
                                }
                                ?>
                            </div>
                            
                            <div class="hotel-location">
                                <i class="fas fa-map-marker-alt">📍</i>
                                <?php echo esc_html($room_data['HotelInfo']['HotelAddress'] ?? 'Address not available'); ?>
                            </div>
                        </div>
                        
                        <div class="hotel-summary-ratings">
                            <div class="rating-badge">Exceptional</div>
                            <div class="rating-score">4.5</div>
                            <div class="rating-reviews">Based on 1250 reviews</div>
                        </div>
                    </div>

                    <?php if (!empty($room_data['HotelInfo']['HotelFacilities'])): ?>
                    <div class="hotel-amenities-summary">
                        <h4>Hotel Amenities</h4>
                        <div class="amenities-list">
                            <?php foreach (array_slice($room_data['HotelInfo']['HotelFacilities'], 0, 10) as $facility): ?>
                                <div class="amenity-item">
                                    <span class="amenity-check">✓</span>
                                    <span><?php echo esc_html($facility); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="booking-summary">
                        <div class="booking-dates">
                            <div class="check-in">
                                <span class="date-label">Check-in</span>
                                <span class="date-value"><?php echo $check_in_date ? $check_in_date->format('D, d M Y') : ''; ?></span>
                            </div>
                            <div class="stay-nights">
                                <span class="nights-value"><?php echo $nights; ?> Nights</span>
                            </div>
                            <div class="check-out">
                                <span class="date-label">Check-out</span>
                                <span class="date-value"><?php echo $check_out_date ? $check_out_date->format('D, d M Y') : ''; ?></span>
                            </div>
                        </div>
                        <div class="booking-guests">
                            <div class="guests-info">
                                <span class="guests-label">Guests</span>
                                <span class="guests-value"><?php echo esc_html($adults); ?> Adults, <?php echo esc_html($children); ?> Children</span>
                            </div>
                            <div class="rooms-info">
                                <span class="rooms-label">Rooms</span>
                                <span class="rooms-value"><?php echo esc_html($rooms); ?> Room<?php echo $rooms > 1 ? 's' : ''; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="price-guarantees">
                    <div class="guarantee-item">
                        <div class="guarantee-icon">💰</div>
                        <div class="guarantee-text">Best Price Guarantee</div>
                    </div>
                    <div class="guarantee-item">
                        <div class="guarantee-icon">✅</div>
                        <div class="guarantee-text">Verified Properties</div>
                    </div>
                    <div class="guarantee-item">
                        <div class="guarantee-icon">🔐</div>
                        <div class="guarantee-text">Secure Payments</div>
                    </div>
                </div>

                <!-- Rooms Selection Section -->
                <div class="rooms-selection-header">
                    <h2>Select Room for Your Stay</h2>
                    <p>Choose from our available room options</p>
                </div>

                <div class="rooms-list">
                    <?php if (!empty($room_data['Rooms'])): ?>
                        <?php foreach ($room_data['Rooms'] as $index => $room): ?>
                            <div class="room-card" id="room-<?php echo esc_attr($index); ?>">
                                <div class="room-info-section">
                                    <div class="room-name-section">
                                        <h3 class="room-name"><?php echo esc_html($room['RoomTypeName'] ?? 'Standard Room'); ?></h3>
                                        
                                        <?php if (!empty($room['Inclusion'])): ?>
                                            <div class="room-inclusions">
                                                <?php foreach (array_slice($room['Inclusion'], 0, 3) as $inclusion): ?>
                                                    <div class="inclusion-item">
                                                        <span class="inclusion-check">✓</span>
                                                        <span><?php echo esc_html($inclusion); ?></span>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="room-capacity">
                                            <div class="capacity-item">
                                                <span class="capacity-icon">👤</span>
                                                <span class="capacity-text">Max <?php echo esc_html($room['MaxAdults'] ?? 2); ?> Adults</span>
                                            </div>
                                            <?php if (isset($room['MaxChild']) && $room['MaxChild'] > 0): ?>
                                                <div class="capacity-item">
                                                    <span class="capacity-icon">🧒</span>
                                                    <span class="capacity-text">Max <?php echo esc_html($room['MaxChild']); ?> Children</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="room-amenities">
                                        <div class="amenities-header">Room Amenities</div>
                                        <div class="amenities-list">
                                            <?php 
                                            // Display room amenities (using either API data or common amenities)
                                            $amenities = array('Free WiFi', 'Air Conditioning', 'TV', 'Private Bathroom');
                                            if (!empty($room['Amenities'])) {
                                                $amenities = array_slice($room['Amenities'], 0, 4);
                                            }
                                            
                                            foreach ($amenities as $amenity):
                                            ?>
                                                <div class="amenity-item">
                                                    <span class="amenity-check">✓</span>
                                                    <span><?php echo esc_html($amenity); ?></span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="room-cancellation">
                                        <?php 
                                        $cancellation_policy = 'Free cancellation before check-in';
                                        if (!empty($room['CancellationPolicy'])) {
                                            $cancellation_policy = $room['CancellationPolicy'];
                                        }
                                        ?>
                                        <div class="cancellation-icon">🛡️</div>
                                        <div class="cancellation-text"><?php echo esc_html($cancellation_policy); ?></div>
                                    </div>
                                </div>
                                
                                <div class="room-pricing-section">
                                    <?php 
                                    // Calculate the room price
                                    $room_price = 0;
                                    $discount_price = 0;
                                    
                                    if (!empty($room['DayRates']) && !empty($room['DayRates'][0]) && !empty($room['DayRates'][0][0])) {
                                        $day_rate = $room['DayRates'][0][0];
                                        $room_price = $day_rate['BasePrice'] ?? 0;
                                        
                                        // Create a mock original price (20% higher)
                                        $discount_price = $room_price * 1.2;
                                    }
                                    
                                    // Calculate total for stay
                                    $total_price = $room_price * $nights;
                                    $total_discount = $discount_price * $nights;
                                    $discount_percent = $discount_price > 0 ? round(($discount_price - $room_price) / $discount_price * 100) : 0;
                                    ?>
                                    
                                    <div class="pricing-details">
                                        <?php if ($discount_percent > 0): ?>
                                            <div class="discount-percent"><?php echo esc_html($discount_percent); ?>% OFF</div>
                                        <?php endif; ?>
                                        
                                        <?php if ($room_price > 0): ?>
                                            <div class="original-price">₹<?php echo number_format($discount_price); ?></div>
                                            <div class="current-price">₹<?php echo number_format($room_price); ?></div>
                                            <div class="price-per-night">per room per night</div>
                                            
                                            <div class="total-price-container">
                                                <div class="total-price-label">Total for <?php echo esc_html($nights); ?> nights</div>
                                                <div class="total-price-value">₹<?php echo number_format($total_price); ?></div>
                                                <div class="total-price-note">+₹<?php echo number_format($total_price * 0.12); ?> taxes & fees</div>
                                            </div>
                                        <?php else: ?>
                                            <div class="current-price">Price on request</div>
                                            <div class="price-per-night">Contact for rates</div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <button class="select-room-btn" data-room-index="<?php echo esc_attr($index); ?>">
                                        Select
                                    </button>
                                    
                                    <div class="room-availability">
                                        <div class="availability-icon">🔥</div>
                                        <div class="availability-text">Only <?php echo rand(1, 5); ?> rooms left at this price!</div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="no-rooms-available">
                            <h3>No rooms available for the selected dates</h3>
                            <p>Please try different dates or contact the hotel directly.</p>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Hotel Policies Section -->
                <div class="hotel-policies">
                    <h2>Hotel Policies</h2>
                    
                    <div class="policies-grid">
                        <div class="policy-item">
                            <div class="policy-icon">⏰</div>
                            <div class="policy-info">
                                <h4>Check-in & Check-out</h4>
                                <p>Check-in: 2:00 PM - 12:00 AM</p>
                                <p>Check-out: Until 12:00 PM</p>
                            </div>
                        </div>
                        
                        <div class="policy-item">
                            <div class="policy-icon">🧾</div>
                            <div class="policy-info">
                                <h4>Payment Information</h4>
                                <p>All major credit cards accepted</p>
                                <p>GST Invoice available</p>
                            </div>
                        </div>
                        
                        <div class="policy-item">
                            <div class="policy-icon">🚫</div>
                            <div class="policy-info">
                                <h4>Cancellation Policy</h4>
                                <p>Free cancellation before check-in</p>
                                <p>See room details for specific policies</p>
                            </div>
                        </div>
                        
                        <div class="policy-item">
                            <div class="policy-icon">ℹ️</div>
                            <div class="policy-info">
                                <h4>Additional Information</h4>
                                <p>Pets not allowed</p>
                                <p>ID proof required at check-in</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="error-message">
                <?php if (is_wp_error($room_data)): ?>
                    <h2>Error</h2>
                    <p><?php echo esc_html($room_data->get_error_message()); ?></p>
                <?php else: ?>
                    <h2>Missing Information</h2>
                    <p>Please provide all required information to view room details.</p>
                <?php endif; ?>
                
                <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="return-to-search">Return to Search</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>