<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'orangnad_wp95');

/** MySQL database username */
define('DB_USER', 'orangnad_wp95');

/** MySQL database password */
define('DB_PASSWORD', '3ZpSF-w5@6');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'd6ofppybjeqimli21xo0m1e6seyekortodwetk3okqfdyxlx0rfpbcg1rkimby0d');
define('SECURE_AUTH_KEY',  'po9azsqqte7gfn578emlyqjwfvm0jchsixultqmjrq1h4fbcnnud5qi8w7mdeoc0');
define('LOGGED_IN_KEY',    't1nyoj8dglexdb5xg88rmdsmd0xpc9fjvx6jmfc1svauyt3k8f0hdk4e2xs70mci');
define('NONCE_KEY',        '5nmljmy7cwuodgh9bg1abckpejeaz4srttloiulbvevev7scbkxup41pi6qx1zdz');
define('AUTH_SALT',        'xdohig7f6mhqgxiehmoem443smzllar4a4vjxtpn12224pxxjkeqb0udo8y4fm4g');
define('SECURE_AUTH_SALT', 'pjykx5mvdb2kfpnml3xzvaquxmvcc8jyyri8zyl1weudokdmmpqcpojrasfys0am');
define('LOGGED_IN_SALT',   '7nvseoleebvjmwqptykit0owzzyiwlobd85eka4zveh281zmic4ll1dnqej63wcx');
define('NONCE_SALT',       'g0qipeie2sa0hmhfjhp8ru768a47uivvamffb49uvj7ixtualxgtv7d9scqgmqqs');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp2z_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

define( 'AUTOSAVE_INTERVAL', 300 );
define( 'WP_POST_REVISIONS', 5 );
define( 'EMPTY_TRASH_DAYS', 7 );
define( 'WP_CRON_LOCK_TIMEOUT', 120 );
/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
