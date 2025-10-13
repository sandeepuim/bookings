<?php
/* Header */
?>
<!doctype html>
<html class="no-js" lang="<?php language_attributes(); ?>">

<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title><?php wp_title('|', true, 'right');
          bloginfo('name'); ?></title>
  <meta name="description" content="<?php bloginfo('description'); ?>">
  <meta name="author" content="<?php the_author_meta('display_name', 1); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="agd-partner-manual-verification" />
  
  <!--start google analytics (gtag.js) -->
	<script async src="https://www.googletagmanager.com/gtag/js?id=G-05R0Y3GMTL"></script>
	<script>
		window.dataLayer = window.dataLayer || [];

		function gtag() {
			dataLayer.push(arguments);
		}
		gtag('js', new Date());

		gtag('config', 'G-05R0Y3GMTL');
	</script>
	<!--end google analytics (gtag.js) -->
    
  <?php wp_head() ?>
</head>
<body>
<header class="header_rw position-absolute">
  <div class="container">
    <div class="header_inner">
      <div class="top_logo">
        <a href="https://bookings.yolandaholidays.com/">
  <img src="https://yolandaholidays.com/wp-content/uploads/2024/02/yo-logo-05.png" alt="logo" width="80">
</a>

        <button type="button" class="toggle_btn d-none" id="toggleBtn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-filter-right" viewBox="0 0 16 16">
            <path d="M14 10.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 .5-.5m0-3a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5m0-3a.5.5 0 0 0-.5-.5h-11a.5.5 0 0 0 0 1h11a.5.5 0 0 0 .5-.5" />
          </svg>
        </button>
      </div>
      <div class="navigation_bar">
        <button type="button" class="close_btn d-none" id="removeBtn">
          <svg xmlns="https://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
            <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
          </svg>
        </button>
        <ul class="un-listed d-flex justify-content-center">
          <li>
            <?php
            wp_nav_menu(array(
              'theme_location' => 'primary-menu',
              'container'      => 'nav',
              'container_class' => 'main-navigation',
              'menu_class'     => 'menu',
              'fallback_cb'    => false,
            ));
            ?>
          </li>
        </ul>
      </div>
    </div>
  </div>
</header>