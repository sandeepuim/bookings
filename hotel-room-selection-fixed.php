<?php
/**
 * Hotel Room Selection Page - Fixed Version
 * 
 * This is a standalone room selection page that works without WordPress integration
 * if necessary, but will use WordPress template parts if available.
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Try to load WordPress
$wp_loaded = false;
if (file_exists('./wp-load.php')) {
    require_once('./wp-load.php');
    $wp_loaded = function_exists('wp_head');
}

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Check if our safe function exists
$use_safe_function = function_exists('tbo_safe_get_room_details');

// If our safe function doesn't exist, try to include it
if (!$use_safe_function && file_exists('./wp-content/themes/twentytwentyone/tbo-room-functions-fixed.php')) {
    require_once('./wp-content/themes/twentytwentyone/tbo-room-functions-fixed.php');
    $use_safe_function = function_exists('tbo_safe_get_room_details');
}

// Fallback function if nothing else is available
if (!$use_safe_function && !function_exists('tbo_hotels_get_room_details')) {
    // Define a local fallback function
    function local_fallback_room_details($params) {
        // Mock hotel information
        $hotel_info = array(
            'HotelName' => 'Grand Hotel Plaza (Local Fallback)',
            'HotelCode' => $params['hotel_code'] ?? '12345',
            'StarRating' => 4,
            'HotelAddress' => 'Via del Corso 126, Rome, Italy',
            'HotelFacilities' => array('Free WiFi', 'Restaurant', 'Pool')
        );
        
        // Just create one room type to simplify
        $rooms = array(
            array(
                'RoomIndex' => 1,
                'RoomName' => 'Standard Room',
                'RoomTypeCode' => 'STD',
                'RoomDescription' => 'Comfortable standard room with modern amenities',
                'Inclusions' => array('Free WiFi', 'Breakfast Included'),
                'DayRates' => array(
                    array(
                        array(
                            'BasePrice' => 5000,
                            'Tax' => 500,
                            'TotalPrice' => 5500
                        )
                    )
                )
            )
        );
        
        return array(
            'HotelInfo' => $hotel_info,
            'Rooms' => $rooms
        );
    }
}

// Get room details using the most appropriate function available
$room_data = $use_safe_function ? 
    tbo_safe_get_room_details(array(
    'hotel_code' => $hotel_code,
    'city_code' => $city_code,
    'check_in' => $check_in,
    'check_out' => $check_out,
    'adults' => $adults,
    'children' => $children,
    'rooms' => $rooms
));

// Extract hotel information
$hotel_info = isset($room_data['HotelInfo']) ? $room_data['HotelInfo'] : array();
$hotel_rooms = isset($room_data['Rooms']) ? $room_data['Rooms'] : array();

// Calculate dates
$check_in_date = !empty($check_in) ? new DateTime($check_in) : new DateTime();
$check_out_date = !empty($check_out) ? new DateTime($check_out) : new DateTime('+1 day');
$nights = $check_in_date->diff($check_out_date)->days;

// Start output
if ($wp_loaded) {
    get_header();
} else {
    // Basic HTML structure if WordPress is not available
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Hotel Room Selection - <?php echo htmlspecialchars($hotel_info['HotelName'] ?? 'Unknown Hotel'); ?></title>
        <style>
            /* Basic styling for the room selection page */
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .room-selection-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
            }
            .hotel-info {
                margin-bottom: 30px;
                padding: 20px;
                background-color: #fff;
                border-radius: 5px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .hotel-name {
                font-size: 24px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .hotel-address {
                color: #666;
                margin-bottom: 15px;
            }
            .star-rating {
                color: #ffc107;
                margin-bottom: 15px;
            }
            .facility-badge {
                display: inline-block;
                margin-right: 8px;
                margin-bottom: 8px;
                padding: 4px 10px;
                background-color: #e1f5fe;
                border-radius: 20px;
                color: #0288d1;
                font-size: 14px;
            }
            .room-list {
                display: flex;
                flex-wrap: wrap;
                gap: 20px;
            }
            .room-card {
                flex: 1 0 300px;
                border-radius: 5px;
                background-color: #fff;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                overflow: hidden;
                transition: transform 0.3s, box-shadow 0.3s;
            }
            .room-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
            }
            .room-image {
                width: 100%;
                height: 200px;
                background-color: #eee;
                background-image: url('https://via.placeholder.com/400x200?text=Room+Image');
                background-size: cover;
                background-position: center;
            }
            .room-details {
                padding: 20px;
            }
            .room-name {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            .room-description {
                margin-bottom: 15px;
                color: #666;
                font-size: 14px;
            }
            .price-section {
                margin: 15px 0;
            }
            .price-amount {
                font-size: 24px;
                font-weight: bold;
                color: #2e7d32;
            }
            .price-details {
                font-size: 14px;
                color: #666;
            }
            .inclusions {
                margin: 15px 0;
            }
            .inclusion-item {
                display: inline-block;
                margin-right: 10px;
                margin-bottom: 5px;
                background-color: #f1f8e9;
                color: #689f38;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 14px;
            }
            .book-button {
                display: block;
                width: 100%;
                padding: 12px;
                background-color: #f44336;
                color: white;
                border: none;
                border-radius: 4px;
                font-size: 16px;
                font-weight: bold;
                cursor: pointer;
                text-align: center;
                text-decoration: none;
                transition: background-color 0.3s;
            }
            .book-button:hover {
                background-color: #d32f2f;
            }
            .header-bar {
                background-color: #003580;
                padding: 15px 0;
                color: white;
            }
            .header-container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }
            .site-title {
                font-size: 20px;
                margin: 0;
            }
            .site-title a {
                color: white;
                text-decoration: none;
            }
            @media (max-width: 768px) {
                .room-list {
                    flex-direction: column;
                }
                .room-card {
                    flex: none;
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <header class="header-bar">
            <div class="header-container">
                <h1 class="site-title"><a href="./">Hotel Booking System</a></h1>
            </div>
        </header>
    <?php
}
?>

<div class="room-selection-container">
    <div class="hotel-info">
        <div class="hotel-name"><?php echo htmlspecialchars($hotel_info['HotelName'] ?? 'Unknown Hotel'); ?></div>
        <div class="hotel-address"><?php echo htmlspecialchars($hotel_info['HotelAddress'] ?? ''); ?></div>
        
        <div class="star-rating">
            <?php 
            $rating = isset($hotel_info['StarRating']) ? intval($hotel_info['StarRating']) : 0;
            for ($i = 0; $i < $rating; $i++) {
                echo '★';
            }
            echo ' ' . $rating . '-Star Hotel';
            ?>
        </div>
        
        <div class="booking-details">
            <strong>Check-in:</strong> <?php echo $check_in_date->format('M d, Y'); ?> | 
            <strong>Check-out:</strong> <?php echo $check_out_date->format('M d, Y'); ?> | 
            <strong>Duration:</strong> <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?> | 
            <strong>Guests:</strong> <?php echo $adults; ?> adult<?php echo $adults > 1 ? 's' : ''; ?>
            <?php if ($children > 0): ?>
                , <?php echo $children; ?> child<?php echo $children > 1 ? 'ren' : ''; ?>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($hotel_info['HotelFacilities'])): ?>
            <div style="margin-top: 15px;">
                <strong>Hotel Facilities:</strong>
                <div style="margin-top: 8px;">
                    <?php foreach ($hotel_info['HotelFacilities'] as $facility): ?>
                        <span class="facility-badge"><?php echo htmlspecialchars($facility); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <h2>Available Rooms</h2>
    
    <?php if (empty($hotel_rooms)): ?>
        <div style="text-align: center; padding: 30px; background-color: #f9f9f9; border-radius: 5px;">
            <p style="font-size: 18px; color: #666;">No rooms are currently available for the selected dates.</p>
            <p>Please try different dates or contact the hotel directly.</p>
        </div>
    <?php else: ?>
        <div class="room-list">
            <?php foreach ($hotel_rooms as $room): ?>
                <div class="room-card">
                    <div class="room-image"></div>
                    <div class="room-details">
                        <h3 class="room-name"><?php echo htmlspecialchars($room['RoomName']); ?></h3>
                        
                        <?php if (!empty($room['RoomDescription'])): ?>
                            <div class="room-description"><?php echo htmlspecialchars($room['RoomDescription']); ?></div>
                        <?php endif; ?>
                        
                        <?php 
                        // Calculate price
                        $price = 0;
                        $tax = 0;
                        $total = 0;
                        
                        if (isset($room['DayRates'][0][0])) {
                            $rate = $room['DayRates'][0][0];
                            $price = isset($rate['BasePrice']) ? $rate['BasePrice'] : 0;
                            $tax = isset($rate['Tax']) ? $rate['Tax'] : 0;
                            $total = isset($rate['TotalPrice']) ? $rate['TotalPrice'] : ($price + $tax);
                        }
                        ?>
                        
                        <div class="price-section">
                            <div class="price-amount">₹<?php echo number_format($total); ?></div>
                            <div class="price-details">
                                Price for <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?>
                                <br>
                                Base price: ₹<?php echo number_format($price); ?> + 
                                Tax: ₹<?php echo number_format($tax); ?>
                            </div>
                        </div>
                        
                        <?php if (!empty($room['Inclusions'])): ?>
                            <div class="inclusions">
                                <strong>Inclusions:</strong>
                                <div style="margin-top: 8px;">
                                    <?php foreach ($room['Inclusions'] as $inclusion): ?>
                                        <span class="inclusion-item"><?php echo htmlspecialchars($inclusion); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <a href="#" class="book-button" 
                           data-room-index="<?php echo $room['RoomIndex']; ?>"
                           data-room-type="<?php echo htmlspecialchars($room['RoomTypeCode']); ?>">
                            Book Now
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($wp_loaded): ?>
    <?php get_footer(); ?>
<?php else: ?>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Book Now button functionality
        var bookButtons = document.querySelectorAll('.book-button');
        bookButtons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                
                var roomIndex = this.getAttribute('data-room-index');
                var roomType = this.getAttribute('data-room-type');
                
                alert('Booking room: ' + roomType + ' (Index: ' + roomIndex + ')\n\nIn a real implementation, this would proceed to the booking form.');
            });
        });
    });
    </script>
    </body>
    </html>
<?php endif; ?>