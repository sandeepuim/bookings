<?php
/**
 * Template Name: Hotel Rooms Redirect
 * 
 * Special template to handle hotel room selection redirects
 *
 * @package TBO_Hotels
 */

// Get the header
get_header();

// Debug - Get current request URL
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
error_log('TBO Hotels - Redirect Debug - Current URL: ' . $current_url);

// Get hotel code and other parameters from the URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Check if we have the required parameters
$has_params = !empty($hotel_code) && !empty($city_code);

if ($has_params) {
    // Include the template directly
    include(get_template_directory() . '/templates/hotel-rooms.php');
} else {
    // Display error message
    echo '<div class="container" style="padding: 50px 20px; text-align: center;">';
    echo '<h2>Missing Required Parameters</h2>';
    echo '<p>The hotel room selection page requires a hotel code and city code.</p>';
    echo '<p><a href="' . home_url('/') . '">Return to Home</a></p>';
    echo '</div>';
}

// Get footer
get_footer();
?>