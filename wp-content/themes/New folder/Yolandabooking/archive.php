<?php get_header(); ?>

<div class="post-list">
    <?php
    $args = array(
        'post_type' => 'post', // Change this to your custom post type if needed
        'posts_per_page' => 10,
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post(); ?>
            <article class="post-item">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                <div class="post-excerpt">
                    <?php the_excerpt(); ?>
                </div>
            </article>
        <?php endwhile;
        wp_reset_postdata();
    else :
        echo '<p>No posts found.</p>';
    endif;
    ?>
</div>

<?php get_footer(); ?>