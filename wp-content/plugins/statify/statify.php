<?php
/*
Plugin Name: Statify
Description: Kompakte, begreifliche und datenschutzkonforme Statistik für WordPress.
Author: Sergej M&uuml;ller
Author URI: http://wpcoder.de
Plugin URI: http://statify.de
License: GPLv2 or later
Version: 1.3.0
*/

/*
Copyright (C)  2011-2014 Sergej Müller

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, write to the Free Software Foundation, Inc.,
51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/


/* Quit */
defined('ABSPATH') OR exit;


/* Konstanten */
define('STATIFY_FILE', __FILE__);
define('STATIFY_BASE', plugin_basename(__FILE__));


/* Hooks */
add_action(
	'plugins_loaded',
	array(
		'Statify',
		'instance'
	)
);
register_activation_hook(
	__FILE__,
	array(
		'Statify_Install',
		'init'
	)
);
register_uninstall_hook(
	__FILE__,
	array(
		'Statify_Uninstall',
		'init'
	)
);


/* Autoload Init */
spl_autoload_register('statify_autoload');

/* Autoload Funktion */
function statify_autoload($class) {
	if ( in_array($class, array('Statify', 'Statify_Dashboard', 'Statify_Install', 'Statify_Uninstall', 'Statify_Table', 'Statify_XMLRPC')) ) {
		require_once(
			sprintf(
				'%s/inc/%s.class.php',
				dirname(__FILE__),
				strtolower($class)
			)
		);
	}
}