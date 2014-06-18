<?php
/**
 * Fired when the plugin is uninstalled
 *
 * @package Add_to_Footer
 */

// If uninstall not called from WordPress, then exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option('ald_addfoot_settings');
?>