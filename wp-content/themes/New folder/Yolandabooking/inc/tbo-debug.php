<?php
if (!defined('ABSPATH')) exit;

/**
 * Admin debug page to test TBO endpoints directly from WP admin.
 * Visible only to users with 'manage_options'.
 */
add_action('admin_menu', function() {
    add_theme_page('TBO Debug', 'TBO Debug', 'manage_options', 'yolanda-tbo-debug', 'yolanda_tbo_debug_page');
});

function yolanda_tbo_debug_page() {
    if (!current_user_can('manage_options')) wp_die('Permission denied');

    // Use existing API class if present
    if (!class_exists('Yolanda_TBO_API')) {
        echo '<div class="notice notice-error"><p>TBO API class not found.</p></div>';
        return;
    }

    // Handle clear transients action
    if (isset($_POST['yola_clear_transients'])) {
        if (!check_admin_referer('yola_clear_transients_action', 'yola_clear_transients_nonce', false)) {
            echo '<div class="notice notice-error"><p>Permission check failed.</p></div>';
        } else {
            global $wpdb;
            $like = $wpdb->esc_like('_transient_yola_hotelcodes_') . '%';
            $rows = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s", $like));
            $deleted = array();
            foreach ($rows as $opt) {
                $key = substr($opt, strlen('_transient_'));
                if (delete_transient($key)) $deleted[] = $key;
            }
            echo '<div class="updated"><p>Deleted ' . count($deleted) . ' yola_hotelcodes transients.</p></div>';
        }
    }

    $city = isset($_POST['city_code']) ? sanitize_text_field($_POST['city_code']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : date('Y-m-d', strtotime('+1 day'));
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : date('Y-m-d', strtotime('+2 days'));
    $limit_codes = isset($_POST['limit_codes']) ? intval($_POST['limit_codes']) : 0;

    echo '<div class="wrap"><h1>TBO Debug</h1>';
    // Small admin form to clear cached hotel codes
    echo '<form method="post" style="margin-bottom:1em">';
    wp_nonce_field('yola_clear_transients_action', 'yola_clear_transients_nonce');
    submit_button('Clear Yola hotelCodes cache', 'secondary', 'yola_clear_transients');
    echo '</form>';
    $api_tmp = new Yolanda_TBO_API();
    $auth = $api_tmp->get_auth_header();
    $masked = preg_replace('/(.{6})(.*)(.{6})/', '$1***$3', $auth);
    echo '<p><strong>Auth header (masked):</strong> ' . esc_html($masked) . '</p>';
    echo '<form method="post">';
    echo '<table class="form-table"><tr><th>City Code</th><td><input name="city_code" value="' . esc_attr($city) . '" /></td></tr>';
    echo '<tr><th>Check In</th><td><input name="check_in" value="' . esc_attr($check_in) . '" /></td></tr>';
    echo '<tr><th>Check Out</th><td><input name="check_out" value="' . esc_attr($check_out) . '" /></td></tr>';
    echo '<tr><th>Limit codes</th><td><input name="limit_codes" value="' . esc_attr($limit_codes) . '" placeholder="0=no-limit" /></td></tr>';
    echo '</table>';
    submit_button('Run Test');
    echo '</form>';

    if ($city) {
        echo '<h2>HotelCodeList (GET then POST fallback)</h2>';
        $api = new Yolanda_TBO_API();
        $resp_get = $api->request('HotelCodeList', array('CityCode' => $city), 'GET');
        echo '<h3>GET response</h3><pre>' . esc_html(is_wp_error($resp_get) ? $resp_get->get_error_message() : wp_json_encode($resp_get, JSON_PRETTY_PRINT)) . '</pre>';

        $codes = array();
        if (!is_wp_error($resp_get)) $codes = yolanda_extract_hotel_codes((array)$resp_get);
        if (empty($codes)) {
            $resp_post = $api->request('HotelCodeList', array('CityCode' => $city), 'POST');
            echo '<h3>POST response</h3><pre>' . esc_html(is_wp_error($resp_post) ? $resp_post->get_error_message() : wp_json_encode($resp_post, JSON_PRETTY_PRINT)) . '</pre>';
            if (!is_wp_error($resp_post)) $codes = yolanda_extract_hotel_codes((array)$resp_post);
        }

        echo '<h3>Extracted hotel codes</h3><pre>' . esc_html(empty($codes) ? '[]' : wp_json_encode($codes, JSON_PRETTY_PRINT)) . '</pre>';

        if (!empty($codes)) {
            echo '<h2>Search (first chunk)</h2>';
            $chunk = $codes;
            if ($limit_codes > 0) $chunk = array_slice($chunk, 0, $limit_codes);
            $payload = array(
                'CheckIn' => $check_in,
                'CheckOut' => $check_out,
                'HotelCodes' => implode(',', $chunk),
                'GuestNationality' => 'IN',
                'PaxRooms' => array(array('Adults' => 2)),
                'ResponseTime' => 20,
                'IsDetailedResponse' => true,
            );
            // Also perform a raw HTTP POST to show status, headers and raw body for debugging
            $tbo_search_url = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/Search';
            $args = array(
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Authorization' => $api->get_auth_header(),
                ),
                'body' => wp_json_encode($payload),
                'timeout' => 40,
            );

            $raw = wp_remote_post($tbo_search_url, $args);
            $status = is_wp_error($raw) ? 'error' : wp_remote_retrieve_response_code($raw);
            $raw_body = is_wp_error($raw) ? $raw->get_error_message() : wp_remote_retrieve_body($raw);
            $raw_headers = is_wp_error($raw) ? array() : wp_remote_retrieve_headers($raw);

            echo '<h3>Search HTTP status</h3><pre>' . esc_html($status) . '</pre>';
            echo '<h3>Search response headers</h3><pre>' . esc_html(wp_json_encode($raw_headers, JSON_PRETTY_PRINT)) . '</pre>';
            echo '<h3>Search raw body (first 8000 chars)</h3><pre>' . esc_html(substr($raw_body,0,8000)) . '</pre>';

            // Also show decoded JSON if possible
            $decoded = null;
            if (!is_wp_error($raw)) {
                $decoded = json_decode($raw_body, true);
            }
            echo '<h3>Search decoded (truncated)</h3><pre>' . esc_html($decoded ? wp_json_encode($decoded, JSON_PRETTY_PRINT) : 'Unable to decode JSON') . '</pre>';
        }
    }

    echo '</div>';
}
