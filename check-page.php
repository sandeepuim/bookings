<?php
// Check if the hotel-rooms page exists
require_once('wp-load.php');
$page = get_page_by_path('hotel-rooms');
if ($page) {
    echo "Hotel Rooms page exists with ID: " . $page->ID . " and slug: " . $page->post_name;
} else {
    echo "Hotel Rooms page does not exist";
}
?>