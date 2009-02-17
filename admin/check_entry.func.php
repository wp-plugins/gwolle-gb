<?php
	/*
	**	Function to check/uncheck an entry.
	*/
	
	function check_gwolle_gb_entry($entry_id, $newCheckedStatus='checked') {
		global $wpdb;
		global $current_user;
		
		//	Let's first check if there's anything to do. Maybe the entry already has the $newCheckedStatus.
		if ($newCheckedStatus == 'checked') { $isChecked = '1'; } else { $isChecked = '0'; }
		$currentStatus_result = mysql_query("
			SELECT *
			FROM
				" . $wpdb->prefix . "gwolle_gb_entries
			WHERE
				entry_id = '" . $entry_id . "'
				AND
				entry_isChecked = '" . $isChecked . "'
		");
		if (mysql_num_rows($currentStatus_result) == 1) {
			//	The entry already has the status we want to apply. Do not proceed here.
			return false;
		}
		else {
			//	Let's apply the new status.
			if ($newCheckedStatus == 'checked') {
				$checkedBy = $current_user->data->ID;
				$log_subject = 'entry-checked';
			}
			else {
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
					entry_id = '" . $entry_id . "'
				LIMIT 1
			");
			
			if (mysql_affected_rows() == 1) {
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
						'" . $entry_id . "',
						'" . $current_user->data->ID . "',
						'" . mktime() . "'
					)
				");
				if (mysql_affected_rows() == 1) {
					return true;
				}
			}
		}
	}
?>