<?php
/**
 * TBO Hotels Direct Hotel Code Test
 * 
 * This script tests searching for hotels by direct hotel code
 */

// Include WordPress core
require_once('../../../wp-load.php');

// Set headers for output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>TBO Hotels - Direct Hotel Code Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
        form { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="date"] { padding: 8px; width: 100%; margin-bottom: 15px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .results { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>TBO Hotels - Direct Hotel Code Test</h1>
        
        <form method="post" action="">
            <h2>Search by Hotel Code</h2>
            
            <div>
                <label for="hotel_code">Hotel Code:</label>
                <input type="text" id="hotel_code" name="hotel_code" 
                    value="<?php echo isset($_POST['hotel_code']) ? htmlspecialchars($_POST['hotel_code']) : '418069'; ?>" required>
            </div>
            
            <div>
                <label for="check_in">Check-in Date:</label>
                <input type="date" id="check_in" name="check_in" 
                    value="<?php echo isset($_POST['check_in']) ? htmlspecialchars($_POST['check_in']) : date('Y-m-d', strtotime('+7 days')); ?>" required>
            </div>
            
            <div>
                <label for="check_out">Check-out Date:</label>
                <input type="date" id="check_out" name="check_out" 
                    value="<?php echo isset($_POST['check_out']) ? htmlspecialchars($_POST['check_out']) : date('Y-m-d', strtotime('+8 days')); ?>" required>
            </div>
            
            <div>
                <label for="adults">Adults:</label>
                <input type="number" id="adults" name="adults" min="1" max="5" 
                    value="<?php echo isset($_POST['adults']) ? intval($_POST['adults']) : 1; ?>">
            </div>
            
            <div>
                <label for="rooms">Rooms:</label>
                <input type="number" id="rooms" name="rooms" min="1" max="5" 
                    value="<?php echo isset($_POST['rooms']) ? intval($_POST['rooms']) : 1; ?>">
            </div>
            
            <div>
                <label for="children">Children:</label>
                <input type="number" id="children" name="children" min="0" max="5" 
                    value="<?php echo isset($_POST['children']) ? intval($_POST['children']) : 0; ?>">
            </div>
            
            <button type="submit" name="search">Search Hotels</button>
        </form>
        
        <?php
        // Process search if form submitted
        if (isset($_POST['search']) && !empty($_POST['hotel_code'])) {
            echo '<div class="results">';
            echo '<h2>Search Results</h2>';
            
            // Collect search parameters
            $params = array(
                'hotel_code' => sanitize_text_field($_POST['hotel_code']),
                'check_in' => sanitize_text_field($_POST['check_in']),
                'check_out' => sanitize_text_field($_POST['check_out']),
                'adults' => intval($_POST['adults']),
                'rooms' => intval($_POST['rooms']),
                'children' => intval($_POST['children']),
            );
            
            echo '<h3>Search Parameters:</h3>';
            echo '<pre>' . print_r($params, true) . '</pre>';
            
            // Execute search
            $results = tbo_hotels_search_hotels($params);
            
            if (is_wp_error($results)) {
                echo '<p class="error">Error: ' . $results->get_error_message() . '</p>';
                
                // Get error details
                echo '<h3>Error Details:</h3>';
                echo '<pre>' . print_r($results, true) . '</pre>';
            } else {
                echo '<p class="success">Search successful!</p>';
                
                // Display results summary
                if (isset($results['Hotels']) && is_array($results['Hotels'])) {
                    echo '<p>Found ' . count($results['Hotels']) . ' hotels.</p>';
                    
                    // Display first hotel details as example
                    if (!empty($results['Hotels'])) {
                        echo '<h3>First Hotel Details:</h3>';
                        echo '<pre>' . print_r($results['Hotels'][0], true) . '</pre>';
                    }
                } else {
                    echo '<p>No hotels found in results.</p>';
                }
                
                // Display full results (limited for readability)
                echo '<h3>Full Results (Limited):</h3>';
                echo '<pre>' . print_r(array_slice($results, 0, 3, true), true) . '...</pre>';
            }
            
            echo '</div>';
        }
        ?>
        
        <h2>API Debugging</h2>
        <p>You can test the direct hotel search via cURL with this command:</p>
        <pre>
curl "http://localhost/bookings/wp-admin/admin-ajax.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-raw "hotel_code=418069&check_in=2025-09-22&check_out=2025-09-23&rooms=1&adults=1&children=0&action=tbo_hotels_search_hotels&nonce=REPLACE_WITH_VALID_NONCE"
        </pre>
    </div>
</body>
</html>