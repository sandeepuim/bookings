
<?php
/**
 * Template Name: Booking Details
 */
get_header();

$booking_reference_id = isset($_GET['ref']) ? sanitize_text_field($_GET['ref']) : '';
$error = '';
$booking_details = null;

if ($booking_reference_id) {
    $payload = [
        "BookingReferenceId" => $booking_reference_id,
        "ConfirmationNumber" => $booking_reference_id,
        "PaymentMode" => "Limit"
    ];
    $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/BookingDetail";
    $username = "YOLANDATHTest";
    $password = "Yol@40360746";
    $auth = base64_encode("$username:$password");
    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic $auth"
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    $result = json_decode($response, true);
    // Debug: Show full API response for admin users
    if (current_user_can('manage_options')) {
        // echo '<pre style="background:#fff;border:1px solid #ccc;padding:12px;max-width:700px;margin:20px auto;">API Response:\n';
        // print_r($result);
        // echo '</pre>';
    }
    if (!empty($result['Status']['Code']) && $result['Status']['Code'] == 200) {
        $booking_details = $result;
    } else {
        $error = $result['Status']['Description'] ?? 'Could not fetch booking details.';
    }
}
?>
<div class="booking-details-page" style="max-width:700px;margin:40px auto;background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,0.08);padding:32px;">
    <h2 style="margin-top:0;">Booking Details</h2>
    <?php if ($error): ?>
        <div style="color:#d9534f;font-weight:600;margin-bottom:24px;">Error: <?php echo esc_html($error); ?></div>
    <?php elseif ($booking_details): ?>
        <?php $bd = $booking_details['BookingDetail'] ?? [];
              $room = isset($bd['Rooms'][0]) ? $bd['Rooms'][0] : [];
              $hotel = $bd['HotelDetails'] ?? [];
              $guest = isset($room['CustomerDetails'][0]['CustomerNames'][0]) ? $room['CustomerDetails'][0]['CustomerNames'][0] : [];
        ?>
        <div style="margin-bottom:24px;">
            <strong>Booking Reference:</strong> <?php echo esc_html($booking_reference_id); ?><br>
            <strong>Status:</strong> <?php echo esc_html($bd['BookingStatus'] ?? ''); ?><br>
            <strong>Hotel:</strong> <?php echo esc_html($hotel['HotelName'] ?? ''); ?><br>
            <strong>Room:</strong> <?php echo esc_html(isset($room['Name'][0]) ? $room['Name'][0] : ''); ?><br>
            <strong>Guest:</strong> <?php echo esc_html($guest['Title'] ?? ''); ?> <?php echo esc_html($guest['FirstName'] ?? ''); ?> <?php echo esc_html($guest['LastName'] ?? ''); ?> (<?php echo esc_html($guest['Type'] ?? ''); ?>)<br>
            <strong>Email:</strong> <!-- Not available in response --><br>
            <strong>Phone:</strong> <!-- Not available in response --><br>
        </div>
        <div style="margin-bottom:24px;">
            <strong>Check-In:</strong> <?php echo esc_html($bd['CheckIn'] ?? ''); ?><br>
            <strong>Check-Out:</strong> <?php echo esc_html($bd['CheckOut'] ?? ''); ?><br>
            <strong>Total Fare:</strong> <?php echo esc_html($room['TotalFare'] ?? ''); ?> <?php echo esc_html($room['Currency'] ?? ''); ?><br>
            <strong>Meal Type:</strong> <?php echo esc_html($room['MealType'] ?? ''); ?><br>
            <strong>Refundable:</strong> <?php echo (isset($room['IsRefundable']) && $room['IsRefundable']) ? 'Yes' : 'No'; ?><br>
        </div>
    <?php else: ?>
        <div>Loading booking details...</div>
    <?php endif; ?>
</div>
<?php get_footer(); ?>
