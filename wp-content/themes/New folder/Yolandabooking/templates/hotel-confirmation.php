<?php
/**
 * Template Name: Hotel Confirmation
 */
get_header();
require_once get_template_directory() . '/inc/TboApiClient.php';
$tbo = new TboApiClient('http://api.tbotechnology.in/TBOHolidays_HotelAPI', 'USERNAME', 'PASSWORD');

if (isset($_GET['ref'])) {
    $details = $tbo->getBookingDetails($_GET['ref']);
    echo "<h2>Booking Confirmation</h2>";
    echo "<p>Booking Reference: " . esc_html($_GET['ref']) . "</p>";
    echo "<pre>" . print_r($details, true) . "</pre>";
}
get_footer();
