<?php
/**
 * Hotel details template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$hotel_id = $atts['id'];
$class = $atts['class'];

// Get search parameters from URL
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : date('Y-m-d', strtotime('+1 day'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;

// Initialize hotel and rooms
$hotel = null;
$rooms = array();

try {
    // Get TBO API instance
    $tbo_api = new TBO_Hotel_Booking_API();
    
    // Get hotel details
    $hotel = $tbo_api->get_hotel_details($hotel_id);
    
    // Check room availability
    $availability = $tbo_api->check_availability($hotel_id, $check_in, $check_out, $adults, $children);
    
    // Get rooms from response
    if (isset($availability['rooms'])) {
        $rooms = $availability['rooms'];
    }
} catch (Exception $e) {
    echo '<div class="tbo-error-message">' . esc_html($e->getMessage()) . '</div>';
    return;
}

// Check if hotel data is available
if (empty($hotel)) {
    echo '<div class="tbo-error-message">' . __('Hotel details not found.', 'tbo-hotel-booking') . '</div>';
    return;
}

// Get hotel data
$hotel_name = $hotel['name'];
$hotel_address = $hotel['address'];
$hotel_description = $hotel['description'];
$hotel_rating = $hotel['rating'];
$hotel_facilities = $hotel['facilities'];
$hotel_images = $hotel['images'];
?>

<div class="tbo-hotel-details <?php echo esc_attr($class); ?>">
    <div class="tbo-hotel-details-header">
        <h1 class="tbo-hotel-name"><?php echo esc_html($hotel_name); ?></h1>
        
        <div class="tbo-hotel-rating">
            <?php for ($i = 1; $i <= 5; $i++) : ?>
                <span class="tbo-star <?php echo ($i <= $hotel_rating) ? 'tbo-star-filled' : 'tbo-star-empty'; ?>"></span>
            <?php endfor; ?>
        </div>
        
        <div class="tbo-hotel-address">
            <p><?php echo esc_html($hotel_address); ?></p>
        </div>
    </div>
    
    <div class="tbo-hotel-gallery">
        <?php if (!empty($hotel_images)) : ?>
            <div class="tbo-hotel-gallery-main">
                <img src="<?php echo esc_url($hotel_images[0]); ?>" alt="<?php echo esc_attr($hotel_name); ?>" id="tbo-gallery-main-image">
            </div>
            
            <?php if (count($hotel_images) > 1) : ?>
                <div class="tbo-hotel-gallery-thumbs">
                    <?php foreach ($hotel_images as $index => $image_url) : ?>
                        <div class="tbo-gallery-thumb <?php echo ($index === 0) ? 'tbo-gallery-thumb-active' : ''; ?>" data-image="<?php echo esc_url($image_url); ?>">
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($hotel_name); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php else : ?>
            <div class="tbo-no-image"><?php _e('No images available', 'tbo-hotel-booking'); ?></div>
        <?php endif; ?>
    </div>
    
    <div class="tbo-hotel-content">
        <div class="tbo-hotel-description">
            <h2><?php _e('Description', 'tbo-hotel-booking'); ?></h2>
            <div class="tbo-description-content">
                <?php echo wpautop(esc_html($hotel_description)); ?>
            </div>
        </div>
        
        <?php if (!empty($hotel_facilities)) : ?>
            <div class="tbo-hotel-facilities">
                <h2><?php _e('Facilities', 'tbo-hotel-booking'); ?></h2>
                <ul class="tbo-facilities-list">
                    <?php foreach ($hotel_facilities as $facility) : ?>
                        <li class="tbo-facility-item"><?php echo esc_html($facility); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="tbo-hotel-rooms">
        <h2><?php _e('Available Rooms', 'tbo-hotel-booking'); ?></h2>
        
        <?php if (empty($rooms)) : ?>
            <div class="tbo-no-rooms">
                <p><?php _e('No rooms available for the selected dates.', 'tbo-hotel-booking'); ?></p>
            </div>
        <?php else : ?>
            <div class="tbo-room-list">
                <?php foreach ($rooms as $room) : 
                    // Get room data
                    $room_id = $room['id'];
                    $room_name = $room['name'];
                    $room_description = $room['description'];
                    $room_price = $room['price'];
                    $room_capacity = $room['capacity'];
                    $room_features = $room['features'];
                    $room_image = isset($room['image_url']) ? $room['image_url'] : '';
                    $room_cancellation_policy = $room['cancellation_policy'];
                ?>
                    <div class="tbo-room-item">
                        <div class="tbo-room-image">
                            <?php if (!empty($room_image)) : ?>
                                <img src="<?php echo esc_url($room_image); ?>" alt="<?php echo esc_attr($room_name); ?>">
                            <?php else : ?>
                                <div class="tbo-no-image"><?php _e('No image available', 'tbo-hotel-booking'); ?></div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="tbo-room-content">
                            <h3 class="tbo-room-name"><?php echo esc_html($room_name); ?></h3>
                            
                            <div class="tbo-room-description">
                                <?php echo wpautop(esc_html($room_description)); ?>
                            </div>
                            
                            <div class="tbo-room-capacity">
                                <span class="tbo-capacity-label"><?php _e('Capacity:', 'tbo-hotel-booking'); ?></span>
                                <span class="tbo-capacity-value"><?php echo esc_html($room_capacity); ?></span>
                            </div>
                            
                            <?php if (!empty($room_features)) : ?>
                                <div class="tbo-room-features">
                                    <h4><?php _e('Features', 'tbo-hotel-booking'); ?></h4>
                                    <ul class="tbo-features-list">
                                        <?php foreach ($room_features as $feature) : ?>
                                            <li class="tbo-feature-item"><?php echo esc_html($feature); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            
                            <div class="tbo-room-cancellation">
                                <h4><?php _e('Cancellation Policy', 'tbo-hotel-booking'); ?></h4>
                                <p><?php echo esc_html($room_cancellation_policy); ?></p>
                            </div>
                            
                            <div class="tbo-room-price">
                                <span class="tbo-price-label"><?php _e('Price:', 'tbo-hotel-booking'); ?></span>
                                <span class="tbo-price-value"><?php echo esc_html($room_price); ?></span>
                                <span class="tbo-price-period"><?php _e('per night', 'tbo-hotel-booking'); ?></span>
                            </div>
                            
                            <div class="tbo-room-actions">
                                <a href="<?php echo esc_url(site_url('/booking/?hotel_id=' . $hotel_id . '&room_id=' . $room_id . '&check_in=' . $check_in . '&check_out=' . $check_out . '&adults=' . $adults . '&children=' . $children)); ?>" class="tbo-book-now-button">
                                    <?php _e('Book Now', 'tbo-hotel-booking'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    jQuery(document).ready(function($) {
        // Gallery image switching
        $('.tbo-gallery-thumb').on('click', function() {
            var imageUrl = $(this).data('image');
            $('#tbo-gallery-main-image').attr('src', imageUrl);
            $('.tbo-gallery-thumb').removeClass('tbo-gallery-thumb-active');
            $(this).addClass('tbo-gallery-thumb-active');
        });
    });
</script>
