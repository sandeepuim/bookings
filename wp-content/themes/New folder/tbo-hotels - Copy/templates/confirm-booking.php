<?php
/**
 * Template Name: Confirm Booking
 */
get_header();

// Collect POST data
$booking_code       = $_POST['booking_code'] ?? '';
$hotel_code         = $_POST['hotel_code'] ?? '';
$guest_first_name   = $_POST['first_name'] ?? '';
$guest_last_name    = $_POST['last_name'] ?? '';
$guest_email        = $_POST['email'] ?? '';
$guest_phone        = $_POST['phone'] ?? '';
$TotalFare          = $_POST['TotalFare'] ?? '';

$error = '';
$booking_reference_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $booking_code && $guest_first_name && $guest_last_name && $guest_email && $guest_phone) {
    
    // Unique client reference
    $client_reference_id = 'REF' . time() . rand(1000, 9999);

    // Build payload for Book API
    $payload = [
        "BookingCode"       => $booking_code,
        "CustomerDetails"   => [[
            "CustomerNames" => [[
                "Title"     => "Mr", // Later make dynamic
                "FirstName" => $guest_first_name,
                "LastName"  => $guest_last_name,
                "Type"      => "Adult"
            ]]
        ]],
        "ClientReferenceId"   => $client_reference_id,
        "BookingReferenceId"  => $client_reference_id,
        "TotalFare"           => $TotalFare,
        "EmailId"             => $guest_email,
        "PhoneNumber"         => $guest_phone,
        "PaymentMode"         => "Limit"
    ];

    // API Call
    $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/Book";
    $username = "YOLANDATHTest";
    $password = "Yol@40360746";
    $auth     = base64_encode("$username:$password");

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

    if (!empty($result['Status']['Code']) && $result['Status']['Code'] == 200) {
        if (!empty($result['BookingReferenceId'])) {
            $booking_reference_id = $result['BookingReferenceId'];

            // Redirect to booking-details page with reference ID
            wp_redirect(home_url('/booking-details/?ref=' . urlencode($booking_reference_id)));
            exit;
        }
    } else {
        $error = $result['Status']['Description'] ?? 'Booking failed. Please try again.';
    }
}
?>

<style>
.confirm-booking-page {
    max-width: 600px;
    margin: 60px auto;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 32px;
    text-align: center;
    font-family: Arial, sans-serif;
}
.confirm-booking-page h2 {
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.6em;
}
.confirm-booking-page .loading {
    font-size: 1.2em;
    color: #555;
}
.confirm-booking-page .error {
    color: #d9534f;
    font-weight: 600;
    margin-bottom: 20px;
}
.spinner {
    margin: 20px auto;
    width: 48px;
    height: 48px;
    border: 5px solid #eee;
    border-top: 5px solid #007b5e;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    100% { transform: rotate(360deg); }
}
</style>

<div class="confirm-booking-page">
    <h2>Confirm Booking</h2>

    <?php if ($error): ?>
        <div class="error">Error: <?php echo esc_html($error); ?></div>
        <a href="<?php echo home_url('/checkout/?booking_code=' . urlencode($booking_code)); ?>" 
           style="color:#007b5e;font-weight:600;">← Go back to Checkout</a>
    <?php elseif (!$booking_reference_id): ?>
        <div class="spinner"></div>
        <div class="loading">Processing your booking, please wait...</div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>
