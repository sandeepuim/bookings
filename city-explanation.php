<?php
// Show how easy it is to add more cities
echo "<h1>🌍 Available City Expansion Options</h1>";

echo "<h2>Current Limited Cities (Mock Data)</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Country</th><th>City</th><th>City Code</th><th>Status</th></tr>";

$currentCities = [
    ['country' => '🇮🇳 India', 'city' => 'New Delhi', 'code' => '418069', 'status' => '✅ Working'],
    ['country' => '🇮🇳 India', 'city' => 'Jaipur, Rajasthan', 'code' => '105141', 'status' => '✅ Working'],
    ['country' => '🇮🇳 India', 'city' => 'Mumbai, Maharashtra', 'code' => '111647', 'status' => '✅ Working'],
    ['country' => '🇮🇳 India', 'city' => 'Goa', 'code' => '105055', 'status' => '✅ Working'],
    ['country' => '🇹🇭 Thailand', 'city' => 'Bangkok', 'code' => '315432', 'status' => '✅ Working'],
    ['country' => '🇹🇭 Thailand', 'city' => 'Phuket', 'code' => '315555', 'status' => '✅ Working'],
];

foreach ($currentCities as $city) {
    echo "<tr>";
    echo "<td>{$city['country']}</td>";
    echo "<td>{$city['city']}</td>";
    echo "<td>{$city['code']}</td>";
    echo "<td>{$city['status']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🚀 Easy to Add More Cities</h2>";
echo "<p>We can easily add more cities by expanding the mock data. For example:</p>";

$potentialCities = [
    ['country' => '🇮🇳 India', 'city' => 'Kolkata', 'code' => '123456', 'effort' => '5 minutes'],
    ['country' => '🇮🇳 India', 'city' => 'Chennai', 'code' => '123457', 'effort' => '5 minutes'],
    ['country' => '🇮🇳 India', 'city' => 'Bangalore', 'code' => '123458', 'effort' => '5 minutes'],
    ['country' => '🇹🇭 Thailand', 'city' => 'Chiang Mai', 'code' => '315999', 'effort' => '5 minutes'],
    ['country' => '🇲🇾 Malaysia', 'city' => 'Kuala Lumpur', 'code' => '400000', 'effort' => '10 minutes'],
    ['country' => '🇸🇬 Singapore', 'city' => 'Singapore', 'code' => '500000', 'effort' => '10 minutes'],
];

echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>Country</th><th>City</th><th>City Code</th><th>Time to Add</th></tr>";

foreach ($potentialCities as $city) {
    echo "<tr style='background: #f0f8ff;'>";
    echo "<td>{$city['country']}</td>";
    echo "<td>{$city['city']}</td>";
    echo "<td>{$city['code']}</td>";
    echo "<td>⏱️ {$city['effort']}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>🎯 The Real Solution</h2>";
echo "<div style='background: #ffffcc; padding: 15px; border-left: 5px solid #ffcc00;'>";
echo "<h3>Option 1: Fix Real TBO API (Recommended)</h3>";
echo "<ul>";
echo "<li><strong>Contact TBO Support</strong> → Get correct API endpoints</li>";
echo "<li><strong>Fix Authentication</strong> → Get proper credentials</li>";
echo "<li><strong>Result:</strong> Access to thousands of real hotels worldwide</li>";
echo "</ul>";

echo "<h3>Option 2: Expand Mock Data (Quick Fix)</h3>";
echo "<ul>";
echo "<li><strong>Add more cities</strong> to SimpleTboApiClient.php</li>";
echo "<li><strong>Create realistic hotel data</strong> for each city</li>";
echo "<li><strong>Result:</strong> More cities, but still fake data</li>";
echo "</ul>";
echo "</div>";

echo "<h2>📞 Next Steps</h2>";
echo "<ol>";
echo "<li><strong>Contact TBO Technology</strong> - Verify API URLs and credentials</li>";
echo "<li><strong>Test Real API</strong> - Once working, remove mock data</li>";
echo "<li><strong>Or Expand Mock</strong> - Add more cities for demonstration</li>";
echo "</ol>";
?>
