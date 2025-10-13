<?php get_header(); ?>

<main class="blog-container">
    <?php
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => 10,
        'paged'          => $paged
    );
    $custom_query = new WP_Query($args);
    ?>

    <?php if ($custom_query->have_posts()) : ?>
        <div class="blog-list">
            <?php while ($custom_query->have_posts()) : $custom_query->the_post(); ?>
                <article class="blog-item">
                    <?php if (has_post_thumbnail()) : ?>
                        <a href="<?php the_permalink(); ?>" class="thumbnail">
                            <?php the_post_thumbnail('medium'); ?>
                        </a>
                    <?php endif; ?>
                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                    <div class="excerpt"><?php the_excerpt(); ?></div>
                </article>
            <?php endwhile; ?>
        </div>

        <div class="pagination">
            <?php 
                echo paginate_links(array(
                    'total' => $custom_query->max_num_pages
                )); 
            ?>
        </div>
    <?php else : ?>
        <p>No posts found.</p>
    <?php endif; ?>

    <?php wp_reset_postdata(); ?>
</main>

<?php get_footer(); ?>