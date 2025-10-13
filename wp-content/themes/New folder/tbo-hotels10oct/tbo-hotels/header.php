<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package TBO_Hotels
 */
// No closing PHP tag and no whitespace before opening tag
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'tbo-hotels' ); ?></a>

	<header id="masthead" class="site-header">
		<div class="container">
			<div class="header-content">
				<div class="site-branding">
					<?php
					if ( has_custom_logo() ) :
						the_custom_logo();
					else :
						?>
						<div class="brand-logo">
							<h1 class="site-title">
								<a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home">
									<span class="logo-text">Yolanda Holidays booking</span>
								</a>
							</h1>
						</div>
					<?php endif; ?>
				</div><!-- .site-branding -->

				<nav id="site-navigation" class="main-navigation">
					<ul class="primary-menu">
						<li class="menu-item">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a>
						</li>
						<li class="menu-item">
							<a href="<?php echo esc_url( home_url( '/hotel-search-page/' ) ); ?>">Yolanda Holidays</a>
						</li>
					</ul>
				</nav><!-- #site-navigation -->
			</div><!-- .header-content -->
		</div><!-- .container -->
	</header><!-- #masthead -->

	<div id="content" class="site-content">