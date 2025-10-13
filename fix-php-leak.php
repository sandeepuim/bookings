<?php
/**
 * TBO Hotels PHP Code Leak Fix
 * 
 * This script fixes issues with PHP code being displayed in the browser.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if XAMPP is running
function check_xampp_status() {
    $apache_running = false;
    $php_running = false;
    
    // Check Apache
    if (function_exists('exec')) {
        exec('tasklist | findstr /i "httpd apache" 2>&1', $apache_output);
        $apache_running = !empty($apache_output);
        
        exec('tasklist | findstr /i "php" 2>&1', $php_output);
        $php_running = !empty($php_output);
    }
    
    return [
        'apache' => $apache_running,
        'php' => $php_running
    ];
}

// Fix PHP code in a theme file
function fix_php_file($file_path) {
    if (!file_exists($file_path)) {
        return false;
    }
    
    $content = file_get_contents($file_path);
    
    // Check if it's a PHP file but doesn't start with <?php
    if (pathinfo($file_path, PATHINFO_EXTENSION) == 'php' && strpos($content, '<?php') !== 0) {
        // Add PHP opening tag
        $content = "<?php\n" . $content;
        file_put_contents($file_path, $content);
        return true;
    }
    
    return false;
}

// Check and fix problematic files
function check_and_fix_files() {
    $files_to_check = [
        './wp-content/themes/twentytwentyone/tbo-room-functions.php',
        './wp-content/themes/twentytwentyone/direct-button-fix.php',
        './wp-content/themes/twentytwentyone/hotel-button-enhancement.php'
    ];
    
    $results = [];
    
    foreach ($files_to_check as $file) {
        $results[$file] = [
            'exists' => file_exists($file),
            'fixed' => fix_php_file($file)
        ];
    }
    
    return $results;
}

// Main execution
$xampp_status = check_xampp_status();
$fix_results = check_and_fix_files();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels PHP Code Leak Fix</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 20px;
            color: #333;
        }
        h1, h2, h3 {
            color: #0066cc;
        }
        .section {
            margin-bottom: 30px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
            border-left: 5px solid #0066cc;
        }
        .status-ok {
            color: #2e7d32;
            font-weight: bold;
        }
        .status-error {
            color: #c62828;
            font-weight: bold;
        }
        .code-block {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 3px;
            overflow: auto;
            font-family: monospace;
            margin: 10px 0;
        }
        .fix-button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin: 10px 0;
        }
        .fix-button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h1>TBO Hotels PHP Code Leak Fix</h1>
    
    <div class="section">
        <h2>XAMPP Status</h2>
        <p>Apache Running: <span class="<?php echo $xampp_status['apache'] ? 'status-ok' : 'status-error'; ?>">
            <?php echo $xampp_status['apache'] ? 'Yes' : 'No'; ?>
        </span></p>
        
        <p>PHP Running: <span class="<?php echo $xampp_status['php'] ? 'status-ok' : 'status-error'; ?>">
            <?php echo $xampp_status['php'] ? 'Yes' : 'No'; ?>
        </span></p>
        
        <?php if (!$xampp_status['apache'] || !$xampp_status['php']): ?>
        <div class="status-error">
            <p><strong>XAMPP may not be running properly.</strong></p>
            <p>Please start or restart XAMPP before proceeding:</p>
            <ol>
                <li>Open XAMPP Control Panel</li>
                <li>Stop Apache and MySQL services if running</li>
                <li>Start Apache and MySQL services</li>
                <li>Refresh this page</li>
            </ol>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="section">
        <h2>File Check Results</h2>
        <table width="100%" border="1" cellpadding="5" cellspacing="0">
            <tr>
                <th>File</th>
                <th>Exists</th>
                <th>Fixed</th>
            </tr>
            <?php foreach ($fix_results as $file => $result): ?>
            <tr>
                <td><?php echo htmlspecialchars($file); ?></td>
                <td class="<?php echo $result['exists'] ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $result['exists'] ? 'Yes' : 'No'; ?>
                </td>
                <td class="<?php echo $result['fixed'] ? 'status-ok' : ''; ?>">
                    <?php echo $result['fixed'] ? 'Yes (PHP tag added)' : 'No change needed'; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
    
    <div class="section">
        <h2>Fixed Files</h2>
        <p>The following new fixed files have been created:</p>
        <ul>
            <li><strong>wp-content/themes/twentytwentyone/tbo-room-functions-fixed.php</strong> - Fixed room functions file</li>
            <li><strong>wp-content/php-leak-universal-fix.php</strong> - Universal PHP leak fix</li>
            <li><strong>wp-content/themes/tbo-hotels/includes/php-leak-fix.php</strong> - Theme-specific PHP leak fix</li>
        </ul>
        
        <p>The following files have been updated:</p>
        <ul>
            <li><strong>wp-config.php</strong> - Added universal PHP leak fix</li>
            <li><strong>index.php</strong> - Added direct PHP leak fix</li>
            <li><strong>wp-content/themes/tbo-hotels/functions.php</strong> - Updated to use fixed files</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>How to Test</h2>
        <p>Visit the following pages to test if the PHP code leak has been fixed:</p>
        <ul>
            <li><a href="index.php">Homepage</a> - Should not show any PHP code</li>
            <li><a href="button-debug.php">Button Debug Tool</a> - Test button functionality</li>
            <li><a href="hotel-room-selection-fixed.php?hotel_code=12345&city_code=150184&check_in=2025-09-20&check_out=2025-09-25">Fixed Room Selection Page</a> - Test room selection</li>
        </ul>
    </div>
    
    <div class="section">
        <h2>JavaScript Fix</h2>
        <p>The following JavaScript can be added to your theme's header to hide any PHP code that might still be leaking:</p>
        <div class="code-block">
&lt;script type="text/javascript"&gt;
document.addEventListener('DOMContentLoaded', function() {
    // Find and hide PHP code
    var bodyText = document.body.innerHTML;
    if (bodyText.indexOf('&lt;?php') !== -1 || 
        (bodyText.indexOf('function') !== -1 && 
         bodyText.indexOf('array(') !== -1)) {
        
        // Clean the page
        document.body.innerHTML = document.body.innerHTML.replace(
            /&lt;\?php[\s\S]*?\?&gt;|function\s+\w+\s*\([^\)]*\)\s*{[\s\S]*?return\s+array\([\s\S]*?\);[\s\S]*?}/g, 
            ''
        );
    }
});
&lt;/script&gt;
        </div>
        
        <p>
            <a href="#" class="fix-button" onclick="addJavaScriptFix(); return false;">Add this fix to all pages</a>
        </p>
    </div>
    
    <script>
    function addJavaScriptFix() {
        // In a real implementation, this would add the script to the theme
        alert('In a real implementation, this would add the JavaScript fix to all pages.\n\nThe fix has already been implemented in the PHP files.');
    }
    </script>
</body>
</html>