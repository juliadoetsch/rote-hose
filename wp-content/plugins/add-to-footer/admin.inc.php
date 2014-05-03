<?php
/**********************************************************************
*					Admin Page										*
*********************************************************************/

function addfoot_options() {
	
	global $wpdb;
    $poststable = $wpdb->posts;

	$addfoot_settings = addfoot_read_options();

	if($_POST['addfoot_save']){
		$addfoot_settings[enable_plugin] = (($_POST['enable_plugin']) ? true : false);
		$addfoot_settings[addfoot_other] = ($_POST['addfoot_other']);
		$addfoot_settings[sc_project] = ($_POST['sc_project']);
		$addfoot_settings[sc_security] = ($_POST['sc_security']);
		$addfoot_settings[ga_uacct] = ($_POST['ga_uacct']);
		$addfoot_settings[ga_domain] = ($_POST['ga_domain']);

		update_option('ald_addfoot_settings', $addfoot_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options saved successfully.',ADDFOOT_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
	
	if ($_POST['addfoot_default']){
		delete_option('ald_addfoot_settings');
		$addfoot_settings = addfoot_default_options();
		update_option('ald_addfoot_settings', $addfoot_settings);
		
		$str = '<div id="message" class="updated fade"><p>'. __('Options set to Default.',ADDFOOT_LOCAL_NAME) .'</p></div>';
		echo $str;
	}
?>

<div class="wrap">
 	<div id="page-wrap">
	<div id="inside">
		<div id="header">
		<h2>Add to Footer</h2>
		</div>
	  <div id="side">
		<div class="side-widget">
			<span class="title"><?php _e('Support the development',ADDFOOT_LOCAL_NAME) ?></span>
			<div id="donate-form">
				<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
				<input type="hidden" name="cmd" value="_xclick">
				<input type="hidden" name="business" value="donate@ajaydsouza.com">
				<input type="hidden" name="lc" value="IN">
				<input type="hidden" name="item_name" value="Donation for Add to Footer">
				<input type="hidden" name="item_number" value="addfoot">
				<strong><?php _e('Enter amount in USD: ',ADDFOOT_LOCAL_NAME) ?></strong> <input name="amount" value="10.00" size="6" type="text"><br />
				<input type="hidden" name="currency_code" value="USD">
				<input type="hidden" name="button_subtype" value="services">
				<input type="hidden" name="bn" value="PP-BuyNowBF:btn_donate_LG.gif:NonHosted">
				<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="<?php _e('Send your donation to the author of',ADDFOOT_LOCAL_NAME) ?> Add to All?">
				<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
				</form>
			</div>
		</div>
		<div class="side-widget">
		<span class="title"><?php _e('Quick links',ADDFOOT_LOCAL_NAME) ?></span>				
		<ul>
			<li><a href="http://ajaydsouza.com/wordpress/plugins/add-to-footer/"><?php _e('Add to Footer ');_e('plugin page',ADDFOOT_LOCAL_NAME) ?></a></li>
			<li><a href="http://ajaydsouza.com/wordpress/plugins/"><?php _e('Other plugins',ADDFOOT_LOCAL_NAME) ?></a></li>
			<li><a href="http://ajaydsouza.com/"><?php _e('Ajay\'s blog',ADDFOOT_LOCAL_NAME) ?></a></li>
			<li><a href="http://ajaydsouza.com/support/"><?php _e('Support',ADDFOOT_LOCAL_NAME) ?></a></li>
			<li><a href="http://twitter.com/ajaydsouza"><?php _e('Follow @ajaydsouza on Twitter',ADDFOOT_LOCAL_NAME) ?></a></li>
		</ul>
		</div>
		<div class="side-widget">
		<span class="title"><?php _e('Recent developments',ADDFOOT_LOCAL_NAME) ?></span>				
		<?php require_once(ABSPATH . WPINC . '/rss.php'); wp_widget_rss_output('http://ajaydsouza.com/archives/category/wordpress/plugins/feed/', array('items' => 5, 'show_author' => 0, 'show_date' => 1));
		?>
		</div>
		<div class="side-widget">
		<iframe src="//www.facebook.com/plugins/likebox.php?href=http%3A%2F%2Fwww.facebook.com%2Fajaydsouzacom&amp;width=292&amp;height=62&amp;colorscheme=light&amp;show_faces=false&amp;border_color&amp;stream=false&amp;header=true&amp;appId=113175385243" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
		</div>
	  </div>

	  <div id="options-div">
	  <div class="updated"><br />If you like <strong>Add to Footer</strong>, check out the more powerful plugin <a href="http://ajaydsouza.com/wordpress/plugins/add-to-all/">Add to All</a><br />&nbsp;</div>
	 <form method="post" id="addfoot_options" name="addfoot_options" style="border: #ccc 1px solid; padding: 10px" onsubmit="return checkForm()">
		<fieldset class="options">
		<table class="form-table">
			<tr style="vertical-align: top;"><th scope="row" style="background:#<?php if ($addfoot_settings[enable_plugin]) echo 'cfc'; else echo 'fcc'; ?>"><label for="enable_plugin"><?php _e('Enable the plugin: ',ADDFOOT_LOCAL_NAME); ?></label></th>
			<td style="background:#<?php if ($addfoot_settings[enable_plugin]) echo 'cfc'; else echo 'fcc'; ?>"><input type="checkbox" name="enable_plugin" id="enable_plugin" <?php if ($addfoot_settings[enable_plugin]) echo 'checked="checked"' ?> /></td>
			</tr>
		</table>
		<br />
		<table class="form-table">
			<tr style="vertical-align: top; background: #eee"><th scope="row" colspan="2"><?php _e('Statcounter Options:',ATA_LOCAL_NAME); ?></th>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="sc_project"><?php _e('StatCounter Project ID (Value of sc_project): ',ATA_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="sc_project" id="sc_project" value="<?php echo attribute_escape(stripslashes($addfoot_settings[sc_project])); ?>" style="width:250px" /></td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="sc_security"><?php _e('StatCounter Security ID (Value of sc_security): ',ATA_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="sc_security" id="sc_security" value="<?php echo attribute_escape(stripslashes($addfoot_settings[sc_security])); ?>" style="width:250px" /></td>
			</tr>
			<tr style="vertical-align: top; background: #eee"><th scope="row" colspan="2"><?php _e('Google Analytics Options: ',ATA_LOCAL_NAME); ?></th>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="ga_uacct"><?php _e('Tracking ID: ',ATA_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="ga_uacct" id="ga_uacct" value="<?php echo attribute_escape(stripslashes($addfoot_settings[ga_uacct])); ?>" style="width:250px" /></td>
			</tr>
			<tr style="vertical-align: top;"><th scope="row"><label for="ga_domain"><?php _e('Multiple sub-domain support (Value of _setDomainName): ',ATA_LOCAL_NAME); ?></label></th>
			<td><input type="textbox" name="ga_domain" id="ga_domain" value="<?php echo attribute_escape(stripslashes($addfoot_settings[ga_domain])); ?>" style="width:250px" /></td>
			</tr>
			<tr style="vertical-align: top; "><th scope="row" colspan="2"><?php _e('Any other HTML (no PHP) to add to <code>wp_footer</code>:',ATA_LOCAL_NAME); ?></th>
			</tr>
			<tr style="vertical-align: top; "><td scope="row" colspan="2"><textarea name="addfoot_other" id="addfoot_other" rows="15" cols="80"><?php echo stripslashes($addfoot_settings[addfoot_other]); ?></textarea></td>
			</tr>
		</table>
		</fieldset>
		<p>
		  <input type="submit" name="addfoot_save" id="addfoot_save" value="Save Options" style="border:#00CC00 1px solid" />
		  <input name="addfoot_default" type="submit" id="addfoot_default" value="Default Options" style="border:#FF0000 1px solid" onclick="if (!confirm('<?php _e('Do you want to set options to Default?',ADDFOOT_LOCAL_NAME); ?>')) return false;" />
		</p>
	  </form>
	</div>

	  </div>
	  <div style="clear: both;"></div>
	</div>
</div>
<?php

}

function addfoot_admin_notice() {
	$plugin_settings_page = '<a href="' . admin_url( 'options-general.php?page=addfoot_options' ) . '">' . __('plugin settings page', ADDFOOT_LOCAL_NAME ) . '</a>';

	$addfoot_settings = addfoot_read_options();
	if ($addfoot_settings[enable_plugin]) return;
	if ( !current_user_can( 'manage_options' ) ) return;

    echo '<div class="error">
       <p>'.__('Add to Footer plugin is disabled. Please visit the ', ADDFOOT_LOCAL_NAME ).$plugin_settings_page.__(' to enable.', ADDFOOT_LOCAL_NAME ).'</p>
    </div>';
}
add_action('admin_notices', 'addfoot_admin_notice');

function addfoot_adminmenu() {
	if (function_exists('current_user_can')) {
		// In WordPress 2.x
		if (current_user_can('manage_options')) {
			$addfoot_is_admin = true;
		}
	} else {
		// In WordPress 1.x
		global $user_ID;
		if (user_can_edit_user($user_ID, 0)) {
			$addfoot_is_admin = true;
		}
	}

	if ((function_exists('add_options_page'))&&($addfoot_is_admin)) {
		$plugin_page = add_options_page(__("Add to Footer", ADDFOOT_LOCAL_NAME), __("Add to Footer", ADDFOOT_LOCAL_NAME), 9, 'addfoot_options', 'addfoot_options');
		add_action( 'admin_head-'. $plugin_page, 'addfoot_adminhead' );
	}
}
add_action('admin_menu', 'addfoot_adminmenu');

function addfoot_adminhead() {
	global $addfoot_url;

?>
<link rel="stylesheet" type="text/css" href="<?php echo $addfoot_url ?>/admin-styles.css" />
<script type="text/javascript" language="JavaScript">
function checkForm() {
answer = true;
if (siw && siw.selectingSomething)
	answer = false;
return answer;
}//
</script>

<?php }

?>