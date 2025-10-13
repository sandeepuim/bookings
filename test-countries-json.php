<?php
// This file simulates the WordPress AJAX response for countries
require_once('wp-load.php');

// Get countries from the theme function
$countries = tbo_hotels_get_countries();

if (is_wp_error($countries)) {
    echo json_encode([]);
} else {
    echo json_encode($countries);
}
?>