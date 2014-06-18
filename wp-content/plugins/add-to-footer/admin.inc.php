<?php
/**
 * Generates the settings page in the Admin
 *
 * @package Add_to_Footer
 */

// If this file is called directly, then abort execution.
if ( ! defined( 'WPINC' ) ) {
	die( "Aren't you supposed to come here via WP-Admin?" );
}

/**
 * Add to Footer options.
 */
function addfoot_options() {

	global $wpdb;
	$poststable = $wpdb->posts;

	$addfoot_settings = addfoot_read_options();

	if ( isset( $_POST['addfoot_save'] ) && check_admin_referer( 'addfoot-admin-options' ) ) {
		$addfoot_settings['enable_plugin'] = isset( $_POST['enable_plugin'] ) ? true : false;
		$addfoot_settings['disable_notice'] = isset( $_POST['disable_notice'] ) ? true : false;
		$addfoot_settings['addfoot_other'] = $_POST['addfoot_other'];
		$addfoot_settings['sc_project'] = $_POST['sc_project'];
		$addfoot_settings['sc_security'] = $_POST['sc_security'];
		$addfoot_settings['ga_ua'] = isset( $_POST['ga_ua'] ) ? true : false;
		$addfoot_settings['ga_uacct'] = $_POST['ga_uacct'];
		$addfoot_settings['ga_domain'] = $_POST['ga_domain'];

		update_option( 'ald_addfoot_settings', $addfoot_settings );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options saved successfully.', 'add-to-footer' ) . '</p></div>';
		echo $str;
	}

	if ( isset( $_POST['addfoot_default'] ) && check_admin_referer( 'addfoot-admin-options' ) ) {
		delete_option( 'ald_addfoot_settings' );
		$addfoot_settings = addfoot_default_options();
		update_option( 'ald_addfoot_settings', $addfoot_settings );

		$str = '<div id="message" class="updated fade"><p>' . __( 'Options set to Default.', 'add-to-footer' ) . '</p></div>';
		echo $str;
	}
?>

<div class="wrap">
	<h2><?php _e( "Add to Footer", 'add-to-footer' ) ?></h2>
	<div id="poststuff">
	<div id="post-body" class="metabox-holder columns-2">
	<div id="post-body-content">
	  <form method="post" id="addfoot_options" name="addfoot_options" onsubmit="return checkForm()">
	    <div id="genopdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'add-to-footer' ); ?>"><br /></div>
	      <h3 class='hndle'><span><?php _e( 'General options', 'add-to-footer' ); ?></span></h3>
	      <div class="inside">
			<table class="form-table">
				<tr><th scope="row" style="background:#<?php if ( $addfoot_settings['enable_plugin'] ) echo 'cfc'; else echo 'fcc'; ?>"><label for="enable_plugin">&nbsp;<?php _e( 'Enable the plugin:', 'add-to-footer' ); ?></label></th>
					<td style="background:#<?php if ( $addfoot_settings['enable_plugin'] ) echo 'cfc'; else echo 'fcc'; ?>"><input type="checkbox" name="enable_plugin" id="enable_plugin" <?php if ( $addfoot_settings['enable_plugin'] ) echo 'checked="checked"' ?> /></td>
				</tr>
				<tr>
					<th scope="row"><label for="disable_notice"><?php _e( 'Disable admin-wide notice:', 'add-to-feed' ); ?></label></th>
					<td>
						<input type="checkbox" name="disable_notice" id="disable_notice" <?php if ( $addfoot_settings['disable_notice'] ) echo 'checked="checked"' ?> />
						<p class="description"><?php _e( 'Disables the "Add to Feed plugin is disabled." notice when the above option is unchecked.', 'add-to-feed' ) ?></p>
					</td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2">&nbsp;<?php _e( 'Statcounter Options:', 'add-to-footer' ); ?></th>
				</tr>
				<tr><th scope="row"><label for="sc_project"><?php _e( 'StatCounter Project ID (Value of sc_project):', 'add-to-footer' ); ?></label></th>
					<td><input type="textbox" name="sc_project" id="sc_project" value="<?php echo esc_attr( stripslashes( $addfoot_settings['sc_project'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="sc_security"><?php _e( 'StatCounter Security ID (Value of sc_security):', 'add-to-footer' ); ?></label></th>
					<td><input type="textbox" name="sc_security" id="sc_security" value="<?php echo esc_attr( stripslashes( $addfoot_settings['sc_security'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2">&nbsp;<?php _e( 'Google Analytics Options:', 'add-to-footer' ); ?></th>
				</tr>
				<tr>
					<th scope="row"><label for="ga_ua"><?php _e( 'Enable Universal Analytics:', 'add-to-footer' ); ?></label></th>
					<td>
						<input type="checkbox" name="ga_ua" id="ga_ua" <?php if ( $addfoot_settings['ga_ua'] ) echo 'checked="checked"' ?> />
						<p class="description"><?php printf( __( 'Only check this box if you have upgraded to Universal Analytics. Visit the <a href="%s" target="_blank">Universal Analytics Upgrade Center</a> to know more', 'add-to-footer' ), esc_url( 'https://developers.google.com/analytics/devguides/collection/upgrade/' ) ); ?></p>
					</td>
				</tr>
				<tr><th scope="row"><label for="ga_uacct"><?php _e( 'Tracking ID:', 'add-to-footer' ); ?></label></th>
					<td><input type="textbox" name="ga_uacct" id="ga_uacct" value="<?php echo esc_attr( stripslashes( $addfoot_settings['ga_uacct'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr><th scope="row"><label for="ga_domain"><?php _e( 'Multiple sub-domain support (Value of _setDomainName):', 'add-to-footer' ); ?></label></th>
					<td><input type="textbox" name="ga_domain" id="ga_domain" value="<?php echo esc_attr( stripslashes( $addfoot_settings['ga_domain'] ) ); ?>" style="width:250px" /></td>
				</tr>
				<tr style="background: #eee"><th scope="row" colspan="2">&nbsp;<?php _e( 'Other HTML or JavaScript', 'add-to-footer' ); ?></th>
				</tr>
				<tr><th scope="row" colspan="2"><?php _e( 'Any other HTML (no PHP) to add to <code>wp_footer</code>:', 'add-to-footer' ); ?></th>
				</tr>
				<tr><td scope="row" colspan="2"><textarea name="addfoot_other" id="addfoot_other" rows="15" cols="80"><?php echo stripslashes( $addfoot_settings['addfoot_other'] ); ?></textarea></td>
				</tr>
			</table>
	      </div>
	    </div>
		<p>
		  <input type="submit" name="addfoot_save" id="addfoot_save" value="<?php _e( 'Save Options', 'add-to-footer' ); ?>" class="button button-primary" />
		  <input type="submit" name="addfoot_default" id="addfoot_default" value="<?php _e( 'Default Options', 'add-to-footer' ); ?>" class="button button-secondary" onclick="if ( ! confirm( '<?php _e( "Do you want to set options to Default?", 'add-to-footer' ); ?>' ) ) return false;" />
		</p>
		<?php wp_nonce_field( 'addfoot-admin-options' ); ?>
	  </form>

	</div><!-- /post-body-content -->
	<div id="postbox-container-1" class="postbox-container">
	  <div id="side-sortables" class="meta-box-sortables ui-sortable">
		  <?php addfoot_admin_side(); ?>
	  </div><!-- /side-sortables -->
	</div><!-- /postbox-container-1 -->
	</div><!-- /post-body -->
	<br class="clear" />
	</div><!-- /poststuff -->
</div><!-- /wrap -->

<?php
}


/**
 * Function to generate the right sidebar of the Settings page.
 */
function addfoot_admin_side() {
?>
    <div id="donatediv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'add-to-footer' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Support the development', 'add-to-footer' ); ?></span></h3>
      <div class="inside">
		<div id="donate-form">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="donate@ajaydsouza.com">
				<input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="<?php _e( 'Donation for Add to Footer', 'add-to-footer' ); ?>">
				<input type="hidden" name="item_number" value="addfoot_admin">
				<strong><?php _e( 'Enter amount in USD: ', 'add-to-footer' ); ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="button_subtype" value="services">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e( 'Send your donation to the author of Add to Footer', 'add-to-footer' ); ?>">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
		</div>
      </div>
    </div>
    <div id="followdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'add-to-footer' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Follow me', 'add-to-footer' ); ?></span></h3>
      <div class="inside">
		<div id="follow-us">
			<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
			<div style="text-align:center"><a href="https://twitter.com/ajaydsouza" class="twitter-follow-button" data-show-count="false" data-size="large" data-dnt="true">Follow @ajaydsouza</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//pladdfootorm.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script></div>
		</div>
      </div>
    </div>
    <div id="qlinksdiv" class="postbox"><div class="handlediv" title="<?php _e( 'Click to toggle', 'add-to-footer' ); ?>"><br /></div>
      <h3 class='hndle'><span><?php _e( 'Quick links', 'add-to-footer' ); ?></span></h3>
      <div class="inside">
        <div id="quick-links">
			<ul>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/add-to-footer/"><?php _e( 'Add to Footer plugin page', 'add-to-footer' ); ?></a></li>
				<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e( 'Other plugins', 'add-to-footer' ); ?></a></li>
				<li><a href="http://ajaydsouza.com/"><?php _e( "Ajay's blog", 'add-to-footer' ); ?></a></li>
				<li><a href="https://wordpress.org/plugins/add-to-footer/faq/"><?php _e( 'FAQ', 'add-to-footer' ); ?></a></li>
				<li><a href="http://wordpress.org/support/plugin/add-to-footer"><?php _e( 'Support', 'add-to-footer' ); ?></a></li>
				<li><a href="https://wordpress.org/support/view/plugin-reviews/add-to-footer"><?php _e( 'Reviews', 'add-to-footer' ); ?></a></li>
			</ul>
        </div>
      </div>
    </div>

<?php
}


/**
 * Display a message at the top of Admin pages if the plugin is disabled. Filters `admin_notices`.
 */
function addfoot_admin_notice() {
	$addfoot_settings = addfoot_read_options();

	$plugin_settings_page = admin_url( 'options-general.php?page=addfoot_options' );

	if ( $addfoot_settings['enable_plugin'] || $addfoot_settings['disable_notice'] ) {
		return;
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

    echo '<div class="error">
       <p>' . sprintf( __( 'Add to Footer plugin is disabled. Please visit the <a href="%s">plugin settings page</a> to enable the plugin or disable this notice.', 'add-to-footer' ), $plugin_settings_page ) . '</p>
    </div>';
}
add_action( 'admin_notices', 'addfoot_admin_notice' );


/**
 * Add menu item in WP-Admin.
 *
 */
function addfoot_adminmenu() {
	$plugin_page = add_options_page( __( "Add to Footer", 'add-to-footer' ), __( "Add to Footer", 'add-to-footer' ), 'manage_options', 'addfoot_options', 'addfoot_options');
	add_action( 'admin_head-'. $plugin_page, 'addfoot_adminhead' );
}
add_action( 'admin_menu', 'addfoot_adminmenu' );


/**
 * Function scripts to Admin head.
 *
 * @access public
 * @return void
 */
function addfoot_adminhead() {
	global $addfoot_url;

	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );
?>

	<style type="text/css">
	.postbox .handlediv:before {
		right:12px;
		font:400 20px/1 dashicons;
		speak:none;
		display:inline-block;
		top:0;
		position:relative;
		-webkit-font-smoothing:antialiased;
		-moz-osx-font-smoothing:grayscale;
		text-decoration:none!important;
		content:'\f142';
		padding:8px 10px;
	}
	.postbox.closed .handlediv:before {
		content: '\f140';
	}
	.wrap h2:before {
	    content: "\f164";
	    display: inline-block;
	    -webkit-font-smoothing: antialiased;
	    font: normal 29px/1 'dashicons';
	    vertical-align: middle;
	    margin-right: 0.3em;
	}
	</style>

	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('addfeed_options');
		});
		//]]>
	</script>

	<script type="text/javascript" language="JavaScript">
		//<![CDATA[
		function checkForm() {
		answer = true;
		if (siw && siw.selectingSomething)
			answer = false;
		return answer;
		}//
		//]]>
	</script>

<?php
}

?>