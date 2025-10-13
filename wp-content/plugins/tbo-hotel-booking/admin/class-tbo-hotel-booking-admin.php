<?php
/**
 * The admin-specific functionality of the plugin.
 */
class TBO_Hotel_Booking_Admin {

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
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, TBO_HOTEL_BOOKING_PLUGIN_URL . 'assets/css/tbo-hotel-booking-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, TBO_HOTEL_BOOKING_PLUGIN_URL . 'assets/js/tbo-hotel-booking-admin.js', array('jquery'), $this->version, false);
        
        // Localize the script with new data
        wp_localize_script($this->plugin_name, 'tbo_hotel_booking_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('tbo_hotel_booking_nonce'),
        ));
    }

    /**
     * Add plugin admin menu.
     */
    public function add_plugin_admin_menu() {
        // Main menu
        add_menu_page(
            __('TBO Hotel Booking', 'tbo-hotel-booking'),
            __('Hotel Booking', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_setup_page'),
            'dashicons-building',
            26
        );
        
        // Settings submenu
        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'tbo-hotel-booking'),
            __('Settings', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_plugin_settings_page')
        );
        
        // Bookings submenu
        add_submenu_page(
            $this->plugin_name,
            __('Bookings', 'tbo-hotel-booking'),
            __('Bookings', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-bookings',
            array($this, 'display_bookings_page')
        );
        
        // Hotels submenu
        add_submenu_page(
            $this->plugin_name,
            __('Hotels', 'tbo-hotel-booking'),
            __('Hotels', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-hotels',
            array($this, 'display_hotels_page')
        );
        
        // Payments submenu
        add_submenu_page(
            $this->plugin_name,
            __('Payments', 'tbo-hotel-booking'),
            __('Payments', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-payments',
            array($this, 'display_payments_page')
        );
        
        // Offers submenu
        add_submenu_page(
            $this->plugin_name,
            __('Offers', 'tbo-hotel-booking'),
            __('Offers', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-offers',
            array($this, 'display_offers_page')
        );
        
        // API Test submenu
        add_submenu_page(
            $this->plugin_name,
            __('API Test', 'tbo-hotel-booking'),
            __('API Test', 'tbo-hotel-booking'),
            'manage_options',
            $this->plugin_name . '-api-test',
            array($this, 'display_api_test_page')
        );
    }

    /**
     * Render the setup page for this plugin.
     */
    public function display_plugin_setup_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-display.php';
    }
    
    /**
     * Render the settings page for this plugin.
     */
    public function display_plugin_settings_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-settings.php';
    }
    
    /**
     * Render the bookings page.
     */
    public function display_bookings_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-bookings.php';
    }
    
    /**
     * Render the hotels page.
     */
    public function display_hotels_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-hotels.php';
    }
    
    /**
     * Render the payments page.
     */
    public function display_payments_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-payments.php';
    }
    
    /**
     * Render the offers page.
     */
    public function display_offers_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-offers.php';
    }
    
    /**
     * Render the API test page.
     */
    public function display_api_test_page() {
        include_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/partials/tbo-hotel-booking-admin-api-test.php';
    }
}
