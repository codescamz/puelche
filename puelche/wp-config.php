<?php
/** 
 * Configuración básica de WordPress.
 *
 * Este archivo contiene las siguientes configuraciones: ajustes de MySQL, prefijo de tablas,
 * claves secretas, idioma de WordPress y ABSPATH. Para obtener más información,
 * visita la página del Codex{@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} . Los ajustes de MySQL te los proporcionará tu proveedor de alojamiento web.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** Ajustes de MySQL. Solicita estos datos a tu proveedor de alojamiento web. ** //
/** El nombre de tu base de datos de WordPress */
define('DB_NAME', 'naconwor_puelche');

/** Tu nombre de usuario de MySQL */
define('DB_USER', 'naconwor_puelche');

/** Tu contraseña de MySQL */
define('DB_PASSWORD', 'VL%4~ER7=Kr]');

/** Host de MySQL (es muy probable que no necesites cambiarlo) */
define('DB_HOST', 'localhost');

/** Codificación de caracteres para la base de datos. */
define('DB_CHARSET', 'utf8mb4');

/** Cotejamiento de la base de datos. No lo modifiques si tienes dudas. */
define('DB_COLLATE', '');

/**#@+
 * Claves únicas de autentificación.
 *
 * Define cada clave secreta con una frase aleatoria distinta.
 * Puedes generarlas usando el {@link https://api.wordpress.org/secret-key/1.1/salt/ servicio de claves secretas de WordPress}
 * Puedes cambiar las claves en cualquier momento para invalidar todas las cookies existentes. Esto forzará a todos los usuarios a volver a hacer login.
 *
 * @since 2.6.0
 */
define('AUTH_KEY', '{ee{#Es&r&1y!cCqUQwgwVnrCUc=#;|cVJ~E Y:?wNb%K>s 2=s@Uau[@pHhVx{c');
define('SECURE_AUTH_KEY', 'Qs0{hon8ugb!i|fgHVO#2>HRw6C>nTnCbqo23Pa!uF?^f7]$|Kl]EC]D-Hb+LNG$');
define('LOGGED_IN_KEY', '1n~z <B^IV}_-{Fsb.^YjAQx58m41gkNpK DW_I9>q6#3i81{&3-Lw=!_][#9G<:');
define('NONCE_KEY', '^/hGU`rFMq<ZRK9e?<.?7<2,p~kp+$Zs-5ZU7wkn{Ql:uq74k6N3NaHZUT;D~{Ol');
define('AUTH_SALT', ':YRs$#Zr*wq}vgUHU;o0Aoo+F{(+lX$qv[,>:?Ag-TF:J9TG|[V4n^oJAaDgvg5`');
define('SECURE_AUTH_SALT', '6pAu$;fF+T5$/Ko!%?a>$1W&zIRieXtY-0HfL2^z&aN)3+uFSayeQWdsn$Y$}<]H');
define('LOGGED_IN_SALT', 'g_`rzvT>-W.-V{~L}k4`og1/gldGN*fpiU#XZ#Ws. ds+QA2Vcy]Ot{eZNj65+!;');
define('NONCE_SALT', 'N6)</Pb7$[l#zZKDZ@L6V`,Y_~eli1XD0T?4Zszgc=4KUD#XbOb(ZyJ`8`&iffVZ');

/**#@-*/

/**
 * Prefijo de la base de datos de WordPress.
 *
 * Cambia el prefijo si deseas instalar multiples blogs en una sola base de datos.
 * Emplea solo números, letras y guión bajo.
 */
$table_prefix  = 'pu_';


/**
 * Para desarrolladores: modo debug de WordPress.
 *
 * Cambia esto a true para activar la muestra de avisos durante el desarrollo.
 * Se recomienda encarecidamente a los desarrolladores de temas y plugins que usen WP_DEBUG
 * en sus entornos de desarrollo.
 */
define('WP_DEBUG', false);

/* ¡Eso es todo, deja de editar! Feliz blogging */

/** WordPress absolute path to the Wordpress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

