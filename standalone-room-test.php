<?php
/**
 * Standalone Room Selection Test
 * 
 * This page doesn't rely on WordPress functions
 */

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? htmlspecialchars($_GET['hotel_code']) : 'TEST123';
$city_code = isset($_GET['city_code']) ? htmlspecialchars($_GET['city_code']) : 'CITY';
$check_in = isset($_GET['check_in']) ? htmlspecialchars($_GET['check_in']) : '2023-12-01';
$check_out = isset($_GET['check_out']) ? htmlspecialchars($_GET['check_out']) : '2023-12-05';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Calculate nights
$nights = ceil((strtotime($check_out) - strtotime($check_in)) / (60 * 60 * 24));
$nights = max(1, $nights);

// Mock room data
$mock_rooms = array(
    array(
        'RoomName' => 'Standard Room',
        'BasePrice' => 5200,
        'TotalPrice' => 5720,
        'Inclusions' => array('Free WiFi', 'Breakfast Included')
    ),
    array(
        'RoomName' => 'Deluxe Room',
        'BasePrice' => 7500,
        'TotalPrice' => 8250,
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar')
    ),
    array(
        'RoomName' => 'Executive Suite',
        'BasePrice' => 12000,
        'TotalPrice' => 13200,
        'Inclusions' => array('Free WiFi', 'Breakfast Included', 'Free Minibar', 'Airport Transfer')
    )
);

// Mock hotel information
$hotel_info = array(
    'HotelName' => 'Grand Hotel Plaza',
    'StarRating' => 4,
    'HotelAddress' => 'Via del Corso 126, Rome, Italy'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Room Selection - <?php echo $hotel_info['HotelName']; ?></title>
    <style>
        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        /* Page container */
        .hotel-rooms-page {
            padding-bottom: 40px;
        }
        
        /* Compact search header */
        .search-header-compact {
            background-color: #003580;
            padding: 15px 0;
            color: white;
            margin-bottom: 30px;
        }
        
        .search-form-compact {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .search-field {
            display: flex;
            flex-direction: column;
            margin-right: 15px;
        }
        
        .search-field label {
            font-size: 12px;
            margin-bottom: 5px;
        }
        
        .search-field input {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            background-color: #fff;
            color: #333;
            min-width: 120px;
        }
        
        .modify-search-btn {
            background-color: #0071c2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            font-weight: bold;
            align-self: flex-end;
        }
        
        .hotel-rooms-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        
        /* Hotel summary */
        .hotel-summary {
            margin-bottom: 30px;
            border-bottom: 1px solid #e7e7e7;
            padding-bottom: 20px;
        }
        
        .hotel-name {
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        
        .hotel-rating {
            margin-bottom: 10px;
        }
        
        .star {
            color: #febb02;
            margin-right: 2px;
        }
        
        /* Rooms selection */
        .rooms-selection-header {
            margin-bottom: 20px;
        }
        
        .rooms-selection-header h2 {
            margin: 0 0 5px 0;
        }
        
        .room-card {
            display: flex;
            margin-bottom: 20px;
            border: 1px solid #e7e7e7;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .room-info-section {
            flex: 3;
            padding: 20px;
        }
        
        .room-name {
            font-size: 18px;
            margin: 0 0 15px 0;
        }
        
        .room-inclusions {
            margin-bottom: 15px;
        }
        
        .inclusion-item {
            display: flex;
            align-items: center;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .inclusion-check {
            color: #008009;
            margin-right: 5px;
        }
        
        .room-pricing-section {
            flex: 1;
            background-color: #f9f9f9;
            padding: 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .pricing-details {
            text-align: right;
        }
        
        .current-price {
            font-size: 24px;
            font-weight: bold;
            color: #d80027;
            margin-bottom: 5px;
        }
        
        .price-per-night {
            font-size: 12px;
            color: #666;
        }
        
        .total-price-container {
            border-top: 1px dashed #e7e7e7;
            margin-top: 15px;
            padding-top: 15px;
        }
        
        .select-room-btn {
            background-color: #0071c2;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 12px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 15px;
        }
        
        .debug-section {
            background-color: #f5f5f5;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0073aa;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .room-card {
                flex-direction: column;
            }
            
            .room-pricing-section {
                border-top: 1px solid #e7e7e7;
            }
        }
    </style>
</head>
<body>
    <div class="hotel-rooms-page">
        <!-- Compact Search Header -->
        <div class="search-header-compact">
            <div class="container">
                <div class="search-form-compact">
                    <div class="search-field">
                        <label>Destination</label>
                        <input type="text" value="<?php echo $city_code; ?>" readonly>
                    </div>
                    <div class="search-field">
                        <label>Check-in</label>
                        <input type="text" value="<?php echo $check_in; ?>" readonly>
                    </div>
                    <div class="search-field">
                        <label>Check-out</label>
                        <input type="text" value="<?php echo $check_out; ?>" readonly>
                    </div>
                    <div class="search-field">
                        <label>Guests</label>
                        <input type="text" value="<?php echo $adults; ?> Adults<?php echo $children > 0 ? ', ' . $children . ' Children' : ''; ?>" readonly>
                    </div>
                    <button class="modify-search-btn">Modify Search</button>
                </div>
            </div>
        </div>
        
        <div class="container">
            <!-- Debug Section -->
            <div class="debug-section">
                <h3>Debug Information</h3>
                <p><strong>Hotel Code:</strong> <?php echo $hotel_code; ?></p>
                <p><strong>City Code:</strong> <?php echo $city_code; ?></p>
                <p><strong>Check In:</strong> <?php echo $check_in; ?></p>
                <p><strong>Check Out:</strong> <?php echo $check_out; ?></p>
                <p><strong>Adults:</strong> <?php echo $adults; ?></p>
                <p><strong>Children:</strong> <?php echo $children; ?></p>
                <p><strong>Rooms:</strong> <?php echo $rooms; ?></p>
                <p><strong>Nights:</strong> <?php echo $nights; ?></p>
                <p><strong>Script Path:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
            </div>
            
            <div class="hotel-rooms-container">
                <!-- Hotel Summary Section -->
                <div class="hotel-summary">
                    <h1 class="hotel-name"><?php echo $hotel_info['HotelName']; ?></h1>
                    
                    <div class="hotel-rating">
                        <?php for ($i = 0; $i < $hotel_info['StarRating']; $i++): ?>
                            <span class="star">★</span>
                        <?php endfor; ?>
                    </div>
                    
                    <div class="hotel-location">
                        <span><?php echo $hotel_info['HotelAddress']; ?></span>
                    </div>
                </div>
                
                <!-- Rooms Selection -->
                <div class="rooms-selection-header">
                    <h2>Available Rooms</h2>
                    <p>Select from <?php echo count($mock_rooms); ?> available room types</p>
                </div>
                
                <!-- Room Cards -->
                <?php foreach ($mock_rooms as $index => $room): 
                    $total_price = $room['BasePrice'] * $nights;
                ?>
                <div class="room-card">
                    <div class="room-info-section">
                        <h3 class="room-name"><?php echo $room['RoomName']; ?></h3>
                        
                        <div class="room-inclusions">
                            <?php foreach ($room['Inclusions'] as $inclusion): ?>
                            <div class="inclusion-item">
                                <span class="inclusion-check">✓</span>
                                <span><?php echo $inclusion; ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="room-capacity">
                            <div class="capacity-item">
                                <span>Max <?php echo $adults; ?> Adults</span>
                            </div>
                            <?php if ($children > 0): ?>
                            <div class="capacity-item">
                                <span>Max <?php echo $children; ?> Children</span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="room-pricing-section">
                        <div class="pricing-details">
                            <div class="current-price">₹<?php echo number_format($room['BasePrice'], 0); ?></div>
                            <div class="price-per-night">per room per night</div>
                            
                            <div class="total-price-container">
                                <div class="total-price-label">Total for <?php echo $nights; ?> night<?php echo $nights > 1 ? 's' : ''; ?></div>
                                <div class="current-price">₹<?php echo number_format($total_price, 0); ?></div>
                                <div class="price-per-night">+taxes & fees</div>
                            </div>
                        </div>
                        
                        <button class="select-room-btn" data-room-index="<?php echo $index; ?>">
                            Select Room
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="hotel-rooms-container">
                <h2>Need Help?</h2>
                <p>If you're experiencing issues with the room selection page, please try these steps:</p>
                <ol>
                    <li>Make sure you have the latest version of the TBO Hotels theme activated</li>
                    <li>Ensure all required files are present in the correct locations</li>
                    <li>Check PHP error logs for any issues</li>
                    <li>Try accessing the <a href="tbo-diagnostic.php">diagnostics page</a> to verify function availability</li>
                </ol>
                
                <h3>Test Links</h3>
                <ul>
                    <li><a href="hotel-room-selection.php?hotel_code=5678&city_code=BOM&check_in=2023-12-10&check_out=2023-12-15&adults=2&children=1&rooms=1">Standard Room Selection Page</a></li>
                    <li><a href="hotel-room-selection.php?hotel_code=5678&city_code=BOM&check_in=2023-12-10&check_out=2023-12-15&adults=2&children=1&rooms=1&debug=1">Room Selection Page with Debug Info</a></li>
                    <li><a href="test-room-buttons.html">Test Room Buttons Page</a></li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Standalone room selection test page loaded');
            
            // Add event listeners to all select room buttons
            var buttons = document.querySelectorAll('.select-room-btn');
            buttons.forEach(function(button) {
                button.addEventListener('click', function() {
                    alert('Room selected! In a real implementation, this would proceed to the booking page.');
                });
            });
            
            // Add event listener to modify search button
            var modifyBtn = document.querySelector('.modify-search-btn');
            if (modifyBtn) {
                modifyBtn.addEventListener('click', function() {
                    window.location.href = 'index.php';
                });
            }
        });
    </script>
</body>
</html>