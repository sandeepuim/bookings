<?php
/**
 * The class responsible for registering shortcodes.
 */
class TBO_Hotel_Booking_Shortcodes {

    /**
     * Register the shortcodes.
     */
    public function register() {
        add_shortcode('tbo_hotel_search', array($this, 'hotel_search_shortcode'));
        add_shortcode('tbo_hotel_listing', array($this, 'hotel_listing_shortcode'));
        add_shortcode('tbo_hotel_details', array($this, 'hotel_details_shortcode'));
        add_shortcode('tbo_booking_form', array($this, 'booking_form_shortcode'));
        add_shortcode('tbo_booking_confirmation', array($this, 'booking_confirmation_shortcode'));
        add_shortcode('tbo_user_bookings', array($this, 'user_bookings_shortcode'));
    }

    /**
     * Hotel search form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function hotel_search_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Find Your Perfect Hotel', 'tbo-hotel-booking'),
            'subtitle' => __('Search for hotels and accommodations', 'tbo-hotel-booking'),
            'class' => '',
        ), $atts, 'tbo_hotel_search');
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/hotel-search-form.php';
        return ob_get_clean();
    }

    /**
     * Hotel listing shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function hotel_listing_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Available Hotels', 'tbo-hotel-booking'),
            'count' => 10,
            'location' => '',
            'category' => '',
            'facility' => '',
            'class' => '',
        ), $atts, 'tbo_hotel_listing');
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/hotel-listing.php';
        return ob_get_clean();
    }

    /**
     * Hotel details shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function hotel_details_shortcode($atts) {
        $atts = shortcode_atts(array(
            'id' => '',
            'class' => '',
        ), $atts, 'tbo_hotel_details');
        
        if (empty($atts['id'])) {
            // Try to get hotel ID from URL parameter
            $hotel_id = isset($_GET['hotel_id']) ? sanitize_text_field($_GET['hotel_id']) : '';
            
            if (empty($hotel_id)) {
                return '<p>' . __('Hotel ID is required.', 'tbo-hotel-booking') . '</p>';
            }
            
            $atts['id'] = $hotel_id;
        }
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/hotel-details.php';
        return ob_get_clean();
    }

    /**
     * Booking form shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function booking_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'hotel_id' => '',
            'room_id' => '',
            'class' => '',
        ), $atts, 'tbo_booking_form');
        
        if (empty($atts['hotel_id'])) {
            // Try to get hotel ID from URL parameter
            $hotel_id = isset($_GET['hotel_id']) ? sanitize_text_field($_GET['hotel_id']) : '';
            
            if (empty($hotel_id)) {
                return '<p>' . __('Hotel ID is required.', 'tbo-hotel-booking') . '</p>';
            }
            
            $atts['hotel_id'] = $hotel_id;
        }
        
        if (empty($atts['room_id'])) {
            // Try to get room ID from URL parameter
            $room_id = isset($_GET['room_id']) ? sanitize_text_field($_GET['room_id']) : '';
            
            if (empty($room_id)) {
                return '<p>' . __('Room ID is required.', 'tbo-hotel-booking') . '</p>';
            }
            
            $atts['room_id'] = $room_id;
        }
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/booking-form.php';
        return ob_get_clean();
    }

    /**
     * Booking confirmation shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function booking_confirmation_shortcode($atts) {
        $atts = shortcode_atts(array(
            'booking_id' => '',
            'class' => '',
        ), $atts, 'tbo_booking_confirmation');
        
        if (empty($atts['booking_id'])) {
            // Try to get booking ID from URL parameter
            $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
            
            if (empty($booking_id)) {
                return '<p>' . __('Booking ID is required.', 'tbo-hotel-booking') . '</p>';
            }
            
            $atts['booking_id'] = $booking_id;
        }
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/booking-confirmation.php';
        return ob_get_clean();
    }

    /**
     * User bookings shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string
     */
    public function user_bookings_shortcode($atts) {
        $atts = shortcode_atts(array(
            'title' => __('My Bookings', 'tbo-hotel-booking'),
            'class' => '',
        ), $atts, 'tbo_user_bookings');
        
        // Check if user is logged in
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your bookings.', 'tbo-hotel-booking') . '</p>';
        }
        
        ob_start();
        include TBO_HOTEL_BOOKING_PLUGIN_DIR . 'templates/user-bookings.php';
        return ob_get_clean();
    }
}
