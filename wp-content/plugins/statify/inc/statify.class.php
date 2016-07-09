<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Statify
*
* @since 0.1.0
*/

class Statify
{


	/* Default */
	private static $_days = 14;
	private static $_limit = 3;
	private static $_today = 0;
	private static $_snippet = 0;


	/**
	* Pseudo-Konstruktor der Klasse
	*
	* @since   0.1.0
	* @change  0.1.0
	*/

	public static function instance()
	{
		new self();
	}


	/**
	* Konstruktor der Klasse
	*
	* @since   0.1.0
	* @change  1.1.0
	*/

	public function __construct()
	{
		/* Filter */
		if ( (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) or (defined('DOING_CRON') && DOING_CRON) or (defined('DOING_AJAX') && DOING_AJAX) ) {
			return;
		}

		/* Tabelle Init */
		Statify_Table::init();

		/* XMLRPC */
		if ( defined('XMLRPC_REQUEST') && XMLRPC_REQUEST ) {
			add_filter(
				'xmlrpc_methods',
				array(
					'Statify_XMLRPC',
					'xmlrpc_methods'
				)
			);

		/* BE */
		} else if ( is_admin() ) {
			add_action(
				'wpmu_new_blog',
				array(
					'Statify_Install',
					'init'
				)
			);
			add_action(
				'delete_blog',
				array(
					'Statify_Uninstall',
					'init'
				)
			);
			add_action(
				'wp_dashboard_setup',
				array(
					'Statify_Dashboard',
					'init'
				)
			);
			add_filter(
				'plugin_row_meta',
				array(
					__CLASS__,
					'add_meta_link'
				),
				10,
				2
			);
			add_filter(
				'plugin_action_links_' .STATIFY_BASE,
				array(
					__CLASS__,
					'add_action_link'
				)
			);

		/* FE */
		} else {
			add_action(
				'template_redirect',
				array(
					__CLASS__,
					'track_visit'
				)
			);
			add_filter(
				'query_vars',
				array(
					__CLASS__,
					'query_vars'
				)
			);
			add_action(
				'wp_footer',
				array(
					__CLASS__,
					'wp_footer'
				)
			);
		}
	}


	/**
	* Hinzufügen der Meta-Links
	*
	* @since   0.1.0
	* @change  1.1.0
	*
	* @param   array   $input  Array mit Links
	* @param   string  $file   Name des Plugins
	* @return  array           Array mit erweitertem Link
	*/

	public static function add_meta_link($input, $file)
	{
		/* Restliche Plugins? */
		if ( $file !== STATIFY_BASE ) {
			return $input;
		}

		return array_merge(
			$input,
			array(
				'<a href="https://flattr.com/t/1733733" target="_blank">Flattr</a>',
				'<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=5RDDW9FEHGLG6" target="_blank">PayPal</a>'
			)
		);
	}


	/**
	* Hinzufügen des Action-Links
	*
	* @since   0.1.0
	* @change  1.1.0
	*/

	public static function add_action_link($input)
	{
		/* Rechte? */
		if ( ! current_user_can('manage_options') ) {
			return $input;
		}

		/* Zusammenführen */
		return array_merge(
			$input,
			array(
				sprintf(
					'<a href="%s">%s</a>',
					add_query_arg(
						array(
							'edit' => 'statify_dashboard#statify_dashboard'
						),
						admin_url('/')
					),
					__('Settings')
				)
			)
		);
	}


	/**
	* Speicherung der Plugin-Optionen
	*
	* @since   1.1.0
	* @change  1.1.0
	*
	* @param   array  $options  Array mit Optionen
	*/

	public static function set_options($options)
	{
		/* In die DB */
		update_option(
			'statify',
			$options
		);

		/* Ins Cache */
		wp_cache_set(
			'statify',
			$options
		);
	}


	/**
	* Rückgabe der Plugin-Optionen
	*
	* @since   1.1.0
	* @change  1.1.0
	*
	* @return  array  $options  Array mit Optionen
	*/

	public static function get_options()
	{
		/* Im Cache */
		if ( $options = wp_cache_get('statify') ) {
			return $options;
		}

		/* Zusammenführen */
		$options = wp_parse_args(
			get_option('statify'),
			array(
				'days'	  => self::$_days,
				'limit'	  => self::$_limit,
				'today'   => self::$_today,
				'snippet' => self::$_snippet
			)
		);

		/* Ins Cache */
		wp_cache_set(
			'statify',
			$options
		);

		return $options;
	}


	/**
	* Rückgabe einer bestimmten Plugin-Option
	*
	* @since   1.1.0
	* @change  1.1.0
	*
	* @param   string  $key  Array-Key für Optionen
	* @return  mixed         Wert der angeforderten Option
	*/

	public static function get_option($key)
	{
		/* Optionen */
		$options = self::get_options();

		return ( empty($options[$key]) ? '' : $options[$key] );
	}


	/**
	* Speicherung des Aufrufes in der DB
	*
	* @since   0.1.0
	* @change  1.3.0
	*/

	public static function track_visit()
	{
		/* JS-Snippet? */
		$use_snippet = self::get_option('snippet');
		$is_snippet = $use_snippet && get_query_var('statify_target');

		/* Snippet? */
		if ( $is_snippet ) {
			$target = urldecode( get_query_var('statify_target') );
			$referrer = urldecode( get_query_var('statify_referrer') );
		} else if ( ! $use_snippet) {
			$target = ( empty($_SERVER['REQUEST_URI']) ? '/' : $_SERVER['REQUEST_URI'] );
			$referrer = ( empty($_SERVER['HTTP_REFERER']) ? '' : $_SERVER['HTTP_REFERER'] );
		} else {
			return;
		}

		/* Kein Ziel? */
		if ( empty($target) ) {
			return self::_jump_out($is_snippet);
		}

		/* Bot? */
		if ( empty($_SERVER['HTTP_USER_AGENT']) OR ! preg_match('/(?:Windows|Macintosh|Linux|iPhone|iPad)/', $_SERVER['HTTP_USER_AGENT']) ) {
			return self::_jump_out($is_snippet);
		}

		/* Filter */
		if ( self::_skip_tracking() ) {
			return self::_jump_out($is_snippet);
		}

		/* Global */
		global $wpdb, $wp_rewrite;

		/* Init */
		$data = array(
			'created'  => '',
			'referrer' => '',
			'target'   => ''
		);

		/* Timestamp */
		$data['created'] = strftime(
			'%Y-%m-%d',
			current_time('timestamp')
		);

		/* Referrer */
		if ( ! empty($referrer) && strpos( $referrer, home_url() ) === false ) {
			$data['referrer'] = esc_url_raw($referrer);
		}

		/* Ziel */
		$data['target'] = home_url($target, 'relative');

		/* Parameter entfernen */
		if ( $wp_rewrite->permalink_structure && ! is_search() ) {
			$data['target'] = parse_url($data['target'], PHP_URL_PATH);
		}

		/* Absichern */
		$data['target'] = esc_url_raw($data['target']);

		/* Insert */
		$wpdb->insert(
			$wpdb->statify,
			$data
		);

		/* Beenden */
		return self::_jump_out($is_snippet);
	}


	/**
	* Steuerung des Tracking-Mechanismus
	*
	* @since   1.2.6
	* @change  1.2.6
	*
	* @hook    boolean  statify_skip_tracking
	*
	* @return  boolean  $skip_hook  TRUE, wenn KEIN Tracking des Seitenaufrufes erfolgen soll
	*/

	private static function _skip_tracking() {
		if ( ( $skip_hook = apply_filters('statify_skip_tracking', NULL) ) !== NULL ) {
			return $skip_hook;
		}

		return ( is_feed() OR is_trackback() OR is_robots() OR is_preview() OR is_user_logged_in() OR is_404() );
	}


	/**
	* JavaScript-Header oder return
	*
	* @since   1.1.0
	* @change  1.3.0
	*
	* @param   boolean  $is_snippet  JavaScript-Snippte als Aufruf?
	* @return  mixed                 Exit oder return je nach Snippet
	*/

	private static function _jump_out($is_snippet) {
		if ( $is_snippet ) {
			nocache_headers();
			header('Content-type: text/javascript', true, 204);
			exit;
		}

		return false;
	}


	/**
	* Deklariert GET-Variablen für die Weiternutzung
	*
	* @since   1.1.0
	* @change  1.1.0
	*
	* @param   array  $vars  Array mit existierenden Variablen
	* @return  array  $vars  Array mit Plugin-Variablen
	*/

	public static function query_vars($vars) {
		$vars[] = 'statify_referrer';
		$vars[] = 'statify_target';

		return $vars;
	}


	/**
	* Ausgabe des JS-Snippets
	*
	* @since   1.1.0
	* @change  1.2.8
	*/

	public static function wp_footer()
	{
		if ( self::get_option('snippet') ) { ?>

			<!-- Tracking von http://statify.de -->
			<script type="text/javascript">
				(function() {
					var e = document.createElement('script'),
						s = document.getElementsByTagName('script')[0],
						r = encodeURIComponent(document.referrer),
						t = encodeURIComponent(location.pathname),
						p = '?statify_referrer=' + r + '&statify_target=' + t;

					e.async = true;
					e.type = 'text/javascript';
					e.src = "<?php echo home_url('/', 'relative') ?>" + p;

					s.parentNode.insertBefore(e, s);
				})();
			</script>

		<?php }
	}
}