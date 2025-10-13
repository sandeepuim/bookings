<?php
/**
 * Template Name: Checkout
 */
get_header();

// 1. Get BookingCode from URL
$booking_code = isset($_GET['BookingCode']) ? sanitize_text_field($_GET['BookingCode']) : '';

$prebook_response = null;

if ($booking_code) {
    // 2. Call TBO PreBook API
    $payload = [
        "BookingCode" => $booking_code,
        "PaymentMode" => "Limit"
    ];

    $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/PreBook";
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
// echo '<pre>';print_r($response);
    if ($response) {
        $prebook_response = json_decode($response, true);
    }
}
?>
<style>
    .checkout-page {
        max-width: 700px;
        margin: 40px auto 60px auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
        padding: 32px 32px 24px 32px;
    }
    .checkout-room-summary {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 28px 28px 20px 28px;
        margin-bottom: 36px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .checkout-room-summary h3 {
        font-size: 1.35em;
        font-weight: 700;
        margin-top: 0;
        margin-bottom: 20px;
        color: #222;
        letter-spacing: 0.5px;
    }
    .checkout-room-summary p {
        margin: 10px 0;
        font-size: 1.09em;
        color: #333;
    }
    .checkout-room-summary strong {
        font-weight: 700;
        color: #222;
    }
    .guest-details-form-wrap {
        margin-bottom: 0;
    }
    .guest-details-form {
        background: #fff;
        border-radius: 12px;
        padding: 28px 28px 20px 28px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    }
    .guest-details-form h3 {
        font-size: 1.2em;
        font-weight: 700;
        margin-bottom: 20px;
        color: #222;
        letter-spacing: 0.5px;
    }
    .guest-details-form label {
        display: block;
        margin-bottom: 18px;
        font-weight: 500;
        color: #333;
        font-size: 1.05em;
    }
    .guest-details-form input[type="text"],
    .guest-details-form input[type="email"] {
        width: 100%;
        padding: 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 1.05em;
        margin-top: 7px;
        background: #f8f9fa;
    }
    .guest-details-form button {
        background: #007b5e;
        color: #fff;
        border: none;
        border-radius: 6px;
        padding: 14px 32px;
        font-size: 1.12em;
        font-weight: 600;
        cursor: pointer;
        margin-top: 16px;
        transition: background 0.2s;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .guest-details-form button:hover {
        background: #005a43;
    }
    .checkout-page {
    max-width: 700px;
    margin: 40px auto 60px auto;
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    padding: 32px;
    font-family: "Segoe UI", Roboto, sans-serif;
}

.card {
    background: #fff;
    border-radius: 12px;
    padding: 24px 28px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    margin-bottom: 32px;
}

.card h3 {
    font-size: 1.3em;
    font-weight: 600;
    margin-bottom: 18px;
    color: #222;
}

.card p {
    margin: 10px 0;
    font-size: 1.05em;
    color: #444;
}

.card strong {
    font-weight: 600;
    color: #111;
}

/* Form styles */
form label {
    display: block;
    margin-bottom: 6px;
    font-weight: 500;
    color: #333;
}

form .form-group {
    margin-bottom: 18px;
}

form input[type="text"],
form input[type="email"] {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 1em;
    background: #f9f9f9;
    transition: border 0.2s;
}

form input[type="text"]:focus,
form input[type="email"]:focus {
    border-color: #007b5e;
    outline: none;
    background: #fff;
}

form button {
    display: block;
    width: 100%;
    background: #007b5e;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 14px;
    font-size: 1.1em;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s ease;
}

form button:hover {
    background: #005a43;
}

/* Multiple Room Booking Styles */
.room-guest-details {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    border: 1px solid #eee;
}

.room-guest-details h4 {
    font-size: 1.1em;
    font-weight: 600;
    margin: 0 0 15px 0;
    color: #333;
    padding-bottom: 8px;
    border-bottom: 1px solid #eee;
}

.contact-details {
    margin-top: 30px;
    padding-top: 10px;
    border-top: 2px solid #eee;
}

.contact-details h4 {
    font-size: 1.1em;
    font-weight: 600;
    margin: 0 0 15px 0;
    color: #333;
}
    </style>
<div class="checkout-page">
<?php if (!empty($prebook_response) && isset($prebook_response['Status']['Code']) && $prebook_response['Status']['Code'] == 200): ?>

    <!-- Room Summary -->
    <div class="card mb-4">
        <h3>Room Summary</h3>
        <?php
        $hotel = $prebook_response['HotelResult'][0] ?? null;
        $room = $hotel['Rooms'][0] ?? null;
        $adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
        $children = isset($_GET['children']) ? intval($_GET['children']) : 0;
        $rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;
        
        if ($room): ?>
            <p><strong>Room:</strong> <?php echo esc_html($room['Name'][0] ?? ''); ?></p>
            <p><strong>Number of Rooms:</strong> <?php echo esc_html($rooms); ?></p>
            <p><strong>Guests per Room:</strong> <?php echo esc_html($adults); ?> Adults, <?php echo esc_html($children); ?> Children</p>
            <p><strong>Price:</strong> <?php echo esc_html($room['TotalFare'] ?? ''); ?> <?php echo esc_html($hotel['Currency'] ?? ''); ?></p>
            <p><strong>Inclusions:</strong> <?php echo esc_html($room['Inclusion'] ?? ''); ?></p>
            <p><strong>Cancellation Policy:</strong> <?php echo isset($room['CancelPolicies'][0]) ? esc_html($room['CancelPolicies'][0]['ChargeType'] . ' ' . $room['CancelPolicies'][0]['CancellationCharge'] . '%') : 'N/A'; ?></p>
        <?php endif; ?>
    </div>

    <!-- Guest Details Form -->
    <div class="card">
        <h3>Guest Details</h3>
        <form method="post" action="<?php echo home_url(''); ?>/confirm-booking/">
            <input type="hidden" name="booking_code" value="<?php echo esc_attr($booking_code); ?>">
            <input type="hidden" name="TotalFare" value="<?php echo esc_html($room['TotalFare'] ?? ''); ?>">
            <input type="hidden" name="rooms" value="<?php echo isset($_GET['rooms']) ? intval($_GET['rooms']) : 1; ?>">
            <input type="hidden" name="adults" value="<?php echo isset($_GET['adults']) ? intval($_GET['adults']) : 1; ?>">
            <input type="hidden" name="children" value="<?php echo isset($_GET['children']) ? intval($_GET['children']) : 0; ?>"
            
            <?php 
            // Get number of rooms from URL parameter
            $num_rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;
            
            // Loop through each room to create guest fields
            for ($i = 1; $i <= $num_rooms; $i++): 
            ?>
                <div class="room-guest-details">
                    <h4>Room <?php echo $i; ?> Guest Details</h4>
                    
                    <div class="form-group">
                        <label>First Name</label>
                        <input type="text" name="first_name_room<?php echo $i; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Last Name</label>
                        <input type="text" name="last_name_room<?php echo $i; ?>" required>
                    </div>
                </div>
            <?php endfor; ?>
            
            <div class="contact-details">
                <h4>Contact Information</h4>
                
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">Confirm Booking</button>
        </form>
    </div>

<?php else: ?>
    <p>Room not available. Please try again.</p>
<?php endif; ?>
</div>

<?php get_footer(); ?>
