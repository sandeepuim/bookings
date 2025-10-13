<?php
/**
 * Simple Hotel Room Selection Page
 * 
 * A standalone room selection page that doesn't depend on WordPress functions
 */

// Error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? htmlspecialchars($_GET['hotel_code']) : '12345';
$city_code = isset($_GET['city_code']) ? htmlspecialchars($_GET['city_code']) : '150184';
$check_in = isset($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : date('Y-m-d', strtotime('+1 day'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Sample hotel data
$hotel_info = array(
    'HotelName' => 'Grand Hotel Plaza',
    'HotelCode' => $hotel_code,
    'StarRating' => 4,
    'HotelAddress' => 'Via del Corso 126, Rome, Italy',
    'HotelFacilities' => array('Free WiFi', 'Restaurant', 'Pool', 'Spa', 'Gym', 'Airport Shuttle')
);

// Sample room data
$hotel_rooms = array(
    array(
        'RoomIndex' => 1,
        'RoomName' => 'Standard Room',
        'RoomTypeCode' => 'STD',
        'RoomDescription' => 'Comfortable standard room with modern amenities',
        'Inclusions' => array('Free WiFi', 'Breakfast Included'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 5200,
                    'Tax' => 520,
                    'TotalPrice' => 5720
                )
            )
        )
    ),
    array(
        'RoomIndex' => 2,
        'RoomName' => 'Deluxe Room',
        'RoomTypeCode' => 'DLX',
        'RoomDescription' => 'Spacious deluxe room with city view',
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 7500,
                    'Tax' => 750,
                    'TotalPrice' => 8250
                )
            )
        )
    ),
    array(
        'RoomIndex' => 3,
        'RoomName' => 'Executive Suite',
        'RoomTypeCode' => 'EXSUITE',
        'RoomDescription' => 'Luxurious suite with separate living area',
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar', 'Airport Transfer'),
        'DayRates' => array(
            array(
                array(
                    'BasePrice' => 12000,
                    'Tax' => 1200,
                    'TotalPrice' => 13200
                )
            )
        )
    )
);

// Calculate dates and stay details
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$nights = $check_in_date->diff($check_out_date)->days;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Room Selection - <?php echo htmlspecialchars($hotel_info['HotelName']); ?></title>
    <style>
        /* Basic styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
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

    <div class="room-selection-container">
        <div class="hotel-info">
            <div class="hotel-name"><?php echo htmlspecialchars($hotel_info['HotelName']); ?></div>
            <div class="hotel-address"><?php echo htmlspecialchars($hotel_info['HotelAddress']); ?></div>
            
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