<?php
/**
 * Template Name: Room Type Selection
 * 
 * Template for displaying hotel room selection
 *
 * @package TBO_Hotels
 */

// Get the header
get_header();

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

// Debug logging
error_log("Room Type Template - Parameters: hotel_code=$hotel_code, city_code=$city_code, check_in=$check_in, check_out=$check_out");

// Fetch room details if we have required parameters
if ($can_fetch_rooms) {
    // Prepare request data
    $params = array(
        'hotel_code' => $hotel_code,
        'city_code' => $city_code,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'adults' => $adults,
        'children' => $children,
        'rooms' => $rooms
    );
    
    // Get room details
    $room_data = tbo_hotels_get_room_details($params);
    
    // Debug log the result
    if (is_wp_error($room_data)) {
        error_log("Room Type Template - Error: " . $room_data->get_error_message());
    } else {
        error_log("Room Type Template - Success: Found " . count($room_data['Rooms'] ?? []) . " rooms");
    }
}
?>

<div class="hotel-rooms-page">
    <!-- Compact Search Header -->
    <div class="search-header-compact">
        <div class="container">
            <div class="search-form-compact">
                <div class="search-field">
                    <label>Destination</label>
                    <input type="text" value="<?php echo esc_attr($city_code); ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Check-in</label>
                    <input type="text" value="<?php echo esc_attr($check_in); ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Check-out</label>
                    <input type="text" value="<?php echo esc_attr($check_out); ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Guests</label>
                    <input type="text" value="<?php echo esc_attr($adults); ?> Adults, <?php echo esc_attr($children); ?> Children" readonly>
                </div>
                <button class="modify-search-btn">Modify Search</button>
            </div>
        </div>
    </div>
    
    <div class="container hotel-rooms-container">
        <?php if ($can_fetch_rooms && $room_data && !is_wp_error($room_data)): ?>
            <?php 
            // Extract hotel info and room data
            $hotel_info = $room_data['HotelInfo'];
            $rooms_data = $room_data['Rooms'];
            ?>
            
            <!-- Hotel Summary Section -->
            <div class="hotel-summary">
                <div class="hotel-summary-header">
                    <div class="hotel-summary-main">
                        <h1 class="hotel-name"><?php echo esc_html($hotel_info['HotelName'] ?? 'Hotel Details'); ?></h1>
                        
                        <div class="hotel-rating">
                            <?php for ($i = 0; $i < ($hotel_info['StarRating'] ?? 0); $i++): ?>
                                <span class="star">★</span>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="hotel-location">
                            <i class="location-icon">📍</i>
                            <span><?php echo esc_html($hotel_info['HotelAddress'] ?? ''); ?></span>
                        </div>
                        
                        <div class="hotel-amenities-summary">
                            <h4>Top Amenities</h4>
                            <div class="amenities-list">
                                <?php 
                                $facilities = $hotel_info['HotelFacilities'] ?? array();
                                $top_facilities = array_slice($facilities, 0, 6);
                                
                                foreach ($top_facilities as $facility): ?>
                                    <div class="amenity-item">
                                        <span class="amenity-check">✓</span>
                                        <span><?php echo esc_html($facility); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="hotel-summary-ratings">
                        <div class="rating-badge">Excellent</div>
                        <div class="rating-score">4.5</div>
                        <div class="rating-reviews">2,458 reviews</div>
                    </div>
                </div>
                
                <div class="booking-summary">
                    <div class="booking-dates">
                        <div class="check-in">
                            <div class="date-label">CHECK-IN</div>
                            <div class="date-value"><?php echo esc_html(date('D, M j, Y', strtotime($check_in))); ?></div>
                        </div>
                        
                        <div class="stay-nights">
                            <div class="nights-value">
                                <?php 
                                $nights = floor((strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24));
                                echo esc_html($nights . ' Night' . ($nights > 1 ? 's' : ''));
                                ?>
                            </div>
                        </div>
                        
                        <div class="check-out">
                            <div class="date-label">CHECK-OUT</div>
                            <div class="date-value"><?php echo esc_html(date('D, M j, Y', strtotime($check_out))); ?></div>
                        </div>
                    </div>
                    
                    <div class="booking-guests">
                        <div class="guests-info">
                            <div class="guests-label">GUESTS</div>
                            <div class="guests-value"><?php echo esc_html($adults); ?> Adults<?php echo $children > 0 ? ', ' . $children . ' Children' : ''; ?></div>
                        </div>
                        
                        <div class="rooms-info">
                            <div class="rooms-label">ROOMS</div>
                            <div class="rooms-value"><?php echo esc_html($rooms); ?> Room<?php echo $rooms > 1 ? 's' : ''; ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Price Guarantees -->
            <div class="price-guarantees">
                <div class="guarantee-item">
                    <span class="guarantee-icon">🔒</span>
                    <span class="guarantee-text">Best Price Guarantee</span>
                </div>
                <div class="guarantee-item">
                    <span class="guarantee-icon">💰</span>
                    <span class="guarantee-text">No Booking Fees</span>
                </div>
                <div class="guarantee-item">
                    <span class="guarantee-icon">🛡️</span>
                    <span class="guarantee-text">Secure Booking</span>
                </div>
                <div class="guarantee-item">
                    <span class="guarantee-icon">📞</span>
                    <span class="guarantee-text">24/7 Support</span>
                </div>
            </div>
            
            <!-- Rooms Selection -->
            <div class="rooms-selection-header">
                <h2>Available Rooms</h2>
                <p>Select from <?php echo count($rooms_data); ?> available room types</p>
            </div>
            
            <div class="rooms-list">
                <?php 
                // Check if we have room data
                if (!empty($rooms_data)): 
                    
                    // Loop through each room type
                    foreach ($rooms_data as $index => $room): 
                        
                        // Calculate pricing
                        $base_price = 0;
                        if (!empty($room['DayRates'][0][0]['BasePrice'])) {
                            $base_price = $room['DayRates'][0][0]['BasePrice'];
                        }
                        
                        $discounted_price = $base_price;
                        $original_price = $base_price * 1.15; // Mock original price (15% higher)
                        $total_price = $discounted_price * $nights;
                        
                        // Calculate discount percentage
                        $discount_percent = round(($original_price - $discounted_price) / $original_price * 100);
                ?>
                
                <div class="room-card" id="room-<?php echo esc_attr($index); ?>">
                    <div class="room-info-section">
                        <div class="room-name-section">
                            <h3 class="room-name">
                                <?php echo esc_html($room['RoomName'] ?? 'Standard Room'); ?>
                            </h3>
                        </div>
                        
                        <div class="room-inclusions">
                            <div class="inclusion-item">
                                <span class="inclusion-check">✓</span>
                                <span>Free Cancellation till 24 hours before check-in</span>
                            </div>
                            <div class="inclusion-item">
                                <span class="inclusion-check">✓</span>
                                <span>Breakfast included</span>
                            </div>
                        </div>
                        
                        <div class="room-capacity">
                            <div class="capacity-item">
                                <span class="capacity-icon">👤</span>
                                <span>Max <?php echo esc_html($adults); ?> Adults</span>
                            </div>
                            <?php if ($children > 0): ?>
                            <div class="capacity-item">
                                <span class="capacity-icon">🧒</span>
                                <span>Max <?php echo esc_html($children); ?> Children</span>
                            </div>
                            <?php endif; ?>
                            <div class="capacity-item">
                                <span class="capacity-icon">🛏️</span>
                                <span>1 King Bed or 2 Twin Beds</span>
                            </div>
                        </div>
                        
                        <div class="room-amenities">
                            <h4 class="amenities-header">Room Amenities</h4>
                            <div class="amenities-list">
                                <?php 
                                // Mock room amenities
                                $room_amenities = array('Air Conditioning', 'Free WiFi', 'Flat-screen TV', 'Private Bathroom', 'Safety Deposit Box', 'Mini Bar');
                                
                                foreach ($room_amenities as $amenity): ?>
                                    <div class="amenity-item">
                                        <span class="amenity-check">✓</span>
                                        <span><?php echo esc_html($amenity); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="room-cancellation">
                            <span class="cancellation-icon">ℹ️</span>
                            <span class="cancellation-text">Free cancellation before <?php echo esc_html(date('M j, Y', strtotime($check_in . ' -1 day'))); ?></span>
                        </div>
                    </div>
                    
                    <div class="room-pricing-section">
                        <div class="pricing-details">
                            <div class="discount-percent"><?php echo esc_html($discount_percent); ?>% off</div>
                            <div class="original-price">₹<?php echo esc_html(number_format($original_price, 0)); ?></div>
                            <div class="current-price">₹<?php echo esc_html(number_format($discounted_price, 0)); ?></div>
                            <div class="price-per-night">per room per night</div>
                            
                            <div class="total-price-container">
                                <div class="total-price-label">Total for <?php echo esc_html($nights); ?> night<?php echo $nights > 1 ? 's' : ''; ?></div>
                                <div class="total-price-value">₹<?php echo esc_html(number_format($total_price, 0)); ?></div>
                                <div class="total-price-note">+taxes & fees</div>
                            </div>
                        </div>
                        
                        <button class="select-room-btn" data-room-index="<?php echo esc_attr($index); ?>">
                            Select Room
                        </button>
                        
                        <div class="room-availability">
                            <span class="availability-icon">⚠️</span>
                            <span>Only <?php echo rand(1, 5); ?> rooms left!</span>
                        </div>
                    </div>
                </div>
                
                <?php endforeach; ?>
                
                <?php else: ?>
                    <div class="no-rooms-message">
                        <h3>No rooms available for your selected dates</h3>
                        <p>Please try different dates or modify your search criteria.</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Hotel Policies -->
            <div class="hotel-policies">
                <h2>Hotel Policies</h2>
                <div class="policies-grid">
                    <div class="policy-item">
                        <span class="policy-icon">🕒</span>
                        <div class="policy-info">
                            <h4>Check-in & Check-out</h4>
                            <p>Check-in: 2:00 PM - 12:00 AM</p>
                            <p>Check-out: Until 12:00 PM</p>
                        </div>
                    </div>
                    
                    <div class="policy-item">
                        <span class="policy-icon">💳</span>
                        <div class="policy-info">
                            <h4>Payment</h4>
                            <p>We accept credit/debit cards</p>
                            <p>Pay at hotel available</p>
                        </div>
                    </div>
                    
                    <div class="policy-item">
                        <span class="policy-icon">🚫</span>
                        <div class="policy-info">
                            <h4>Cancellation</h4>
                            <p>Free cancellation before 24 hours</p>
                            <p>Charges may apply after that</p>
                        </div>
                    </div>
                    
                    <div class="policy-item">
                        <span class="policy-icon">🧒</span>
                        <div class="policy-info">
                            <h4>Children & Pets</h4>
                            <p>Children of all ages welcome</p>
                            <p>Pets not allowed</p>
                        </div>
                    </div>
                </div>
            </div>
            
        <?php else: ?>
            <!-- Error message if required parameters are missing or API call failed -->
            <div class="error-message">
                <h2>Unable to Display Room Information</h2>
                <?php if (is_wp_error($room_data)): ?>
                    <p><?php echo esc_html($room_data->get_error_message()); ?></p>
                <?php else: ?>
                    <p>Required parameters are missing or invalid. Please try searching again.</p>
                <?php endif; ?>
                <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="return-to-search">Return to Search</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
// Get the footer
get_footer();
?>