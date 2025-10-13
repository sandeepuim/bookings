<?php
// Fallback format price function if not defined elsewhere
if (!function_exists('tbo_hotels_format_price')) {
    function tbo_hotels_format_price($price, $currency = 'INR') {
        $price = floatval($price);
        $currency = $currency ? strtoupper($currency) : 'INR';
        $symbol = '';
        switch ($currency) {
            case 'INR': $symbol = '₹'; break;
            case 'USD': $symbol = '$'; break;
            case 'EUR': $symbol = '€'; break;
            case 'GBP': $symbol = '£'; break;
            default: $symbol = $currency . ' '; break;
        }
        return $symbol . number_format($price, 2);
    }
}

// Fallback calculate nights function if not defined elsewhere
if (!function_exists('tbo_hotels_calculate_nights')) {
    function tbo_hotels_calculate_nights($checkin, $checkout) {
        $checkin_date = strtotime($checkin);
        $checkout_date = strtotime($checkout);
        if ($checkin_date && $checkout_date && $checkout_date > $checkin_date) {
            $nights = ($checkout_date - $checkin_date) / (60 * 60 * 24);
            return max(1, intval($nights));
        }
        return 1;
    }
}

// Fallback star rating function if not defined elsewhere
if (!function_exists('tbo_hotels_get_star_rating')) {
    function tbo_hotels_get_star_rating($rating, $size = 'normal') {
        $rating = intval($rating);
        $output = '<span class="star-rating star-rating-' . esc_attr($size) . '">';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $rating) {
                $output .= '<i class="fas fa-star"></i>';
            } else {
                $output .= '<i class="far fa-star"></i>';
            }
        }
        $output .= '</span>';
        return $output;
    }
}

function tbo_hotels_get_hotel_details_api($hotel_code) {
    $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/Hoteldetails";
    $payload = json_encode([
        "Hotelcodes" => $hotel_code,
        "Language" => "en"
    ]);

    $username = "YOLANDATHTest";
    $password = "Yol@40360746";
    $auth = base64_encode("$username:$password");

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic $auth"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    curl_close($ch);
    $data = json_decode($response, true);
    if (!empty($data['HotelDetails']) && is_array($data['HotelDetails'])) {
        return $data['HotelDetails'][0];
    }
    return null;
}

function tbo_hotels_get_hotel_details_from_search_api($params) {
    $api_url = "http://api.tbotechnology.in/TBOHolidays_HotelAPI/Search";
    
    // Create the PaxRooms array with correct number of room entries
    $paxRooms = [];
    $num_rooms = isset($params['rooms']) ? intval($params['rooms']) : 1;
    $adults_per_room = isset($params['adults']) ? intval($params['adults']) : 1;
    $children_per_room = isset($params['children']) ? intval($params['children']) : 0;
    
    // Create the number of rooms requested
    for ($i = 0; $i < $num_rooms; $i++) {
        // If there are children, create an ages array (using default age 10)
        $childrenAges = [];
        if ($children_per_room > 0) {
            for ($j = 0; $j < $children_per_room; $j++) {
                $childrenAges[] = 10; // Default age 10 for children
            }
        }
        
        $paxRooms[] = [
            "Adults" => $adults_per_room,
            "Children" => $children_per_room,
            "ChildrenAges" => $childrenAges
        ];
    }
    
    $payload = json_encode([
        "CheckIn" => $params['checkin'],
        "CheckOut" => $params['checkout'],
        "HotelCodes" => $params['hotel_code'],
        "GuestNationality" => $params['country_code'] ?: "IN",
        "PaxRooms" => $paxRooms,
        "ResponseTime" => 20,
        "IsDetailedResponse" => true,
        "Currency" => "INR"
    ]);

    $username = "YOLANDATHTest";
    $password = "Yol@40360746";
    $auth = base64_encode("$username:$password");

    $ch = curl_init($api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Basic $auth"
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    $response = curl_exec($ch);
    curl_close($ch);
// echo "<h3>➡️ Request URL:</h3><pre>{$api_url}</pre>";
// echo "<h3>➡️ Request Headers:</h3><pre>";
// print_r([
//     "Content-Type: application/json",
//     "Authorization: Basic $auth"
// ]);
// echo "</pre>";
    $data = json_decode($response, true);

// echo "<h3>➡️ Request Body (Payload):</h3><pre>{$payload}</pre>";
// echo "<h3>➡️ API Response:</h3><pre>{$response}</pre>";
    if (!empty($data['HotelResult'])) {
        foreach ($data['HotelResult'] as $hotel) {
            if ($hotel['HotelCode'] == $params['hotel_code']) {
                // Normalize room data for template compatibility
                if (!empty($hotel['Rooms'])) {
                    $hotel['RoomDetails'] = [];
                    foreach ($hotel['Rooms'] as $room) {
                        $hotel['RoomDetails'][] = [
                            'RoomTypeName' => isset($room['Name'][0]) ? $room['Name'][0] : '',
                            'RoomImages' => [], // No images in Search API room, fallback to hotel images
                            'RoomFacilities' => isset($room['Inclusion']) ? [$room['Inclusion']] : [],
                            'MaxAdults' => 2, // Not provided, fallback
                            'MaxChildren' => 0, // Not provided, fallback
                            'RoomPrice' => $room['TotalFare'] ?? null,
                            'CurrencyCode' => $hotel['Currency'] ?? 'INR',
                            'CancellationPolicy' => isset($room['CancelPolicies'][0]) ? ($room['CancelPolicies'][0]['ChargeType'] . ' ' . $room['CancelPolicies'][0]['CancellationCharge'] . '%') : '',
                            'MealType' => $room['MealType'] ?? '',
                            'BookingCode' => $room['BookingCode'] ?? '',
                            'RoomPromotion' => isset($room['RoomPromotion'][0]) ? $room['RoomPromotion'][0] : '',
                        ];
                    }
                }
                return $hotel;
            }
        }
    }
    return null;
}
 
/**
 * Template Name: Hotel Details
 * 
 * Template for displaying hotel details
 */

// Get the header
get_header();
$hotel_code   = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$country_code = isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : '';
$city_code    = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in     = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out    = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$rooms        = isset($_GET['rooms']) ? intval($_GET['rooms']) : 0;
$adults       = isset($_GET['adults']) ? intval($_GET['adults']) : 0;
$children     = isset($_GET['children']) ? intval($_GET['children']) : 0;
// echo "<pre>";
// print_r($_GET);
// Get hotel code from URL
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$country_code = isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$checkin = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$checkout = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 1;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Check if we have required parameters
$can_fetch_details = !empty($hotel_code) && !empty($city_code) && !empty($checkin) && !empty($checkout);
 // print_r($can_fetch_details);
// Initialize hotel data
$hotel_data = null;
$hotel_detail_api = null;

// Fetch hotel details if we have required parameters
if ($can_fetch_details) {
    // Prepare request data
    $request_data = array(
        'hotel_code' => $hotel_code,
        'country_code' => $country_code,
        'city_code' => $city_code,
        'checkin' => $checkin,
        'checkout' => $checkout,
        'adults' => $adults,
        'children' => $children,
        'rooms' => $rooms
    );
    // Get hotel room types and pricing from Search API
    $hotel_data = tbo_hotels_get_hotel_details_from_search_api($request_data);
//    echo '<pre>';print_r($hotel_data);echo '</pre>';
    // Get hotel details from new Hoteldetails API
    $hotel_detail_api = tbo_hotels_get_hotel_details_api($hotel_code);
    // echo '<pre>';print_r($hotel_data);echo '</pre>';
    // Merge details from Hoteldetails API into hotel_data
    if ($hotel_data && $hotel_detail_api) {
        // Overwrite/merge key details
        $hotel_data['HotelName'] = $hotel_detail_api['HotelName'] ?? $hotel_data['HotelName'];
        $hotel_data['HotelDescription'] = $hotel_detail_api['Description'] ?? $hotel_data['HotelDescription'];
        $hotel_data['HotelFacilities'] = $hotel_detail_api['HotelFacilities'] ?? $hotel_data['HotelFacilities'];
        $hotel_data['HotelAddress'] = $hotel_detail_api['Address'] ?? ($hotel_data['HotelAddress'] ?? '');
        $hotel_data['HotelImages'] = $hotel_detail_api['Images'] ?? $hotel_data['HotelImages'];
        $hotel_data['HotelLocation'] = $hotel_detail_api['Attractions'] ? implode(', ', $hotel_detail_api['Attractions']) : ($hotel_data['HotelLocation'] ?? '');
        $hotel_data['StarRating'] = $hotel_detail_api['HotelRating'] ?? ($hotel_data['StarRating'] ?? 0);
        $hotel_data['Latitude'] = isset($hotel_detail_api['Map']) ? explode('|', $hotel_detail_api['Map'])[0] : ($hotel_data['Latitude'] ?? '');
        $hotel_data['Longitude'] = isset($hotel_detail_api['Map']) ? explode('|', $hotel_detail_api['Map'])[1] : ($hotel_data['Longitude'] ?? '');
        $hotel_data['HotelWebsiteUrl'] = $hotel_detail_api['HotelWebsiteUrl'] ?? '';
        $hotel_data['PhoneNumber'] = $hotel_detail_api['PhoneNumber'] ?? '';
        $hotel_data['Email'] = $hotel_detail_api['Email'] ?? '';
        $hotel_data['CheckInTime'] = $hotel_detail_api['CheckInTime'] ?? '';
        $hotel_data['CheckOutTime'] = $hotel_detail_api['CheckOutTime'] ?? '';
    }
}
?>

<div class="container">
    <div class="hotel-details-container">
        <?php            // echo '<pre>'; print_r($hotel_data); echo '</pre>';
if ($can_fetch_details && $hotel_data && !is_wp_error($hotel_data)): ?>
            
            <div class="hotel-details-header" style="display: flex; gap: 32px; align-items: flex-start; margin-bottom: 32px;">
                                        <!-- Left: Images -->
                                        <div style="flex: 2;">
                                            <h1 style="font-size: 2em; font-weight: 700; margin-bottom: 6px; color: #222;">
                                                <?php echo esc_html($hotel_data['HotelName']); ?>
                                                <span style="margin-left: 8px; vertical-align: middle;">
                                                    <?php echo tbo_hotels_get_star_rating($hotel_data['StarRating'] ?? 0); ?>
                                                </span>
                                            </h1>
                                            <div style="color: #007bff; margin-bottom: 10px; font-size: 1.1em;">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <?php echo esc_html($hotel_data['HotelAddress'] ?? 'Address not available'); ?>
                                            </div>
                                            <!-- Image grid -->
                                            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 12px; margin-bottom: 8px;">
                                                <?php if (!empty($hotel_data['HotelImages']) && count($hotel_data['HotelImages']) > 0): ?>
                                                    <div style="grid-row: 1 / span 2; grid-column: 1 / 2;">
                                                        <img src="<?php echo esc_url($hotel_data['HotelImages'][0]); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>" style="width: 100%; height: 260px; object-fit: cover; border-radius: 12px;">
                                                    </div>
                                                    <?php if (count($hotel_data['HotelImages']) > 1): ?>
                                                        <div style="grid-column: 2 / 3;">
                                                            <img src="<?php echo esc_url($hotel_data['HotelImages'][1]); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 12px;">
                                                        </div>
                                                    <?php endif; ?>
                                                    <?php if (count($hotel_data['HotelImages']) > 2): ?>
                                                        <div style="grid-column: 2 / 3; grid-row: 2 / 3; position: relative;">
                                                            <img src="<?php echo esc_url($hotel_data['HotelImages'][2]); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>" style="width: 100%; height: 120px; object-fit: cover; border-radius: 12px; filter: brightness(0.7);">
                                                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 1.3em; font-weight: 600;">
                                                                View <?php echo count($hotel_data['HotelImages']) - 2; ?>+ more
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <div style="grid-row: 1 / span 2; grid-column: 1 / 2;">
                                                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/placeholder.jpg'); ?>" alt="<?php echo esc_attr($hotel_data['HotelName']); ?>" style="width: 100%; height: 260px; object-fit: cover; border-radius: 12px;">
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <!-- Right: Room Card -->
                                        <div style="flex: 1; background: #fff; border: 1px solid #eee; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 24px; min-width: 320px;">
                                            <?php $firstRoom = !empty($hotel_data['RoomDetails']) ? $hotel_data['RoomDetails'][0] : null; ?>
                                            <?php if ($firstRoom): ?>
                                                <div style="font-size: 1.15em; font-weight: 600; margin-bottom: 6px; color: #222;">
                                                    <?php echo esc_html($firstRoom['RoomTypeName'] ?? 'Room'); ?>
                                                </div>
                                                <div style="font-size: 1.7em; font-weight: 700; color: #222; margin-bottom: 2px;">
                                                    <?php
                                                   // echo $firstRoom['RoomPrice'].'<br>sandeepz';

                                                    $room_price = $firstRoom['RoomPrice'] ?? ($firstRoom['Price'] ?? null);
                                                    $currency_code = $firstRoom['CurrencyCode'] ?? ($hotel_data['Price']['CurrencyCode'] ?? 'INR');

                                                    if ($room_price !== null) {
                                                        echo tbo_hotels_format_price($room_price, $currency_code);
                                                    } else {
                                                        echo 'Price not available';
                                                    }
                                                    ?>
                                                </div>
                                                <div style="color: #888; font-size: 0.98em; margin-bottom: 10px;">+ taxes & fees per room per night</div>
                                                <div style="display: flex; gap: 18px; margin-bottom: 10px;">
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <i class="far fa-calendar-check"></i>
                                                        <span style="font-weight: 500;">Check In:</span>
                                                        <span style="font-weight: 700; color: #222;"> <?php echo !empty($hotel_data['CheckInTime']) ? esc_html($hotel_data['CheckInTime']) : '03:00 PM'; ?> </span>
                                                    </div>
                                                    <div style="display: flex; align-items: center; gap: 6px;">
                                                        <i class="far fa-calendar-times"></i>
                                                        <span style="font-weight: 500;">Check Out:</span>
                                                        <span style="font-weight: 700; color: #222;"> <?php echo !empty($hotel_data['CheckOutTime']) ? esc_html($hotel_data['CheckOutTime']) : '12:00 PM'; ?> </span>
                                                    </div>
                                                </div>
                                                <hr style="margin: 12px 0;">
                                                <div style="margin-bottom: 10px;">
                                                    <?php if (!empty($firstRoom['RoomFacilities'])): ?>
                                                        <?php foreach (array_slice($firstRoom['RoomFacilities'], 0, 2) as $facility): ?>
                                                            <div style="color: #222; font-size: 1em; margin-bottom: 4px;"><span style="color: #2eb67d; font-size: 1.1em; margin-right: 6px;">&#10003;</span> <?php echo esc_html($facility); ?></div>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div style="display: flex; gap: 12px; margin-top: 18px;">
                                                    <a href="#rooms" style="background: #fff; color: #d9534f; border: 1px solid #d9534f; border-radius: 6px; padding: 10px 18px; font-size: 1em; font-weight: 600; text-align: center; text-decoration: none;">Other rooms</a>
                                                    <form action="<?php echo esc_url(home_url('/checkout/')); ?>" method="get" style="margin: 0;">
                                                        <input type="hidden" name="hotel_code" value="<?php echo esc_attr($hotel_code); ?>">
                                                        <input type="hidden" name="city_code" value="<?php echo esc_attr($city_code); ?>">
                                                        <input type="hidden" name="check_in" value="<?php echo esc_attr($checkin); ?>">
                                                        <input type="hidden" name="check_out" value="<?php echo esc_attr($checkout); ?>">
                                                        <input type="hidden" name="adults" value="<?php echo esc_attr($adults); ?>">
                                                        <input type="hidden" name="children" value="<?php echo esc_attr($children); ?>">
                                                        <input type="hidden" name="rooms" value="<?php echo esc_attr($rooms); ?>">
                                                        <input type="hidden" name="room_type" value="<?php echo esc_attr($firstRoom['RoomTypeName'] ?? ''); ?>">
                                                        <input type="hidden" name="BookingCode" value="<?php echo esc_attr($firstRoom['BookingCode'] ?? ''); ?>">
                                                        <button type="submit" style="background: #d9534f; color: #fff; border: none; border-radius: 6px; padding: 10px 18px; font-size: 1em; font-weight: 600; cursor: pointer;">Book this room</button>
                                                    </form>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
            
            <div class="hotel-details-content">
                <div class="hotel-details-main">
                    <?php if (!empty($hotel_data['HotelDescription'])): ?>
                        <section class="hotel-description" style="margin-bottom: 40px;">
                            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 18px; color: #222;">About This Hotel</h2>
                            <div class="description-content" style="font-size: 1.15em; color: #333; line-height: 1.7;">
                                <?php 
                                $desc = $hotel_data['HotelDescription'];
                                // Add extra spacing after paragraphs and lists
                                $desc = str_replace('<ul>', '<ul style="margin: 16px 0 24px 32px;">', $desc);
                                $desc = str_replace('<li>', '<li style="margin-bottom: 8px;">', $desc);
                                $desc = str_replace('<b>', '<b style="font-weight:700; color:#222;">', $desc);
                                $desc = str_replace('<h2>', '<h2 style="font-size:1.3em; font-weight:600; margin-top:24px; color:#222;">', $desc);
                                $desc = str_replace('<p>', '<p style="margin-bottom: 16px;">', $desc);
                                echo wpautop($desc);
                                ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <!-- Room Types Section -->
                    <?php if (!empty($hotel_data['RoomDetails']) && is_array($hotel_data['RoomDetails'])): ?>
                        <section class="hotel-room-types yatra-style">
                            <h2 class="room-types-title">Choose Your Room</h2>
                            <?php $roomDetails = $hotel_data['RoomDetails']; ?>
                            <?php $firstRoom = $roomDetails[0]; ?>
                            <!-- Featured Room (first) -->
                            
                            <!-- Other Rooms -->
                            <?php if (count($roomDetails) > 1): ?>
                                <div class="room-type-list yatra-list">
                                    <?php foreach (array_slice($roomDetails, 1) as $room): ?>
                                        <div class="room-type-card yatra-card" style="display: flex; align-items: flex-start; gap: 24px; margin-bottom: 32px; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.04); padding: 16px;">
                                            <div class="room-type-image-wrap" style="flex: 0 0 120px;">
                                                <?php if (!empty($room['RoomImages'][0])): ?>
                                                    <img class="room-type-image" src="<?php echo esc_url($room['RoomImages'][0]); ?>" alt="<?php echo esc_attr($room['RoomTypeName'] ?? 'Room'); ?>" style="width: 120px; height: 90px; object-fit: cover; border-radius: 6px;">
                                                <?php else: ?>
                                                    <img class="room-type-image" src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/room-placeholder.jpg'); ?>" alt="Room" style="width: 120px; height: 90px; object-fit: cover; border-radius: 6px;">
                                                <?php endif; ?>
                                            </div>
                                            <div class="room-type-info" style="flex: 1;">
                                                <h3 class="room-type-name yatra-room-title" style="margin: 0 0 8px 0; font-size: 1.2em; font-weight: 600; color: #222;"><?php echo esc_html($room['RoomTypeName'] ?? 'Room'); ?></h3>
                                                <div class="room-type-occupancy yatra-occupancy" style="margin-bottom: 6px; color: #555;">
                                                    <span title="Adults"><i class="fas fa-user-friends"></i> <?php echo intval($room['MaxAdults'] ?? 2); ?> Adults</span>
                                                    <?php if (isset($room['MaxChildren'])): ?>
                                                        <span title="Children" style="margin-left: 12px;"><i class="fas fa-child"></i> <?php echo intval($room['MaxChildren']); ?> Children</span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($room['RoomFacilities'])): ?>
                                                    <div class="room-type-amenities yatra-amenities" style="margin-bottom: 6px; color: #666; font-size: 0.98em;">
                                                        <?php foreach (array_slice($room['RoomFacilities'], 0, 6) as $facility): ?>
                                                            <span class="room-amenity yatra-amenity" style="margin-right: 10px;"><i class="fas fa-check-circle"></i> <?php echo esc_html($facility); ?></span>
                                                        <?php endforeach; ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="room-type-price yatra-price" style="margin-bottom: 6px; color: #007b5e; font-weight: 500;">
                                                    <span class="price-label">Price per night:</span>
                                                    <span class="price-value">
                                                    <?php
                                                    $room_price = $room['RoomPrice'] ?? ($room['Price'] ?? null);
                                                    $currency_code = $room['CurrencyCode'] ?? ($hotel_data['Price']['CurrencyCode'] ?? 'INR');
                                                    if ($room_price !== null) {
                                                        echo tbo_hotels_format_price($room_price, $currency_code);
                                                    } else {
                                                        echo 'Price not available';
                                                    }
                                                    ?>
                                                    </span>
                                                </div>
                                                <?php if (!empty($room['CancellationPolicy'])): ?>
                                                    <div class="room-type-cancellation yatra-cancellation" style="margin-bottom: 6px; color: #d9534f;">
                                                        <i class="fas fa-undo-alt"></i> <span class="cancellation-label">Free Cancellation:</span> <span class="cancellation-value"><?php echo esc_html($room['CancellationPolicy']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                            <div class="room-type-action yatra-action" style="flex: 0 0 120px; display: flex; align-items: center; justify-content: center;">
                                                <form action="<?php echo esc_url(home_url('/checkout/')); ?>" method="get">
                                                    <input type="hidden" name="hotel_code" value="<?php echo esc_attr($hotel_code); ?>">
                                                    <input type="hidden" name="city_code" value="<?php echo esc_attr($city_code); ?>">
                                                    <input type="hidden" name="checkin" value="<?php echo esc_attr($checkin); ?>">
                                                    <input type="hidden" name="checkout" value="<?php echo esc_attr($checkout); ?>">
                                                    <input type="hidden" name="adults" value="<?php echo esc_attr($adults); ?>">
                                                    <input type="hidden" name="children" value="<?php echo esc_attr($children); ?>">
                                                    <input type="hidden" name="rooms" value="<?php echo esc_attr($rooms); ?>">
                                                    <input type="hidden" name="room_type" value="<?php echo esc_attr($room['RoomTypeName'] ?? ''); ?>">
                                                    <input type="hidden" name="BookingCode" value="<?php echo esc_attr($room['BookingCode'] ?? ''); ?>">
                                                    
                                                    <button type="submit" class="select-room-button yatra-book-btn" style="background: #007b5e; color: #fff; border: none; border-radius: 4px; padding: 10px 18px; font-size: 1em; cursor: pointer;">Book Now</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($hotel_data['HotelFacilities']) && count($hotel_data['HotelFacilities']) > 0): ?>
                        <section class="hotel-facilities" style="margin-top: 32px;">
                            <h2 style="font-size: 2em; font-weight: 700; margin-bottom: 18px;">Hotel Amenities</h2>
                            <div class="hotel-facilities-grid" style="background: #fff; border: 1px solid #ddd; border-radius: 12px; padding: 24px; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;">
                                <?php
                                // Example grouping logic (replace with your actual facility data structure)
                                $facility_groups = [
                                    'Services' => ['Laundry facilities', 'WIFI', 'Separate Smoking Area', 'Laundry facilities', 'Lift', 'Wedding services', 'Luggage storage'],
                                    'Miscellaneous' => ['Comprehensive recycling policy', 'Vegetarian breakfast available', 'Parking (limited spaces)', 'Biodegradable/compostable stirrers', 'Front entrance ramp', 'Number of meeting rooms - 9', 'No single-use plastic stirrers'],
                                    'General' => ['Breakfast Services', 'Safe Deposit Box'],
                                    'Activities - Indoor & Outdoor' => ['Fitness Centre', 'Porter'],
                                    'Parking & Transportation' => ['Parking'],
                                    'Payment Modes Accepted' => ['Visa', 'Diners Club', 'Debit cards', 'Discover', 'Cash', 'American Express'],
                                ];
                                $group_icons = [
                                    'Services' => '<i class="fas fa-concierge-bell"></i>',
                                    'Miscellaneous' => '<i class="fas fa-tasks"></i>',
                                    'General' => '<i class="fas fa-user-tie"></i>',
                                    'Activities - Indoor & Outdoor' => '<i class="fas fa-volleyball-ball"></i>',
                                    'Parking & Transportation' => '<i class="fas fa-parking"></i>',
                                    'Payment Modes Accepted' => '<i class="fas fa-credit-card"></i>',
                                ];
                                foreach ($facility_groups as $group => $items):
                                ?>
                                    <div class="facility-group" style="margin-bottom: 12px;">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                            <span style="font-size: 1.6em; color: #222;">
                                                <?php echo $group_icons[$group] ?? ''; ?>
                                            </span>
                                            <span style="font-size: 1.3em; font-weight: 700; color: #222;"> <?php echo esc_html($group); ?></span>
                                        </div>
                                        <ul style="list-style: none; padding-left: 0; margin-bottom: 0;">
                                            <?php foreach (array_slice($items, 0, 7) as $facility): ?>
                                                <li style="margin-bottom: 6px; color: #222; font-size: 1em;"><span style="color: #2eb67d; font-size: 1.1em; margin-right: 6px;">&#10003;</span> <?php echo esc_html($facility); ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                        <?php if (count($items) > 7): ?>
                                            <a href="#" style="color: #007b5e; font-size: 0.98em; margin-top: 4px; display: inline-block;">View More <span style="font-size: 1.1em;">&#x25BC;</span></a>
                                        <?php else: ?>
                                            <span style="display: block; height: 24px;"></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($hotel_data['HotelLocation'])): ?>
                        <section class="hotel-location-section">
                            <h2>Location</h2>
                            <div class="location-content">
                                <div class="location-map">
                                    <?php if (!empty($hotel_data['Latitude']) && !empty($hotel_data['Longitude'])): ?>
                                        <div class="map-container">
                                            <iframe
                                                width="100%"
                                                height="400"
                                                frameborder="0"
                                                style="border:0"
                                                src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=<?php echo esc_attr($hotel_data['Latitude']); ?>,<?php echo esc_attr($hotel_data['Longitude']); ?>"
                                                allowfullscreen
                                            ></iframe>
                                        </div>
                                    <?php else: ?>
                                        <div class="map-placeholder">
                                            <p>Map location not available</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="location-description">
                                    <?php echo wpautop(esc_html($hotel_data['HotelLocation'])); ?>
                                </div>
                            </div>
                        </section>
                    <?php endif; ?>

                    <?php if (!empty($hotel_data['HotelPolicy'])): ?>
                        <section class="hotel-policies">
                            <h2>Hotel Policies</h2>
                            <div class="policies-content">
                                <?php echo wpautop(esc_html($hotel_data['HotelPolicy'])); ?>
                            </div>
                        </section>
                    <?php endif; ?>
                </div>
                
                <div class="hotel-details-sidebar">
                    
                    sdasd
                    <?php if (!empty($hotel_data['HotelReviews'])): ?>
                        <div class="hotel-reviews-summary">
                            <h3>Guest Reviews</h3>
                            
                            <div class="reviews-average">
                                <div class="average-score"><?php echo number_format($hotel_data['ReviewRating'], 1); ?></div>
                                <div class="average-label">
                                    <?php echo tbo_hotels_get_rating_text($hotel_data['ReviewRating']); ?>
                                    <div class="review-count"><?php echo count($hotel_data['HotelReviews']); ?> reviews</div>
                                </div>
                            </div>
                            
                            <div class="reviews-preview">
                                <?php foreach (array_slice($hotel_data['HotelReviews'], 0, 2) as $review): ?>
                                    <div class="review-item">
                                        <div class="review-header">
                                            <div class="reviewer-name"><?php echo esc_html($review['GuestName']); ?></div>
                                            <div class="review-date"><?php echo date('M Y', strtotime($review['ReviewDate'])); ?></div>
                                        </div>
                                        <div class="review-rating"><?php echo tbo_hotels_get_star_rating($review['Rating'], 'small'); ?></div>
                                        <div class="review-content"><?php echo esc_html($review['Comments']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($hotel_data['HotelReviews']) > 2): ?>
                                    <a href="#reviews" class="view-all-reviews">View all <?php echo count($hotel_data['HotelReviews']); ?> reviews</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php
           // echo '<pre>'; print_r($hotel_data); echo '</pre>';
            if (!empty($hotel_data['HotelReviews']) && count($hotel_data['HotelReviews']) > 2): ?>
                <section id="reviews" class="hotel-reviews-section">
                    <h2>Guest Reviews</h2>
                    
                    <div class="reviews-list">
                        <?php foreach ($hotel_data['HotelReviews'] as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="reviewer-name"><?php echo esc_html($review['GuestName']); ?></div>
                                    <div class="review-date"><?php echo date('M Y', strtotime($review['ReviewDate'])); ?></div>
                                </div>
                                <div class="review-rating"><?php echo tbo_hotels_get_star_rating($review['Rating'], 'small'); ?></div>
                                <div class="review-content"><?php echo esc_html($review['Comments']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="hotel-details-error">
                <h2>Hotel Not Found</h2>
                <p>Sorry, we couldn't find the hotel you're looking for. Please try searching again.</p>
                <a href="<?php echo esc_url(home_url('/hotel-search/')); ?>" class="button">Back to Search</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>