<?php
/**
 * Flush Rewrite Rules
 * 
 * Run this script to flush rewrite rules after adding new rules
 */

// Bootstrap WordPress
require_once('wp-load.php');

// Flush rewrite rules
flush_rewrite_rules();

echo "Rewrite rules flushed successfully.";
?>