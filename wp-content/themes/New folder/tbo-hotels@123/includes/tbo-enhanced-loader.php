<?php
/**
 * TBO Hotels Enhancement Loader
 * 
 * This file handles loading of the enhanced TBO API implementation
 * and related assets.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Include the installation script
require_once get_template_directory() . '/includes/tbo-enhanced-install.php';

/**
 * Register and enqueue enhanced TBO API scripts and styles
 */
function tbo_enhanced_enqueue_scripts() {
    // Register CSS
    wp_register_style(
        'tbo-enhanced-styles',
        get_template_directory_uri() . '/assets/css/tbo-enhanced.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/tbo-enhanced.css')
    );
    
    // Register JS
    wp_register_script(
        'tbo-enhanced-script',
        get_template_directory_uri() . '/assets/js/tbo-enhanced.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/tbo-enhanced.js'),
        true
    );
    
    // Register AJAX response fix script
    wp_register_script(
        'ajax-response-fix',
        get_template_directory_uri() . '/assets/js/ajax-response-fix.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/ajax-response-fix.js'),
        true
    );
    
    // Enqueue CSS
    wp_enqueue_style('tbo-enhanced-styles');
    
    // Enqueue scripts
    wp_enqueue_script('ajax-response-fix');
    wp_enqueue_script('tbo-enhanced-script');
    
    // Localize script with AJAX URL
    wp_localize_script('tbo-enhanced-script', 'tbo_ajax_obj', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('tbo-enhanced-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'tbo_enhanced_enqueue_scripts');

/**
 * Include enhanced TBO API implementation
 */
function tbo_enhanced_load_api() {
    // Include the enhanced API file
    require_once get_template_directory() . '/includes/tbo-api-enhancement.php';
}
add_action('init', 'tbo_enhanced_load_api', 5); // Priority 5 to load before other plugins

/**
 * Add settings page for TBO API credentials
 */
function tbo_enhanced_add_settings_page() {
    add_submenu_page(
        'options-general.php',
        'TBO API Settings',
        'TBO API Settings',
        'manage_options',
        'tbo-api-settings',
        'tbo_enhanced_settings_page'
    );
}
add_action('admin_menu', 'tbo_enhanced_add_settings_page');

/**
 * Register TBO API settings
 */
function tbo_enhanced_register_settings() {
    register_setting('tbo-api-settings-group', 'tbo_api_username');
    register_setting('tbo-api-settings-group', 'tbo_api_password');
    register_setting('tbo-api-settings-group', 'tbo_api_cache_time');
}
add_action('admin_init', 'tbo_enhanced_register_settings');

/**
 * Render TBO API settings page
 */
function tbo_enhanced_settings_page() {
    ?>
    <div class="wrap">
        <h1>TBO API Settings</h1>
        
        <form method="post" action="options.php">
            <?php settings_fields('tbo-api-settings-group'); ?>
            <?php do_settings_sections('tbo-api-settings-group'); ?>
            
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Username</th>
                    <td>
                        <input type="text" name="tbo_api_username" value="<?php echo esc_attr(get_option('tbo_api_username')); ?>" class="regular-text" />
                        <p class="description">Your TBO API username.</p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">API Password</th>
                    <td>
                        <input type="password" name="tbo_api_password" value="<?php echo esc_attr(get_option('tbo_api_password')); ?>" class="regular-text" />
                        <p class="description">Your TBO API password.</p>
                    </td>
                </tr>
                
                <tr valign="top">
                    <th scope="row">Cache Time</th>
                    <td>
                        <select name="tbo_api_cache_time">
                            <option value="1800" <?php selected(get_option('tbo_api_cache_time'), '1800'); ?>>30 Minutes</option>
                            <option value="3600" <?php selected(get_option('tbo_api_cache_time', '3600'), '3600'); ?>>1 Hour</option>
                            <option value="7200" <?php selected(get_option('tbo_api_cache_time'), '7200'); ?>>2 Hours</option>
                            <option value="14400" <?php selected(get_option('tbo_api_cache_time'), '14400'); ?>>4 Hours</option>
                            <option value="28800" <?php selected(get_option('tbo_api_cache_time'), '28800'); ?>>8 Hours</option>
                            <option value="86400" <?php selected(get_option('tbo_api_cache_time'), '86400'); ?>>24 Hours</option>
                        </select>
                        <p class="description">How long to cache API results.</p>
                    </td>
                </tr>
            </table>
            
            <div class="actions">
                <input type="submit" class="button-primary" value="Save Settings" />
                <a href="<?php echo admin_url('admin.php?page=tbo-api-settings&action=clear-cache'); ?>" class="button">Clear Cache</a>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Clear TBO API cache
 */
function tbo_enhanced_clear_cache() {
    if (isset($_GET['page']) && $_GET['page'] === 'tbo-api-settings' && 
        isset($_GET['action']) && $_GET['action'] === 'clear-cache') {
        
        global $wpdb;
        
        // Delete all transients with tbo_api_ prefix
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_tbo_api_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_tbo_api_%'");
        
        // Redirect back to settings page
        wp_redirect(admin_url('admin.php?page=tbo-api-settings&cache-cleared=1'));
        exit;
    }
    
    // Show notice if cache was cleared
    if (isset($_GET['page']) && $_GET['page'] === 'tbo-api-settings' && 
        isset($_GET['cache-cleared']) && $_GET['cache-cleared'] === '1') {
        
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p>TBO API cache has been cleared.</p></div>';
        });
    }
}
add_action('admin_init', 'tbo_enhanced_clear_cache');

/**
 * Add meta box to show debug info for hotel search
 */
function tbo_enhanced_add_debug_meta_box() {
    // Check if user is an administrator
    if (current_user_can('manage_options')) {
        add_meta_box(
            'tbo-api-debug',
            'TBO API Debug',
            'tbo_enhanced_debug_meta_box_callback',
            'page',
            'normal',
            'low'
        );
    }
}
add_action('add_meta_boxes', 'tbo_enhanced_add_debug_meta_box');

/**
 * Render TBO API debug meta box
 */
function tbo_enhanced_debug_meta_box_callback($post) {
    // Check if this is a hotel search page
    $post_content = $post->post_content;
    
    if (strpos($post_content, '[tbo_hotel_search]') !== false || 
        strpos($post_content, '[tbo_hotels]') !== false) {
        ?>
        <div class="tbo-debug-info">
            <h3>TBO API Debug</h3>
            
            <p>This page contains a TBO Hotel Search shortcode. The enhanced TBO API implementation has been loaded.</p>
            
            <h4>API Settings</h4>
            <ul>
                <li><strong>Username:</strong> <?php echo esc_html(get_option('tbo_api_username', defined('TBO_API_USERNAME') ? TBO_API_USERNAME : 'Not set')); ?></li>
                <li><strong>Password:</strong> <?php echo get_option('tbo_api_password') || defined('TBO_API_PASSWORD') ? '********' : 'Not set'; ?></li>
                <li><strong>Cache Time:</strong> <?php echo human_time_diff(0, intval(get_option('tbo_api_cache_time', 3600))); ?></li>
            </ul>
            
            <h4>Enhanced Features</h4>
            <ul>
                <li>Improved error handling with fallback to stale cache</li>
                <li>Dynamic cache expiration based on search date proximity</li>
                <li>Better parameter validation</li>
                <li>Enhanced frontend with loading indicators</li>
                <li>AJAX response sanitization to prevent JavaScript errors</li>
            </ul>
            
            <p><a href="<?php echo admin_url('admin.php?page=tbo-api-settings'); ?>" class="button">Edit API Settings</a></p>
        </div>
        <?php
    } else {
        echo '<p>This page does not contain a TBO Hotel Search shortcode.</p>';
    }
}

/**
 * Add shortcode for enhanced hotel search
 */
function tbo_enhanced_hotel_search_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Search for Hotels',
        'default_city_id' => '',
        'default_city_name' => '',
        'results_page' => '',
    ), $atts);
    
    ob_start();
    ?>
    <div class="tbo-enhanced-search-container">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <form class="tbo-search-form" method="get" action="<?php echo esc_url($atts['results_page'] ? get_permalink($atts['results_page']) : ''); ?>">
            <div class="form-group">
                <label for="city_id">Destination</label>
                <input type="text" id="city-search" class="city-search" name="city_name" placeholder="Enter city name" 
                       value="<?php echo esc_attr($atts['default_city_name']); ?>" autocomplete="off" required>
                <input type="hidden" id="city_id" name="city_id" value="<?php echo esc_attr($atts['default_city_id']); ?>">
                <div class="city-search-results"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="check_in">Check-in</label>
                    <input type="date" id="check_in" name="check_in" 
                           value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="check_out">Check-out</label>
                    <input type="date" id="check_out" name="check_out" 
                           value="<?php echo date('Y-m-d', strtotime('+2 days')); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="adults">Adults</label>
                    <select id="adults" name="adults">
                        <?php for ($i = 1; $i <= 6; $i++) : ?>
                            <option value="<?php echo $i; ?>" <?php selected($i, 2); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="children">Children</label>
                    <select id="children" name="children">
                        <?php for ($i = 0; $i <= 4; $i++) : ?>
                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="child-ages-container" style="display: none;">
                <div class="form-row child-ages">
                    <!-- Child age inputs will be added dynamically -->
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="search-button">Search Hotels</button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle check-in date changes
        $('#check_in').on('change', function() {
            const checkInDate = new Date($(this).val());
            const checkOutDate = new Date($('#check_out').val());
            
            // Ensure check-out is after check-in
            if (checkInDate >= checkOutDate) {
                const newCheckOutDate = new Date(checkInDate);
                newCheckOutDate.setDate(newCheckOutDate.getDate() + 1);
                
                const year = newCheckOutDate.getFullYear();
                let month = newCheckOutDate.getMonth() + 1;
                let day = newCheckOutDate.getDate();
                
                if (month < 10) month = '0' + month;
                if (day < 10) day = '0' + day;
                
                $('#check_out').val(`${year}-${month}-${day}`);
            }
            
            // Update min date of check-out
            $('#check_out').attr('min', $(this).val());
        });
        
        // Handle children count changes
        $('#children').on('change', function() {
            const childCount = parseInt($(this).val());
            const $container = $('.child-ages');
            
            // Clear existing age inputs
            $container.empty();
            
            if (childCount > 0) {
                $('.child-ages-container').show();
                
                // Add age inputs for each child
                for (let i = 0; i < childCount; i++) {
                    $container.append(`
                        <div class="form-group">
                            <label for="child_age_${i}">Child ${i + 1} Age</label>
                            <select id="child_age_${i}" name="child_ages[]" class="child-age">
                                ${generateAgeOptions()}
                            </select>
                        </div>
                    `);
                }
            } else {
                $('.child-ages-container').hide();
            }
        });
        
        // Generate age options for children
        function generateAgeOptions() {
            let options = '';
            for (let i = 0; i <= 17; i++) {
                options += `<option value="${i}">${i}</option>`;
            }
            return options;
        }
        
        // Handle city search
        $('#city-search').on('input', function() {
            const query = $(this).val();
            
            if (query.length < 3) {
                $('.city-search-results').empty().hide();
                return;
            }
            
            // Perform city search
            $.ajax({
                url: tbo_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbo_enhanced_city_search',
                    query: query,
                    nonce: tbo_ajax_obj.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayCityResults(response.data);
                    } else {
                        $('.city-search-results').html('<div class="no-results">No cities found</div>').show();
                    }
                }
            });
        });
        
        // Display city search results
        function displayCityResults(cities) {
            const $results = $('.city-search-results');
            $results.empty();
            
            if (cities.length === 0) {
                $results.html('<div class="no-results">No cities found</div>').show();
                return;
            }
            
            $.each(cities, function(index, city) {
                $results.append(`
                    <div class="city-result" data-city-id="${city.id}" data-city-name="${city.name}">
                        ${city.name}${city.country ? ', ' + city.country : ''}
                    </div>
                `);
            });
            
            $results.show();
        }
        
        // Handle city selection
        $(document).on('click', '.city-result', function() {
            const cityId = $(this).data('city-id');
            const cityName = $(this).data('city-name');
            
            $('#city_id').val(cityId);
            $('#city-search').val(cityName);
            
            $('.city-search-results').empty().hide();
        });
        
        // Hide city results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.city-search, .city-search-results').length) {
                $('.city-search-results').empty().hide();
            }
        });
    });
    </script>
    
    <style>
    .tbo-enhanced-search-container {
        max-width: 500px;
        margin: 0 auto;
        padding: 20px;
        background-color: #f9f9f9;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .tbo-enhanced-search-container h3 {
        margin-top: 0;
        margin-bottom: 20px;
        text-align: center;
    }
    
    .tbo-search-form .form-group {
        margin-bottom: 15px;
    }
    
    .tbo-search-form .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .tbo-search-form .form-row .form-group {
        flex: 1;
        margin-bottom: 0;
    }
    
    .tbo-search-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    
    .tbo-search-form input[type="text"],
    .tbo-search-form input[type="date"],
    .tbo-search-form select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .tbo-search-form input[type="date"] {
        padding: 7px 12px;
    }
    
    .tbo-search-form .search-button {
        width: 100%;
        padding: 10px;
        background-color: #3498db;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .tbo-search-form .search-button:hover {
        background-color: #2980b9;
    }
    
    .city-search-results {
        position: absolute;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        z-index: 100;
        display: none;
    }
    
    .city-result {
        padding: 8px 12px;
        cursor: pointer;
    }
    
    .city-result:hover {
        background-color: #f5f5f5;
    }
    
    .no-results {
        padding: 8px 12px;
        color: #777;
        font-style: italic;
    }
    
    .child-ages .form-group {
        margin-bottom: 10px;
    }
    
    .form-actions {
        margin-top: 20px;
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('tbo_enhanced_search', 'tbo_enhanced_hotel_search_shortcode');

/**
 * Add shortcode for enhanced hotel results
 */
function tbo_enhanced_hotel_results_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Hotel Results',
        'default_city_id' => '',
    ), $atts);
    
    // Get search parameters
    $city_id = isset($_GET['city_id']) ? sanitize_text_field($_GET['city_id']) : $atts['default_city_id'];
    $city_name = isset($_GET['city_name']) ? sanitize_text_field($_GET['city_name']) : '';
    $check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : date('Y-m-d', strtotime('+1 day'));
    $check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : date('Y-m-d', strtotime('+2 days'));
    $adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
    $children = isset($_GET['children']) ? intval($_GET['children']) : 0;
    $child_ages = isset($_GET['child_ages']) ? array_map('intval', $_GET['child_ages']) : array();
    
    ob_start();
    ?>
    <div class="tbo-enhanced-results-container">
        <h1 class="page-title"><?php echo esc_html($atts['title']); ?></h1>
        
        <div class="search-summary">
            <div class="search-info">
                <div class="destination">
                    <i class="fas fa-map-marker-alt"></i> 
                    <span><?php echo esc_html($city_name ? $city_name : 'Destination'); ?></span>
                </div>
                <div class="dates">
                    <i class="fas fa-calendar-alt"></i> 
                    <span><?php echo date('d M Y', strtotime($check_in)); ?> - <?php echo date('d M Y', strtotime($check_out)); ?></span>
                    <small>(<?php echo floor((strtotime($check_out) - strtotime($check_in)) / 86400); ?> nights)</small>
                </div>
                <div class="guests">
                    <i class="fas fa-users"></i> 
                    <span><?php echo $adults; ?> Adults<?php echo $children ? ', ' . $children . ' Children' : ''; ?></span>
                </div>
            </div>
            <div class="search-actions">
                <button class="modify-search-btn">Modify Search</button>
            </div>
        </div>
        
        <div class="modify-search-form" style="display: none;">
            <form class="tbo-search-form" method="get">
                <div class="form-group">
                    <label for="city_id">Destination</label>
                    <input type="text" id="city-search" class="city-search" name="city_name" 
                           value="<?php echo esc_attr($city_name); ?>" placeholder="Enter city name" autocomplete="off" required>
                    <input type="hidden" id="city_id" name="city_id" value="<?php echo esc_attr($city_id); ?>">
                    <div class="city-search-results"></div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="check_in">Check-in</label>
                        <input type="date" id="check_in" name="check_in" 
                               value="<?php echo esc_attr($check_in); ?>" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="check_out">Check-out</label>
                        <input type="date" id="check_out" name="check_out" 
                               value="<?php echo esc_attr($check_out); ?>" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="adults">Adults</label>
                        <select id="adults" name="adults">
                            <?php for ($i = 1; $i <= 6; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected($i, $adults); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="children">Children</label>
                        <select id="children" name="children">
                            <?php for ($i = 0; $i <= 4; $i++) : ?>
                                <option value="<?php echo $i; ?>" <?php selected($i, $children); ?>><?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                
                <div class="child-ages-container" <?php echo $children > 0 ? '' : 'style="display: none;"'; ?>>
                    <div class="form-row child-ages">
                        <?php for ($i = 0; $i < $children; $i++) : ?>
                            <div class="form-group">
                                <label for="child_age_<?php echo $i; ?>">Child <?php echo $i + 1; ?> Age</label>
                                <select id="child_age_<?php echo $i; ?>" name="child_ages[]" class="child-age">
                                    <?php for ($j = 0; $j <= 17; $j++) : ?>
                                        <option value="<?php echo $j; ?>" <?php selected($j, isset($child_ages[$i]) ? $child_ages[$i] : 0); ?>><?php echo $j; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="search-button">Update Search</button>
                    <button type="button" class="cancel-button">Cancel</button>
                </div>
            </form>
        </div>
        
        <div class="hotels-container">
            <div class="hotels-list" id="hotels-list">
                <div class="loading-hotels">
                    <div class="spinner"></div>
                    <p>Searching for hotels...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Toggle modify search form
        $('.modify-search-btn').on('click', function() {
            $('.modify-search-form').slideToggle();
        });
        
        $('.cancel-button').on('click', function() {
            $('.modify-search-form').slideUp();
        });
        
        // Handle check-in date changes
        $('#check_in').on('change', function() {
            const checkInDate = new Date($(this).val());
            const checkOutDate = new Date($('#check_out').val());
            
            // Ensure check-out is after check-in
            if (checkInDate >= checkOutDate) {
                const newCheckOutDate = new Date(checkInDate);
                newCheckOutDate.setDate(newCheckOutDate.getDate() + 1);
                
                const year = newCheckOutDate.getFullYear();
                let month = newCheckOutDate.getMonth() + 1;
                let day = newCheckOutDate.getDate();
                
                if (month < 10) month = '0' + month;
                if (day < 10) day = '0' + day;
                
                $('#check_out').val(`${year}-${month}-${day}`);
            }
            
            // Update min date of check-out
            $('#check_out').attr('min', $(this).val());
        });
        
        // Handle children count changes
        $('#children').on('change', function() {
            const childCount = parseInt($(this).val());
            const $container = $('.child-ages');
            
            // Clear existing age inputs
            $container.empty();
            
            if (childCount > 0) {
                $('.child-ages-container').show();
                
                // Add age inputs for each child
                for (let i = 0; i < childCount; i++) {
                    $container.append(`
                        <div class="form-group">
                            <label for="child_age_${i}">Child ${i + 1} Age</label>
                            <select id="child_age_${i}" name="child_ages[]" class="child-age">
                                ${generateAgeOptions()}
                            </select>
                        </div>
                    `);
                }
            } else {
                $('.child-ages-container').hide();
            }
        });
        
        // Generate age options for children
        function generateAgeOptions() {
            let options = '';
            for (let i = 0; i <= 17; i++) {
                options += `<option value="${i}">${i}</option>`;
            }
            return options;
        }
        
        // Handle city search
        $('#city-search').on('input', function() {
            const query = $(this).val();
            
            if (query.length < 3) {
                $('.city-search-results').empty().hide();
                return;
            }
            
            // Perform city search
            $.ajax({
                url: tbo_ajax_obj.ajax_url,
                type: 'POST',
                data: {
                    action: 'tbo_enhanced_city_search',
                    query: query,
                    nonce: tbo_ajax_obj.nonce
                },
                success: function(response) {
                    if (response.success) {
                        displayCityResults(response.data);
                    } else {
                        $('.city-search-results').html('<div class="no-results">No cities found</div>').show();
                    }
                }
            });
        });
        
        // Display city search results
        function displayCityResults(cities) {
            const $results = $('.city-search-results');
            $results.empty();
            
            if (cities.length === 0) {
                $results.html('<div class="no-results">No cities found</div>').show();
                return;
            }
            
            $.each(cities, function(index, city) {
                $results.append(`
                    <div class="city-result" data-city-id="${city.id}" data-city-name="${city.name}">
                        ${city.name}${city.country ? ', ' + city.country : ''}
                    </div>
                `);
            });
            
            $results.show();
        }
        
        // Handle city selection
        $(document).on('click', '.city-result', function() {
            const cityId = $(this).data('city-id');
            const cityName = $(this).data('city-name');
            
            $('#city_id').val(cityId);
            $('#city-search').val(cityName);
            
            $('.city-search-results').empty().hide();
        });
        
        // Hide city results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.city-search, .city-search-results').length) {
                $('.city-search-results').empty().hide();
            }
        });
        
        // Perform initial hotel search
        const cityId = '<?php echo esc_js($city_id); ?>';
        const checkIn = '<?php echo esc_js($check_in); ?>';
        const checkOut = '<?php echo esc_js($check_out); ?>';
        const adults = <?php echo esc_js($adults); ?>;
        const children = <?php echo esc_js($children); ?>;
        const childAges = <?php echo json_encode($child_ages); ?>;
        
        // Create room configuration
        const rooms = [{
            adults: adults,
            children: children,
            child_ages: childAges
        }];
        
        // Only search if we have a city ID
        if (cityId) {
            // Let the enhanced TBO functionality handle the search
            TBOEnhanced.performHotelSearch(cityId, checkIn, checkOut, 0, 20, {
                rooms: JSON.stringify(rooms)
            });
        } else {
            // Show no city selected message
            $('#hotels-list').html('<div class="no-hotels-found">Please select a destination to search for hotels.</div>');
        }
    });
    </script>
    
    <style>
    .tbo-enhanced-results-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .search-summary {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background-color: #f5f5f5;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .search-info {
        display: flex;
        gap: 20px;
    }
    
    .search-info i {
        color: #3498db;
        margin-right: 5px;
    }
    
    .search-info small {
        color: #777;
        margin-left: 5px;
    }
    
    .modify-search-btn {
        background-color: #3498db;
        color: #fff;
        border: none;
        padding: 8px 15px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
    }
    
    .modify-search-form {
        background-color: #f9f9f9;
        padding: 20px;
        border-radius: 8px;
        margin-bottom: 20px;
    }
    
    .modify-search-form .form-row {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .modify-search-form .form-group {
        flex: 1;
        margin-bottom: 15px;
    }
    
    .modify-search-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #333;
        font-size: 14px;
    }
    
    .modify-search-form input[type="text"],
    .modify-search-form input[type="date"],
    .modify-search-form select {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .modify-search-form input[type="date"] {
        padding: 7px 12px;
    }
    
    .form-actions {
        display: flex;
        gap: 10px;
    }
    
    .search-button {
        flex: 1;
        padding: 10px;
        background-color: #3498db;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .search-button:hover {
        background-color: #2980b9;
    }
    
    .cancel-button {
        padding: 10px 15px;
        background-color: #e74c3c;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .cancel-button:hover {
        background-color: #c0392b;
    }
    
    .loading-hotels {
        text-align: center;
        padding: 50px 0;
    }
    
    .loading-hotels .spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-hotels p {
        color: #777;
        font-size: 16px;
    }
    
    .hotels-list {
        margin-top: 20px;
    }
    
    .city-search-results {
        position: absolute;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background-color: #fff;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 4px 4px;
        z-index: 100;
        display: none;
    }
    
    .city-result {
        padding: 8px 12px;
        cursor: pointer;
    }
    
    .city-result:hover {
        background-color: #f5f5f5;
    }
    
    .no-results {
        padding: 8px 12px;
        color: #777;
        font-style: italic;
    }
    
    /* Responsive Styles */
    @media (max-width: 768px) {
        .search-info {
            flex-direction: column;
            gap: 10px;
        }
        
        .search-summary {
            flex-direction: column;
            gap: 15px;
        }
        
        .modify-search-form .form-row {
            flex-direction: column;
            gap: 0;
        }
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('tbo_enhanced_results', 'tbo_enhanced_hotel_results_shortcode');

/**
 * Add AJAX handler for city search
 */
add_action('wp_ajax_tbo_enhanced_city_search', 'tbo_enhanced_city_search_handler');
add_action('wp_ajax_nopriv_tbo_enhanced_city_search', 'tbo_enhanced_city_search_handler');

/**
 * AJAX handler for city search
 */
function tbo_enhanced_city_search_handler() {
    // Validate query
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    if (empty($query) || strlen($query) < 3) {
        wp_send_json_error('Search query too short');
        exit;
    }
    
    // Check cache first
    $cache_key = 'tbo_api_city_search_' . md5($query);
    $cached_result = get_transient($cache_key);
    
    if ($cached_result !== false) {
        wp_send_json_success($cached_result);
        exit;
    }
    
    // Mock city data for testing - in a real implementation, this would call the TBO API
    $cities = array(
        array('id' => '150184', 'name' => 'Mumbai', 'country' => 'India'),
        array('id' => '150185', 'name' => 'Delhi', 'country' => 'India'),
        array('id' => '150186', 'name' => 'Bangalore', 'country' => 'India'),
        array('id' => '150187', 'name' => 'Chennai', 'country' => 'India'),
        array('id' => '150188', 'name' => 'Kolkata', 'country' => 'India'),
        array('id' => '150189', 'name' => 'Hyderabad', 'country' => 'India'),
        array('id' => '150190', 'name' => 'Pune', 'country' => 'India'),
        array('id' => '150191', 'name' => 'Ahmedabad', 'country' => 'India'),
        array('id' => '150192', 'name' => 'Jaipur', 'country' => 'India'),
        array('id' => '150193', 'name' => 'Lucknow', 'country' => 'India'),
        array('id' => '150194', 'name' => 'New York', 'country' => 'United States'),
        array('id' => '150195', 'name' => 'Los Angeles', 'country' => 'United States'),
        array('id' => '150196', 'name' => 'Chicago', 'country' => 'United States'),
        array('id' => '150197', 'name' => 'London', 'country' => 'United Kingdom'),
        array('id' => '150198', 'name' => 'Paris', 'country' => 'France'),
        array('id' => '150199', 'name' => 'Tokyo', 'country' => 'Japan'),
        array('id' => '150200', 'name' => 'Sydney', 'country' => 'Australia'),
        array('id' => '150201', 'name' => 'Dubai', 'country' => 'United Arab Emirates'),
        array('id' => '150202', 'name' => 'Singapore', 'country' => 'Singapore'),
        array('id' => '150203', 'name' => 'Hong Kong', 'country' => 'China')
    );
    
    // Filter cities based on query
    $filtered_cities = array();
    $query = strtolower($query);
    
    foreach ($cities as $city) {
        if (strpos(strtolower($city['name']), $query) !== false) {
            $filtered_cities[] = $city;
        }
    }
    
    // Cache the result
    set_transient($cache_key, $filtered_cities, HOUR_IN_SECONDS);
    
    wp_send_json_success($filtered_cities);
    exit;
}

/**
 * Add shortcode for enhanced hotel details
 */
function tbo_enhanced_hotel_details_shortcode($atts) {
    $atts = shortcode_atts(array(
        'title' => 'Hotel Details',
    ), $atts);
    
    // Get hotel code
    $hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
    
    ob_start();
    ?>
    <div class="tbo-enhanced-details-container">
        <div class="hotel-details-container">
            <div class="tbo-hotel-details-container" id="hotel-details">
                <div class="loading-hotel-details">
                    <div class="spinner"></div>
                    <p>Loading hotel details...</p>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Get hotel code
        const hotelCode = '<?php echo esc_js($hotel_code); ?>';
        
        // Only load if we have a hotel code
        if (hotelCode) {
            // Let the enhanced TBO functionality handle loading the details
            TBOEnhanced.loadHotelDetails(hotelCode);
        } else {
            // Show no hotel selected message
            $('#hotel-details').html('<div class="no-hotel-found">Please select a hotel to view details.</div>');
        }
        
        // Initialize gallery functionality
        $(document).on('tbo_hotel_details_updated', function() {
            // Handle gallery thumbnails
            $('.gallery-thumbnails .thumbnail').on('click', function() {
                const imageSrc = $(this).data('image');
                $('.gallery-main img').attr('src', imageSrc);
            });
            
            // Initialize Google Map if available
            if (typeof google !== 'undefined' && typeof google.maps !== 'undefined') {
                const $mapCanvas = $('#hotel-map-canvas');
                
                if ($mapCanvas.length) {
                    const lat = parseFloat($mapCanvas.data('lat'));
                    const lng = parseFloat($mapCanvas.data('lng'));
                    const title = $mapCanvas.data('title');
                    
                    if (lat && lng) {
                        const mapOptions = {
                            center: new google.maps.LatLng(lat, lng),
                            zoom: 15,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        };
                        
                        const map = new google.maps.Map($mapCanvas[0], mapOptions);
                        
                        const marker = new google.maps.Marker({
                            position: new google.maps.LatLng(lat, lng),
                            map: map,
                            title: title
                        });
                    } else {
                        $mapCanvas.html('<p>Map location not available</p>');
                    }
                }
            }
        });
    });
    </script>
    
    <style>
    .tbo-enhanced-details-container {
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .loading-hotel-details {
        text-align: center;
        padding: 50px 0;
    }
    
    .loading-hotel-details .spinner {
        border: 5px solid #f3f3f3;
        border-top: 5px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
        margin: 0 auto 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .loading-hotel-details p {
        color: #777;
        font-size: 16px;
    }
    
    .no-hotel-found {
        background-color: #f8f9fa;
        padding: 30px;
        text-align: center;
        border-radius: 8px;
        color: #555;
        font-size: 16px;
        margin: 30px 0;
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('tbo_enhanced_details', 'tbo_enhanced_hotel_details_shortcode');