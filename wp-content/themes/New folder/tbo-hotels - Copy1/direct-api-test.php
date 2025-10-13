<?php
/**
 * TBO Hotels API Direct Test Tool
 * 
 * This script directly tests the API endpoints using cURL
 */

// Include WordPress core - only needed for constants and authentication
require_once('../../../wp-load.php');

// Set headers for output
header('Content-Type: text/html; charset=utf-8');

// Define API credentials
$api_username = TBO_API_USERNAME;
$api_password = TBO_API_PASSWORD;
$api_base_url = TBO_API_BASE_URL;

// Handle form submission
$endpoint = isset($_POST['endpoint']) ? $_POST['endpoint'] : 'HotelCodeList';
$request_data = isset($_POST['request_data']) ? $_POST['request_data'] : '{"DestinationCode":"268880"}';
$response = null;
$error = null;

if (isset($_POST['submit'])) {
    try {
        // Parse the request data
        $data = json_decode($request_data, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error = 'Invalid JSON in request data: ' . json_last_error_msg();
        } else {
            // Make the API request
            $response = sendApiRequest($api_base_url, $endpoint, $data, $api_username, $api_password);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

/**
 * Send API request using cURL
 */
function sendApiRequest($base_url, $endpoint, $data, $username, $password) {
    $url = rtrim($base_url, '/') . '/' . $endpoint;
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json',
        'Authorization: Basic ' . base64_encode($username . ':' . $password)
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    // Enable verbose output
    $verbose = fopen('php://temp', 'w+');
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, $verbose);
    
    // Execute the request
    $response_body = curl_exec($ch);
    $error = curl_error($ch);
    $info = curl_getinfo($ch);
    
    // Get verbose info
    rewind($verbose);
    $verbose_log = stream_get_contents($verbose);
    
    // Close cURL
    curl_close($ch);
    
    // Check for cURL errors
    if ($error) {
        throw new Exception('cURL Error: ' . $error);
    }
    
    // Decode the response
    $decoded = json_decode($response_body, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('JSON Parse Error: ' . json_last_error_msg());
    }
    
    // Return complete information
    return array(
        'response' => $decoded,
        'info' => $info,
        'verbose' => $verbose_log,
        'raw' => $response_body
    );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>TBO Hotels - Direct API Test Tool</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
        h1, h2, h3 { color: #333; }
        .container { max-width: 1200px; margin: 0 auto; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto; max-height: 500px; }
        .success { color: green; }
        .error { color: red; }
        .warning { color: orange; }
        form { margin: 20px 0; padding: 20px; background: #f9f9f9; border-radius: 5px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], select, textarea { padding: 8px; width: 100%; margin-bottom: 15px; box-sizing: border-box; }
        textarea { height: 150px; font-family: monospace; }
        button { padding: 10px 15px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        .results { margin-top: 20px; }
        .tabs { display: flex; margin-bottom: 0; }
        .tab { padding: 10px 15px; background: #eee; border: 1px solid #ddd; border-bottom: none; cursor: pointer; margin-right: 5px; }
        .tab.active { background: #f5f5f5; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .response-container { border: 1px solid #ddd; border-radius: 0 5px 5px 5px; }
        .code-block { background: #f0f0f0; padding: 10px; border-radius: 5px; margin: 10px 0; }
        .template-selector { margin-bottom: 15px; }
        .template-selector button { margin-right: 5px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>TBO Hotels - Direct API Test Tool</h1>
        
        <form method="post" action="">
            <h2>API Request</h2>
            
            <div>
                <label for="endpoint">API Endpoint:</label>
                <select id="endpoint" name="endpoint">
                    <option value="CountryList" <?php echo $endpoint === 'CountryList' ? 'selected' : ''; ?>>CountryList</option>
                    <option value="CityList" <?php echo $endpoint === 'CityList' ? 'selected' : ''; ?>>CityList</option>
                    <option value="HotelCodeList" <?php echo $endpoint === 'HotelCodeList' ? 'selected' : ''; ?>>HotelCodeList</option>
                    <option value="HotelSearch" <?php echo $endpoint === 'HotelSearch' ? 'selected' : ''; ?>>HotelSearch</option>
                </select>
            </div>
            
            <div class="template-selector">
                <label>Request Templates:</label>
                <button type="button" onclick="loadTemplate('CountryList')">CountryList</button>
                <button type="button" onclick="loadTemplate('CityList')">CityList (India)</button>
                <button type="button" onclick="loadTemplate('HotelCodeList')">HotelCodeList (Mumbai)</button>
                <button type="button" onclick="loadTemplate('HotelSearch')">HotelSearch</button>
            </div>
            
            <div>
                <label for="request_data">Request Data (JSON):</label>
                <textarea id="request_data" name="request_data"><?php echo htmlspecialchars($request_data); ?></textarea>
            </div>
            
            <div>
                <button type="submit" name="submit">Send Request</button>
            </div>
        </form>
        
        <?php if ($error): ?>
            <div class="results">
                <h2>Error</h2>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php elseif ($response): ?>
            <div class="results">
                <h2>API Response</h2>
                
                <div class="tabs">
                    <div class="tab active" onclick="showTab('response')">Response</div>
                    <div class="tab" onclick="showTab('raw')">Raw</div>
                    <div class="tab" onclick="showTab('info')">Info</div>
                    <div class="tab" onclick="showTab('verbose')">Verbose</div>
                    <div class="tab" onclick="showTab('analyze')">Analysis</div>
                </div>
                
                <div class="response-container">
                    <div id="response" class="tab-content active">
                        <pre><?php echo htmlspecialchars(json_encode($response['response'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                    
                    <div id="raw" class="tab-content">
                        <pre><?php echo htmlspecialchars($response['raw']); ?></pre>
                    </div>
                    
                    <div id="info" class="tab-content">
                        <pre><?php echo htmlspecialchars(json_encode($response['info'], JSON_PRETTY_PRINT)); ?></pre>
                    </div>
                    
                    <div id="verbose" class="tab-content">
                        <pre><?php echo htmlspecialchars($response['verbose']); ?></pre>
                    </div>
                    
                    <div id="analyze" class="tab-content">
                        <h3>Response Analysis</h3>
                        <?php
                        $data = $response['response'];
                        echo '<p>HTTP Status: <strong>' . $response['info']['http_code'] . '</strong></p>';
                        echo '<p>Response Time: <strong>' . $response['info']['total_time'] . 's</strong></p>';
                        
                        if ($endpoint === 'CountryList') {
                            if (isset($data['CountryList']) && is_array($data['CountryList'])) {
                                echo '<p class="success">Found ' . count($data['CountryList']) . ' countries</p>';
                                echo '<h4>Sample Countries:</h4>';
                                echo '<pre>' . htmlspecialchars(json_encode(array_slice($data['CountryList'], 0, 5), JSON_PRETTY_PRINT)) . '</pre>';
                            } else {
                                echo '<p class="error">No countries found in response</p>';
                            }
                        } elseif ($endpoint === 'CityList') {
                            if (isset($data['CityList']) && is_array($data['CityList'])) {
                                echo '<p class="success">Found ' . count($data['CityList']) . ' cities</p>';
                                echo '<h4>Sample Cities:</h4>';
                                echo '<pre>' . htmlspecialchars(json_encode(array_slice($data['CityList'], 0, 5), JSON_PRETTY_PRINT)) . '</pre>';
                            } else {
                                echo '<p class="error">No cities found in response</p>';
                            }
                        } elseif ($endpoint === 'HotelCodeList') {
                            // Check different possible structures
                            $hotelCodes = [];
                            if (isset($data['HotelCodes']) && is_array($data['HotelCodes'])) {
                                $hotelCodes = $data['HotelCodes'];
                                echo '<p class="success">Found ' . count($hotelCodes) . ' hotel codes in HotelCodes property</p>';
                            } elseif (isset($data['Result']) && is_array($data['Result'])) {
                                $hotelCodes = $data['Result'];
                                echo '<p class="success">Found ' . count($hotelCodes) . ' hotel codes in Result property</p>';
                            } elseif (isset($data['HotelCodesArray']) && is_array($data['HotelCodesArray'])) {
                                $hotelCodes = $data['HotelCodesArray'];
                                echo '<p class="success">Found ' . count($hotelCodes) . ' hotel codes in HotelCodesArray property</p>';
                            } else {
                                echo '<p class="error">No hotel codes found in standard properties</p>';
                                echo '<p>Response keys: ' . implode(', ', array_keys($data)) . '</p>';
                            }
                            
                            if (!empty($hotelCodes)) {
                                echo '<h4>Sample Hotel Codes:</h4>';
                                echo '<pre>' . htmlspecialchars(json_encode(array_slice($hotelCodes, 0, 10), JSON_PRETTY_PRINT)) . '</pre>';
                                
                                // Analyze code types
                                $typeCounter = [];
                                $lengthCounter = [];
                                foreach ($hotelCodes as $code) {
                                    $type = gettype($code);
                                    $typeCounter[$type] = ($typeCounter[$type] ?? 0) + 1;
                                    
                                    if (is_string($code) || is_numeric($code)) {
                                        $length = strlen((string)$code);
                                        $lengthCounter[$length] = ($lengthCounter[$length] ?? 0) + 1;
                                    }
                                }
                                
                                echo '<h4>Code Types:</h4>';
                                echo '<ul>';
                                foreach ($typeCounter as $type => $count) {
                                    echo '<li>' . $type . ': ' . $count . '</li>';
                                }
                                echo '</ul>';
                                
                                echo '<h4>Code Lengths:</h4>';
                                echo '<ul>';
                                ksort($lengthCounter);
                                foreach ($lengthCounter as $length => $count) {
                                    echo '<li>Length ' . $length . ': ' . $count . '</li>';
                                }
                                echo '</ul>';
                                
                                // Check filter validity
                                $validCodes = array_filter($hotelCodes, function($code) {
                                    return is_numeric($code) && strlen((string)$code) >= 5;
                                });
                                echo '<p>Codes valid for filtering (numeric & length >= 5): ' . count($validCodes) . 
                                     ' (' . round(count($validCodes) / count($hotelCodes) * 100, 2) . '%)</p>';
                            }
                        } elseif ($endpoint === 'HotelSearch') {
                            // Analyze HotelSearch response
                            $hotels = [];
                            if (isset($data['Hotels']) && is_array($data['Hotels'])) {
                                $hotels = $data['Hotels'];
                                echo '<p class="success">Found ' . count($hotels) . ' hotels in Hotels property</p>';
                            } elseif (isset($data['HotelResult']) && is_array($data['HotelResult'])) {
                                $hotels = $data['HotelResult'];
                                echo '<p class="success">Found ' . count($hotels) . ' hotels in HotelResult property</p>';
                            } elseif (isset($data['Result']) && is_array($data['Result'])) {
                                $hotels = $data['Result'];
                                echo '<p class="success">Found ' . count($hotels) . ' hotels in Result property</p>';
                            } else {
                                echo '<p class="error">No hotels found in standard properties</p>';
                                echo '<p>Response keys: ' . implode(', ', array_keys($data)) . '</p>';
                            }
                            
                            if (!empty($hotels)) {
                                echo '<h4>Sample Hotel Data:</h4>';
                                echo '<pre>' . htmlspecialchars(json_encode(array_slice($hotels, 0, 1), JSON_PRETTY_PRINT)) . '</pre>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <h2>Common Request Examples</h2>
        
        <h3>CountryList Request</h3>
        <div class="code-block">
            <pre>
{
    // Empty JSON object for CountryList
}
            </pre>
        </div>
        
        <h3>CityList Request</h3>
        <div class="code-block">
            <pre>
{
    "CountryCode": "IN"
}
            </pre>
        </div>
        
        <h3>HotelCodeList Request</h3>
        <div class="code-block">
            <pre>
{
    "DestinationCode": "268880"
}
            </pre>
        </div>
        
        <h3>HotelSearch Request</h3>
        <div class="code-block">
            <pre>
{
    "CheckIn": "2025-09-23",
    "CheckOut": "2025-09-26",
    "HotelCodes": "268880,268881,268882",
    "GuestNationality": "IN",
    "PaxRooms": [
        {
            "Adults": 2,
            "Children": 0,
            "ChildrenAges": []
        }
    ]
}
            </pre>
        </div>
        
        <script>
            // Function to show selected tab
            function showTab(tabId) {
                // Hide all tab contents
                var tabContents = document.getElementsByClassName('tab-content');
                for (var i = 0; i < tabContents.length; i++) {
                    tabContents[i].classList.remove('active');
                }
                
                // Deactivate all tabs
                var tabs = document.getElementsByClassName('tab');
                for (var i = 0; i < tabs.length; i++) {
                    tabs[i].classList.remove('active');
                }
                
                // Activate the selected tab and content
                document.getElementById(tabId).classList.add('active');
                document.querySelector('.tab[onclick="showTab(\'' + tabId + '\')"]').classList.add('active');
            }
            
            // Function to load template
            function loadTemplate(template) {
                var requestData = document.getElementById('request_data');
                var endpointSelect = document.getElementById('endpoint');
                
                // Set the endpoint
                endpointSelect.value = template;
                
                // Set the request data based on template
                switch (template) {
                    case 'CountryList':
                        requestData.value = '{}';
                        break;
                    case 'CityList':
                        requestData.value = '{\n    "CountryCode": "IN"\n}';
                        break;
                    case 'HotelCodeList':
                        requestData.value = '{\n    "DestinationCode": "268880"\n}';
                        break;
                    case 'HotelSearch':
                        requestData.value = '{\n    "CheckIn": "2025-09-23",\n    "CheckOut": "2025-09-26",\n    "HotelCodes": "268880,268881,268882",\n    "GuestNationality": "IN",\n    "PaxRooms": [\n        {\n            "Adults": 2,\n            "Children": 0,\n            "ChildrenAges": []\n        }\n    ]\n}';
                        break;
                }
            }
        </script>
    </div>
</body>
</html>