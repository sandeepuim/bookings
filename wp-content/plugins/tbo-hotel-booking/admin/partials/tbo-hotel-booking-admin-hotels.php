<?php
/**
 * Provide a admin area view for managing hotels
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    TBO_Hotel_Booking
 * @subpackage TBO_Hotel_Booking/admin/partials
 */
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <div class="tbo-hotel-booking-admin-wrapper">
        <div class="tbo-hotel-booking-admin-content">
            <h2><?php _e('Manage Hotels', 'tbo-hotel-booking'); ?></h2>
            
            <?php
            // Placeholder for hotels management
            // In a full implementation, you would add code here to display and manage hotels
            ?>
            
            <div class="tbo-hotels-list">
                <p><?php _e('This page will allow you to manage hotel information cached from the TBO API.', 'tbo-hotel-booking'); ?></p>
                <!-- This would typically be replaced with a WP_List_Table implementation -->
            </div>
        </div>
    </div>
</div>
