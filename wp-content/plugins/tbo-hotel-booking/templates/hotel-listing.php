<?php
/**
 * Hotel listing template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$title = $atts['title'];
$count = intval($atts['count']);
$location = $atts['location'];
$category = $atts['category'];
$facility = $atts['facility'];
$class = $atts['class'];

// Get search parameters from URL
$destination = isset($_GET['destination']) ? sanitize_text_field($_GET['destination']) : $location;
$check_in = isset($_GET['check_in']) ? sanitize_text_field($_GET['check_in']) : date('Y-m-d');
$check_out = isset($_GET['check_out']) ? sanitize_text_field($_GET['check_out']) : date('Y-m-d', strtotime('+1 day'));
$adults = isset($_GET['adults']) ? intval($_GET['adults']) : 2;
$children = isset($_GET['children']) ? intval($_GET['children']) : 0;

// Initialize hotels array
$hotels = array();

// Search for hotels if destination is provided
if (!empty($destination)) {
    try {
        // Get TBO API instance
        $tbo_api = new TBO_Hotel_Booking_API();
        
        // Search hotels
        $response = $tbo_api->search_hotels($destination, $check_in, $check_out, $adults, $children);
        
        // Get hotels from response
        if (isset($response['hotels'])) {
            $hotels = $response['hotels'];
        }
    } catch (Exception $e) {
        echo '<div class="tbo-error-message">' . esc_html($e->getMessage()) . '</div>';
    }
}
?>

<div class="tbo-hotel-listing <?php echo esc_attr($class); ?>">
    <div class="tbo-listing-header">
        <?php if (!empty($title)) : ?>
            <h2 class="tbo-listing-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <?php if (!empty($destination)) : ?>
            <p class="tbo-search-info">
                <?php printf(
                    __('Showing results for %s from %s to %s for %d adults and %d children', 'tbo-hotel-booking'),
                    '<strong>' . esc_html($destination) . '</strong>',
                    '<strong>' . date('F j, Y', strtotime($check_in)) . '</strong>',
                    '<strong>' . date('F j, Y', strtotime($check_out)) . '</strong>',
                    $adults,
                    $children
                ); ?>
            </p>
        <?php endif; ?>
    </div>
    
    <?php if (empty($hotels)) : ?>
        <div class="tbo-no-results">
            <p><?php _e('No hotels found. Please try a different search.', 'tbo-hotel-booking'); ?></p>
        </div>
    <?php else : ?>
        <div class="tbo-hotel-list">
            <?php foreach ($hotels as $index => $hotel) : 
                // Limit the number of hotels to display
                if ($index >= $count) {
                    break;
                }
                
                // Get hotel data
                $hotel_id = $hotel['id'];
                $hotel_name = $hotel['name'];
                $hotel_address = $hotel['address'];
                $hotel_rating = $hotel['rating'];
                $hotel_price = $hotel['price'];
                $hotel_image = $hotel['image_url'];
            ?>
                <div class="tbo-hotel-item">
                    <div class="tbo-hotel-image">
                        <?php if (!empty($hotel_image)) : ?>
                            <img src="<?php echo esc_url($hotel_image); ?>" alt="<?php echo esc_attr($hotel_name); ?>">
                        <?php else : ?>
                            <div class="tbo-no-image"><?php _e('No image available', 'tbo-hotel-booking'); ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="tbo-hotel-content">
                        <h3 class="tbo-hotel-name"><?php echo esc_html($hotel_name); ?></h3>
                        
                        <div class="tbo-hotel-rating">
                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                <span class="tbo-star <?php echo ($i <= $hotel_rating) ? 'tbo-star-filled' : 'tbo-star-empty'; ?>"></span>
                            <?php endfor; ?>
                        </div>
                        
                        <div class="tbo-hotel-address">
                            <p><?php echo esc_html($hotel_address); ?></p>
                        </div>
                        
                        <div class="tbo-hotel-price">
                            <span class="tbo-price-label"><?php _e('From', 'tbo-hotel-booking'); ?></span>
                            <span class="tbo-price-value"><?php echo esc_html($hotel_price); ?></span>
                            <span class="tbo-price-period"><?php _e('per night', 'tbo-hotel-booking'); ?></span>
                        </div>
                        
                        <div class="tbo-hotel-actions">
                            <a href="<?php echo esc_url(site_url('/hotel-details/?hotel_id=' . $hotel_id . '&check_in=' . $check_in . '&check_out=' . $check_out . '&adults=' . $adults . '&children=' . $children)); ?>" class="tbo-view-details-button">
                                <?php _e('View Details', 'tbo-hotel-booking'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
