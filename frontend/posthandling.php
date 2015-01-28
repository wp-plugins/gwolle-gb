<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * Save new entries to the database, when valid.
 *
 * Mandatory fields:
 * - author_name
 * - author_email
 * - content
 * - negative Akismet result (= no spam)
 * - correct reCAPTCHA
 * (the last two only when turned on in the settings panel)
 *
 * global vars used:
 * $gwolle_gb_errors: false if no errors found, true if errors found
 * $gwolle_gb_error_fields: array of the formfields with errors
 * $gwolle_gb_messages: array of messages to be shown
 * $gwolle_gb_data: the data that was submitted, and will be used to fill the form for resubmit
 */

function gwolle_gb_frontend_posthandling() {
	global $gwolle_gb_errors, $gwolle_gb_error_fields, $gwolle_gb_messages, $gwolle_gb_data;

	/*
	 * Handle $_POST and check and save entry.
	 */

	if ( isset($_POST['gwolle_gb_function']) && $_POST['gwolle_gb_function'] == 'add_entry' ) {

		// Initialize errors
		$gwolle_gb_errors = false;
		$gwolle_gb_error_fields = array();

		// Initialize messages
		$gwolle_gb_messages = '';


		/*
		 * Collect data from the Form
		 */
		$gwolle_gb_data = array();
		if (isset($_POST['gwolle_gb_author_name'])) {
			$gwolle_gb_data['author_name'] = trim($_POST['gwolle_gb_author_name']);
			if ( $gwolle_gb_data['author_name'] == "" ) {
				$gwolle_gb_errors = true;
				$gwolle_gb_error_fields[] = 'name'; // mandatory
			}
		} else {
			$gwolle_gb_errors = true;
			$gwolle_gb_error_fields[] = 'name'; // mandatory
		}
		if (isset($_POST['gwolle_gb_author_origin'])) {
			$gwolle_gb_data['author_origin'] = trim($_POST['gwolle_gb_author_origin']);
		}
		if (isset($_POST['gwolle_gb_author_email'])) {
			$gwolle_gb_data['author_email'] = trim($_POST['gwolle_gb_author_email']);
			if ( $gwolle_gb_data['author_email'] == "" ) {
				$gwolle_gb_errors = true;
				$gwolle_gb_error_fields[] = 'author_email'; // mandatory
			}
		} else {
			$gwolle_gb_errors = true;
			$gwolle_gb_error_fields[] = 'author_email'; // mandatory
		}
		if (isset($_POST['gwolle_gb_author_website'])) {
			$gwolle_gb_data['author_website'] = trim($_POST['gwolle_gb_author_website']);
		}
		if (isset($_POST['gwolle_gb_content'])) {
			$gwolle_gb_data['content'] = trim($_POST['gwolle_gb_content']);
			if ( $gwolle_gb_data['content'] == "" ) {
				$gwolle_gb_errors = true;
				$gwolle_gb_error_fields[] = 'content'; // mandatory
			}
		} else {
			$gwolle_gb_errors = true;
			$gwolle_gb_error_fields[] = 'content'; // mandatory
		}


		/* reCAPTCHA */
		if (get_option('gwolle_gb-recaptcha-active', 'false') === 'true' ) {

			// Avoid Nasty Crash
			if (!class_exists('ReCaptcha') && !class_exists('ReCaptchaResponse') ) {
				require_once "recaptchalib.php";
			}

			// We can only use it if it is really loaded.
			if (class_exists('ReCaptcha') && class_exists('ReCaptchaResponse') ) {
				// Register API keys at https://www.google.com/recaptcha/admin
				//$recaptcha_publicKey = get_option('recaptcha-public-key');
				$recaptcha_privateKey = get_option('recaptcha-private-key');

				// The response from reCAPTCHA
				$resp = null;
				// The error code from reCAPTCHA, if any
				$error = null;

				$reCaptcha = new ReCaptcha( $recaptcha_privateKey );

				// Was there a reCAPTCHA response?
				if ( isset($_POST["g-recaptcha-response"]) && $_POST["g-recaptcha-response"] ) {
					$resp = $reCaptcha->verifyResponse(
						$_SERVER["REMOTE_ADDR"],
						$_POST["g-recaptcha-response"]
					);
				}

				if ( $resp != null && $resp->success ) {
					//echo "You got it!";
				} else {
					$gwolle_gb_errors = true;
					$gwolle_gb_error_fields[] = 'recaptcha'; // mandatory
				}
			}
		}


		/* If there are errors, stop here and return false */
		if ( is_array( $gwolle_gb_error_fields ) && !empty( $gwolle_gb_error_fields ) ) {
			// There was no data filled in, even though that was mandatory.
			$gwolle_gb_messages .= '<p class="error_fields"><strong>' . __('There were errors submitting your guestbook entry.', GWOLLE_GB_TEXTDOMAIN) . '</strong></p>';
			return false; // no need to check and save
		}


		/* New Instance of gwolle_gb_entry. */
		$entry = new gwolle_gb_entry();


		/* Set the data in the instance */
		$set_data = $entry->set_data( $gwolle_gb_data );
		if ( !$set_data ) {
			// Data is not set in the Instance, something happened
			$gwolle_gb_errors = true;
			$gwolle_gb_messages .= '<p class="set_data"><strong>' . __('There were errors submitting your guestbook entry.', GWOLLE_GB_TEXTDOMAIN) . '</strong></p>';
			return false;
		}


		/* Check for spam and set accordingly */
		$isspam = gwolle_gb_akismet( $entry, 'comment-check' );
		if ( $isspam ) {
			// Returned true, so considered spam
			$entry->set_isspam(true);
			// Is it wise to make them any wiser? Probably not...
			// $gwolle_gb_messages .= '<p><strong>' . __('Your guestbook entry is probably spam. A moderator will decide upon it.', GWOLLE_GB_TEXTDOMAIN) . '</strong></p>';
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
			$entry->set_ischecked( true );
		}


		/* Check for logged in user, and set the userid as author_id, just in case someone is also admin, or gets promoted some day */
		$entry->set_author_id( $user_id );


		/*
		 * Network Information
		 */
		$entry->set_author_ip( $_SERVER['REMOTE_ADDR'] );
		$entry->set_author_host( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );


		/*
		 * Check for double post using email field and content.
		 */

		$entries = gwolle_gb_get_entries(array(
				'email' => $entry->get_author_email()
			));
		if ( is_array( $entries ) && !empty( $entries ) ) {
			foreach ( $entries as $entry_email ) {
				if ( $entry_email->get_content() == $entry->get_content() ) {
					// Match is double entry
					$gwolle_gb_errors = true;
					$gwolle_gb_messages .= '<p class="double_post"><strong>' . __('Double post: An entry with the data you entered has already been saved.', GWOLLE_GB_TEXTDOMAIN) . '</strong></p>';
					return false;
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
			$gwolle_gb_messages .= '<p class="entry_saved">' . __('Thank you for your entry.',GWOLLE_GB_TEXTDOMAIN) . '</p>';
			if ( get_option('gwolle_gb-moderate-entries', 'true') === 'true' && !gwolle_gb_is_moderator($user_id) ) {
				$gwolle_gb_messages .= '<p>' . __('We will review it and unlock it in a short while.',GWOLLE_GB_TEXTDOMAIN) . '</p>';
			}
		}


		/*
		 * Send the Notification Mail to moderators that have subscribed (only when it is not Spam)
		 */

		if ( !$isspam ) {
			$subscribers = Array();
			$recipients = get_option('gwolle_gb-notifyByMail', Array() );
			if ( count($recipients ) > 0 ) {
				$recipients = explode( ",", $recipients );
				foreach ( $recipients as $recipient ) {
					if ( is_numeric($recipient) ) {
						$userdata = get_userdata( $recipient );
						$subscribers[] = $userdata->user_email;
					}
				}
			}


			@ini_set('sendmail_from', get_bloginfo('admin_mail'));

			// Set the Mail Content
			$mailTags = array('user_email', 'entry_management_url', 'blog_name', 'blog_url', 'wp_admin_url');
			$mail_body = stripslashes( get_option( 'gwolle_gb-adminMailContent' ) );
			if (!$mail_body) {
				$mail_body = get_option( 'gwolle_gb-defaultMailText' );
			}
			// FIXME: use more content in the mailbody from the entry, like author_name, email, content

			// Set the Mail Headers
			$subject = '[' . get_bloginfo('name') . '] ' . __('New Guestbook Entry', GWOLLE_GB_TEXTDOMAIN);
			$header = "";
			if ( get_option('gwolle_gb-mail-from', false) ) {
				$header .= "From: " . get_bloginfo('name') . " <" . get_option('gwolle_gb-mail-from') . ">\r\n";
			} else {
				$header .= "From: " . get_bloginfo('name') . " <" . get_bloginfo('admin_email') . ">\r\n";
			}
			$header .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Encoding of the mail

			// Replace the tags from the mailtemplate with real data from the website and entry
			$info['blog_name'] = get_bloginfo('name');
			$info['blog_url'] = get_bloginfo('wpurl');
			$info['wp_admin_url'] = $info['blog_url'] . '/wp-admin';
			$info['entry_management_url'] = $info['wp_admin_url'] . '/admin.php?page=' . GWOLLE_GB_FOLDER . '/editor.php&entry_id=' . $entry->get_id();
			// The last tags are bloginfo-based
			for ($tagNum = 1; $tagNum < count($mailTags); $tagNum++) {
				$mail_body = str_replace('%' . $mailTags[$tagNum] . '%', $info[$mailTags[$tagNum]], $mail_body);
			}

			if ( is_array($subscribers) && !empty($subscribers) ) {
				foreach ( $subscribers as $subscriber ) {
					$mailBody = $mail_body;
					$mailBody = str_replace('%user_email%', $subscriber, $mailBody);
					$mailBody = str_replace('%entry_content%', gwolle_gb_format_values_for_mail($entry->get_content()), $mailBody);

					wp_mail($subscriber, $subject, $mailBody, $header);
				}
			}
		}


		/*
		 * FIXME: Send Notification Mail to the author if set to true in an option
		 */


		/*
		 * No Log for the Entry needed, it has a default post date in the Entry itself.
		 */

	}
}

