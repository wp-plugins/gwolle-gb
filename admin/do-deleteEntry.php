<?php
	/*
	**	Deletes guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $current_user;
	global $wpdb;
	
	if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user has no rights to access to this
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	else {
		$delete_result = mysql_query("
			UPDATE
				" . $wpdb->prefix . "gwolle_gb_entries
			SET
				" . $wpdb->prefix . "gwolle_gb_entries.entry_isDeleted = '1'
			WHERE
				" . $wpdb->prefix . "gwolle_gb_entries.entry_id = '" . $_REQUEST['entry_id'] . "'
			LIMIT 1
		");
		if (mysql_affected_rows() > 0) {
			//	Add this action to log
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
					'entry-deleted',
					'" . $_REQUEST['entry_id'] . "',
					'" . $current_user->data->ID . "',
					'" . mktime() . "'
				)
			");
			$msg = 'deleted';
		}
		else {
			$msg = 'error-deleting';
		}
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=' . $msg);
		exit;
	}
?>