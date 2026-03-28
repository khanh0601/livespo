<?php
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

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'livespo');

/** Database username */
define('DB_USER', 'root');

/** Database password */
define('DB_PASSWORD', 'khanhkhanh123');

/** Database hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY', '-#P4{@)DRl;y-58Ir**FD xkXhtjyzc5+W-~W>& l+z<+1h=1(33`FRzcVeV;fm`');
define('SECURE_AUTH_KEY', 'a0oc.zV{+3fM!T+nuhVCA</:d+X{.2i/.I;H~g`;Ch,TO>MgzI_xMOAU5~=+C#:K');
define('LOGGED_IN_KEY', '7tjTT|j9A+4pJf|~<[/FN -QD ^,2NPvwK3i7},/O*%QPVH{xVj*`qjC]U=#*d(?');
define('NONCE_KEY', 'uUVfNF9=[w*85|s{05|8bR|46Mn+ hK>)B%,83b=JqCpCfq?Q7-MUB=V#O),g1BG');
define('AUTH_SALT', 'QqSP9x`] qmv<kKy.>iXYH7U&uFV}Vcb|%e9TpXT$V&SnEyxN{0r8`d?UT6G%r7T');
define('SECURE_AUTH_SALT', '!|-@F`F)7bOJ!A7B 7@gP^QeD ,)#etdCV(i=-y@_Dg>zk%zKIO||[Av5Jc%KQpK');
define('LOGGED_IN_SALT', 'EQG<Z#|a!&bYHebo)b{Eu]rYmLOI%&o]/gx>2$dPW]lU,$(!,<P=ZYu9]$+|,sz5');
define('NONCE_SALT', 'HOnvEmlbv>cRTp1GHv]+GA|t.mC,L:RIuD*:g@>Bv+/Yw5&)#(Aea5O9bN|27lCC');

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
$table_prefix = 'wspo_';

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
@ini_set('display_errors', 0);

/* Add any custom values between this line and the "stop editing" line. */

define('DISALLOW_FILE_EDIT', true);


/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if (!defined('ABSPATH')) {
	define('ABSPATH', __DIR__ . '/');
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
