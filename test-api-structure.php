<?php
// Simple debug script to check TBO API response structure
require_once 'wp-load.php';

// Direct API test with minimal data
$api_url = 'https://api.tboholidays.com/TBOHolidays_HotelAPI/HotelSearch';
$credentials = 'YOLANDATHTest:Yol@40360746';

$search_data = array(
    'CheckIn' => date('Y-m-d', strtotime('+7 days')),
    'CheckOut' => date('Y-m-d', strtotime('+9 days')),
    'HotelCodes' => array('150184-67742'), // Just one hotel code
    'GuestNationality' => 'AE',
    'PaxRooms' => array(
        array(
            'Adults' => 2,
            'Children' => 0,
            'ChildrenAges' => array()
        )
    )
);

$headers = array(
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($credentials)
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($search_data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "cURL Error: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: $http_code\n";
    
    if ($response) {
        $data = json_decode($response, true);
        if ($data) {
            echo "Response keys: " . implode(', ', array_keys($data)) . "\n\n";
            
            if (isset($data['HotelResult']) && is_array($data['HotelResult']) && count($data['HotelResult']) > 0) {
                echo "Found " . count($data['HotelResult']) . " hotels\n\n";
                $firstHotel = $data['HotelResult'][0];
                echo "First hotel keys: " . implode(', ', array_keys($firstHotel)) . "\n\n";
                
                // Look for name fields
                foreach ($firstHotel as $key => $value) {
                    if (stripos($key, 'name') !== false || stripos($key, 'hotel') !== false) {
                        echo "$key: " . (is_string($value) ? $value : json_encode($value)) . "\n";
                    }
                }
            } else {
                echo "No HotelResult found\n";
                if (isset($data['Error'])) {
                    echo "Error: " . print_r($data['Error'], true) . "\n";
                }
            }
        } else {
            echo "Failed to decode JSON response\n";
            echo "Raw response: " . substr($response, 0, 500) . "\n";
        }
    } else {
        echo "Empty response\n";
    }
}

curl_close($ch);
?>