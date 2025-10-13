<?php
/**
 * Template Name: Hotel Cancel
 */
get_header();
require_once get_template_directory() . '/inc/TboApiClient.php';
$tbo = new TboApiClient('http://api.tbotechnology.in/TBOHolidays_HotelAPI', 'USERNAME', 'PASSWORD');

if ($_POST) {
    $cancel = $tbo->cancelBooking($_POST['booking_ref']);
    echo "<h2>Cancel Booking</h2>";
    echo "<pre>" . print_r($cancel, true) . "</pre>";
}
?>
<h2>Cancel Booking</h2>
<form method="post">
    <label>Booking Reference:</label>
    <input type="text" name="booking_ref" required>
    <button type="submit">Cancel Booking</button>
</form>
<?php get_footer(); ?>
