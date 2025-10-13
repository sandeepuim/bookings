<?php
// Comprehensive city/country test
echo "<h1>🌍 International Hotel Search Test</h1>";

require_once 'wp-content/themes/Yolandabooking/inc/SimpleTboApiClient.php';

$api = new SimpleTboApiClient();

$testCases = [
    ['country' => 'IN', 'city' => '418069', 'name' => '🇮🇳 Delhi, India'],
    ['country' => 'IN', 'city' => '105141', 'name' => '🏰 Jaipur, Rajasthan'],
    ['country' => 'IN', 'city' => '111647', 'name' => '🌆 Mumbai, Maharashtra'],
    ['country' => 'IN', 'city' => '105055', 'name' => '🏖️ Goa, India'],
    ['country' => 'TH', 'city' => '315432', 'name' => '🇹🇭 Bangkok, Thailand'],
    ['country' => 'TH', 'city' => '315555', 'name' => '🏝️ Phuket, Thailand'],
    ['country' => 'TH', 'city' => '399999', 'name' => '🇹🇭 Unknown Thai City (should show Bangkok)'],
    ['country' => 'US', 'city' => '999999', 'name' => '🇺🇸 Unknown Country (should default to India)']
];

foreach ($testCases as $test) {
    echo "<h2>{$test['name']} (City Code: {$test['city']})</h2>";
    
    $results = $api->searchHotels($test['country'], $test['city'], '2025-09-28', '2025-09-30', 1, 1, 0);
    
    if (!empty($results['Hotels'])) {
        echo "<p><strong>✅ Found " . count($results['Hotels']) . " hotels</strong></p>";
        
        // Show first 3 hotels
        $count = 0;
        foreach ($results['Hotels'] as $hotel) {
            echo "<p>" . ++$count . ". " . $hotel['HotelName'] . " - " . $hotel['HotelAddress'] . "</p>";
            if ($count >= 3) break;
        }
        echo "<p>...</p>";
        
        // Test link
        echo '<p><a href="http://localhost/bookings/hotel-results/?country_code=' . $test['country'] . '&city_code=' . $test['city'] . '&check_in=2025-09-28&check_out=2025-09-30&rooms=1&adults=1&children=0" target="_blank">🔗 View Full Results</a></p>';
    } else {
        echo "<p>❌ No hotels found</p>";
    }
    
    echo "<hr>";
}

echo "<h2>🎯 Test Summary</h2>";
echo "<p>Each location should now show appropriate hotels with local names and addresses!</p>";
?>
