<?php
/**
 * PHP Code Leak Universal Fix
 *
 * This script will fix PHP code being displayed in the browser by
 * adding an output buffer at the very beginning of WordPress loading.
 * Place this file in the wp-content directory and include it from wp-config.php.
 */

// Start output buffering
ob_start(function($buffer) {
    // Check if the buffer contains PHP code
    if (strpos($buffer, '<?php') !== false || 
        (strpos($buffer, 'function') !== false && 
         strpos($buffer, 'array(') !== false && 
         strpos($buffer, 'return') !== false)) {
        
        // Log the issue
        error_log('PHP code leak detected and fixed by universal fix');
        
        // Remove the PHP code from the output
        $buffer = preg_replace('/<\?php.*?\?>/s', '', $buffer);
        $buffer = preg_replace('/function\s+\w+\s*\([^\)]*\)\s*{.*?return\s+array\(/s', '', $buffer);
        
        // Add a warning for admins (only visible to logged-in administrators)
        if (function_exists('current_user_can') && current_user_can('administrator')) {
            $buffer = '<div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin: 10px 0; border: 1px solid #f5c6cb; border-radius: 5px;">
                <strong>Admin Notice:</strong> PHP code leak detected and fixed. Check your theme files.
            </div>' . $buffer;
        }
    }
    
    return $buffer;
});

// Register shutdown function to flush the buffer
register_shutdown_function(function() {
    ob_end_flush();
});