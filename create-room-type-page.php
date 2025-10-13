<?php
/**
 * Create Room Type Page
 * 
 * This script creates a WordPress page with the slug "room-type" 
 * and assigns our room type template to it.
 */

// Bootstrap WordPress
require_once('wp-load.php');

// Check if page already exists
$existing_page = get_page_by_path('room-type');

if ($existing_page) {
    echo "Room Type page already exists with ID: " . $existing_page->ID . "\n";
    echo "Updating template...\n";
    
    // Update the template
    update_post_meta($existing_page->ID, '_wp_page_template', 'page-templates/room-type-template.php');
    
    echo "Template updated successfully.\n";
} else {
    // Create the page
    $page_id = wp_insert_post(array(
        'post_title'     => 'Room Type Selection',
        'post_name'      => 'room-type',
        'post_status'    => 'publish',
        'post_type'      => 'page',
        'comment_status' => 'closed'
    ));
    
    if (is_wp_error($page_id)) {
        echo "Error creating page: " . $page_id->get_error_message() . "\n";
    } else {
        // Set the page template
        update_post_meta($page_id, '_wp_page_template', 'page-templates/room-type-template.php');
        
        echo "Room Type page created with ID: " . $page_id . "\n";
        echo "Template set to: room-type-template.php\n";
    }
}

// Flush rewrite rules to ensure our page is accessible
flush_rewrite_rules();
echo "Rewrite rules flushed.\n";

echo "Process completed.\n";
?>