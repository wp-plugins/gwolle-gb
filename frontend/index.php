<?php
/*
 * Guestbook frontend
 * Called by using the shortcode [gwolle_gb] in a page or post.
 * $output will be used as replacement for that shortcode using the Shortcode API.
 */


/* Frontend Function
 * Use this to display the guestbook on a page without using a shortcode
 */

function show_gwolle_gb() {
	echo get_gwolle_gb();
}


function get_gwolle_gb() {

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css', plugins_url('style.css', __FILE__), array(), GWOLLE_GB_VER,  'screen');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the form
	$output .= gwolle_gb_frontend_write();

	// Add the list of entries to show
	$output .= gwolle_gb_frontend_read();

	$output .= '</div>';

	return $output;
}

add_shortcode( 'gwolle-gb', 'get_gwolle_gb' ); // deprecated, do not use dashes in Shortcode API
add_shortcode( 'gwolle_gb', 'get_gwolle_gb' );

