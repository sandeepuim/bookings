<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Button Test</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
        h1, h2 {
            color: #333;
        }
    </style>
</head>
<body>
    <h1>TBO Hotels Button Test</h1>
    
    <p>This page tests the "Choose Room" button functionality without WordPress integration.</p>
    
    <div class="hotel-card">
        <div class="hotel-name">Grand Hotel Plaza</div>
        <p>Location: Rome, Italy</p>
        <p>Price: ₹5,720 per night</p>
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
    
    <div class="hotel-card">
        <div class="hotel-name">Luxury Palace Hotel</div>
        <p>Location: Mumbai, India</p>
        <p>Price: ₹8,250 per night</p>
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
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Simple button fix
        var buttons = document.querySelectorAll('.choose-room-btn');
        
        buttons.forEach(function(button) {
            button.addEventListener('click', function() {
                // Get data attributes
                var hotelCode = this.getAttribute('data-hotel-code');
                var cityCode = this.getAttribute('data-city-code');
                var checkIn = this.getAttribute('data-check-in');
                var checkOut = this.getAttribute('data-check-out');
                var adults = this.getAttribute('data-adults');
                var children = this.getAttribute('data-children');
                
                // Build URL for room selection
                var url = 'simple-room-selection.php';
                url += '?hotel_code=' + encodeURIComponent(hotelCode || '');
                url += '&city_code=' + encodeURIComponent(cityCode || '');
                url += '&check_in=' + encodeURIComponent(checkIn || '');
                url += '&check_out=' + encodeURIComponent(checkOut || '');
                url += '&adults=' + encodeURIComponent(adults || '2');
                url += '&children=' + encodeURIComponent(children || '0');
                
                // Navigate to room selection page
                window.location.href = url;
            });
        });
    });
    </script>
</body>
</html>