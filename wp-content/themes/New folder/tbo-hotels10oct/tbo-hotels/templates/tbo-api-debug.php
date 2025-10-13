<?php
/**
 * Template Name: TBO API Debug
 * 
 * This page shows the last API request sent to TBO for debugging purposes
 */

// Only administrators can access this page
if (!current_user_can('manage_options')) {
    wp_die('You do not have permission to access this page.');
}

get_header();
?>

<div class="container" style="max-width: 900px; margin: 40px auto; padding: 20px;">
    <h1>TBO API Debug</h1>
    
    <div class="debug-section">
        <h2>Last Booking Request</h2>
        <?php
        $last_booking_request = get_option('tbo_last_booking_request', '');
        
        if (!empty($last_booking_request)) {
            echo '<pre style="background: #f5f5f5; padding: 20px; border-radius: 8px; overflow: auto; max-height: 600px;">';
            echo esc_html($last_booking_request);
            echo '</pre>';
        } else {
            echo '<p>No booking request data available.</p>';
        }
        ?>
    </div>
    
    <?php if (isset($_GET['clear']) && $_GET['clear'] == '1'): ?>
        <?php 
        delete_option('tbo_last_booking_request');
        echo '<p style="color: green;">Debug data has been cleared.</p>';
        ?>
    <?php else: ?>
        <a href="?clear=1" class="button" style="background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 20px;">Clear Debug Data</a>
    <?php endif; ?>
</div>

<?php get_footer(); ?>