<?php get_header(); ?>

<div class="single-blog-wrapper">
    <div class="container">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                <div class="single-blog-layout">

                    <!-- Table of Contents (Left Sidebar) -->
                    <aside class="toc-sidebar">
                        <div id="table-of-contents" class="toc-box">
                            <h2>Table of Contents</h2>
                            <ul id="toc-list"></ul>
                        </div>
                    </aside>

                    <!-- Blog Content (Main Content) -->
                    <article class="single-blog-content">

                        <h1 class="single-title"><?php the_title(); ?></h1>

                        <div class="single-meta">
                            <span class="blog-date"><?php echo get_the_date(); ?></span>
                            <span class="blog-author">By <?php the_author(); ?></span>
                        </div>

                        <?php if (has_post_thumbnail()) : ?>
                            <div class="single-thumbnail">
                                <?php the_post_thumbnail('large'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="single-content" id="post-content">
                            <?php the_content(); ?>
                        </div>

                    </article>
                </div>
        <?php endwhile;
        endif; ?>
    </div>
</div>
<?php get_footer(); ?>