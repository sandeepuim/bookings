<?php
/**
 * Template Name: Hotel Search
 * 
 * Template for displaying the hotel search form and results
 *
 * @package TBO_Hotels
 */

// Get the header
get_header();
?>

<div class="hotel-search-wrapper">
    <div class="container">
        <div class="hotel-search-container">
            <h1 class="search-title"><?php esc_html_e('Find Your Perfect Hotel', 'tbo-hotels'); ?></h1>
            
            <div class="search-form-container">
                <form id="hotel-search-form" class="hotel-search-form" method="post">
                    <div class="single-row-form">
                        <div class="form-group location-group">
                            <label for="city_code"><?php esc_html_e('Select City, Location or Hotel Name', 'tbo-hotels'); ?></label>
                            <div class="location-selector">
                                <select id="country_code" name="country_code" required class="country-select">
                                    <option value=""><?php esc_html_e('Select Country', 'tbo-hotels'); ?></option>
                                </select>
                                <select id="city_code" name="city_code" required class="city-select">
                                    <option value=""><?php esc_html_e('Select City', 'tbo-hotels'); ?></option>
                                </select>
                                <div class="location-info">
                                    <span class="country-name">India</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group date-group">
                            <label for="check_in"><?php esc_html_e('Check-in Date', 'tbo-hotels'); ?></label>
                            <input type="date" id="check_in" name="check_in" required class="date-input">
                            <div class="date-display" onclick="document.getElementById('check_in').showPicker()">
                                <span class="date-num">15</span>
                                <span class="date-text">Sep' 25<br>Monday</span>
                            </div>
                        </div>
                        
                        <div class="form-group date-group">
                            <label for="check_out"><?php esc_html_e('Check-out Date', 'tbo-hotels'); ?></label>
                            <input type="date" id="check_out" name="check_out" required class="date-input">
                            <div class="date-display" onclick="document.getElementById('check_out').showPicker()">
                                <span class="date-num">16</span>
                                <span class="date-text">Sep' 25<br>Tuesday</span>
                            </div>
                        </div>
                        
                        <div class="form-group guests-group">
                            <label for="rooms"><?php esc_html_e('Room & Guest', 'tbo-hotels'); ?></label>
                            <div class="guests-selector">
                                <div class="guests-display" onclick="toggleGuestPanel(event)">
                                    <span class="room-count">1</span> Room, 
                                    <span class="guest-count">2</span> Guests
                                    <div class="guest-details">2 Adults</div>
                                </div>
                                <div class="guests-inputs" id="guest-panel">
                                    <div class="guest-row">
                                        <label>Rooms:</label>
                                        <select id="rooms" name="rooms" onchange="updateGuestsDisplay()" onclick="event.stopPropagation()">
                                            <?php for ($i = 1; $i <= 5; $i++) : ?>
                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="guest-row">
                                        <label>Adults:</label>
                                        <select id="adults" name="adults" onchange="updateGuestsDisplay()" onclick="event.stopPropagation()">
                                            <?php for ($i = 1; $i <= 6; $i++) : ?>
                                                <option value="<?php echo esc_attr($i); ?>" <?php selected($i, 2); ?>><?php echo esc_html($i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="guest-row">
                                        <label>Children:</label>
                                        <select id="children" name="children" onchange="updateGuestsDisplay()" onclick="event.stopPropagation()">
                                            <?php for ($i = 0; $i <= 4; $i++) : ?>
                                                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group search-group">
                            <button type="submit" class="search-button-inline">
                                <?php esc_html_e('Search', 'tbo-hotels'); ?>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <div id="search-results" class="hotel-search-results">
            <!-- Results will be loaded here via AJAX -->
        </div>
    </div>
</div>

<!-- DESKTOP PROFESSIONAL LAYOUT - NO RESPONSIVE -->
<style>
/* DESKTOP ONLY: Professional hotel card layout optimized for 1200px+ screens */
.yatra-hotel-card,
.hotel-search-results .yatra-hotel-card,
.hotels-grid .yatra-hotel-card,
div.yatra-hotel-card,
[data-hotel-code] {
    display: flex !important;
    flex-direction: row !important;
    width: 100% !important;
    max-width: 1150px !important;
    margin: 0 auto 24px auto !important;
    border: 1px solid #e6e6e6 !important;
    border-radius: 12px !important;
    background: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
    overflow: hidden !important;
    transition: box-shadow 0.3s ease !important;
    min-height: 200px !important;
}

.yatra-hotel-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
}

/* Left Section: Hotel Image (300px fixed) */
.yatra-hotel-card .hotel-image-section {
    width: 300px !important;
    min-width: 300px !important;
    max-width: 300px !important;
    height: 200px !important;
    position: relative !important;
    background: #f8f9fa !important;
    overflow: hidden !important;
    flex-shrink: 0 !important;
}

.yatra-hotel-card .hotel-image-section img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}

/* Middle Section: Hotel Details (flexible width) */
.yatra-hotel-card .hotel-details-section {
    flex: 1 !important;
    padding: 24px !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    background: #ffffff !important;
    min-width: 400px !important;
}

.yatra-hotel-card .hotel-name {
    font-size: 24px !important;
    font-weight: 600 !important;
    color: #1a1a1a !important;
    margin: 0 0 8px 0 !important;
    line-height: 1.3 !important;
}

.yatra-hotel-card .hotel-location {
    color: #666666 !important;
    font-size: 14px !important;
    margin-bottom: 16px !important;
}

.yatra-hotel-card .hotel-badges {
    display: flex !important;
    gap: 8px !important;
    margin-bottom: 16px !important;
}

.yatra-hotel-card .badge {
    padding: 4px 8px !important;
    border-radius: 4px !important;
    font-size: 11px !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
}

.eco-badge { background: #e8f5e8 !important; color: #2d7d32 !important; }
.couple-badge { background: #fce4ec !important; color: #c2185b !important; }
.wifi-badge { background: #e3f2fd !important; color: #1976d2 !important; }

/* Right Section: Pricing (280px fixed) */
.yatra-hotel-card .hotel-pricing-section {
    width: 280px !important;
    min-width: 280px !important;
    max-width: 280px !important;
    padding: 24px !important;
    background: #fafbfc !important;
    border-left: 1px solid #f0f0f0 !important;
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    align-items: flex-end !important;
    text-align: right !important;
    flex-shrink: 0 !important;
}

.yatra-hotel-card .user-rating {
    margin-bottom: 16px !important;
}

.yatra-hotel-card .rating-badge {
    background: #4caf50 !important;
    color: white !important;
    padding: 4px 8px !important;
    border-radius: 4px !important;
    font-size: 12px !important;
    font-weight: 600 !important;
}

.yatra-hotel-card .rating-score {
    font-size: 24px !important;
    font-weight: 700 !important;
    color: #1a1a1a !important;
    margin: 8px 0 4px 0 !important;
}

.yatra-hotel-card .current-price {
    font-size: 28px !important;
    font-weight: 700 !important;
    color: #1a1a1a !important;
    margin-bottom: 4px !important;
}

.yatra-hotel-card .original-price {
    font-size: 16px !important;
    color: #999999 !important;
    text-decoration: line-through !important;
    margin-bottom: 8px !important;
}

.yatra-hotel-card .choose-room-btn {
    background: #ff6b35 !important;
    color: white !important;
    border: none !important;
    padding: 12px 24px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    font-size: 14px !important;
    margin-top: 16px !important;
    transition: background 0.3s ease !important;
}

.yatra-hotel-card .choose-room-btn:hover {
    background: #e55a2b !important;
}

/* Remove debug borders and content */
.debug-content {
    display: none !important;
}

/* Force image section width */
.yatra-hotel-card .hotel-image-section,
.hotel-search-results .yatra-hotel-card .hotel-image-section,
.hotels-grid .yatra-hotel-card .hotel-image-section,
div.yatra-hotel-card div.hotel-image-section {
    width: 280px !important;
    min-width: 280px !important;
    max-width: 280px !important;
    flex-shrink: 0 !important;
    flex-grow: 0 !important;
}

/* Force pricing section width */
.yatra-hotel-card .hotel-pricing-section,
.hotel-search-results .yatra-hotel-card .hotel-pricing-section,
.hotels-grid .yatra-hotel-card .hotel-pricing-section,
div.yatra-hotel-card div.hotel-pricing-section {
    width: 220px !important;
    min-width: 220px !important;
    max-width: 220px !important;
    flex-shrink: 0 !important;
    flex-grow: 0 !important;
}

/* Ensure details section takes remaining space */
.yatra-hotel-card .hotel-details-section,
.hotel-search-results .yatra-hotel-card .hotel-details-section,
.hotels-grid .yatra-hotel-card .hotel-details-section,
div.yatra-hotel-card div.hotel-details-section {
    flex: 1 !important;
    flex-grow: 1 !important;
    flex-shrink: 1 !important;
}
</style>

<script>
// Global functions for form interaction
function toggleGuestPanel(event) {
    if (event) {
        event.stopPropagation();
    }
    
    console.log('toggleGuestPanel called');
    
    var panel = document.getElementById('guest-panel');
    if (!panel) {
        console.error('Guest panel not found');
        return;
    }
    
    var isVisible = panel.classList.contains('show');
    
    if (isVisible) {
        panel.classList.remove('show');
        console.log('Panel hidden');
    } else {
        panel.classList.add('show');
        console.log('Panel shown');
    }
}

function updateGuestsDisplay() {
    console.log('updateGuestsDisplay called');
    
    var roomsSelect = document.getElementById('rooms');
    var adultsSelect = document.getElementById('adults');
    var childrenSelect = document.getElementById('children');
    
    if (!roomsSelect || !adultsSelect || !childrenSelect) {
        console.error('One or more select elements not found');
        return;
    }
    
    var rooms = roomsSelect.value || 1;
    var adults = adultsSelect.value || 2;
    var children = childrenSelect.value || 0;
    
    console.log('Values:', { rooms, adults, children });
    
    var guestTotal = parseInt(adults) + parseInt(children);
    
    var roomCountElement = document.querySelector('.room-count');
    var guestCountElement = document.querySelector('.guest-count');
    var guestDetailsElement = document.querySelector('.guest-details');
    
    if (roomCountElement) roomCountElement.textContent = rooms;
    if (guestCountElement) guestCountElement.textContent = guestTotal;
    
    var detailText = adults + ' Adult' + (adults > 1 ? 's' : '');
    if (children > 0) {
        detailText += ', ' + children + ' Child' + (children > 1 ? 'ren' : '');
    }
    
    if (guestDetailsElement) {
        guestDetailsElement.textContent = detailText;
    }
    
    console.log('Display updated:', detailText);
}

// Close guest panel when clicking outside
document.addEventListener('click', function(event) {
    var guestPanel = document.getElementById('guest-panel');
    var guestGroup = document.querySelector('.guests-group');
    
    if (guestPanel && guestGroup && !guestGroup.contains(event.target)) {
        guestPanel.classList.remove('show');
    }
});

// Prevent panel from closing when clicking inside
document.addEventListener('DOMContentLoaded', function() {
    var guestPanel = document.getElementById('guest-panel');
    if (guestPanel) {
        guestPanel.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    }
    
    // Initialize guest display
    updateGuestsDisplay();
});

// FORCE HORIZONTAL LAYOUT WITH JAVASCRIPT
function forceHorizontalLayout() {
    console.log('🔧 EMERGENCY: Forcing horizontal layout...');
    console.log('📍 Current page URL:', window.location.href);
    
    // Check if we're on the results page
    var searchResults = document.getElementById('search-results');
    if (!searchResults) {
        console.log('❌ No search-results container found!');
        return;
    }
    
    console.log('✅ Found search-results container');
    
    // Find all hotel cards
    var hotelCards = document.querySelectorAll('.yatra-hotel-card, [data-hotel-code]');
    console.log('📊 Found hotel cards:', hotelCards.length);
    
    if (hotelCards.length === 0) {
        console.log('❌ No hotel cards found! Looking for any div with hotel in class name...');
        var anyHotelDivs = document.querySelectorAll('div[class*="hotel"]');
        console.log('🔍 Found divs with "hotel" in class:', anyHotelDivs.length);
        anyHotelDivs.forEach(function(div, i) {
            console.log('📝 Hotel div', i, ':', div.className);
        });
        return;
    }
    
    hotelCards.forEach(function(card, index) {
        console.log('🏨 Processing card', index + 1, 'with classes:', card.className);
        
        // Apply clean desktop layout
        card.style.setProperty('display', 'flex', 'important');
        card.style.setProperty('flex-direction', 'row', 'important');
        card.style.setProperty('width', '100%', 'important');
        card.style.setProperty('max-width', '1150px', 'important');
        card.style.setProperty('margin', '0 auto 24px auto', 'important');
        
        // Clean up any debug borders
        card.style.setProperty('border', '1px solid #e6e6e6', 'important');
        
        // Force image section
        var imageSection = card.querySelector('.hotel-image-section');
        if (imageSection) {
            imageSection.style.setProperty('width', '300px', 'important');
            imageSection.style.setProperty('min-width', '300px', 'important');
            imageSection.style.setProperty('height', '200px', 'important');
            imageSection.style.setProperty('border', 'none', 'important');
            console.log('✅ Fixed image section for card', index + 1);
        }
        
        // Force pricing section
        var pricingSection = card.querySelector('.hotel-pricing-section');
        if (pricingSection) {
            pricingSection.style.setProperty('width', '280px', 'important');
            pricingSection.style.setProperty('min-width', '280px', 'important');
            pricingSection.style.setProperty('border', 'none', 'important');
            pricingSection.style.setProperty('border-left', '1px solid #f0f0f0', 'important');
            console.log('✅ Fixed pricing section for card', index + 1);
        }
        
        // Clean details section
        var detailsSection = card.querySelector('.hotel-details-section');
        if (detailsSection) {
            detailsSection.style.setProperty('flex', '1', 'important');
            detailsSection.style.setProperty('border', 'none', 'important');
            console.log('✅ Fixed details section for card', index + 1);
        }
        
        console.log('🎯 Card', index + 1, 'layout forced successfully');
    });
    
    console.log('🚀 Horizontal layout force completed for', hotelCards.length, 'cards');
}

// EMERGENCY: Run layout fix immediately when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 DOM loaded, running emergency layout fix...');
    forceHorizontalLayout();
    
    // Also run after a short delay
    setTimeout(function() {
        console.log('🎯 Running delayed layout fix...');
        forceHorizontalLayout();
    }, 1000);
    
    // And run periodically to catch any dynamically loaded content
    setInterval(function() {
        var hotelCards = document.querySelectorAll('.yatra-hotel-card, [data-hotel-code]');
        if (hotelCards.length > 0) {
            console.log('🎯 Periodic layout check - found', hotelCards.length, 'cards');
            forceHorizontalLayout();
        }
    }, 2000);
});

// Monitor for new hotel cards being added via AJAX
var searchResultsObserver = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            console.log('🔄 New content detected, applying horizontal layout...');
            setTimeout(forceHorizontalLayout, 100); // Small delay to ensure content is rendered
        }
    });
});

// Start observing search results container
document.addEventListener('DOMContentLoaded', function() {
    var searchResults = document.getElementById('search-results');
    if (searchResults) {
        searchResultsObserver.observe(searchResults, {
            childList: true,
            subtree: true
        });
        console.log('Started observing search results for changes');
    }
    
    // Also apply layout immediately if cards already exist
    setTimeout(forceHorizontalLayout, 500);
});
</script>

<?php
// Get the footer
get_footer();