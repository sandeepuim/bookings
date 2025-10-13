<?php
// Define WordPress constants
define('WP_USE_THEMES', false);

// Load WordPress
require('./wp-load.php');

// Check if page already exists
$existing_page = get_page_by_path('hotel-rooms');
if ($existing_page) {
    echo "Hotel Rooms page already exists with ID: {$existing_page->ID}\n";
    exit;
}

// Create page
$page_id = wp_insert_post([
    'post_title'     => 'Hotel Rooms',
    'post_name'      => 'hotel-rooms',
    'post_status'    => 'publish',
    'post_type'      => 'page',
    'post_content'   => '',
]);

if (is_wp_error($page_id)) {
    echo "Error creating page: {$page_id->get_error_message()}\n";
    exit;
}

// Set page template
update_post_meta($page_id, '_wp_page_template', 'page-templates/hotel-rooms-page.php');

echo "Hotel Rooms page created with ID: {$page_id}\n";