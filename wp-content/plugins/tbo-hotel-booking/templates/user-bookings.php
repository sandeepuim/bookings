<?php
/**
 * User bookings template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$title = $atts['title'];
$class = $atts['class'];

// Get current user ID
$user_id = get_current_user_id();

// Get user bookings from database
global $wpdb;
$bookings = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}tbo_bookings WHERE user_id = %d ORDER BY created_at DESC",
    $user_id
), ARRAY_A);
?>

<div class="tbo-user-bookings <?php echo esc_attr($class); ?>">
    <div class="tbo-user-bookings-header">
        <?php if (!empty($title)) : ?>
            <h2 class="tbo-user-bookings-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
    </div>
    
    <?php if (empty($bookings)) : ?>
        <div class="tbo-no-bookings">
            <p><?php _e('You have no bookings yet.', 'tbo-hotel-booking'); ?></p>
            <a href="<?php echo esc_url(site_url('/')); ?>" class="tbo-search-hotels-button"><?php _e('Search Hotels', 'tbo-hotel-booking'); ?></a>
        </div>
    <?php else : ?>
        <div class="tbo-bookings-list">
            <?php foreach ($bookings as $booking) : 
                // Calculate stay duration
                $check_in_date = new DateTime($booking['check_in']);
                $check_out_date = new DateTime($booking['check_out']);
                $stay_duration = $check_in_date->diff($check_out_date)->days;
                
                // Get status class
                $status_class = 'tbo-status-' . $booking['status'];
            ?>
                <div class="tbo-booking-item">
                    <div class="tbo-booking-header">
                        <div class="tbo-booking-number">
                            <span class="tbo-booking-label"><?php _e('Booking #:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-booking-value"><?php echo esc_html($booking['booking_number']); ?></span>
                        </div>
                        
                        <div class="tbo-booking-date">
                            <span class="tbo-booking-label"><?php _e('Booked on:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-booking-value"><?php echo date_i18n(get_option('date_format'), strtotime($booking['created_at'])); ?></span>
                        </div>
                        
                        <div class="tbo-booking-status">
                            <span class="tbo-status <?php echo esc_attr($status_class); ?>"><?php echo esc_html(ucfirst($booking['status'])); ?></span>
                        </div>
                    </div>
                    
                    <div class="tbo-booking-content">
                        <div class="tbo-booking-hotel">
                            <h3 class="tbo-hotel-name"><?php echo esc_html($booking['hotel_name']); ?></h3>
                        </div>
                        
                        <div class="tbo-booking-details">
                            <div class="tbo-booking-dates">
                                <span class="tbo-date-label"><?php _e('Stay:', 'tbo-hotel-booking'); ?></span>
                                <span class="tbo-date-value">
                                    <?php echo date_i18n(get_option('date_format'), strtotime($booking['check_in'])); ?> - 
                                    <?php echo date_i18n(get_option('date_format'), strtotime($booking['check_out'])); ?>
                                    (<?php echo sprintf(_n('%d night', '%d nights', $stay_duration, 'tbo-hotel-booking'), $stay_duration); ?>)
                                </span>
                            </div>
                            
                            <div class="tbo-booking-guests">
                                <span class="tbo-guests-label"><?php _e('Guests:', 'tbo-hotel-booking'); ?></span>
                                <span class="tbo-guests-value">
                                    <?php 
                                    echo sprintf(_n('%d adult', '%d adults', $booking['adults'], 'tbo-hotel-booking'), $booking['adults']);
                                    if ($booking['children'] > 0) {
                                        echo ', ' . sprintf(_n('%d child', '%d children', $booking['children'], 'tbo-hotel-booking'), $booking['children']);
                                    }
                                    ?>
                                </span>
                            </div>
                            
                            <div class="tbo-booking-total">
                                <span class="tbo-total-label"><?php _e('Total:', 'tbo-hotel-booking'); ?></span>
                                <span class="tbo-total-value">$<?php echo number_format($booking['total_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tbo-booking-actions">
                        <a href="<?php echo esc_url(site_url('/booking-confirmation/?booking_id=' . $booking['id'])); ?>" class="tbo-view-booking-button"><?php _e('View Details', 'tbo-hotel-booking'); ?></a>
                        
                        <?php if ($booking['status'] === 'confirmed') : ?>
                            <a href="#" class="tbo-cancel-booking-button" data-booking-id="<?php echo esc_attr($booking['id']); ?>"><?php _e('Cancel Booking', 'tbo-hotel-booking'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
    jQuery(document).ready(function($) {
        // Cancel booking
        $('.tbo-cancel-booking-button').on('click', function(e) {
            e.preventDefault();
            
            if (confirm('<?php _e('Are you sure you want to cancel this booking? This action cannot be undone.', 'tbo-hotel-booking'); ?>')) {
                var bookingId = $(this).data('booking-id');
                
                // Show loading state
                $(this).text('<?php _e('Processing...', 'tbo-hotel-booking'); ?>').prop('disabled', true);
                
                // Send AJAX request
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'tbo_cancel_booking',
                        booking_id: bookingId,
                        nonce: '<?php echo wp_create_nonce('tbo_hotel_booking_nonce'); ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            alert('<?php _e('Your booking has been cancelled successfully.', 'tbo-hotel-booking'); ?>');
                            window.location.reload();
                        } else {
                            alert(response.data);
                            $('.tbo-cancel-booking-button[data-booking-id="' + bookingId + '"]').text('<?php _e('Cancel Booking', 'tbo-hotel-booking'); ?>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'tbo-hotel-booking'); ?>');
                        $('.tbo-cancel-booking-button[data-booking-id="' + bookingId + '"]').text('<?php _e('Cancel Booking', 'tbo-hotel-booking'); ?>').prop('disabled', false);
                    }
                });
            }
        });
    });
</script>
