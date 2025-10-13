<?php
echo "PHP is working!\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current directory: " . getcwd() . "\n";

// Test if WordPress files exist
if (file_exists('wp-config.php')) {
    echo "wp-config.php exists\n";
} else {
    echo "wp-config.php NOT found\n";
}

if (file_exists('wp-load.php')) {
    echo "wp-load.php exists\n";
} else {
    echo "wp-load.php NOT found\n";
}

// Test basic WordPress loading
try {
    require_once 'wp-config.php';
    echo "wp-config.php loaded successfully\n";
    
    require_once 'wp-load.php';
    echo "WordPress loaded successfully\n";
    
    if (function_exists('wp_create_nonce')) {
        echo "WordPress functions available\n";
    } else {
        echo "WordPress functions NOT available\n";
    }
} catch (Exception $e) {
    echo "Error loading WordPress: " . $e->getMessage() . "\n";
}

echo "Test completed!\n";