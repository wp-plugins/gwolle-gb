<?php
	/*
	**	This function handles spam/no spam-requests.
	*/
	
	function spam_gwolle_gb_entry($entry_id,$markAs='spam',$doRedirect=false,$redirectToShow='all') {
		global $current_user;
		global $wpdb;
		
		//	get Wordpress API key
		$wordpressApiKey = get_option('wordpress_api_key');
		
		if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			//	The current user's not allowed to do this.
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
			exit;
		}
		elseif (!$wordpressApiKey) {
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=check-akismet-configuration');
		}
		elseif (get_option('gwolle_gb-akismet-active') != 'true') {
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=akismet-not-activated');
		}
		else {
			//	Check if the entry exists.
			$entryExists_result = mysql_query("
				SELECT *
				FROM
					" . $wpdb->prefix . "gwolle_gb_entries
				WHERE
					entry_id = '" . $entry_id . "'
				LIMIT 1
			");
			if (mysql_num_rows($entryExists_result) == 0) {
				//	entry was not found
				if ($doRedirect) {
					header('Location: ' . get_bloginfo('wpurl')  . '/wp-admin/admin.php?page=gwolle-gb/entries.php&show=' . $redirectToShow . '&msg=entry-not-found');
					exit;
				}
				else {
					//	No redirect; just return false.
					$success = false;
				}
			}
			else {
				//	entry found; proceed!
				$entry = mysql_fetch_array($entryExists_result);
				if ($markAs == 'spam' && $entry['entry_isSpam'] == '1') {
					$msg = 'already-marked-as-spam';
				}
				elseif ($markAs == 'no-spam' && $entry['entry_isSpam'] == '0') {
					$msg = 'not-marked-as-spam';
				}
				else {
					if (get_option('gwolle_gb-akismet-active')) {
						//	include the Akismet class and contruct a new akismet object
						$blogURL = get_bloginfo('wpurl');
						if (version_compare(phpversion(),'5.0','>=')) {	//	Use the PHP5 class
						
							if (!class_exists('Akismet')) {
								//	Only include if the Akismet class doesn't already exist.
								include(WP_PLUGIN_DIR . '/gwolle-gb/' . AKISMET_PHP5_CLASS_DIR . '/Akismet.class.php');
							}
			 				
			 				$akismet = new Akismet($blogURL, $wordpressApiKey);
			 				$akismet->setCommentAuthor($entry['entry_author_name']);
			 				$akismet->setCommentAuthorEmail($entry['entry_author_email']);
			 				$akismet->setCommentAuthorURL($entry['entry_author_website']);
			 				$akismet->setCommentContent($entry['entry_content']);
			 				$akismet->setPermalink($blogURL); //	what's this?
						}
						elseif (version_compare(phpversion(),'4.0','>=')) {	//	Use the PHP4 class
							if (!class_exists('Akismet')) {
								//	Only include if the Akismet class doesn't already exist.
								include(WP_PLUGIN_DIR . '/gwolle-gb/' . AKISMET_PHP4_CLASS_DIR . '/Akismet.class.php');
							}
							$comment = array(
								'author' => $entry['entry_author_name'],
								'email' => $entry['entry_author_email'],
								'website' => $entry['entry_author_website'],
								'body' => $entry['entry_content'],
								'permalink' => get_bloginfo('wpurl')
							); 
							$akismet = new Akismet(get_bloginfo('wpurl'), $wordpressApiKey, $comment);
						}
					}
						
					if ($markAs == 'no-spam') {
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
					if ($isSpam == '1') {
						mysql_query("
							UPDATE
								" . $wpdb->prefix . "gwolle_gb_entries
							SET
								" . $wpdb->prefix . "gwolle_gb_entries.entry_isSpam = '" . $isSpam . "',
								" . $wpdb->prefix . "gwolle_gb_entries.entry_isChecked = '0'
							WHERE
								" . $wpdb->prefix . "gwolle_gb_entries.entry_id = '" . $entry_id . "'
							LIMIT 1
						");
						$success = true;
					}
					else {
						mysql_query("
							UPDATE
								" . $wpdb->prefix . "gwolle_gb_entries
							SET
								" . $wpdb->prefix . "gwolle_gb_entries.entry_isSpam = '" . $isSpam . "'
							WHERE
								" . $wpdb->prefix . "gwolle_gb_entries.entry_id = '" . $entry_id . "'
							LIMIT 1
						");
						$success = true;
					}
					
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
				if ($doRedirect) {
					if ($redirectToShow == 'editor') {
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . $entry['entry_id'] . '&msg=' . $msg);
						exit;
					}
					else {
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&show=' . $redirectToShow . '&msg=' . $msg);
					}
				}
				else {
					return $success;
				}
			}
		}
	}
?>