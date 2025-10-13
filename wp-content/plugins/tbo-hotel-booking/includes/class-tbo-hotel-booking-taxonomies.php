<?php
/**
 * The class responsible for registering taxonomies.
 */
class TBO_Hotel_Booking_Taxonomies {

    /**
     * Register the taxonomies.
     */
    public function register() {
        $this->register_hotel_category_taxonomy();
        $this->register_hotel_facility_taxonomy();
        $this->register_hotel_location_taxonomy();
        $this->register_room_type_taxonomy();
    }

    /**
     * Register the hotel category taxonomy.
     */
    private function register_hotel_category_taxonomy() {
        $labels = array(
            'name'                       => _x('Hotel Categories', 'Taxonomy General Name', 'tbo-hotel-booking'),
            'singular_name'              => _x('Hotel Category', 'Taxonomy Singular Name', 'tbo-hotel-booking'),
            'menu_name'                  => __('Categories', 'tbo-hotel-booking'),
            'all_items'                  => __('All Categories', 'tbo-hotel-booking'),
            'parent_item'                => __('Parent Category', 'tbo-hotel-booking'),
            'parent_item_colon'          => __('Parent Category:', 'tbo-hotel-booking'),
            'new_item_name'              => __('New Category Name', 'tbo-hotel-booking'),
            'add_new_item'               => __('Add New Category', 'tbo-hotel-booking'),
            'edit_item'                  => __('Edit Category', 'tbo-hotel-booking'),
            'update_item'                => __('Update Category', 'tbo-hotel-booking'),
            'view_item'                  => __('View Category', 'tbo-hotel-booking'),
            'separate_items_with_commas' => __('Separate categories with commas', 'tbo-hotel-booking'),
            'add_or_remove_items'        => __('Add or remove categories', 'tbo-hotel-booking'),
            'choose_from_most_used'      => __('Choose from the most used', 'tbo-hotel-booking'),
            'popular_items'              => __('Popular Categories', 'tbo-hotel-booking'),
            'search_items'               => __('Search Categories', 'tbo-hotel-booking'),
            'not_found'                  => __('Not Found', 'tbo-hotel-booking'),
            'no_terms'                   => __('No categories', 'tbo-hotel-booking'),
            'items_list'                 => __('Categories list', 'tbo-hotel-booking'),
            'items_list_navigation'      => __('Categories list navigation', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'hotel_categories',
            'rewrite'                    => array('slug' => 'hotel-category'),
        );
        
        register_taxonomy('hotel_category', array('tbo_hotel'), $args);
    }

    /**
     * Register the hotel facility taxonomy.
     */
    private function register_hotel_facility_taxonomy() {
        $labels = array(
            'name'                       => _x('Hotel Facilities', 'Taxonomy General Name', 'tbo-hotel-booking'),
            'singular_name'              => _x('Hotel Facility', 'Taxonomy Singular Name', 'tbo-hotel-booking'),
            'menu_name'                  => __('Facilities', 'tbo-hotel-booking'),
            'all_items'                  => __('All Facilities', 'tbo-hotel-booking'),
            'parent_item'                => __('Parent Facility', 'tbo-hotel-booking'),
            'parent_item_colon'          => __('Parent Facility:', 'tbo-hotel-booking'),
            'new_item_name'              => __('New Facility Name', 'tbo-hotel-booking'),
            'add_new_item'               => __('Add New Facility', 'tbo-hotel-booking'),
            'edit_item'                  => __('Edit Facility', 'tbo-hotel-booking'),
            'update_item'                => __('Update Facility', 'tbo-hotel-booking'),
            'view_item'                  => __('View Facility', 'tbo-hotel-booking'),
            'separate_items_with_commas' => __('Separate facilities with commas', 'tbo-hotel-booking'),
            'add_or_remove_items'        => __('Add or remove facilities', 'tbo-hotel-booking'),
            'choose_from_most_used'      => __('Choose from the most used', 'tbo-hotel-booking'),
            'popular_items'              => __('Popular Facilities', 'tbo-hotel-booking'),
            'search_items'               => __('Search Facilities', 'tbo-hotel-booking'),
            'not_found'                  => __('Not Found', 'tbo-hotel-booking'),
            'no_terms'                   => __('No facilities', 'tbo-hotel-booking'),
            'items_list'                 => __('Facilities list', 'tbo-hotel-booking'),
            'items_list_navigation'      => __('Facilities list navigation', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => false,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => true,
            'show_in_rest'               => true,
            'rest_base'                  => 'hotel_facilities',
            'rewrite'                    => array('slug' => 'hotel-facility'),
        );
        
        register_taxonomy('hotel_facility', array('tbo_hotel'), $args);
    }

    /**
     * Register the hotel location taxonomy.
     */
    private function register_hotel_location_taxonomy() {
        $labels = array(
            'name'                       => _x('Hotel Locations', 'Taxonomy General Name', 'tbo-hotel-booking'),
            'singular_name'              => _x('Hotel Location', 'Taxonomy Singular Name', 'tbo-hotel-booking'),
            'menu_name'                  => __('Locations', 'tbo-hotel-booking'),
            'all_items'                  => __('All Locations', 'tbo-hotel-booking'),
            'parent_item'                => __('Parent Location', 'tbo-hotel-booking'),
            'parent_item_colon'          => __('Parent Location:', 'tbo-hotel-booking'),
            'new_item_name'              => __('New Location Name', 'tbo-hotel-booking'),
            'add_new_item'               => __('Add New Location', 'tbo-hotel-booking'),
            'edit_item'                  => __('Edit Location', 'tbo-hotel-booking'),
            'update_item'                => __('Update Location', 'tbo-hotel-booking'),
            'view_item'                  => __('View Location', 'tbo-hotel-booking'),
            'separate_items_with_commas' => __('Separate locations with commas', 'tbo-hotel-booking'),
            'add_or_remove_items'        => __('Add or remove locations', 'tbo-hotel-booking'),
            'choose_from_most_used'      => __('Choose from the most used', 'tbo-hotel-booking'),
            'popular_items'              => __('Popular Locations', 'tbo-hotel-booking'),
            'search_items'               => __('Search Locations', 'tbo-hotel-booking'),
            'not_found'                  => __('Not Found', 'tbo-hotel-booking'),
            'no_terms'                   => __('No locations', 'tbo-hotel-booking'),
            'items_list'                 => __('Locations list', 'tbo-hotel-booking'),
            'items_list_navigation'      => __('Locations list navigation', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'hotel_locations',
            'rewrite'                    => array('slug' => 'hotel-location'),
        );
        
        register_taxonomy('hotel_location', array('tbo_hotel'), $args);
    }

    /**
     * Register the room type taxonomy.
     */
    private function register_room_type_taxonomy() {
        $labels = array(
            'name'                       => _x('Room Types', 'Taxonomy General Name', 'tbo-hotel-booking'),
            'singular_name'              => _x('Room Type', 'Taxonomy Singular Name', 'tbo-hotel-booking'),
            'menu_name'                  => __('Room Types', 'tbo-hotel-booking'),
            'all_items'                  => __('All Room Types', 'tbo-hotel-booking'),
            'parent_item'                => __('Parent Room Type', 'tbo-hotel-booking'),
            'parent_item_colon'          => __('Parent Room Type:', 'tbo-hotel-booking'),
            'new_item_name'              => __('New Room Type Name', 'tbo-hotel-booking'),
            'add_new_item'               => __('Add New Room Type', 'tbo-hotel-booking'),
            'edit_item'                  => __('Edit Room Type', 'tbo-hotel-booking'),
            'update_item'                => __('Update Room Type', 'tbo-hotel-booking'),
            'view_item'                  => __('View Room Type', 'tbo-hotel-booking'),
            'separate_items_with_commas' => __('Separate room types with commas', 'tbo-hotel-booking'),
            'add_or_remove_items'        => __('Add or remove room types', 'tbo-hotel-booking'),
            'choose_from_most_used'      => __('Choose from the most used', 'tbo-hotel-booking'),
            'popular_items'              => __('Popular Room Types', 'tbo-hotel-booking'),
            'search_items'               => __('Search Room Types', 'tbo-hotel-booking'),
            'not_found'                  => __('Not Found', 'tbo-hotel-booking'),
            'no_terms'                   => __('No room types', 'tbo-hotel-booking'),
            'items_list'                 => __('Room Types list', 'tbo-hotel-booking'),
            'items_list_navigation'      => __('Room Types list navigation', 'tbo-hotel-booking'),
        );
        
        $args = array(
            'labels'                     => $labels,
            'hierarchical'               => true,
            'public'                     => true,
            'show_ui'                    => true,
            'show_admin_column'          => true,
            'show_in_nav_menus'          => true,
            'show_tagcloud'              => false,
            'show_in_rest'               => true,
            'rest_base'                  => 'room_types',
            'rewrite'                    => array('slug' => 'room-type'),
        );
        
        register_taxonomy('room_type', array('tbo_room'), $args);
    }
}
