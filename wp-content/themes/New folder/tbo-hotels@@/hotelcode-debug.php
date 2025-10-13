<?php
/**
 * TBO Hotels API Debug Tool
 * 
 * This script directly tests the HotelCodeList API for a given city code
 */

// Include WordPress core
require_once('../../../wp-load.php');

// Set headers for output
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>TBO Hotels - Hotel Code List Debug Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 500px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        form { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] { padding: 8px; width: 100%; margin-bottom: 15px; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .results { margin-top: 20px; }
        .code-block { background: #f0f0f0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .action-buttons { margin-top: 15px; }
        .action-buttons button { margin-right: 10px; }
        .search-form { display: flex; }
        .search-form input { flex: 1; margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>TBO Hotels - Hotel Code List Debug Tool</h1>
        
        <form method="post" action="">
            <h2>Test HotelCodeList API</h2>
            
            <div>
                <label for="city_code">City Code:</label>
                <input type="text" id="city_code" name="city_code" 
                    value="<?php echo isset($_POST['city_code']) ? htmlspecialchars($_POST['city_code']) : '268880'; ?>" required>
                <p class="description">Enter the city code to test (e.g., 268880 for Mumbai)</p>
            </div>
            
            <div class="action-buttons">
                <button type="submit" name="test_api">Test API</button>
                <button type="submit" name="clear_cache">Clear Cache</button>
                <button type="submit" name="test_direct">Test Direct cURL</button>
            </div>
        </form>
        
        <?php
        // Clear cache if requested
        if (isset($_POST['clear_cache']) && !empty($_POST['city_code'])) {
            $city_code = sanitize_text_field($_POST['city_code']);
            $cache_key = 'tbo_hotels_codes_' . $city_code;
            $deleted = delete_transient($cache_key);
            
            echo '<div class="results">';
            if ($deleted) {
                echo '<p class="success">Successfully cleared cache for city code: ' . htmlspecialchars($city_code) . '</p>';
            } else {
                echo '<p class="warning">No cache found for city code: ' . htmlspecialchars($city_code) . '</p>';
            }
            echo '</div>';
        }
        
        // Process API test if requested
        if ((isset($_POST['test_api']) || isset($_POST['test_direct'])) && !empty($_POST['city_code'])) {
            echo '<div class="results">';
            $city_code = sanitize_text_field($_POST['city_code']);
            
            echo '<h2>Test Results for City Code: ' . htmlspecialchars($city_code) . '</h2>';
            
            // Start output buffering to capture error logs
            ob_start();
            
            // Modify error logging to capture to our buffer
            $old_error_log = ini_get('error_log');
            ini_set('error_log', 'php://output');
            
            try {
                // Add additional error reporting
                error_log("==== Starting hotel code test for city: $city_code ====");
                
                if (isset($_POST['test_direct'])) {
                    // Test with direct cURL
                    echo '<h3>Testing with Direct cURL Request</h3>';
                    $data = array('DestinationCode' => $city_code);
                    $response = tbo_hotels_direct_curl_request(TBO_API_BASE_URL . '/HotelCodeList', $data, 'POST');
                } else {
                    // Test with normal function
                    echo '<h3>Testing with Standard API Function</h3>';
                    $response = tbo_hotels_get_hotel_codes($city_code);
                }
                
                // Check results
                if (is_wp_error($response)) {
                    echo '<p class="error">Error: ' . $response->get_error_message() . '</p>';
                    echo '<pre>' . print_r($response, true) . '</pre>';
                } else {
                    if (is_array($response)) {
                        $count = count($response);
                        echo '<p class="success">Success! Found ' . $count . ' hotel codes.</p>';
                        
                        // Show sample of hotel codes
                        echo '<h3>Sample Hotel Codes:</h3>';
                        echo '<pre>' . print_r(array_slice($response, 0, 20), true) . '</pre>';
                        
                        // Table of hotel codes
                        echo '<h3>Hotel Codes Table (first 50):</h3>';
                        echo '<table>';
                        echo '<tr><th>#</th><th>Hotel Code</th><th>Length</th><th>Type</th></tr>';
                        
                        $i = 1;
                        foreach (array_slice($response, 0, 50) as $code) {
                            echo '<tr>';
                            echo '<td>' . $i++ . '</td>';
                            echo '<td>' . htmlspecialchars($code) . '</td>';
                            echo '<td>' . strlen($code) . '</td>';
                            echo '<td>' . gettype($code) . '</td>';
                            echo '</tr>';
                        }
                        
                        echo '</table>';
                        
                        // Show filtering diagnostic
                        echo '<h3>Filter Test:</h3>';
                        $valid_codes = array_filter($response, function($code) {
                            return is_numeric($code) && strlen($code) >= 5;
                        });
                        
                        $valid_count = count($valid_codes);
                        $invalid_count = $count - $valid_count;
                        
                        echo '<p>Total codes: ' . $count . '</p>';
                        echo '<p>Valid codes (numeric, length >= 5): ' . $valid_count . '</p>';
                        echo '<p>Invalid codes: ' . $invalid_count . '</p>';
                        
                        if ($invalid_count > 0) {
                            echo '<h4>Invalid Codes Sample:</h4>';
                            $invalid_codes = array_filter($response, function($code) {
                                return !(is_numeric($code) && strlen($code) >= 5);
                            });
                            
                            echo '<pre>' . print_r(array_slice($invalid_codes, 0, 20), true) . '</pre>';
                        }
                    } else {
                        echo '<p class="error">Unexpected response format: ' . gettype($response) . '</p>';
                        echo '<pre>' . print_r($response, true) . '</pre>';
                    }
                }
            } catch (Exception $e) {
                echo '<p class="error">Exception: ' . $e->getMessage() . '</p>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            }
            
            // Restore error log
            ini_set('error_log', $old_error_log);
            
            // Get the error log buffer
            $error_log = ob_get_clean();
            
            if (!empty($error_log)) {
                echo '<h3>Debug Log:</h3>';
                echo '<pre class="error-log">' . htmlspecialchars($error_log) . '</pre>';
            }
            
            echo '</div>';
        }
        ?>
        
        <h2>Test cURL Directly</h2>
        <p>You can also test the HotelCodeList API directly with cURL using this command:</p>
        <div class="code-block">
            <pre>
curl "http://api.tbotechnology.in/TBOHolidays_HotelAPI/HotelCodeList" \
  -H "Content-Type: application/json" \
  -H "Authorization: Basic <?php echo base64_encode(TBO_API_USERNAME . ':' . TBO_API_PASSWORD); ?>" \
  -d '{"DestinationCode":"268880"}' \
  --verbose
            </pre>
        </div>
        
        <h2>Test Search by City Code</h2>
        <p>You can test the hotel search using city code with this AJAX request:</p>
        <div class="code-block">
            <pre>
curl "http://localhost/bookings/wp-admin/admin-ajax.php" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  --data-raw "city_code=268880&check_in=2025-09-23&check_out=2025-09-26&rooms=1&adults=2&children=0&action=tbo_hotels_search_hotels&nonce=<?php echo wp_create_nonce('tbo_hotels_nonce'); ?>"
            </pre>
        </div>
        
        <div class="search-form">
            <input type="text" id="search-nonce" value="<?php echo wp_create_nonce('tbo_hotels_nonce'); ?>" readonly>
            <button onclick="copyNonce()">Copy Nonce</button>
        </div>
        
        <script>
            function copyNonce() {
                var copyText = document.getElementById("search-nonce");
                copyText.select();
                document.execCommand("copy");
                alert("Nonce copied to clipboard: " + copyText.value);
            }
        </script>
    </div>
</body>
</html>