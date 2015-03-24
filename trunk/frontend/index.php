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
	global $gwolle_gb_errors;

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the form
	$output .= gwolle_gb_frontend_write();

	/*
	 * Add CSS for showing the Form or the Button.
	 */

	if ( $gwolle_gb_errors ) {
		// Errors, show the Form again, not the Button
		$output .= '
			<style>
				div#gwolle_gb_write_button { display:none; }
			</style>
		';
	} else {
		// No errors, just the Button, not the Form
		$output .= '
			<style>
				form#gwolle_gb_new_entry { display:none; }
			</style>
		';

		/* Add JavaScript to show or hide the Form or the Button. */
		$output .= '
			<script>
			jQuery(document).ready(function($) {
				jQuery( "#gwolle_gb_write_button" ).click(function() {
					document.getElementById("gwolle_gb_write_button").style.display = "none";
					jQuery("#gwolle_gb_new_entry").slideDown(1000);
					return false;
				});
			});
			</script>';
	}

	// Add the list of entries to show
	$output .= gwolle_gb_frontend_read();

	$output .= '</div>';

	return $output;
}
add_shortcode( 'gwolle-gb', 'get_gwolle_gb' ); // deprecated, do not use dashes in Shortcode API
add_shortcode( 'gwolle_gb', 'get_gwolle_gb' );


/* Frontend function to show just the form */
function get_gwolle_gb_write() {

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the form
	$output .= gwolle_gb_frontend_write();

	$output .= '</div>';

	$output .= '
		<style>
			div#gwolle_gb_write_button { display:none; }
		</style>
	';

	return $output;
}
add_shortcode( 'gwolle_gb_write', 'get_gwolle_gb_write' );


/* Frontend function to show just the list of entries */
function get_gwolle_gb_read() {

	// Load Frontend CSS in Footer, only when it's active
	wp_enqueue_style('gwolle_gb_frontend_css');
	//wp_enqueue_script('jquery');

	// Define $output
	$output = '<div id="gwolle_gb">';

	// Add the list of entries to show
	$output .= gwolle_gb_frontend_read();

	$output .= '</div>';

	return $output;
}
add_shortcode( 'gwolle_gb_read', 'get_gwolle_gb_read' );


