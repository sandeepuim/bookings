<?php
/**
 * Provide a admin area view for the plugin settings
 *
 * This file is used to markup the admin-facing settings of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    TBO_Hotel_Booking
 * @subpackage TBO_Hotel_Booking/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Process form submission
if (isset($_POST['tbo_settings_submit']) && check_admin_referer('tbo_settings_nonce', 'tbo_settings_nonce')) {
    // Sanitize and save settings
    $settings = array(
        'api_base_url' => esc_url_raw($_POST['api_base_url']),
        'api_username' => sanitize_text_field($_POST['api_username']),
        'api_password' => sanitize_text_field($_POST['api_password']),
        'api_client_id' => sanitize_text_field($_POST['api_client_id']),
        'api_client_secret' => sanitize_text_field($_POST['api_client_secret']),
        'default_currency' => sanitize_text_field($_POST['default_currency']),
        'default_country' => sanitize_text_field($_POST['default_country']),
        'results_per_page' => intval($_POST['results_per_page']),
        'cache_duration' => intval($_POST['cache_duration']),
        'debug_mode' => isset($_POST['debug_mode']) ? 1 : 0,
    );
    
    update_option('tbo_hotel_booking_settings', $settings);
    
    // Delete any stored transients
    delete_transient('tbo_api_token');
    
    // Show success message
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Settings saved successfully.', 'tbo-hotel-booking') . '</p></div>';
}

// Get current settings
$settings = get_option('tbo_hotel_booking_settings', array());

// Set default values if not set
$api_base_url = isset($settings['api_base_url']) ? $settings['api_base_url'] : 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/';
$api_username = isset($settings['api_username']) ? $settings['api_username'] : 'YOLANDATHTest';
$api_password = isset($settings['api_password']) ? $settings['api_password'] : 'Yol@40360746';
$api_client_id = isset($settings['api_client_id']) ? $settings['api_client_id'] : '';
$api_client_secret = isset($settings['api_client_secret']) ? $settings['api_client_secret'] : '';
$default_currency = isset($settings['default_currency']) ? $settings['default_currency'] : 'USD';
$default_country = isset($settings['default_country']) ? $settings['default_country'] : 'IN';
$results_per_page = isset($settings['results_per_page']) ? $settings['results_per_page'] : 10;
$cache_duration = isset($settings['cache_duration']) ? $settings['cache_duration'] : 3600; // 1 hour default
$debug_mode = isset($settings['debug_mode']) ? $settings['debug_mode'] : 0;
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <form method="post" action="">
        <?php wp_nonce_field('tbo_settings_nonce', 'tbo_settings_nonce'); ?>
        
        <div class="tbo-admin-settings">
            <h2><?php _e('TBO API Settings', 'tbo-hotel-booking'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="api_base_url"><?php _e('API Base URL', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="url" name="api_base_url" id="api_base_url" class="regular-text" value="<?php echo esc_attr($api_base_url); ?>" required />
                        <p class="description"><?php _e('The base URL for the TBO API.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="api_username"><?php _e('API Username', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="api_username" id="api_username" class="regular-text" value="<?php echo esc_attr($api_username); ?>" required />
                        <p class="description"><?php _e('Your TBO API username.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="api_password"><?php _e('API Password', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="api_password" id="api_password" class="regular-text" value="<?php echo esc_attr($api_password); ?>" required />
                        <p class="description"><?php _e('Your TBO API password.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="api_client_id"><?php _e('API Client ID', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="text" name="api_client_id" id="api_client_id" class="regular-text" value="<?php echo esc_attr($api_client_id); ?>" />
                        <p class="description"><?php _e('Your TBO API client ID (if required).', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="api_client_secret"><?php _e('API Client Secret', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="password" name="api_client_secret" id="api_client_secret" class="regular-text" value="<?php echo esc_attr($api_client_secret); ?>" />
                        <p class="description"><?php _e('Your TBO API client secret (if required).', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('General Settings', 'tbo-hotel-booking'); ?></h2>
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="default_currency"><?php _e('Default Currency', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <select name="default_currency" id="default_currency">
                            <option value="USD" <?php selected($default_currency, 'USD'); ?>>USD</option>
                            <option value="EUR" <?php selected($default_currency, 'EUR'); ?>>EUR</option>
                            <option value="GBP" <?php selected($default_currency, 'GBP'); ?>>GBP</option>
                            <option value="INR" <?php selected($default_currency, 'INR'); ?>>INR</option>
                            <option value="AED" <?php selected($default_currency, 'AED'); ?>>AED</option>
                            <option value="THB" <?php selected($default_currency, 'THB'); ?>>THB</option>
                        </select>
                        <p class="description"><?php _e('The default currency for hotel prices.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="default_country"><?php _e('Default Guest Country', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <select name="default_country" id="default_country">
                            <option value="US" <?php selected($default_country, 'US'); ?>>United States</option>
                            <option value="GB" <?php selected($default_country, 'GB'); ?>>United Kingdom</option>
                            <option value="IN" <?php selected($default_country, 'IN'); ?>>India</option>
                            <option value="AE" <?php selected($default_country, 'AE'); ?>>United Arab Emirates</option>
                            <option value="TH" <?php selected($default_country, 'TH'); ?>>Thailand</option>
                            <option value="SG" <?php selected($default_country, 'SG'); ?>>Singapore</option>
                        </select>
                        <p class="description"><?php _e('The default nationality for hotel guests.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="results_per_page"><?php _e('Results Per Page', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="results_per_page" id="results_per_page" class="small-text" value="<?php echo esc_attr($results_per_page); ?>" min="5" max="50" />
                        <p class="description"><?php _e('Number of hotels to display per page in search results.', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="cache_duration"><?php _e('Cache Duration', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <input type="number" name="cache_duration" id="cache_duration" class="small-text" value="<?php echo esc_attr($cache_duration); ?>" min="0" />
                        <p class="description"><?php _e('How long to cache API responses in seconds (0 to disable caching).', 'tbo-hotel-booking'); ?></p>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row">
                        <label for="debug_mode"><?php _e('Debug Mode', 'tbo-hotel-booking'); ?></label>
                    </th>
                    <td>
                        <label>
                            <input type="checkbox" name="debug_mode" id="debug_mode" value="1" <?php checked($debug_mode, 1); ?> />
                            <?php _e('Enable debug logging', 'tbo-hotel-booking'); ?>
                        </label>
                        <p class="description"><?php _e('Log API requests and responses for debugging purposes.', 'tbo-hotel-booking'); ?></p>
                        
                        <?php if ($debug_mode && class_exists('TBO_Hotel_Booking_Logger')) : 
                            $logger = new TBO_Hotel_Booking_Logger();
                            $log_file = $logger->get_log_file_path();
                        ?>
                            <div class="debug-actions" style="margin-top: 10px;">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=tbo-hotel-booking-api-test&action=view_logs')); ?>" class="button button-secondary"><?php _e('View Logs', 'tbo-hotel-booking'); ?></a>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=tbo-hotel-booking-settings&action=clear_logs&_wpnonce=' . wp_create_nonce('clear_logs'))); ?>" class="button button-secondary" onclick="return confirm('<?php _e('Are you sure you want to clear the logs?', 'tbo-hotel-booking'); ?>');"><?php _e('Clear Logs', 'tbo-hotel-booking'); ?></a>
                            </div>
                            <p class="description"><?php printf(__('Log file: %s', 'tbo-hotel-booking'), $log_file); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
            
            <p class="submit">
                <input type="submit" name="tbo_settings_submit" id="submit" class="button button-primary" value="<?php _e('Save Settings', 'tbo-hotel-booking'); ?>" />
                <a href="<?php echo esc_url(admin_url('admin.php?page=tbo-hotel-booking-api-test')); ?>" class="button button-secondary"><?php _e('Test API Connection', 'tbo-hotel-booking'); ?></a>
            </p>
        </div>
    </form>
</div>

<style>
.tbo-admin-settings h2 {
    margin-top: 30px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}
</style>
