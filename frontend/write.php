<?php

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }


/*
 * Save new entries to the database, when valid.
 * Obligatory fields:
 * - author_name
 * - author_email
 * - content
 * and a negative Akismet result (= no spam) and a correct captcha; both only when turned on in the settings panel.
 *
 * Build up a form for the user, including possible error_fields
 */

function gwolle_gb_frontend_write() {
	global $wpdb, $gwolle_gb_errors, $gwolle_gb_error_fields, $gwolle_gb_messages, $gwolle_gb_data;

	$output = '';

	// Set data up for refilling an already submitted form that had errors
	$name = '';
	$origin = '';
	$email = '';
	$website = '';
	$content = '';

	// Only show old data when there are errors
	if ( $gwolle_gb_errors ) {
		if ( is_array($gwolle_gb_data) && count($gwolle_gb_data) > 0 ) {
			if (isset($gwolle_gb_data['author_name'])) {
				$name = $gwolle_gb_data['author_name'];
			}
			if (isset($gwolle_gb_data['author_origin'])) {
				$origin = $gwolle_gb_data['author_origin'];
			}
			if (isset($gwolle_gb_data['author_email'])) {
				$email = $gwolle_gb_data['author_email'];
			}
			if (isset($gwolle_gb_data['author_website'])) {
				$website = $gwolle_gb_data['author_website'];
			}
			if (isset($gwolle_gb_data['content'])) {
				$content = $gwolle_gb_data['content'];
			}
		}
	}

	// FIXME: If user is logged in, auto-fill the form if there's no data yet

	// Initialize errors, if not set
	if ( empty( $gwolle_gb_error_fields ) ) {
		$gwolle_gb_error_fields = array();
	}


	/*
	 * Handle Messaging to the user
	 */

	$class="";
	if ( $gwolle_gb_errors ) {
		$class="error";
	}

	if ( isset($gwolle_gb_messages) && $gwolle_gb_messages != '') {
		$output .= "<div id='gwolle_gb_messages' class='$class'>";
		$output .= $gwolle_gb_messages;
		$output .= "</div>";
	}


	/*
	 * Link 'write a new entry.'
	 */

	$output .= '
		<div id="gwolle_gb_write_button">
			<a target="_self" href="#">&raquo; ' . __('Write a new entry.', GWOLLE_GB_TEXTDOMAIN) . '</a>
		</div>';


	/*
	 * Build up Form including possible error_fields
	 */

	// Form for submitting new entries
	$output .= '
		<form id="gwolle_gb_new_entry" action="" method="POST">
			<h3>' . __('Write a new entry for the Guestbook', GWOLLE_GB_TEXTDOMAIN) . '</h3>
			<input type="hidden" name="gwolle_gb_function" value="add_entry" />
			<div class="label">' . __('Name', GWOLLE_GB_TEXTDOMAIN) . ': *</div>
			<div class="input"><input class="';
	if (in_array('name', $gwolle_gb_error_fields)) {
		$output .= ' error';
	}
	$output .= '" value="' . $name . '" type="text" name="gwolle_gb_author_name" placeholder="' . __('Name', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
		<div class="clearBoth">&nbsp;</div>

		<div class="label">' . __('City', GWOLLE_GB_TEXTDOMAIN) . ':</div>
		<div class="input"><input value="' . $origin . '" type="text" name="gwolle_gb_author_origin" placeholder="' . __('City', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
		<div class="clearBoth">&nbsp;</div>

		<div class="label">' . __('Email', GWOLLE_GB_TEXTDOMAIN) . ': *</div>
		<div class="input"><input class="';
	if (in_array('author_email', $gwolle_gb_error_fields)) {
		$output .= ' error';
	}
	$output .= '" value="' . $email . '" type="text" name="gwolle_gb_author_email" placeholder="' . __('Email', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
		<div class="clearBoth">&nbsp;</div>

		<div class="label">' . __('Homepage', GWOLLE_GB_TEXTDOMAIN) . ':</div>
		<div class="input"><input value="' . $website . '" type="text" name="gwolle_gb_author_website" placeholder="' . __('Homepage', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
		<div class="clearBoth">&nbsp;</div>

		<div class="label">' . __('Guestbook entry', GWOLLE_GB_TEXTDOMAIN) . ': *</div>
		<div class="input"><textarea name="gwolle_gb_content" class="';
	if (in_array('content', $gwolle_gb_error_fields)) {
		$output .= ' error';
	}
	$output .= '">' . $content . '</textarea></div>
			<div class="clearBoth">&nbsp;</div>';

	/* FIXME: commented out for now.
	if (get_option('gwolle_gb-recaptcha-active') === TRUE) {
		$output .= '
			<div class="label">&nbsp;</div>
			<div class="input">';

		if (!function_exists('recaptcha_get_html')) {
			/*
			 * If function recaptcha_get_html already exists
			 * the reCAPTCHA library has been included by another
			 * plugin. Don't load it now since it would result in an error.
			 */
			/* require_once('recaptcha/recaptchalib.php');
		}
		$publickey = get_option('recaptcha-public-key');
		$output .=
			recaptcha_get_html($publickey).'
			</div>
			<div class="clearBoth">&nbsp;</div>';
	} */

	$output .= '
			<div class="label">&nbsp;</div>
			<div class="input"><input type="submit" name="gwolle_gb_submit" value="' . __('Submit', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
			<div class="clearBoth">&nbsp;</div>

			<div class="notice">
			' . __('Fields marked with * are obligatory.', GWOLLE_GB_TEXTDOMAIN) . '
			<br />
			' . str_replace('%1', $_SERVER['REMOTE_ADDR'], __('For security reasons we save the ip address <span id="ip">%1</span>.', GWOLLE_GB_TEXTDOMAIN)) . '
			<br />';

	if (get_option('gwolle_gb-moderate-entries', 'true') === 'true') {
		$output .= __('Your entry will be visible in the guestbook after we reviewed it.', GWOLLE_GB_TEXTDOMAIN) . '&nbsp;';
	}
	$output .= __('We reserve our right to edit, delete, or not publish entries.', GWOLLE_GB_TEXTDOMAIN) . '
				</div>
			</form>';


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
	}


	/*
	 * Add JavaScript to show or hide the Form or the Button.
	 */

	$output .= '
		<script>
		jQuery( "#gwolle_gb_write_button" ).click(function() {
			document.getElementById("gwolle_gb_write_button").style.display = "none";
			document.getElementById("gwolle_gb_new_entry").style.display = "block";
			return false;
		});
		</script>';


	return $output;
}

