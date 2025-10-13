<?php
// Expected variables in scope:
// $results (array), $country_code, $city_code, $check_in, $check_out, $rooms, $adults, $children
?>
<div class="search-summary">
    <h1>Hotel Search Results</h1>
    <p class="search-details">
        Showing hotels in <strong><?php echo esc_html($city_code); ?></strong>
        from <strong><?php echo esc_html(date('F j, Y', strtotime($check_in))); ?></strong>
        to <strong><?php echo esc_html(date('F j, Y', strtotime($check_out))); ?></strong>
        for <strong><?php echo esc_html($adults); ?> adults</strong>
        <?php if ($children > 0): ?> and <strong><?php echo esc_html($children); ?> children</strong><?php endif; ?>
        in <strong><?php echo esc_html($rooms); ?> room(s)</strong>
    </p>
    <div class="modify-search"><a href="#" class="btn-modify js-modify-search">Modify Search</a></div>
</div>

<?php if (!empty($results['Hotels'])): ?>
    <div class="success-message"><p>Success! We found <?php echo count($results['Hotels']); ?> hotels matching your search criteria.</p></div>

    <div class="hotel-results">
        <?php foreach ($results['Hotels'] as $index => $hotel): ?>
            <?php
            // Minimal card rendering used for AJAX fragment; reuse logic from full template where possible
            $hotelImage = '';
            if (isset($hotel['HotelPicture']) && !empty($hotel['HotelPicture'])) {
                $hotelImage = $hotel['HotelPicture'];
            } elseif (isset($hotel['Images']) && !empty($hotel['Images'][0])) {
                $hotelImage = $hotel['Images'][0];
            }

            $hotelName = '';
            if (!empty($hotel['HotelName'])) { $hotelName = $hotel['HotelName']; }
            elseif (!empty($hotel['Name'])) { $hotelName = is_array($hotel['Name']) ? ($hotel['Name'][0] ?? 'Hotel') : $hotel['Name']; }
            else { $hotelName = 'Premium Hotel'; }

            // Price detection (simplified)
            $lowestPrice = null;
            if (isset($hotel['Rooms']) && is_array($hotel['Rooms'])) {
                foreach ($hotel['Rooms'] as $room) {
                    if (isset($room['TotalFare']) && is_numeric($room['TotalFare'])) {
                        $pf = $room['TotalFare'];
                        if ($lowestPrice === null || $pf < $lowestPrice) $lowestPrice = $pf;
                    }
                }
            }
            if ($lowestPrice === null) {
                if (isset($hotel['Price']['TotalAmount'])) $lowestPrice = $hotel['Price']['TotalAmount'];
                elseif (isset($hotel['MinHotelPrice'])) $lowestPrice = $hotel['MinHotelPrice'];
                else $lowestPrice = 0;
            }

            $hotelCode = $hotel['BookingCode'] ?? ($hotel['HotelCode'] ?? '');
            ?>

            <div class="hotel-card" id="hotel-<?php echo esc_attr($index); ?>">
                <div class="hotel-image"><img src="<?php echo esc_url($hotelImage ?: get_template_directory_uri() . '/assets/images/dummy-hotel.jpg'); ?>" alt="<?php echo esc_attr($hotelName); ?>" /></div>
                <div class="hotel-info">
                    <h2 class="hotel-name"><?php echo esc_html($hotelName); ?></h2>
                    <div class="hotel-location"><?php echo esc_html($hotel['CityName'] ?? ($hotel['HotelAddress'] ?? '')); ?></div>
                </div>
                <div class="hotel-price-card">
                    <div class="current-price">₹<?php echo esc_html(number_format($lowestPrice, 0)); ?></div>
                    <a class="btn-choose-room" href="<?php echo esc_url(add_query_arg(['hotel_code' => $hotelCode], site_url('/hotel-details'))); ?>">Choose Room</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-results"><p>No hotels found matching your search criteria. Please try different search parameters.</p></div>
<?php endif; ?>
