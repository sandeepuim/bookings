<?php
/**
 * Template Name: Hotel Details
 */
get_header();

// Load the CSS files
wp_enqueue_style('hotel-details', get_template_directory_uri() . '/assets/css/hotel-details.css', array(), '1.0');
wp_enqueue_style('hotel-rooms', get_template_directory_uri() . '/assets/css/hotel-rooms.css', array(), '1.0');

require_once get_template_directory() . '/inc/TboApiClient.php';

// Get the hotel code from URL
$hotel_code = sanitize_text_field($_GET['hotel_code'] ?? '');

// Get search parameters from URL or use defaults
$check_in = sanitize_text_field($_GET['check_in'] ?? date('Y-m-d', strtotime('+1 day')));
$check_out = sanitize_text_field($_GET['check_out'] ?? date('Y-m-d', strtotime('+3 days')));
$rooms = intval($_GET['rooms'] ?? 1);
$adults = intval($_GET['adults'] ?? 1);
$children = intval($_GET['children'] ?? 0);
$country_code = sanitize_text_field($_GET['country_code'] ?? 'IN');
$city_code = sanitize_text_field($_GET['city_code'] ?? '');

// Connect to TBO API
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI', // Service URL
    'YOLANDATHTest',
    'Yol@40360746'
);

try {
    // Set error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    if (empty($hotel_code)) {
        throw new Exception('Hotel code is missing. Please select a hotel first.');
    }
    
    // Get hotel details
    $details = $tbo->getHotelDetails($hotel_code);
    
    if (empty($details) || empty($details['Hotel'])) {
        throw new Exception('Hotel details not found. Please try again later.');
    }
    
    // Get hotel data
    $hotel = $details['Hotel'];
    
    // Extract hotel name
    $hotelName = isset($hotel['HotelName']) ? $hotel['HotelName'] : 'Premium Hotel';
    
    // Get hotel location/address
    $hotelAddress = isset($hotel['HotelAddress']) ? $hotel['HotelAddress'] : '';
    $hotelLocation = isset($hotel['Location']) ? $hotel['Location'] : '';
    $hotelCity = isset($hotel['CityName']) ? $hotel['CityName'] : $city_code;
    $displayLocation = !empty($hotelAddress) ? $hotelAddress : (!empty($hotelLocation) ? $hotelLocation : $hotelCity);
    
    // Get hotel images
    $hotelImages = [];
    
    // Try to find hotel image from various possible locations
    if (isset($hotel['HotelPicture']) && !empty($hotel['HotelPicture'])) {
        $hotelImages[] = $hotel['HotelPicture'];
    } 
    
    if (isset($hotel['Images']) && is_array($hotel['Images'])) {
        foreach ($hotel['Images'] as $image) {
            if (!empty($image) && !in_array($image, $hotelImages)) {
                $hotelImages[] = $image;
            }
        }
    }
    
    // Add dummy images if we don't have enough
    $dummyImages = [
        'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
        'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGhvdGVsfGVufDB8fDB8fHww&auto=format&fit=crop&w=800&q=60',
        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
        'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
        'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60'
    ];
    
    if (empty($hotelImages)) {
        $hotelImages = $dummyImages;
    } elseif (count($hotelImages) < 3) {
        // Add some dummy images if we don't have enough
        $numToAdd = 3 - count($hotelImages);
        for ($i = 0; $i < $numToAdd; $i++) {
            $hotelImages[] = $dummyImages[$i];
        }
    }
    
    // Get hotel rating
    $rating = isset($hotel['StarRating']) ? intval($hotel['StarRating']) : 4;
    
    // Get hotel amenities
    $amenities = [
        'Free Wi-Fi',
        'Swimming Pool',
        'Fitness Center',
        'Restaurant',
        'Room Service',
        'Parking',
        'Air Conditioning',
        'Breakfast Available'
    ];
    
    // Get available rooms
    $rooms = $tbo->getRoomAvailability($hotel_code, $check_in, $check_out);
    
    // Calculate nights
    $nights = ceil((strtotime($check_out) - strtotime($check_in)) / 86400);
    
    ?>
    
    <div class="hotel-details-container">
        <!-- Hotel Search Summary -->
        <div class="search-summary">
            <div class="container">
                <div class="search-info">
                    <h1><?php echo esc_html($hotelName); ?></h1>
                    <div class="hotel-location">
                        <?php if (!empty($displayLocation)): ?>
                            <i class="location-icon">📍</i> <?php echo esc_html($displayLocation); ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="search-params">
                    <div class="search-dates">
                        <div class="date-box">
                            <span class="date-label">Check-in</span>
                            <span class="date-value"><?php echo esc_html(date('d M Y', strtotime($check_in))); ?></span>
                        </div>
                        <div class="date-separator">-</div>
                        <div class="date-box">
                            <span class="date-label">Check-out</span>
                            <span class="date-value"><?php echo esc_html(date('d M Y', strtotime($check_out))); ?></span>
                        </div>
                    </div>
                    <div class="search-guests">
                        <span><?php echo $rooms; ?> Room, <?php echo $adults; ?> Guest<?php echo $adults > 1 ? 's' : ''; ?></span>
                    </div>
                    <div class="modify-search">
                        <a href="javascript:history.back()" class="btn-modify">Modify Search</a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hotel Gallery -->
        <div class="hotel-gallery-container">
            <div class="container">
                <div class="hotel-gallery-grid">
                    <div class="main-image">
                        <img src="<?php echo esc_url($hotelImages[0]); ?>" alt="<?php echo esc_attr($hotelName); ?>" class="hotel-main-image">
                    </div>
                    <div class="gallery-thumbnails">
                        <?php for ($i = 1; $i < min(3, count($hotelImages)); $i++): ?>
                            <div class="gallery-thumb">
                                <img src="<?php echo esc_url($hotelImages[$i]); ?>" alt="<?php echo esc_attr($hotelName); ?> image <?php echo $i+1; ?>" class="thumb-image">
                            </div>
                        <?php endfor; ?>
                        
                        <?php if (count($hotelImages) > 3): ?>
                            <div class="gallery-thumb more-photos">
                                <div class="more-overlay">
                                    <span>+<?php echo count($hotelImages) - 3; ?> Photos</span>
                                </div>
                                <img src="<?php echo esc_url($hotelImages[3]); ?>" alt="<?php echo esc_attr($hotelName); ?> more photos" class="thumb-image">
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Hotel Content Area -->
        <div class="hotel-content-area">
            <div class="container">
                <div class="hotel-content-grid">
                    <!-- Left Column: Hotel Details -->
                    <div class="hotel-info-column">
                        <div class="hotel-header">
                            <h1 class="hotel-name"><?php echo esc_html($hotelName); ?></h1>
                            <div class="hotel-rating">
                                <?php for ($i = 0; $i < $rating; $i++): ?>
                                    <span class="star">★</span>
                                <?php endfor; ?>
                            </div>
                            <?php if (!empty($displayLocation)): ?>
                                <div class="hotel-address">
                                    <i class="location-icon">📍</i> <?php echo esc_html($displayLocation); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Hotel Amenities -->
                        <?php if (!empty($amenities)): ?>
                        <div class="hotel-amenities-section">
                            <h3>Hotel Amenities</h3>
                            <div class="amenities-grid">
                                <?php foreach ($amenities as $amenity): ?>
                                    <div class="amenity-item">
                                        <i class="amenity-icon">✓</i>
                                        <span class="amenity-name"><?php echo esc_html($amenity); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Hotel Description -->
                        <div class="hotel-description">
                            <h3>About <?php echo esc_html($hotelName); ?></h3>
                            <div class="description-text">
                                <p>Located in the heart of <?php echo esc_html($city_code); ?>, <?php echo esc_html($hotelName); ?> offers comfortable accommodation with modern amenities. Guests can enjoy a pleasant stay with convenient access to local attractions.</p>
                                
                                <p>The hotel features well-appointed rooms designed for comfort and relaxation. Whether traveling for business or leisure, guests will appreciate the attentive service and quality facilities.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Available Rooms -->
                    <div class="available-rooms-column">
                        <div class="rooms-header">
                            <h2>Available Rooms</h2>
                            <p class="stay-info">For <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>, <?php echo $adults; ?> guest<?php echo $adults > 1 ? 's' : ''; ?></p>
                        </div>
                        
                        <?php if (!empty($rooms['Rooms']) && is_array($rooms['Rooms'])): ?>
                            <div class="room-list">
                                <?php foreach($rooms['Rooms'] as $roomIndex => $room): 
                                    // Get room name
                                    $roomName = isset($room['RoomName']) ? $room['RoomName'] : 'Standard Room';
                                    
                                    // Use a short name version if the original is too long
                                    $displayName = (strlen($roomName) > 40) ? substr($roomName, 0, 38) . '...' : $roomName;
                                    
                                    // Get meal type
                                    $roomMealType = isset($room['MealType']) ? $room['MealType'] : 'Room Only';
                                    // Convert technical meal types to user-friendly names
                                    switch($roomMealType) {
                                        case 'Room_Only': $roomMealType = 'Room Only'; break;
                                        case 'BreakFast': $roomMealType = 'Breakfast Included'; break;
                                        case 'Half_Board': $roomMealType = 'Half Board'; break;
                                        case 'Full_Board': $roomMealType = 'Full Board'; break;
                                        case 'All_Inclusive': $roomMealType = 'All Inclusive'; break;
                                    }
                                    
                                    // Get price
                                    $roomPrice = isset($room['Price']) ? floatval($room['Price']) : 0;
                                    $roomCurrency = isset($room['Currency']) ? $room['Currency'] : 'INR';
                                    
                                    // Convert currency if needed (example: THB to INR)
                                    if ($roomCurrency == 'THB') {
                                        // Approximate conversion rate THB to INR (as of 2023)
                                        $roomPrice = $roomPrice * 2.2; // Conversion factor 
                                        $roomCurrency = 'INR';
                                    }
                                    
                                    // Calculate original price (higher) for discount display
                                    $originalPrice = $roomPrice * (1 + (mt_rand(20, 40) / 100));
                                    $discountPercent = round(($originalPrice - $roomPrice) / $originalPrice * 100);
                                    
                                    // Get inclusions
                                    $roomInclusions = isset($room['Inclusion']) ? $room['Inclusion'] : '';
                                    $inclusionsList = !empty($roomInclusions) ? explode(',', $roomInclusions) : [
                                        'Free Cancellation',
                                        'Free WiFi',
                                        'AC Room',
                                        'TV'
                                    ];
                                    
                                    // Is refundable?
                                    $isRefundable = isset($room['IsRefundable']) ? $room['IsRefundable'] : true;
                                    
                                    // Room image - use the first hotel image as default
                                    $roomImage = !empty($hotelImages) ? $hotelImages[array_rand($hotelImages)] : '';
                                    
                                    // Booking URL
                                    $bookingUrl = add_query_arg([
                                        'hotel'     => $hotel_code,
                                        'room'      => $room['RoomCode'],
                                        'check_in'  => $check_in,
                                        'check_out' => $check_out,
                                        'adults'    => $adults,
                                        'children'  => $children,
                                    ], site_url('/hotel-booking'));
                                ?>
                                <div class="room-card" id="room-<?php echo esc_attr($roomIndex); ?>">
                                    <div class="room-header">
                                        <h3 class="room-name"><?php echo esc_html($displayName); ?></h3>
                                        <div class="room-price-container">
                                            <?php if ($discountPercent > 0): ?>
                                            <div class="discount-tag"><?php echo esc_html($discountPercent); ?>% off</div>
                                            <div class="original-price">₹<?php echo esc_html(number_format($originalPrice, 0)); ?></div>
                                            <?php endif; ?>
                                            <div class="current-price">₹<?php echo esc_html(number_format($roomPrice, 0)); ?></div>
                                            <div class="price-info">
                                                +taxes & fees<br>
                                                per room per night
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="room-details">
                                        <div class="room-image">
                                            <img src="<?php echo esc_url($roomImage); ?>" alt="<?php echo esc_attr($roomName); ?>">
                                        </div>
                                        
                                        <div class="room-features">
                                            <div class="room-inclusion-row">
                                                <div class="inclusion-item meal-type">
                                                    <i class="inclusion-icon">🍽️</i>
                                                    <span><?php echo esc_html($roomMealType); ?></span>
                                                </div>
                                                
                                                <div class="inclusion-item cancellation">
                                                    <i class="inclusion-icon"><?php echo $isRefundable ? '✓' : '✗'; ?></i>
                                                    <span><?php echo $isRefundable ? 'Refundable' : 'Non-refundable'; ?></span>
                                                </div>
                                            </div>
                                            
                                            <?php if (!empty($inclusionsList)): ?>
                                            <div class="room-inclusions">
                                                <?php 
                                                // Show up to 4 inclusions
                                                $displayInclusions = array_slice($inclusionsList, 0, 4); 
                                                foreach($displayInclusions as $inclusion): 
                                                    $inclusion = trim($inclusion);
                                                    if (!empty($inclusion)):
                                                ?>
                                                <div class="inclusion-item">
                                                    <i class="inclusion-icon">✓</i>
                                                    <span><?php echo esc_html($inclusion); ?></span>
                                                </div>
                                                <?php 
                                                    endif;
                                                endforeach; 
                                                ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="room-actions">
                                            <a href="#" class="btn-room-details">Room Details</a>
                                            <a href="<?php echo esc_url($bookingUrl); ?>" class="btn-book-now">Book Now</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-rooms">
                                <p>No room information available for this hotel. Please try again later or contact us for assistance.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php
} catch (Exception $e) {
    ?>
    <div class="error-container">
        <div class="container">
            <div class="error-message">
                <h2>Error</h2>
                <p><?php echo esc_html($e->getMessage()); ?></p>
                
                <div class="error-actions">
                    <a href="javascript:history.back()" class="btn-back">Go Back</a>
                </div>
                
                <?php if (current_user_can('manage_options')): ?>
                <div class="admin-debug-info">
                    <h3>Debug Information (Admin Only)</h3>
                    <p>Error Details: <?php echo esc_html($e->getTraceAsString()); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

get_footer();
