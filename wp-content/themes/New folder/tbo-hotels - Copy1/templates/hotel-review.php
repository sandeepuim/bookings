<?php
/**
 * Template Name: Hotel Review
 * 
 * Template for displaying hotel review/booking summary page
 *
 * @package TBO_Hotels
 */
get_header();

// Get parameters from URL
$hotel_id = isset($_GET['hotelId']) ? sanitize_text_field($_GET['hotelId']) : '';
$checkin = isset($_GET['checkinDate']) ? sanitize_text_field($_GET['checkinDate']) : '';
$checkout = isset($_GET['checkoutDate']) ? sanitize_text_field($_GET['checkoutDate']) : '';
$city_code = isset($_GET['city.code']) ? sanitize_text_field($_GET['city.code']) : '';
$country_code = isset($_GET['country.code']) ? sanitize_text_field($_GET['country.code']) : '';
$room_type_id = isset($_GET['roomTypeId']) ? sanitize_text_field($_GET['roomTypeId']) : '';
$rate_plan_id = isset($_GET['ratePlanId']) ? sanitize_text_field($_GET['ratePlanId']) : '';
$adults = isset($_GET['roomRequests'][0]['noOfAdults']) ? intval($_GET['roomRequests'][0]['noOfAdults']) : 2;
$children = isset($_GET['roomRequests'][0]['noOfChildren']) ? intval($_GET['roomRequests'][0]['noOfChildren']) : 0;

?>
<div class="hotel-review-page">
    <div class="container">
        <h1>Hotel Review / Booking Summary</h1>
        <div class="review-header" style="display: flex; gap: 32px; align-items: flex-start; margin-bottom: 32px;">
            <div style="flex: 2;">
                <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 6px; color: #222;">Hotel Name Here</h2>
                <div style="color: #007bff; margin-bottom: 10px; font-size: 1.1em;">
                    <i class="fas fa-map-marker-alt"></i> Hotel Address Here
                </div>
                <div style="margin-bottom: 18px;">
                    <img src="https://source.unsplash.com/featured/400x300/?hotel,room" style="width: 320px; border-radius: 12px;">
                </div>
                <div style="display: flex; gap: 32px;">
                    <div>
                        <div style="font-weight: 600;">Check-In</div>
                        <div><?php echo esc_html($checkin); ?></div>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Check-Out</div>
                        <div><?php echo esc_html($checkout); ?></div>
                    </div>
                    <div>
                        <div style="font-weight: 600;">Guests</div>
                        <div><?php echo esc_html($adults); ?> Adults, <?php echo esc_html($children); ?> Children</div>
                    </div>
                </div>
            </div>
            <div style="flex: 1; background: #fff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; min-width: 320px;">
                <h3>Price Breakup</h3>
                <div>Hotel Charges: ₹9,401</div>
                <div>Discounts: -₹575</div>
                <div>Taxes & Fees: ₹1,757</div>
                <div>Convenience Fee: ₹333</div>
                <div style="font-weight: 700; margin-top: 12px;">Total Amount: ₹10,917</div>
                <div style="color: #4caf50; font-weight: 600;">Your Savings: ₹575</div>
            </div>
        </div>
        <div class="room-details-box" style="background: #fafbfc; border-radius: 12px; padding: 18px; margin-bottom: 32px;">
            <h3 style="margin-bottom: 12px;">Room Details</h3>
            <div>1 x Deluxe Room, 1 King Bed, Non Smoking, City View</div>
            <div style="margin-top: 8px; color: #4caf50; font-weight: 600;">✓ Free Breakfast ✓ Free WiFi ✓ Free Parking</div>
            <div style="margin-top: 8px; color: #1976d2; font-weight: 600;">+ free cancellation till 20-Sep-25</div>
        </div>
        <div class="cancellation-policy-box" style="margin-bottom: 32px;">
            <h3>Cancellation Policy</h3>
            <div style="font-weight: 600; color: #388e3c;">100% refund available</div>
            <div style="margin-bottom: 8px;">Before 20th September, 10:30 PM IST</div>
            <div style="font-weight: 600; color: #d32f2f;">Non-refundable</div>
            <div>After 20th September, 10:30 PM IST</div>
        </div>
        <div class="primary-guest-details-box" style="margin-bottom: 32px;">
            <h3>Primary Guest Details</h3>
            <form>
                <div style="display: flex; gap: 24px; margin-bottom: 16px;">
                    <input type="email" placeholder="Enter your email" style="flex: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <input type="text" placeholder="Enter your phone number" style="flex: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                </div>
                <div style="display: flex; gap: 24px; margin-bottom: 16px;">
                    <input type="text" placeholder="First Name" style="flex: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                    <input type="text" placeholder="Last Name" style="flex: 1; padding: 10px; border-radius: 6px; border: 1px solid #ccc;">
                </div>
                <input type="text" placeholder="Enter your PAN" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-bottom: 16px;">
            </form>
        </div>
        <div class="special-request-box" style="margin-bottom: 32px;">
            <h3>Special Request</h3>
            <form>
                <label><input type="checkbox"> Extra Bed</label>
                <label><input type="checkbox"> Early Check-in</label>
                <label><input type="checkbox"> Wheelchair</label>
                <label><input type="checkbox"> Smoking Room</label>
                <textarea placeholder="Eg. I want an extra bed" style="width: 100%; margin-top: 12px; padding: 10px; border-radius: 6px; border: 1px solid #ccc;"></textarea>
            </form>
        </div>
        <div class="gst-details-box" style="margin-bottom: 32px;">
            <h3>Add GST Details (Optional)</h3>
            <form>
                <label><input type="checkbox"> I have a GST number</label>
                <input type="text" placeholder="Enter your GST number" style="width: 100%; padding: 10px; border-radius: 6px; border: 1px solid #ccc; margin-top: 8px;">
            </form>
        </div>
        <div class="payment-options-box" style="margin-bottom: 32px;">
            <h3>Payment Options</h3>
            <label><input type="radio" checked> Pay with full amount now</label>
            <div style="font-size: 1.5em; font-weight: 700; margin-top: 8px;">₹10,917</div>
        </div>
        <button style="background: #d32f2f; color: #fff; border: none; border-radius: 6px; padding: 14px 32px; font-size: 1.2em; font-weight: 700; cursor: pointer;">Proceed to Pay</button>
    </div>
</div>
<?php get_footer(); ?>
