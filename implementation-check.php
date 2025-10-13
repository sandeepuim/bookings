<?php
/**
 * TBO Hotels Button Fix - Implementation Checker
 * 
 * This script checks if the button fix has been implemented correctly.
 */

// Basic settings
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start output buffering
ob_start();

// Define log file path
$logFile = __DIR__ . '/logs/implementation-check.log';

// Function to log message
function log_message($message, $type = 'INFO') {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp] [$type] $message\n";
    file_put_contents($logFile, $logEntry, FILE_APPEND);
}

// Function to check if a file exists and is readable
function check_file($filePath, $description) {
    $relativePath = str_replace(__DIR__ . '/', '', $filePath);
    if (file_exists($filePath) && is_readable($filePath)) {
        log_message("$description found: $relativePath", 'SUCCESS');
        return true;
    } else {
        log_message("$description not found: $relativePath", 'ERROR');
        return false;
    }
}

// Check for required files
$fixes = [
    'Hotel Results Fixed' => __DIR__ . '/hotel-results-fixed.php',
    'Button Debug Tool' => __DIR__ . '/button-debug.php',
    'Simple Room Selection' => __DIR__ . '/simple-room-selection.php',
    'Console Fix HTML' => __DIR__ . '/button-console-fix.html',
    'Button Click Logger' => __DIR__ . '/log-button-click.php'
];

// Start the check
log_message("Starting implementation check");

// Check each file
$totalFiles = count($fixes);
$foundFiles = 0;

foreach ($fixes as $description => $filePath) {
    if (check_file($filePath, $description)) {
        $foundFiles++;
    }
}

// Check WordPress theme directory for advanced-button-fix.js
$wp_loaded = file_exists('./wp-load.php');
if ($wp_loaded) {
    require_once('./wp-load.php');
    if (function_exists('get_template_directory')) {
        $theme_dir = get_template_directory();
        $advanced_js = $theme_dir . '/../twentytwentyone/advanced-button-fix.js';
        
        if (check_file($advanced_js, 'Advanced Button Fix JS')) {
            $foundFiles++;
            $totalFiles++;
        } else {
            log_message("Advanced Button Fix JS not found in theme directory", 'WARNING');
        }
    } else {
        log_message("WordPress functions not available", 'WARNING');
    }
} else {
    log_message("WordPress core not loaded", 'WARNING');
}

// Conclude check
$score = ($foundFiles / $totalFiles) * 100;
log_message("Implementation check complete: $foundFiles/$totalFiles files found ($score%)");

// Check for logs directory
if (is_dir(__DIR__ . '/logs') && is_writable(__DIR__ . '/logs')) {
    log_message("Logs directory exists and is writable", 'SUCCESS');
} else {
    log_message("Logs directory does not exist or is not writable", 'ERROR');
}

// Get total number of tests
$totalTests = $totalFiles + 1; // Files + logs directory
$passedTests = $foundFiles + (is_dir(__DIR__ . '/logs') && is_writable(__DIR__ . '/logs') ? 1 : 0);
$finalScore = ($passedTests / $totalTests) * 100;

log_message("Final implementation score: $passedTests/$totalTests tests passed ($finalScore%)");

// Generate the page output
$checkResult = [
    'timestamp' => date('Y-m-d H:i:s'),
    'total_tests' => $totalTests,
    'passed_tests' => $passedTests,
    'score' => $finalScore,
    'files_checked' => $fixes,
    'files_found' => $foundFiles,
    'logs_directory' => is_dir(__DIR__ . '/logs') && is_writable(__DIR__ . '/logs')
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TBO Hotels - Implementation Check</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #003580;
            color: white;
            padding: 15px 0;
        }
        .header-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .site-title {
            font-size: 22px;
            margin: 0;
        }
        .site-title a {
            color: white;
            text-decoration: none;
        }
        h2 {
            color: #003580;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }
        .card {
            background: white;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .score {
            font-size: 48px;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
        }
        .score.good {
            color: #2e7d32;
        }
        .score.warning {
            color: #f57c00;
        }
        .score.bad {
            color: #c62828;
        }
        .result-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .result-table th, .result-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .result-table th {
            background-color: #f2f2f2;
        }
        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: bold;
        }
        .status.pass {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .status.fail {
            background-color: #ffebee;
            color: #c62828;
        }
        .next-steps {
            margin-top: 20px;
        }
        .next-steps ul {
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1 class="site-title">TBO Hotels Implementation Check</h1>
            <div>
                <a href="hotel-results-fixed.php" style="color: white; margin-right: 15px;">Fixed Results</a>
                <a href="button-debug.php" style="color: white;">Debug Tools</a>
            </div>
        </div>
    </div>
    
    <div class="container">
        <h2>Implementation Check Results</h2>
        
        <div class="card">
            <div class="score <?php echo $finalScore >= 80 ? 'good' : ($finalScore >= 50 ? 'warning' : 'bad'); ?>">
                <?php echo round($finalScore); ?>%
            </div>
            
            <p style="text-align: center;">
                <?php echo $passedTests; ?> out of <?php echo $totalTests; ?> tests passed
            </p>
            
            <table class="result-table">
                <tr>
                    <th>Component</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($fixes as $description => $filePath): ?>
                <tr>
                    <td><?php echo htmlspecialchars($description); ?></td>
                    <td>
                        <?php if (file_exists($filePath) && is_readable($filePath)): ?>
                            <span class="status pass">PASS</span>
                        <?php else: ?>
                            <span class="status fail">FAIL</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td>Logs Directory</td>
                    <td>
                        <?php if (is_dir(__DIR__ . '/logs') && is_writable(__DIR__ . '/logs')): ?>
                            <span class="status pass">PASS</span>
                        <?php else: ?>
                            <span class="status fail">FAIL</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php if ($wp_loaded): ?>
                <tr>
                    <td>Advanced Button Fix JS</td>
                    <td>
                        <?php if (isset($advanced_js) && file_exists($advanced_js) && is_readable($advanced_js)): ?>
                            <span class="status pass">PASS</span>
                        <?php else: ?>
                            <span class="status fail">FAIL</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </table>
        </div>
        
        <div class="card next-steps">
            <h3>Next Steps</h3>
            
            <?php if ($finalScore >= 80): ?>
                <p>Congratulations! The button fix implementation is mostly complete. Here are some final recommendations:</p>
                <ul>
                    <li>Test the buttons on all your hotel results pages</li>
                    <li>Monitor the logs for any unexpected behavior</li>
                    <li>Consider implementing the missing components if any</li>
                </ul>
            <?php elseif ($finalScore >= 50): ?>
                <p>The implementation is partially complete. Here are some steps to improve it:</p>
                <ul>
                    <li>Check the failed components and implement them</li>
                    <li>Make sure the logs directory exists and is writable</li>
                    <li>Test the existing components to ensure they work correctly</li>
                </ul>
            <?php else: ?>
                <p>The implementation needs significant work. Here are the recommended steps:</p>
                <ul>
                    <li>Start by implementing the Hotel Results Fixed page</li>
                    <li>Create the Simple Room Selection page</li>
                    <li>Add the Button Debug Tool for troubleshooting</li>
                    <li>Create a logs directory with proper permissions</li>
                    <li>Implement the Button Click Logger</li>
                </ul>
            <?php endif; ?>
            
            <p>For additional help, visit the Button Debug Tool or contact support.</p>
        </div>
    </div>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>