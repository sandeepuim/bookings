<?php
/**
 * Template Name: Hotel Results
 * 
 * Template for displaying ONLY hotel search results
 *
 * @package TBO_Hotels
 */

get_header();

// Get search parameters from URL
$search_params = array(
    'country' => isset($_GET['country_code']) ? sanitize_text_field($_GET['country_code']) : '',
    'city' => isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '',
    'checkin' => isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '',
    'checkout' => isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '',
    'adults' => isset($_GET['adults']) ? intval($_GET['adults']) : 2,
    'children' => isset($_GET['children']) ? intval($_GET['children']) : 0,
    'rooms' => isset($_GET['rooms']) ? intval($_GET['rooms']) : 1,
);

// Get city and country names
$city_code = $search_params['city'];
$country_code = $search_params['country'];

// Hardcoded mappings for common city codes and country codes
// This avoids database queries that might fail if tables don't exist yet
$city_mappings = array(
    '105141' => 'Mount Abu',
    '149311' => 'New Delhi',
    '130443' => 'Mumbai',
    '147258' => 'Agra',
    '146940' => 'Jaipur'
);

$country_mappings = array(
    'IN' => 'India',
    'AE' => 'United Arab Emirates',
    'US' => 'United States',
    'GB' => 'United Kingdom',
    'SG' => 'Singapore'
);

// Set default names based on code
$city_name = isset($city_mappings[$city_code]) ? $city_mappings[$city_code] : "Destination #" . $city_code;
$country_name = isset($country_mappings[$country_code]) ? $country_mappings[$country_code] : "Country #" . $country_code;

// We'll skip the database queries that were causing errors
// Once you implement the actual tables, you can uncomment these sections
/*
global $wpdb;
if (!empty($city_code)) {
    // Check if table exists first to avoid errors
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}tbo_cities_cache'");
    
    if ($table_exists) {
        $cached_city = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT city_name FROM {$wpdb->prefix}tbo_cities_cache WHERE city_code = %s LIMIT 1",
                $city_code
            )
        );
        
        if ($cached_city && isset($cached_city->city_name)) {
            $city_name = $cached_city->city_name;
        }
    }
}

if (!empty($country_code)) {
    // Check if table exists first to avoid errors
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}tbo_countries_cache'");
    
    if ($table_exists) {
        $cached_country = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT country_name FROM {$wpdb->prefix}tbo_countries_cache WHERE country_code = %s LIMIT 1",
                $country_code
            )
        );
        
        if ($cached_country && isset($cached_country->country_name)) {
            $country_name = $cached_country->country_name;
        }
    }
}
*/
 
?>

<div class="hotel-results-page">
    <!-- Compact Search Header -->
    <div class="search-header-compact">
        <div class="container">
            <div class="search-form-compact">
                <div class="search-field">
                    <label>Destination</label>
                    <input type="text" value="<?php echo esc_attr($city_name); ?> (<?php echo esc_attr($country_name); ?>)" readonly>
                </div>
                <div class="search-field">
                    <label>Check-in</label>
                    <input type="date" value="<?php echo esc_attr($search_params['checkin']); ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Check-out</label>
                    <input type="date" value="<?php echo esc_attr($search_params['checkout']); ?>" readonly>
                </div>
                <div class="search-field">
                    <label>Guests</label>
                    <select disabled>
                        <option><?php echo $search_params['rooms']; ?> Room, <?php echo $search_params['adults']; ?> Adults</option>
                    </select>
                </div>
                <div class="search-field">
                    <button onclick="window.location.href='/bookings/hotel-search/'" class="btn-modify-search">Modify Search</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Results Container -->
    <div class="results-container">
        <div class="container">
            <!-- Results Header -->
            <div class="results-header">
                <div class="results-info">
                    <h2 id="results-title">Hotels in <?php echo esc_html($city_name); ?>, <?php echo esc_html($country_name); ?></h2>
                    <p id="results-subtitle">
                        <?php echo esc_html($search_params['checkin'] . ' - ' . $search_params['checkout']); ?> • 
                        <?php echo $search_params['rooms']; ?> Room, <?php echo $search_params['adults']; ?> Adults
                    </p>
                </div>
                <div class="results-sort">
                    <label>Sort by:</label>
                    <select>
                        <option>Recommended</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Guest Rating</option>
                    </select>
                </div>
            </div>

            <!-- Hotel Results -->
            <div id="hotel-results" class="hotel-results-grid">
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <h3>Searching Hotels...</h3>
                    <p>Finding the best deals for you</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* RESULTS PAGE STYLES */
.hotel-results-page {
    background: #f8f9fa;
    min-height: 100vh;
}

.search-header-compact {
    background: white;
    padding: 20px 0;
    border-bottom: 1px solid #e0e0e0;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.search-form-compact {
    display: flex;
    gap: 16px;
    align-items: end;
    flex-wrap: wrap;
}

.search-field {
    display: flex;
    flex-direction: column;
    min-width: 150px;
}

.search-field label {
    font-size: 12px;
    color: #666;
    margin-bottom: 5px;
    font-weight: 600;
    text-transform: uppercase;
}

.search-field input,
.search-field select {
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

.btn-modify-search {
    background: #ff6b35;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    height: 42px;
}

.results-container {
    padding: 30px 0;
}

.results-header {
    background: white;
    padding: 24px;
    border-radius: 12px;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.results-info h2 {
    margin: 0 0 8px 0;
    font-size: 28px;
    color: #333;
}

.results-info p {
    margin: 0;
    color: #666;
    font-size: 16px;
}

.results-sort {
    display: flex;
    align-items: center;
    gap: 12px;
}

.results-sort select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
}

/* HORIZONTAL HOTEL CARDS */
.yatra-hotel-card {
    display: flex !important;
    flex-direction: row !important;
    background: white !important;
    border: 1px solid #e0e0e0 !important;
    border-radius: 12px !important;
    margin-bottom: 20px !important;
    overflow: hidden !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    min-height: 200px !important;
    transition: transform 0.2s, box-shadow 0.2s !important;
}

.yatra-hotel-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}

.hotel-image-section {
    width: 280px !important;
    min-width: 280px !important;
    max-width: 280px !important;
    height: 200px !important;
    background: #f5f5f5 !important;
    flex-shrink: 0 !important;
}

.hotel-details-section {
    flex: 1 !important;
    padding: 20px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
}

.hotel-pricing-section {
    width: 250px !important;
    min-width: 250px !important;
    max-width: 250px !important;
    padding: 20px !important;
    border-left: 1px solid #e0e0e0 !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    justify-content: space-between !important;
    text-align: right !important;
    flex-shrink: 0 !important;
    background: #fafbfc !important;
}

.loading-container {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #ff6b35;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px auto;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}
</style>

<script>
jQuery(document).ready(function($) {
    console.log('🏨 Hotel Results Page loaded');
    
    // Parse URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const countryCode = urlParams.get('country_code');
    const cityCode = urlParams.get('city_code');
    const checkIn = urlParams.get('check_in');
    const checkOut = urlParams.get('check_out');
    const adults = urlParams.get('adults') || 2;
    const children = urlParams.get('children') || 0;
    const rooms = urlParams.get('rooms') || 1;
    
    console.log('Search Parameters:', {
        country: countryCode,
        city: cityCode,
        checkIn: checkIn,
        checkOut: checkOut,
        adults: adults,
        children: children,
        rooms: rooms
    });
    
    // Automatically fetch search results when page loads
    setTimeout(function() {
        performHotelSearch(countryCode, cityCode, checkIn, checkOut, adults, children, rooms);
    }, 1000);
    
    /**
     * Perform hotel search
     */
    function performHotelSearch(countryCode, cityCode, checkIn, checkOut, adults, children, rooms) {
        var formData = {
            action: 'tbo_hotels_search_hotels',
            country_code: countryCode,
            city_code: cityCode,
            check_in: checkIn,
            check_out: checkOut,
            rooms: rooms,
            adults: adults,
            children: children,
            nonce: tbo_hotels_params.nonce
        };
        
        console.log('🔍 Sending hotel search request:', formData);
        
        // Show loading animation
        $('#hotel-results').html(`
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <h3>Searching Hotels...</h3>
                <p>Finding the best deals for you</p>
            </div>
        `);
        
        $.ajax({
            url: tbo_hotels_params.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('✅ Hotel search response:', response);
                
                if (response.success && response.data) {
                    let hotels = response.data.Hotels || [];
                    
                    if (hotels.length > 0) {
                        // Log detailed information about the first hotel to see the structure
                        console.log('First hotel data structure:', hotels[0]);
                        
                        // Check specifically for image data
                        if (hotels[0].ImageUrls && hotels[0].ImageUrls.length > 0) {
                            console.log('✓ Hotel images found in API response:', 
                                        hotels[0].ImageUrls.length + ' images for ' + hotels[0].HotelName);
                            console.log('First image URL:', hotels[0].ImageUrls[0].ImageUrl);
                        } else {
                            console.log('❌ No images found in hotel data. Using fallback images.');
                        }
                        
                        displayHotelResults(hotels);
                    } else {
                        // Fallback to sample hotels if API returns empty
                        console.log('API returned no hotels, using sample data for demonstration');
                        const sampleHotels = getSampleHotels(cityCode);
                        //displayHotelResults(sampleHotels);
                    }
                } else {
                    console.log('API call unsuccessful, using sample data for demonstration');
                    const sampleHotels = getSampleHotels(cityCode);
                   // displayHotelResults(sampleHotels);
                }
            },
            error: function(xhr, status, error) {
                console.error('❌ Error searching for hotels:', error);
                
                // Fallback to sample data for demonstration
                console.log('Using sample hotels data for demonstration');
                const sampleHotels = getSampleHotels(cityCode);
               // displayHotelResults(sampleHotels);
            }
        });
    }
    
    /**
     * Display hotel results
     */
    function displayHotelResults(hotels) {
        console.log('Displaying hotel results:', hotels);
        
        if (!hotels || hotels.length === 0) {
            $('#hotel-results').html(
                '<div style="text-align: center; padding: 60px 20px; background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">' +
                    '<h3>No hotels found</h3>' +
                    '<p>Please try a different search or modify your criteria.</p>' +
                    '<button onclick="window.location.href=\'/bookings/hotel-search/\'" class="btn-modify-search" style="margin-top: 20px;">Modify Search</button>' +
                '</div>'
            );
            return;
        }
        
        let html = '';
        
        hotels.forEach(function(hotel) {
            html += buildHotelCard(hotel);
        });
        
        $('#hotel-results').html(html);
        
        // Force horizontal layout on all hotel cards
        forceHorizontalLayout();
    }
    
    /**
     * Build a hotel card HTML
     */
    function buildHotelCard(hotel) {
    // Debug: Show full hotel object to inspect API fields
    console.log('Hotel object:', hotel);
    // Get price from first room in Rooms array
    let currentPrice = 'N/A';
    let originalPrice = 'N/A';
    let currency = hotel.Currency || 'INR';
    if (hotel.Rooms && hotel.Rooms.length > 0) {
        currentPrice = hotel.Rooms[0].TotalFare || hotel.Rooms[0].DayRates?.[0]?.[0]?.BasePrice || 'N/A';
        originalPrice = hotel.Rooms[0].DayRates?.[0]?.[0]?.BasePrice || currentPrice;
        currency = hotel.Currency || 'INR';
    }
    const rating = hotel.StarRating || hotel.HotelRating || 3;
    const reviews = hotel.ReviewCount || hotel.Reviews || 'N/A';
        
        // Get hotel image URL from API response
        let hotelImage;
        
        // Check if API has provided image URLs in the response
        if (hotel.ImageUrls && hotel.ImageUrls.length > 0 && hotel.ImageUrls[0].ImageUrl) {
            // Use the first image from the API response
            hotelImage = hotel.ImageUrls[0].ImageUrl;
            console.log('Using API image for hotel:', hotel.HotelName, hotelImage);
        } else {
            // Fallback if API doesn't provide images
            const imageId = parseInt(hotel.HotelCode.replace(/\D/g, '')) % 10 + 1;
            hotelImage = `https://source.unsplash.com/featured/300x200/?luxury,hotel&sig=${hotel.HotelCode}`;
            console.log('Using fallback image for hotel:', hotel.HotelName);
        }
        
        return `
            <div class="yatra-hotel-card" data-hotel-code="${hotel.HotelCode}">
                <div class="hotel-image-section">
                    <div style="width: 100%; height: 100%; background-size: cover; background-position: center; background-color: #f5f5f5; background-image: url('${hotelImage}');">
                    </div>
                </div>
                
                <div class="hotel-details-section">
                    <div>
                        <h3 style="font-size: 20px; font-weight: 600; color: #333; margin: 0 0 8px 0;">${hotel.HotelName}</h3>
                        <div style="display: flex; margin-bottom: 12px;">
                            ${generateStars(hotel.StarRating)}
                        </div>
                        <p style="color: #666; font-size: 14px; margin-bottom: 16px;">📍 ${hotel.HotelAddress || hotel.Address || 'Address information not available'}</p>
                        
                        <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                            <span style="background: #e8f5e8; color: #4caf50; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">ECO+</span>
                            <span style="background: #fce4ec; color: #e91e63; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">COUPLE FRIENDLY</span>
                        </div>
                        
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; color: #666; font-size: 13px;">
                            <div>✓ Free Breakfast</div>
                            <div>✓ Free WiFi</div>
                            <div>✓ Swimming Pool</div>
                            <div>✓ Spa Services</div>
                        </div>
                    </div>
                    
                    <div style="background: #e8f5e8; color: #4caf50; padding: 8px 12px; border-radius: 4px; font-size: 12px; font-weight: 600; margin-top: 16px;">
                        🔓 Unlock 18% Off every night - Limited Time Offer!
                    </div>
                </div>
                
                <div class="hotel-pricing-section">
                    <div>
                        <div style="margin-bottom: 16px;">
                            <div style="display: flex; align-items: center; justify-content: flex-end; margin-bottom: 4px;">
                                <span style="background: #4caf50; color: white; padding: 4px 8px; border-radius: 4px; font-weight: 600; font-size: 12px; margin-right: 8px;">Exceptional</span>
                                <span style="background: #4caf50; color: white; padding: 6px 10px; border-radius: 4px; font-weight: 600; font-size: 16px;">${rating}</span>
                            </div>
                            <div style="font-size: 12px; color: #666;">${reviews} reviews</div>
                        </div>
                        
                        <div style="color: #ff6b35; font-size: 12px; font-weight: 500; margin-bottom: 16px;">
                            ${Math.floor(Math.random() * 8) + 2} rooms left
                        </div>
                    </div>
                    
                    <div>
                        <div style="margin-bottom: 20px;">
                            <div style="background: #ff4444; color: white; padding: 3px 6px; border-radius: 3px; font-size: 11px; font-weight: 600; margin-bottom: 6px; display: inline-block;">18% off</div>
                            <div style="text-decoration: line-through; color: #999; font-size: 14px; margin-bottom: 4px;">${currency} ${originalPrice !== 'N/A' ? Number(originalPrice).toLocaleString() : 'N/A'}</div>
                            <div style="font-size: 24px; font-weight: 700; color: #333; margin-bottom: 4px;">${currency} ${currentPrice !== 'N/A' ? Number(currentPrice).toLocaleString() : 'N/A'}</div>
                            <div style="font-size: 11px; color: #666;">+ taxes & fees<br>per room per night</div>
                        </div>
                        
                                <a href="/bookings/hotel-details/?hotel_code=${hotel.HotelCode}&country_code=${encodeURIComponent(countryCode)}&city_code=${encodeURIComponent(cityCode)}&check_in=${encodeURIComponent(checkIn)}&check_out=${encodeURIComponent(checkOut)}&rooms=${encodeURIComponent(rooms)}&adults=${encodeURIComponent(adults)}&children=${encodeURIComponent(children)}" style="background: #ff6b35; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-size: 14px; font-weight: 600; cursor: pointer; width: 100%; display: inline-block; text-align: center; text-decoration: none;">
                                    Choose Room
                                </a>
                    </div>
                </div>
            </div>
        `;
    }
    
    /**
     * Generate star rating HTML
     */
    function generateStars(rating) {
        let stars = '';
        rating = parseInt(rating) || 3;
        
        for (let i = 1; i <= 5; i++) {
            const color = i <= rating ? '#ffa726' : '#ddd';
            stars += `<span style="color: ${color}; font-size: 16px;">★</span>`;
        }
        return stars;
    }
    
    /**
     * Get sample hotels for demonstration when API fails
     */
    function getSampleHotels(cityCode) {
        // Create city-specific sample hotels
        const cityName = "<?php echo esc_js($city_name); ?>";
        const countryName = "<?php echo esc_js($country_name); ?>";
        
        // Hotel names based on city code to keep them consistent
        let hotelNames = [];
        
        if (cityCode === '105141') {
            // Mount Abu
            hotelNames = [
                'Hilltone Resort & Spa',
                'The Fern Ratan Villas',
                'Hotel Mount Regency',
                'Hotel Sunset Inn',
                'Cama Rajputana Club Resort'
            ];
        } else if (cityCode === '149311') {
            // New Delhi
            hotelNames = [
                'The Imperial New Delhi',
                'Taj Palace',
                'The Leela Palace',
                'ITC Maurya Luxury Collection',
                'JW Marriott Hotel New Delhi Aerocity'
            ];
        } else {
            // Generic names for other cities
            hotelNames = [
                cityName + ' Grand Hotel & Resort',
                'Taj ' + cityName + ' Palace',
                cityName + ' Garden Resort & Spa',
                'Hotel ' + cityName + ' Plaza',
                cityName + ' Homestay'
            ];
        }
        
        // Create hotel objects with consistent data that match the TBO API structure
        return [
            {
                HotelCode: 'HTL' + cityCode + '001',
                HotelName: hotelNames[0],
                Address: 'Near City Center, ' + cityName + ', ' + countryName,
                HotelRating: 5,
                GeoCode: { Latitude: '26.5921', Longitude: '78.9321' },
                HotelCategory: 'Resort',
                Price: { OfferedPrice: 7520, Currency: 'INR' },
                ImageUrls: [
                    { ImageUrl: 'https://source.unsplash.com/featured/600x400/?hotel,resort&sig=1' + cityCode + '001' }
                ]
            },
            {
                HotelCode: 'HTL' + cityCode + '002',
                HotelName: hotelNames[1],
                Address: 'Palace Road, ' + cityName + ' District, ' + countryName,
                HotelRating: 5,
                GeoCode: { Latitude: '26.5990', Longitude: '78.9420' },
                HotelCategory: 'Luxury',
                Price: { OfferedPrice: 9200, Currency: 'INR' },
                ImageUrls: [
                    { ImageUrl: 'https://source.unsplash.com/featured/600x400/?luxury,hotel&sig=1' + cityCode + '002' }
                ]
            },
            {
                HotelCode: 'HTL' + cityCode + '003',
                HotelName: hotelNames[2],
                Address: 'Highway 21, Outskirts of ' + cityName + ', ' + countryName,
                HotelRating: 4,
                GeoCode: { Latitude: '26.6010', Longitude: '78.9510' },
                HotelCategory: 'Business',
                Price: { OfferedPrice: 5840, Currency: 'INR' },
                ImageUrls: [
                    { ImageUrl: 'https://source.unsplash.com/featured/600x400/?resort,pool&sig=1' + cityCode + '003' }
                ]
            },
            {
                HotelCode: 'HTL' + cityCode + '004',
                HotelName: hotelNames[3],
                Address: 'Central Plaza, ' + cityName + ', ' + countryName,
                HotelRating: 3,
                GeoCode: { Latitude: '26.5850', Longitude: '78.9370' },
                HotelCategory: 'Budget',
                Price: { OfferedPrice: 4320, Currency: 'INR' },
                ImageUrls: [
                    { ImageUrl: 'https://source.unsplash.com/featured/600x400/?hotel,budget&sig=1' + cityCode + '004' }
                ]
            },
            {
                HotelCode: 'HTL' + cityCode + '005',
                HotelName: hotelNames[4],
                Address: 'Residential Area, ' + cityName + ', ' + countryName,
                HotelRating: 3,
                GeoCode: { Latitude: '26.5880', Longitude: '78.9290' },
                HotelCategory: 'Homestay',
                Price: { OfferedPrice: 3960, Currency: 'INR' },
                ImageUrls: [
                    { ImageUrl: 'https://source.unsplash.com/featured/600x400/?homestay,apartment&sig=1' + cityCode + '005' }
                ]
            }
        ];
    }
    
    /**
     * Force horizontal layout for hotel cards
     */
    function forceHorizontalLayout() {
        console.log('Enforcing horizontal layout for hotel cards');
        
        $('.yatra-hotel-card').each(function() {
            $(this).css({
                'display': 'flex',
                'flex-direction': 'row'
            });
        });
    }
});
</script>

<?php get_footer(); ?>