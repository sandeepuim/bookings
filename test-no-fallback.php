<?php
// Test to verify no default fallback behavior
echo "<h1>🚫 No Default Fallback Test</h1>";
echo "<p>Testing that unknown cities show 'No hotels available' instead of Delhi hotels</p>";

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';

$api = new SimpleTboApiClient();

$testCases = [
    ['city' => '418069', 'name' => '✅ Delhi (Should work)', 'expected' => 'Hotels found'],
    ['city' => '105141', 'name' => '✅ Jaipur (Should work)', 'expected' => 'Hotels found'],
    ['city' => '315432', 'name' => '✅ Bangkok (Should work)', 'expected' => 'Hotels found'],
    ['city' => '999999', 'name' => '❌ Unknown City 999999', 'expected' => 'No hotels'],
    ['city' => '123456', 'name' => '❌ Unknown City 123456', 'expected' => 'No hotels'],
    ['city' => '555555', 'name' => '❌ Unknown City 555555', 'expected' => 'No hotels']
];

foreach ($testCases as $test) {
    echo "<h2>{$test['name']} (City Code: {$test['city']})</h2>";
    
    $results = $api->searchHotels('IN', $test['city'], '2025-09-28', '2025-09-30', 1, 1, 0);
    
    $hotelCount = count($results['Hotels']);
    
    if ($hotelCount > 0) {
        echo "<p><strong>🏨 Result: {$hotelCount} hotels found</strong></p>";
        if ($test['expected'] === 'No hotels') {
            echo "<p style='color: red;'>⚠️ UNEXPECTED: Should show no hotels but found {$hotelCount}</p>";
        } else {
            echo "<p style='color: green;'>✅ EXPECTED: Hotels found as expected</p>";
        }
        
        // Show first hotel to verify location
        if (!empty($results['Hotels'][0])) {
            $firstHotel = $results['Hotels'][0];
            echo "<p>First hotel: <strong>{$firstHotel['HotelName']}</strong> - {$firstHotel['HotelAddress']}</p>";
        }
    } else {
        echo "<p><strong>🚫 Result: No hotels available</strong></p>";
        if ($test['expected'] === 'Hotels found') {
            echo "<p style='color: red;'>⚠️ UNEXPECTED: Should have hotels but found none</p>";
        } else {
            echo "<p style='color: green;'>✅ EXPECTED: No hotels as expected</p>";
        }
    }
    
    // Test link
    echo '<p><a href="http://localhost/bookings/hotel-results/?country_code=IN&city_code=' . $test['city'] . '&check_in=2025-09-28&check_out=2025-09-30&rooms=1&adults=1&children=0" target="_blank">🔗 Test in Browser</a></p>';
    echo "<hr>";
}

echo "<h2>🎯 Test Summary</h2>";
echo "<ul>";
echo "<li>✅ <strong>Known cities</strong>: Should show location-specific hotels</li>";
echo "<li>❌ <strong>Unknown cities</strong>: Should show 'No hotels available' message</li>";
echo "<li>🚫 <strong>No more Delhi defaults</strong>: Unknown cities won't fallback to Delhi</li>";
echo "</ul>";
?>
