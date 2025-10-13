<?php
/**
 * Test the enhanced hotel search with merged hotel names
 */
require_once 'wp-load.php';

echo "<h1>Enhanced Hotel Search Test</h1>\n";

// Test parameters for Dubai
$test_params = array(
    'city_code' => '150184', // Dubai
    'check_in' => date('Y-m-d', strtotime('+7 days')),
    'check_out' => date('Y-m-d', strtotime('+9 days')),
    'adults' => 2,
    'rooms' => 1,
    'children' => 0
);

echo "<h2>Test Parameters:</h2>\n";
echo "<ul>\n";
echo "<li>City Code: " . $test_params['city_code'] . " (Dubai)</li>\n";
echo "<li>Check-in: " . $test_params['check_in'] . "</li>\n";
echo "<li>Check-out: " . $test_params['check_out'] . "</li>\n";
echo "<li>Adults: " . $test_params['adults'] . "</li>\n";
echo "<li>Rooms: " . $test_params['rooms'] . "</li>\n";
echo "</ul>\n";

echo "<h2>Step 1: Testing Hotel Details Fetch</h2>\n";
$hotel_details = tbo_hotels_get_hotel_details($test_params['city_code']);

if (is_wp_error($hotel_details)) {
    echo "<p style='color: red;'>Error fetching hotel details: " . $hotel_details->get_error_message() . "</p>\n";
} else {
    echo "<p style='color: green;'>✓ Successfully fetched " . count($hotel_details) . " hotel details</p>\n";
    
    // Show first 3 hotel details
    echo "<h3>Sample Hotel Details (first 3):</h3>\n";
    $count = 0;
    foreach ($hotel_details as $hotelCode => $details) {
        if ($count >= 3) break;
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>\n";
        echo "<strong>Hotel Code:</strong> $hotelCode<br>\n";
        echo "<strong>Name:</strong> " . $details['HotelName'] . "<br>\n";
        echo "<strong>Address:</strong> " . $details['HotelAddress'] . "<br>\n";
        echo "<strong>Rating:</strong> " . $details['StarRating'] . " stars<br>\n";
        echo "</div>\n";
        $count++;
    }
}

echo "<h2>Step 2: Testing Enhanced Hotel Search</h2>\n";
$search_results = tbo_hotels_search_hotels($test_params);

if (is_wp_error($search_results)) {
    echo "<p style='color: red;'>Error in hotel search: " . $search_results->get_error_message() . "</p>\n";
} else {
    echo "<p style='color: green;'>✓ Successfully found " . $search_results['TotalHotels'] . " hotels with enhanced data</p>\n";
    
    // Show first 3 search results
    echo "<h3>Sample Search Results (first 3):</h3>\n";
    if (isset($search_results['Hotels']) && is_array($search_results['Hotels'])) {
        $hotels = array_slice($search_results['Hotels'], 0, 3);
        
        foreach ($hotels as $index => $hotel) {
            $hasDetails = $hotel['HasDetails'] ?? false;
            $detailsStatus = $hasDetails ? "<span style='color: green;'>✓ Enhanced</span>" : "<span style='color: orange;'>⚠ Basic</span>";
            
            echo "<div style='border: 1px solid #ccc; padding: 15px; margin: 10px 0; background: " . ($hasDetails ? '#f9f9f9' : '#fff9e6') . ";'>\n";
            echo "<h4>Hotel " . ($index + 1) . " - $detailsStatus</h4>\n";
            echo "<strong>Hotel Code:</strong> " . ($hotel['HotelCode'] ?? 'N/A') . "<br>\n";
            echo "<strong>Hotel Name:</strong> " . ($hotel['HotelName'] ?? 'Unknown') . "<br>\n";
            
            if ($hasDetails) {
                echo "<strong>Address:</strong> " . ($hotel['HotelAddress'] ?? 'N/A') . "<br>\n";
                echo "<strong>Star Rating:</strong> " . ($hotel['StarRating'] ?? 'N/A') . " stars<br>\n";
            }
            
            // Show pricing information if available
            if (isset($hotel['Rooms']) && is_array($hotel['Rooms']) && count($hotel['Rooms']) > 0) {
                echo "<strong>Available Rooms:</strong> " . count($hotel['Rooms']) . "<br>\n";
                
                // Show first room price if available
                $firstRoom = $hotel['Rooms'][0];
                if (isset($firstRoom['DayRates']) && is_array($firstRoom['DayRates'])) {
                    echo "<strong>Sample Price:</strong> Available<br>\n";
                }
            }
            
            echo "</div>\n";
        }
    }
}

echo "<h2>Summary</h2>\n";
echo "<p>This test demonstrates the enhanced hotel search functionality that:</p>\n";
echo "<ol>\n";
echo "<li>Fetches hotel details (names, addresses, ratings) from HotelCodeList API</li>\n";
echo "<li>Performs hotel search using Search API</li>\n";
echo "<li>Merges the results to provide complete hotel information</li>\n";
echo "</ol>\n";

echo "<p>Hotels marked as 'Enhanced' have complete information, while 'Basic' hotels only have search data.</p>\n";
?>