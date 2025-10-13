<?php
/**
 * Front to the WordPress application. This file doesn't do anything, but loads
 * wp-blog-header.php which does and tells WordPress to load the theme.
 *
 * @package WordPress
 */

// Direct PHP leak fix - Will remove any exposed PHP code from the output
ob_start(function($buffer) {
    // Check for PHP code patterns
    if (strpos($buffer, '<?php') !== false || 
        (strpos($buffer, 'function') !== false && 
         strpos($buffer, 'array(') !== false)) {
        
        // Clean unwanted PHP code from output
        $buffer = preg_replace('/\/\*\*[\s\S]*?\*\//', '', $buffer); // Remove PHP doc blocks
        $buffer = preg_replace('/<\?php.*?\?>/s', '', $buffer); // Remove PHP tags
        $buffer = preg_replace('/function\s+\w+\s*\([^\)]*\)\s*{.*?}/s', '', $buffer); // Remove function declarations
        $buffer = preg_replace('/\$\w+\s*=\s*array\(.*?\);/s', '', $buffer); // Remove array assignments
        $buffer = preg_replace('/return\s+array\(.*?\);/s', '', $buffer); // Remove return statements
    }
    
    return $buffer;
});

// Ensure the buffer gets flushed at the end
register_shutdown_function(function() {
    ob_end_flush();
});

/**
 * Tells WordPress to load the WordPress theme and output it.
 *
 * @var bool
 */
define( 'WP_USE_THEMES', true );

/** Loads the WordPress Environment and Template */
require __DIR__ . '/wp-blog-header.php';
