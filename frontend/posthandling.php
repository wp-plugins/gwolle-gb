<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Save new entries to the database, when valid.
 *
 * global vars used:
 * $gwolle_gb_errors: false if no errors found, true if errors found
 * $gwolle_gb_error_fields: array of the formfields with errors
 * $gwolle_gb_messages: array of messages to be shown
 * $gwolle_gb_data: the data that was submitted, and will be used to fill the form for resubmit
 */

function gwolle_gb_frontend_posthandling() {
	global $wpdb, $gwolle_gb_errors, $gwolle_gb_error_fields, $gwolle_gb_messages, $gwolle_gb_data;

	/*
	 * Handle $_POST and check and save entry.
	 */

	if ( isset($_POST['gwolle_gb_function']) && $_POST['gwolle_gb_function'] == 'add_entry' ) {

		// Initialize errors
		$gwolle_gb_errors = false;
		$gwolle_gb_error_fields = array();

		// Initialize messages
		$gwolle_gb_messages = '';


		// Option to allow only logged-in users to post. Don't show the form if not logged-in.
		if ( !is_user_logged_in() && get_option('gwolle_gb-require_login', 'false') == 'true' ) {
			$gwolle_gb_errors = true;
			$gwolle_gb_messages .= '<p class="require_login"><strong>' . __('Submitting a new guestbook entry is only allowed for logged-in users.', 'gwolle-gb') . '</strong></p>';
			return;
		}


		/*
		 * Collect data from the Form
		 */
		$gwolle_gb_data = array();
		$form_setting = gwolle_gb_get_setting( 'form' );

		/* Name */
		if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_name'])) {
				$gwolle_gb_data['author_name'] = trim($_POST['gwolle_gb_author_name']);
				$gwolle_gb_data['author_name'] = gwolle_gb_maybe_encode_emoji( $gwolle_gb_data['author_name'], 'author_name' );
				if ( $gwolle_gb_data['author_name'] == "" ) {
					if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
						$gwolle_gb_errors = true;
						$gwolle_gb_error_fields[] = 'name'; // mandatory
					}
				}
			} else {
				if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'name'; // mandatory
				}
			}
		}

		/* City / Origin */
		if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_origin'])) {
				$gwolle_gb_data['author_origin'] = trim($_POST['gwolle_gb_author_origin']);
				$gwolle_gb_data['author_origin'] = gwolle_gb_maybe_encode_emoji( $gwolle_gb_data['author_origin'], 'author_origin' );
				if ( $gwolle_gb_data['author_origin'] == "" ) {
					if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
						$gwolle_gb_errors = true;
						$gwolle_gb_error_fields[] = 'author_origin'; // mandatory
					}
				}
			} else {
				if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'author_origin'; // mandatory
				}
			}
		}

		/* Email */
		if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_email'])) {
				$gwolle_gb_data['author_email'] = trim($_POST['gwolle_gb_author_email']);
				if ( filter_var( $gwolle_gb_data['author_email'], FILTER_VALIDATE_EMAIL ) ) {
					// Valid Email address.
				} else if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'author_email'; // mandatory
				}
			} else {
				if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'author_email'; // mandatory
				}
			}
		} else {
			if (isset($_POST['gwolle_gb_author_email'])) {
				$gwolle_gb_data['author_email'] = trim($_POST['gwolle_gb_author_email']);
			}
		}

		/* Website / Homepage */
		if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_author_website'])) {
				$gwolle_gb_data['author_website'] = trim($_POST['gwolle_gb_author_website']);
				$pattern = '/^http/';
				if ( !preg_match($pattern, $gwolle_gb_data['author_website'], $matches) ) {
					$gwolle_gb_data['author_website'] = "http://" . $gwolle_gb_data['author_website'];
				}
				if ( filter_var( $gwolle_gb_data['author_website'], FILTER_VALIDATE_URL ) ) {
					// Valid Website URL.
				} else if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'author_website'; // mandatory
				}
			} else {
				if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'author_website'; // mandatory
				}
			}
		}

		/* Message */
		if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
			if (isset($_POST['gwolle_gb_content'])) {
				$gwolle_gb_data['content'] = trim($_POST['gwolle_gb_content']);
				if ( $gwolle_gb_data['content'] == "" ) {
					if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
						$gwolle_gb_errors = true;
						$gwolle_gb_error_fields[] = 'content'; // mandatory
					}
				} else {
					$gwolle_gb_data['content'] = gwolle_gb_maybe_encode_emoji( $gwolle_gb_data['content'], 'content' );
				}
			} else {
				if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'content'; // mandatory
				}
			}
		}

		/* Custom Anti-Spam */
		if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
			$antispam_question = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-question') );
			$antispam_answer   = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-answer') );

			if ( isset($antispam_question) && strlen($antispam_question) > 0 && isset($antispam_answer) && strlen($antispam_answer) > 0 ) {
				if ( isset($_POST["gwolle_gb_antispam_answer"]) && trim($_POST["gwolle_gb_antispam_answer"]) == trim($antispam_answer) ) {
					//echo "You got it!";
				} else {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'antispam'; // mandatory
				}
			}
			if ( isset($_POST["gwolle_gb_antispam_answer"]) ) {
				$gwolle_gb_data['antispam'] = trim($_POST['gwolle_gb_antispam_answer']);
			}
		}

		/* CAPTCHA */
		if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
			if ( class_exists('ReallySimpleCaptcha') ) {
				$gwolle_gb_captcha = new ReallySimpleCaptcha();
				// This variable holds the CAPTCHA image prefix, which corresponds to the correct answer
				$gwolle_gb_captcha_prefix = $_POST['gwolle_gb_captcha_prefix'];
				// This variable holds the CAPTCHA response, entered by the user
				$gwolle_gb_captcha_code = $_POST['gwolle_gb_captcha_code'];
				// Validate the CAPTCHA response
				$gwolle_gb_captcha_correct = $gwolle_gb_captcha->check( $gwolle_gb_captcha_prefix, $gwolle_gb_captcha_code );
				// If CAPTCHA validation fails (incorrect value entered in CAPTCHA field) mark comment as spam.
				if ( true != $gwolle_gb_captcha_correct ) {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'captcha'; // mandatory
					//$gwolle_gb_messages .= '<p style="display_:none"><strong>' . $gwolle_gb_captcha_correct . '</strong></p>';
				} else {
					// verified!
					//$gwolle_gb_messages .= '<p class="error_fields"><strong>Verified.</strong></p>';
				}
				// clean up the tmp directory
				$gwolle_gb_captcha->remove($gwolle_gb_captcha_prefix);
				$gwolle_gb_captcha->cleanup();
			}
		}


		/* If there are errors, stop here and return false */
		if ( is_array( $gwolle_gb_error_fields ) && !empty( $gwolle_gb_error_fields ) ) {
			// There was no data filled in, even though that was mandatory.
			$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('There were errors submitting your guestbook entry.', 'gwolle-gb') . '</strong></p>';

			if ( isset($gwolle_gb_error_fields) ) {
				foreach ( $gwolle_gb_error_fields as $field ) {
					switch ( $field ) {
						case 'name':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('Your name is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'author_origin':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('Your origin is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'author_email':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('Your e-mail address is not filled in correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'author_website':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('Your website is not filled in, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'content':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('There is no message, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'antispam':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('The anti-spam question was not answered correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
						case 'captcha':
							$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('The CAPTCHA was not filled in correctly, even though it is mandatory.', 'gwolle-gb') . '</strong></p>';
							break;
					}
				}
			}
			$gwolle_gb_messages .= '<p class="error_fields" style="display: none;">' . print_r( $gwolle_gb_error_fields, true ) . '</p>';
			return false; // no need to check and save
		}


		/* New Instance of gwolle_gb_entry. */
		$entry = new gwolle_gb_entry();


		/* Set the data in the instance */
		$set_data = $entry->set_data( $gwolle_gb_data );
		if ( !$set_data ) {
			// Data is not set in the Instance, something happened
			$gwolle_gb_errors = true;
			$gwolle_gb_messages .= '<p class="set_data"><strong>' . __('There were errors submitting your guestbook entry.', 'gwolle-gb') . '</strong></p>';
			return false;
		}


		/* Check for spam and set accordingly */
		$isspam = gwolle_gb_akismet( $entry, 'comment-check' );
		if ( $isspam ) {
			// Returned true, so considered spam
			$entry->set_isspam(true);
			// Is it wise to make them any wiser? Probably not...
			// $gwolle_gb_messages .= '<p><strong>' . __('Your guestbook entry is probably spam. A moderator will decide upon it.', 'gwolle-gb') . '</strong></p>';
		}


		/* if Moderation is off, set it to "ischecked" */
		$user_id = get_current_user_id(); // returns 0 if no current user

		if ( get_option('gwolle_gb-moderate-entries', 'true') == 'true' ) {
			if ( gwolle_gb_is_moderator($user_id) ) {
				$entry->set_ischecked( true );
			} else {
				$entry->set_ischecked( false );
			}
		} else {
			// First set to checked
			$entry->set_ischecked( true );

			// Check for abusive content (too long words). Set it to unchecked, so manual moderation is needed.
			$maxlength = 100;
			$words = explode( " ", $entry->get_content() );
			foreach ( $words as $word ) {
				if ( strlen($word) > $maxlength ) {
					$entry->set_ischecked( false );
					break;
				}
			}
			$maxlength = 60;
			$words = explode( " ", $entry->get_author_name() );
			foreach ( $words as $word ) {
				if ( strlen($word) > $maxlength ) {
					$entry->set_ischecked( false );
					break;
				}
			}
		}


		/* Check for logged in user, and set the userid as author_id, just in case someone is also admin, or gets promoted some day */
		$entry->set_author_id( $user_id );


		/*
		 * Network Information
		 */
		$entry->set_author_ip( $_SERVER['REMOTE_ADDR'] );
		$entry->set_author_host( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );


		/*
		 * Book ID
		 */
		if ( isset( $_POST['gwolle_gb_book_id'] ) ) {
			$gwolle_gb_data['book_id'] = (int) $_POST['gwolle_gb_book_id'];
		}
		if ( $gwolle_gb_data['book_id'] < 1 ) {
			$gwolle_gb_data['book_id'] = 1;
		}
		$entry->set_book_id( $gwolle_gb_data['book_id'] );


		/*
		 * Check for double post using email field and content.
		 * Only if content is mandatory.
		 */
		if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
			$entries = gwolle_gb_get_entries(array(
					'email' => $entry->get_author_email()
				));
			if ( is_array( $entries ) && !empty( $entries ) ) {
				foreach ( $entries as $entry_email ) {
					if ( $entry_email->get_content() == $entry->get_content() ) {
						// Match is double entry
						$gwolle_gb_errors = true;
						$gwolle_gb_messages .= '<p class="double_post"><strong>' . __('Double post: An entry with the data you entered has already been saved.', 'gwolle-gb') . '</strong></p>';
						return false;
					}
				}
			}
		}


		/*
		 * Save the Entry
		 */
		// $save = ""; // Testing mode
		$save = $entry->save();
		//if ( WP_DEBUG ) { echo "save: "; var_dump($save); }
		if ( $save ) {
			// We have been saved to the Database
			$gwolle_gb_messages .= '<p class="entry_saved">' . __('Thank you for your entry.','gwolle-gb') . '</p>';
			if ( $entry->get_ischecked() == 0 ) {
				$gwolle_gb_messages .= '<p>' . __('We will review it and unlock it in a short while.','gwolle-gb') . '</p>';
			}
		}


		/*
		 * Update Cache plugins
		 */
		if ( $entry->get_ischecked() == 1 ) {
			gwolle_gb_clear_cache();
		}


		/*
		 * Send the Notification Mail to moderators that have subscribed (only when it is not Spam)
		 */
		gwolle_gb_mail_moderators( $entry );



		/*
		 * Send Notification Mail to the author if set to true in an option
		 */
		gwolle_gb_mail_author( $entry );


		/*
		 * No Log for the Entry needed, it has a default post date in the Entry itself.
		 */

	}
}

