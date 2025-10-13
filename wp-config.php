<?php

// Include PHP code leak universal fix
if (file_exists(dirname(__FILE__) . '/wp-content/php-leak-universal-fix.php')) {
    require_once(dirname(__FILE__) . '/wp-content/php-leak-universal-fix.php');
}

 // By Speed Optimizer by SiteGround


/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_MAX_MEMORY_LIMIT', '256M' );

define('TBO_API_URL', 'http://api.tbotechnology.in/TBOHolidays_HotelAPI');
define('TBO_USERNAME', 'YOLANDATHTest');
define('TBO_PASSWORD', 'Yol@40360746');



// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', "bookings");

/** Database username */
define('DB_USER', "root");

/** Database password */
define('DB_PASSWORD', "");

/** Database hostname */
define('DB_HOST', "localhost");

/** Database charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The database collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '6_o/mz)INAEMc[{wa=>>VXo}`>.l.S)ei0_T*bHMMgu&p9uw.RKR.wJbJ`IPM^(3');
define('SECURE_AUTH_KEY',  '<3=I<zpEbz8^|L_r (Q:d~)0a2y7Tjp7;6g{HK),xvVq&Y]m~)c6ZUxb2C><>n)]');
define('LOGGED_IN_KEY',    '-6~4BDlJ7c.4>q$ym}FfT{DdHl1G?j<LU5}U5@i@@}v,h!C7=Fa&dG-o%[jPHs>}');
define('NONCE_KEY',        '0s3eQwoe,~6V_~^2CJ{l#A5@{#{1`=$z#Q[8qZ)P#KW JXB+oA@gs;yjE!fD87+-');
define('AUTH_SALT',        'GhK!<g TZ`n!E%qrUtfk.fSP@hX{;u]~AVKl4QR0=1Ab*,19]kD72Pm~vyiDs2{7');
define('SECURE_AUTH_SALT', 'TWgt|s#jcWL@8q=%lPP*QWV##akdP3^,/BjybJH|PvSF] )v*0feQKTXe]E/+H3m');
define('LOGGED_IN_SALT',   'kP$yF9e)TY7 yqGj9^z(Hkw[verwtfh/:-KJ/2^I:$NH2_ZQQX;R>dc`;.5:DC<o');
define('NONCE_SALT',       'BV$;LPL~ftxNv[VWE?n);{X=|V:^2!n2~=3C,)Wd-k8 {=uMdC+Fu=0]#d}.OV7,');

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */

define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);


/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (! defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

define('TBO_API_USER', 'YOLANDATHTest');
define('TBO_API_PASS', 'Yol@40360746');
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
