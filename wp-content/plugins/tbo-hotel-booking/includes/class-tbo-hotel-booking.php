<?php
/**
 * The main plugin class
 */
class TBO_Hotel_Booking {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        $this->version = TBO_HOTEL_BOOKING_VERSION;
        $this->plugin_name = 'tbo-hotel-booking';
        
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->register_post_types();
        $this->register_taxonomies();
        $this->register_shortcodes();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking-loader.php';
        
        // The class responsible for defining all actions in the admin area
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'admin/class-tbo-hotel-booking-admin.php';
        
        // The class responsible for defining all actions in the public-facing side
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'public/class-tbo-hotel-booking-public.php';
        
        // The class responsible for defining all custom post types
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking-post-types.php';
        
        // The class responsible for defining all taxonomies
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking-taxonomies.php';
        
        // The class responsible for defining all shortcodes
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking-shortcodes.php';
        
        // The class responsible for TBO API integration
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/api/class-tbo-hotel-booking-api.php';
        
        // The class responsible for logging
        require_once TBO_HOTEL_BOOKING_PLUGIN_DIR . 'includes/class-tbo-hotel-booking-logger.php';
        
        $this->loader = new TBO_Hotel_Booking_Loader();
    }

    /**
     * Register all of the hooks related to the admin area.
     */
    private function define_admin_hooks() {
        $plugin_admin = new TBO_Hotel_Booking_Admin($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     */
    private function define_public_hooks() {
        $plugin_public = new TBO_Hotel_Booking_Public($this->get_plugin_name(), $this->get_version());
        
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    /**
     * Register custom post types.
     */
    private function register_post_types() {
        $post_types = new TBO_Hotel_Booking_Post_Types();
        $this->loader->add_action('init', $post_types, 'register');
    }

    /**
     * Register taxonomies.
     */
    private function register_taxonomies() {
        $taxonomies = new TBO_Hotel_Booking_Taxonomies();
        $this->loader->add_action('init', $taxonomies, 'register');
    }

    /**
     * Register shortcodes.
     */
    private function register_shortcodes() {
        $shortcodes = new TBO_Hotel_Booking_Shortcodes();
        $shortcodes->register();
    }

    /**
     * Run the loader to execute all the hooks.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }

    /**
     * Plugin activation.
     */
    public static function activate() {
        // Create necessary database tables
        self::create_tables();
        
        // Create logs directory if it doesn't exist
        $logs_dir = TBO_HOTEL_BOOKING_PLUGIN_DIR . 'logs';
        if (!file_exists($logs_dir)) {
            mkdir($logs_dir, 0755, true);
        }
        
        // Create an empty index.php file in the logs directory for security
        $index_file = $logs_dir . '/index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
        
        // Create an empty .htaccess file to protect log files
        $htaccess_file = $logs_dir . '/.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "# Disable directory browsing\n";
            $htaccess_content .= "Options -Indexes\n\n";
            $htaccess_content .= "# Deny access to all files\n";
            $htaccess_content .= "<Files *>\n";
            $htaccess_content .= "    Order Deny,Allow\n";
            $htaccess_content .= "    Deny from all\n";
            $htaccess_content .= "</Files>\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Plugin deactivation.
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom database tables.
     */
    private static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Table for storing bookings
        $table_bookings = $wpdb->prefix . 'tbo_bookings';
        
        // Table for storing booking items (rooms)
        $table_booking_items = $wpdb->prefix . 'tbo_booking_items';
        
        // Table for storing payments
        $table_payments = $wpdb->prefix . 'tbo_payments';
        
        // SQL for creating the bookings table
        $sql_bookings = "CREATE TABLE $table_bookings (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_number varchar(50) NOT NULL,
            user_id bigint(20) unsigned NOT NULL,
            hotel_id varchar(50) NOT NULL,
            hotel_name varchar(255) NOT NULL,
            check_in date NOT NULL,
            check_out date NOT NULL,
            adults int(11) NOT NULL DEFAULT 1,
            children int(11) NOT NULL DEFAULT 0,
            total_amount decimal(10,2) NOT NULL DEFAULT 0,
            status varchar(20) NOT NULL DEFAULT 'pending',
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            booking_data longtext,
            PRIMARY KEY  (id),
            KEY booking_number (booking_number),
            KEY user_id (user_id),
            KEY hotel_id (hotel_id),
            KEY status (status)
        ) $charset_collate;";
        
        // SQL for creating the booking items table
        $sql_booking_items = "CREATE TABLE $table_booking_items (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            room_id varchar(50) NOT NULL,
            room_name varchar(255) NOT NULL,
            room_type varchar(100) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            price decimal(10,2) NOT NULL DEFAULT 0,
            taxes decimal(10,2) NOT NULL DEFAULT 0,
            total decimal(10,2) NOT NULL DEFAULT 0,
            room_data longtext,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id)
        ) $charset_collate;";
        
        // SQL for creating the payments table
        $sql_payments = "CREATE TABLE $table_payments (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            booking_id bigint(20) unsigned NOT NULL,
            transaction_id varchar(100) NOT NULL,
            amount decimal(10,2) NOT NULL DEFAULT 0,
            payment_method varchar(50) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'pending',
            payment_date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            payment_data longtext,
            PRIMARY KEY  (id),
            KEY booking_id (booking_id),
            KEY transaction_id (transaction_id),
            KEY status (status)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        dbDelta($sql_bookings);
        dbDelta($sql_booking_items);
        dbDelta($sql_payments);
    }
}
