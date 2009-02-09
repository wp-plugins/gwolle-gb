<?php
	/*
	**	Applies changes to guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $current_user;
	global $wpdb;
	
	if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user's not allowed to do this
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	else {
		//	The current user's allowed; proceed with saving the changes to the database.
		if (is_numeric($_REQUEST['entry_id'])) {
			//	Check if the entry exists
			$entryExists_result = mysql_query("
				SELECT *
				FROM
					" . $wpdb->prefix . "gwolle_gb_entries
				WHERE
					entry_id = '" . $_REQUEST['entry_id'] . "'
				LIMIT 1
			");
			if (mysql_num_rows($entryExists_result) == 0) {
				//	Entry does not exist; redirect to entry list and prompt with an error message.
				header('Location: ' . get_bloginfo('url')  . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=entry-not-found');
				exit;
			}
			else {
				$entry = mysql_fetch_array($entryExists_result);
				
				if (
					($_POST['entry_isChecked'] == 'on' && $entry['entry_isChecked'] == '0')
					||
					($_POST['entry_isChecked'] != 'on' && $entry['entry_isChecked'] == '1')
				) {
					//	Entry is supposed to be locked/unlocked.
					if ($_POST['entry_isChecked'] == 'on') {
						$isChecked = '1';
						$checkedBy = $current_user->data->ID;
						$log_subject = 'entry-checked';
					}
					else {
						$isChecked = '0';
						$checkedBy = '0';
						$log_subject = 'entry-unchecked';
					}
					
					$entryCheck_result = $wpdb->query("
						UPDATE
							" . $wpdb->prefix . "gwolle_gb_entries
						SET
							entry_isChecked = '" . $isChecked . "',
							entry_checkedBy = '" . $checkedBy . "'
						WHERE
							entry_id = '" . $_REQUEST['entry_id'] . "'
						LIMIT 1
					");
					
					//	Write a log entry on this.
					$log_result = $wpdb->query("
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
							'" . $_REQUEST['entry_id'] . "',
							'" . $current_user->data->ID . "',
							'" . mktime() . "'
						)
					");
				}
				$update_result = $wpdb->query($wpdb->prepare("
					UPDATE
						" . $wpdb->prefix . "gwolle_gb_entries
					SET
						entry_content = '" . mysql_real_escape_string($_POST['entry_content']) . "',
						entry_author_origin = '" . mysql_real_escape_string($_POST['entry_author_origin']) . "',
						entry_author_website = '" . mysql_real_escape_string($_POST['entry_author_website']) . "'
					WHERE
						entry_id = '" . $_REQUEST['entry_id'] . "'
					LIMIT 1
				"));
				header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . $_REQUEST['entry_id'] . '&updated=true');
				exit;
			}
		}
		else {
			//	No entry_id has been submitted; this is a new admin entry.
			if ($_POST['entry_isChecked'] == 'on') {
				$isChecked = '1';
				$checkedBy = $current_user->data->ID;
			}
			else {
				$isChecked = '0';
				$checkedBy = '0';
			}
			
			$save_result = mysql_query("
				INSERT
				INTO
					" . $wpdb->prefix . "gwolle_gb_entries
				(
					entry_authorAdminId,
					entry_author_website,
					entry_author_origin,
					entry_author_ip,
					entry_author_host,
					entry_content,
					entry_date,
					entry_isChecked,
					entry_checkedBy
				)
				VALUES
				(
					'" . $current_user->data->ID . "',
					'" . mysql_real_escape_string($_POST['entry_author_website']) . "',
					'" . mysql_real_escape_string($_POST['entry_author_origin']) . "',
					'" . $_SERVER['REMOTE_ADDR'] . "',
					'" . gethostbyaddr($_SERVER['REMOTE_ADDR']) . "',
					'" . mysql_real_escape_string($_POST['entry_content']) . "',
					'" . mktime() . "',
					'" . $isChecked . "',
					'" . $checkedBy . "'
				)
			");
			if (mysql_affected_rows() > 0) {
				$msg = 'updated=true';
			}
			else {
				$msg = 'error=true';
			}
			header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . mysql_insert_id() . '&' . $msg);
		}
	}
?>