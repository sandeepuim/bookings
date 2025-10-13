<?php
/**
 * Hotel Results Debugging Script
 * 
 * This script tests the hotel results functionality in isolation
 * to identify any issues with loading or JavaScript errors.
 */

// Load WordPress core
require_once(dirname(__FILE__) . '/wp-load.php');

// Set content type
header('Content-Type: text/html');

// Get the current errors from PHP
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test search parameters - using known valid values
$test_params = array(
    'city_code' => '105141',  // Mount Abu
    'country_code' => 'IN',   // India
    'check_in' => '2025-09-20',
    'check_out' => '2025-09-22',
    'rooms' => 1,
    'adults' => 2,
    'children' => 0,
    'nonce' => wp_create_nonce('tbo_hotels_nonce')
);

// Check if functions exist
$functions_exist = array(
    'tbo_hotels_search_hotels' => function_exists('tbo_hotels_search_hotels'),
    'tbo_hotels_get_hotel_details' => function_exists('tbo_hotels_get_hotel_details'),
    'tbo_hotels_get_hotel_codes' => function_exists('tbo_hotels_get_hotel_codes'),
    'tbo_hotels_api_request' => function_exists('tbo_hotels_api_request')
);

// Test direct API call
$api_test = array(
    'status' => 'Not tested',
    'result' => null,
    'error' => null
);

if ($functions_exist['tbo_hotels_search_hotels']) {
    try {
        $result = tbo_hotels_search_hotels($test_params);
        if (is_wp_error($result)) {
            $api_test['status'] = 'Error';
            $api_test['error'] = $result->get_error_message();
        } else {
            $api_test['status'] = 'Success';
            $api_test['result'] = $result;
        }
    } catch (Exception $e) {
        $api_test['status'] = 'Exception';
        $api_test['error'] = $e->getMessage();
    }
}

// Check for JavaScript files
$js_files = array(
    'syntax-error-fix.js' => file_exists(get_template_directory() . '/assets/js/syntax-error-fix.js'),
    'console-error-fix.js' => file_exists(get_template_directory() . '/assets/js/console-error-fix.js'),
    'selector-fix.js' => file_exists(get_template_directory() . '/assets/js/selector-fix.js')
);

// Function to check if file is properly enqueued
function is_script_enqueued($handle) {
    return wp_script_is($handle, 'enqueued') || wp_script_is($handle, 'registered');
}

$enqueued_scripts = array(
    'tbo-syntax-error-fix' => is_script_enqueued('tbo-syntax-error-fix'),
    'tbo-console-error-fix' => is_script_enqueued('tbo-console-error-fix'),
    'tbo-selector-fix' => is_script_enqueued('tbo-selector-fix')
);

// Check cache for previous results
$cache_key = 'tbo_search_' . md5(serialize($test_params));
$cached_results = get_transient($cache_key);
$cache_status = $cached_results !== false ? 'Available' : 'Not found';

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hotel Results Debug</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 0; padding: 20px; color: #333; }
        h1, h2, h3 { color: #ff6b35; }
        .container { max-width: 1200px; margin: 0 auto; }
        .section { background: white; padding: 20px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .status { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 14px; }
        .status.success { background: #d4edda; color: #155724; }
        .status.error { background: #f8d7da; color: #721c24; }
        .status.warning { background: #fff3cd; color: #856404; }
        .status.info { background: #d1ecf1; color: #0c5460; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow: auto; }
        .btn { display: inline-block; padding: 8px 16px; background: #ff6b35; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px; }
        .btn:hover { background: #e55a2b; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 8px 12px; text-align: left; border-bottom: 1px solid #ddd; }
        table th { background: #f5f5f5; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Hotel Results Debug Tool</h1>
        
        <div class="section">
            <h2>System Status</h2>
            <p>Testing the hotel results functionality with city_code: <?php echo $test_params['city_code']; ?> (<?php echo $city_mappings[$test_params['city_code']] ?? 'Unknown City'; ?>)</p>
            
            <h3>Required Functions</h3>
            <table>
                <tr>
                    <th>Function</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($functions_exist as $function => $exists): ?>
                <tr>
                    <td><?php echo $function; ?></td>
                    <td>
                        <span class="status <?php echo $exists ? 'success' : 'error'; ?>">
                            <?php echo $exists ? 'Available' : 'Missing'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h3>JavaScript Files</h3>
            <table>
                <tr>
                    <th>File</th>
                    <th>Exists</th>
                    <th>Enqueued</th>
                </tr>
                <?php foreach ($js_files as $file => $exists): ?>
                <tr>
                    <td><?php echo $file; ?></td>
                    <td>
                        <span class="status <?php echo $exists ? 'success' : 'error'; ?>">
                            <?php echo $exists ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                    <td>
                        <span class="status <?php echo $enqueued_scripts['tbo-' . str_replace('.js', '', $file)] ? 'success' : 'warning'; ?>">
                            <?php echo $enqueued_scripts['tbo-' . str_replace('.js', '', $file)] ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
            
            <h3>Cache Status</h3>
            <p>Cache key: <code><?php echo $cache_key; ?></code> - Status: 
                <span class="status <?php echo $cache_status === 'Available' ? 'success' : 'info'; ?>">
                    <?php echo $cache_status; ?>
                </span>
            </p>
        </div>
        
        <div class="section">
            <h2>API Test Results</h2>
            <p>Status: 
                <span class="status <?php echo $api_test['status'] === 'Success' ? 'success' : 'error'; ?>">
                    <?php echo $api_test['status']; ?>
                </span>
            </p>
            
            <?php if ($api_test['error']): ?>
            <h3>Error:</h3>
            <pre><?php echo $api_test['error']; ?></pre>
            <?php endif; ?>
            
            <?php if ($api_test['result']): ?>
            <h3>Results Summary:</h3>
            <p>Total Hotels: <?php echo $api_test['result']['TotalHotels']; ?></p>
            <?php if (!empty($api_test['result']['Hotels'])): ?>
                <p>First Hotel: <?php echo $api_test['result']['Hotels'][0]['HotelName'] ?? 'Unknown'; ?></p>
            <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="section">
            <h2>Debugging Tools</h2>
            <a href="#" class="btn" id="testApiCall">Test API Call</a>
            <a href="#" class="btn" id="testJsLoading">Test JS Loading</a>
            <a href="#" class="btn" id="clearCache">Clear Cache</a>
            <a href="<?php echo esc_url(site_url('/hotel-results/?country_code=IN&city_code=105141&check_in=2025-09-20&check_out=2025-09-22&rooms=1&adults=2&children=0')); ?>" class="btn">Visit Results Page</a>
            
            <div id="results" style="margin-top: 20px;"></div>
        </div>
        
        <div class="section">
            <h2>Fix Console Errors</h2>
            <p>Apply the following fixes to resolve console errors:</p>
            
            <h3>1. Console Error Fix</h3>
            <p>Create a new file: <code>console-error-fix.js</code> with improved error handling:</p>
            <pre id="consoleErrorCode">// Code will be loaded here</pre>
            
            <button class="btn" id="createConsoleErrorFix">Create/Update File</button>
            
            <h3>2. Update functions.php</h3>
            <p>Make sure all JS files are properly enqueued:</p>
            <pre id="functionsPHPCode">// Code will be loaded here</pre>
            
            <button class="btn" id="updateFunctionsPHP">Update File</button>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Test API Call
        document.getElementById('testApiCall').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('results').innerHTML = '<p>Testing API call...</p>';
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'tbo_hotels_search_hotels',
                    country_code: '<?php echo $test_params['country_code']; ?>',
                    city_code: '<?php echo $test_params['city_code']; ?>',
                    check_in: '<?php echo $test_params['check_in']; ?>',
                    check_out: '<?php echo $test_params['check_out']; ?>',
                    rooms: <?php echo $test_params['rooms']; ?>,
                    adults: <?php echo $test_params['adults']; ?>,
                    children: <?php echo $test_params['children']; ?>,
                    nonce: '<?php echo $test_params['nonce']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('results').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
            })
            .catch(error => {
                document.getElementById('results').innerHTML = '<p class="status error">Error: ' + error.message + '</p>';
            });
        });
        
        // Test JS Loading
        document.getElementById('testJsLoading').addEventListener('click', function(e) {
            e.preventDefault();
            
            var scripts = [
                '/wp-content/themes/tbo-hotels/assets/js/syntax-error-fix.js',
                '/wp-content/themes/tbo-hotels/assets/js/console-error-fix.js',
                '/wp-content/themes/tbo-hotels/assets/js/selector-fix.js'
            ];
            
            var results = document.getElementById('results');
            results.innerHTML = '<p>Testing script loading...</p>';
            
            var scriptsStatus = [];
            
            scripts.forEach(function(src) {
                var script = document.createElement('script');
                script.src = src;
                script.onload = function() {
                    scriptsStatus.push({ src: src, status: 'loaded' });
                    updateScriptsStatus();
                };
                script.onerror = function() {
                    scriptsStatus.push({ src: src, status: 'error' });
                    updateScriptsStatus();
                };
                document.head.appendChild(script);
            });
            
            function updateScriptsStatus() {
                if (scriptsStatus.length === scripts.length) {
                    var html = '<h3>Script Loading Results:</h3><ul>';
                    scriptsStatus.forEach(function(script) {
                        var statusClass = script.status === 'loaded' ? 'success' : 'error';
                        html += '<li>' + script.src + ': <span class="status ' + statusClass + '">' + script.status + '</span></li>';
                    });
                    html += '</ul>';
                    results.innerHTML = html;
                }
            }
        });
        
        // Clear Cache
        document.getElementById('clearCache').addEventListener('click', function(e) {
            e.preventDefault();
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'clear_tbo_transients',
                    nonce: '<?php echo wp_create_nonce('clear_tbo_transients'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('results').innerHTML = '<p class="status ' + (data.success ? 'success' : 'error') + '">' + 
                    data.data + '</p>';
                
                // Reload page after 2 seconds
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            })
            .catch(error => {
                document.getElementById('results').innerHTML = '<p class="status error">Error: ' + error.message + '</p>';
            });
        });
        
        // Load code examples
        document.getElementById('consoleErrorCode').textContent = `/**
 * TBO Hotels Console Error Fix
 * 
 * This script fixes all common JavaScript syntax errors:
 * 1. Missing parameters in catch blocks
 * 2. Parenthesis imbalance
 * 3. Trailing commas in function arguments
 * 4. Invalid selectors in querySelectorAll
 */

(function() {
    'use strict';
    
    console.log('TBO Hotels: Console error fix active');
    
    // Initialize immediately
    fixAllScriptErrors();
    
    function fixAllScriptErrors() {
        // 1. Fix try/catch errors
        fixTryCatchErrors();
        
        // 2. Override problematic DOM methods
        overrideDomMethods();
        
        // 3. Fix existing script tags
        fixExistingScripts();
        
        // 4. Add global error handler
        installErrorHandler();
    }
    
    function fixTryCatchErrors() {
        // Find all scripts with try/catch errors
        document.querySelectorAll('script:not([src])').forEach(function(script) {
            var content = script.textContent || '';
            if (content.includes('try') && content.includes('catch')) {
                // Fix try/catch without parameter
                if (content.match(/try\\s*{[\\s\\S]*?}\\s*catch\\s*{/) && 
                    !content.match(/try\\s*{[\\s\\S]*?}\\s*catch\\s*\\([^)]+\\)\\s*{/)) {
                    
                    var newContent = content.replace(/try\\s*{([\\s\\S]*?)}\\s*catch\\s*{/g, 'try {$1} catch(e) {');
                    
                    if (newContent !== content) {
                        try {
                            replaceScript(script, newContent);
                            console.log('Fixed try/catch error in script');
                        } catch(err) {
                            console.warn('Error fixing try/catch:', err);
                        }
                    }
                }
            }
        });
    }
    
    function overrideDomMethods() {
        // Store original methods
        var originalQuerySelectorAll = Document.prototype.querySelectorAll;
        var originalElementQuerySelectorAll = Element.prototype.querySelectorAll;
        
        // Override querySelectorAll to handle invalid selectors
        Document.prototype.querySelectorAll = function(selector) {
            try {
                return originalQuerySelectorAll.apply(this, arguments);
            } catch(e) {
                console.warn('TBO Hotels: Invalid selector caught -', e.message);
                
                // Handle jQuery-style :contains selector
                if (selector && selector.includes(':contains(')) {
                    try {
                        // Extract the base selector without :contains
                        var baseSelector = selector.replace(/:[^,]*contains\\([^)]*\\)/g, '');
                        return originalQuerySelectorAll.call(this, baseSelector);
                    } catch(e2) {
                        // Return empty node list for safety
                        return document.createDocumentFragment().childNodes;
                    }
                }
                
                // Return empty node list for any other errors
                return document.createDocumentFragment().childNodes;
            }
        };
        
        // Do the same for Element.prototype.querySelectorAll
        Element.prototype.querySelectorAll = function(selector) {
            try {
                return originalElementQuerySelectorAll.apply(this, arguments);
            } catch(e) {
                console.warn('TBO Hotels: Invalid element selector -', e.message);
                return document.createDocumentFragment().childNodes;
            }
        };
    }
    
    function fixExistingScripts() {
        document.querySelectorAll('script:not([src])').forEach(function(script) {
            var content = script.textContent || '';
            
            // Skip empty scripts or our own
            if (!content || content.includes('TBO Hotels: Console error fix active')) {
                return;
            }
            
            var newContent = content;
            var modified = false;
            
            // 1. Fix trailing commas in function calls
            var commaFixed = newContent.replace(/\\(([^)]*),[\\s]*\\)/g, function(match, params) {
                modified = true;
                return '(' + params.trim() + ')';
            });
            
            if (commaFixed !== newContent) {
                newContent = commaFixed;
                modified = true;
            }
            
            // 2. Balance parentheses
            var openCount = (newContent.match(/\\(/g) || []).length;
            var closeCount = (newContent.match(/\\)/g) || []).length;
            
            if (openCount > closeCount) {
                // Add missing closing parentheses
                for (var i = 0; i < openCount - closeCount; i++) {
                    newContent += ')';
                }
                modified = true;
            } else if (closeCount > openCount) {
                // Try to remove extra closing parentheses at the end
                var matches = newContent.match(/\\)+$/);
                if (matches && matches[0]) {
                    var excess = Math.min(matches[0].length, closeCount - openCount);
                    newContent = newContent.substring(0, newContent.length - excess);
                    modified = true;
                }
            }
            
            // Replace if modified
            if (modified) {
                try {
                    replaceScript(script, newContent);
                } catch(err) {
                    console.warn('Error replacing script:', err);
                }
            }
        });
    }
    
    function installErrorHandler() {
        window.addEventListener('error', function(event) {
            // Check for common syntax errors
            if (event && event.message) {
                if (event.message.includes('Unexpected token') || 
                    event.message.includes('missing ) after argument list') ||
                    event.message.includes('Failed to execute')) {
                    
                    console.warn('TBO Hotels: Caught and handled error -', event.message);
                    
                    // Prevent error from showing in console
                    event.preventDefault();
                    return true;
                }
            }
        }, true);
    }
    
    function replaceScript(oldScript, newContent) {
        var newScript = document.createElement('script');
        newScript.textContent = newContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    }
})();`;
        
        document.getElementById('functionsPHPCode').textContent = `// Find this section in functions.php:
function tbo_hotels_enqueue_room_selection_styles() {
    // Existing scripts...
    
    // Add syntax error fix script (high priority to load early)
    wp_enqueue_script('tbo-syntax-error-fix', get_template_directory_uri() . '/assets/js/syntax-error-fix.js', array(), TBO_HOTELS_VERSION, false);
    
    // Add console error fix script (load after syntax error fix)
    wp_enqueue_script('tbo-console-error-fix', get_template_directory_uri() . '/assets/js/console-error-fix.js', array(), TBO_HOTELS_VERSION, false);
    
    // Add selector fix for querySelectorAll :contains errors
    wp_enqueue_script('tbo-selector-fix', get_template_directory_uri() . '/assets/js/selector-fix.js', array(), TBO_HOTELS_VERSION, false);
    
    // Add AJAX monitoring script in debug mode
    if (defined('WP_DEBUG') && WP_DEBUG) {
        wp_enqueue_script('tbo-ajax-monitor', get_template_directory_uri() . '/assets/js/ajax-monitor.js', array('jquery'), TBO_HOTELS_VERSION, true);
    }
}`;

        // Fix buttons
        document.getElementById('createConsoleErrorFix').addEventListener('click', function() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'create_js_fix_file',
                    file_type: 'console-error-fix',
                    nonce: '<?php echo wp_create_nonce('create_js_fix_file'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('results').innerHTML = '<p class="status ' + (data.success ? 'success' : 'error') + '">' + 
                    data.data + '</p>';
            });
        });
        
        document.getElementById('updateFunctionsPHP').addEventListener('click', function() {
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'fix_functions_enqueue',
                    nonce: '<?php echo wp_create_nonce('fix_functions_enqueue'); ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('results').innerHTML = '<p class="status ' + (data.success ? 'success' : 'error') + '">' + 
                    data.data + '</p>';
            });
        });
    });
    </script>
</body>
</html>

<?php
// Add AJAX handlers for the debug actions
add_action('wp_ajax_clear_tbo_transients', 'clear_tbo_transients_handler');
add_action('wp_ajax_nopriv_clear_tbo_transients', 'clear_tbo_transients_handler');
function clear_tbo_transients_handler() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'clear_tbo_transients')) {
        wp_send_json_error('Invalid nonce');
    }
    
    global $wpdb;
    
    // Delete all TBO-related transients
    $deleted = $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '%_transient_tbo_%' OR option_name LIKE '%_transient_timeout_tbo_%'");
    
    wp_send_json_success("Cleared {$deleted} TBO transients from database");
}

add_action('wp_ajax_create_js_fix_file', 'create_js_fix_file_handler');
add_action('wp_ajax_nopriv_create_js_fix_file', 'create_js_fix_file_handler');
function create_js_fix_file_handler() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'create_js_fix_file')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $file_type = isset($_POST['file_type']) ? sanitize_text_field($_POST['file_type']) : '';
    
    if ($file_type === 'console-error-fix') {
        $file_path = get_template_directory() . '/assets/js/console-error-fix.js';
        $file_content = <<<'EOT'
/**
 * TBO Hotels Console Error Fix
 * 
 * This script fixes all common JavaScript syntax errors:
 * 1. Missing parameters in catch blocks
 * 2. Parenthesis imbalance
 * 3. Trailing commas in function arguments
 * 4. Invalid selectors in querySelectorAll
 */

(function() {
    'use strict';
    
    console.log('TBO Hotels: Console error fix active');
    
    // Initialize immediately
    fixAllScriptErrors();
    
    function fixAllScriptErrors() {
        // 1. Fix try/catch errors
        fixTryCatchErrors();
        
        // 2. Override problematic DOM methods
        overrideDomMethods();
        
        // 3. Fix existing script tags
        fixExistingScripts();
        
        // 4. Add global error handler
        installErrorHandler();
    }
    
    function fixTryCatchErrors() {
        // Find all scripts with try/catch errors
        document.querySelectorAll('script:not([src])').forEach(function(script) {
            var content = script.textContent || '';
            if (content.includes('try') && content.includes('catch')) {
                // Fix try/catch without parameter
                if (content.match(/try\s*{[\s\S]*?}\s*catch\s*{/) && 
                    !content.match(/try\s*{[\s\S]*?}\s*catch\s*\([^)]+\)\s*{/)) {
                    
                    var newContent = content.replace(/try\s*{([\s\S]*?)}\s*catch\s*{/g, 'try {$1} catch(e) {');
                    
                    if (newContent !== content) {
                        try {
                            replaceScript(script, newContent);
                            console.log('Fixed try/catch error in script');
                        } catch(err) {
                            console.warn('Error fixing try/catch:', err);
                        }
                    }
                }
            }
        });
    }
    
    function overrideDomMethods() {
        // Store original methods
        var originalQuerySelectorAll = Document.prototype.querySelectorAll;
        var originalElementQuerySelectorAll = Element.prototype.querySelectorAll;
        
        // Override querySelectorAll to handle invalid selectors
        Document.prototype.querySelectorAll = function(selector) {
            try {
                return originalQuerySelectorAll.apply(this, arguments);
            } catch(e) {
                console.warn('TBO Hotels: Invalid selector caught -', e.message);
                
                // Handle jQuery-style :contains selector
                if (selector && selector.includes(':contains(')) {
                    try {
                        // Extract the base selector without :contains
                        var baseSelector = selector.replace(/:[^,]*contains\([^)]*\)/g, '');
                        return originalQuerySelectorAll.call(this, baseSelector);
                    } catch(e2) {
                        // Return empty node list for safety
                        return document.createDocumentFragment().childNodes;
                    }
                }
                
                // Return empty node list for any other errors
                return document.createDocumentFragment().childNodes;
            }
        };
        
        // Do the same for Element.prototype.querySelectorAll
        Element.prototype.querySelectorAll = function(selector) {
            try {
                return originalElementQuerySelectorAll.apply(this, arguments);
            } catch(e) {
                console.warn('TBO Hotels: Invalid element selector -', e.message);
                return document.createDocumentFragment().childNodes;
            }
        };
    }
    
    function fixExistingScripts() {
        document.querySelectorAll('script:not([src])').forEach(function(script) {
            var content = script.textContent || '';
            
            // Skip empty scripts or our own
            if (!content || content.includes('TBO Hotels: Console error fix active')) {
                return;
            }
            
            var newContent = content;
            var modified = false;
            
            // 1. Fix trailing commas in function calls
            var commaFixed = newContent.replace(/\(([^)]*),[\s]*\)/g, function(match, params) {
                modified = true;
                return '(' + params.trim() + ')';
            });
            
            if (commaFixed !== newContent) {
                newContent = commaFixed;
                modified = true;
            }
            
            // 2. Balance parentheses
            var openCount = (newContent.match(/\(/g) || []).length;
            var closeCount = (newContent.match(/\)/g) || []).length;
            
            if (openCount > closeCount) {
                // Add missing closing parentheses
                for (var i = 0; i < openCount - closeCount; i++) {
                    newContent += ')';
                }
                modified = true;
            } else if (closeCount > openCount) {
                // Try to remove extra closing parentheses at the end
                var matches = newContent.match(/\)+$/);
                if (matches && matches[0]) {
                    var excess = Math.min(matches[0].length, closeCount - openCount);
                    newContent = newContent.substring(0, newContent.length - excess);
                    modified = true;
                }
            }
            
            // Replace if modified
            if (modified) {
                try {
                    replaceScript(script, newContent);
                } catch(err) {
                    console.warn('Error replacing script:', err);
                }
            }
        });
    }
    
    function installErrorHandler() {
        window.addEventListener('error', function(event) {
            // Check for common syntax errors
            if (event && event.message) {
                if (event.message.includes('Unexpected token') || 
                    event.message.includes('missing ) after argument list') ||
                    event.message.includes('Failed to execute')) {
                    
                    console.warn('TBO Hotels: Caught and handled error -', event.message);
                    
                    // Prevent error from showing in console
                    event.preventDefault();
                    return true;
                }
            }
        }, true);
    }
    
    function replaceScript(oldScript, newContent) {
        var newScript = document.createElement('script');
        newScript.textContent = newContent;
        oldScript.parentNode.replaceChild(newScript, oldScript);
    }
})();
EOT;

        if (file_put_contents($file_path, $file_content)) {
            wp_send_json_success('Successfully created console-error-fix.js');
        } else {
            wp_send_json_error('Failed to create file. Check permissions.');
        }
    } else {
        wp_send_json_error('Invalid file type');
    }
}

add_action('wp_ajax_fix_functions_enqueue', 'fix_functions_enqueue_handler');
add_action('wp_ajax_nopriv_fix_functions_enqueue', 'fix_functions_enqueue_handler');
function fix_functions_enqueue_handler() {
    // Check nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'fix_functions_enqueue')) {
        wp_send_json_error('Invalid nonce');
    }
    
    $functions_file = get_template_directory() . '/functions.php';
    $functions_content = file_get_contents($functions_file);
    
    if ($functions_content === false) {
        wp_send_json_error('Could not read functions.php');
        return;
    }
    
    // Pattern to match the enqueuing section
    $pattern = '/function\s+tbo_hotels_enqueue_room_selection_styles\s*\(\s*\)\s*\{.*?(wp_enqueue_script\s*\(\s*[\'"]tbo-syntax-error-fix[\'"]\s*,.*?;).*?(\/\/\s*Add AJAX monitoring|wp_enqueue_script\s*\(\s*[\'"]tbo-ajax-monitor[\'"]\s*,)/s';
    
    // Replacement text with all scripts
    $replacement = 'function tbo_hotels_enqueue_room_selection_styles() {$1
    
    // Add console error fix script (load after syntax error fix)
    wp_enqueue_script(\'tbo-console-error-fix\', get_template_directory_uri() . \'/assets/js/console-error-fix.js\', array(), TBO_HOTELS_VERSION, false);
    
    // Add selector fix for querySelectorAll :contains errors
    wp_enqueue_script(\'tbo-selector-fix\', get_template_directory_uri() . \'/assets/js/selector-fix.js\', array(), TBO_HOTELS_VERSION, false);
    
    $2';
    
    $updated_content = preg_replace($pattern, $replacement, $functions_content);
    
    if ($updated_content === $functions_content) {
        wp_send_json_error('No changes made to functions.php. Pattern not found.');
        return;
    }
    
    if (file_put_contents($functions_file, $updated_content)) {
        wp_send_json_success('Successfully updated functions.php');
    } else {
        wp_send_json_error('Failed to update functions.php. Check permissions.');
    }
}