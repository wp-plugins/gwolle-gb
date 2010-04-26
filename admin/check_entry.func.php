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
				" . $wpdb->prefix . "gwolle_gb_entries e
			WHERE
				e.entry_id = '" . $entry_id . "'
				AND
				e.entry_isChecked = '" . $isChecked . "'
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
			
			if (mysql_affected_rows() === 1) {
				//	Write a log entry on this.
				include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_add_log_entry.func.php');
				return gwolle_gb_add_log_entry(array(
				  'subject'     => $log_subject,
				  'subject_id'  => $entry_id
				));
			}
		}
	}
?>