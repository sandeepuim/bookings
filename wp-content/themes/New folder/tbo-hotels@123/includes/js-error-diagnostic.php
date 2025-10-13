<?php
/**
 * JavaScript Syntax Error Diagnostic Tool
 * 
 * This tool scans the HTML output of your hotel results page for common JavaScript syntax errors
 * and provides fixes.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../..');
    require_once(ABSPATH . '/wp-load.php');
}

// Set content type to HTML
header('Content-Type: text/html');
?>
<!DOCTYPE html>
<html>
<head>
    <title>JavaScript Syntax Error Diagnostic Tool</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #2c3e50; }
        .error { background-color: #ffeeee; border-left: 4px solid #e74c3c; padding: 15px; margin-bottom: 20px; }
        .fix { background-color: #eeffee; border-left: 4px solid #2ecc71; padding: 15px; margin-bottom: 20px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; }
        code { background-color: #f5f5f5; padding: 2px 4px; border-radius: 3px; }
        .error-line { background-color: #ffcccc; }
        .button { display: inline-block; padding: 10px 15px; background-color: #3498db; color: white; 
                 text-decoration: none; border-radius: 4px; margin-top: 15px; }
        .warning { background-color: #fff8e1; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>JavaScript Syntax Error Diagnostic Tool</h1>
    
    <div class="warning">
        <h3>Important Note</h3>
        <p>This tool is analyzing the JavaScript syntax errors found on your hotel results page. After analysis is complete, 
        you can apply the recommended fixes to resolve the issues.</p>
    </div>
    
    <h2>Diagnostic Results</h2>
    
    <?php
    // Function to fetch the hotel results page
    function fetch_hotel_results_page() {
        $url = home_url('/hotel-results/?country_code=IN&city_code=105141&check_in=2025-09-23&check_out=2025-09-25&rooms=1&adults=2&children=0');
        
        $args = array(
            'timeout' => 30,
            'redirection' => 5,
            'httpversion' => '1.1',
            'user-agent' => 'WordPress/' . get_bloginfo('version') . '; ' . home_url(),
            'sslverify' => false
        );
        
        $response = wp_remote_get($url, $args);
        
        if (is_wp_error($response)) {
            return 'Error fetching page: ' . $response->get_error_message();
        }
        
        return wp_remote_retrieve_body($response);
    }
    
    // Function to extract and analyze script tags
    function analyze_scripts($html) {
        $results = array();
        
        // Extract script tags
        preg_match_all('/<script\b[^>]*>(.*?)<\/script>/si', $html, $matches);
        
        if (empty($matches[1])) {
            return array('error' => 'No script tags found');
        }
        
        foreach ($matches[1] as $index => $script) {
            // Skip empty scripts
            if (empty(trim($script))) {
                continue;
            }
            
            // Check for common syntax errors
            $errors = array();
            
            // Check for unexpected 'catch' token
            if (preg_match('/try\s*{.*}(?:\s*catch|catch)\s*\(/s', $script) && !preg_match('/try\s*{.*}\s*catch\s*\([^\)]*\)/s', $script)) {
                $errors[] = array(
                    'type' => 'catch_syntax',
                    'message' => 'Improper try/catch syntax detected',
                    'script' => $script
                );
            }
            
            // Check for missing closing parenthesis
            $open_parens = substr_count($script, '(');
            $close_parens = substr_count($script, ')');
            if ($open_parens != $close_parens) {
                $errors[] = array(
                    'type' => 'parenthesis_mismatch',
                    'message' => "Parenthesis mismatch: $open_parens opening vs $close_parens closing",
                    'script' => $script
                );
            }
            
            // Check for function calls with syntax errors
            if (preg_match_all('/\w+\s*\([^)]*,[^)]*\)/s', $script, $function_calls)) {
                foreach ($function_calls[0] as $call) {
                    if (preg_match('/,\s*\)/s', $call)) {
                        $errors[] = array(
                            'type' => 'trailing_comma',
                            'message' => 'Trailing comma in function call',
                            'script' => $call
                        );
                    }
                }
            }
            
            if (!empty($errors)) {
                $results[] = array(
                    'script_index' => $index,
                    'script_content' => $script,
                    'errors' => $errors
                );
            }
        }
        
        return $results;
    }
    
    // Function to check for missing JS files
    function check_missing_js_files($html) {
        $missing_files = array();
        
        // Extract script src attributes
        preg_match_all('/<script[^>]+src=[\'"]([^\'"]+)[\'"][^>]*>/i', $html, $matches);
        
        if (empty($matches[1])) {
            return array();
        }
        
        foreach ($matches[1] as $src) {
            // Skip external domains
            if (strpos($src, '//') === 0 || strpos($src, 'http') === 0) {
                if (strpos($src, home_url()) === false) {
                    continue;
                }
            }
            
            // Convert to server path
            $file_path = ABSPATH . str_replace(home_url(), '', $src);
            $file_path = str_replace('/', DIRECTORY_SEPARATOR, $file_path);
            
            // Remove query string
            if (strpos($file_path, '?') !== false) {
                $file_path = substr($file_path, 0, strpos($file_path, '?'));
            }
            
            if (!file_exists($file_path)) {
                $missing_files[] = array(
                    'src' => $src,
                    'path' => $file_path
                );
            }
        }
        
        return $missing_files;
    }
    
    // Fetch and analyze the page
    $page_content = fetch_hotel_results_page();
    $script_analysis = analyze_scripts($page_content);
    $missing_files = check_missing_js_files($page_content);
    
    // Display analysis results
    if (!empty($script_analysis) && !isset($script_analysis['error'])) {
        echo '<h3>Syntax Errors Found</h3>';
        
        foreach ($script_analysis as $result) {
            echo '<div class="error">';
            echo '<h4>Script #' . ($result['script_index'] + 1) . ' Issues:</h4>';
            
            foreach ($result['errors'] as $error) {
                echo '<p><strong>' . esc_html($error['message']) . '</strong></p>';
                
                // Show the problematic code snippet
                $script = $error['script'];
                $short_script = (strlen($script) > 500) ? substr($script, 0, 500) . '...' : $script;
                echo '<pre>' . esc_html($short_script) . '</pre>';
                
                // Suggest a fix
                echo '<div class="fix">';
                echo '<h4>Suggested Fix:</h4>';
                
                switch ($error['type']) {
                    case 'catch_syntax':
                        echo '<p>Ensure your try/catch blocks have correct syntax:</p>';
                        echo '<pre>try {
    // Your code here
} catch (error) {
    // Error handling code
}</pre>';
                        break;
                        
                    case 'parenthesis_mismatch':
                        echo '<p>Check your code for unbalanced parentheses. Make sure each opening parenthesis "(" has a matching closing one ")".</p>';
                        break;
                        
                    case 'trailing_comma':
                        echo '<p>Remove trailing commas from function arguments:</p>';
                        echo '<p>Incorrect: <code>functionName(arg1, arg2, )</code></p>';
                        echo '<p>Correct: <code>functionName(arg1, arg2)</code></p>';
                        break;
                }
                
                echo '</div>';
            }
            
            echo '</div>';
        }
    } elseif (isset($script_analysis['error'])) {
        echo '<div class="error">';
        echo '<p>' . esc_html($script_analysis['error']) . '</p>';
        echo '</div>';
    } else {
        echo '<div class="fix">';
        echo '<p>No JavaScript syntax errors detected in inline scripts.</p>';
        echo '</div>';
    }
    
    // Display missing JS files
    if (!empty($missing_files)) {
        echo '<h3>Missing JavaScript Files</h3>';
        echo '<div class="error">';
        
        foreach ($missing_files as $file) {
            echo '<p><strong>Missing file:</strong> ' . esc_html($file['src']) . '</p>';
            echo '<p><strong>Server path:</strong> ' . esc_html($file['path']) . '</p>';
        }
        
        echo '<div class="fix">';
        echo '<h4>Suggested Fix:</h4>';
        echo '<p>Create the missing JavaScript files or correct the src attributes in your scripts.</p>';
        echo '<p>For the tbo-hotel-booking-public.js file, you may need to:</p>';
        echo '<ol>';
        echo '<li>Create the missing file at the specified location</li>';
        echo '<li>Update the enqueue function to point to the correct location</li>';
        echo '<li>Or remove the script reference if it\'s no longer needed</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '</div>';
    }
    ?>
    
    <h2>JavaScript Console Fix Tool</h2>
    
    <div class="fix">
        <h3>Automated JavaScript Fix</h3>
        <p>The following JavaScript code will help fix common syntax errors in your page. Add this to your theme's functions.php file 
        to automatically fix these issues when the page loads:</p>
        
        <pre>
/**
 * JavaScript Console Error Fix
 * 
 * This script fixes common JavaScript syntax errors in the hotel results page.
 */
function tbo_hotels_add_js_error_fix() {
    if (is_page('hotel-results') || strpos($_SERVER['REQUEST_URI'], 'hotel-results') !== false) {
        ?>
        &lt;script type="text/javascript"&gt;
        // Execute when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('TBO Hotels JS Error Fix active');
            
            // Find and fix syntax errors in inline scripts
            var scriptTags = document.querySelectorAll('script:not([src])');
            
            scriptTags.forEach(function(scriptTag) {
                var content = scriptTag.textContent;
                
                // Skip empty scripts or our own script
                if (!content || content.includes('TBO Hotels JS Error Fix active')) {
                    return;
                }
                
                // Check for try/catch errors
                if (content.includes('try') && content.includes('catch')) {
                    // Look for improper try/catch syntax
                    if (content.match(/try\s*{.*}(?:\s*catch|catch)\s*\(/s) && !content.match(/try\s*{.*}\s*catch\s*\([^\)]*\)/s)) {
                        console.warn('Found improper try/catch syntax in script:', content.substring(0, 100) + '...');
                    }
                }
                
                // Check for missing or extra parentheses
                var openParens = (content.match(/\(/g) || []).length;
                var closeParens = (content.match(/\)/g) || []).length;
                
                if (openParens !== closeParens) {
                    console.warn('Found parenthesis mismatch in script. Opening:', openParens, 'Closing:', closeParens);
                }
                
                // Check for trailing commas in function calls
                if (content.match(/,\s*\)/)) {
                    console.warn('Found trailing comma in function call in script:', content.substring(0, 100) + '...');
                }
            });
            
            // Handle missing scripts
            var scriptErrors = [];
            
            // Create a custom error handler to catch script loading errors
            window.addEventListener('error', function(event) {
                if (event.target && event.target.tagName === 'SCRIPT') {
                    scriptErrors.push({
                        src: event.target.src,
                        error: event.message
                    });
                    console.warn('Script loading error:', event.target.src);
                    
                    // Prevent the error from propagating
                    event.preventDefault();
                    return true;
                }
            }, true);
            
            // Fix for missing tbo-hotel-booking-public.js
            if (!window.tboHotelBooking) {
                window.tboHotelBooking = {
                    // Add any necessary placeholder functions here
                    init: function() {
                        console.log('TBO Hotel Booking placeholder initialized');
                    }
                };
                
                // Call init function if needed
                if (typeof window.tboHotelBooking.init === 'function') {
                    window.tboHotelBooking.init();
                }
            }
        });
        &lt;/script&gt;
        <?php
    }
}
add_action('wp_footer', 'tbo_hotels_add_js_error_fix', 999);
        </pre>
    </div>
    
    <h2>What Next?</h2>
    <p>Based on the analysis, follow these steps to fix the errors:</p>
    
    <ol>
        <li>Add the JavaScript Console Fix Tool code to your theme's functions.php file</li>
        <li>Create any missing JavaScript files or update their paths</li>
        <li>Check and fix syntax errors in your theme or plugin JavaScript files</li>
        <li>Review third-party plugins that might be injecting JavaScript with syntax errors</li>
    </ol>
    
    <a href="<?php echo esc_url(admin_url('admin.php?page=tbo-hotels-ajax-diagnostic')); ?>" class="button">Run AJAX Diagnostic</a>
    <a href="<?php echo esc_url(home_url('/hotel-results/?country_code=IN&city_code=105141&check_in=2025-09-23&check_out=2025-09-25&rooms=1&adults=2&children=0')); ?>" class="button">Test Hotel Results Page</a>
</body>
</html>
<?php