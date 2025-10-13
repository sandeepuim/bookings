<?php
// Simple test to check if hotel search template is working
echo "<h1>Hotel Search Template Test</h1>";
echo "<p>PHP is working correctly.</p>";
echo "<p>Current time: " . date('Y-m-d H:i:s') . "</p>";

// Check if template files exist
$template_path = get_template_directory() . '/templates/hotel-search.php';
if (file_exists($template_path)) {
    echo "<p>✅ Hotel search template exists at: " . $template_path . "</p>";
} else {
    echo "<p>❌ Hotel search template NOT found at: " . $template_path . "</p>";
}

// Check CSS files
$css_path = get_template_directory() . '/assets/css/hotel-results.css';
if (file_exists($css_path)) {
    echo "<p>✅ Hotel results CSS exists</p>";
} else {
    echo "<p>❌ Hotel results CSS NOT found</p>";
}

// Check JavaScript files
$js_path = get_template_directory() . '/assets/js/hotel-search.js';
if (file_exists($js_path)) {
    echo "<p>✅ Hotel search JS exists</p>";
} else {
    echo "<p>❌ Hotel search JS NOT found</p>";
}

echo "<p><a href='".home_url()."'>← Back to Home</a></p>";
?>