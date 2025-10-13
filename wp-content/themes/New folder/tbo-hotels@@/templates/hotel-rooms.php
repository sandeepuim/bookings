<?php
/**
 * Template Name: Hotel Rooms
 * 
 * Template for displaying hotel details and room options
 *
 * @package TBO_Hotels
 */

get_header();

// Get hotel code from URL parameter
$hotel_code = isset($_GET['hotel_code']) ? sanitize_text_field($_GET['hotel_code']) : '';
$city_code = isset($_GET['city_code']) ? sanitize_text_field($_GET['city_code']) : '';
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : '';
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : '';
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;
$rooms = isset($_GET['rooms']) ? intval($_GET['rooms']) : 1;

// Format dates for display
$check_in_display = !empty($check_in) ? date('j F\' y', strtotime($check_in)) : '';
$check_out_display = !empty($check_out) ? date('j F\' y', strtotime($check_out)) : '';

// Calculate number of nights
$nights = 0;
if (!empty($check_in) && !empty($check_out)) {
    $check_in_obj = new DateTime($check_in);
    $check_out_obj = new DateTime($check_out);
    $interval = $check_in_obj->diff($check_out_obj);
    $nights = $interval->days;
}

// Initialize hotel details
$hotel_name = 'Hotel Details';
$hotel_address = '';
$hotel_rating = 0;
$hotel_images = [];

// Set default city name (will be replaced by actual data from API)
$city_name = 'City';
?>

<div class="hotel-room-page">
    <!-- Hotel Header Section -->
    <div class="hotel-header">
        <div class="container">
            <div class="breadcrumbs">
                <a href="/bookings/">Home</a> &gt; 
                <a href="/bookings/hotel-results/?city_code=<?php echo esc_attr($city_code); ?>&check_in=<?php echo esc_attr($check_in); ?>&check_out=<?php echo esc_attr($check_out); ?>&adults=<?php echo esc_attr($adults); ?>&children=<?php echo esc_attr($children); ?>&rooms=<?php echo esc_attr($rooms); ?>">Hotels in <?php echo esc_html($city_name); ?></a> &gt; 
                <span id="hotel-name-breadcrumb">Hotel Details</span>
            </div>
            
            <div class="hotel-title-section">
                <h1 id="hotel-name-title"><?php echo esc_html($hotel_name); ?></h1>
                <div class="hotel-rating" id="hotel-rating-stars">
                    <span class="star-rating"></span>
                </div>
                <p id="hotel-address" class="hotel-address"><?php echo esc_html($hotel_address); ?></p>
                
                <div class="hotel-tags">
                    <span class="hotel-tag eco">ECO+</span>
                    <span class="hotel-tag couple-friendly">COUPLE FRIENDLY</span>
                </div>
            </div>
            
            <!-- Hotel Images Gallery -->
            <div class="hotel-gallery">
                <div class="main-image" id="main-hotel-image">
                    <img src="https://source.unsplash.com/featured/800x500/?hotel,luxury" alt="Hotel Image">
                </div>
                <div class="gallery-thumbs" id="hotel-image-thumbnails">
                    <div class="thumb">
                        <img src="https://source.unsplash.com/featured/300x200/?hotel,room" alt="Hotel Room">
                    </div>
                    <div class="thumb">
                        <img src="https://source.unsplash.com/featured/300x200/?hotel,lobby" alt="Hotel Lobby">
                    </div>
                    <div class="thumb">
                        <img src="https://source.unsplash.com/featured/300x200/?hotel,pool" alt="Hotel Pool">
                    </div>
                    <div class="more-images">
                        <span>View 57+ more</span>
                    </div>
                </div>
            </div>
            
            <!-- Search Summary Bar -->
            <div class="search-summary">
                <div class="search-dates">
                    <div class="date-block">
                        <div class="date-label">Check-in</div>
                        <div class="date-value"><?php echo esc_html($check_in_display); ?></div>
                    </div>
                    <div class="date-block">
                        <div class="date-label">Check-out</div>
                        <div class="date-value"><?php echo esc_html($check_out_display); ?></div>
                    </div>
                </div>
                <div class="guest-summary">
                    <div class="guest-label">Room & Guest</div>
                    <div class="guest-value"><?php echo esc_html($rooms); ?> Room, <?php echo esc_html($adults); ?> Guests</div>
                </div>
                <div class="search-action">
                    <button class="btn-modify-search">Modify Search</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation Tabs -->
    <div class="hotel-navigation">
        <div class="container">
            <div class="nav-tabs">
                <a href="#overview" class="nav-tab active">Overview</a>
                <a href="#rooms" class="nav-tab">Rooms</a>
                <a href="#amenities" class="nav-tab">Amenities</a>
                <a href="#map" class="nav-tab">Map</a>
                <a href="#restaurant" class="nav-tab">Bars and Restaurant</a>
                <a href="#policies" class="nav-tab">Hotel Policies</a>
                <a href="#reviews" class="nav-tab">Reviews</a>
            </div>
        </div>
    </div>
    
    <!-- Overview Section -->
    <div id="overview" class="hotel-section overview-section">
        <div class="container">
            <h2 class="section-title">Overview</h2>
            <div class="overview-content">
                <p id="hotel-description" class="hotel-description">
                    Loading hotel description...
                </p>
                <div class="read-more">Read More</div>
            </div>
        </div>
    </div>
    
    <!-- Room Options Section -->
    <div id="rooms" class="hotel-section rooms-section">
        <div class="container">
            <h2 class="section-title">Available Rooms</h2>
            <div id="room-options" class="room-options">
                <!-- Room cards will be inserted here dynamically -->
                <div class="loading-container">
                    <div class="loading-spinner"></div>
                    <h3>Loading Room Options...</h3>
                    <p>Finding the best rates for your stay</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Hotel Room Page Styles */
.hotel-room-page {
    background-color: #f8f9fa;
    padding-bottom: 60px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

/* Breadcrumbs */
.breadcrumbs {
    padding: 15px 0;
    font-size: 14px;
    color: #666;
}

.breadcrumbs a {
    color: #0071c2;
    text-decoration: none;
}

.breadcrumbs a:hover {
    text-decoration: underline;
}

/* Hotel Header Section */
.hotel-header {
    background: white;
    padding-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.hotel-title-section {
    margin-bottom: 20px;
}

.hotel-title-section h1 {
    font-size: 28px;
    margin: 0 0 5px 0;
    color: #333;
}

.hotel-rating {
    margin-bottom: 5px;
}

.star-rating {
    color: #ffa726;
    font-size: 16px;
}

.hotel-address {
    font-size: 14px;
    color: #666;
    margin-bottom: 10px;
}

.hotel-tags {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
}

.hotel-tag {
    display: inline-block;
    padding: 4px 8px;
    font-size: 11px;
    font-weight: 600;
    border-radius: 4px;
}

.eco {
    background: #e8f5e8;
    color: #4caf50;
}

.couple-friendly {
    background: #fce4ec;
    color: #e91e63;
}

/* Hotel Gallery */
.hotel-gallery {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 10px;
    margin-bottom: 20px;
}

.main-image {
    height: 400px;
    overflow: hidden;
    border-radius: 8px;
}

.main-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.gallery-thumbs {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    grid-template-rows: repeat(2, 1fr);
    gap: 10px;
}

.thumb {
    border-radius: 8px;
    overflow: hidden;
    height: 195px;
}

.thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.more-images {
    background: rgba(0, 0, 0, 0.5);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    cursor: pointer;
    border-radius: 8px;
}

/* Search Summary */
.search-summary {
    background: #f8f8f8;
    padding: 15px;
    border-radius: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.search-dates {
    display: flex;
    gap: 20px;
}

.date-label, .guest-label {
    font-size: 12px;
    color: #666;
}

.date-value, .guest-value {
    font-weight: 600;
}

.btn-modify-search {
    background: #ff6b35;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 600;
}

/* Navigation Tabs */
.hotel-navigation {
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    position: sticky;
    top: 0;
    z-index: 100;
}

.nav-tabs {
    display: flex;
    overflow-x: auto;
    white-space: nowrap;
}

.nav-tab {
    display: inline-block;
    padding: 15px 20px;
    color: #333;
    text-decoration: none;
    font-weight: 500;
    border-bottom: 3px solid transparent;
}

.nav-tab.active {
    border-bottom-color: #ff6b35;
    color: #ff6b35;
}

/* Hotel Sections */
.hotel-section {
    background: white;
    padding: 30px 0;
    margin-bottom: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.section-title {
    font-size: 22px;
    color: #333;
    margin-top: 0;
    margin-bottom: 20px;
}

.overview-content {
    color: #555;
    line-height: 1.6;
}

.read-more {
    color: #0071c2;
    cursor: pointer;
    margin-top: 10px;
    display: inline-block;
}

/* Room Options */
.room-options {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.room-card {
    background: white;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    overflow: hidden;
    display: flex;
}

.room-image {
    width: 250px;
    min-width: 250px;
}

.room-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.room-details {
    padding: 20px;
    flex: 1;
}

.room-name {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 10px 0;
}

.room-amenities {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
    margin-bottom: 15px;
}

.room-amenity {
    font-size: 13px;
    color: #555;
}

.room-pricing {
    width: 250px;
    min-width: 250px;
    padding: 20px;
    background: #fafbfc;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border-left: 1px solid #e0e0e0;
    text-align: right;
}

.price-discount {
    background: #ff4444;
    color: white;
    display: inline-block;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 5px;
}

.price-original {
    text-decoration: line-through;
    color: #999;
    font-size: 14px;
}

.price-current {
    font-size: 24px;
    font-weight: 700;
    color: #333;
    margin: 5px 0;
}

.price-taxes {
    font-size: 11px;
    color: #666;
}

.btn-book-room {
    background: #ff6b35;
    color: white;
    border: none;
    padding: 12px;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    margin-top: 15px;
}

.inclusion-list {
    margin-top: 15px;
}

.inclusion-item {
    font-size: 13px;
    color: #333;
    margin-bottom: 5px;
}

.inclusion-item:before {
    content: "✓";
    color: #4caf50;
    margin-right: 5px;
}

/* Loading Container */
.loading-container {
    text-align: center;
    padding: 60px 20px;
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

/* Media Queries for Responsiveness */
@media (max-width: 992px) {
    .hotel-gallery {
        grid-template-columns: 1fr;
    }
    
    .gallery-thumbs {
        grid-template-columns: repeat(4, 1fr);
        grid-template-rows: 1fr;
    }
    
    .room-card {
        flex-direction: column;
    }
    
    .room-image {
        width: 100%;
        height: 200px;
    }
    
    .room-pricing {
        width: 100%;
        border-left: none;
        border-top: 1px solid #e0e0e0;
    }
}

@media (max-width: 576px) {
    .search-summary {
        flex-direction: column;
        align-items: flex-start;
        gap: 15px;
    }
    
    .gallery-thumbs {
        grid-template-columns: repeat(2, 1fr);
        grid-template-rows: repeat(2, 1fr);
    }
    
    .room-amenities {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Function to load hotel details
    function loadHotelDetails(hotelCode, checkIn, checkOut, adults, children, rooms) {
        console.log('Loading hotel details for hotel code:', hotelCode);
        
        // Show loading state
        $('#hotel-name-title, #hotel-name-breadcrumb').text('Loading hotel details...');
        
        // AJAX call to get hotel details
        $.ajax({
            url: tbo_hotels_params.ajax_url,
            type: 'POST',
            data: {
                action: 'tbo_hotels_get_hotel_details',
                hotel_code: hotelCode,
                city_code: '<?php echo esc_js($city_code); ?>',
                check_in: checkIn,
                check_out: checkOut,
                adults: adults,
                children: children,
                rooms: rooms,
                nonce: tbo_hotels_params.nonce
            },
            success: function(response) {
                console.log('Hotel details response:', response);
                
                if (response.success && response.data) {
                    // Update hotel info
                    updateHotelInfo(response.data);
                    
                    // Load room options
                    loadRoomOptions(hotelCode, checkIn, checkOut, adults, children, rooms);
                } else {
                    // Show error
                    $('#hotel-name-title').text('Error loading hotel details');
                    $('#hotel-description').text('Unable to load hotel information. Please try again.');
                }
            },
            error: function() {
                // Show error
                $('#hotel-name-title').text('Error loading hotel details');
                $('#hotel-description').text('Unable to connect to the server. Please try again.');
            }
        });
    }
    
    // Function to update hotel information
    function updateHotelInfo(hotelData) {
        // Basic info
        $('#hotel-name-title, #hotel-name-breadcrumb').text(hotelData.HotelName || 'Hotel Details');
        $('#hotel-address').text(hotelData.Address || '');
        
        // Update city name in breadcrumb if available
        if (hotelData.CityName) {
            // Find the city name in the breadcrumb and update it
            const breadcrumbLink = $('.breadcrumbs a').eq(1);
            const href = breadcrumbLink.attr('href');
            breadcrumbLink.text('Hotels in ' + hotelData.CityName);
        }
        
        // Description
        if (hotelData.Description) {
            $('#hotel-description').text(hotelData.Description);
        }
        
        // Star rating
        const rating = parseInt(hotelData.HotelRating) || 0;
        let starsHtml = '';
        for (let i = 0; i < 5; i++) {
            starsHtml += `<span style="color: ${i < rating ? '#ffa726' : '#ddd'}">★</span>`;
        }
        $('#hotel-rating-stars .star-rating').html(starsHtml);
        
        // Hotel images
        if (hotelData.ImageUrls && Array.isArray(hotelData.ImageUrls) && hotelData.ImageUrls.length > 0) {
            // Get the main image, handling both object and direct property access
            const mainImageObj = hotelData.ImageUrls[0];
            const mainImage = typeof mainImageObj === 'object' ? mainImageObj.ImageUrl : null;
            
            if (mainImage) {
                $('#main-hotel-image img').attr('src', mainImage);
                
                // Thumbnail images (up to 3)
                const thumbnailContainer = $('#hotel-image-thumbnails');
                thumbnailContainer.empty();
                
                const maxThumbs = Math.min(hotelData.ImageUrls.length - 1, 3);
                for (let i = 1; i <= maxThumbs; i++) {
                    if (i < hotelData.ImageUrls.length) {
                        const thumbObj = hotelData.ImageUrls[i];
                        const thumbUrl = typeof thumbObj === 'object' ? thumbObj.ImageUrl : null;
                        
                        if (thumbUrl) {
                            thumbnailContainer.append(`
                                <div class="thumb">
                                    <img src="${thumbUrl}" alt="Hotel Image">
                                </div>
                            `);
                        }
                    }
                }
                
                // Add "more images" button if there are more than 4 images
                if (hotelData.ImageUrls.length > 4) {
                    const remaining = hotelData.ImageUrls.length - 4;
                    thumbnailContainer.append(`
                        <div class="more-images">
                            <span>View ${remaining}+ more</span>
                        </div>
                    `);
                }
            }
        }
    }
    
    // Function to load room options
    function loadRoomOptions(hotelCode, checkIn, checkOut, adults, children, rooms) {
        console.log('Loading room options for hotel:', hotelCode);
        
        // Show loading state
        $('#room-options').html(`
            <div class="loading-container">
                <div class="loading-spinner"></div>
                <h3>Loading Room Options...</h3>
                <p>Finding the best rates for your stay</p>
            </div>
        `);
        
        // AJAX call to get room options
        $.ajax({
            url: tbo_hotels_params.ajax_url,
            type: 'POST',
            data: {
                action: 'tbo_hotels_get_room_rates',
                hotel_code: hotelCode,
                check_in: checkIn,
                check_out: checkOut,
                adults: adults,
                children: children,
                rooms: rooms,
                nonce: tbo_hotels_params.nonce
            },
            success: function(response) {
                console.log('Room options response:', response);
                
                if (response.success && response.data && response.data.RoomTypes) {
                    // Display room options
                    displayRoomOptions(response.data.RoomTypes);
                } else {
                    // Use sample data if API fails
                    const sampleRooms = getSampleRoomTypes();
                    displayRoomOptions(sampleRooms);
                }
            },
            error: function() {
                // Use sample data if API fails
                const sampleRooms = getSampleRoomTypes();
                displayRoomOptions(sampleRooms);
            }
        });
    }
    
    // Function to display room options
    function displayRoomOptions(roomTypes) {
        console.log('Displaying room options:', roomTypes);
        
        const roomOptionsContainer = $('#room-options');
        roomOptionsContainer.empty();
        
        if (!roomTypes || roomTypes.length === 0) {
            roomOptionsContainer.html('<p>No room options available for the selected dates.</p>');
            return;
        }
        
        // Make sure roomTypes is an array
        const roomTypesArray = Array.isArray(roomTypes) ? roomTypes : [roomTypes];
        
        // Sort room types by price (lowest first)
        roomTypesArray.sort((a, b) => {
            // Handle both array and object access patterns for backward compatibility
            const priceA = typeof a.Price === 'object' ? (a.Price?.OfferedPrice || 999999) : 999999;
            const priceB = typeof b.Price === 'object' ? (b.Price?.OfferedPrice || 999999) : 999999;
            return priceA - priceB;
        });
        
        // Display each room type
        roomTypesArray.forEach(room => {
            const roomCard = buildRoomCard(room);
            roomOptionsContainer.append(roomCard);
        });
    }
    
    // Function to build room card
    function buildRoomCard(room) {
        console.log('Building room card for:', room);
        
        // Calculate prices - handle both object and property access
        const originalPrice = typeof room.Price === 'object' ? 
            (room.Price?.OriginalPrice || Math.floor((room.Price?.OfferedPrice || 5084) * 1.22) || 6200) : 6200;
        const currentPrice = typeof room.Price === 'object' ? 
            (room.Price?.OfferedPrice || 5084) : 5084;
        const discount = Math.round(((originalPrice - currentPrice) / originalPrice) * 100);
        
        // Get room name, defaulting if not present
        const roomTypeName = room.RoomTypeName || 'Deluxe Room';
        
        // Get room image URL from API response
        let roomImage;
        
        // Check if API has provided image URLs in the response
        if (room.ImageUrls && Array.isArray(room.ImageUrls) && room.ImageUrls.length > 0) {
            // Use the first image from the API response (handle both object and direct property access)
            const imgObj = room.ImageUrls[0];
            roomImage = typeof imgObj === 'object' && imgObj.ImageUrl ? 
                        imgObj.ImageUrl : 
                        'https://source.unsplash.com/featured/600x400/?hotel,room&sig=' + room.RoomTypeCode;
        } else {
            // Fallback if API doesn't provide images
            roomImage = `https://source.unsplash.com/featured/600x400/?hotel,room&sig=${room.RoomTypeCode}`;
        }
        
        // Build inclusions list
        let inclusionsHtml = '';
        if (room.Inclusions && Array.isArray(room.Inclusions) && room.Inclusions.length > 0) {
            room.Inclusions.forEach(inclusion => {
                inclusionsHtml += `<div class="inclusion-item">${inclusion}</div>`;
            });
        } else {
            // Default inclusions
            inclusionsHtml = `
                <div class="inclusion-item">Breakfast</div>
                <div class="inclusion-item">Complimentary Wifi</div>
            `;
        }
        
        // Generate amenities
        let amenitiesHtml = '';
        if (room.Amenities && Array.isArray(room.Amenities) && room.Amenities.length > 0) {
            room.Amenities.forEach(amenity => {
                amenitiesHtml += `
                    <div class="room-amenity">
                        <span class="amenity-icon">✓</span> ${amenity}
                    </div>
                `;
            });
        } else {
            // Default amenities
            amenitiesHtml = `
                <div class="room-amenity"><span class="amenity-icon">✓</span> King/Twin Bed</div>
                <div class="room-amenity"><span class="amenity-icon">✓</span> 400 Sq.Ft</div>
                <div class="room-amenity"><span class="amenity-icon">✓</span> Attached Bathroom</div>
                <div class="room-amenity"><span class="amenity-icon">✓</span> Hot/cold Water</div>
            `;
        }
        
        // Room cancellation policy
        let cancellationPolicy = 'Free cancellation before 24 hours of check-in';
        if (room.CancellationPolicy && typeof room.CancellationPolicy === 'string') {
            cancellationPolicy = room.CancellationPolicy;
        }
        
        // Build the room card HTML
        return `
            <div class="room-card" data-room-code="${room.RoomTypeCode || 'RT001'}">
                <div class="room-image">
                    <img src="${roomImage}" alt="${roomTypeName}">
                </div>
                
                <div class="room-details">
                    <h3 class="room-name">${roomTypeName}</h3>
                    
                    <div class="room-amenities">
                        ${amenitiesHtml}
                    </div>
                    
                    <div class="inclusion-list">
                        ${inclusionsHtml}
                    </div>
                    
                    <div class="cancellation-policy">
                        <span class="policy-icon">✓</span> ${cancellationPolicy}
                    </div>
                </div>
                
                <div class="room-pricing">
                    <div>
                        <span class="price-discount">${discount}% off</span>
                        <div class="price-original">₹${originalPrice.toLocaleString()}</div>
                        <div class="price-current">₹${currentPrice.toLocaleString()}</div>
                        <div class="price-taxes">+₹${Math.round(currentPrice * 0.12).toLocaleString()} taxes & fees<br>per room per night</div>
                    </div>
                    
                    <button class="btn-book-room" data-room-code="${room.RoomTypeCode || 'RT001'}">Book Now</button>
                </div>
            </div>
        `;
    }
    
    // Function to get sample room types (for testing)
    function getSampleRoomTypes() {
        return [
            {
                RoomTypeCode: 'RT001',
                RoomTypeName: 'Deluxe Room with Breakfast',
                Inclusions: ['Breakfast', 'Complimentary Wifi'],
                Amenities: ['King/Twin Bed', '400 Sq.Ft', 'Attached Bathroom', 'Hot/cold Water'],
                Price: { OfferedPrice: 5084, OriginalPrice: 6200 },
                ImageUrls: [{ ImageUrl: 'https://source.unsplash.com/featured/600x400/?hotel,room&sig=1' }]
            },
            {
                RoomTypeCode: 'RT002',
                RoomTypeName: 'Premium Pool View with Balcony and Bathtub',
                Inclusions: ['Breakfast', 'Complimentary Wifi'],
                Amenities: ['King Bed', '565 Sq.Ft', 'Pool View', 'Wooden Flooring', 'Attached Bathroom', 'Hot/cold Water'],
                Price: { OfferedPrice: 6314, OriginalPrice: 7700 },
                ImageUrls: [{ ImageUrl: 'https://source.unsplash.com/featured/600x400/?hotel,luxury,room&sig=2' }]
            },
            {
                RoomTypeCode: 'RT003',
                RoomTypeName: 'Premium Pool View with Balcony and Breakfast + Lunch/Dinner',
                Inclusions: ['Breakfast and Dinner', 'Complimentary Wifi'],
                Amenities: ['King Bed', '565 Sq.Ft', 'Pool View', 'Wooden Flooring', 'Attached Bathroom', 'Hot/cold Water'],
                Price: { OfferedPrice: 8118, OriginalPrice: 9900 },
                ImageUrls: [{ ImageUrl: 'https://source.unsplash.com/featured/600x400/?hotel,suite&sig=3' }]
            }
        ];
    }
    
    // Handle tab navigation
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        $('.nav-tab').removeClass('active');
        $(this).addClass('active');
        
        const target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top - 70
        }, 500);
    });
    
    // Handle book now button click
    $(document).on('click', '.btn-book-room', function() {
        const roomCode = $(this).data('room-code');
        const hotelCode = '<?php echo esc_js($hotel_code); ?>';
        
        // Redirect to booking page
        window.location.href = '/bookings/hotel-booking/?hotel_code=' + hotelCode + 
                              '&room_code=' + roomCode +
                              '&check_in=<?php echo esc_js($check_in); ?>' +
                              '&check_out=<?php echo esc_js($check_out); ?>' +
                              '&adults=<?php echo esc_js($adults); ?>' +
                              '&children=<?php echo esc_js($children); ?>' +
                              '&rooms=<?php echo esc_js($rooms); ?>';
    });
    
    // Handle read more click
    $('.read-more').on('click', function() {
        const descElem = $('#hotel-description');
        if (descElem.hasClass('expanded')) {
            descElem.removeClass('expanded');
            $(this).text('Read More');
        } else {
            descElem.addClass('expanded');
            $(this).text('Read Less');
        }
    });
    
    // Initialize page
    function initPage() {
        const hotelCode = '<?php echo esc_js($hotel_code); ?>';
        const cityCode = '<?php echo esc_js($city_code); ?>';
        const checkIn = '<?php echo esc_js($check_in); ?>';
        const checkOut = '<?php echo esc_js($check_out); ?>';
        const adults = <?php echo intval($adults); ?>;
        const children = <?php echo intval($children); ?>;
        const rooms = <?php echo intval($rooms); ?>;
        
        if (hotelCode) {
            loadHotelDetails(hotelCode, checkIn, checkOut, adults, children, rooms);
        } else {
            $('#hotel-name-title').text('Hotel Details Not Found');
            $('#hotel-description').text('Please select a hotel from the search results.');
        }
    }
    
    // Start the page initialization
    initPage();
});
</script>

<?php get_footer(); ?>