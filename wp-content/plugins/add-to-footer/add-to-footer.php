<?php
/**
 * Add to Feed lets you add a copyright notice and custom text or HTML to your WordPress feed.
 *
 * @package Add_to_Footer
 *
 * @wordpress-plugin
 * Plugin Name: Add to Footer
 * Version:     1.3
 * Plugin URI:  http://ajaydsouza.com/wordpress/plugins/add-to-footer/
 * Description: Allows you to add absolutely anything to the footer of your WordPress theme
 * Author:      Ajay D'Souza
 * Author URI:  http://ajaydsouza.com/
 * Text Domain:	add-to-footer
 * License:		GPL-2.0+
 * License URI:	http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:	/languages
*/

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Holds the filesystem directory path.
 */
define( 'ALD_ADDFOOT_DIR', dirname( __FILE__ ) );


// Set the global variables for Better Search path and URL
$addfoot_path = plugin_dir_path( __FILE__ );
$addfoot_url = plugins_url() . '/' . plugin_basename( dirname( __FILE__ ) );


/**
 * Function to load translation files.
 */
function addfoot_lang_init() {
	load_plugin_textdomain( 'add-to-footer', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'init', 'addfoot_lang_init' );


/**
 * Adds scripts to the footer. Filters `wp_footer`.
 */
function ald_addfoot() {

	$addfoot_settings = addfoot_read_options();

	$addfoot_other = stripslashes( $addfoot_settings['addfoot_other'] );
	$sc_project = stripslashes( $addfoot_settings['sc_project'] );
	$sc_security = stripslashes( $addfoot_settings['sc_security'] );
	$ga_uacct = stripslashes( $addfoot_settings['ga_uacct'] );
	$ga_domain = stripslashes( $addfoot_settings['ga_domain'] );


	if ( '' != $addfoot_other ) {
		echo $addfoot_other;
	}

	if ( '' != $sc_project ) {
?>
	<!-- Start of StatCounter Code -->
	<script type="text/javascript">
	// <![CDATA[
		var sc_project=<?php echo $sc_project; ?>;
		var sc_security="<?php echo $sc_security; ?>";
		var sc_invisible=1;
		var sc_click_stat=1;
	// ]]>
	</script>
	<script type="text/javascript" src="http://www.statcounter.com/counter/counter_xhtml.js"></script>
	<noscript><div class="statcounter"><a title="tumblr hit counter" href="http://statcounter.com/tumblr/" class="statcounter"><img class="statcounter" src="http://c.statcounter.com/<?php echo $sc_project; ?>/0/<?php echo $sc_security; ?>/1/" alt="tumblr hit counter" /></a></div></noscript>
	<!-- End of StatCounter Code -->
<?php	}

	if ( '' != $ga_uacct ) {
		if ( $addfoot_settings['ga_ua'] ) {
?>

	<!-- Start Google Analytics -->
	<script>
	  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
	  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
	  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
	  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

	  ga('create', '<?php echo $ga_uacct; ?>', '<?php echo $ga_domain; ?>');
	  ga('send', 'pageview');

	</script>
	<!-- End Google Analytics -->

<?php } else { ?>

	<!-- Start Google Analytics -->
	<script type="text/javascript">
	// <![CDATA[
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', '<?php echo $ga_uacct; ?>']);
	  _gaq.push(['_setDomainName', '<?php echo $ga_domain; ?>']);
	  _gaq.push(['_setAllowLinker', true]);
	  _gaq.push(['_trackPageview']);

	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	// ]]>
	</script>
	<!-- End Google Analytics -->
<?php	}
	}

}
add_action( 'wp_footer', 'ald_addfoot' );


/**
 * Default options.
 *
 * @return array Array of default options
 */
function addfoot_default_options() {

	$ga_url = parse_url( get_option( 'home' ), PHP_URL_HOST );

	$addfoot_settings = array (
		'enable_plugin' => false,	// Enable plugin switch
		'disable_notice' => false,	// // Disable notice that is displayed when enable_plugin is false
		'sc_project' => '',			// StatCounter Project ID
		'sc_security' => '',		// StatCounter Security String
		'ga_uacct' => '',			// Google Analytics Web Property ID
		'ga_ua' => false,			// Choose between Classic Analytics or Universal Analytics
		'ga_domain' => $ga_url,		// Google Analytics Value of _setDomainName
		'addfoot_other' => '',		// For any other code
	);
	return apply_filters( 'addfoot_default_options', $addfoot_settings );
}


/**
 * Function to read options from the database and add any new ones.
 *
 * @return array Options from the database
 */
function addfoot_read_options() {
	$addfoot_settings_changed = false;

	$defaults = addfoot_default_options();

	$addfoot_settings = array_map( 'stripslashes', (array) get_option( 'ald_addfoot_settings' ) );
	unset( $addfoot_settings[0] ); // produced by the (array) casting when there's nothing in the DB

	// If there are any new options added to the Default Options array, let's add them
	foreach ( $defaults as $k=>$v ) {
		if ( ! isset( $addfoot_settings[ $k ] ) ) {
			$addfoot_settings[ $k ] = $v;
		}
		$addfoot_settings_changed = true;
	}

	if ( true == $addfoot_settings_changed ) {
		update_option( 'ald_addfoot_settings', $addfoot_settings );
	}

	return apply_filters( 'addfoot_read_options', $addfoot_settings );
}


/**
 *  Admin option
 *
 */
if ( is_admin() || strstr( $_SERVER['PHP_SELF'], 'wp-admin/' ) ) {

	/**
	 *  Load the admin pages if we're in the Admin.
	 *
	 */
	require_once( ALD_ADDFOOT_DIR . "/admin.inc.php" );

	/**
	 * Adding WordPress plugin action links.
	 *
	 * @param array $links
	 * @return array
	 */
	function addfoot_plugin_actions_links( $links ) {

		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'options-general.php?page=addfoot_options' ) . '">' . __( 'Settings', 'add-to-footer' ) . '</a>'
			),
			$links
		);

	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'addfoot_plugin_actions_links' );

	/**
	 * Add meta links on Plugins page.
	 *
	 * @param array $links
	 * @param string $file
	 * @return array
	 */
	function addfoot_plugin_actions( $links, $file ) {
		static $plugin;
		if ( ! $plugin ) {
			$plugin = plugin_basename( __FILE__ );
		}

		// create link
		if ( $file == $plugin ) {
			$links[] = '<a href="http://wordpress.org/support/plugin/better-search">' . __( 'Support', 'add-to-footer' ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/donate/">' . __( 'Donate', 'add-to-footer' ) . '</a>';
		}
		return $links;
	}
	add_filter( 'plugin_row_meta', 'addfoot_plugin_actions', 10, 2 ); // only 2.8 and higher

} // End admin.inc

?>