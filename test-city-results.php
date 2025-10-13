<?php
// Test to show different city results
echo "<h1>Hotel Search Results Comparison</h1>";

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';

$api = new SimpleTboApiClient();

echo "<h2>🏛️ Delhi Hotels (City Code: 418069)</h2>";
$delhiResults = $api->searchHotels('IN', '418069', '2025-09-28', '2025-09-30', 1, 1, 0);
if (!empty($delhiResults['Hotels'])) {
    $count = 0;
    foreach ($delhiResults['Hotels'] as $hotel) {
        echo "<p>" . ++$count . ". " . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . "</p>";
        if ($count >= 5) break; // Show first 5
    }
    echo "<p><strong>Total Delhi Hotels: " . count($delhiResults['Hotels']) . "</strong></p>";
}

echo "<h2>🏰 Rajasthan Hotels (City Code: 105141)</h2>";
$rajasthanResults = $api->searchHotels('IN', '105141', '2025-09-28', '2025-09-30', 1, 1, 0);
if (!empty($rajasthanResults['Hotels'])) {
    $count = 0;
    foreach ($rajasthanResults['Hotels'] as $hotel) {
        echo "<p>" . ++$count . ". " . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . "</p>";
        if ($count >= 5) break; // Show first 5
    }
    echo "<p><strong>Total Rajasthan Hotels: " . count($rajasthanResults['Hotels']) . "</strong></p>";
}

echo "<h2>🌆 Mumbai Hotels (City Code: 111647)</h2>";
$mumbaiResults = $api->searchHotels('IN', '111647', '2025-09-28', '2025-09-30', 1, 1, 0);
if (!empty($mumbaiResults['Hotels'])) {
    $count = 0;
    foreach ($mumbaiResults['Hotels'] as $hotel) {
        echo "<p>" . ++$count . ". " . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . "</p>";
        if ($count >= 5) break; // Show first 5
    }
    echo "<p><strong>Total Mumbai Hotels: " . count($mumbaiResults['Hotels']) . "</strong></p>";
}

echo "<h2>✅ Test Complete!</h2>";
echo "<p>Now your hotel search will show location-specific results based on the city code you search for.</p>";
?>
