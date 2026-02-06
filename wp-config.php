<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'live_AQnx1' );

/** Database username */
define( 'DB_USER', 'live_user_AQnx1' );

/** Database password */
define( 'DB_PASSWORD', 'qvVs8hjvOVaHN1PTUB0lqoB4VHDJq4Y78V' );

/** Database hostname */
define( 'DB_HOST', 'mysql.10web.site' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

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
define( 'AUTH_KEY',          '^8a!:5y&bpJD1Zi<;UTrM53%]Z($nlq-}:sN%xa!{0V.L:3JY<QkM4GbKl@&^OCq' );
define( 'SECURE_AUTH_KEY',   '$YHK[}|bgGE);8,:<^&b!?}D;^;Hw|@+e`6}u]O`C<nehHm)H>(o,l=-$O]I/krt' );
define( 'LOGGED_IN_KEY',     '6v2Itv~[]GGxJsaCj^9AXZstGCs,z/![N}OD|I^ZbQ=4Rs:SP.+F {ao{i>EG #7' );
define( 'NONCE_KEY',         'Oc>q!wgW lKLxV^e[m@*I23n;|vRr/5Z*/hsr3>cofQ~7Q:ar>R=@nt h2AFpXyR' );
define( 'AUTH_SALT',         'c*:5/!n_Is&QmVUmU6?h[j*z/ZTH<J6)*aCF]-1,#L}}Au:A!UUl-ApW&cX+0wto' );
define( 'SECURE_AUTH_SALT',  '+j2XSA{[OY-P]n]TA$0X,-9MJ;_:!YO&(KB8H_eAe@fMX<k<S}!Fihb$fIh!5Su9' );
define( 'LOGGED_IN_SALT',    '?h5BZWU[}k{r^j#-KDWqEqOhf[~LDWKS7M]B]V4&o,f~I)RLMPH-ACj5p*M8.DUG' );
define( 'NONCE_SALT',        'W`H7tIB_Tb4@hoq]#I*wh:x6>=imj!LL{%(YO+2vPo:?2P=tED{(9CD<YNtIjJF|' );
define( 'WP_CACHE_KEY_SALT', '@^10M6i/TN9.iy?xc~:daP%S9Yg:p#,jfOi8yjPwdP<L+J[2[eG2r7Me~@?M)5ng' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
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
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );


/* Add any custom values between this line and the "stop editing" line. */



define( 'WP_MEMORY_LIMIT', '256M' );
define( 'WP_REDIS_HOST', '10.44.10.10' );
define( 'WP_REDIS_PREFIX', 'TENWEBLXC-771459-object-cache-' );
define( 'WP_REDIS_PASSWORD', ["redis_user_771459","QVezRQVnwSoU4qL8gblLWU4Bml65zlWWwm"] );
define( 'WP_REDIS_MAXTTL', '360' );
define( 'WP_REDIS_IGNORED_GROUPS', ["comment","counts","plugins","wc_session_id"] );
define( 'WP_REDIS_GLOBAL_GROUPS', ["users","userlogins","useremail","userslugs","usermeta","user_meta","site-transient","site-options","site-lookup","site-details","blog-lookup","blog-details","blog-id-cache","rss","global-posts","global-cache-test"] );
define( 'WP_REDIS_TIMEOUT', '5' );
define( 'WP_REDIS_READ_TIMEOUT', '5' );
define( 'TENWEB_OBJECT_CACHE', '1' );
define( 'TENWEB_CACHE', '1' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
