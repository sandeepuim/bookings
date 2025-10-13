<?php
/**
 * The main template file
 *
 * @package TBO_Hotels
 */

get_header();
?>

<main id="primary" class="site-main">
    <div class="container">
        <?php
        if (have_posts()) :
            while (have_posts()) : the_post();
                ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <header class="entry-header">
                        <?php the_title('<h1 class="entry-title">', '</h1>'); ?>
                    </header><!-- .entry-header -->

                    <div class="entry-content">
                        <?php the_content(); ?>
                    </div><!-- .entry-content -->
                </article><!-- #post-<?php the_ID(); ?> -->
                <?php
            endwhile;
        else :
            ?>
            <p><?php esc_html_e('No content found.', 'tbo-hotels'); ?></p>
            <?php
        endif;
        ?>
        
        <!-- Include Hotel Search on Home Page -->
        <div class="home-hotel-search">
            <?php include(get_template_directory() . '/templates/hotel-search.php'); ?>
        </div>
    </div><!-- .container -->
</main><!-- #primary -->

<?php
get_footer();