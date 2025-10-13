<?php
/**
 * Choose Room Button Debug
 * 
 * This script helps debug the "Choose Room" button functionality.
 */

// Basic setup
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load WordPress core if available
$wp_loaded = false;
if (file_exists('./wp-load.php')) {
    require_once('./wp-load.php');
    $wp_loaded = function_exists('wp_head');
}

// Check if button fixing scripts are loaded properly
$script_paths = [
    './wp-content/themes/twentytwentyone/direct-button-fix.php',
    './wp-content/themes/twentytwentyone/hotel-button-enhancement.php',
    './wp-content/themes/twentytwentyone/tbo-room-functions.php',
];

// Debug header
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choose Room Button Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #0066cc;
        }
        .debug-section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 5px solid #0066cc;
        }
        .test-section {
            margin-top: 30px;
            padding: 20px;
            background: #eef6ff;
            border-radius: 5px;
        }
        .status-ok {
            color: #2e7d32;
            font-weight: bold;
        }
        .status-error {
            color: #c62828;
            font-weight: bold;
        }
        .code-block {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 3px;
            overflow: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .hotel-card {
            border: 1px solid #ddd;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            display: inline-block;
            text-decoration: none;
        }
        .btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>Choose Room Button Debug</h1>
    
    <div class="debug-section">
        <h2>Environment Check</h2>
        <p>WordPress loaded: <span class="<?php echo $wp_loaded ? 'status-ok' : 'status-error'; ?>">
            <?php echo $wp_loaded ? 'Yes' : 'No'; ?>
        </span></p>
        
        <h3>Script Files:</h3>
        <ul>
            <?php foreach($script_paths as $path): ?>
                <li>
                    <?php echo $path; ?>: 
                    <span class="<?php echo file_exists($path) ? 'status-ok' : 'status-error'; ?>">
                        <?php echo file_exists($path) ? 'Found' : 'Not Found'; ?>
                    </span>
                    <?php if(file_exists($path)): ?>
                        <br>Size: <?php echo filesize($path); ?> bytes
                        <br>Last modified: <?php echo date("Y-m-d H:i:s", filemtime($path)); ?>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="debug-section">
        <h2>PHP Info</h2>
        <p>PHP Version: <?php echo phpversion(); ?></p>
        <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
        <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
    </div>
    
    <div class="test-section">
        <h2>Test Button Implementation</h2>
        
        <div class="hotel-card">
            <h3>Test Hotel</h3>
            <p>This is a test hotel for debugging the Choose Room button functionality.</p>
            
            <h4>Test 1: Direct URL Button</h4>
            <a href="simple-room-selection.php?hotel_code=12345&city_code=150184&check_in=2025-09-20&check_out=2025-09-25&adults=2&children=0&rooms=1" class="btn">
                Choose Room (Direct URL)
            </a>
            
            <h4>Test 2: Button with Data Attributes</h4>
            <button class="choose-room-btn btn" 
                data-hotel-code="12345" 
                data-city-code="150184" 
                data-check-in="2025-09-20"
                data-check-out="2025-09-25"
                data-adults="2"
                data-children="0"
                data-rooms="1">
                Choose Room (With Data Attributes)
            </button>
            
            <h4>Test 3: Form Submit Button</h4>
            <form action="simple-room-selection.php" method="get">
                <input type="hidden" name="hotel_code" value="12345">
                <input type="hidden" name="city_code" value="150184">
                <input type="hidden" name="check_in" value="2025-09-20">
                <input type="hidden" name="check_out" value="2025-09-25">
                <input type="hidden" name="adults" value="2">
                <input type="hidden" name="children" value="0">
                <input type="hidden" name="rooms" value="1">
                <button type="submit" class="btn">Choose Room (Form Submit)</button>
            </form>
        </div>
    </div>

    <!-- Test console output -->
    <div class="debug-section">
        <h2>Console Output</h2>
        <p>Open your browser's developer console to see debug messages.</p>
        <div class="code-block" id="consoleOutput">Console messages will appear in your browser's developer tools console.</div>
    </div>

    <!-- Include our scripts -->
    <?php if($wp_loaded): ?>
        <?php 
        // If WordPress is loaded, the scripts should be included through the functions.php file
        // Just add a note here
        ?>
        <div class="debug-section">
            <p class="status-ok">WordPress is loaded. Button enhancement scripts should be included automatically through functions.php.</p>
        </div>
    <?php else: ?>
        <!-- Manually include the scripts if WordPress is not loaded -->
        <?php foreach($script_paths as $path): ?>
            <?php if(file_exists($path)): ?>
                <?php include_once($path); ?>
                <div class="debug-section">
                    <p class="status-ok">Manually included: <?php echo $path; ?></p>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Add direct implementation of the button fix to ensure it works -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Debug script loaded');
        
        // Test direct implementation of button fix
        var buttons = document.querySelectorAll('.choose-room-btn');
        console.log('Found ' + buttons.length + ' "Choose Room" buttons');
        
        buttons.forEach(function(button) {
            console.log('Setting up button', button);
            
            button.addEventListener('click', function(e) {
                // Prevent default action
                e.preventDefault();
                
                // Get data attributes
                var hotelCode = this.getAttribute('data-hotel-code');
                var cityCode = this.getAttribute('data-city-code');
                var checkIn = this.getAttribute('data-check-in');
                var checkOut = this.getAttribute('data-check-out');
                var adults = this.getAttribute('data-adults');
                var children = this.getAttribute('data-children');
                var rooms = this.getAttribute('data-rooms');
                
                console.log('Button clicked with data:', {
                    hotelCode: hotelCode,
                    cityCode: cityCode,
                    checkIn: checkIn,
                    checkOut: checkOut,
                    adults: adults,
                    children: children,
                    rooms: rooms
                });
                
                // Build URL
                var url = 'hotel-room-selection.php';
                url += '?hotel_code=' + encodeURIComponent(hotelCode || '');
                url += '&city_code=' + encodeURIComponent(cityCode || '');
                url += '&check_in=' + encodeURIComponent(checkIn || '');
                url += '&check_out=' + encodeURIComponent(checkOut || '');
                url += '&adults=' + encodeURIComponent(adults || '2');
                url += '&children=' + encodeURIComponent(children || '0');
                url += '&rooms=' + encodeURIComponent(rooms || '1');
                
                console.log('Navigating to:', url);
                
                // Add a brief delay for console messages to be seen
                setTimeout(function() {
                    window.location.href = url;
                }, 300);
            });
            
            console.log('Button setup complete');
        });
    });
    </script>
</body>
</html>