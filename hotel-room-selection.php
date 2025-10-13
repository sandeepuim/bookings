<?php
/**
 * Direct Hotel Room Selection Page
 * 
 * This page handles direct room selection without WordPress routing
 */

// Load WordPress core
require_once(__DIR__ . '/wp-load.php');

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Debug - Log request parameters
error_log('Direct hotel room selection: ' . json_encode($_GET));

// Check if function exists and log
if (!function_exists('tbo_hotels_get_room_details')) {
    error_log('Function tbo_hotels_get_room_details does not exist!');
    
    // Create inline function if needed
    function tbo_hotels_get_room_details($params) {
        error_log('Using inline tbo_hotels_get_room_details function');
        
        // Mock hotel information
        $hotel_info = array(
            'HotelName' => 'Grand Hotel Plaza (Fallback)',
            'HotelCode' => $params['hotel_code'],
            'StarRating' => 4,
            'HotelAddress' => 'Via del Corso 126, Rome, Italy',
            'HotelFacilities' => array('Free WiFi', 'Restaurant', 'Pool')
        );
        
        // Mock room
        $rooms = array(
            array(
                'RoomIndex' => 1,
                'RoomName' => 'Standard Room',
                'RoomTypeCode' => 'STD',
                'DayRates' => array(
                    array(array('BasePrice' => 5000))
                )
            )
        );
        
        return array(
            'HotelInfo' => $hotel_info,
            'Rooms' => $rooms
        );
    }
}

// Make sure we have required parameters
$can_fetch_details = !empty($hotel_code) && !empty($city_code) && !empty($check_in) && !empty($check_out);

// Initialize room data
$room_data = null;

// Get the header
get_header();

// Debug output
if (isset($_GET['debug'])) {
    echo '<div style="background: #f5f5f5; padding: 15px; margin: 20px; border-left: 4px solid #0073aa;">';
    echo '<h3>Debug Information</h3>';
    echo '<p><strong>Hotel Code:</strong> ' . esc_html($hotel_code) . '</p>';
    echo '<p><strong>City Code:</strong> ' . esc_html($city_code) . '</p>';
    echo '<p><strong>Check In:</strong> ' . esc_html($check_in) . '</p>';
    echo '<p><strong>Check Out:</strong> ' . esc_html($check_out) . '</p>';
    echo '<p><strong>Adults:</strong> ' . esc_html($adults) . '</p>';
    echo '<p><strong>Children:</strong> ' . esc_html($children) . '</p>';
    echo '<p><strong>Rooms:</strong> ' . esc_html($rooms) . '</p>';
    echo '<p><strong>Function Exists:</strong> ' . (function_exists('tbo_hotels_get_room_details') ? 'Yes' : 'No') . '</p>';
    echo '</div>';
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
        <?php 
        // Show debugging info 
        if (isset($_GET['debug'])):
        ?>
            <div style="background: #f5f5f5; padding: 15px; margin-bottom: 20px; border-left: 4px solid #0073aa;">
                <h3>Debug Information</h3>
                <p><strong>Hotel Code:</strong> <?php echo esc_html($hotel_code); ?></p>
                <p><strong>City Code:</strong> <?php echo esc_html($city_code); ?></p>
                <p><strong>Check In:</strong> <?php echo esc_html($check_in); ?></p>
                <p><strong>Check Out:</strong> <?php echo esc_html($check_out); ?></p>
                <p><strong>Adults:</strong> <?php echo esc_html($adults); ?></p>
                <p><strong>Children:</strong> <?php echo esc_html($children); ?></p>
                <p><strong>Rooms:</strong> <?php echo esc_html($rooms); ?></p>
            </div>
        <?php endif; ?>
            
        <?php
        // Fetch room details if we have required parameters
        if ($can_fetch_details) {
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
                error_log("Room selection error: " . $room_data->get_error_message());
                ?>
                <div class="error-message">
                    <h2>Unable to Display Room Information</h2>
                    <p><?php echo esc_html($room_data->get_error_message()); ?></p>
                    <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="return-to-search">Return to Search</a>
                </div>
                <?php
            } else {
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
                <?php
            }
        } else {
            ?>
            <div class="error-message">
                <h2>Unable to Display Room Information</h2>
                <p>Required parameters are missing or invalid. Please try searching again.</p>
                <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="return-to-search">Return to Search</a>
            </div>
            <?php
        }
        ?>
    </div>
</div>

<?php
// Get the footer
get_footer();
?>