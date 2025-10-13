<?php
/**
 * AJAX Output Buffer Diagnostic and Fix
 * 
 * This file diagnoses and fixes issues with output buffering
 * that can cause AJAX responses to fail in WordPress.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../..');
    require_once(ABSPATH . '/wp-load.php');
}

// Add header to indicate this is a diagnostic page
header('Content-Type: text/html');
echo "<h1>AJAX Output Buffer Diagnostic</h1>";

// Function to test AJAX output
function test_ajax_output() {
    // Start output buffering to capture any unexpected output
    ob_start();
    
    // Call the city AJAX handler function directly
    echo "<h2>Testing tbo_hotels_ajax_get_cities function</h2>";
    
    // Setup test parameters
    $_POST['country_code'] = 'IN';
    
    // Capture what would normally be output
    try {
        // Don't actually execute wp_send_json_* functions
        function wp_send_json_success($data) {
            echo "<div class='success'><strong>Success Response:</strong> ";
            echo "<pre>" . json_encode($data, JSON_PRETTY_PRINT) . "</pre></div>";
            return true;
        }
        
        function wp_send_json_error($message) {
            echo "<div class='error'><strong>Error Response:</strong> ";
            echo "<pre>" . json_encode($message, JSON_PRETTY_PRINT) . "</pre></div>";
            return true;
        }
        
        // Call the function
        tbo_hotels_ajax_get_cities();
        
    } catch (Exception $e) {
        echo "<p>Exception: " . $e->getMessage() . "</p>";
    }
    
    // Get anything that might have been output before the JSON response
    $output = ob_get_clean();
    
    echo "<h3>Content before JSON response:</h3>";
    if (trim($output)) {
        echo "<div style='background: #ffdddd; padding: 10px; border: 1px solid #ff0000;'>";
        echo "<p><strong>Problem detected:</strong> Content was output before the JSON response.</p>";
        echo "<pre>" . htmlspecialchars(substr($output, 0, 500)) . (strlen($output) > 500 ? "..." : "") . "</pre>";
        echo "</div>";
    } else {
        echo "<div style='background: #ddffdd; padding: 10px; border: 1px solid #00ff00;'>";
        echo "<p><strong>Good:</strong> No content was output before the JSON response.</p>";
        echo "</div>";
    }
}

// Function to provide the fix
function ajax_output_buffer_fix() {
    echo "<h2>Implementing AJAX Output Buffer Fix</h2>";
    
    $fix_code = <<<'EOT'
/**
 * AJAX Output Buffer Fix
 * Prevents premature output before AJAX responses
 */
function tbo_hotels_ajax_output_buffer_fix() {
    // Only apply to our AJAX actions
    $our_actions = array(
        'tbo_hotels_get_countries',
        'tbo_hotels_get_cities',
        'tbo_hotels_search_hotels'
    );
    
    if (
        defined('DOING_AJAX') && 
        DOING_AJAX && 
        isset($_REQUEST['action']) && 
        in_array($_REQUEST['action'], $our_actions)
    ) {
        // Start output buffering
        ob_start();
    }
}
add_action('init', 'tbo_hotels_ajax_output_buffer_fix', 1);
EOT;

    echo "<p>Add the following code to your theme's functions.php file:</p>";
    echo "<pre style='background: #eaeaea; padding: 10px;'>" . htmlspecialchars($fix_code) . "</pre>";
    
    // Create the fix file
    $fix_file_path = dirname(__FILE__) . '/ajax-buffer-fix.php';
    file_put_contents($fix_file_path, "<?php\n" . $fix_code);
    
    echo "<p>Fix has been saved to: <code>" . basename($fix_file_path) . "</code></p>";
    echo "<p>Include this file in your theme's functions.php with:</p>";
    echo "<pre>require_once(get_template_directory() . '/includes/ajax-buffer-fix.php');</pre>";
}

// Run the tests
echo "<div style='max-width: 800px; margin: 0 auto;'>";
test_ajax_output();
ajax_output_buffer_fix();
echo "</div>";

// Provide implementation instructions
echo "<h2>Implementation Steps</h2>";
echo "<ol>";
echo "<li>Add the code above to your theme's functions.php file</li>";
echo "<li>Alternatively, include the generated ajax-buffer-fix.php file</li>";
echo "<li>Test the AJAX calls again after implementing the fix</li>";
echo "</ol>";

echo "<h2>Additional Diagnostic Information</h2>";
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";
echo "<p><strong>WordPress Version:</strong> " . (defined('WP_VERSION') ? WP_VERSION : 'Unknown') . "</p>";
echo "<p><strong>Output Buffering Level:</strong> " . ob_get_level() . "</p>";
echo "<p><strong>Output Buffering Handler:</strong> " . ob_get_status()['name'] . "</p>";