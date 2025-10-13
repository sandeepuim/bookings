<?php
/**
 * TBO Hotels Button Fix - Direct Results Page Injection
 * 
 * This script directly modifies the hotel results page to fix the "Choose Room" buttons.
 */

// Basic settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Include WordPress core if available (for functions like site_url)
$wp_loaded = false;
if (file_exists('./wp-load.php')) {
    require_once('./wp-load.php');
    $wp_loaded = function_exists('wp_head');
}

// Function to get site URL
function get_site_url_safe() {
    if (function_exists('site_url')) {
        return site_url();
    }
    
    // Fallback: determine site URL from request
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    
    // Remove /wp-admin or similar from the path
    $base_path = preg_replace('#/wp-.*$#', '', $script_name);
    
    return $protocol . $host . $base_path;
}

// Get room selection page URL
$room_selection_url = get_site_url_safe() . '/simple-room-selection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel Results - Button Fix</title>
    <style>
        /* Basic styling */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .fix-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003580;
            color: white;
            padding: 15px 0;
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .site-title {
            font-size: 22px;
            margin: 0;
        }
        .site-title a {
            color: white;
            text-decoration: none;
        }
        .hotel-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .hotel-card {
            background-color: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .hotel-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15);
        }
        .hotel-image {
            width: 100%;
            height: 180px;
            background-color: #eee;
            background-image: url('https://via.placeholder.com/400x200?text=Hotel+Image');
            background-size: cover;
            background-position: center;
        }
        .hotel-details {
            padding: 15px;
        }
        .hotel-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .hotel-location {
            color: #666;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .hotel-rating {
            color: #ffc107;
            margin-bottom: 12px;
        }
        .hotel-price {
            font-size: 20px;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 15px;
        }
        .choose-room-btn {
            display: block;
            width: 100%;
            padding: 10px;
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
        .choose-room-btn:hover {
            background-color: #d32f2f;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1 class="site-title"><a href="<?php echo get_site_url_safe(); ?>">TBO Hotels</a></h1>
            <div>
                <a href="button-debug.php" style="color: white; margin-right: 15px;">Debug Tools</a>
                <a href="simple-room-selection.php" style="color: white;">Room Selection</a>
            </div>
        </div>
    </div>
    
    <div class="fix-container">
        <h2>Hotel Results with Fixed Button Implementation</h2>
        <p>This is a demonstration page with fixed "Choose Room" buttons that will work correctly.</p>
        
        <div class="hotel-list">
            <!-- Hotel 1 -->
            <div class="hotel-card" data-hotel-code="12345" data-city-code="150184">
                <div class="hotel-image"></div>
                <div class="hotel-details">
                    <div class="hotel-name">Grand Hotel Plaza</div>
                    <div class="hotel-location">Rome, Italy</div>
                    <div class="hotel-rating">★★★★☆ 4.2/5</div>
                    <div class="hotel-price">₹5,720 per night</div>
                    <button class="choose-room-btn" 
                            data-hotel-code="12345" 
                            data-city-code="150184"
                            data-check-in="2025-09-20"
                            data-check-out="2025-09-25"
                            data-adults="2"
                            data-children="0">
                        Choose Room
                    </button>
                </div>
            </div>
            
            <!-- Hotel 2 -->
            <div class="hotel-card" data-hotel-code="67890" data-city-code="150185">
                <div class="hotel-image"></div>
                <div class="hotel-details">
                    <div class="hotel-name">Luxury Palace Hotel</div>
                    <div class="hotel-location">Mumbai, India</div>
                    <div class="hotel-rating">★★★★★ 4.8/5</div>
                    <div class="hotel-price">₹8,250 per night</div>
                    <button class="choose-room-btn" 
                            data-hotel-code="67890" 
                            data-city-code="150185"
                            data-check-in="2025-09-20"
                            data-check-out="2025-09-25"
                            data-adults="2"
                            data-children="1">
                        Choose Room
                    </button>
                </div>
            </div>
            
            <!-- Hotel 3 -->
            <div class="hotel-card" data-hotel-code="24680" data-city-code="150186">
                <div class="hotel-image"></div>
                <div class="hotel-details">
                    <div class="hotel-name">Seaside Resort</div>
                    <div class="hotel-location">Goa, India</div>
                    <div class="hotel-rating">★★★★☆ 4.0/5</div>
                    <div class="hotel-price">₹6,500 per night</div>
                    <button class="choose-room-btn" 
                            data-hotel-code="24680" 
                            data-city-code="150186"
                            data-check-in="2025-09-20"
                            data-check-out="2025-09-25"
                            data-adults="2"
                            data-children="0">
                        Choose Room
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Button Fix Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('TBO Hotels - Button Fix Script Running');
        
        // Find all Choose Room buttons
        var buttons = document.querySelectorAll('.choose-room-btn');
        console.log('Found ' + buttons.length + ' Choose Room buttons');
        
        // Fix each button
        buttons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                // Prevent default action
                e.preventDefault();
                
                // Get data from the button
                var hotelCode = this.getAttribute('data-hotel-code');
                var cityCode = this.getAttribute('data-city-code');
                var checkIn = this.getAttribute('data-check-in');
                var checkOut = this.getAttribute('data-check-out');
                var adults = this.getAttribute('data-adults');
                var children = this.getAttribute('data-children');
                var rooms = this.getAttribute('data-rooms') || '1';
                
                console.log('Button clicked for hotel:', hotelCode);
                
                // Build URL for room selection
                var url = '<?php echo $room_selection_url; ?>';
                url += '?hotel_code=' + encodeURIComponent(hotelCode);
                url += '&city_code=' + encodeURIComponent(cityCode);
                url += '&check_in=' + encodeURIComponent(checkIn);
                url += '&check_out=' + encodeURIComponent(checkOut);
                url += '&adults=' + encodeURIComponent(adults || '2');
                url += '&children=' + encodeURIComponent(children || '0');
                url += '&rooms=' + encodeURIComponent(rooms || '1');
                
                console.log('Navigating to:', url);
                
                // Navigate to the room selection page
                window.location.href = url;
            });
        });
    });
    </script>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>