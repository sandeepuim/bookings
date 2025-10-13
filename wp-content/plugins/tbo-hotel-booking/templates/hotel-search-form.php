<?php
/**
 * Hotel search form template.
 *
 * @var array $atts Shortcode attributes.
 */

// Extract attributes
$title = $atts['title'];
$subtitle = $atts['subtitle'];
$class = $atts['class'];
?>

<div class="tbo-hotel-search-form <?php echo esc_attr($class); ?>">
    <div class="tbo-search-header">
        <?php if (!empty($title)) : ?>
            <h2 class="tbo-search-title"><?php echo esc_html($title); ?></h2>
        <?php endif; ?>
        
        <?php if (!empty($subtitle)) : ?>
            <p class="tbo-search-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
    </div>
    
    <form id="tbo-search-form" class="tbo-form" method="get" action="<?php echo esc_url(site_url('/hotels/')); ?>">
        <div class="tbo-form-row">
            <div class="tbo-form-field">
                <label for="tbo-destination"><?php _e('Destination', 'tbo-hotel-booking'); ?></label>
                <input type="text" id="tbo-destination" name="destination" placeholder="<?php _e('City, region or country', 'tbo-hotel-booking'); ?>" required>
            </div>
        </div>
        
        <div class="tbo-form-row">
            <div class="tbo-form-field">
                <label for="tbo-check-in"><?php _e('Check-in', 'tbo-hotel-booking'); ?></label>
                <input type="date" id="tbo-check-in" name="check_in" min="<?php echo date('Y-m-d'); ?>" required>
            </div>
            
            <div class="tbo-form-field">
                <label for="tbo-check-out"><?php _e('Check-out', 'tbo-hotel-booking'); ?></label>
                <input type="date" id="tbo-check-out" name="check_out" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
            </div>
        </div>
        
        <div class="tbo-form-row">
            <div class="tbo-form-field">
                <label for="tbo-adults"><?php _e('Adults', 'tbo-hotel-booking'); ?></label>
                <select id="tbo-adults" name="adults" required>
                    <?php for ($i = 1; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="tbo-form-field">
                <label for="tbo-children"><?php _e('Children', 'tbo-hotel-booking'); ?></label>
                <select id="tbo-children" name="children">
                    <?php for ($i = 0; $i <= 10; $i++) : ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        
        <div class="tbo-form-row">
            <div class="tbo-form-field">
                <button type="submit" class="tbo-search-button"><?php _e('Search Hotels', 'tbo-hotel-booking'); ?></button>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function($) {
        // Set minimum check-out date based on check-in date
        $('#tbo-check-in').on('change', function() {
            var checkInDate = new Date($(this).val());
            var checkOutDate = new Date(checkInDate);
            checkOutDate.setDate(checkOutDate.getDate() + 1);
            
            var minCheckOutDate = checkOutDate.toISOString().split('T')[0];
            $('#tbo-check-out').attr('min', minCheckOutDate);
            
            // If current check-out date is before new min date, update it
            if ($('#tbo-check-out').val() < minCheckOutDate) {
                $('#tbo-check-out').val(minCheckOutDate);
            }
        });
    });
</script>
