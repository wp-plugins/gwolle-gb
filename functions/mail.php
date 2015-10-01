<?php
/*
 * Mail Functions
 */



/*
 * Send the Notification Mail to moderators that have subscribed (only when it is not Spam).
 *
 * $arg: $entry, instance of gwolle_gb_entry
 * since 1.4.9
 */
function gwolle_gb_mail_moderators( $entry ) {
	$isspam = $entry->get_isspam();
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
		$mailTags = array('user_email', 'user_name', 'status', 'entry_management_url', 'blog_name', 'blog_url', 'wp_admin_url', 'entry_content', 'author_ip');
		$mail_body = gwolle_gb_sanitize_output( get_option( 'gwolle_gb-adminMailContent', false ) );
		if (!$mail_body) {
				$mail_body = __("
Hello,

There is a new guestbook entry at '%blog_name%'.
You can check it at %entry_management_url%.

Have a nice day.
Your Gwolle-GB-Mailer


Website address: %blog_url%
User name: %user_name%
User email: %user_email%
Entry status: %status%
Entry content:
%entry_content%
"
, 'gwolle-gb');
		}

		// Set the Mail Headers
		$subject = '[' . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . '] ' . __('New Guestbook Entry', 'gwolle-gb');
		$header = "";
		if ( get_option('gwolle_gb-mail-from', false) ) {
			$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . get_option('gwolle_gb-mail-from') . ">\r\n";
		} else {
			$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . get_bloginfo('admin_email') . ">\r\n";
		}
		$header .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Encoding of the mail

		// Replace the tags from the mailtemplate with real data from the website and entry
		$info['user_name'] = gwolle_gb_sanitize_output( $entry->get_author_name() );
		$info['user_email'] = $entry->get_author_email();
		$info['blog_name'] = get_bloginfo('name');
		$postid = gwolle_gb_get_postid();
		if ( $postid ) {
			$info['blog_url'] = get_bloginfo('wpurl') . '?p=' . $postid;
		} else {
			$info['blog_url'] = get_bloginfo('wpurl');
		}
		$info['wp_admin_url'] = admin_url( '/admin.php' );
		$info['entry_management_url'] = admin_url( '/admin.php?page=' . GWOLLE_GB_FOLDER . '/editor.php&entry_id=' . $entry->get_id() );
		$info['entry_content'] = gwolle_gb_format_values_for_mail(gwolle_gb_sanitize_output( $entry->get_content() ));
		$info['author_ip'] = $_SERVER['REMOTE_ADDR'];
		if ( $entry->get_ischecked() ) {
			$info['status'] = __('Checked', 'gwolle-gb');
		} else {
			$info['status'] = __('Unchecked', 'gwolle-gb');
		}

		// The last tags are bloginfo-based
		for ($tagNum = 0; $tagNum < count($mailTags); $tagNum++) {
			$mail_body = str_replace('%' . $mailTags[$tagNum] . '%', $info[$mailTags[$tagNum]], $mail_body);
			$mail_body = gwolle_gb_format_values_for_mail( $mail_body );
		}

		if ( is_array($subscribers) && !empty($subscribers) ) {
			foreach ( $subscribers as $subscriber ) {
				wp_mail($subscriber, $subject, $mail_body, $header);
			}
		}
	}
}


/*
 * Send Notification Mail to the author if set to true in an option (only when it is not Spam).
 *
 * $arg: $entry, instance of gwolle_gb_entry
 * since 1.4.9
 */
function gwolle_gb_mail_author( $entry ) {
	$isspam = $entry->get_isspam();
	if ( !$isspam ) {
		if ( get_option( 'gwolle_gb-mail_author', 'false' ) == 'true' ) {

			// Set the Mail Content
			$mailTags = array('user_email', 'user_name', 'blog_name', 'blog_url', 'entry_content');
			$mail_body = gwolle_gb_sanitize_output( get_option( 'gwolle_gb-authorMailContent', false ) );
			if (!$mail_body) {
					$mail_body = __("
Hello,

You have just posted a new guestbook entry at '%blog_name%'.

Have a nice day.
The editors at %blog_name%.


Website address: %blog_url%
User name: %user_name%
User email: %user_email%
Entry content:
%entry_content%
"
, 'gwolle-gb');
			}

			// Set the Mail Headers
			$subject = '[' . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . '] ' . __('New Guestbook Entry', 'gwolle-gb');
			$header = "";
			if ( get_option('gwolle_gb-mail-from', false) ) {
				$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . gwolle_gb_sanitize_output( get_option('gwolle_gb-mail-from') ) . ">\r\n";
			} else {
				$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . get_bloginfo('admin_email') . ">\r\n";
			}
			$header .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Encoding of the mail

			// Replace the tags from the mailtemplate with real data from the website and entry
			$info['user_name'] = gwolle_gb_sanitize_output( $entry->get_author_name() );
			$info['user_email'] = $entry->get_author_email();
			$info['blog_name'] = get_bloginfo('name');
			$postid = gwolle_gb_get_postid();
			if ( $postid ) {
				$info['blog_url'] = get_bloginfo('wpurl') . '?p=' . $postid;
			} else {
				$info['blog_url'] = get_bloginfo('wpurl');
			}
			$info['entry_content'] = gwolle_gb_format_values_for_mail(gwolle_gb_sanitize_output( $entry->get_content() ));
			for ($tagNum = 0; $tagNum < count($mailTags); $tagNum++) {
				$mail_body = str_replace('%' . $mailTags[$tagNum] . '%', $info[$mailTags[$tagNum]], $mail_body);
				$mail_body = gwolle_gb_format_values_for_mail( $mail_body );
			}

			wp_mail($entry->get_author_email(), $subject, $mail_body, $header);

		}
	}
}


/*
 * Send Notification Mail to the author that there is an admin_reply (only when it is not Spam).
 *
 * $arg: $entry, instance of gwolle_gb_entry
 * since 1.4.9
 */
function gwolle_gb_mail_author_on_admin_reply( $entry ) {
	$isspam = $entry->get_isspam();
	if ( !$isspam ) {

		// Set the Mail Content
		$mailTags = array('user_email', 'user_name', 'blog_name', 'blog_url', 'admin_reply');
		$mail_body = gwolle_gb_sanitize_output( get_option( 'gwolle_gb-mail_admin_replyContent', false ) );
		if (!$mail_body) {
			$mail_body = __("
Hello,

An admin has just added or changed a reply message to your guestbook entry at '%blog_name%'.

Have a nice day.
The editors at %blog_name%.


Website address: %blog_url%
Admin Reply:
%admin_reply%
"
, 'gwolle-gb');
		}

		// Set the Mail Headers
		$subject = '[' . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . '] ' . __('Admin Reply', 'gwolle-gb');
		$header = "";
		if ( get_option('gwolle_gb-mail-from', false) ) {
			$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . gwolle_gb_sanitize_output( get_option('gwolle_gb-mail-from') ) . ">\r\n";
		} else {
			$header .= "From: " . gwolle_gb_format_values_for_mail(get_bloginfo('name')) . " <" . get_bloginfo('admin_email') . ">\r\n";
		}
		$header .= "Content-Type: text/plain; charset=UTF-8\r\n"; // Encoding of the mail

		// Replace the tags from the mailtemplate with real data from the website and entry
		$info['user_name'] = gwolle_gb_sanitize_output( $entry->get_author_name() );
		$info['user_email'] = $entry->get_author_email();
		$info['blog_name'] = get_bloginfo('name');
		$postid = gwolle_gb_get_postid();
		if ( $postid ) {
			$info['blog_url'] = get_bloginfo('wpurl') . '?p=' . $postid;
		} else {
			$info['blog_url'] = get_bloginfo('wpurl');
		}
		$info['admin_reply'] = gwolle_gb_format_values_for_mail(gwolle_gb_sanitize_output( $entry->get_admin_reply() ));
		for ($tagNum = 0; $tagNum < count($mailTags); $tagNum++) {
			$mail_body = str_replace('%' . $mailTags[$tagNum] . '%', $info[$mailTags[$tagNum]], $mail_body);
			$mail_body = gwolle_gb_format_values_for_mail( $mail_body );
		}

		wp_mail($entry->get_author_email(), $subject, $mail_body, $header);

	}
}
