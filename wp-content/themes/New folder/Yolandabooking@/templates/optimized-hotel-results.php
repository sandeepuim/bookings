<?php
/**
 * Template Name: Optimized Hotel Results
 */

// Set execution time limit
set_time_limit(30);

// Include the optimized API client
require_once get_template_directory() . '/inc/OptimizedTboApiClient.php';

// Initialize the API client
$api = new OptimizedTboApiClient();

// Get search parameters
$params = [
    'country_code' => $_GET['country_code'] ?? '',
    'city_code' => $_GET['city_code'] ?? '',
    'check_in' => $_GET['check_in'] ?? '',
    'check_out' => $_GET['check_out'] ?? '',
    'rooms' => intval($_GET['rooms'] ?? 1),
    'adults' => intval($_GET['adults'] ?? 1),
    'children' => intval($_GET['children'] ?? 0),
    'hotel_codes' => $_GET['hotel_codes'] ?? null
];

// Validate required parameters
$required = ['country_code', 'city_code', 'check_in', 'check_out'];
$missing = array_filter($required, function($param) use ($params) {
    return empty($params[$param]);
});

if (!empty($missing)) {
    wp_die('Missing required parameters: ' . implode(', ', $missing));
}

try {
    // Show loading state
    echo '<div id="hotel-search-loader" class="loading-overlay active">
            <div class="loading-spinner"></div>
            <div class="loading-text">Searching for the best hotel deals...</div>
          </div>';
    
    // Flush output buffer to show loader
    flush();
    ob_flush();

    // Perform the search
    $results = $api->searchHotels($params);

    // Hide loader
    echo '<script>document.getElementById("hotel-search-loader").style.display = "none";</script>';

    if (empty($results['Hotels'])) {
        echo '<div class="no-results">No hotels found for your search criteria.</div>';
        return;
    }

    // Display results
    foreach ($results['Hotels'] as $hotel): ?>
        <div class="hotel-card" data-hotel-code="<?php echo esc_attr($hotel['HotelCode']); ?>">
            <div class="hotel-image">
                <?php if (!empty($hotel['Images'])): ?>
                    <img src="<?php echo esc_url($hotel['Images'][0]); ?>" alt="<?php echo esc_attr($hotel['HotelName']); ?>">
                <?php else: ?>
                    <img src="<?php echo get_template_directory_uri(); ?>/assets/images/no-image.jpg" alt="No image available">
                <?php endif; ?>
            </div>

            <div class="hotel-info">
                <h2 class="hotel-name"><?php echo esc_html($hotel['HotelName']); ?></h2>
                
                <?php if (!empty($hotel['Address'])): ?>
                    <div class="hotel-address"><?php echo esc_html($hotel['Address']); ?></div>
                <?php endif; ?>

                <?php if (!empty($hotel['Facilities'])): ?>
                    <div class="hotel-facilities">
                        <?php foreach (array_slice($hotel['Facilities'], 0, 5) as $facility): ?>
                            <span class="facility-badge"><?php echo esc_html($facility); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="hotel-pricing">
                <?php if (!empty($hotel['CheapestRoom'])): ?>
                    <div class="price-from">
                        <span class="price-label">From</span>
                        <span class="price-amount">₹<?php echo number_format($hotel['CheapestRoom']['TotalFare']); ?></span>
                        <span class="price-night">per night</span>
                    </div>
                    
                    <button class="choose-room-btn" 
                            onclick="showRoomDetails('<?php echo esc_js($hotel['HotelCode']); ?>')">
                        Choose Room
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach;

    // Add room selection modal
    ?>
    <div id="room-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="room-details-content"></div>
        </div>
    </div>

    <script>
    function showRoomDetails(hotelCode) {
        const hotel = <?php echo json_encode($results['Hotels']); ?>.find(h => h.HotelCode === hotelCode);
        if (!hotel || !hotel.Rooms) return;

        const modalContent = document.getElementById('room-details-content');
        modalContent.innerHTML = `
            <h2>${hotel.HotelName}</h2>
            <div class="room-list">
                ${hotel.Rooms.map(room => `
                    <div class="room-option">
                        <h3>${room.RoomTypeName}</h3>
                        <div class="room-price">₹${room.TotalFare.toLocaleString()}</div>
                        <button onclick="bookRoom('${hotelCode}', '${room.RoomTypeCode}')">
                            Book Now
                        </button>
                    </div>
                `).join('')}
            </div>
        `;

        document.getElementById('room-modal').style.display = 'block';
    }

    // Close modal when clicking the X or outside the modal
    document.querySelector('.close').onclick = function() {
        document.getElementById('room-modal').style.display = 'none';
    }

    window.onclick = function(event) {
        const modal = document.getElementById('room-modal');
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }

    function bookRoom(hotelCode, roomTypeCode) {
        // Add your booking logic here
        console.log(`Booking room ${roomTypeCode} in hotel ${hotelCode}`);
    }
    </script>

    <?php
} catch (Exception $e) {
    error_log("Hotel search error: " . $e->getMessage());
    echo '<div class="error-message">Sorry, we encountered an error while searching for hotels. Please try again.</div>';
}
