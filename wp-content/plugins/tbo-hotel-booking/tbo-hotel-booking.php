<?php
/**
 * Plugin Name: TBO Hotel Booking
 * Plugin URI: https://yourdomain.com/tbo-hotel-booking
 * Description: A WordPress hotel booking MVP integrated with TBO Stage API.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourdomain.com
 * Text Domain: tbo-hotel-booking
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('TBO_HOTEL_BOOKING_VERSION', '1.0.0');
define('TBO_HOTEL_BOOKING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('TBO_HOTEL_BOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TBO_HOTEL_BOOKING_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking.php';

// Activation hook
register_activation_hook(__FILE__, array('TBO_Hotel_Booking', 'activate'));

// Deactivation hook
register_deactivation_hook(__FILE__, array('TBO_Hotel_Booking', 'deactivate'));

// Run the plugin
function run_tbo_hotel_booking() {
    $plugin = new TBO_Hotel_Booking();
    $plugin->run();
}
run_tbo_hotel_booking();
