<?php
/**
 * Template Name: TBO Multi-Room Test
 * 
 * A simple template to test the TBO Hotels API with multiple rooms
 */

// Include WordPress header
get_header();

// Initialize variables
$results = [];
$api_response = '';
$api_request = '';
$error = '';

// Process form submission
if (isset($_POST['test_api']) && $_POST['test_api'] == 'true') {
    // Get form parameters
    $hotel_code = isset($_POST['hotel_code']) ? sanitize_text_field($_POST['hotel_code']) : '';
    $check_in = isset($_POST['check_in']) ? sanitize_text_field($_POST['check_in']) : '';
    $check_out = isset($_POST['check_out']) ? sanitize_text_field($_POST['check_out']) : '';
    $num_rooms = isset($_POST['rooms']) ? intval($_POST['rooms']) : 1;
    $adults_per_room = isset($_POST['adults']) ? intval($_POST['adults']) : 1;
    $children_per_room = isset($_POST['children']) ? intval($_POST['children']) : 0;
    $country_code = isset($_POST['country_code']) ? sanitize_text_field($_POST['country_code']) : 'IN';
    $test_type = isset($_POST['test_type']) ? sanitize_text_field($_POST['test_type']) : 'search';
    
    // Create the PaxRooms array
    $pax_rooms = [];
    for ($i = 0; $i < $num_rooms; $i++) {
        $childrenAges = [];
        if ($children_per_room > 0) {
            for ($j = 0; $j < $children_per_room; $j++) {
                $childrenAges[] = 10; // Default age 10
            }
        }
        
        $pax_rooms[] = [
            "Adults" => $adults_per_room,
            "Children" => $children_per_room,
            "ChildrenAges" => $childrenAges
        ];
    }
    
    // Set up the API request based on test type
    if ($test_type === 'search') {
        $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/Search";
        $payload = [
            "CheckIn" => $check_in,
            "CheckOut" => $check_out,
            "HotelCodes" => $hotel_code,
            "GuestNationality" => $country_code,
            "PaxRooms" => $pax_rooms,
            "ResponseTime" => 20,
            "IsDetailedResponse" => true,
            "Currency" => "INR"
        ];
    } elseif ($test_type === 'prebook' && !empty($_POST['booking_code'])) {
        $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/PreBook";
        $payload = [
            "BookingCode" => sanitize_text_field($_POST['booking_code']),
            "PaymentMode" => "Limit"
        ];
    } elseif ($test_type === 'book' && !empty($_POST['booking_code'])) {
        $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/Book";
        
        // Create customer details based on room count
        $customer_details = [];
        for ($i = 0; $i < $num_rooms; $i++) {
            $customer_names = [];
            
            // Add adults for this room
            for ($adult = 0; $adult < $adults_per_room; $adult++) {
                $customer_names[] = [
                    "Title" => ($adult == 0) ? "Mr" : "Ms",
                    "FirstName" => "Test" . ($adult + 1),
                    "LastName" => "User" . ($i + 1),
                    "Type" => "Adult"
                ];
            }
            
            // Add children if any
            for ($child = 0; $child < $children_per_room; $child++) {
                $customer_names[] = [
                    "Title" => "Master",
                    "FirstName" => "Child" . ($child + 1),
                    "LastName" => "User" . ($i + 1),
                    "Type" => "Child"
                ];
            }
            
            $customer_details[] = [
                "CustomerNames" => $customer_names
            ];
        }
        
        // Generate a unique client reference
        $client_reference_id = 'TEST' . time() . rand(1000, 9999);
        
        $payload = [
            "BookingCode" => sanitize_text_field($_POST['booking_code']),
            "CustomerDetails" => $customer_details,
            "ClientReferenceId" => $client_reference_id,
            "BookingReferenceId" => $client_reference_id,
            "TotalFare" => 1000, // Placeholder value
            "EmailId" => "test@example.com",
            "PhoneNumber" => "1234567890",
            "PaymentMode" => "Limit"
        ];
    } else {
        $error = "Please provide a booking code for PreBook or Book operations";
    }
    
    if (!$error) {
        // Make API call
        $username = "YOLANDATHTest";
        $password = "Yol@40360746";
        $auth = base64_encode("$username:$password");
        
        $json_payload = json_encode($payload);
        
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Basic $auth"
        ]);
        $response = curl_exec($ch);
        $curl_info = curl_getinfo($ch);
        curl_close($ch);
        
        // Save results
        $api_request = $json_payload;
        $api_response = $response;
        $results = json_decode($response, true);
    }
}

// Current date + 2 days for default values
$tomorrow = date('Y-m-d', strtotime('+1 day'));
$day_after = date('Y-m-d', strtotime('+2 days'));
?>

<style>
    .api-test-container {
        max-width: 1000px;
        margin: 40px auto;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    .api-form {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .api-form h1 {
        margin-top: 0;
        margin-bottom: 25px;
        font-size: 1.8em;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .form-row {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 20px;
    }
    .form-group {
        flex: 1;
        min-width: 200px;
    }
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #333;
    }
    .form-group input, 
    .form-group select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 15px;
    }
    .form-actions {
        margin-top: 30px;
    }
    .btn {
        background: #d9534f;
        color: white;
        border: none;
        padding: 12px 25px;
        font-size: 16px;
        font-weight: 500;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn:hover {
        background: #c9302c;
    }
    .results-container {
        background: #fff;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 3px 12px rgba(0,0,0,0.1);
    }
    .results-container h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 1.6em;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .results-section {
        margin-bottom: 30px;
    }
    .results-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.3em;
        color: #444;
    }
    .code-block {
        background: #f5f5f5;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        padding: 15px;
        white-space: pre-wrap;
        overflow-x: auto;
        font-family: monospace;
        font-size: 14px;
        line-height: 1.4;
    }
    .error-message {
        color: #d9534f;
        padding: 10px;
        background: #f2dede;
        border-radius: 4px;
        margin-bottom: 20px;
    }
</style>

<div class="api-test-container">
    <div class="api-form">
        <h1>TBO Hotels API Test Tool - Multiple Rooms</h1>
        
        <?php if ($error): ?>
            <div class="error-message"><?php echo esc_html($error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <input type="hidden" name="test_api" value="true">
            
            <div class="form-row">
                <div class="form-group">
                    <label for="test_type">API Operation</label>
                    <select name="test_type" id="test_type">
                        <option value="search" <?php selected(isset($_POST['test_type']) && $_POST['test_type'] == 'search'); ?>>Search</option>
                        <option value="prebook" <?php selected(isset($_POST['test_type']) && $_POST['test_type'] == 'prebook'); ?>>PreBook</option>
                        <option value="book" <?php selected(isset($_POST['test_type']) && $_POST['test_type'] == 'book'); ?>>Book</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="hotel_code">Hotel Code</label>
                    <input type="text" name="hotel_code" id="hotel_code" value="<?php echo isset($_POST['hotel_code']) ? esc_attr($_POST['hotel_code']) : '1131638'; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="booking_code">Booking Code (for PreBook/Book)</label>
                    <input type="text" name="booking_code" id="booking_code" value="<?php echo isset($_POST['booking_code']) ? esc_attr($_POST['booking_code']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="check_in">Check In</label>
                    <input type="date" name="check_in" id="check_in" value="<?php echo isset($_POST['check_in']) ? esc_attr($_POST['check_in']) : $tomorrow; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="check_out">Check Out</label>
                    <input type="date" name="check_out" id="check_out" value="<?php echo isset($_POST['check_out']) ? esc_attr($_POST['check_out']) : $day_after; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="country_code">Country Code</label>
                    <input type="text" name="country_code" id="country_code" value="<?php echo isset($_POST['country_code']) ? esc_attr($_POST['country_code']) : 'IN'; ?>" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="rooms">Number of Rooms</label>
                    <select name="rooms" id="rooms">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected(isset($_POST['rooms']) && $_POST['rooms'] == $i); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="adults">Adults per Room</label>
                    <select name="adults" id="adults">
                        <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected(isset($_POST['adults']) && $_POST['adults'] == $i); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="children">Children per Room</label>
                    <select name="children" id="children">
                        <?php for ($i = 0; $i <= 3; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php selected(isset($_POST['children']) && $_POST['children'] == $i); ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn">Test API</button>
            </div>
        </form>
    </div>
    
    <?php if (!empty($api_response)): ?>
        <div class="results-container">
            <h2>API Test Results</h2>
            
            <div class="results-section">
                <h3>Request Payload:</h3>
                <div class="code-block"><?php echo esc_html(json_encode(json_decode($api_request), JSON_PRETTY_PRINT)); ?></div>
            </div>
            
            <div class="results-section">
                <h3>API Response:</h3>
                <div class="code-block"><?php echo esc_html(json_encode($results, JSON_PRETTY_PRINT)); ?></div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php get_footer(); ?>