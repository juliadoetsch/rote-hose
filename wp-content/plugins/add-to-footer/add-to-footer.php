<?php
/*
Plugin Name: Add to Footer
Version:     1.2
Plugin URI:  http://ajaydsouza.com/wordpress/plugins/add-to-footer/
Description: Allows you to add absolutely anything to the footer of your WordPress theme
Author:      Ajay D'Souza
Author URI:  http://ajaydsouza.com/
*/

if (!defined('ABSPATH')) die("Aren't you supposed to come here via WP-Admin?");

define('ALD_ADDFOOT_DIR', dirname(__FILE__));
define('ADDFOOT_LOCAL_NAME', 'addfoot');

// Pre-2.6 compatibility
if ( ! defined( 'WP_CONTENT_URL' ) )
      define( 'WP_CONTENT_URL', get_option( 'siteurl' ) . '/wp-content' );
if ( ! defined( 'WP_CONTENT_DIR' ) )
      define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
if ( ! defined( 'WP_PLUGIN_URL' ) )
      define( 'WP_PLUGIN_URL', WP_CONTENT_URL. '/plugins' );
if ( ! defined( 'WP_PLUGIN_DIR' ) )
      define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );

// Guess the location
$addfoot_path = WP_PLUGIN_DIR.'/'.plugin_basename(dirname(__FILE__));
$addfoot_url = WP_PLUGIN_URL.'/'.plugin_basename(dirname(__FILE__));

function ald_addfoot_init() {
	//* Begin Localization Code */
	$addfoot_localizationName = ADDFOOT_LOCAL_NAME;
	$addfoot_comments_locale = get_locale();
	$addfoot_comments_mofile = ALD_ADDFOOT_DIR . "/languages/" . $addfoot_localizationName . "-". $addfoot_comments_locale.".mo";
	load_textdomain($addfoot_localizationName, $addfoot_comments_mofile);
	//* End Localization Code */
}
add_action('init', 'ald_addfoot_init');

/*********************************************************************
*				Main Function (Do not edit)							*
********************************************************************/
add_action('wp_footer','ald_addfoot');
function ald_addfoot() {

	$addfoot_settings = addfoot_read_options();

	$addfoot_other = stripslashes($addfoot_settings[addfoot_other]);
	$sc_project = stripslashes($addfoot_settings[sc_project]);
	$sc_security = stripslashes($addfoot_settings[sc_security]);
	$ga_uacct = stripslashes($addfoot_settings[ga_uacct]);
	$ga_domain = stripslashes($addfoot_settings[ga_domain]);


	if ($addfoot_other != '') {
		echo $addfoot_other;
	}

	if ($sc_project != ''){
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

	if ($ga_uacct != '') {
?>
	<!-- Start Google Analytics -->
	<script type="text/javascript">
	// <![CDATA[
	  var _gaq = _gaq || [];
	  _gaq.push(['_setAccount', '<?php echo $ga_uacct; ?>']);
	  _gaq.push(['_setDomainName', '<?php echo $ga_url; ?>']);
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

// Default Options
function addfoot_default_options() {

	$ga_url = ".".parse_url(get_option('home'),PHP_URL_HOST);

	$addfoot_settings = 	Array (
						'enable_plugin' => false,		// Enable plugin switch
						'sc_project' => '',		// StatCounter Project ID
						'sc_security' => '',		// StatCounter Security String
						'ga_uacct' => '',			// Google Analytics Web Property ID
						'ga_domain' => $ga_url,			// Google Analytics Value of _setDomainName
						'addfoot_other' => '',	// For any other code
						);
	return $addfoot_settings;
}

// Function to read options from the database
function addfoot_read_options() 
{
	$addfoot_settings_changed = false;
	
	//ald_addfoot_activate();
	
	$defaults = addfoot_default_options();
	
	$addfoot_settings = array_map('stripslashes',(array)get_option('ald_addfoot_settings'));
	unset($addfoot_settings[0]); // produced by the (array) casting when there's nothing in the DB
	
	foreach ($defaults as $k=>$v) {
		if (!isset($addfoot_settings[$k]))
			$addfoot_settings[$k] = $v;
		$addfoot_settings_changed = true;	
	}
	if ($addfoot_settings_changed == true)
		update_option('ald_addfoot_settings', $addfoot_settings);
	
	return $addfoot_settings;

}


// This function adds an Options page in WP Admin
if (is_admin() || strstr($_SERVER['PHP_SELF'], 'wp-admin/')) {
	require_once(ALD_ADDFOOT_DIR . "/admin.inc.php");

		// Add meta links
	function addfoot_plugin_actions( $links, $file ) {
		static $plugin;
		if (!$plugin) $plugin = plugin_basename(__FILE__);
	 
		// create link
		if ($file == $plugin) {
			$links[] = '<a href="' . admin_url( 'options-general.php?page=addfoot_options' ) . '">' . __('Settings', ADDFOOT_LOCAL_NAME ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/support/">' . __('Support', ADDFOOT_LOCAL_NAME ) . '</a>';
			$links[] = '<a href="http://ajaydsouza.com/donate/">' . __('Donate', ADDFOOT_LOCAL_NAME ) . '</a>';
		}
		return $links;
	}
	global $wp_version;
	if ( version_compare( $wp_version, '2.8alpha', '>' ) )
		add_filter( 'plugin_row_meta', 'addfoot_plugin_actions', 10, 2 ); // only 2.8 and higher
	else add_filter( 'plugin_action_links', 'addfoot_plugin_actions', 10, 2 );

}


?>