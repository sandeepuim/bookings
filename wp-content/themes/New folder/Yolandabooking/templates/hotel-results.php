<?php
/**
 * Template Name: Hotel Results
 */
get_header();

// Load the hotel results CSS
wp_enqueue_style('hotel-results', get_template_directory_uri() . '/assets/css/hotel-results.css', array(), '1.3');
wp_enqueue_style('hotel-thumbnails', get_template_directory_uri() . '/assets/css/hotel-thumbnails.css', array(), '1.0');
wp_enqueue_style('hotel-rooms', get_template_directory_uri() . '/assets/css/hotel-rooms.css', array(), '1.0');

require_once get_template_directory() . '/inc/TboApiClient.php';

// 1. Get form data
$country_code = sanitize_text_field($_GET['country_code'] ?? '');
$city_code    = sanitize_text_field($_GET['city_code'] ?? '');
$check_in     = sanitize_text_field($_GET['check_in'] ?? '');
$check_out    = sanitize_text_field($_GET['check_out'] ?? '');
$rooms        = intval($_GET['rooms'] ?? 1);
$adults       = intval($_GET['adults'] ?? 1);
$children     = intval($_GET['children'] ?? 0);

// Validate dates before proceeding
$date_error = '';
if (!empty($check_in) && !empty($check_out)) {
    // Convert to timestamps for comparison
    $check_in_time = strtotime($check_in);
    $check_out_time = strtotime($check_out);
    $today = strtotime(date('Y-m-d'));
    
    if ($check_in_time === false || $check_out_time === false) {
        $date_error = 'Invalid date format. Please use YYYY-MM-DD format.';
    } else if ($check_in_time < $today) {
        $date_error = 'Check-in date cannot be in the past.';
    } else if ($check_out_time < $check_in_time) {
        $date_error = 'Check-out date must not be before check-in date.';
    }
}

// 2. Connect to TBO API
$tbo = new TboApiClient(
    'http://api.tbotechnology.in/TBOHolidays_HotelAPI', // Service URL
    'YOLANDATHTest',
    'Yol@40360746'
);

try {
    // Set error reporting for debugging
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
    // Check for date validation errors before making API call
    if (!empty($date_error)) {
        throw new Exception('Invalid date: ' . $date_error);
    }
    
    // 3. Search hotels
    $results = $tbo->searchHotels($country_code, $city_code, $check_in, $check_out, $rooms, $adults, $children);

    // Output detailed response structure for debugging (only visible to admins)
    if (current_user_can('administrator')) {
        error_log('Hotel search response structure: ' . json_encode(array_keys($results)));
        
        // Check for different known result formats
        if (isset($results['HotelResult'])) {
            error_log('Response uses HotelResult format. Keys: ' . json_encode(array_keys($results['HotelResult'])));
        }
        
        if (isset($results['Hotels'])) {
            error_log('Response contains Hotels array. Count: ' . count($results['Hotels']));
            
            // Sample the first hotel to see its structure
            if (!empty($results['Hotels'])) {
                $firstHotel = reset($results['Hotels']);
                error_log('First hotel structure. Keys: ' . json_encode(array_keys($firstHotel)));
            }
        }
    }

    // Normalize response format for consistent handling
    if (isset($results['HotelResult']) && !isset($results['Hotels'])) {
        $results['Hotels'] = $results['HotelResult'];
    } elseif (!isset($results['Hotels']) && isset($results['Result']) && is_array($results['Result'])) {
        $results['Hotels'] = $results['Result'];
    }

    // Ensure we have an array of hotels even if the API returns a single hotel in a non-array format
    if (isset($results['Hotels']) && !empty($results['Hotels']) && !isset($results['Hotels'][0])) {
        // If the Hotels key doesn't contain a numeric array, it's likely a single hotel
        // We'll wrap it in an array to ensure consistent processing
        $results['Hotels'] = array($results['Hotels']);
    }

    // Debug the hotel count
    if (current_user_can('administrator')) {
        error_log('Number of hotels after normalization: ' . (isset($results['Hotels']) ? count($results['Hotels']) : 0));
    }

    // 4. Display results
    ?>
    <section class="hotel-results-section">
        <div class="container">
            <?php if (current_user_can('administrator')): ?>
            <div class="admin-debug-info" style="margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border: 1px solid #ddd;">
                <h3>API Response Structure (Admin Only)</h3>
                <p>This information helps identify the API response format and where to look for hotel data:</p>
                <div>
                    <strong>Top-level keys:</strong> 
                    <?php echo implode(', ', array_keys($results)); ?>
                </div>
                
                <?php if (!empty($results['Hotels'])): ?>
                    <div>
                        <strong>First hotel keys:</strong> 
                        <?php 
                            $firstHotel = reset($results['Hotels']);
                            echo implode(', ', array_keys($firstHotel)); 
                        ?>
                    </div>
                    
                    <div>
                        <strong>Total hotels found:</strong> <?php echo count($results['Hotels']); ?>
                        <?php if (count($results['Hotels']) == 1): ?>
                            <p><em>Note: The TBO API is returning a single hotel with multiple room options, not multiple hotels.</em></p>
                        <?php endif; ?>
                    </div>
                    
                    <?php 
                    // Check for room information
                    if (isset($firstHotel['Rooms']) && is_array($firstHotel['Rooms']) && !empty($firstHotel['Rooms'])): 
                    ?>
                    <div>
                        <strong>Room options for this hotel:</strong> <?php echo count($firstHotel['Rooms']); ?>
                        <p><em>Each hotel in the API response can have multiple room options with different prices and features.</em></p>
                        
                        <div style="margin-left: 20px; padding-left: 10px; border-left: 3px solid #ddd;">
                            <strong>First room keys:</strong>
                            <?php 
                                $firstRoom = reset($firstHotel['Rooms']);
                                echo implode(', ', array_keys($firstRoom)); 
                            ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (isset($firstHotel['Price'])): ?>
                    <div>
                        <strong>Price structure:</strong> 
                        <?php echo implode(', ', array_keys($firstHotel['Price'])); ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <p><a href="#" onclick="document.querySelector('.admin-debug-info').style.display='none'; return false;">Hide Debug Info</a></p>
            </div>
            <?php endif; ?>
            
            <!-- Add custom CSS to fix the hotel search results title overlapping with logo -->
            <style>
                /* Fix for hotel search results title overlapping logo */
                .search-summary {
                    margin-top: 80px; /* Add space below the header/logo */
                }
                
                .search-summary h1 {
                    font-size: 22px; /* Slightly smaller font size */
                    margin-left: 100px; /* Add left margin to avoid logo overlap */
                    padding-top: 10px; /* Add a little more space at the top */
                }
                
                /* Hide the "Hotel Search Results" text that might appear in the header */
                header .container > h1 {
                    display: none !important;
                }
                
                /* Consistent spacing for headings */
                .hotel-gallery h3 {
                    margin-left: 0; /* Override the inline style */
                    color: #2c3e50;
                }
                
                @media (max-width: 768px) {
                    .search-summary h1 {
                        margin-left: 0; /* Remove margin on mobile */
                    }
                }
            </style>
            
            <div class="search-summary">
                <h1>Hotel Search Results</h1>
                
                <p class="search-details">
                    Showing hotels in <strong><?php echo esc_html($city_code); ?></strong>
                    from <strong><?php echo esc_html(date('F j, Y', strtotime($check_in))); ?></strong> 
                    to <strong><?php echo esc_html(date('F j, Y', strtotime($check_out))); ?></strong>
                    for <strong><?php echo esc_html($adults); ?> adults</strong>
                    <?php if ($children > 0): ?>
                        and <strong><?php echo esc_html($children); ?> children</strong>
                    <?php endif; ?>
                    in <strong><?php echo esc_html($rooms); ?> room(s)</strong>
                </p>
                
                <div class="modify-search">
                    <a href="<?php echo esc_url(home_url()); ?>" class="btn-modify">Modify Search</a>
                </div>
            </div>
            
            <?php 
            // Display success message if hotels were found
            if (!empty($results['Hotels'])):
                $hotel_count = count($results['Hotels']);
            ?>
            <div class="success-message">
                <p>Success! We found <?php echo $hotel_count; ?> hotels matching your search criteria.</p>
            </div>
            
            <!-- Hotel Gallery Section - Show thumbnails of all hotels -->
            <div class="hotel-gallery">
                <h3>Browse All Hotels</h3>
                <div class="hotel-thumbnails">
                    <?php foreach ($results['Hotels'] as $index => $hotel): 
                        // Get hotel image or dummy
                        $hotelImage = '';
                        
                        // Check different possible locations for images
                        if (isset($hotel['HotelPicture']) && !empty($hotel['HotelPicture'])) {
                            $hotelImage = $hotel['HotelPicture'];
                        } elseif (isset($hotel['HotelInfo']['HotelPicture']) && !empty($hotel['HotelInfo']['HotelPicture'])) {
                            $hotelImage = $hotel['HotelInfo']['HotelPicture'];
                        } elseif (isset($hotel['Images']) && is_array($hotel['Images']) && !empty($hotel['Images'][0])) {
                            $hotelImage = $hotel['Images'][0];
                        } elseif (isset($hotel['HotelInfo']['Images']) && is_array($hotel['HotelInfo']['Images']) && !empty($hotel['HotelInfo']['Images'][0])) {
                            $hotelImage = $hotel['HotelInfo']['Images'][0];
                        }
                        
                        // Define dummy image URLs for different hotel categories
                        $dummyImages = [
                            'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                            'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGhvdGVsfGVufDB8fDB8fHww&auto=format&fit=crop&w=800&q=60',
                            'https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                            'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                            'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60'
                        ];
                        
                        // Use a random dummy image if no image is found
                        if (empty($hotelImage)) {
                            // Use hotel code as a seed for consistent image selection
                            $seed = isset($hotel['HotelCode']) ? intval($hotel['HotelCode']) : $index;
                            $imageIndex = $seed % count($dummyImages);
                            $hotelImage = $dummyImages[$imageIndex];
                        }
                        
                        // Extract hotel name
                        $hotelName = '';
                        if (!empty($hotel['HotelName'])) {
                            $hotelName = $hotel['HotelName'];
                        } elseif (!empty($hotel['Name'])) {
                            $hotelName = is_array($hotel['Name']) ? ($hotel['Name'][0] ?? 'Hotel') : $hotel['Name'];
                        } elseif (isset($hotel['HotelInfo']) && !empty($hotel['HotelInfo']['HotelName'])) {
                            $hotelName = $hotel['HotelInfo']['HotelName'];
                        } else {
                            $hotelName = 'Premium Hotel';
                        }
                    ?>
                        <div class="thumbnail-item">
                            <a href="#hotel-<?php echo esc_attr($index); ?>">
                                <div class="thumbnail-image" style="background-image: url('<?php echo esc_url($hotelImage); ?>')"></div>
                                <div class="thumbnail-caption"><?php echo esc_html($hotelName); ?></div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="hotel-results">
                <?php if (!empty($results['Hotels'])): ?>
                    <?php foreach ($results['Hotels'] as $index => $hotel): ?>
                        <div class="hotel-card" id="hotel-<?php echo esc_attr($index); ?>">
                            <?php
                            // Try to find hotel image from various possible locations in the API response
                            $hotelImage = '';
                            
                            // Check different possible locations for images
                            if (isset($hotel['HotelPicture']) && !empty($hotel['HotelPicture'])) {
                                $hotelImage = $hotel['HotelPicture'];
                            } elseif (isset($hotel['HotelInfo']['HotelPicture']) && !empty($hotel['HotelInfo']['HotelPicture'])) {
                                $hotelImage = $hotel['HotelInfo']['HotelPicture'];
                            } elseif (isset($hotel['Images']) && is_array($hotel['Images']) && !empty($hotel['Images'][0])) {
                                $hotelImage = $hotel['Images'][0];
                            } elseif (isset($hotel['HotelInfo']['Images']) && is_array($hotel['HotelInfo']['Images']) && !empty($hotel['HotelInfo']['Images'][0])) {
                                $hotelImage = $hotel['HotelInfo']['Images'][0];
                            }
                            
                            // Define dummy image URLs for different hotel categories
                            $dummyImages = [
                                'https://images.unsplash.com/photo-1566073771259-6a8506099945?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8Mnx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                                'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTB8fGhvdGVsfGVufDB8fDB8fHww&auto=format&fit=crop&w=800&q=60',
                                'https://images.unsplash.com/photo-1618773928121-c32242e63f39?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8OXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                                'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8NXx8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60',
                                'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxzZWFyY2h8M3x8aG90ZWx8ZW58MHx8MHx8fDA%3D&auto=format&fit=crop&w=800&q=60'
                            ];
                            
                            // Use a random dummy image if no image is found
                            if (empty($hotelImage)) {
                                // Use hotel code as a seed for consistent image selection
                                $seed = isset($hotel['HotelCode']) ? intval($hotel['HotelCode']) : $index;
                                $imageIndex = $seed % count($dummyImages);
                                $hotelImage = $dummyImages[$imageIndex];
                            }
                            
                            // Extract hotel name
                            $hotelName = '';
                            if (!empty($hotel['HotelName'])) {
                                $hotelName = $hotel['HotelName'];
                            } elseif (!empty($hotel['Name'])) {
                                $hotelName = is_array($hotel['Name']) ? ($hotel['Name'][0] ?? 'Hotel') : $hotel['Name'];
                            } elseif (isset($hotel['HotelInfo']) && !empty($hotel['HotelInfo']['HotelName'])) {
                                $hotelName = $hotel['HotelInfo']['HotelName'];
                            } else {
                                $hotelName = 'Premium Hotel';
                            }

                            // Get hotel location/address
                            $hotelAddress = isset($hotel['HotelAddress']) ? $hotel['HotelAddress'] : '';
                            $hotelLocation = isset($hotel['Location']) ? $hotel['Location'] : '';
                            $hotelCity = isset($hotel['CityName']) ? $hotel['CityName'] : '';
                            $displayLocation = !empty($hotelAddress) ? $hotelAddress : (!empty($hotelLocation) ? $hotelLocation : $hotelCity);

                            // Calculate lowest room price and find amenities
                            $lowestPrice = null;
                            $originalPrice = null;
                            $currency = 'INR';
                            $amenities = [];

                            // Check for rooms array to get the lowest price
                            if (isset($hotel['Rooms']) && is_array($hotel['Rooms']) && !empty($hotel['Rooms'])) {
                                foreach ($hotel['Rooms'] as $room) {
                                    if (isset($room['TotalFare']) && is_numeric($room['TotalFare'])) {
                                        $roomPrice = $room['TotalFare'];
                                        $roomCurrency = isset($hotel['Currency']) ? $hotel['Currency'] : 'INR';
                                        
                                        // Convert currency if needed
                                        if ($roomCurrency == 'THB') {
                                            $roomPrice = $roomPrice * 2.2;
                                        }
                                        
                                        if ($lowestPrice === null || $roomPrice < $lowestPrice) {
                                            $lowestPrice = $roomPrice;
                                            // Set original price 30-48% higher for display purposes (simulating discount)
                                            $originalPrice = $roomPrice * (1 + (mt_rand(30, 48) / 100));
                                        }
                                    }
                                    
                                    // Collect amenities from rooms
                                    if (isset($room['Inclusion']) && !empty($room['Inclusion'])) {
                                        $inclusions = explode(',', $room['Inclusion']);
                                        foreach ($inclusions as $inclusion) {
                                            $inclusion = trim($inclusion);
                                            if (!empty($inclusion) && !in_array($inclusion, $amenities)) {
                                                $amenities[] = $inclusion;
                                            }
                                        }
                                    }
                                }
                            }
                            
                            // If no price found in rooms, try hotel level price
                            if ($lowestPrice === null) {
                                // Various price locations in API response
                                if (isset($hotel['TotalFare']) && is_numeric($hotel['TotalFare'])) {
                                    $lowestPrice = $hotel['TotalFare'];
                                } elseif (isset($hotel['Price']['TotalAmount']) && is_numeric($hotel['Price']['TotalAmount'])) {
                                    $lowestPrice = $hotel['Price']['TotalAmount'];
                                    $currency = $hotel['Price']['CurrencyCode'] ?? $currency;
                                } elseif (isset($hotel['MinHotelPrice']) && is_numeric($hotel['MinHotelPrice'])) {
                                    $lowestPrice = $hotel['MinHotelPrice'];
                                } else {
                                    // Default price
                                    $lowestPrice = 5000;
                                }
                                
                                // Set original price 30-48% higher for display purposes (simulating discount)
                                $originalPrice = $lowestPrice * (1 + (mt_rand(30, 48) / 100));
                            }
                            
                            // Convert currency if needed
                            if (isset($hotel['Currency']) && $hotel['Currency'] == 'THB') {
                                $lowestPrice = $lowestPrice * 2.2;
                                $originalPrice = $originalPrice * 2.2;
                                $currency = 'INR';
                            }
                            
                            // Get discount percentage
                            $discountPercent = 0;
                            if ($originalPrice > 0) {
                                $discountPercent = round(($originalPrice - $lowestPrice) / $originalPrice * 100);
                            }
                            
                            // Get hotel rating
                            $rating = isset($hotel['StarRating']) ? intval($hotel['StarRating']) : 4;
                            
                            // Calculate nights
                            $nights = ceil((strtotime($check_out) - strtotime($check_in)) / 86400);
                            
                            // Limited amenities to display
                            $displayAmenities = array_slice($amenities, 0, 3);
                            
                            // Get the hotel code for details page
                            $hotelCode = $hotel['BookingCode'] ?? ($hotel['HotelCode'] ?? '');
                            ?>
                            
                            <div class="hotel-image">
                                <img src="<?php echo esc_url($hotelImage); ?>" alt="<?php echo esc_attr($hotelName); ?>" class="hotel-thumbnail">
                            </div>
                            
                            <div class="hotel-info">
                                <h2 class="hotel-name"><?php echo esc_html($hotelName); ?></h2>
                                
                                <div class="hotel-rating">
                                    <?php for ($i = 0; $i < $rating; $i++): ?>
                                        <span class="star">★</span>
                                    <?php endfor; ?>
                                </div>
                                
                                <?php if (!empty($displayLocation)): ?>
                                <div class="hotel-location">
                                    <i class="location-icon">📍</i> <?php echo esc_html($displayLocation); ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($displayAmenities)): ?>
                                <div class="hotel-amenities-list">
                                    <?php foreach ($displayAmenities as $amenity): ?>
                                    <span class="amenity-badge"><?php echo esc_html($amenity); ?></span>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (current_user_can('administrator')): ?>
                                <div class="hotel-code">
                                    <small>Hotel Code: <?php echo esc_html($hotel['HotelCode'] ?? 'N/A'); ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hotel-price-card">
                                <?php if ($discountPercent > 0): ?>
                                <div class="discount-tag"><?php echo esc_html($discountPercent); ?>% off</div>
                                <?php endif; ?>
                                
                                <div class="price-details">
                                    <?php if ($originalPrice > 0): ?>
                                    <div class="original-price">₹<?php echo esc_html(number_format($originalPrice, 0)); ?></div>
                                    <?php endif; ?>
                                    
                                    <div class="current-price">₹<?php echo esc_html(number_format($lowestPrice, 0)); ?></div>
                                    
                                    <div class="price-info">
                                        +taxes & fees<br>
                                        per room per night
                                    </div>
                                </div>
                                
                                <a href="<?php echo esc_url(add_query_arg(['hotel_code' => $hotelCode], site_url('/hotel-details'))); ?>" class="btn-choose-room">Choose Room</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <p>No hotels found matching your search criteria. Please try different search parameters.</p>
                        
                        <?php if (current_user_can('manage_options')): ?>
                        <div class="admin-debug-info">
                            <h3>Debug Information (visible to admins only):</h3>
                            <p>Search Parameters:</p>
                            <ul>
                                <li>Country Code: <?php echo esc_html($country_code); ?></li>
                                <li>City Code: <?php echo esc_html($city_code); ?></li>
                                <li>Check-in: <?php echo esc_html($check_in); ?></li>
                                <li>Check-out: <?php echo esc_html($check_out); ?></li>
                                <li>Rooms: <?php echo esc_html($rooms); ?></li>
                                <li>Adults: <?php echo esc_html($adults); ?></li>
                                <li>Children: <?php echo esc_html($children); ?></li>
                            </ul>
                            
                            <p>API Response:</p>
                            <pre><?php print_r($results); ?></pre>
                            
                            <p>Suggestions:</p>
                            <ul>
                                <li>Try a different city or country</li>
                                <li>Try dates with a longer stay (2+ nights)</li>
                                <li>Check if the city code is valid</li>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php

} catch (Exception $e) {
    echo '<div class="error-container">';
    echo "<p>Error: " . esc_html($e->getMessage()) . "</p>";
    
    // Add debugging information for admins
    if (current_user_can('manage_options')) {
        echo '<div class="admin-debug-info">';
        echo '<h3>Debug Information (visible to admins only):</h3>';
        echo '<p>Search Parameters:</p>';
        echo '<ul>';
        echo '<li>Country Code: ' . esc_html($country_code) . '</li>';
        echo '<li>City Code: ' . esc_html($city_code) . '</li>';
        echo '<li>Check-in: ' . esc_html($check_in) . '</li>';
        echo '<li>Check-out: ' . esc_html($check_out) . '</li>';
        echo '<li>Rooms: ' . esc_html($rooms) . '</li>';
        echo '<li>Adults: ' . esc_html($adults) . '</li>';
        echo '<li>Children: ' . esc_html($children) . '</li>';
        echo '</ul>';
        echo '<p>Error Details: ' . esc_html($e->getTraceAsString()) . '</p>';
        echo '</div>';
    }
    
    echo '</div>';
}

get_footer();
