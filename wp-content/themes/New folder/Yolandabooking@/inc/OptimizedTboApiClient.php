<?php
/**
 * Optimized TBO API Client
 */
class OptimizedTboApiClient {
    private $apiUrl;
    private $debug = true;
    private $batchSize = 20;

    public function __construct($apiUrl = 'http://api.tbotechnology.in/TBOHolidays_HotelAPI') {
        $this->apiUrl = rtrim($apiUrl, '/') . '/';
        
        // Set optimization settings
        ini_set('max_execution_time', '30');
        ini_set('memory_limit', '256M');
    }

    /**
     * Main optimized search function
     */
    public function searchHotels($params) {
        try {
            $startTime = microtime(true);
            error_log("[TBO Search] Starting optimized search");

            // Extract parameters
            $hotelCodes = $params['hotel_codes'] ?? null;
            $countryCode = $params['country_code'] ?? 'IN';
            $cityCode = $params['city_code'] ?? '';
            $checkIn = $params['check_in'] ?? '';
            $checkOut = $params['check_out'] ?? '';
            $rooms = $params['rooms'] ?? 1;
            $adults = $params['adults'] ?? 1;
            $children = $params['children'] ?? 0;

            // Get hotel codes (either from parameter or fetch from API)
            $processableHotelCodes = $this->getProcessableHotelCodes($hotelCodes, $cityCode);
            
            // Process hotels in batches
            $results = $this->processHotelBatches(
                $processableHotelCodes,
                $countryCode,
                $checkIn,
                $checkOut,
                $rooms,
                $adults,
                $children
            );

            $endTime = microtime(true);
            error_log("[TBO Search] Search completed in " . round($endTime - $startTime, 2) . " seconds");

            return $results;

        } catch (Exception $e) {
            error_log("[TBO Search] Error: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get the hotel codes to process
     */
    private function getProcessableHotelCodes($providedCodes, $cityCode) {
        if (!empty($providedCodes)) {
            $codes = is_array($providedCodes) ? $providedCodes : explode(',', $providedCodes);
            return array_slice($codes, 0, $this->batchSize);
        }

        // Try to get from cache first
        $cacheKey = 'hotel_codes_' . $cityCode;
        $cachedCodes = get_transient($cacheKey);
        
        if ($cachedCodes !== false) {
            return array_slice($cachedCodes, 0, $this->batchSize);
        }

        // Fetch from API if not in cache
        $codes = $this->fetchHotelCodesFromAPI($cityCode);
        set_transient($cacheKey, $codes, HOUR_IN_SECONDS);
        
        return array_slice($codes, 0, $this->batchSize);
    }

    /**
     * Fetch hotel codes from API
     */
    private function fetchHotelCodesFromAPI($cityCode) {
        $data = ['CityCode' => $cityCode];
        $response = $this->makeAPIRequest('HotelCodeList', $data, 'GET');
        
        if (!empty($response['HotelCodes'])) {
            return $response['HotelCodes'];
        }
        
        return [];
    }

    /**
     * Process hotels in batches
     */
    private function processHotelBatches($hotelCodes, $countryCode, $checkIn, $checkOut, $rooms, $adults, $children) {
        $batches = array_chunk($hotelCodes, 5); // Process 5 hotels at a time
        $allResults = ['Hotels' => []];

        foreach ($batches as $index => $batch) {
            try {
                $batchResults = $this->processSingleBatch(
                    $batch,
                    $countryCode,
                    $checkIn,
                    $checkOut,
                    $rooms,
                    $adults,
                    $children
                );

                if (!empty($batchResults)) {
                    $allResults['Hotels'] = array_merge($allResults['Hotels'], $batchResults);
                }

                // Small delay between batches
                if ($index < count($batches) - 1) {
                    usleep(100000); // 100ms delay
                }

            } catch (Exception $e) {
                error_log("[TBO Search] Batch {$index} error: " . $e->getMessage());
                continue;
            }
        }

        // Sort by price
        usort($allResults['Hotels'], function($a, $b) {
            $priceA = $a['CheapestRoom']['TotalFare'] ?? PHP_FLOAT_MAX;
            $priceB = $b['CheapestRoom']['TotalFare'] ?? PHP_FLOAT_MAX;
            return $priceA <=> $priceB;
        });

        return $allResults;
    }

    /**
     * Process a single batch of hotels
     */
    private function processSingleBatch($hotelCodes, $countryCode, $checkIn, $checkOut, $rooms, $adults, $children) {
        // Calculate room distribution
        $paxRooms = $this->calculateRoomDistribution($rooms, $adults, $children);

        // Prepare search data
        $searchData = [
            'CheckIn' => $checkIn,
            'CheckOut' => $checkOut,
            'HotelCodes' => implode(',', $hotelCodes),
            'GuestNationality' => $countryCode,
            'PaxRooms' => $paxRooms,
            'ResponseTime' => 20,
            'IsDetailedResponse' => true
        ];

        // Get availability
        $result = $this->makeAPIRequest('Search', $searchData, 'POST');
        
        if (empty($result['HotelResult']) && empty($result['Hotels'])) {
            return [];
        }

        // Get hotel details in bulk
        $hotelsToProcess = $result['HotelResult'] ?? $result['Hotels'] ?? [];
        return $this->enrichWithHotelDetails($hotelsToProcess);
    }

    /**
     * Enrich hotels with detailed information
     */
    private function enrichWithHotelDetails($hotels) {
        if (empty($hotels)) {
            return [];
        }

        $enrichedHotels = [];
        foreach ($hotels as $hotel) {
            try {
                if (empty($hotel['HotelCode'])) {
                    continue;
                }

                // Get hotel details
                $detailsData = [
                    'Hotelcodes' => $hotel['HotelCode'],
                    'Language' => 'en'
                ];
                
                $hotelDetails = $this->makeAPIRequest('Hoteldetails', $detailsData, 'POST');

                if (!empty($hotelDetails['Hotel'])) {
                    $details = $hotelDetails['Hotel'];
                    $hotel['HotelName'] = $details['HotelName'] ?? '';
                    $hotel['Description'] = $details['Description'] ?? '';
                    $hotel['Address'] = $details['Address'] ?? '';
                    $hotel['Images'] = $details['Images'] ?? [];
                    $hotel['Facilities'] = $details['Facilities'] ?? [];
                }

                // Find cheapest room
                $hotel['CheapestRoom'] = $this->findCheapestRoom($hotel);
                $enrichedHotels[] = $hotel;

            } catch (Exception $e) {
                error_log("[TBO Search] Error enriching hotel {$hotel['HotelCode']}: " . $e->getMessage());
                continue;
            }
        }

        return $enrichedHotels;
    }

    /**
     * Calculate room distribution
     */
    private function calculateRoomDistribution($rooms, $adults, $children) {
        $paxRooms = [];
        $adultsPerRoom = floor($adults / $rooms);
        $extraAdults = $adults % $rooms;
        
        for ($i = 0; $i < $rooms; $i++) {
            $room = [
                'Adults' => $adultsPerRoom + ($i < $extraAdults ? 1 : 0)
            ];
            
            if ($children > 0) {
                $childrenPerRoom = floor($children / $rooms);
                $extraChildren = $children % $rooms;
                $roomChildren = $childrenPerRoom + ($i < $extraChildren ? 1 : 0);
                
                if ($roomChildren > 0) {
                    $room['Children'] = $roomChildren;
                    $room['ChildAges'] = array_fill(0, $roomChildren, 5); // Default age 5
                }
            }
            
            $paxRooms[] = $room;
        }
        
        return $paxRooms;
    }

    /**
     * Find the cheapest room in a hotel
     */
    private function findCheapestRoom($hotel) {
        $cheapestRoom = null;
        $cheapestPrice = PHP_FLOAT_MAX;

        if (!empty($hotel['Rooms']) && is_array($hotel['Rooms'])) {
            foreach ($hotel['Rooms'] as $room) {
                if (isset($room['TotalFare']) && $room['TotalFare'] < $cheapestPrice) {
                    $cheapestPrice = $room['TotalFare'];
                    $cheapestRoom = $room;
                }
            }
        }

        return $cheapestRoom;
    }

    /**
     * Make an API request with optimized settings
     */
    private function makeAPIRequest($endpoint, $data, $method = 'POST') {
        $url = $this->apiUrl . $endpoint;
        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic WU9MQU5EQVRIVGVzdDpZb2xANDAzNjA3NDY='
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        $result = json_decode($response, true);
        if (!$result) {
            throw new Exception('Failed to decode API response');
        }
        
        return $result;
    }
}
