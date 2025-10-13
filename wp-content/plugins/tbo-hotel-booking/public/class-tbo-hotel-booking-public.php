<?php
/**
 * The public-facing functionality of the plugin.
 */
class TBO_Hotel_Booking_Public {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @param string $plugin_name The name of this plugin.
     * @param string $version     The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, TBO_HOTEL_BOOKING_PLUGIN_URL . 'assets/css/tbo-hotel-booking-public.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, TBO_HOTEL_BOOKING_PLUGIN_URL . 'assets/js/tbo-hotel-booking-public.js', array('jquery'), $this->version, false);
        
        // Localize the script with new data
        wp_localize_script($this->plugin_name, 'tbo_hotel_booking', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tbo_hotel_booking_nonce'),
        ));
    }
    
    /**
     * Register AJAX endpoints for public-facing actions
     */
    public function register_ajax_endpoints() {
        // Search hotels
        add_action('wp_ajax_tbo_search_hotels', array($this, 'ajax_search_hotels'));
        add_action('wp_ajax_nopriv_tbo_search_hotels', array($this, 'ajax_search_hotels'));
        
        // Get hotel details
        add_action('wp_ajax_tbo_get_hotel_details', array($this, 'ajax_get_hotel_details'));
        add_action('wp_ajax_nopriv_tbo_get_hotel_details', array($this, 'ajax_get_hotel_details'));
        
        // Check room availability
        add_action('wp_ajax_tbo_check_availability', array($this, 'ajax_check_availability'));
        add_action('wp_ajax_nopriv_tbo_check_availability', array($this, 'ajax_check_availability'));
        
        // Process booking
        add_action('wp_ajax_tbo_process_booking', array($this, 'ajax_process_booking'));
        add_action('wp_ajax_nopriv_tbo_process_booking', array($this, 'ajax_process_booking'));
    }
    
    /**
     * AJAX handler for hotel search
     */
    public function ajax_search_hotels() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotel_booking_nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        $destination = isset($_POST['destination']) ? sanitize_text_field($_POST['destination']) : '';
        $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
        $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        
        // Validate required fields
        if (empty($destination) || empty($check_in) || empty($check_out)) {
            wp_send_json_error('Required fields are missing.');
        }
        
        try {
            // Get TBO API instance
            $tbo_api = new TBO_Hotel_Booking_API();
            
            // Search hotels
            $results = $tbo_api->search_hotels($destination, $check_in, $check_out, $adults, $children);
            
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler for getting hotel details
     */
    public function ajax_get_hotel_details() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotel_booking_nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        $hotel_id = isset($_POST['hotel_id']) ? sanitize_text_field($_POST['hotel_id']) : '';
        
        // Validate required fields
        if (empty($hotel_id)) {
            wp_send_json_error('Hotel ID is required.');
        }
        
        try {
            // Get TBO API instance
            $tbo_api = new TBO_Hotel_Booking_API();
            
            // Get hotel details
            $hotel = $tbo_api->get_hotel_details($hotel_id);
            
            wp_send_json_success($hotel);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler for checking room availability
     */
    public function ajax_check_availability() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotel_booking_nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        $hotel_id = isset($_POST['hotel_id']) ? sanitize_text_field($_POST['hotel_id']) : '';
        $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
        $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        
        // Validate required fields
        if (empty($hotel_id) || empty($check_in) || empty($check_out)) {
            wp_send_json_error('Required fields are missing.');
        }
        
        try {
            // Get TBO API instance
            $tbo_api = new TBO_Hotel_Booking_API();
            
            // Check availability
            $availability = $tbo_api->check_availability($hotel_id, $check_in, $check_out, $adults, $children);
            
            wp_send_json_success($availability);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
        
        wp_die();
    }
    
    /**
     * AJAX handler for processing bookings
     */
    public function ajax_process_booking() {
        // Check nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'tbo_hotel_booking_nonce')) {
            wp_send_json_error('Invalid security token.');
        }
        
        // Get booking data
        $hotel_id = isset($_POST['hotel_id']) ? sanitize_text_field($_POST['hotel_id']) : '';
        $room_id = isset($_POST['room_id']) ? sanitize_text_field($_POST['room_id']) : '';
        $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
        $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
        $adults = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
        $children = isset($_POST['children']) ? intval($_POST['children']) : 0;
        $guest_info = isset($_POST['guest_info']) ? $_POST['guest_info'] : array();
        $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
        
        // Validate required fields
        if (empty($hotel_id) || empty($room_id) || empty($check_in) || empty($check_out) || empty($guest_info) || empty($payment_method)) {
            wp_send_json_error('Required fields are missing.');
        }
        
        try {
            // Get TBO API instance
            $tbo_api = new TBO_Hotel_Booking_API();
            
            // Process booking
            $booking = $tbo_api->book_hotel($hotel_id, $room_id, $check_in, $check_out, $adults, $children, $guest_info, $payment_method);
            
            // Save booking in database
            global $wpdb;
            
            // Start transaction
            $wpdb->query('START TRANSACTION');
            
            try {
                // Insert booking
                $wpdb->insert(
                    $wpdb->prefix . 'tbo_bookings',
                    array(
                        'booking_number' => $booking['booking_number'],
                        'user_id' => get_current_user_id(),
                        'hotel_id' => $hotel_id,
                        'hotel_name' => $booking['hotel_name'],
                        'check_in' => $check_in,
                        'check_out' => $check_out,
                        'adults' => $adults,
                        'children' => $children,
                        'total_amount' => $booking['total_amount'],
                        'status' => 'confirmed',
                        'booking_data' => json_encode($booking),
                    )
                );
                
                $booking_id = $wpdb->insert_id;
                
                // Insert booking items (rooms)
                foreach ($booking['rooms'] as $room) {
                    $wpdb->insert(
                        $wpdb->prefix . 'tbo_booking_items',
                        array(
                            'booking_id' => $booking_id,
                            'room_id' => $room['room_id'],
                            'room_name' => $room['room_name'],
                            'room_type' => $room['room_type'],
                            'quantity' => $room['quantity'],
                            'price' => $room['price'],
                            'taxes' => $room['taxes'],
                            'total' => $room['total'],
                            'room_data' => json_encode($room),
                        )
                    );
                }
                
                // Insert payment
                $wpdb->insert(
                    $wpdb->prefix . 'tbo_payments',
                    array(
                        'booking_id' => $booking_id,
                        'transaction_id' => $booking['payment']['transaction_id'],
                        'amount' => $booking['payment']['amount'],
                        'payment_method' => $payment_method,
                        'status' => $booking['payment']['status'],
                        'payment_data' => json_encode($booking['payment']),
                    )
                );
                
                // Commit transaction
                $wpdb->query('COMMIT');
                
                // Send booking confirmation email
                $this->send_booking_confirmation_email($booking_id);
                
                wp_send_json_success(array(
                    'booking_id' => $booking_id,
                    'booking_number' => $booking['booking_number'],
                    'redirect_url' => site_url('/booking-confirmation/?booking_id=' . $booking_id),
                ));
                
            } catch (Exception $e) {
                // Rollback transaction
                $wpdb->query('ROLLBACK');
                throw $e;
            }
            
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
        
        wp_die();
    }
    
    /**
     * Send booking confirmation email
     *
     * @param int $booking_id The booking ID
     */
    private function send_booking_confirmation_email($booking_id) {
        global $wpdb;
        
        // Get booking data
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}tbo_bookings WHERE id = %d",
            $booking_id
        ), ARRAY_A);
        
        if (!$booking) {
            return;
        }
        
        // Get user email
        $user_info = get_userdata($booking['user_id']);
        $user_email = $user_info ? $user_info->user_email : '';
        
        if (empty($user_email)) {
            return;
        }
        
        // Email subject
        $subject = sprintf(__('Your Booking Confirmation: %s', 'tbo-hotel-booking'), $booking['booking_number']);
        
        // Email message
        $message = sprintf(
            __('
Hello,

Thank you for your booking!

Booking Details:
- Booking Number: %s
- Hotel: %s
- Check-in: %s
- Check-out: %s
- Total Amount: %s

You can view your booking details here: %s

Thank you for choosing our service.

Best regards,
The %s Team
', 'tbo-hotel-booking'),
            $booking['booking_number'],
            $booking['hotel_name'],
            $booking['check_in'],
            $booking['check_out'],
            number_format($booking['total_amount'], 2),
            site_url('/my-account/bookings/' . $booking_id),
            get_bloginfo('name')
        );
        
        // Headers
        $headers = array('Content-Type: text/html; charset=UTF-8');
        
        // Send email
        wp_mail($user_email, $subject, nl2br($message), $headers);
    }
}
