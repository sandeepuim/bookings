<?php
// Simple standalone test
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h1>Testing Hotel Details - Standalone</h1>";

// Check if the TboApiClient file exists
$apiClientPath = 'wp-content/themes/Yolandabooking/inc/TboApiClient.php';
if (file_exists($apiClientPath)) {
    echo "<p>✓ TboApiClient.php found</p>";
    
    require_once $apiClientPath;
    echo "<p>✓ TboApiClient.php loaded successfully</p>";
    
    // Test if class exists
    if (class_exists('TboApiClient')) {
        echo "<p>✓ TboApiClient class exists</p>";
        
        // Create instance
        try {
            $tbo = new TboApiClient(
                'http://api.tbotechnology.in/TBOHolidays_HotelAPI',
                'YOLANDATHTest',
                'Yol@40360746'
            );
            echo "<p>✓ TboApiClient instance created</p>";
            
            // Test method
            if (method_exists($tbo, 'getHotelWithRooms')) {
                echo "<p>✓ getHotelWithRooms method exists</p>";
                
                $result = $tbo->getHotelWithRooms('HTL00004', '2025-09-28', '2025-09-29', 1, 0, 'IN');
                
                if (isset($result['HotelDetails'])) {
                    echo "<p>✅ SUCCESS: Hotel details found!</p>";
                    echo "<p>Hotel: " . $result['HotelDetails']['HotelName'] . "</p>";
                    echo "<p>Rooms available: " . count($result['Rooms']) . "</p>";
                } else {
                    echo "<p>❌ ERROR: HotelDetails not found in result</p>";
                    echo "<pre>" . print_r($result, true) . "</pre>";
                }
                
            } else {
                echo "<p>❌ getHotelWithRooms method does not exist</p>";
            }
            
        } catch (Exception $e) {
            echo "<p>❌ Exception: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p>❌ TboApiClient class not found</p>";
    }
    
} else {
    echo "<p>❌ TboApiClient.php not found at: $apiClientPath</p>";
}

echo "<h2>Current Directory Files:</h2>";
echo "<pre>" . print_r(scandir('.'), true) . "</pre>";

echo "<p>Test completed!</p>";
?>
