<?php
/**
 * Room Selection Redirect Script
 * 
 * This script handles direct redirection to the room selection page
 * Used as a fallback for when the JavaScript redirect doesn't work
 */

// Get parameters from URL
$hotel_code = isset($_GET['hotel_code']) ? $_GET['hotel_code'] : '';
$city_code = isset($_GET['city_code']) ? $_GET['city_code'] : '';
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : '';
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$adults = isset($_GET['adults']) ? $_GET['adults'] : '';
$children = isset($_GET['children']) ? $_GET['children'] : '';
$rooms = isset($_GET['rooms']) ? $_GET['rooms'] : '';

// Debug logging
error_log("Room redirect script called with: hotel_code=$hotel_code, city_code=$city_code");

// Build query string
$query_string = http_build_query([
    'hotel_code' => $hotel_code,
    'city_code' => $city_code,
    'check_in' => $check_in,
    'check_out' => $check_out,
    'adults' => $adults,
    'children' => $children,
    'rooms' => $rooms
]);

// Build the direct URL to room-type page or use direct template
if (file_exists(__DIR__ . '/wp-content/themes/tbo-hotels/page-templates/room-type-template.php')) {
    // Load WordPress to use get_permalink
    require_once('wp-load.php');
    
    // Get the page ID of the room-type page
    $page = get_page_by_path('room-type');
    
    if ($page) {
        // Get permalink and redirect
        $redirect_url = get_permalink($page->ID) . '?' . $query_string;
    } else {
        // Fallback to direct path
        $redirect_url = '/bookings/room-type/?' . $query_string;
    }
} else {
    // Direct template as fallback
    $redirect_url = '/bookings/wp-content/themes/tbo-hotels/direct-hotel-rooms.php?' . $query_string;
}

// Output debugging info if DEBUG parameter is set
if (isset($_GET['debug'])) {
    echo "<h1>Debug Information</h1>";
    echo "<p>Redirect URL: " . htmlspecialchars($redirect_url) . "</p>";
    echo "<p>Parameters:</p>";
    echo "<pre>";
    print_r([
        'hotel_code' => $hotel_code,
        'city_code' => $city_code,
        'check_in' => $check_in,
        'check_out' => $check_out,
        'adults' => $adults,
        'children' => $children,
        'rooms' => $rooms
    ]);
    echo "</pre>";
    echo "<p><a href='" . htmlspecialchars($redirect_url) . "'>Continue to Room Selection</a></p>";
    exit;
}

// Redirect to room selection page
header('Location: ' . $redirect_url);
exit;
?>