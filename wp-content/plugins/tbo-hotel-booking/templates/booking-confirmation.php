<?php
/**
 * Booking confirmation template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$booking_id = intval($atts['booking_id']);
$class = $atts['class'];

// Get booking data from database
global $wpdb;
$booking = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}tbo_bookings WHERE id = %d",
    $booking_id
), ARRAY_A);

// Check if booking exists
if (!$booking) {
    echo '<div class="tbo-error-message">' . __('Booking not found.', 'tbo-hotel-booking') . '</div>';
    return;
}

// Get booking items (rooms)
$booking_items = $wpdb->get_results($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}tbo_booking_items WHERE booking_id = %d",
    $booking_id
), ARRAY_A);

// Get payment information
$payment = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}tbo_payments WHERE booking_id = %d",
    $booking_id
), ARRAY_A);

// Calculate stay duration
$check_in_date = new DateTime($booking['check_in']);
$check_out_date = new DateTime($booking['check_out']);
$stay_duration = $check_in_date->diff($check_out_date)->days;
?>

<div class="tbo-booking-confirmation <?php echo esc_attr($class); ?>">
    <div class="tbo-confirmation-header">
        <h1><?php _e('Booking Confirmation', 'tbo-hotel-booking'); ?></h1>
        <div class="tbo-confirmation-message">
            <p><?php _e('Your booking has been confirmed!', 'tbo-hotel-booking'); ?></p>
            <p><?php printf(__('Booking Number: %s', 'tbo-hotel-booking'), '<strong>' . esc_html($booking['booking_number']) . '</strong>'); ?></p>
        </div>
    </div>
    
    <div class="tbo-confirmation-details">
        <div class="tbo-confirmation-section">
            <h2><?php _e('Hotel Information', 'tbo-hotel-booking'); ?></h2>
            <div class="tbo-confirmation-content">
                <p class="tbo-hotel-name"><?php echo esc_html($booking['hotel_name']); ?></p>
            </div>
        </div>
        
        <div class="tbo-confirmation-section">
            <h2><?php _e('Booking Details', 'tbo-hotel-booking'); ?></h2>
            <div class="tbo-confirmation-content">
                <div class="tbo-booking-detail">
                    <span class="tbo-detail-label"><?php _e('Check-in:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-detail-value"><?php echo date_i18n(get_option('date_format'), strtotime($booking['check_in'])); ?></span>
                </div>
                
                <div class="tbo-booking-detail">
                    <span class="tbo-detail-label"><?php _e('Check-out:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-detail-value"><?php echo date_i18n(get_option('date_format'), strtotime($booking['check_out'])); ?></span>
                </div>
                
                <div class="tbo-booking-detail">
                    <span class="tbo-detail-label"><?php _e('Duration:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-detail-value"><?php echo sprintf(_n('%d night', '%d nights', $stay_duration, 'tbo-hotel-booking'), $stay_duration); ?></span>
                </div>
                
                <div class="tbo-booking-detail">
                    <span class="tbo-detail-label"><?php _e('Guests:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-detail-value">
                        <?php 
                        echo sprintf(_n('%d adult', '%d adults', $booking['adults'], 'tbo-hotel-booking'), $booking['adults']);
                        if ($booking['children'] > 0) {
                            echo ', ' . sprintf(_n('%d child', '%d children', $booking['children'], 'tbo-hotel-booking'), $booking['children']);
                        }
                        ?>
                    </span>
                </div>
                
                <div class="tbo-booking-detail">
                    <span class="tbo-detail-label"><?php _e('Status:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-detail-value tbo-status-<?php echo esc_attr($booking['status']); ?>"><?php echo esc_html(ucfirst($booking['status'])); ?></span>
                </div>
            </div>
        </div>
        
        <div class="tbo-confirmation-section">
            <h2><?php _e('Room Information', 'tbo-hotel-booking'); ?></h2>
            <div class="tbo-confirmation-content">
                <?php if (!empty($booking_items)) : ?>
                    <div class="tbo-room-list">
                        <?php foreach ($booking_items as $item) : ?>
                            <div class="tbo-room-item">
                                <div class="tbo-room-name"><?php echo esc_html($item['room_name']); ?></div>
                                <div class="tbo-room-type"><?php echo esc_html($item['room_type']); ?></div>
                                <div class="tbo-room-quantity"><?php echo sprintf(_n('%d room', '%d rooms', $item['quantity'], 'tbo-hotel-booking'), $item['quantity']); ?></div>
                                <div class="tbo-room-price">
                                    <span class="tbo-price-label"><?php _e('Price:', 'tbo-hotel-booking'); ?></span>
                                    <span class="tbo-price-value">$<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p><?php _e('No room information available.', 'tbo-hotel-booking'); ?></p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="tbo-confirmation-section">
            <h2><?php _e('Payment Information', 'tbo-hotel-booking'); ?></h2>
            <div class="tbo-confirmation-content">
                <?php if (!empty($payment)) : ?>
                    <div class="tbo-payment-details">
                        <div class="tbo-payment-detail">
                            <span class="tbo-detail-label"><?php _e('Payment Method:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-detail-value"><?php echo esc_html(ucwords(str_replace('_', ' ', $payment['payment_method']))); ?></span>
                        </div>
                        
                        <div class="tbo-payment-detail">
                            <span class="tbo-detail-label"><?php _e('Transaction ID:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-detail-value"><?php echo esc_html($payment['transaction_id']); ?></span>
                        </div>
                        
                        <div class="tbo-payment-detail">
                            <span class="tbo-detail-label"><?php _e('Date:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-detail-value"><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($payment['payment_date'])); ?></span>
                        </div>
                        
                        <div class="tbo-payment-detail">
                            <span class="tbo-detail-label"><?php _e('Status:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-detail-value tbo-status-<?php echo esc_attr($payment['status']); ?>"><?php echo esc_html(ucfirst($payment['status'])); ?></span>
                        </div>
                        
                        <div class="tbo-payment-detail tbo-payment-total">
                            <span class="tbo-detail-label"><?php _e('Total Amount:', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-detail-value">$<?php echo number_format($payment['amount'], 2); ?></span>
                        </div>
                    </div>
                <?php else : ?>
                    <p><?php _e('No payment information available.', 'tbo-hotel-booking'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="tbo-confirmation-actions">
        <a href="<?php echo esc_url(site_url('/my-account/bookings/')); ?>" class="tbo-view-bookings-button"><?php _e('View All Bookings', 'tbo-hotel-booking'); ?></a>
        
        <?php if ($booking['status'] === 'confirmed') : ?>
            <a href="#" class="tbo-cancel-booking-button" data-booking-id="<?php echo esc_attr($booking_id); ?>"><?php _e('Cancel Booking', 'tbo-hotel-booking'); ?></a>
        <?php endif; ?>
    </div>
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
                            $('.tbo-cancel-booking-button').text('<?php _e('Cancel Booking', 'tbo-hotel-booking'); ?>').prop('disabled', false);
                        }
                    },
                    error: function() {
                        alert('<?php _e('An error occurred. Please try again.', 'tbo-hotel-booking'); ?>');
                        $('.tbo-cancel-booking-button').text('<?php _e('Cancel Booking', 'tbo-hotel-booking'); ?>').prop('disabled', false);
                    }
                });
            }
        });
    });
</script>
