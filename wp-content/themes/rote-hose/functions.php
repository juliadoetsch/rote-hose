<?php
/**
 * rote-hose theme functions
 */

// include widgets + helpers
require_once( get_template_directory() . '/../rote-hose/widgets/RSS2.php' );
require_once( get_template_directory() . '/../rote-hose/helpers/GAhelper.php' );

add_action( 'after_setup_theme', 'rote_hose_setup', 100 );

function rote_hose_setup() {

    register_widget( 'WP_Widget_RSS2' );

    // TODO: figure out why this doesn't remove the generator tag as it should,
    // remove additional identifiers for security
    remove_action('wp_head', 'wp_generator');

    add_action('wp_footer', 'add_ga_snippet');
}
