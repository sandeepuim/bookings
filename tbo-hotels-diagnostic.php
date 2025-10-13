<?php
/**
 * TBO Hotels Implementation Diagnostic
 * 
 * This script checks the implementation of the TBO Hotels functionality
 * and provides diagnostic information about the setup.
 */

// Basic setup check
echo "<h1>TBO Hotels Implementation Diagnostic</h1>";

// Check WordPress integration
$wp_loaded = false;
if (file_exists('wp-load.php')) {
    require_once('wp-load.php');
    $wp_loaded = function_exists('wp_head');
}

echo "<h2>WordPress Integration</h2>";
echo "WordPress loaded: " . ($wp_loaded ? "Yes" : "No") . "<br>";

// Check required files
echo "<h2>Required Files Check</h2>";
$required_files = [
    'wp-content/themes/twentytwentyone/tbo-room-functions.php',
    'wp-content/themes/twentytwentyone/direct-button-fix.php',
    'wp-content/themes/twentytwentyone/hotel-button-enhancement.php',
    'hotel-room-selection.php'
];

echo "<ul>";
foreach ($required_files as $file) {
    $exists = file_exists($file);
    $readable = $exists ? is_readable($file) : false;
    $syntax_check = null;
    
    if ($exists && $readable) {
        // Check PHP syntax
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($file), $output, $return_var);
        $syntax_check = $return_var === 0;
    }
    
    echo "<li>";
    echo "<strong>" . htmlspecialchars($file) . "</strong><br>";
    echo "Exists: " . ($exists ? "✅ Yes" : "❌ No") . "<br>";
    if ($exists) {
        echo "Readable: " . ($readable ? "✅ Yes" : "❌ No") . "<br>";
        echo "PHP Syntax: " . ($syntax_check ? "✅ Valid" : "❌ Invalid") . "<br>";
        
        if ($syntax_check) {
            // Show file size and modification date
            echo "File size: " . filesize($file) . " bytes<br>";
            echo "Last modified: " . date("Y-m-d H:i:s", filemtime($file)) . "<br>";
        } else {
            // Show syntax error
            echo "Syntax error: " . htmlspecialchars(implode("<br>", $output)) . "<br>";
        }
    }
    echo "</li>";
}
echo "</ul>";

// Test functionality
echo "<h2>Functionality Test</h2>";
echo "<p>Testing the button functionality:</p>";
echo "<a href='test-button-functionality.php' class='button'>Run Button Test</a>";

// Test room selection page
echo "<p>Testing the room selection page:</p>";
echo "<a href='hotel-room-selection.php?hotel_id=12345&hotel_name=Test+Hotel&city_id=150184&check_in=2023-07-01&check_out=2023-07-05&adults=2&children=0' class='button'>Test Room Selection Page</a>";

// Styling
echo "<style>
body {
    font-family: Arial, sans-serif;
    margin: 20px;
    line-height: 1.6;
}
h1, h2 {
    color: #333;
}
ul {
    list-style-type: none;
    padding: 0;
}
li {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
}
.button {
    display: inline-block;
    background-color: #4CAF50;
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 4px;
    margin-right: 10px;
}
.button:hover {
    background-color: #45a049;
}
</style>";
?>