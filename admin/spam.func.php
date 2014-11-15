<?php
/*
 * This function handles spam/no spam-requests.
 */

function spam_gwolle_gb_entry($entry_id, $markAs = 'spam', $doRedirect = false, $redirectToShow = 'all') {
	global $current_user;
	global $wpdb;

	// get WordPress API key.
	$wordpressApiKey = get_option('wordpress_api_key');

	if (!current_user_can('moderate_comments')) {
		// The current user's not allowed to do this.
		die(__('Cheatin&#8217; uh?'));
	} elseif (!$wordpressApiKey) {
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=check-akismet-configuration');
	} elseif (get_option('gwolle_gb-akismet-active') != 'true') {
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=akismet-not-activated');
	} else {
		//	Check if the entry exists.
		$entryExists_result = $wpdb->get_results("
				SELECT *
				FROM
					" . $wpdb->prefix . "gwolle_gb_entries
				WHERE
					entry_id = '" . $entry_id . "'
				LIMIT 1
			");
var_dump($entryExists_result); // Test, maar waar en hoe?
		if (count($entryExists_result) == 0) {
			//	entry was not found
			if ($doRedirect) {
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&show=' . $redirectToShow . '&msg=entry-not-found');
				exit ;
			} else {
				//	No redirect; just return false.
				$success = false;
			}
		} else {
			//	entry found; proceed!
			$entry = $entryExists_result;
			if ($markAs == 'spam' && $entry['entry_isSpam'] == '1') {
				$msg = 'already-marked-as-spam';
			} elseif ($markAs == 'no-spam' && $entry['entry_isSpam'] == '0') {
				$msg = 'not-marked-as-spam';
			} else {
				if (get_option('gwolle_gb-akismet-active')) {
					// Contruct a new akismet object
					$blogURL = get_bloginfo('wpurl');
					if (class_exists('Akismet')) {
						$akismet = new Akismet($blogURL, $wordpressApiKey, $entry);
					}
				}
				if ($markAs == 'no-spam') {
					//	entry is no spam.
					$isSpam = '0';
					$log_subject = 'marked-as-not-spam';
					if ($akismet) {
						// $akismet -> submitHam(); // FIXME
					}
					$msg = 'successfully-unmarkedSpam';
				} else {
					//	entry is spam
					$isSpam = '1';
					$log_subject = 'marked-as-spam';
					if ($akismet) {
						// $akismet -> submitSpam(); // FIXME
					}
				}

				//	update the database
				if ($isSpam == '1') {
					$wpdb->query("
							UPDATE
								" . $wpdb -> prefix . "gwolle_gb_entries
							SET
								" . $wpdb -> prefix . "gwolle_gb_entries.entry_isSpam = '" . $isSpam . "',
								" . $wpdb -> prefix . "gwolle_gb_entries.entry_isChecked = '0'
							WHERE
								" . $wpdb -> prefix . "gwolle_gb_entries.entry_id = '" . $entry_id . "'
							LIMIT 1
						");
					$success = true;
				} else {
					$wpdb->query("
							UPDATE
								" . $wpdb -> prefix . "gwolle_gb_entries
							SET
								" . $wpdb -> prefix . "gwolle_gb_entries.entry_isSpam = '" . $isSpam . "'
							WHERE
								" . $wpdb -> prefix . "gwolle_gb_entries.entry_id = '" . $entry_id . "'
							LIMIT 1
						");
					$success = true;
				}

				//	insert a log entry
				$log_result = $wpdb->query("
						INSERT
						INTO
							" . $wpdb -> prefix . "gwolle_gb_log
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
							'" . $current_user -> data -> ID . "',
							'" . mktime() . "'
						)
					");

			}
			if ($doRedirect) {
				if ($redirectToShow == 'editor') {
					header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . $entry['entry_id'] . '&msg=' . $msg);
					exit ;
				} else {
					header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&show=' . $redirectToShow . '&msg=' . $msg);
				}
			} else {
				return $success;
			}
		}
	}
}
?>