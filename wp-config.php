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
define( 'DB_NAME', 'woo_orders_api' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Oy05x0,ymIi*jZKvu}~=x7Oo83^C6:3U?%6f`v5nvx#U![3s`H@SL.rE1YS9{lsX' );
define( 'SECURE_AUTH_KEY',  'mr<#{Z/5F(oFQ$%/QzA;XfoE$hLk(R!xv-skpnOi J/BUu%7r6F.uutGAMZ&o# }' );
define( 'LOGGED_IN_KEY',    'HciRs&DEk6]}|)(P+8xju.kSN@$kPdr^^[^Ee!9SWmPVMS{|,NXh%O^hrzm.z5Z>' );
define( 'NONCE_KEY',        'M+pYS4ks|N.*CYV*JUk4K<QdUQ8:GvY7N:>4=z!D2<I7`3X$Y3XN6@Q(8BArbiDB' );
define( 'AUTH_SALT',        '&p.#Hl f|c5z_:BFgp:UWBP5%v|`YyKV0^j%K@1q7p4Ei|eD0*/>wi.`~l^$F%]l' );
define( 'SECURE_AUTH_SALT', '3FHN`I^/^%H0J5EMaV?F:72Kyt2*}xxO/[_V;>Ljb}NzH(!t}UM.//C/F[]xv-%&' );
define( 'LOGGED_IN_SALT',   'SZQI#<VVJkvs0dR<BkC:A?:!T4ph*|Gy|nX}fZ$Z.(?/|H}d=>#-hWQAkdRL2sy@' );
define( 'NONCE_SALT',       'hk%O#}@M XscQUgi*Kj]Rf6K DEjPbjN~Fq 5UFW>vjfRm)l0IBfXH`EnKQJLXou' );

define('JWT_AUTH_SECRET_KEY', 'hjRN=c{Uv@%Vv2hK0%4;|Jkz}o#(Ws9HN3%%+.d>G1ydH.lLRBSFWE-E3xey/HFC');
define('JWT_AUTH_CORS_ENABLE', true);

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
