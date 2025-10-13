<?php
// Test Real TBO API vs Dummy Data
echo "<h1>🔄 Real TBO API vs Dummy Data Comparison</h1>";

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';
require_once 'wp-content/themes/Yolandabooking/inc/RealTboApiClient.php';

$cityCode = $_GET['city'] ?? '150184';
$checkIn = '2025-09-28';
$checkOut = '2025-09-30';

echo "<h2>Testing City Code: $cityCode</h2>";
echo "<p>This will show the difference between dummy data and real API data</p>";

// Test Dummy Data
echo "<div style='border: 2px solid red; padding: 15px; margin: 10px; background: #ffe6e6;'>";
echo "<h3>🎭 DUMMY DATA (Current System)</h3>";
$dummyApi = new SimpleTboApiClient();
$dummyResults = $dummyApi->searchHotels('TH', $cityCode, $checkIn, $checkOut, 1, 2, 0);
echo "<p><strong>Hotels Found:</strong> " . count($dummyResults['Hotels']) . "</p>";

if (!empty($dummyResults['Hotels'])) {
    echo "<p><strong>Sample Hotels (FAKE):</strong></p>";
    echo "<ul>";
    for ($i = 0; $i < min(5, count($dummyResults['Hotels'])); $i++) {
        $hotel = $dummyResults['Hotels'][$i];
        echo "<li>" . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . " (₹" . ($hotel['Rooms'][0]['TotalFare'] ?? 'N/A') . ")</li>";
    }
    echo "</ul>";
    echo "<p style='color: red;'><strong>⚠️ These are FAKE hotels with made-up names!</strong></p>";
} else {
    echo "<p style='color: orange;'>No dummy data available for this city</p>";
}
echo "</div>";

// Test Real API
echo "<div style='border: 2px solid green; padding: 15px; margin: 10px; background: #e6ffe6;'>";
echo "<h3>🌐 REAL API DATA (TBO Live)</h3>";
echo "<p>🔄 Calling real TBO API...</p>";

try {
    $realApi = new RealTboApiClient();
    
    // First, just get hotel codes to show they exist
    echo "<p>Step 1: Getting real hotel codes...</p>";
    $hotelCodes = $realApi->getHotelCodes($cityCode);
    
    if (!empty($hotelCodes)) {
        echo "<p><strong>✅ Real Hotel Codes Found:</strong> " . count($hotelCodes) . "</p>";
        echo "<p><strong>Sample Hotel Codes:</strong> " . implode(', ', array_slice($hotelCodes, 0, 10)) . "...</p>";
        
        echo "<p>Step 2: Searching for available hotels...</p>";
        $realResults = $realApi->searchHotels('TH', $cityCode, $checkIn, $checkOut, 1, 2, 0);
        
        echo "<p><strong>Hotels with Availability:</strong> " . count($realResults['Hotels']) . "</p>";
        
        if (!empty($realResults['Hotels'])) {
            echo "<p><strong>Real Hotels:</strong></p>";
            echo "<ul>";
            for ($i = 0; $i < min(5, count($realResults['Hotels'])); $i++) {
                $hotel = $realResults['Hotels'][$i];
                echo "<li>";
                echo "Hotel Code: " . ($hotel['HotelCode'] ?? 'N/A');
                echo " - Name: " . ($hotel['HotelName'] ?? 'N/A');
                echo " - Address: " . ($hotel['Address'] ?? 'N/A');
                echo "</li>";
            }
            echo "</ul>";
            echo "<p style='color: green;'><strong>✅ These are REAL hotels from TBO!</strong></p>";
        } else {
            echo "<p style='color: orange;'>No hotels have availability for these dates, but hotel codes exist!</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ No hotel codes found for city $cityCode</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ API Error:</strong> " . $e->getMessage() . "</p>";
}

echo "</div>";

echo "<h2>🎯 The Solution</h2>";
echo "<div style='background: #f0f8ff; padding: 15px; border-left: 5px solid #007acc;'>";
echo "<ol>";
echo "<li><strong>Replace SimpleTboApiClient</strong> with RealTboApiClient in hotel-results.php</li>";
echo "<li><strong>Update authentication</strong> to use your working credentials</li>";
echo "<li><strong>Test with different cities</strong> to ensure global coverage</li>";
echo "<li><strong>Remove all dummy data</strong> once real API is confirmed working</li>";
echo "</ol>";
echo "</div>";

echo "<h2>🧪 Test Different Cities</h2>";
echo "<p><a href='?city=150184'>🇹🇭 Thailand City 150184</a> | ";
echo "<a href='?city=105141'>🇮🇳 Jaipur (105141)</a> | ";
echo "<a href='?city=418069'>🇮🇳 Delhi (418069)</a> | ";
echo "<a href='?city=123456'>❓ Unknown City (123456)</a></p>";
?>
