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

 * * ABSPATH

 *

 * @link https://wordpress.org/support/article/editing-wp-config-php/

 *

 * @package WordPress

 */


// ** Database settings - You can get this info from your web host ** //

/** The name of the database for WordPress */

define( 'DB_NAME', "moniwowk_wp702" );


/** Database username */

define( 'DB_USER', "root" );


/** Database password */

define( 'DB_PASSWORD', "" );


/** Database hostname */

define( 'DB_HOST', "localhost" );


/** Database charset to use in creating database tables. */

define( 'DB_CHARSET', 'utf8mb4' );


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

define( 'AUTH_KEY',         'rW3QMA=Nf#S)kPnwe.G-s`F6Rs>cj`r9Y^LQ{8lMcFQbo?`bGuL&G-}05fc2%eIn' );

define( 'SECURE_AUTH_KEY',  '>orBB{(x]|N^S@ 1Q<1SX(}:5;+!mGza^GWH3EvO?#OlD^3ev^{gVZlp]a>126n1' );

define( 'LOGGED_IN_KEY',    '!=XE_XNy#m=^4Ofspso].WO  sc::=eEK5^54pB8aJW$f6 /KG>U,:@htv,OGyL~' );

define( 'NONCE_KEY',        '{u3T0IahAO$Jhdij%R22i#KAa_UXq9#}YS;%TqwSEWy)5m|@{ACiXccvs}Rp 2e;' );

define( 'AUTH_SALT',        'rcl:%%TGVXtPbszrq`s]Z4gN`.dlz-,5_KhfnKF^C1yJ:|[Ih5C~Q^ HQM/)P{A>' );

define( 'SECURE_AUTH_SALT', 'XJ-T=)}g(/LQd^{?W2Sf;xjd8lYqVG5uW|73DaTPVz8ZxSOuc:Nh%:a+2F?5p.VN' );

define( 'LOGGED_IN_SALT',   'k6fI#EvxjWLH3PE; ddNB`298M4!sXKLUu%RI/PzMjPiae-mJ]V2=EqgeL/,}*q6' );

define( 'NONCE_SALT',       'TOhYSf(K`*kRp5{[[66N/mfU-TBJ1fk2z+oc{|}!PCu*Su)]0qq;uBD;gr|I8*R6' );


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




/* That's all, stop editing! Happy publishing. */


/** Absolute path to the WordPress directory. */

if ( ! defined( 'ABSPATH' ) ) {

	define( 'ABSPATH', __DIR__ . '/' );

}


/** Sets up WordPress vars and included files. */

require_once ABSPATH . 'wp-settings.php';

