<?php
/**
 * TBO Hotels Enhanced Installation
 * 
 * This file helps set up the enhanced TBO Hotels functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add an installation notice to the admin dashboard
 */
function tbo_enhanced_admin_notice() {
    // Check if the user is an admin
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Check if we've already shown the notice
    if (get_option('tbo_enhanced_installed', false)) {
        return;
    }
    
    ?>
    <div class="notice notice-info is-dismissible" id="tbo-enhanced-install-notice">
        <h3>TBO Hotels Enhanced Functionality</h3>
        <p>Thank you for installing the enhanced TBO Hotels functionality. This update includes:</p>
        <ul style="list-style-type: disc; padding-left: 20px;">
            <li>Improved error handling with fallback to stale cache</li>
            <li>Better parameter validation</li>
            <li>Dynamic cache expiration based on search date proximity</li>
            <li>Enhanced frontend with loading indicators and error messages</li>
            <li>AJAX response sanitization to prevent JavaScript errors</li>
            <li>New shortcodes for hotel search, results, and details</li>
        </ul>
        <p>Would you like to:</p>
        <p>
            <a href="<?php echo admin_url('admin.php?page=tbo-api-settings'); ?>" class="button button-primary">Configure API Settings</a>
            <a href="<?php echo admin_url('admin.php?page=tbo-enhanced-install&action=create-pages'); ?>" class="button">Create Demo Pages</a>
            <a href="#" class="button" id="tbo-dismiss-notice">Dismiss</a>
        </p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Handle dismiss button
        $('#tbo-dismiss-notice').on('click', function(e) {
            e.preventDefault();
            
            // Hide the notice
            $('#tbo-enhanced-install-notice').hide();
            
            // Mark as installed via AJAX
            $.post(ajaxurl, {
                action: 'tbo_enhanced_dismiss_notice',
                nonce: '<?php echo wp_create_nonce('tbo_enhanced_dismiss_notice'); ?>'
            });
        });
    });
    </script>
    <?php
}
add_action('admin_notices', 'tbo_enhanced_admin_notice');

/**
 * Handle dismissing the installation notice
 */
function tbo_enhanced_dismiss_notice() {
    // Verify nonce
    check_ajax_referer('tbo_enhanced_dismiss_notice', 'nonce');
    
    // Check if the user is an admin
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
        exit;
    }
    
    // Mark as installed
    update_option('tbo_enhanced_installed', true);
    
    wp_send_json_success();
    exit;
}
add_action('wp_ajax_tbo_enhanced_dismiss_notice', 'tbo_enhanced_dismiss_notice');

/**
 * Add the installation page
 */
function tbo_enhanced_add_install_page() {
    add_submenu_page(
        null, // No parent page
        'TBO Enhanced Install',
        'TBO Enhanced Install',
        'manage_options',
        'tbo-enhanced-install',
        'tbo_enhanced_install_page'
    );
}
add_action('admin_menu', 'tbo_enhanced_add_install_page');

/**
 * Render the installation page
 */
function tbo_enhanced_install_page() {
    // Check if the user is an admin
    if (!current_user_can('manage_options')) {
        wp_die('Permission denied');
    }
    
    // Handle creating demo pages
    if (isset($_GET['action']) && $_GET['action'] === 'create-pages') {
        tbo_enhanced_create_demo_pages();
    }
    
    ?>
    <div class="wrap">
        <h1>TBO Enhanced Installation</h1>
        
        <div class="card">
            <h2>Installation Complete</h2>
            <p>The enhanced TBO Hotels functionality has been installed successfully.</p>
            
            <?php if (get_option('tbo_enhanced_demo_pages_created', false)) : ?>
                <div class="notice notice-success">
                    <p>Demo pages have been created successfully.</p>
                </div>
                
                <h3>Demo Pages</h3>
                <ul>
                    <?php
                    $search_page_id = get_option('tbo_enhanced_search_page_id');
                    $results_page_id = get_option('tbo_enhanced_results_page_id');
                    $details_page_id = get_option('tbo_enhanced_details_page_id');
                    ?>
                    
                    <?php if ($search_page_id) : ?>
                        <li>
                            <strong>Search Page:</strong>
                            <a href="<?php echo get_permalink($search_page_id); ?>" target="_blank"><?php echo get_the_title($search_page_id); ?></a>
                            (<a href="<?php echo get_edit_post_link($search_page_id); ?>">Edit</a>)
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($results_page_id) : ?>
                        <li>
                            <strong>Results Page:</strong>
                            <a href="<?php echo get_permalink($results_page_id); ?>" target="_blank"><?php echo get_the_title($results_page_id); ?></a>
                            (<a href="<?php echo get_edit_post_link($results_page_id); ?>">Edit</a>)
                        </li>
                    <?php endif; ?>
                    
                    <?php if ($details_page_id) : ?>
                        <li>
                            <strong>Details Page:</strong>
                            <a href="<?php echo get_permalink($details_page_id); ?>" target="_blank"><?php echo get_the_title($details_page_id); ?></a>
                            (<a href="<?php echo get_edit_post_link($details_page_id); ?>">Edit</a>)
                        </li>
                    <?php endif; ?>
                </ul>
            <?php else : ?>
                <p>
                    <a href="<?php echo admin_url('admin.php?page=tbo-enhanced-install&action=create-pages'); ?>" class="button button-primary">Create Demo Pages</a>
                </p>
            <?php endif; ?>
            
            <h3>Next Steps</h3>
            <ul>
                <li><a href="<?php echo admin_url('admin.php?page=tbo-api-settings'); ?>">Configure API Settings</a> - Set your TBO API credentials</li>
                <li><a href="<?php echo admin_url('customize.php'); ?>">Customize Theme</a> - Adjust the appearance of the hotel search and results</li>
                <li><a href="<?php echo admin_url('edit.php?post_type=page'); ?>">Manage Pages</a> - Edit or create new pages with TBO shortcodes</li>
            </ul>
            
            <h3>Available Shortcodes</h3>
            <table class="widefat">
                <thead>
                    <tr>
                        <th>Shortcode</th>
                        <th>Description</th>
                        <th>Parameters</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>[tbo_enhanced_search]</code></td>
                        <td>Displays a hotel search form</td>
                        <td>
                            <code>title</code> - Form title<br>
                            <code>default_city_id</code> - Default city ID<br>
                            <code>default_city_name</code> - Default city name<br>
                            <code>results_page</code> - Results page ID
                        </td>
                    </tr>
                    <tr>
                        <td><code>[tbo_enhanced_results]</code></td>
                        <td>Displays hotel search results</td>
                        <td>
                            <code>title</code> - Results title<br>
                            <code>default_city_id</code> - Default city ID
                        </td>
                    </tr>
                    <tr>
                        <td><code>[tbo_enhanced_details]</code></td>
                        <td>Displays hotel details</td>
                        <td>
                            <code>title</code> - Details title
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}

/**
 * Create demo pages for the enhanced functionality
 */
function tbo_enhanced_create_demo_pages() {
    // Check if pages already exist
    if (get_option('tbo_enhanced_demo_pages_created', false)) {
        return;
    }
    
    // Create search page
    $search_page_id = wp_insert_post(array(
        'post_title'     => 'Find Hotels',
        'post_content'   => '[tbo_enhanced_search title="Find Your Perfect Hotel" results_page="tbo_results_page_id"]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ));
    
    // Create results page
    $results_page_id = wp_insert_post(array(
        'post_title'     => 'Hotel Results',
        'post_content'   => '[tbo_enhanced_results title="Hotels for Your Stay"]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ));
    
    // Create details page
    $details_page_id = wp_insert_post(array(
        'post_title'     => 'Hotel Details',
        'post_content'   => '[tbo_enhanced_details title="Hotel Information"]',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed',
        'ping_status'    => 'closed',
    ));
    
    // Update search page content with correct results page ID
    wp_update_post(array(
        'ID'           => $search_page_id,
        'post_content' => '[tbo_enhanced_search title="Find Your Perfect Hotel" results_page="' . $results_page_id . '"]',
    ));
    
    // Save page IDs
    update_option('tbo_enhanced_search_page_id', $search_page_id);
    update_option('tbo_enhanced_results_page_id', $results_page_id);
    update_option('tbo_enhanced_details_page_id', $details_page_id);
    
    // Mark as created
    update_option('tbo_enhanced_demo_pages_created', true);
}