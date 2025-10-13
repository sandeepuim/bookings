<?php
// Simple test page to verify the no hotels logic
echo "<h1>Simple Hotel Search Test</h1>";

// Include WordPress if available
if (file_exists('../wp-load.php')) {
    require_once '../wp-load.php';
}

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';

// Test parameters
$country_code = $_GET['country_code'] ?? 'TH';
$city_code = $_GET['city_code'] ?? '150184';
$check_in = $_GET['check_in'] ?? '2025-10-02';
$check_out = $_GET['check_out'] ?? '2025-10-03';
$rooms = intval($_GET['rooms'] ?? 1);
$adults = intval($_GET['adults'] ?? 2);
$children = intval($_GET['children'] ?? 0);

echo "<h2>Search Parameters</h2>";
echo "<ul>";
echo "<li>Country: $country_code</li>";
echo "<li>City Code: $city_code</li>";
echo "<li>Check-in: $check_in</li>";
echo "<li>Check-out: $check_out</li>";
echo "<li>Rooms: $rooms</li>";
echo "<li>Adults: $adults</li>";
echo "<li>Children: $children</li>";
echo "</ul>";

// Test API
$tbo = new SimpleTboApiClient();
$results = $tbo->searchHotels($country_code, $city_code, $check_in, $check_out, $rooms, $adults, $children);

echo "<h2>API Results</h2>";
echo "<p><strong>Hotels Found:</strong> " . count($results['Hotels']) . "</p>";

if (!empty($results['Hotels'])) {
    echo "<h3>✅ Hotels Available</h3>";
    foreach ($results['Hotels'] as $index => $hotel) {
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 10px 0;'>";
        echo "<h4>" . htmlspecialchars($hotel['HotelName']) . "</h4>";
        echo "<p>" . htmlspecialchars($hotel['HotelAddress']) . "</p>";
        echo "<p>Rating: " . $hotel['StarRating'] . " stars</p>";
        echo "</div>";
        if ($index >= 2) {
            echo "<p>... and " . (count($results['Hotels']) - 3) . " more hotels</p>";
            break;
        }
    }
} else {
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center;'>";
    echo "<h3>🚫 No Hotels Available</h3>";
    echo "<p>Sorry, we don't have hotel availability for city code <strong>$city_code</strong> in <strong>$country_code</strong>.</p>";
    echo "<p>Please try one of our supported destinations:</p>";
    
    echo "<div style='text-align: left; max-width: 500px; margin: 0 auto;'>";
    echo "<h4>🇮🇳 India</h4>";
    echo "<ul>";
    echo "<li>Delhi - 418069</li>";
    echo "<li>Jaipur - 105141</li>";
    echo "<li>Mumbai - 111647</li>";
    echo "<li>Goa - 105055</li>";
    echo "</ul>";
    
    echo "<h4>🇹🇭 Thailand</h4>";
    echo "<ul>";
    echo "<li>Bangkok - 315432</li>";
    echo "<li>Phuket - 315555</li>";
    echo "</ul>";
    echo "</div>";
    echo "</div>";
}

echo "<h2>Test Other Cities</h2>";
echo '<p><a href="?country_code=TH&city_code=315432">🇹🇭 Bangkok (should have hotels)</a></p>';
echo '<p><a href="?country_code=TH&city_code=150184">🇹🇭 Unknown Thai city (should be empty)</a></p>';
echo '<p><a href="?country_code=IN&city_code=105141">🇮🇳 Jaipur (should have hotels)</a></p>';
echo '<p><a href="?country_code=US&city_code=999999">🇺🇸 Unknown US city (should be empty)</a></p>';
?>
