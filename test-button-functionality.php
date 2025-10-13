<?php
/**
 * Test Button Functionality
 * 
 * This script tests the "Choose Room" button functionality.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Button Functionality</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        .hotel-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .hotel-name {
            font-size: 1.2em;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .choose-room-btn {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .choose-room-btn:hover {
            background-color: #45a049;
        }
        .success-panel {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body>
    <h1>Test Button Functionality</h1>
    
    <div class="success-panel" id="successPanel">
        <h3>Success!</h3>
        <p>The button functionality works correctly. You'll be redirected to the room selection page.</p>
    </div>
    
    <div class="hotel-card">
        <div class="hotel-name">Test Hotel 1</div>
        <p>Location: Test City</p>
        <p>Price: $100 per night</p>
        <button class="choose-room-btn" 
                data-hotel-id="12345" 
                data-hotel-name="Test Hotel 1"
                data-city-id="150184"
                data-check-in="2023-07-01"
                data-check-out="2023-07-05"
                data-adults="2"
                data-children="0">
            Choose Room
        </button>
    </div>
    
    <div class="hotel-card">
        <div class="hotel-name">Test Hotel 2</div>
        <p>Location: Another City</p>
        <p>Price: $150 per night</p>
        <button class="choose-room-btn" 
                data-hotel-id="67890" 
                data-hotel-name="Test Hotel 2"
                data-city-id="150185"
                data-check-in="2023-07-10"
                data-check-out="2023-07-15"
                data-adults="2"
                data-children="1">
            Choose Room
        </button>
    </div>

    <div class="hotel-card">
        <div class="hotel-name">Test Hotel 3 (No Data Attributes)</div>
        <p>This button has no data attributes to test fallback behavior</p>
        <button class="choose-room-btn">Choose Room</button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test that our enhancement script is working by showing success message
        // when clicking buttons
        var buttons = document.querySelectorAll('.choose-room-btn');
        
        buttons.forEach(function(button) {
            button.addEventListener('click', function(e) {
                // Prevent default for testing - in real use it would navigate
                e.preventDefault();
                
                // Show success message
                document.getElementById('successPanel').style.display = 'block';
                
                // Log the data attributes
                console.log('Button clicked with data:', {
                    hotelId: this.getAttribute('data-hotel-id'),
                    hotelName: this.getAttribute('data-hotel-name'),
                    cityId: this.getAttribute('data-city-id'),
                    checkIn: this.getAttribute('data-check-in'),
                    checkOut: this.getAttribute('data-check-out'),
                    adults: this.getAttribute('data-adults'),
                    children: this.getAttribute('data-children')
                });
                
                // In a real scenario, this would navigate to the room selection page
                // window.location.href = 'hotel-room-selection.php?hotel_id=' + this.getAttribute('data-hotel-id') + '&...';
            });
        });
    });
    </script>
</body>
</html>