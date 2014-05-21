<?php
/**
 * rote-hose theme functions
 */

// include widgets
require_once( get_template_directory() . '/../rote-hose/widgets/RSS2.php' );

add_action( 'after_setup_theme', 'rote_hose_setup', 100 );

function rote_hose_setup() {

    register_widget( 'WP_Widget_RSS2' );
}
