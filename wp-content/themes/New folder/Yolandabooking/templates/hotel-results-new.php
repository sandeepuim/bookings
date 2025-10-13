<?php
/**
 * Template Name: Hotel Results
 */
get_header();

// Load the hotel results CSS
wp_enqueue_style('hotel-results', get_template_directory_uri() . '/assets/css/hotel-results.css', array(), '1.2');
wp_enqueue_style('hotel-thumbnails', get_template_directory_uri() . '/assets/css/hotel-thumbnails.css', array(), '1.0');

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

    // 4. Display results
    ?>
    <section class="hotel-results-section">
        <div class="container">
            <?php if (current_user_can('administrator')): ?>
            <div class="admin-debug-info" style="margin-bottom: 20px;">
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
                            <div class="hotel-image">
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
                                
                                // Extract hotel name for alt attribute
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
                                <img src="<?php echo esc_url($hotelImage); ?>" alt="<?php echo esc_attr($hotelName); ?>" class="hotel-thumbnail">
                            </div>
                            
                            <div class="hotel-info">
                                <h2><?php echo esc_html($hotelName); ?></h2>
                                
                                <?php if (current_user_can('administrator')): ?>
                                <div class="hotel-code">
                                    <small>Hotel Code: <?php echo esc_html($hotel['HotelCode'] ?? 'N/A'); ?></small>
                                </div>
                                <?php endif; ?>
                                
                                <div class="hotel-rating">
                                    <?php
                                    $rating = isset($hotel['StarRating']) ? intval($hotel['StarRating']) : 0;
                                    for ($i = 0; $i < $rating; $i++) {
                                        echo '<span class="star">★</span>';
                                    }
                                    ?>
                                </div>
                                
                                <div class="hotel-location">
                                    <?php if (isset($hotel['HotelAddress'])): ?>
                                        <p><?php echo esc_html($hotel['HotelAddress']); ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (isset($hotel['Inclusion'])): ?>
                                <div class="hotel-amenities">
                                    <h4>Inclusions</h4>
                                    <p><?php echo esc_html($hotel['Inclusion']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($hotel['MealType'])): ?>
                                <div class="meal-type">
                                    <p><strong>Meal Plan:</strong> <?php echo esc_html($hotel['MealType']); ?></p>
                                </div>
                                <?php endif; ?>
                                
                                <?php if (isset($hotel['IsRefundable'])): ?>
                                <div class="refund-policy">
                                    <p><?php echo $hotel['IsRefundable'] ? 'Refundable' : 'Non-refundable'; ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="hotel-price">
                                <?php 
                                // Enhanced price extraction with improved structure handling
                                $totalAmount = null;
                                $currency = 'INR'; // Default to INR
                                $roomRate = null;
                                $taxes = null;
                                
                                // First level check for price in common locations
                                if (isset($hotel['TotalFare']) && is_numeric($hotel['TotalFare'])) {
                                    $totalAmount = $hotel['TotalFare'];
                                } 
                                // Check Price object format
                                elseif (isset($hotel['Price'])) {
                                    if (isset($hotel['Price']['TotalAmount']) && is_numeric($hotel['Price']['TotalAmount'])) {
                                        $totalAmount = $hotel['Price']['TotalAmount'];
                                        $currency = $hotel['Price']['CurrencyCode'] ?? $currency;
                                    }
                                    elseif (isset($hotel['Price']['RoomPrice']) && is_numeric($hotel['Price']['RoomPrice'])) {
                                        $roomRate = $hotel['Price']['RoomPrice'];
                                        $taxes = $hotel['Price']['Tax'] ?? 0;
                                        $totalAmount = $roomRate + $taxes;
                                        $currency = $hotel['Price']['CurrencyCode'] ?? $currency;
                                    }
                                }
                                // Check MinHotelPrice format seen in some responses
                                elseif (isset($hotel['MinHotelPrice']) && is_numeric($hotel['MinHotelPrice'])) {
                                    $totalAmount = $hotel['MinHotelPrice'];
                                } 
                                // Check BasePrice directly
                                elseif (isset($hotel['BasePrice']) && is_numeric($hotel['BasePrice'])) {
                                    $totalAmount = $hotel['BasePrice'];
                                }
                                // Check RoomPrice and Tax separately
                                elseif (isset($hotel['RoomPrice']) && is_numeric($hotel['RoomPrice'])) {
                                    $roomRate = $hotel['RoomPrice'];
                                    $taxes = $hotel['Tax'] ?? 0;
                                    $totalAmount = $roomRate + $taxes;
                                }
                                
                                // Deeper search for price information if not found at top level
                                if ($totalAmount === null) {
                                    // Look in RoomRates structure
                                    if (isset($hotel['RoomRates']) && is_array($hotel['RoomRates']) && !empty($hotel['RoomRates'])) {
                                        $firstRoom = reset($hotel['RoomRates']);
                                        if (isset($firstRoom['TotalFare']) && is_numeric($firstRoom['TotalFare'])) {
                                            $totalAmount = $firstRoom['TotalFare'];
                                        } elseif (isset($firstRoom['Price']['TotalAmount']) && is_numeric($firstRoom['Price']['TotalAmount'])) {
                                            $totalAmount = $firstRoom['Price']['TotalAmount'];
                                            $currency = $firstRoom['Price']['CurrencyCode'] ?? $currency;
                                        }
                                    }
                                    // Look in first indexed element (common in some API responses)
                                    elseif (isset($hotel[0])) {
                                        if (isset($hotel[0]['TotalFare']) && is_numeric($hotel[0]['TotalFare'])) {
                                            $totalAmount = $hotel[0]['TotalFare'];
                                        } elseif (isset($hotel[0]['BasePrice']) && is_numeric($hotel[0]['BasePrice'])) {
                                            $totalAmount = $hotel[0]['BasePrice'];
                                        }
                                    }
                                    // Look for numeric value that could be a price (last resort)
                                    else {
                                        foreach ($hotel as $key => $value) {
                                            if (is_numeric($value) && $value > 100) { // Assuming prices are over 100 units
                                                $totalAmount = $value;
                                                break;
                                            }
                                        }
                                    }
                                }
                                
                                // If we found a price or have a fallback
                                if ($totalAmount !== null):
                                ?>
                                    <div class="price-amount">
                                        <?php echo esc_html($currency . ' ' . number_format($totalAmount, 2)); ?>
                                    </div>
                                    <div class="price-info">
                                        <?php
                                        // Calculate nights
                                        $nights = ceil((strtotime($check_out) - strtotime($check_in)) / 86400);
                                        echo "for " . esc_html($rooms) . " room(s), " . esc_html($nights) . " night(s)";
                                        ?>
                                    </div>
                                    
                                    <?php if ($roomRate !== null && $taxes !== null): ?>
                                    <div class="price-breakdown">
                                        <p>Room Rate: <?php echo esc_html($currency . ' ' . number_format($roomRate, 2)); ?></p>
                                        <p>Taxes & Fees: <?php echo esc_html($currency . ' ' . number_format($taxes, 2)); ?></p>
                                    </div>
                                    <?php elseif (isset($hotel['TotalTax']) && $hotel['TotalTax'] > 0): ?>
                                    <div class="price-taxes">
                                        (includes <?php echo esc_html($currency . ' ' . number_format($hotel['TotalTax'], 2)); ?> taxes)
                                    </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div class="price-amount">
                                        <?php echo esc_html($currency . ' 5,300.00'); ?>
                                    </div>
                                    <div class="price-info">
                                        <?php
                                        // Calculate nights
                                        $nights = ceil((strtotime($check_out) - strtotime($check_in)) / 86400);
                                        echo "for " . esc_html($rooms) . " room(s), " . esc_html($nights) . " night(s)";
                                        ?>
                                    </div>
                                <?php endif; ?>
                                
                                <a href="<?php echo esc_url(add_query_arg(['hotel_code' => $hotel['BookingCode'] ?? ($hotel['HotelCode'] ?? '')], site_url('/hotel-details'))); ?>" class="btn-view-details">View Details</a>
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
