<?php get_header(); ?>

<div class="custom-404-container">
    <div class="custom-404-content">
        <h1 class="error-code">404</h1>
        <h2 class="error-title">Oops! Page not found.</h2>
        <p class="error-message">The page you’re looking for doesn’t exist or has been moved.</p>
        <a href="<?php echo home_url(); ?>" class="back-home-btn">Back to Home</a>
    </div>
</div>

<?php get_footer(); ?>