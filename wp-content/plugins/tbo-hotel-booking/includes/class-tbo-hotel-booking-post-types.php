<?php
/**
 * The class responsible for registering custom post types.
 */
class TBO_Hotel_Booking_Post_Types {

    /**
     * Register the custom post types.
     */
    public function register() {
        $this->register_hotel_post_type();
        $this->register_room_post_type();
        $this->register_booking_post_type();
        $this->register_offer_post_type();
    }

    /**
     * Register the hotel post type.
     */
    private function register_hotel_post_type() {
        $labels = array(
            'name'                  => _x('Hotels', 'Post Type General Name', 'tbo-hotel-booking'),
            'singular_name'         => _x('Hotel', 'Post Type Singular Name', 'tbo-hotel-booking'),
            'menu_name'             => __('Hotels', 'tbo-hotel-booking'),
            'name_admin_bar'        => __('Hotel', 'tbo-hotel-booking'),
            'archives'              => __('Hotel Archives', 'tbo-hotel-booking'),
            'attributes'            => __('Hotel Attributes', 'tbo-hotel-booking'),
            'parent_item_colon'     => __('Parent Hotel:', 'tbo-hotel-booking'),
            'all_items'             => __('All Hotels', 'tbo-hotel-booking'),
            'add_new_item'          => __('Add New Hotel', 'tbo-hotel-booking'),
            'add_new'               => __('Add New', 'tbo-hotel-booking'),
            'new_item'              => __('New Hotel', 'tbo-hotel-booking'),
            'edit_item'             => __('Edit Hotel', 'tbo-hotel-booking'),
            'update_item'           => __('Update Hotel', 'tbo-hotel-booking'),
            'view_item'             => __('View Hotel', 'tbo-hotel-booking'),
            'view_items'            => __('View Hotels', 'tbo-hotel-booking'),
            'search_items'          => __('Search Hotel', 'tbo-hotel-booking'),
            'not_found'             => __('Not found', 'tbo-hotel-booking'),
            'not_found_in_trash'    => __('Not found in Trash', 'tbo-hotel-booking'),
            'featured_image'        => __('Featured Image', 'tbo-hotel-booking'),
            'set_featured_image'    => __('Set featured image', 'tbo-hotel-booking'),
            'remove_featured_image' => __('Remove featured image', 'tbo-hotel-booking'),
            'use_featured_image'    => __('Use as featured image', 'tbo-hotel-booking'),
            'insert_into_item'      => __('Insert into hotel', 'tbo-hotel-booking'),
            'uploaded_to_this_item' => __('Uploaded to this hotel', 'tbo-hotel-booking'),
            'items_list'            => __('Hotels list', 'tbo-hotel-booking'),
            'items_list_navigation' => __('Hotels list navigation', 'tbo-hotel-booking'),
            'filter_items_list'     => __('Filter hotels list', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'label'                 => __('Hotel', 'tbo-hotel-booking'),
            'description'           => __('Hotel listings from TBO API', 'tbo-hotel-booking'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-building',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'tbo_hotels',
            'rewrite'               => array('slug' => 'hotels'),
        );
        
        register_post_type('tbo_hotel', $args);
    }

    /**
     * Register the room post type.
     */
    private function register_room_post_type() {
        $labels = array(
            'name'                  => _x('Rooms', 'Post Type General Name', 'tbo-hotel-booking'),
            'singular_name'         => _x('Room', 'Post Type Singular Name', 'tbo-hotel-booking'),
            'menu_name'             => __('Rooms', 'tbo-hotel-booking'),
            'name_admin_bar'        => __('Room', 'tbo-hotel-booking'),
            'archives'              => __('Room Archives', 'tbo-hotel-booking'),
            'attributes'            => __('Room Attributes', 'tbo-hotel-booking'),
            'parent_item_colon'     => __('Parent Room:', 'tbo-hotel-booking'),
            'all_items'             => __('All Rooms', 'tbo-hotel-booking'),
            'add_new_item'          => __('Add New Room', 'tbo-hotel-booking'),
            'add_new'               => __('Add New', 'tbo-hotel-booking'),
            'new_item'              => __('New Room', 'tbo-hotel-booking'),
            'edit_item'             => __('Edit Room', 'tbo-hotel-booking'),
            'update_item'           => __('Update Room', 'tbo-hotel-booking'),
            'view_item'             => __('View Room', 'tbo-hotel-booking'),
            'view_items'            => __('View Rooms', 'tbo-hotel-booking'),
            'search_items'          => __('Search Room', 'tbo-hotel-booking'),
            'not_found'             => __('Not found', 'tbo-hotel-booking'),
            'not_found_in_trash'    => __('Not found in Trash', 'tbo-hotel-booking'),
            'featured_image'        => __('Featured Image', 'tbo-hotel-booking'),
            'set_featured_image'    => __('Set featured image', 'tbo-hotel-booking'),
            'remove_featured_image' => __('Remove featured image', 'tbo-hotel-booking'),
            'use_featured_image'    => __('Use as featured image', 'tbo-hotel-booking'),
            'insert_into_item'      => __('Insert into room', 'tbo-hotel-booking'),
            'uploaded_to_this_item' => __('Uploaded to this room', 'tbo-hotel-booking'),
            'items_list'            => __('Rooms list', 'tbo-hotel-booking'),
            'items_list_navigation' => __('Rooms list navigation', 'tbo-hotel-booking'),
            'filter_items_list'     => __('Filter rooms list', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'label'                 => __('Room', 'tbo-hotel-booking'),
            'description'           => __('Room listings for hotels', 'tbo-hotel-booking'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-admin-home',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'tbo_rooms',
            'rewrite'               => array('slug' => 'rooms'),
        );
        
        register_post_type('tbo_room', $args);
    }

    /**
     * Register the booking post type.
     */
    private function register_booking_post_type() {
        $labels = array(
            'name'                  => _x('Bookings', 'Post Type General Name', 'tbo-hotel-booking'),
            'singular_name'         => _x('Booking', 'Post Type Singular Name', 'tbo-hotel-booking'),
            'menu_name'             => __('Bookings', 'tbo-hotel-booking'),
            'name_admin_bar'        => __('Booking', 'tbo-hotel-booking'),
            'archives'              => __('Booking Archives', 'tbo-hotel-booking'),
            'attributes'            => __('Booking Attributes', 'tbo-hotel-booking'),
            'parent_item_colon'     => __('Parent Booking:', 'tbo-hotel-booking'),
            'all_items'             => __('All Bookings', 'tbo-hotel-booking'),
            'add_new_item'          => __('Add New Booking', 'tbo-hotel-booking'),
            'add_new'               => __('Add New', 'tbo-hotel-booking'),
            'new_item'              => __('New Booking', 'tbo-hotel-booking'),
            'edit_item'             => __('Edit Booking', 'tbo-hotel-booking'),
            'update_item'           => __('Update Booking', 'tbo-hotel-booking'),
            'view_item'             => __('View Booking', 'tbo-hotel-booking'),
            'view_items'            => __('View Bookings', 'tbo-hotel-booking'),
            'search_items'          => __('Search Booking', 'tbo-hotel-booking'),
            'not_found'             => __('Not found', 'tbo-hotel-booking'),
            'not_found_in_trash'    => __('Not found in Trash', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'label'                 => __('Booking', 'tbo-hotel-booking'),
            'description'           => __('Booking information', 'tbo-hotel-booking'),
            'labels'                => $labels,
            'supports'              => array('title', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-calendar-alt',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'tbo_bookings',
        );
        
        register_post_type('tbo_booking', $args);
    }

    /**
     * Register the offer post type.
     */
    private function register_offer_post_type() {
        $labels = array(
            'name'                  => _x('Offers', 'Post Type General Name', 'tbo-hotel-booking'),
            'singular_name'         => _x('Offer', 'Post Type Singular Name', 'tbo-hotel-booking'),
            'menu_name'             => __('Offers', 'tbo-hotel-booking'),
            'name_admin_bar'        => __('Offer', 'tbo-hotel-booking'),
            'archives'              => __('Offer Archives', 'tbo-hotel-booking'),
            'attributes'            => __('Offer Attributes', 'tbo-hotel-booking'),
            'parent_item_colon'     => __('Parent Offer:', 'tbo-hotel-booking'),
            'all_items'             => __('All Offers', 'tbo-hotel-booking'),
            'add_new_item'          => __('Add New Offer', 'tbo-hotel-booking'),
            'add_new'               => __('Add New', 'tbo-hotel-booking'),
            'new_item'              => __('New Offer', 'tbo-hotel-booking'),
            'edit_item'             => __('Edit Offer', 'tbo-hotel-booking'),
            'update_item'           => __('Update Offer', 'tbo-hotel-booking'),
            'view_item'             => __('View Offer', 'tbo-hotel-booking'),
            'view_items'            => __('View Offers', 'tbo-hotel-booking'),
            'search_items'          => __('Search Offer', 'tbo-hotel-booking'),
            'not_found'             => __('Not found', 'tbo-hotel-booking'),
            'not_found_in_trash'    => __('Not found in Trash', 'tbo-hotel-booking'),
            'featured_image'        => __('Featured Image', 'tbo-hotel-booking'),
            'set_featured_image'    => __('Set featured image', 'tbo-hotel-booking'),
            'remove_featured_image' => __('Remove featured image', 'tbo-hotel-booking'),
            'use_featured_image'    => __('Use as featured image', 'tbo-hotel-booking'),
            'insert_into_item'      => __('Insert into offer', 'tbo-hotel-booking'),
            'uploaded_to_this_item' => __('Uploaded to this offer', 'tbo-hotel-booking'),
            'items_list'            => __('Offers list', 'tbo-hotel-booking'),
            'items_list_navigation' => __('Offers list navigation', 'tbo-hotel-booking'),
            'filter_items_list'     => __('Filter offers list', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'label'                 => __('Offer', 'tbo-hotel-booking'),
            'description'           => __('Special offers and promotions', 'tbo-hotel-booking'),
            'labels'                => $labels,
            'supports'              => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'hierarchical'          => false,
            'public'                => true,
            'show_ui'               => true,
            'show_in_menu'          => false,
            'menu_position'         => 5,
            'menu_icon'             => 'dashicons-tag',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => true,
            'can_export'            => true,
            'has_archive'           => true,
            'exclude_from_search'   => false,
            'publicly_queryable'    => true,
            'capability_type'       => 'page',
            'show_in_rest'          => true,
            'rest_base'             => 'tbo_offers',
            'rewrite'               => array('slug' => 'offers'),
        );
        
        register_post_type('tbo_offer', $args);
    }
}
