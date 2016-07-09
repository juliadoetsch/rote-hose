<?php


/* Quit */
defined('ABSPATH') OR exit;


/**
* Statify_Dashboard
*
* @since 1.1
*/

class Statify_Dashboard
{


	/**
	* Anzeige des Dashboard-Widgets
	*
	* @since   0.1.0
	* @change  1.2.3
	*/

	public static function init()
	{
		/* Filter */
		if ( ! current_user_can('edit_dashboard') ) {
			return;
		}

		/* Version definieren */
		self::_define_version();

		/* Widget */
		wp_add_dashboard_widget(
			'statify_dashboard',
			'Statify',
			array(
				__CLASS__,
				'print_frontview'
			),
			array(
				__CLASS__,
				'print_backview'
			)
		);

		/* CSS laden */
		add_action(
			'admin_print_styles',
			array(
				__CLASS__,
				'add_style'
			)
		);

		/* JS laden */
		add_action(
			'admin_print_scripts',
			array(
				__CLASS__,
				'add_js'
			)
		);
	}


	/**
	* Ausgabe der Stylesheets
	*
	* @since   0.1.0
	* @change  1.1.0
	*/

	public static function add_style()
	{
		/* CSS registrieren */
		wp_register_style(
			'statify',
			plugins_url('/css/dashboard.min.css', STATIFY_FILE),
			array(),
			STATIFY_VERSION
		);

		/* CSS ausgeben */
		wp_enqueue_style('statify');
	}


	/**
	* Ausgabe von JavaScript
	*
	* @since   0.1.0
	* @change  1.2.5
	*/

	public static function add_js() {
		/* Keine Statistiken? */
		if ( ! self::get_stats() ) {
			return;
		}

		/* Edit modus? */
		if ( isset($_GET['edit']) && $_GET['edit'] === 'statify_dashboard' ) {
			return;
		}

		/* Register scripts */
		wp_register_script(
			'sm_raphael_js',
			plugins_url('js/raphael.min.js', STATIFY_FILE),
			array(),
			STATIFY_VERSION,
			true
		);
		wp_register_script(
			'sm_raphael_helper',
			plugins_url('js/raphael.helper.min.js', STATIFY_FILE),
			array(),
			STATIFY_VERSION,
			true
		);
		wp_register_script(
			'statify_chart_js',
			plugins_url('js/dashboard.min.js', STATIFY_FILE),
			array('jquery'),
			STATIFY_VERSION,
			true
		);

		/* Embed scripts */
		wp_enqueue_script('sm_raphael_js');
		wp_enqueue_script('sm_raphael_helper');
		wp_enqueue_script('statify_chart_js');
	}


	/**
	* Ausgabe der Frontseite
	*
	* @since   0.1.0
	* @change  1.3.0
	*/

	public static function print_frontview()
	{
		/* Get stats */
		$stats = self::get_stats();

		/* No results? */
		if ( ! $stats ) {
			echo sprintf(
				'<div id="statify_chart"><p>%s</p></div>',
				'Keine Daten verfügbar.'
			);

			return;
		}

		/* Get visits */
		$visits = array_reverse($stats['visits']);

		/* HTML start */
		$html = "<table id=statify_chart_data>\n";


		/* Timestamp table */
		$html .= "<tfoot><tr>\n";
		foreach ($visits as $item) {
			$html .= "<th>" .esc_html($item['date']). "</th>\n";
		}
		$html .= "</tr></tfoot>\n";

		/* Counter table */
		$html .= "<tbody><tr>\n";
		foreach($visits as $item) {
			$html .= "<td>" .intval($item['count']). "</td>\n";
		}
		$html .= "</tr></tbody>\n";


		/* HTML end */
		$html .= "</table>\n";

		/* Print html */
		echo '<div id="statify_chart">' .$html. '</div>'; ?>

		<?php if ( $stats['target'] ) { ?>
			<div class="table target">
				<p class="sub">Top Inhalte</p>

				<div>
					<table>
						<?php foreach ($stats['target'] as $target) { ?>
							<tr>
								<td class="b">
									<?php echo intval($target['count']) ?>
								</td>
								<td class="t">
									<a href="<?php echo esc_url($target['url']) ?>" target="_blank"><?php echo esc_url($target['url']) ?></a>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		<?php } ?>

		<?php if ( $stats['referrer'] ) { ?>
			<div class="table referrer">
				<p class="sub">Top Referrer</p>

				<div>
					<table>
						<?php foreach ($stats['referrer'] as $referrer) { ?>
							<tr>
								<td class="b">
									<?php echo intval($referrer['count']) ?>
								</td>
								<td class="t">
									<a href="<?php echo esc_url($referrer['url']) ?>" target="_blank"><?php echo esc_url($referrer['host']) ?></a>
								</td>
							</tr>
						<?php } ?>
					</table>
				</div>
			</div>
		<?php } ?>
	<?php }


	/**
	* Ausgabe der Backseite
	*
	* @since   0.4.0
	* @change  1.2.3
	*/

	public static function print_backview()
	{
		/* Rechte */
		if ( ! current_user_can('edit_dashboard') ) {
			return;
		}

		/* Speichern */
		if ( ! empty($_POST['statify']) ) {
			/* Formular-Referer */
			check_admin_referer('_statify');

			/* Optionen speichern */
			Statify::set_options(
				array(
					'days'	  => (int)@$_POST['statify']['days'],
					'limit'	  => (int)@$_POST['statify']['limit'],
					'today'	  => (int)@$_POST['statify']['today'],
					'snippet' => (int)@$_POST['statify']['snippet']
				)
			);

			/* Internen Cache Leeren */
			delete_transient('statify_chart');

			/* Cachify Cache leeren */
			if ( has_action('cachify_flush_cache') ) {
				do_action('cachify_flush_cache');
			}
		}

		/* Zeiträume */
		$dmatrix = array(
			7 => '1 Woche',
			14 => '2 Wochen',
			21 => '3 Wochen',
			30 => '1 Monat',
			90 => '3 Monate',
			180 => '6 Monate',
			365 => '1 Jahr'
		);

		/* Optionen */
		$options = Statify::get_options();

		/* Security */
		wp_nonce_field('_statify'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					Statistik
				</th>
				<td>
					<fieldset>
						<label for="statify_days">
							<select name="statify[days]" id="statify_days">
								<?php foreach( $dmatrix as $days => $string ) { ?>
									<option value="<?php echo $days ?>" <?php selected($options['days'], $days); ?>>
										<?php echo $string ?>
									</option>
								<?php } ?>
							</select>
							Zeitraum der Aufbewahrung
						</label>

						<br />

						<label for="statify_limit">
							<select name="statify[limit]" id="statify_limit">
								<?php foreach( range(0, 12) as $num ) { ?>
									<option <?php selected($options['limit'], $num) ?>>
										<?php echo $num ?>
									</option>
								<?php } ?>
							</select>
							Anzahl der Einträge in Listen
						</label>

						<br />

						<label for="statify_today">
							<input type="checkbox" name="statify[today]" id="statify_today" value="1" <?php checked($options['today'], 1) ?> />
							Einträge in Listen nur von heute
						</label>
					</fieldset>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					Tracking
				</th>
				<td>
					<fieldset>
						<label for="statify_snippet">
							<input type="checkbox" name="statify[snippet]" id="statify_snippet" value="1" <?php checked($options['snippet'], 1) ?> />
							Seitenzählung via JavaScript-Snippet
						</label>
					</fieldset>
				</td>
			</tr>
		</table>

		<p class="meta-links">
			<a href="http://playground.ebiene.de/statify-wordpress-statistik/" target="_blank">Handbuch</a> &bull; <a href="https://flattr.com/t/1733733" target="_blank">Flattr</a> &bull; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=5RDDW9FEHGLG6" target="_blank">PayPal</a>
		</p>
	<?php }


	/**
	* Rückgabe der Statistiken
	*
	* @since   0.1.0
	* @change  1.3.0
	*
	* @return  array  $data  Array mit Statistiken
	*/

	public static function get_stats()
	{
		/* Auf Cache zugreifen */
		if ( $data = get_transient('statify_chart') ) {
			return $data;
		}

		/* DB reinigen */
		self::_clean_data();

		/* Stats abrufen */
		$data = self::_select_data();

		/* Empty stats */
		if ( empty($data['visits']) ) {
			$data = NULL;
		}

		/* Merken */
		set_transient(
		   'statify_chart',
		   $data,
		   MINUTE_IN_SECONDS * 4
		);

		return $data;
	}


	/**
	* Statistiken aus der DB
	*
	* @since   0.1.0
	* @change  1.2.5
	*
	* @return  array  Array mit ausgelesenen Daten
	*/

	private static function _select_data()
	{
		/* GLobal */
		global $wpdb;

		/* Optionen */
		$options = Statify::get_options();

		return array(
			'visits' => $wpdb->get_results(
				$wpdb->prepare(
					"SELECT DATE_FORMAT(`created`, '%%d.%%m.%%Y') as `date`, COUNT(`created`) as `count` FROM `$wpdb->statify` GROUP BY `created` ORDER BY `created` DESC LIMIT %d",
					(int)$options['days']
				),
				ARRAY_A
			),
			'target' => $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COUNT(`target`) as `count`, `target` as `url` FROM `$wpdb->statify` " .( $options['today'] ? 'WHERE created = DATE(NOW())' : '' ). " GROUP BY `target` ORDER BY `count` DESC LIMIT %d",
					(int)$options['limit']
				),
				ARRAY_A
			),
			'referrer' => $wpdb->get_results(
				$wpdb->prepare(
					"SELECT COUNT(`referrer`) as `count`, `referrer` as `url`, SUBSTRING_INDEX(SUBSTRING_INDEX(TRIM(LEADING 'www.' FROM(TRIM(LEADING 'https://' FROM TRIM(LEADING 'http://' FROM TRIM(`referrer`))))), '/', 1), ':', 1) as `host` FROM `$wpdb->statify` WHERE `referrer` != '' " .( $options['today'] ? 'AND created = DATE(NOW())' : '' ). " GROUP BY `host` ORDER BY `count` DESC LIMIT %d",
					(int)$options['limit']
				),
				ARRAY_A
			)
		);
	}


	/**
	* Bereinigung der veralteten Werte in der DB
	*
	* @since   0.3.0
	* @change  1.3.0
	*/

	private static function _clean_data()
	{
		/* Überspringen? */
		if ( get_transient('statify_cron') ) {
			return;
		}

		/* Global */
		global $wpdb;

		/* Optionen */
		$options = Statify::get_options();

		/* Löschen */
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM `$wpdb->statify` WHERE created <= SUBDATE(CURDATE(), %d)",
				(int)$options['days']
			)
		);

		/* DB optimieren */
		$wpdb->query(
			"OPTIMIZE TABLE `$wpdb->statify`"
		);

		/* Merken */
		set_transient(
			'statify_cron',
			'ilovesweta',
			HOUR_IN_SECONDS * 12
		);
	}


	/**
	* Plugin-Version als Konstante
	*
	* @since   1.1.0
	* @change  1.1.0
	*/

	private static function _define_version()
	{
		/* Auslesen */
		$meta = get_plugin_data(STATIFY_FILE);

		/* Zuweisen */
		define('STATIFY_VERSION', $meta['Version']);
	}
}