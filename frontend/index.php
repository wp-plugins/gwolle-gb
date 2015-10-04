<?php
/*
 * Guestbook frontend
 * Called by using the shortcode [gwolle_gb] in a page or post.
 * $output will be used as replacement for that shortcode using the Shortcode API.
 */


/* Frontend Function
 * Use this to display the guestbook on a page without using a shortcode.
 *
 * For multiple guestbooks, use it like this:
 * show_gwolle_gb( array('book_id'=>2) );
 * which will show Book ID 2.
 */

function show_gwolle_gb( $atts ) {
	echo get_gwolle_gb( $atts );
}


/* Frontend Function
 * Used for the main shortcode.
 * shortcode_atts: book_id = 1
 */

function get_gwolle_gb( $atts ) {
	global $gwolle_gb_errors;

	$shortcode_atts = shortcode_atts( array(
		'book_id' => 1,
	), $atts );

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');
	wp_enqueue_script('gwolle_gb_frontend_js');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the form
	$output .= gwolle_gb_frontend_write( $shortcode_atts );

	/*
	 * Add CSS for showing the Form or the Button.
	 */

	if ( $gwolle_gb_errors ) {
		// Errors, show the Form again, not the Button
		$output .= '
			<style type="text/css" scoped>
				div#gwolle_gb_write_button { display:none; }
			</style>
		';
	} else {
		// No errors, just the Button, not the Form
		$output .= '
			<style type="text/css" scoped>
				form#gwolle_gb_new_entry { display:none; }
				div#gwolle_gb_new_entry { display:none; }
			</style>
		';
	}

	// Add the list of entries to show
	$output .= gwolle_gb_frontend_read( $shortcode_atts );

	$output .= '</div>';

	return $output;
}
add_shortcode( 'gwolle-gb', 'get_gwolle_gb' ); // deprecated, do not use dashes in Shortcode API
add_shortcode( 'gwolle_gb', 'get_gwolle_gb' );


/* Frontend function to show just the form */
function get_gwolle_gb_write( $atts ) {

	$shortcode_atts = shortcode_atts( array(
		'book_id' => 1,
	), $atts );

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');
	wp_enqueue_script('gwolle_gb_frontend_js');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the form
	$output .= gwolle_gb_frontend_write( $shortcode_atts );

	$output .= '</div>';

	$output .= '
		<style type="text/css" scoped>
			div#gwolle_gb_write_button { display:none; }
		</style>
	';

	return $output;
}
add_shortcode( 'gwolle_gb_write', 'get_gwolle_gb_write' );


/* Frontend function to show just the list of entries */
function get_gwolle_gb_read( $atts ) {

	$shortcode_atts = shortcode_atts( array(
		'book_id' => 1,
	), $atts );

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');
	wp_enqueue_script('gwolle_gb_frontend_js');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the list of entries to show
	$output .= gwolle_gb_frontend_read( $shortcode_atts );

	$output .= '</div>';

	return $output;
}
add_shortcode( 'gwolle_gb_read', 'get_gwolle_gb_read' );


