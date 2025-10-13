<?php
// Test specific city code 150184
echo "<h1>Testing City Code 150184 (Thailand)</h1>";

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';

$api = new SimpleTboApiClient();

echo "<h2>Search Parameters:</h2>";
echo "<ul>";
echo "<li>Country Code: TH</li>";
echo "<li>City Code: 150184</li>";
echo "<li>Check-in: 2025-10-02</li>";
echo "<li>Check-out: 2025-10-03</li>";
echo "<li>Rooms: 1</li>";
echo "<li>Adults: 2</li>";
echo "<li>Children: 0</li>";
echo "</ul>";

echo "<h2>API Response:</h2>";

$results = $api->searchHotels('TH', '150184', '2025-10-02', '2025-10-03', 1, 2, 0);

echo "<p><strong>Hotel Count:</strong> " . count($results['Hotels']) . "</p>";

if (count($results['Hotels']) > 0) {
    echo "<p style='color: red;'>⚠️ ISSUE: Found hotels when none should exist for city 150184</p>";
    echo "<h3>Hotels Found:</h3>";
    foreach ($results['Hotels'] as $index => $hotel) {
        echo "<p>" . ($index + 1) . ". " . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . "</p>";
        if ($index >= 4) break;
    }
} else {
    echo "<p style='color: green;'>✅ CORRECT: No hotels found for unsupported city</p>";
}

echo "<h2>Supported Thailand Cities:</h2>";
echo "<ul>";
echo "<li><strong>Bangkok</strong> - City Code: 315432</li>";
echo "<li><strong>Phuket</strong> - City Code: 315555</li>";
echo "</ul>";

echo "<h2>Test Links:</h2>";
echo '<p><a href="http://localhost/bookings/hotel-results/?country_code=TH&city_code=150184&check_in=2025-10-02&check_out=2025-10-03&rooms=1&adults=2&children=0" target="_blank">🔗 Test City 150184 (Should show No Hotels)</a></p>';
echo '<p><a href="http://localhost/bookings/hotel-results/?country_code=TH&city_code=315432&check_in=2025-10-02&check_out=2025-10-03&rooms=1&adults=2&children=0" target="_blank">🔗 Test Bangkok 315432 (Should show Hotels)</a></p>';
?>
