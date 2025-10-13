<?php
/* Template Name: Hotel Bookings */
get_header();

// Include TBO API Client
require_once get_template_directory() . '/inc/TboApiClient.php';

// Get search parameters from URL
$country_code = sanitize_text_field($_GET['country_code']);
$city_code    = sanitize_text_field($_GET['city_code']);
$check_in     = sanitize_text_field($_GET['check_in']);
$check_out    = sanitize_text_field($_GET['check_out']);
$rooms        = intval($_GET['rooms']);
$adults       = intval($_GET['adults']);
$children     = intval($_GET['children']);

// Create TBO client instance
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI', // API URL
    'YOUR_USERNAME',
    'YOUR_PASSWORD'
);

// Call hotel search API
try {
    $results = $tbo->searchHotels($country_code, $city_code, $check_in, $check_out, $rooms, $adults, $children);

    if (!empty($results['Hotels'])) {
        echo "<h2>Available Hotels</h2><div class='hotel-list'>";
        foreach ($results['Hotels'] as $hotel) {
            echo "<div class='hotel-card'>";
            echo "<h3>" . esc_html($hotel['HotelName']) . "</h3>";
            echo "<p>Rating: " . esc_html($hotel['StarRating']) . "</p>";
            echo "<p>Price: " . esc_html($hotel['Price']) . "</p>";
            echo "</div>";
        }
        echo "</div>";
    } else {
        echo "<p>No hotels found.</p>";
    }
} catch (Exception $e) {
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
}

get_footer();