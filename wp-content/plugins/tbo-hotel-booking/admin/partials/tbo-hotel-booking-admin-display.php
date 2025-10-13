<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    TBO_Hotel_Booking
 * @subpackage TBO_Hotel_Booking/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="tbo-hotel-booking-admin-wrapper">
        <div class="tbo-hotel-booking-admin-content">
            <h2><?php _e('Welcome to TBO Hotel Booking Plugin', 'tbo-hotel-booking'); ?></h2>
            <p><?php _e('This plugin allows you to connect to the TBO API and display hotel booking options on your website.', 'tbo-hotel-booking'); ?></p>
            
            <h3><?php _e('Getting Started', 'tbo-hotel-booking'); ?></h3>
            <ol>
                <li><?php _e('Configure your API credentials in the Settings page', 'tbo-hotel-booking'); ?></li>
                <li><?php _e('Test your API connection using the API Test page', 'tbo-hotel-booking'); ?></li>
                <li><?php _e('Add the search form to your website using the shortcode: [tbo_hotel_search_form]', 'tbo-hotel-booking'); ?></li>
            </ol>
            
            <h3><?php _e('Available Shortcodes', 'tbo-hotel-booking'); ?></h3>
            <ul>
                <li><code>[tbo_hotel_search_form]</code> - <?php _e('Displays the hotel search form', 'tbo-hotel-booking'); ?></li>
                <li><code>[tbo_hotel_listing]</code> - <?php _e('Displays hotel search results', 'tbo-hotel-booking'); ?></li>
                <li><code>[tbo_hotel_details]</code> - <?php _e('Displays detailed information about a specific hotel', 'tbo-hotel-booking'); ?></li>
                <li><code>[tbo_booking_form]</code> - <?php _e('Displays the booking form for a selected hotel', 'tbo-hotel-booking'); ?></li>
                <li><code>[tbo_booking_confirmation]</code> - <?php _e('Displays booking confirmation details', 'tbo-hotel-booking'); ?></li>
                <li><code>[tbo_user_bookings]</code> - <?php _e('Displays a list of bookings for the current user', 'tbo-hotel-booking'); ?></li>
            </ul>
        </div>
    </div>
</div>
