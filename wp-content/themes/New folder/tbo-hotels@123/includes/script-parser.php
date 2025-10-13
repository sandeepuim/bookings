<?php
/**
 * Script Parser and Fixer
 * 
 * This tool extracts all inline scripts from your hotel results page,
 * fixes syntax errors, and shows a detailed report.
 */

// Load WordPress core
require_once(dirname(__FILE__) . '/../../../../wp-load.php');

// Set content type to HTML
header('Content-Type: text/html');

// Function to fetch the hotel results page
function fetch_hotel_results_page() {
    $url = home_url('/hotel-results/?country_code=IN&city_code=105141&check_in=2025-09-20&check_out=2025-09-22&rooms=1&adults=2&children=0');
    
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

// Function to extract and fix scripts
function extract_and_fix_scripts($html) {
    // Extract all script tags
    preg_match_all('/<script\b[^>]*>(.*?)<\/script>/si', $html, $matches);
    
    $scripts = array();
    
    if (empty($matches[1])) {
        return array('error' => 'No script tags found');
    }
    
    foreach ($matches[1] as $index => $content) {
        // Skip empty scripts
        if (empty(trim($content))) {
            continue;
        }
        
        // Check for errors
        $errors = array();
        $fixes = array();
        
        // Check for try/catch errors
        if (strpos($content, 'try') !== false && strpos($content, 'catch') !== false) {
            if (preg_match('/try\s*{.*?}\s*catch\s*{/s', $content) && !preg_match('/try\s*{.*?}\s*catch\s*\([^)]+\)\s*{/s', $content)) {
                $errors[] = array(
                    'type' => 'catch_missing_parameter',
                    'message' => 'Catch statement missing parameter'
                );
                
                // Fix: Add parameter to catch
                $fixed_content = preg_replace('/try\s*{(.*?)}\s*catch\s*{/s', 'try {$1} catch(e) {', $content);
                if ($fixed_content != $content) {
                    $fixes[] = array(
                        'type' => 'catch_missing_parameter',
                        'before' => '...} catch {',
                        'after' => '...} catch(e) {'
                    );
                    $content = $fixed_content;
                }
            }
        }
        
        // Check for parenthesis balance
        $open_parens = substr_count($content, '(');
        $close_parens = substr_count($content, ')');
        
        if ($open_parens != $close_parens) {
            $errors[] = array(
                'type' => 'parenthesis_imbalance',
                'message' => "Parenthesis mismatch: $open_parens opening vs $close_parens closing"
            );
            
            // Fix: Balance parentheses
            if ($open_parens > $close_parens) {
                // Add missing closing parentheses
                $diff = $open_parens - $close_parens;
                $fixed_content = $content;
                for ($i = 0; $i < $diff; $i++) {
                    $fixed_content .= ')';
                }
                $fixes[] = array(
                    'type' => 'parenthesis_imbalance',
                    'before' => "Missing $diff closing parentheses",
                    'after' => "Added $diff closing parentheses"
                );
                $content = $fixed_content;
            } else {
                // Remove extra closing parentheses
                $pattern = '/\)+$/';
                preg_match($pattern, $content, $matches);
                if (!empty($matches[0])) {
                    $excess = min(strlen($matches[0]), $close_parens - $open_parens);
                    $fixed_content = substr($content, 0, strlen($content) - $excess);
                    $fixes[] = array(
                        'type' => 'parenthesis_imbalance',
                        'before' => "Extra $excess closing parentheses",
                        'after' => "Removed $excess closing parentheses"
                    );
                    $content = $fixed_content;
                }
            }
        }
        
        // Check for trailing commas in function arguments
        if (preg_match('/\([^)]*,\s*\)/', $content)) {
            $errors[] = array(
                'type' => 'trailing_comma',
                'message' => 'Trailing comma in function arguments'
            );
            
            // Fix: Remove trailing commas
            $fixed_content = preg_replace('/\(([^)]*),\s*\)/', '($1)', $content);
            if ($fixed_content != $content) {
                $fixes[] = array(
                    'type' => 'trailing_comma',
                    'before' => 'function(param1, param2, )',
                    'after' => 'function(param1, param2)'
                );
                $content = $fixed_content;
            }
        }
        
        $scripts[] = array(
            'index' => $index,
            'original_content' => $matches[1][$index],
            'fixed_content' => $content,
            'errors' => $errors,
            'fixes' => $fixes,
            'was_fixed' => count($fixes) > 0
        );
    }
    
    return $scripts;
}

// Fetch the hotel results page
$html = fetch_hotel_results_page();
$scripts = extract_and_fix_scripts($html);

// Output the report
?>
<!DOCTYPE html>
<html>
<head>
    <title>Script Parser and Fixer</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; max-width: 1200px; margin: 0 auto; padding: 20px; }
        h1, h2, h3 { color: #2c3e50; }
        .script-block { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; border-radius: 4px; }
        .script-block.has-errors { border-left: 4px solid #e74c3c; }
        .script-block.was-fixed { border-left: 4px solid #2ecc71; }
        .error-item { background-color: #ffeeee; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        .fix-item { background-color: #eeffee; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
        pre { background-color: #f5f5f5; padding: 10px; border-radius: 4px; overflow-x: auto; max-height: 300px; }
        .diff { display: flex; }
        .diff-before, .diff-after { flex: 1; margin: 0 5px; }
        .action-button { display: inline-block; padding: 10px 15px; background-color: #3498db; color: white; 
                      text-decoration: none; border-radius: 4px; margin-top: 15px; }
        .summary { background-color: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <h1>Script Parser and Fixer</h1>
    
    <div class="summary">
        <h2>Summary</h2>
        <?php
        $total_scripts = count($scripts);
        $scripts_with_errors = 0;
        $scripts_fixed = 0;
        $total_errors = 0;
        $error_types = array();
        
        foreach ($scripts as $script) {
            if (!empty($script['errors'])) {
                $scripts_with_errors++;
                $total_errors += count($script['errors']);
                
                foreach ($script['errors'] as $error) {
                    if (!isset($error_types[$error['type']])) {
                        $error_types[$error['type']] = 0;
                    }
                    $error_types[$error['type']]++;
                }
            }
            
            if ($script['was_fixed']) {
                $scripts_fixed++;
            }
        }
        ?>
        
        <p><strong>Total scripts analyzed:</strong> <?php echo $total_scripts; ?></p>
        <p><strong>Scripts with errors:</strong> <?php echo $scripts_with_errors; ?></p>
        <p><strong>Scripts fixed:</strong> <?php echo $scripts_fixed; ?></p>
        <p><strong>Total errors found:</strong> <?php echo $total_errors; ?></p>
        
        <?php if (!empty($error_types)): ?>
            <h3>Error Types:</h3>
            <ul>
                <?php foreach ($error_types as $type => $count): ?>
                    <li><strong><?php echo ucwords(str_replace('_', ' ', $type)); ?>:</strong> <?php echo $count; ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    
    <h2>Script Analysis</h2>
    
    <?php if (isset($scripts['error'])): ?>
        <div class="error-item">
            <p><?php echo $scripts['error']; ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($scripts as $script): ?>
            <?php
            $class = 'script-block';
            if (!empty($script['errors'])) {
                $class .= ' has-errors';
            }
            if ($script['was_fixed']) {
                $class .= ' was-fixed';
            }
            ?>
            <div class="<?php echo $class; ?>">
                <h3>Script #<?php echo $script['index'] + 1; ?></h3>
                
                <?php if (!empty($script['errors'])): ?>
                    <h4>Errors Found:</h4>
                    <?php foreach ($script['errors'] as $error): ?>
                        <div class="error-item">
                            <p><strong><?php echo ucwords(str_replace('_', ' ', $error['type'])); ?>:</strong> <?php echo $error['message']; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><em>No errors detected in this script.</em></p>
                <?php endif; ?>
                
                <?php if (!empty($script['fixes'])): ?>
                    <h4>Fixes Applied:</h4>
                    <?php foreach ($script['fixes'] as $fix): ?>
                        <div class="fix-item">
                            <p><strong><?php echo ucwords(str_replace('_', ' ', $fix['type'])); ?> Fixed</strong></p>
                            <div class="diff">
                                <div class="diff-before">
                                    <p><strong>Before:</strong></p>
                                    <pre><?php echo htmlspecialchars($fix['before']); ?></pre>
                                </div>
                                <div class="diff-after">
                                    <p><strong>After:</strong></p>
                                    <pre><?php echo htmlspecialchars($fix['after']); ?></pre>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <h4>Script Content:</h4>
                <pre><?php echo htmlspecialchars(substr($script['original_content'], 0, 500)) . (strlen($script['original_content']) > 500 ? '...' : ''); ?></pre>
                
                <?php if ($script['was_fixed']): ?>
                    <h4>Fixed Content:</h4>
                    <pre><?php echo htmlspecialchars(substr($script['fixed_content'], 0, 500)) . (strlen($script['fixed_content']) > 500 ? '...' : ''); ?></pre>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <h2>Implementation</h2>
    
    <p>To implement the fixes on your site, these improvements have been added to:</p>
    
    <ul>
        <li><strong>syntax-error-fix.js</strong> - Automatically fixes syntax errors in scripts</li>
        <li><strong>js-error-fix.php</strong> - Provides server-side fixes for JavaScript errors</li>
        <li><strong>tbo-hotel-booking-public.js</strong> - Fixed version of the missing script</li>
    </ul>
    
    <p>These scripts should work together to prevent the JavaScript errors in your console.</p>
    
    <a href="<?php echo esc_url(home_url('/hotel-results/?country_code=IN&city_code=105141&check_in=2025-09-20&check_out=2025-09-22&rooms=1&adults=2&children=0')); ?>" class="action-button">Test Hotel Results Page</a>
</body>
</html>
<?php