<?php
/* Front Page Template */
get_header();

// Include hotel search functionality
require_once get_template_directory() . '/inc/hotel-search.php';
?>
<div class="hotel-search-section">
    <div class="container">
        <h1 class="search-title">Find Your Perfect Hotel</h1>
        
        <form method="post" action="" class="hotel-search-form" onsubmit="return false;">
            <?php get_template_part('template-parts/search-form'); ?>
        </form>
    </div>
</div>

<!-- Search Results Section -->
<div id="search-results" style="display: none; padding: 40px 0;">
    <div class="container">
        <!-- Results will be populated by JavaScript -->
    </div>
</div>

<?php get_footer(); ?>
