<?php
	/*
	**	Handles spam/no spam-requests for guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $current_user;
	global $wpdb;
	
	//	get Wordpress API key
	$wordpressApiKey = get_option('wordpress_api_key');
	
	if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user's not allowed to do this.
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	elseif (!$wordpressApiKey || get_option('gwolle_gb-akismet-active') != 'true') {
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=check-akismet-configuration');
	}
	else {
		//	Check if the entry exists.
		$entryExists_result = mysql_query("
			SELECT *
			FROM
				" . $wpdb->prefix . "gwolle_gb_entries
			WHERE
				entry_id = '" . $_REQUEST['entry_id'] . "'
			LIMIT 1
		");
		if (mysql_num_rows($entryExists_result) == 0) {
			//	entry was not found
			header('Location: ' . get_bloginfo('url')  . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=entry-not-found');
			exit;
		}
		else {
			//	entry found; proceed!
			$entry = mysql_fetch_array($entryExists_result);
			if ($_REQUEST['action'] == 'markSpam' && $entry['entry_isSpam'] == '1') {
				$msg = 'already-marked-as-spam';
			}
			elseif ($_REQUEST['action'] == 'unmarkSpam' && $entry['entry_isSpam'] == '0') {
				$msg = 'not-marked-as-spam';
			}
			else {
				if (get_option('gwolle_gb-akismet-active')) {
					//	include the Akismet class and contruct a new akismet object
					if (version_compare(phpversion(),'5.0','>=')) {
						//	Use the PHP5 class
						include(WP_PLUGIN_DIR . '/gwolle-gb/' . AKISMET_PHP5_CLASS_DIR . '/Akismet.class.php');
							
						$blogURL = get_bloginfo('url');
		 				
		 				$akismet = new Akismet($blogURL, $wordpressApiKey);
		 				$akismet->setCommentAuthor($entry['entry_author_name']);
		 				$akismet->setCommentAuthorEmail($entry['entry_author_email']);
		 				$akismet->setCommentAuthorURL($entry['entry_author_website']);
		 				$akismet->setCommentContent($entry['entry_content']);
		 				$akismet->setPermalink($blogURL); //	what's this?
					}
					elseif (version_compare(phpversion(),'4.0','>=')) {
						//	Use the PHP4 class
						include(WP_PLUGIN_DIR . '/gwolle-gb/' . AKISMET_PHP4_CLASS_DIR . '/Akismet.class.php');
						$comment = array(
							'author' => $entry['entry_author_name'],
							'email' => $entry['entry_author_email'],
							'website' => $entry['entry_author_website'],
							'body' => $entry['entry_content'],
							'permalink' => get_bloginfo('url')
						); 
						$akismet = new Akismet(get_bloginfo('url'), $wordpressApiKey, $comment);
					}
				}
					
				if ($_REQUEST['action'] == 'unmarkSpam') {
					//	entry is no spam.
					$isSpam = '0';
					$log_subject = 'marked-as-not-spam';
					if ($akismet) { $akismet->submitHam(); }
					$msg = 'successfully-unmarkedSpam';
				}
				else {
					//	entry is spam
					$isSpam = '1';
					$log_subject = 'marked-as-spam';
					if ($akismet) { $akismet->submitSpam(); }
				}
				
				
				//	update the database
				mysql_query("
					UPDATE
						" . $wpdb->prefix . "gwolle_gb_entries
					SET
						" . $wpdb->prefix . "gwolle_gb_entries.entry_isSpam = '" . $isSpam . "'
					WHERE
						" . $wpdb->prefix . "gwolle_gb_entries.entry_id = '" . $entry['entry_id'] . "'
					LIMIT 1
				");
				
				//	insert a log entry
				$log_result = mysql_query("
					INSERT
					INTO
						" . $wpdb->prefix . "gwolle_gb_log
					(
						log_subject,
						log_subjectId,
						log_authorId,
						log_date
					)
					VALUES
					(
						'" . $log_subject . "',
						'" . $entry['entry_id'] . "',
						'" . $current_user->data->ID . "',
						'" . mktime() . "'
					)
				");

			}
			header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . $entry['entry_id'] . '&msg=' . $msg);
			exit;
		}
	}
?>