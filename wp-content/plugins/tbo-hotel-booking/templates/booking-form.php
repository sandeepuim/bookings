<?php
/**
 * Booking form template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$hotel_id = $atts['hotel_id'];
$room_id = $atts['room_id'];
$class = $atts['class'];

// Get search parameters from URL
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : date('Y-m-d', strtotime('+1 day'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;

// Initialize hotel and room
$hotel = null;
$room = null;

try {
    // Get TBO API instance
    $tbo_api = new TBO_Hotel_Booking_API();
    
    // Get hotel details
    $hotel = $tbo_api->get_hotel_details($hotel_id);
    
    // Check room availability
    $availability = $tbo_api->check_availability($hotel_id, $check_in, $check_out, $adults, $children);
    
    // Find the selected room
    if (isset($availability['rooms'])) {
        foreach ($availability['rooms'] as $available_room) {
            if ($available_room['id'] === $room_id) {
                $room = $available_room;
                break;
            }
        }
    }
} catch (Exception $e) {
    echo '<div class="tbo-error-message">' . esc_html($e->getMessage()) . '</div>';
    return;
}

// Check if hotel and room data is available
if (empty($hotel) || empty($room)) {
    echo '<div class="tbo-error-message">' . __('Hotel or room details not found.', 'tbo-hotel-booking') . '</div>';
    return;
}

// Get hotel and room data
$hotel_name = $hotel['name'];
$room_name = $room['name'];
$room_price = $room['price'];
$room_cancellation_policy = $room['cancellation_policy'];

// Calculate stay duration and total price
$check_in_date = new DateTime($check_in);
$check_out_date = new DateTime($check_out);
$stay_duration = $check_in_date->diff($check_out_date)->days;
$total_price = floatval(str_replace(array('$', ','), '', $room_price)) * $stay_duration;

// Check if user is logged in
$user_logged_in = is_user_logged_in();
$user_data = array();

if ($user_logged_in) {
    $current_user = wp_get_current_user();
    $user_data = array(
        'first_name' => $current_user->first_name,
        'last_name' => $current_user->last_name,
        'email' => $current_user->user_email,
    );
}
?>

<div class="tbo-booking-form <?php echo esc_attr($class); ?>">
    <div class="tbo-booking-header">
        <h1><?php _e('Complete Your Booking', 'tbo-hotel-booking'); ?></h1>
    </div>
    
    <div class="tbo-booking-summary">
        <h2><?php _e('Booking Summary', 'tbo-hotel-booking'); ?></h2>
        
        <div class="tbo-summary-content">
            <div class="tbo-summary-hotel">
                <h3><?php echo esc_html($hotel_name); ?></h3>
                <p><?php echo esc_html($room_name); ?></p>
            </div>
            
            <div class="tbo-summary-details">
                <div class="tbo-summary-item">
                    <span class="tbo-summary-label"><?php _e('Check-in:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value"><?php echo date_i18n(get_option('date_format'), strtotime($check_in)); ?></span>
                </div>
                
                <div class="tbo-summary-item">
                    <span class="tbo-summary-label"><?php _e('Check-out:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value"><?php echo date_i18n(get_option('date_format'), strtotime($check_out)); ?></span>
                </div>
                
                <div class="tbo-summary-item">
                    <span class="tbo-summary-label"><?php _e('Duration:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value"><?php echo sprintf(_n('%d night', '%d nights', $stay_duration, 'tbo-hotel-booking'), $stay_duration); ?></span>
                </div>
                
                <div class="tbo-summary-item">
                    <span class="tbo-summary-label"><?php _e('Guests:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value">
                        <?php 
                        echo sprintf(_n('%d adult', '%d adults', $adults, 'tbo-hotel-booking'), $adults);
                        if ($children > 0) {
                            echo ', ' . sprintf(_n('%d child', '%d children', $children, 'tbo-hotel-booking'), $children);
                        }
                        ?>
                    </span>
                </div>
                
                <div class="tbo-summary-item tbo-summary-price">
                    <span class="tbo-summary-label"><?php _e('Price per night:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value"><?php echo esc_html($room_price); ?></span>
                </div>
                
                <div class="tbo-summary-item tbo-summary-total">
                    <span class="tbo-summary-label"><?php _e('Total price:', 'tbo-hotel-booking'); ?></span>
                    <span class="tbo-summary-value">$<?php echo number_format($total_price, 2); ?></span>
                </div>
            </div>
            
            <div class="tbo-summary-cancellation">
                <h4><?php _e('Cancellation Policy', 'tbo-hotel-booking'); ?></h4>
                <p><?php echo esc_html($room_cancellation_policy); ?></p>
            </div>
        </div>
    </div>
    
    <div class="tbo-booking-form-container">
        <h2><?php _e('Guest Information', 'tbo-hotel-booking'); ?></h2>
        
        <?php if (!$user_logged_in) : ?>
            <div class="tbo-login-notice">
                <p><?php _e('Already have an account?', 'tbo-hotel-booking'); ?> <a href="<?php echo esc_url(wp_login_url(site_url('/booking/?hotel_id=' . $hotel_id . '&room_id=' . $room_id . '&check_in=' . $check_in . '&check_out=' . $check_out . '&adults=' . $adults . '&children=' . $children))); ?>"><?php _e('Log in', 'tbo-hotel-booking'); ?></a></p>
            </div>
        <?php endif; ?>
        
        <form id="tbo-booking-form" class="tbo-form" method="post">
            <input type="hidden" name="hotel_id" value="<?php echo esc_attr($hotel_id); ?>">
            <input type="hidden" name="room_id" value="<?php echo esc_attr($room_id); ?>">
            <input type="hidden" name="check_in" value="<?php echo esc_attr($check_in); ?>">
            <input type="hidden" name="check_out" value="<?php echo esc_attr($check_out); ?>">
            <input type="hidden" name="adults" value="<?php echo esc_attr($adults); ?>">
            <input type="hidden" name="children" value="<?php echo esc_attr($children); ?>">
            <input type="hidden" name="total_price" value="<?php echo esc_attr($total_price); ?>">
            <input type="hidden" name="action" value="tbo_process_booking">
            <?php wp_nonce_field('tbo_hotel_booking_nonce', 'nonce'); ?>
            
            <div class="tbo-form-section">
                <h3><?php _e('Contact Information', 'tbo-hotel-booking'); ?></h3>
                
                <div class="tbo-form-row">
                    <div class="tbo-form-field">
                        <label for="tbo-first-name"><?php _e('First Name', 'tbo-hotel-booking'); ?></label>
                        <input type="text" id="tbo-first-name" name="guest_info[first_name]" value="<?php echo isset($user_data['first_name']) ? esc_attr($user_data['first_name']) : ''; ?>" required>
                    </div>
                    
                    <div class="tbo-form-field">
                        <label for="tbo-last-name"><?php _e('Last Name', 'tbo-hotel-booking'); ?></label>
                        <input type="text" id="tbo-last-name" name="guest_info[last_name]" value="<?php echo isset($user_data['last_name']) ? esc_attr($user_data['last_name']) : ''; ?>" required>
                    </div>
                </div>
                
                <div class="tbo-form-row">
                    <div class="tbo-form-field">
                        <label for="tbo-email"><?php _e('Email', 'tbo-hotel-booking'); ?></label>
                        <input type="email" id="tbo-email" name="guest_info[email]" value="<?php echo isset($user_data['email']) ? esc_attr($user_data['email']) : ''; ?>" required>
                    </div>
                    
                    <div class="tbo-form-field">
                        <label for="tbo-phone"><?php _e('Phone', 'tbo-hotel-booking'); ?></label>
                        <input type="tel" id="tbo-phone" name="guest_info[phone]" required>
                    </div>
                </div>
            </div>
            
            <div class="tbo-form-section">
                <h3><?php _e('Special Requests', 'tbo-hotel-booking'); ?></h3>
                
                <div class="tbo-form-row">
                    <div class="tbo-form-field">
                        <textarea id="tbo-special-requests" name="guest_info[special_requests]" rows="4" placeholder="<?php _e('Enter any special requests or requirements...', 'tbo-hotel-booking'); ?>"></textarea>
                    </div>
                </div>
            </div>
            
            <div class="tbo-form-section">
                <h3><?php _e('Payment Information', 'tbo-hotel-booking'); ?></h3>
                
                <div class="tbo-form-row">
                    <div class="tbo-form-field">
                        <label for="tbo-payment-method"><?php _e('Payment Method', 'tbo-hotel-booking'); ?></label>
                        <select id="tbo-payment-method" name="payment_method" required>
                            <option value="credit_card"><?php _e('Credit Card', 'tbo-hotel-booking'); ?></option>
                            <option value="paypal"><?php _e('PayPal', 'tbo-hotel-booking'); ?></option>
                            <option value="bank_transfer"><?php _e('Bank Transfer', 'tbo-hotel-booking'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div id="tbo-credit-card-fields" class="tbo-payment-fields">
                    <div class="tbo-form-row">
                        <div class="tbo-form-field">
                            <label for="tbo-card-number"><?php _e('Card Number', 'tbo-hotel-booking'); ?></label>
                            <input type="text" id="tbo-card-number" name="payment_details[card_number]" placeholder="xxxx xxxx xxxx xxxx">
                        </div>
                    </div>
                    
                    <div class="tbo-form-row">
                        <div class="tbo-form-field">
                            <label for="tbo-card-name"><?php _e('Cardholder Name', 'tbo-hotel-booking'); ?></label>
                            <input type="text" id="tbo-card-name" name="payment_details[card_name]">
                        </div>
                    </div>
                    
                    <div class="tbo-form-row">
                        <div class="tbo-form-field">
                            <label for="tbo-card-expiry"><?php _e('Expiry Date', 'tbo-hotel-booking'); ?></label>
                            <input type="text" id="tbo-card-expiry" name="payment_details[card_expiry]" placeholder="MM/YY">
                        </div>
                        
                        <div class="tbo-form-field">
                            <label for="tbo-card-cvv"><?php _e('CVV', 'tbo-hotel-booking'); ?></label>
                            <input type="text" id="tbo-card-cvv" name="payment_details[card_cvv]" placeholder="xxx">
                        </div>
                    </div>
                </div>
                
                <div class="tbo-form-row">
                    <div class="tbo-form-field tbo-checkbox-field">
                        <input type="checkbox" id="tbo-terms" name="terms" required>
                        <label for="tbo-terms"><?php _e('I agree to the terms and conditions', 'tbo-hotel-booking'); ?></label>
                    </div>
                </div>
            </div>
            
            <div class="tbo-form-actions">
                <button type="submit" class="tbo-submit-booking-button"><?php _e('Complete Booking', 'tbo-hotel-booking'); ?></button>
            </div>
        </form>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Show/hide payment fields based on selected payment method
        $('#tbo-payment-method').on('change', function() {
            var paymentMethod = $(this).val();
            
            if (paymentMethod === 'credit_card') {
                $('#tbo-credit-card-fields').show();
            } else {
                $('#tbo-credit-card-fields').hide();
            }
        });
        
        // Form submission
        $('#tbo-booking-form').on('submit', function(e) {
            e.preventDefault();
            
            var formData = $(this).serialize();
            
            // Show loading state
            $('.tbo-submit-booking-button').prop('disabled', true).text('<?php _e('Processing...', 'tbo-hotel-booking'); ?>');
            
            // Submit form via AJAX
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        // Redirect to confirmation page
                        window.location.href = response.data.redirect_url;
                    } else {
                        // Show error message
                        alert(response.data);
                        $('.tbo-submit-booking-button').prop('disabled', false).text('<?php _e('Complete Booking', 'tbo-hotel-booking'); ?>');
                    }
                },
                error: function() {
                    alert('<?php _e('An error occurred. Please try again.', 'tbo-hotel-booking'); ?>');
                    $('.tbo-submit-booking-button').prop('disabled', false).text('<?php _e('Complete Booking', 'tbo-hotel-booking'); ?>');
                }
            });
        });
    });
</script>
