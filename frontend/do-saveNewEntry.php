<?php
	/*
	**	Save new entries to the database, when valid.
	**	Obligatory fields:
	**	- name
	**	- entry
	**	... and a negative Akismet result (= no spam) and a correct captcha; both only when turned on in the settings panel.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $wpdb;
	
	//	Captcha processing
	$recaptchaActive = get_option('gwolle_gb-recaptcha-active');
	if ($recaptchaActive == 'true') {
		require_once('recaptcha/recaptchalib.php');
		$privatekey = get_option('recaptcha-private-key');
		$resp = recaptcha_check_answer ($privatekey,
		                                $_SERVER["REMOTE_ADDR"],
		                                $_POST["recaptcha_challenge_field"],
		                                $_POST["recaptcha_response_field"]);
	}
	
	if ($resp->is_valid || $recaptchaActive == 'false') {
		if (strlen(str_replace(' ','',$_POST['entry_author_name'])) == 0) {
			$error = true;
		}
		if (strlen(str_replace(' ','',$_POST['entry_content'])) == 0) {
			$error = true;
		}
		
		if (!$error) {	//	Neither the name field nor the entry field threw an error.
			//	Double post?
			$doublePost_result = mysql_query("
				SELECT *
				FROM
					" . $wpdb->prefix . "gwolle_gb_entries
				WHERE
					entry_author_name = '" . mysql_real_escape_string($_POST['entry_author_name']) . "'
					AND
					entry_author_email = '" . mysql_real_escape_string($_POST['entry_author_email']) . "'
					AND
					entry_author_origin = '" . mysql_real_escape_string($_POST['entry_author_origin']) . "'
					AND
					entry_author_ip = '" . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . "'
					AND
					entry_content = '" . mysql_real_escape_string($_POST['entry_content']) . "'
			");
			if (mysql_num_rows($doublePost_result) > 0) {
				$msg = 'entry-saved';
			}
			else {
				//	Entries are not checked by default
				$isChecked = '0';
				
				/*
				**	Check for spam using Akismet, if this has been set in the
				**	settings dialog of Gwolle-GB and if there's a Wordpress API key defined.
				*/
				$wordpressApiKey = get_option('wordpress_api_key');
				
				if (get_option('gwolle_gb-akismet-active') == 'true' && $wordpressApiKey) {
					if (version_compare(phpversion(),'5.0','>=')) {
						include('wp-content/plugins/gwolle-gb/' . AKISMET_PHP5_CLASS_DIR . '/Akismet.class.php');
						
						$blogURL = get_bloginfo('url');
	   				
	   				$akismet = new Akismet($blogURL, $wordpressApiKey);
	   				$akismet->setCommentAuthor($_POST['entry_author_name']);
	   				$akismet->setCommentAuthorEmail($_POST['entry_author_email']);
	   				$akismet->setCommentAuthorURL($_POST['entry_author_website']);
	   				$akismet->setCommentContent($_POST['entry_content']);
	   				$akismet->setPermalink($blogURL); //	what's this?
	   				
	   				if($akismet->isCommentSpam()) {
							//	Akismet detected spam.
							$isSpam = '1';
						}
					}
					elseif (version_compare(phpversion(),'4.0','>=')) {
						//	Use the PHP4 class
						include(WP_PLUGIN_DIR . '/gwolle-gb/' . AKISMET_PHP4_CLASS_DIR . '/Akismet.class.php');
						$comment = array(
							'author' => $_POST['entry_author_name'],
							'email' => $_POST['entry_author_email'],
							'website' => $_POST['entry_author_website'],
							'body' => $_POST['entry_content'],
							'permalink' => get_bloginfo('url')
						); 
						$akismet = new Akismet(get_bloginfo('url'), $wordpressApiKey, $comment);
						
						if ($akismet->isSpam()) {
							//	Akismet detected spam.
							$isSpam = '1';
						}
					}
				}
				if (!$isSpam) {
					$isSpam = '0';
					if (get_option('gwolle_gb-moderate-entries') == 'false') {
						$isChecked = 1;
					}
				}
				
				$saveEntry_result = mysql_query("
					INSERT
					INTO
						" . $wpdb->prefix . "gwolle_gb_entries
					(
						entry_author_name,
						entry_author_email,
						entry_author_origin,
						entry_author_website,
						entry_author_ip,
						entry_author_host,
						entry_content,
						entry_date,
						entry_isSpam,
						entry_isChecked
					)
					VALUES
					(
						'" . mysql_real_escape_string($_POST['entry_author_name']) . "',
						'" . mysql_real_escape_string($_POST['entry_author_email']) . "',
						'" . mysql_real_escape_string($_POST['entry_author_origin']) . "',
						'" . mysql_real_escape_string($_POST['entry_author_website']) . "',
						'" . mysql_real_escape_string($_SERVER['REMOTE_ADDR']) . "',
						'" . mysql_real_escape_string(gethostbyaddr($_SERVER['REMOTE_ADDR'])) . "',
						'" . mysql_real_escape_string($_POST['entry_content']) . "',
						'" . mktime() . "',
						'" . $isSpam . "',
						'" . $isChecked . "'
					)
				");
				if (mysql_affected_rows() > 0) {
					//	Send an email to everyone who subscribed to the notification.
					$recipients_result = mysql_query("
						SELECT *
						FROM
							" . $wpdb->prefix . "options
						WHERE
							option_name LIKE 'gwolle_gb-notifyByMail-%'
					");
					while ($recipient = mysql_fetch_array($recipients_result)) {
						$userdata = get_userdata(str_replace('gwolle_gb-notifyByMail-','',$recipient['option_name']));
						if (($isSpam =='1' && get_option('gwolle_gb-notifyAll-' . $userdata->ID) == 'true') || $isSpam == '0') {
							$subscriber[]['user_email'] = $userdata->user_email;
						}
					}
					
					@ini_set('sendmail_from', get_bloginfo('admin_mail'));
				
					//	Set the mail content
					$mailTags = array('user_email','entry_management_url','blog_name','blog_url','wp_admin_url');
					$mail_body = stripslashes(get_option('gwolle_gb-adminMailContent'));
					if (!$mail_body) { $mail_body = $defaultMailText; }
					
					$subject = "[" . get_bloginfo('name') . "] New guestbook entry"; //subject
					$header = "From: Gwolle-Gb-Mailer <" . get_bloginfo('admin_email') . ">\r\n"; //optional headerfields
					
					$info['blog_name'] = get_bloginfo('name');
					$info['blog_url'] = get_bloginfo('url');
					$info['wp_admin_url'] = $info['blog_url'] . '/wp-admin';
					$info['entry_management_url'] = $info['wp_admin_url'] . '/admin.php?page=gwolle-gb/editor.php&entry_id=' . mysql_insert_id();
					//	The last tags are bloginfo-based
					for ($tagNum=1; $tagNum<count($mailTags); $tagNum++) {
						$mail_body = str_replace('%' . $mailTags[$tagNum] . '%',$info[$mailTags[$tagNum]],$mail_body);
					}
					
					for ($i=0; $i<count($subscriber); $i++) {
						$mailBody[$i] = $mail_body;
						$mailBody[$i] = str_replace('%user_email%',$subscriber[$i]['user_email'],$mailBody[$i]);
						
						wp_mail($subscriber[$i]['user_email'], $subject, $mailBody[$i], $header);
					}
					$msg = 'entry-saved';
				}
				else {
					$msg = 'error';
				}
			}
			
			$gb_link = gwolle_gb_formatGuestbookLink($_POST['gb_link']);
			
			header('Location: ' . $gb_link . 'msg=' . $msg);
			exit;
		}
	}
?>