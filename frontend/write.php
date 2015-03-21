<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Build up a form for the user, including possible error_fields
 */

function gwolle_gb_frontend_write() {
	global $gwolle_gb_errors, $gwolle_gb_error_fields, $gwolle_gb_messages, $gwolle_gb_data;

	$output = '';


	// Set data up for refilling an already submitted form that had errors
	$name = '';
	$origin = '';
	$email = '';
	$website = '';
	$antispam = '';
	$content = '';

	// Auto-fill the form if the user is already logged in
	$user_id = get_current_user_id(); // returns 0 if no current user
	if ( $user_id > 0 ) {
		$userdata = get_userdata( $user_id );
		if (is_object($userdata)) {
			if ( isset( $userdata->display_name ) ) {
				$name = $userdata->display_name;
			} else {
				$name = $userdata->user_login;
			}
			$email = $userdata->user_email;
			$website = $userdata->user_url;
		}
	}

	// Only show old data when there are errors
	if ( $gwolle_gb_errors ) {
		if ( is_array($gwolle_gb_data) && !empty($gwolle_gb_data) ) {
			if (isset($gwolle_gb_data['author_name'])) {
				$name = stripslashes($gwolle_gb_data['author_name']);
			}
			if (isset($gwolle_gb_data['author_origin'])) {
				$origin = stripslashes($gwolle_gb_data['author_origin']);
			}
			if (isset($gwolle_gb_data['author_email'])) {
				$email = stripslashes($gwolle_gb_data['author_email']);
			}
			if (isset($gwolle_gb_data['author_website'])) {
				$website = stripslashes($gwolle_gb_data['author_website']);
			}
			if (isset($gwolle_gb_data['antispam'])) {
				$antispam = stripslashes($gwolle_gb_data['antispam']);
			}
			if (isset($gwolle_gb_data['content'])) {
				$content = stripslashes($gwolle_gb_data['content']);
			}
		}
	}

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


	// Option to allow only logged-in users to post. Don't show the form if not logged-in. We still see the messages above.
	if ( get_option('gwolle_gb-require_login', 'false') == 'true' ) {
		return $output;
	}


	/*
	 * Button 'write a new entry.'
	 */

	$output .= '
		<div id="gwolle_gb_write_button">
			<input type="button" value="&raquo; ' . __('Write a new entry.', GWOLLE_GB_TEXTDOMAIN) . '" />
		</div>';


	/*
	 * Build up Form including possible error_fields
	 */

	$form_setting = gwolle_gb_get_setting( 'form' );
	$autofocus = 'autofocus="autofocus"';

	// Form for submitting new entries
	$output .= '
		<form id="gwolle_gb_new_entry" action="" method="POST">
			<h3>' . __('Write a new entry for the Guestbook', GWOLLE_GB_TEXTDOMAIN) . '</h3>
			<input type="hidden" name="gwolle_gb_function" value="add_entry" />';

	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_name">
				<div class="label"><label for="gwolle_gb_author_name">' . __('Name', GWOLLE_GB_TEXTDOMAIN) . ':';
		if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('name', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $name . '" type="text" name="gwolle_gb_author_name" id="gwolle_gb_author_name" placeholder="' . __('Name', GWOLLE_GB_TEXTDOMAIN) . '" ';
		if ( in_array('name', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_origin">
					<div class="label"><label for="gwolle_gb_author_origin">' . __('City', GWOLLE_GB_TEXTDOMAIN) . ':';
		if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
					<div class="input"><input class="';
		if (in_array('author_origin', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $origin . '" type="text" name="gwolle_gb_author_origin" id="gwolle_gb_author_origin" placeholder="' . __('City', GWOLLE_GB_TEXTDOMAIN) . '" ';
		if ( in_array('author_origin', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
				</div>
				<div class="clearBoth">&nbsp;</div>';
	}

	if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_email">
				<div class="label"><label for="gwolle_gb_author_email">' . __('Email', GWOLLE_GB_TEXTDOMAIN) . ':';
		if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('author_email', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $email . '" type="text" name="gwolle_gb_author_email" id="gwolle_gb_author_email" placeholder="' . __('Email', GWOLLE_GB_TEXTDOMAIN) . '" ';
		if ( in_array('author_email', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_website">
				<div class="label"><label for="gwolle_gb_author_website">' . __('Website', GWOLLE_GB_TEXTDOMAIN) . ':';
		if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('author_website', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $website . '" type="text" name="gwolle_gb_author_website" id="gwolle_gb_author_website" placeholder="' . __('Website', GWOLLE_GB_TEXTDOMAIN) . '" ';
		if ( in_array('author_website', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_content">
				<div class="label"><label for="gwolle_gb_content">' . __('Guestbook entry', GWOLLE_GB_TEXTDOMAIN) . ':';
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><textarea name="gwolle_gb_content" id="gwolle_gb_content" class="';
		if (in_array('content', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" ';
		if ( in_array('content', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' >' . $content . '</textarea></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}


	/* FIXME: add smileys for use in the content textarea */


	if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
		$antispam_question = get_option('gwolle_gb-antispam-question');
		$antispam_answer   = get_option('gwolle_gb-antispam-answer');

		if ( isset($antispam_question) && strlen($antispam_question) > 0 && isset($antispam_answer) && strlen($antispam_answer) > 0 ) {
			$output .= '
				<div class="gwolle_gb_antispam">
					<div class="label">
						<label for="gwolle_gb_antispam_answer">' . __('Anti-spam', GWOLLE_GB_TEXTDOMAIN) . ': *<br />
						' . __('Question:', GWOLLE_GB_TEXTDOMAIN) . " " .  $antispam_question . '</label>
					</div>
					<div class="input"><input class="';
			if (in_array('antispam', $gwolle_gb_error_fields)) {
				$output .= ' error';
			}
			$output .= '" value="' . $antispam . '" type="text" name="gwolle_gb_antispam_answer" id="gwolle_gb_antispam_answer" placeholder="' . __('Answer', GWOLLE_GB_TEXTDOMAIN) . '" ';
		if ( in_array('antispam', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' />
					</div>
				</div>
				<div class="clearBoth">&nbsp;</div>';
		}
	}


	/* reCAPTCHA */
	if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
		// Register API keys at https://www.google.com/recaptcha/admin
		$recaptcha_publicKey = get_option('recaptcha-public-key');
		$recaptcha_privateKey = get_option('recaptcha-private-key');

		if ( isset($recaptcha_publicKey) && strlen($recaptcha_publicKey) > 0 && isset($recaptcha_privateKey) && strlen($recaptcha_privateKey) > 0 ) {
			// Don't show it, if we cannot use it, with only the ReCaptchaResponse class available
			if ( !(!class_exists('ReCaptcha') && class_exists('ReCaptchaResponse')) ) {
				$output .= '
					<div class="gwolle_gb_recaptcha">
						<div class="label">' . __('Anti-spam', GWOLLE_GB_TEXTDOMAIN) . ': *</div>
						<div class="input ';
				if (in_array('recaptcha', $gwolle_gb_error_fields)) {
					$output .= ' error';
				}
				$output .=
						' ">
							<div class="g-recaptcha" data-sitekey="' . $recaptcha_publicKey . '"></div>
						</div>
					</div>
					<div class="clearBoth">&nbsp;</div>';
				wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js', 'jquery', GWOLLE_GB_VER, false );
			}
		}
	}


	$output .= '
			<div class="gwolle_gb_submit">
				<div class="label">&nbsp;</div>
				<div class="input"><input type="submit" name="gwolle_gb_submit" value="' . __('Submit', GWOLLE_GB_TEXTDOMAIN) . '" /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>

			<div class="gwolle_gb_notice">
				' . __('Fields marked with * are obligatory.', GWOLLE_GB_TEXTDOMAIN) . '
				<br />';
	if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
		$output .= __('The E-mail address wil not be published.', GWOLLE_GB_TEXTDOMAIN) . '
				<br />';
	}
	$output .= sprintf( __('For security reasons we save the ip address <span id="gwolle_gb_ip">%s</span>.', GWOLLE_GB_TEXTDOMAIN), $_SERVER['REMOTE_ADDR'] ) . '
				<br />';


	if (get_option('gwolle_gb-moderate-entries', 'true') === 'true') {
		$output .= __('Your entry will be visible in the guestbook after we reviewed it.', GWOLLE_GB_TEXTDOMAIN) . '
				<br />';
	}
	$output .= __('We reserve our right to edit, delete, or not publish entries.', GWOLLE_GB_TEXTDOMAIN) . '
			</div>
		</form>';

	if ( get_option( 'gwolle_gb-labels_float', 'true' ) === 'true' ) {
		?>
		<style type='text/css'>
			#gwolle_gb .label,
			#gwolle_gb .input {
				float: left;
			}
		</style>
		<?php
	}

	// FIXME: make this notice into a textarea for the admin so it can be changed as desired.



	// Add filter for the form, so devs can manipulate it.
	$output = apply_filters( 'gwolle_gb_write', $output);

	return $output;
}

