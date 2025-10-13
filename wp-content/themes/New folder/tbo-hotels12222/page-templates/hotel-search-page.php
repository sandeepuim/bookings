<?php
/**
 * Template Name: Hotel Search Page
 * 
 * Template for displaying the hotel search form
 */

// Get the header
get_header();
?>

<div class="container">
    <div class="hotel-search-container">
        <div class="hotel-search-intro">
            <h1><?php the_title(); ?></h1>
            <?php the_content(); ?>
        </div>
        
        <?php include(get_template_directory() . '/templates/hotel-search.php'); ?>
    </div>
</div>

<?php get_footer(); ?>