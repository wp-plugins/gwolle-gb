<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Build up a form for the user, including possible error_fields
 */

function gwolle_gb_frontend_write( $shortcode_atts ) {
	global $gwolle_gb_errors, $gwolle_gb_error_fields, $gwolle_gb_messages, $gwolle_gb_data;

	$html5 = current_theme_supports( 'html5' );
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


	/*
	 * Button 'write a new entry.'
	 */

	$output .= '
		<div id="gwolle_gb_write_button">
			<input type="button" value="&raquo; ' . esc_attr__('Write a new entry.', 'gwolle-gb') . '" />
		</div>';


	// Option to allow only logged-in users to post. Don't show the form if not logged-in. We still see the messages above.
	if ( !is_user_logged_in() && get_option('gwolle_gb-require_login', 'false') == 'true' ) {
		$output .= '
			<div id="gwolle_gb_new_entry">
				<h3>' . __('Log in to post an entry', 'gwolle-gb') . '</h3>';

		$args = array(
			'echo'     => false,
			'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
		);
		$output .= wp_login_form( $args );

		$output .= wp_register('', '', false);

		$output .= '</div>';

		return $output;
	}



	/*
	 * Build up Form including possible error_fields
	 */

	$form_setting = gwolle_gb_get_setting( 'form' );
	$autofocus = 'autofocus="autofocus"';

	// Form for submitting new entries
	$header = gwolle_gb_sanitize_output( get_option('gwolle_gb-header', false) );
	if ( $header == false ) {
		$header = __('Write a new entry for the Guestbook', 'gwolle-gb');
	}

	$output .= '
		<form id="gwolle_gb_new_entry" action="#" method="POST">
			<h3>' . $header . '</h3>
			<input type="hidden" name="gwolle_gb_function" id="gwolle_gb_function" value="add_entry" />';

	// The book_id from the shortcode, to be used by the posthandling function again.
	$output .= '<input type="hidden" name="gwolle_gb_book_id" id="gwolle_gb_book_id" value="' . $shortcode_atts['book_id'] . '" />';

	// Use this filter to just add something
	$output .= apply_filters( 'gwolle_gb_write_add_before', '' );

	/* Name */
	if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_name">
				<div class="label"><label for="gwolle_gb_author_name">' . __('Name', 'gwolle-gb') . ':';
		if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('name', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $name . '" type="text" name="gwolle_gb_author_name" id="gwolle_gb_author_name" placeholder="' . __('Name', 'gwolle-gb') . '" ';
		if ( in_array('name', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	/* City / Origin */
	if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_origin">
					<div class="label"><label for="gwolle_gb_author_origin">' . __('City', 'gwolle-gb') . ':';
		if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
					<div class="input"><input class="';
		if (in_array('author_origin', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $origin . '" type="text" name="gwolle_gb_author_origin" id="gwolle_gb_author_origin" placeholder="' . __('City', 'gwolle-gb') . '" ';
		if ( in_array('author_origin', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
				</div>
				<div class="clearBoth">&nbsp;</div>';
	}

	/* Email */
	if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_email">
				<div class="label"><label for="gwolle_gb_author_email">' . __('Email', 'gwolle-gb') . ':';
		if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('author_email', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $email . '" ' . ($html5 ? 'type="email"' : 'type="text"') . ' name="gwolle_gb_author_email" id="gwolle_gb_author_email" placeholder="' . __('Email', 'gwolle-gb') . '" ';
		if ( in_array('author_email', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	} else {
		if ( isset($email) && strlen($email) > 0 ) {
			// For logged in users, just save the email anyway.
			$output .= '<input class="" value="' . $email . '" type="hidden" name="gwolle_gb_author_email" id="gwolle_gb_author_email" />';
		}
	}

	/* Website / Homepage */
	if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_author_website">
				<div class="label"><label for="gwolle_gb_author_website">' . __('Website', 'gwolle-gb') . ':';
		if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><input class="';
		if (in_array('author_website', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" value="' . $website . '" ' . ($html5 ? 'type="url"' : 'type="text"') . ' name="gwolle_gb_author_website" id="gwolle_gb_author_website" placeholder="' . __('Website', 'gwolle-gb') . '" ';
		if ( in_array('author_website', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	/* Content */
	if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
		$output .= '<div class="gwolle_gb_content">
				<div class="label"><label for="gwolle_gb_content">' . __('Guestbook entry', 'gwolle-gb') . ':';
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) { $output .= ' *';}
		$output .= '</label></div>
				<div class="input"><textarea name="gwolle_gb_content" id="gwolle_gb_content" class="';
		if (in_array('content', $gwolle_gb_error_fields)) {
			$output .= ' error';
		}
		$output .= '" placeholder="' . __('Message', 'gwolle-gb') . '" ';
		if ( in_array('content', $gwolle_gb_error_fields) && isset($autofocus) ) {
			$output .= $autofocus;
			$autofocus = false; // disable it for the next error.
		}
		$output .= ' >' . $content . '</textarea>';

		if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
			// BBcode and MarkItUp
			wp_enqueue_script( 'markitup', plugins_url('markitup/jquery.markitup.js', __FILE__), 'jquery', GWOLLE_GB_VER, false );
			wp_enqueue_script( 'markitup_set', plugins_url('markitup/set.js', __FILE__), 'jquery', GWOLLE_GB_VER, false );
			wp_enqueue_style('gwolle_gb_markitup_css', plugins_url('markitup/style.css', __FILE__), false, GWOLLE_GB_VER,  'screen');

			$dataToBePassed = array(
				'bold'      => __('Bold', 'gwolle-gb' ),
				'italic'    => __('Italic', 'gwolle-gb' ),
				'bullet'    => __('Bulleted List', 'gwolle-gb' ),
				'numeric'   => __('Numeric List', 'gwolle-gb' ),
				'picture'   => __('Picture', 'gwolle-gb' ),
				'source'    => __('Source', 'gwolle-gb' ),
				'link'      => __('Link', 'gwolle-gb' ),
				'linktext'  => __('Your text to link...', 'gwolle-gb' ),
				'clean'     => __('Clean', 'gwolle-gb' ),
				'emoji'     => __('Emoji', 'gwolle-gb' )
			);
			wp_localize_script( 'markitup_set', 'gwolle_gb_localize', $dataToBePassed );

			// Emoji symbols
			$output .= '<div class="gwolle_gb_emoji" style="display:none;">';
			$output .= gwolle_gb_get_emoji();
			$output .= '</div>';
		}

		$output .= '</div>'; // .input

		$output .= '
				</div>
			<div class="clearBoth">&nbsp;</div>';
	}

	/* Custom Anti-Spam */
	if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
		$antispam_question = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-question') );
		$antispam_answer   = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-answer') );

		if ( isset($antispam_question) && strlen($antispam_question) > 0 && isset($antispam_answer) && strlen($antispam_answer) > 0 ) {
			$output .= '
				<div class="gwolle_gb_antispam">
					<div class="label">
						<label for="gwolle_gb_antispam_answer">' . __('Anti-spam', 'gwolle-gb') . ': *<br />
						' . __('Question:', 'gwolle-gb') . " " .  $antispam_question . '</label>
					</div>
					<div class="input"><input class="';
			if (in_array('antispam', $gwolle_gb_error_fields)) {
				$output .= ' error';
			}
			$output .= '" value="' . $antispam . '" type="text" name="gwolle_gb_antispam_answer" id="gwolle_gb_antispam_answer" placeholder="' . __('Answer', 'gwolle-gb') . '" ';
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

	/* CAPTCHA */
	if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
		if ( class_exists('ReallySimpleCaptcha') ) {
			// Disable page caching, we want a new CAPTCHA image each time.
			if ( ! defined( 'DONOTCACHEPAGE' ) )
				define( "DONOTCACHEPAGE", "true" );

			// Instantiate the ReallySimpleCaptcha class, which will handle all of the heavy lifting
			$gwolle_gb_captcha = new ReallySimpleCaptcha();

			// Set Really Simple CAPTCHA Options
			$gwolle_gb_captcha->chars           = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
			$gwolle_gb_captcha->char_length     = '4';
			$gwolle_gb_captcha->img_size        = array( '72', '24' );
			$gwolle_gb_captcha->fg              = array( '0', '0', '0' );
			$gwolle_gb_captcha->bg              = array( '255', '255', '255' );
			$gwolle_gb_captcha->font_size       = '16';
			$gwolle_gb_captcha->font_char_width = '15';
			$gwolle_gb_captcha->img_type        = 'png';
			$gwolle_gb_captcha->base            = array( '6', '18' );

			// Generate random word and image prefix
			$gwolle_gb_captcha_word = $gwolle_gb_captcha->generate_random_word();
			$gwolle_gb_captcha_prefix = mt_rand();
			// Generate CAPTCHA image
			$gwolle_gb_captcha_image_name = $gwolle_gb_captcha->generate_image($gwolle_gb_captcha_prefix, $gwolle_gb_captcha_word);
			// Define values for CAPTCHA fields
			$gwolle_gb_captcha_image_url = content_url('plugins/really-simple-captcha/tmp/');
			$gwolle_gb_captcha_image_src = $gwolle_gb_captcha_image_url . $gwolle_gb_captcha_image_name;
			$gwolle_gb_captcha_image_width = $gwolle_gb_captcha->img_size[0];
			$gwolle_gb_captcha_image_height = $gwolle_gb_captcha->img_size[1];
			$gwolle_gb_captcha_field_size = $gwolle_gb_captcha->char_length;

			// Enqueue and localize the frontend script for CAPTCHA.
			wp_enqueue_script('gwolle_gb_captcha_js', plugins_url('js/captcha.js', __FILE__), 'jquery', GWOLLE_GB_VER, true );
			$dataToBePassed = array(
				// URL to wp-admin/admin-ajax.php to process the request
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				// generate a nonce with a unique ID "gwolle_gb_captcha_ajax"
				// so that you can check it later when an AJAX request is sent
				'security'  => wp_create_nonce( 'gwolle_gb_captcha_ajax' ),
				'correct'   => __ ('Correct CAPTCHA value.', 'gwolle-gb' ),
				'incorrect' => __( 'Incorrect CAPTCHA value.', 'gwolle-gb' ),
				'gwolle_gb_captcha_prefix' => $gwolle_gb_captcha_prefix
			);
			wp_localize_script( 'gwolle_gb_captcha_js', 'gwolle_gb_captcha', $dataToBePassed );

			// Output the CAPTCHA fields
			$output .= '
				<div class="gwolle_gb_captcha">
					<div class="label">
						<label for="gwolle_gb_captcha_code">' . __('Anti-spam', 'gwolle-gb') . ': *<br />
						<img src="' . $gwolle_gb_captcha_image_src . '" alt="captcha" width="' . $gwolle_gb_captcha_image_width . '" height="' . $gwolle_gb_captcha_image_height . '" />
						</label>
					</div>
					<div class="input">
					<input class="';
			if (in_array('captcha', $gwolle_gb_error_fields)) {
				$output .= 'error';
			}
			$output .= '" value="" type="text" name="gwolle_gb_captcha_code" id="gwolle_gb_captcha_code" placeholder="' . __('CAPTCHA', 'gwolle-gb') . '" ';
			if ( in_array('captcha', $gwolle_gb_error_fields) && isset($autofocus) ) {
				$output .= $autofocus;
				$autofocus = false; // disable it for the next error.
			}
			$output .= ' />
							<input type="hidden" name="gwolle_gb_captcha_prefix" id="gwolle_gb_captcha_prefix" value="' . $gwolle_gb_captcha_prefix . '" />
							<span id="gwolle_gb_captcha_verify"></span>
						</div>
					</div>
					<div class="clearBoth">&nbsp;</div>';
		}
	}

	// Use this filter to just add something
	$output .= apply_filters( 'gwolle_gb_write_add_form', '' );

	$output .= '
			<div class="gwolle_gb_submit">
				<div class="label">&nbsp;</div>
				<div class="input"><input type="submit" name="gwolle_gb_submit" value="' . esc_attr__('Submit', 'gwolle-gb') . '" /></div>
			</div>
			<div class="clearBoth">&nbsp;</div>

			<div class="gwolle_gb_notice">
				';

	$notice = gwolle_gb_sanitize_output( get_option('gwolle_gb-notice', false) );
	if ( $notice == false ) { // No text set by the user. Use the default text.
		$notice = __('
Fields marked with * are obligatory.
Your E-mail address wil not be published.
For security reasons we save the ip address %ip%.
It might be that your entry will only be visible in the guestbook after we reviewed it.
We reserve our right to edit, delete, or not publish entries.
'
, 'gwolle-gb');
	}

	$notice = nl2br($notice);
	$output .= str_replace('%ip%', $_SERVER['REMOTE_ADDR'], $notice);

	$output .= '
			</div>';

	// Use this filter to just add something
	$output .= apply_filters( 'gwolle_gb_write_add_after', '' );

	$output .= '</form>';

	if ( get_option( 'gwolle_gb-labels_float', 'true' ) === 'true' ) {
		$output .= '
		<style type="text/css" scoped>
			#gwolle_gb .label,
			#gwolle_gb .input {
				float: left;
			}
		</style>
		';
	}


	// Add filter for the form, so devs can manipulate it.
	$output = apply_filters( 'gwolle_gb_write', $output);

	return $output;
}

