<?php
/**
 * Simple TBO API Client Class - Simplified approach
 * 
 * @package Yolandabooking
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class SimpleTboApiClient {
    private $staticApiUrl = 'http://api.tbotechnology.in/hotelapi_v10/';
    private $hotelApiUrl = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI/';
    private $searchApiUrl = 'http://api.tbotechnology.in/hotelapi_v10/'; // Same as static for search
    private $tokenId = null;

    /**
     * Get city-specific hotel data based on city code
     */
    private function getCitySpecificData($cityCode) {
        $cityMappings = [
            // Rajasthan cities
            '105141' => [
                'cityName' => 'Jaipur, Rajasthan',
                'state' => 'Rajasthan',
                'hotelNames' => [
                    'Rajputana Palace Hotel', 'Heritage Haveli Jaipur', 'Pink City Grand Hotel',
                    'Maharaja Palace Hotel', 'Jaipur Royal Hotel', 'Desert Rose Hotel',
                    'Golden Triangle Hotel', 'Amber Fort View Hotel', 'City Palace Hotel',
                    'Hawa Mahal Hotel', 'Jantar Mantar Hotel', 'Rajasthan Heritage Hotel',
                    'Pink City Palace', 'Royal Rajput Hotel', 'Jaipur Crown Hotel',
                    'Maharani Palace Hotel', 'Rajasthani Cultural Hotel', 'Desert Wind Hotel',
                    'Jaipur Heritage Inn', 'Royal Jaipur Hotel', 'Pink City Luxury',
                    'Amber Palace Hotel', 'Rajput Heritage Hotel', 'Jaipur Royal Palace', 'Desert Palace Hotel'
                ],
                'addresses' => [
                    'MI Road, Jaipur', 'C-Scheme, Jaipur', 'Bani Park, Jaipur',
                    'Civil Lines, Jaipur', 'Malviya Nagar, Jaipur', 'Tonk Road, Jaipur',
                    'Ajmer Road, Jaipur', 'JLN Marg, Jaipur', 'Vaishali Nagar, Jaipur',
                    'Near City Palace, Jaipur'
                ]
            ],
            // Delhi (default)
            '418069' => [
                'cityName' => 'New Delhi',
                'state' => 'Delhi',
                'hotelNames' => [
                    'Grand Palace Hotel Delhi', 'Luxury Business Hotel', 'Royal Heritage Hotel',
                    'Modern Comfort Inn', 'Executive Suites', 'Premium Stay Hotel',
                    'City Center Hotel', 'Garden View Hotel', 'Metro Station Hotel',
                    'Airport Link Hotel', 'Downtown Business Hotel', 'Riverside Resort',
                    'Hill View Hotel', 'Urban Style Hotel', 'Classic Comfort Hotel',
                    'Elite Business Hotel', 'Sunset Boulevard Hotel', 'Pearl Continental',
                    'Golden Triangle Hotel', 'Silver Star Hotel', 'Diamond Plaza Hotel',
                    'Platinum Suites', 'Emerald Garden Hotel', 'Ruby Tower Hotel', 'Sapphire Inn'
                ],
                'addresses' => [
                    'Connaught Place, New Delhi', 'Karol Bagh, New Delhi', 'Paharganj, New Delhi',
                    'Janpath, New Delhi', 'Rajouri Garden, New Delhi', 'Lajpat Nagar, New Delhi',
                    'Greater Kailash, New Delhi', 'Saket, New Delhi', 'Vasant Kunj, New Delhi',
                    'Dwarka, New Delhi'
                ]
            ],
            // Mumbai
            '111647' => [
                'cityName' => 'Mumbai, Maharashtra',
                'state' => 'Maharashtra',
                'hotelNames' => [
                    'Mumbai Grand Hotel', 'Bollywood Palace Hotel', 'Marine Drive Hotel',
                    'Gateway of India Hotel', 'Mumbai Business Inn', 'Colaba Heritage Hotel',
                    'Bandra West Hotel', 'Andheri Airport Hotel', 'Juhu Beach Resort',
                    'Powai Lake Hotel', 'Mumbai Central Hotel', 'Worli Sea Link Hotel',
                    'Thane Metro Hotel', 'BKC Business Hotel', 'Navi Mumbai Hotel',
                    'Mumbai Port Hotel', 'Film City Hotel', 'Mumbai Express Hotel',
                    'Crawford Market Hotel', 'Mumbai Heritage Inn', 'Suburban Mumbai Hotel',
                    'Mumbai Royal Hotel', 'Sea View Mumbai', 'Mumbai Corporate Hotel', 'City of Dreams Hotel'
                ],
                'addresses' => [
                    'Colaba, Mumbai', 'Bandra West, Mumbai', 'Andheri East, Mumbai',
                    'Juhu, Mumbai', 'Powai, Mumbai', 'BKC, Mumbai',
                    'Worli, Mumbai', 'Marine Drive, Mumbai', 'Fort, Mumbai',
                    'Thane, Mumbai'
                ]
            ]
        ];
        
        // Return city-specific data or default to Delhi if city not found
        return $cityMappings[$cityCode] ?? $cityMappings['418069'];
    }

    public function searchHotels($countryCode, $cityCode, $checkIn, $checkOut, $rooms, $adults, $children) {
        error_log("=== SIMPLE TBO SEARCH CALLED ===");
        error_log("City: $cityCode, CheckIn: $checkIn, CheckOut: $checkOut");
        
        set_time_limit(180);
        $allResults = ['Hotels' => []];

        try {
            error_log("[Simple TBO] Starting batch search for city: $cityCode");
            
            // Get city-specific data
            $cityData = $this->getCitySpecificData($cityCode);
            error_log("[Simple TBO] Using data for: " . $cityData['cityName']);
            
            // First, let's show mock data with more hotels to simulate batch processing
            error_log("[Simple TBO] Using enhanced mock data to simulate batch processing");
            
            $mockHotels = [];
            
            // Create 25 mock hotels to simulate batch processing results
            for ($i = 1; $i <= 25; $i++) {
                $hotelNames = $cityData['hotelNames'];
                $addresses = $cityData['addresses'];
                
                $ratings = ['ThreeStar', 'FourStar', 'FiveStar'];
                $images = [
                    'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=60',
                    'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=800&q=60',
                    'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=60',
                    'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=60',
                    'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?auto=format&fit=crop&w=800&q=60'
                ];
                
                $rating = $ratings[($i - 1) % 3];
                $starRating = $rating === 'ThreeStar' ? 3 : ($rating === 'FourStar' ? 4 : 5);
                $basePrice = $starRating === 3 ? rand(1500, 2500) : ($starRating === 4 ? rand(2500, 4500) : rand(4500, 8000));
                
                $mockHotels[] = [
                    'HotelCode' => 'HTL' . str_pad($i, 5, '0', STR_PAD_LEFT),
                    'HotelName' => $hotelNames[($i - 1) % count($hotelNames)] . ' - ' . $i,
                    'HotelAddress' => $addresses[($i - 1) % count($addresses)],
                    'HotelRating' => $rating,
                    'StarRating' => $starRating,
                    'ResultIndex' => $i - 1,
                    'HotelPicture' => $images[($i - 1) % count($images)],
                    'Description' => 'A comfortable ' . strtolower(str_replace('Star', ' star', $rating)) . ' hotel offering excellent service and amenities in ' . $cityData['cityName'] . '.',
                    'Rooms' => [
                        [
                            'RoomTypeCode' => 'DELUXE',
                            'RoomTypeName' => 'Deluxe Room',
                            'TotalFare' => $basePrice,
                            'Currency' => 'INR',
                            'MealType' => ($i % 3 === 0) ? 'Breakfast Included' : 'Room Only',
                            'RoomIndex' => 1,
                            'IsRefundable' => $i % 2 === 0
                        ],
                        [
                            'RoomTypeCode' => 'SUITE',
                            'RoomTypeName' => 'Executive Suite',
                            'TotalFare' => $basePrice + rand(1000, 2000),
                            'Currency' => 'INR',
                            'MealType' => 'Breakfast Included',
                            'RoomIndex' => 2,
                            'IsRefundable' => true
                        ]
                    ]
                ];
            }
            
            $allResults['Hotels'] = $mockHotels;
            error_log("[Simple TBO] Returning " . count($allResults['Hotels']) . " mock hotels (simulating batch processing)");
            
            // Add a note about this being mock data
            error_log("[Simple TBO] NOTE: Currently showing mock data. Real API endpoints are returning 404 errors.");
            
            return $allResults;
            
            // The actual API code below is commented out until API endpoints are fixed
            /*
            // Step 1: Get authentication token
            $tokenId = $this->getAuthenticationToken();
            error_log("[Simple TBO] Got token: $tokenId");
            
            // Step 2: Get hotel codes using static API
            $hotelCodesData = [
                'CityCode' => $cityCode,
                'IsDetailedResponse' => true
            ];
            
            $codesUrl = $this->staticApiUrl . 'HotelCodeList';
            $codesResponse = $this->makeApiCall($codesUrl, $hotelCodesData, [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode('TBOStaticAPITest:Tbo@11530818')
            ]);
            // ... rest of the actual API code
            */
            
        } catch (Exception $e) {
            error_log("[Simple TBO] Error: " . $e->getMessage());
            return ['Hotels' => []];
        }
    }
    
    /**
     * Create batches of hotels (like Node.js HotelProcessor.partitionList)
     */
    private function createBatches($hotels, $batchSize, $maxBatches) {
        $batches = [];
        $hotelChunks = array_chunk($hotels, $batchSize);
        
        // Limit to maxBatches
        $limitedChunks = array_slice($hotelChunks, 0, $maxBatches);
        
        return $limitedChunks;
    }
    
    /**
     * Get authentication token - Skip for now, use Basic Auth
     */
    private function getAuthenticationToken() {
        // For now, we'll skip token authentication and use Basic Auth directly
        // Based on your Node.js code, the search API uses Basic Auth
        return 'SKIP_TOKEN';
    }
    
    private function makeApiCall($url, $data, $headers) {
        error_log("[Simple TBO] Making API call to: $url");
        error_log("[Simple TBO] Request data: " . json_encode($data));
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("Curl error: $error");
        }
        
        curl_close($ch);
        
        error_log("[Simple TBO] HTTP Code: $httpCode");
        error_log("[Simple TBO] Raw response (first 1000 chars): " . substr($response, 0, 1000));
        
        $result = json_decode($response, true);
        if (!$result) {
            throw new Exception("Failed to decode API response. HTTP Code: $httpCode, Response: " . substr($response, 0, 500));
        }
        
        return $result;
    }

    /**
     * Get hotel details with rooms (for hotel-details page)
     * This method is needed for hotel-details.php compatibility
     */
    public function getHotelWithRooms($hotelCode, $checkIn, $checkOut, $adults, $children, $countryCode) {
        error_log("[Simple TBO Hotel Details] Getting details for hotel: $hotelCode");
        
        try {
            // For now, return mock hotel data based on the hotel code
            // This matches the structure expected by hotel-details.php
            
            $hotelNames = [
                'HTL00001' => 'Grand Palace Hotel Delhi',
                'HTL00002' => 'Luxury Business Hotel', 
                'HTL00003' => 'Royal Heritage Hotel',
                'HTL00004' => 'Modern Comfort Inn',
                'HTL00005' => 'Executive Suites',
                'HTL00006' => 'Premium Stay Hotel',
                'HTL00007' => 'City Center Hotel',
                'HTL00008' => 'Garden View Hotel',
                'HTL00009' => 'Metro Station Hotel',
                'HTL00010' => 'Airport Link Hotel'
            ];
            
            $hotelName = $hotelNames[$hotelCode] ?? 'Premium Hotel Delhi';
            $hotelNumber = (int)substr($hotelCode, -2);
            $starRating = ($hotelNumber % 3) + 3; // 3, 4, or 5 stars
            $basePrice = $starRating === 3 ? 2500 : ($starRating === 4 ? 4000 : 6500);
            
            $hotelData = [
                'hotel' => [
                    'HotelCode' => $hotelCode,
                    'HotelName' => $hotelName,
                    'HotelAddress' => 'Central Delhi, New Delhi, India',
                    'StarRating' => $starRating,
                    'HotelRating' => $starRating . 'Star',
                    'Description' => 'A premium hotel offering excellent service and modern amenities in the heart of Delhi. Perfect for both business and leisure travelers.',
                    'HotelPicture' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=60',
                    'Images' => [
                        'https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=800&q=60',
                        'https://images.unsplash.com/photo-1445019980597-93fa8acb246c?auto=format&fit=crop&w=800&q=60',
                        'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=800&q=60'
                    ],
                    'CheckIn' => $checkIn,
                    'CheckOut' => $checkOut,
                    'Currency' => 'INR'
                ],
                'rooms' => [
                    [
                        'RoomTypeCode' => 'DELUXE',
                        'RoomTypeName' => 'Deluxe Room',
                        'RoomDescription' => 'Spacious deluxe room with modern amenities, city view, and comfortable bedding.',
                        'MaxOccupancy' => 3,
                        'RoomSize' => '32 sqm',
                        'BedType' => 'King Size Bed',
                        'TotalFare' => $basePrice,
                        'Currency' => 'INR',
                        'MealType' => 'Room Only',
                        'IsRefundable' => true,
                        'RoomIndex' => 1,
                        'RoomImages' => [
                            'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=800&q=60'
                        ],
                        'Amenities' => [
                            'Free WiFi',
                            'Air Conditioning', 
                            'Room Service',
                            'Flat Screen TV',
                            'Mini Bar',
                            'Safe'
                        ],
                        'CancellationPolicy' => 'Free cancellation up to 24 hours before check-in'
                    ],
                    [
                        'RoomTypeCode' => 'SUITE',
                        'RoomTypeName' => 'Executive Suite',
                        'RoomDescription' => 'Luxurious suite with separate living area, premium amenities, and stunning city views.',
                        'MaxOccupancy' => 4,
                        'RoomSize' => '55 sqm',
                        'BedType' => 'King Size Bed + Sofa Bed',
                        'TotalFare' => $basePrice + 1500,
                        'Currency' => 'INR',
                        'MealType' => 'Breakfast Included',
                        'IsRefundable' => true,
                        'RoomIndex' => 2,
                        'RoomImages' => [
                            'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=800&q=60'
                        ],
                        'Amenities' => [
                            'Free WiFi',
                            'Air Conditioning',
                            'Room Service',
                            'Flat Screen TV',
                            'Mini Bar',
                            'Safe',
                            'Separate Living Area',
                            'Premium Bathroom',
                            'Complimentary Breakfast'
                        ],
                        'CancellationPolicy' => 'Free cancellation up to 48 hours before check-in'
                    ],
                    [
                        'RoomTypeCode' => 'PREMIUM',
                        'RoomTypeName' => 'Premium Room',
                        'RoomDescription' => 'Premium room with elegant decor, enhanced amenities, and excellent service.',
                        'MaxOccupancy' => 2,
                        'RoomSize' => '28 sqm',
                        'BedType' => 'Queen Size Bed',
                        'TotalFare' => $basePrice + 800,
                        'Currency' => 'INR',
                        'MealType' => 'Room Only',
                        'IsRefundable' => false,
                        'RoomIndex' => 3,
                        'RoomImages' => [
                            'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=800&q=60'
                        ],
                        'Amenities' => [
                            'Free WiFi',
                            'Air Conditioning',
                            'Room Service',
                            'Flat Screen TV',
                            'Coffee Maker',
                            'Work Desk'
                        ],
                        'CancellationPolicy' => 'Non-refundable'
                    ]
                ],
                'facilities' => [
                    '24/7 Front Desk',
                    'Restaurant',
                    'Room Service',
                    'Free WiFi',
                    'Fitness Center',
                    'Business Center',
                    'Concierge Service',
                    'Laundry Service',
                    'Airport Shuttle',
                    'Parking Available'
                ]
            ];
            
            error_log("[Simple TBO Hotel Details] Returning mock data for hotel: $hotelName");
            return $hotelData;
            
        } catch (Exception $e) {
            error_log("[Simple TBO Hotel Details] Error: " . $e->getMessage());
            throw $e;
        }
    }
}
