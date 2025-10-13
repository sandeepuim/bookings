<?php
/**
 * TBO Hotels Function Diagnostic Page
 * 
 * This page tests if the TBO Hotels functions are loaded properly
 */

// Load WordPress core
require_once(__DIR__ . '/wp-load.php');

// Start output buffering to capture any errors
ob_start();

// Initialize output
$output = array(
    'status' => 'success',
    'wordpress_loaded' => true,
    'functions' => array(),
    'constants' => array(),
    'includes' => array(),
    'theme_info' => array()
);

// Check WordPress functions
$output['functions']['get_template_directory'] = function_exists('get_template_directory');
$output['functions']['get_stylesheet_directory'] = function_exists('get_stylesheet_directory');
$output['functions']['get_header'] = function_exists('get_header');
$output['functions']['get_footer'] = function_exists('get_footer');

// Check TBO Hotels functions
$output['functions']['tbo_hotels_get_room_details'] = function_exists('tbo_hotels_get_room_details');
$output['functions']['tbo_hotels_api_request'] = function_exists('tbo_hotels_api_request');
$output['functions']['tbo_hotels_get_auth_header'] = function_exists('tbo_hotels_get_auth_header');
$output['functions']['tbo_hotels_add_direct_button_fix'] = function_exists('tbo_hotels_add_direct_button_fix');
$output['functions']['tbo_hotels_enhance_result_buttons'] = function_exists('tbo_hotels_enhance_result_buttons');

// Check theme information
$output['theme_info']['active_theme'] = wp_get_theme()->get('Name');
$output['theme_info']['theme_version'] = wp_get_theme()->get('Version');
$output['theme_info']['theme_path'] = get_template_directory();
$output['theme_info']['stylesheet_path'] = get_stylesheet_directory();

// Check constants
$constants = array(
    'ABSPATH', 
    'WP_CONTENT_DIR', 
    'WP_CONTENT_URL'
);

// TBO Hotels constants
$tbo_constants = array(
    'TBO_HOTELS_VERSION',
    'TBO_HOTELS_DIR',
    'TBO_HOTELS_URI',
    'TBO_API_BASE_URL',
    'TBO_API_USERNAME',
    'TBO_API_PASSWORD'
);

foreach ($constants as $constant) {
    $output['constants'][$constant] = defined($constant) ? 'Defined' : 'Not defined';
}

foreach ($tbo_constants as $constant) {
    $output['constants'][$constant] = defined($constant) ? 'Defined' : 'Not defined';
}

// Check include files
$include_files = array(
    TBO_HOTELS_DIR . '/includes/tbo-hotels-room-api.php',
    TBO_HOTELS_DIR . '/includes/room-button-fix.php',
    TBO_HOTELS_DIR . '/../twentytwentyone/tbo-room-functions.php',
    TBO_HOTELS_DIR . '/../twentytwentyone/direct-button-fix.php',
    TBO_HOTELS_DIR . '/../twentytwentyone/hotel-button-enhancement.php'
);

foreach ($include_files as $file) {
    $output['includes'][$file] = file_exists($file) ? 'Exists' : 'Not found';
}

// Test the function with dummy data
if ($output['functions']['tbo_hotels_get_room_details']) {
    try {
        $params = array(
            'hotel_code' => 'TEST123',
            'city_code' => 'TEST',
            'check_in' => '2023-12-01',
            'check_out' => '2023-12-05',
            'adults' => 2,
            'children' => 0,
            'rooms' => 1
        );
        
        $result = tbo_hotels_get_room_details($params);
        $output['function_test'] = array(
            'params' => $params,
            'result' => $result ? 'Success' : 'Failed',
            'data_sample' => is_array($result) ? 'Data returned' : 'No data'
        );
    } catch (Exception $e) {
        $output['function_test'] = array(
            'result' => 'Exception',
            'message' => $e->getMessage()
        );
    }
}

// End output buffering and check for errors
$errors = ob_get_clean();
if (!empty($errors)) {
    $output['status'] = 'error';
    $output['errors'] = $errors;
}

// Send JSON response if requested
if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json');
    echo json_encode($output, JSON_PRETTY_PRINT);
    exit;
}

// HTML output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels Function Diagnostic</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        h1 {
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .success {
            color: green;
        }
        .error {
            color: red;
        }
        .warning {
            color: orange;
        }
        .section {
            margin-bottom: 30px;
        }
        pre {
            background-color: #f5f5f5;
            padding: 10px;
            overflow-x: auto;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <h1>TBO Hotels Function Diagnostic</h1>
    
    <div class="section">
        <h2>WordPress Status</h2>
        <p class="<?php echo $output['wordpress_loaded'] ? 'success' : 'error'; ?>">
            WordPress Core: <?php echo $output['wordpress_loaded'] ? 'Loaded successfully' : 'Failed to load'; ?>
        </p>
    </div>
    
    <div class="section">
        <h2>Theme Information</h2>
        <table>
            <tr>
                <th>Setting</th>
                <th>Value</th>
            </tr>
            <?php foreach ($output['theme_info'] as $key => $value): ?>
                <tr>
                    <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                    <td><?php echo $value; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Required Functions</h2>
        <table>
            <tr>
                <th>Function</th>
                <th>Status</th>
            </tr>
            <?php foreach ($output['functions'] as $function => $exists): ?>
                <tr>
                    <td><?php echo $function; ?>()</td>
                    <td class="<?php echo $exists ? 'success' : 'error'; ?>">
                        <?php echo $exists ? 'Available' : 'Not available'; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Constants</h2>
        <table>
            <tr>
                <th>Constant</th>
                <th>Status</th>
            </tr>
            <?php foreach ($output['constants'] as $constant => $status): ?>
                <tr>
                    <td><?php echo $constant; ?></td>
                    <td class="<?php echo $status === 'Defined' ? 'success' : 'error'; ?>">
                        <?php echo $status; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Include Files</h2>
        <table>
            <tr>
                <th>File</th>
                <th>Status</th>
            </tr>
            <?php foreach ($output['includes'] as $file => $status): ?>
                <tr>
                    <td><?php echo $file; ?></td>
                    <td class="<?php echo $status === 'Exists' ? 'success' : 'error'; ?>">
                        <?php echo $status; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <?php if (isset($output['function_test'])): ?>
    <div class="section">
        <h2>Function Test: tbo_hotels_get_room_details()</h2>
        <table>
            <tr>
                <th>Test</th>
                <th>Result</th>
            </tr>
            <?php foreach ($output['function_test'] as $key => $value): ?>
                <tr>
                    <td><?php echo ucwords(str_replace('_', ' ', $key)); ?></td>
                    <td>
                        <?php 
                        if (is_array($value)) {
                            echo '<pre>' . print_r($value, true) . '</pre>';
                        } else {
                            echo $value;
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
    <?php endif; ?>
    
    <?php if (isset($output['errors']) && !empty($output['errors'])): ?>
    <div class="section">
        <h2>Errors</h2>
        <pre class="error"><?php echo $output['errors']; ?></pre>
    </div>
    <?php endif; ?>
    
    <div class="section">
        <h2>Full Diagnostic Data</h2>
        <pre><?php echo json_encode($output, JSON_PRETTY_PRINT); ?></pre>
    </div>
    
    <div class="section">
        <p>
            <a href="<?php echo $_SERVER['PHP_SELF']; ?>?format=json">View as JSON</a> | 
            <a href="hotel-room-selection.php?hotel_code=5678&city_code=BOM&check_in=2023-12-10&check_out=2023-12-15&adults=2&children=1&rooms=1&debug=1">Test Room Selection Page</a>
        </p>
    </div>
</body>
</html>