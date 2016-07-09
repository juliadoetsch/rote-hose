<?php
/**
 * Theme Installer List Table class.
 *
 * @package WordPress
 * @subpackage List_Table
 * @since 3.1.0
 * @access private
 */
class WP_Theme_Install_List_Table extends WP_Themes_List_Table {

	var $features = array();

	function ajax_user_can() {
		return current_user_can( 'install_themes' );
	}

	function prepare_items() {
		include( ABSPATH . 'wp-admin/includes/theme-install.php' );

		global $tabs, $tab, $paged, $type, $theme_field_defaults;
		wp_reset_vars( array( 'tab' ) );

		$search_terms = array();
		$search_string = '';
		if ( ! empty( $_REQUEST['s'] ) ){
			$search_string = strtolower( wp_unslash( $_REQUEST['s'] ) );
			$search_terms = array_unique( array_filter( array_map( 'trim', explode( ',', $search_string ) ) ) );
		}

		if ( ! empty( $_REQUEST['features'] ) )
			$this->features = $_REQUEST['features'];

		$paged = $this->get_pagenum();

		$per_page = 36;

		// These are the tabs which are shown on the page,
		$tabs = array();
		$tabs['dashboard'] = __( 'Search' );
		if ( 'search' == $tab )
			$tabs['search']	= __( 'Search Results' );
		$tabs['upload'] = __( 'Upload' );
		$tabs['featured'] = _x( 'Featured','Theme Installer' );
		//$tabs['popular']  = _x( 'Popular','Theme Installer' );
		$tabs['new']      = _x( 'Newest','Theme Installer' );
		$tabs['updated']  = _x( 'Recently Updated','Theme Installer' );

		$nonmenu_tabs = array( 'theme-information' ); // Valid actions to perform which do not have a Menu item.

		/**
		 * Filter the tabs shown on the Install Themes screen.
		 *
		 * @since 2.8.0
		 *
		 * @param array $tabs The tabs shown on the Install Themes screen. Defaults are
		 *                    'dashboard', 'search', 'upload', 'featured', 'new', and 'updated'.
		 */
		$tabs = apply_filters( 'install_themes_tabs', $tabs );

		/**
		 * Filter tabs not associated with a menu item on the Install Themes screen.
		 *
		 * @since 2.8.0
		 *
		 * @param array $nonmenu_tabs The tabs that don't have a menu item on
		 *                            the Install Themes screen.
		 */
		$nonmenu_tabs = apply_filters( 'install_themes_nonmenu_tabs', $nonmenu_tabs );

		// If a non-valid menu tab has been selected, And it's not a non-menu action.
		if ( empty( $tab ) || ( ! isset( $tabs[ $tab ] ) && ! in_array( $tab, (array) $nonmenu_tabs ) ) )
			$tab = key( $tabs );

		$args = array( 'page' => $paged, 'per_page' => $per_page, 'fields' => $theme_field_defaults );

		switch ( $tab ) {
			case 'search':
				$type = isset( $_REQUEST['type'] ) ? wp_unslash( $_REQUEST['type'] ) : 'term';
				switch ( $type ) {
					case 'tag':
						$args['tag'] = array_map( 'sanitize_key', $search_terms );
						break;
					case 'term':
						$args['search'] = $search_string;
						break;
					case 'author':
						$args['author'] = $search_string;
						break;
				}

				if ( ! empty( $this->features ) ) {
					$args['tag'] = $this->features;
					$_REQUEST['s'] = implode( ',', $this->features );
					$_REQUEST['type'] = 'tag';
				}

				add_action( 'install_themes_table_header', 'install_theme_search_form', 10, 0 );
				break;

			case 'featured':
			//case 'popular':
			case 'new':
			case 'updated':
				$args['browse'] = $tab;
				break;

			default:
				$args = false;
				break;
		}

		/**
		 * Filter API request arguments for each Install Themes screen tab.
		 *
		 * The dynamic portion of the hook name, $tab, refers to the theme install
		 * tabs. Default tabs are 'dashboard', 'search', 'upload', 'featured',
		 * 'new', and 'updated'.
		 *
		 * @since 3.7.0
		 *
		 * @param array $args An array of themes API arguments.
		 */
		$args = apply_filters( 'install_themes_table_api_args_' . $tab, $args );

		if ( ! $args )
			return;

		$api = themes_api( 'query_themes', $args );

		if ( is_wp_error( $api ) )
			wp_die( $api->get_error_message() . '</p> <p><a href="#" onclick="document.location.reload(); return false;">' . __( 'Try again' ) . '</a>' );

		$this->items = $api->themes;

		$this->set_pagination_args( array(
			'total_items' => $api->info['results'],
			'per_page' => $args['per_page'],
			'infinite_scroll' => true,
		) );
	}

	function no_items() {
		_e( 'No themes match your request.' );
	}

	function get_views() {
		global $tabs, $tab;

		$display_tabs = array();
		foreach ( (array) $tabs as $action => $text ) {
			$class = ( $action == $tab ) ? ' class="current"' : '';
			$href = self_admin_url('theme-install.php?tab=' . $action);
			$display_tabs['theme-install-'.$action] = "<a href='$href'$class>$text</a>";
		}

		return $display_tabs;
	}

	function display() {
		wp_nonce_field( "fetch-list-" . get_class( $this ), '_ajax_fetch_list_nonce' );
?>
		<div class="tablenav top themes">
			<div class="alignleft actions">
				<?php
				/**
				 * Fires in the Install Themes list table header.
				 *
				 * @since 2.8.0
				 */
				do_action( 'install_themes_table_header' );
				?>
			</div>
			<?php $this->pagination( 'top' ); ?>
			<br class="clear" />
		</div>

		<div id="availablethemes">
			<?php $this->display_rows_or_placeholder(); ?>
		</div>

		<?php
		parent::tablenav( 'bottom' );
	}

	function display_rows() {
		$themes = $this->items;
		foreach ( $themes as $theme ) {
				?>
				<div class="available-theme installable-theme"><?php
					$this->single_row( $theme );
				?></div>
		<?php } // end foreach $theme_names

		$this->theme_installer();
	}

	/**
	 * Prints a theme from the WordPress.org API.
	 *
	 * @param object $theme An object that contains theme data returned by the WordPress.org API.
	 *
	 * Example theme data:
	 *   object(stdClass)[59]
	 *     public 'name' => string 'Magazine Basic'
	 *     public 'slug' => string 'magazine-basic'
	 *     public 'version' => string '1.1'
	 *     public 'author' => string 'tinkerpriest'
	 *     public 'preview_url' => string 'http://wp-themes.com/?magazine-basic'
	 *     public 'screenshot_url' => string 'http://wp-themes.com/wp-content/themes/magazine-basic/screenshot.png'
	 *     public 'rating' => float 80
	 *     public 'num_ratings' => int 1
	 *     public 'homepage' => string 'http://wordpress.org/themes/magazine-basic'
	 *     public 'description' => string 'A basic magazine style layout with a fully customizable layout through a backend interface. Designed by <a href="http://bavotasan.com">c.bavota</a> of <a href="http://tinkerpriestmedia.com">Tinker Priest Media</a>.'
	 *     public 'download_link' => string 'http://wordpress.org/themes/download/magazine-basic.1.1.zip'
	 */
	function single_row( $theme ) {
		global $themes_allowedtags;

		if ( empty( $theme ) )
			return;

		$name   = wp_kses( $theme->name,   $themes_allowedtags );
		$author = wp_kses( $theme->author, $themes_allowedtags );

		$preview_title = sprintf( __('Preview &#8220;%s&#8221;'), $name );
		$preview_url   = add_query_arg( array(
			'tab'   => 'theme-information',
			'theme' => $theme->slug,
		), self_admin_url( 'theme-install.php' ) );

		$actions = array();

		$install_url = add_query_arg( array(
			'action' => 'install-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$update_url = add_query_arg( array(
			'action' => 'upgrade-theme',
			'theme'  => $theme->slug,
		), self_admin_url( 'update.php' ) );

		$status = $this->_get_theme_status( $theme );

		switch ( $status ) {
			default:
			case 'install':
				$actions[] = '<a class="install-now" href="' . esc_url( wp_nonce_url( $install_url, 'install-theme_' . $theme->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Install %s' ), $name ) ) . '">' . __( 'Install Now' ) . '</a>';
				break;
			case 'update_available':
				$actions[] = '<a class="install-now" href="' . esc_url( wp_nonce_url( $update_url, 'upgrade-theme_' . $theme->slug ) ) . '" title="' . esc_attr( sprintf( __( 'Update to version %s' ), $theme->version ) ) . '">' . __( 'Update' ) . '</a>';
				break;
			case 'newer_installed':
			case 'latest_installed':
				$actions[] = '<span class="install-now" title="' . esc_attr__( 'This theme is already installed and is up to date' ) . '">' . _x( 'Installed', 'theme' ) . '</span>';
				break;
		}

		$actions[] = '<a class="install-theme-preview" href="' . esc_url( $preview_url ) . '" title="' . esc_attr( sprintf