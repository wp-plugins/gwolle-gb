<?php
	/*
	**	Function to delete guestbook entries.
	*/
	
	function delete_gwolle_gb_entry($entry_id,$doRedirect=false,$redirectToShow='all') {
		global $current_user;
		global $wpdb;
		
		if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			//	The current user has no rights to access to this
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
			exit;
		}
		else {
			$delete_result = mysql_query("
				UPDATE
					" . $wpdb->prefix . "gwolle_gb_entries
				SET
					" . $wpdb->prefix . "gwolle_gb_entries.entry_isDeleted = '1'
				WHERE
					" . $wpdb->prefix . "gwolle_gb_entries.entry_id = '" . $entry_id . "'
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
						'" . $entry_id . "',
						'" . $current_user->data->ID . "',
						'" . mktime() . "'
					)
				");
				$msg = 'deleted';
				$success = true;
			}
			else {
				$msg = 'error-deleting';
			}
			
			//	Only redirect if $doRedirect = true
			if ($doRedirect) {
				if ($redirectToShow) {
					$show = '&show=' . $redirectToShow;
				}
				elseif ($_POST['show']) {
					//	It seems as if the user has been viewing a specific type of entries. Redirect him to the corresponding page.
					$show = '&show=' . $_POST['show'];
				}
				
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=' . $msg . $show);
				exit;
			}
			else {
				//	Don't redirect; just return success
				return $success;
			}
		}
	}
?>