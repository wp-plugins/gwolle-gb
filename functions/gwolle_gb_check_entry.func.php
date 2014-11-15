<?php
/**
 * gwolle_gb_check_entry
 * Checks/unchecks an entry
 */
function gwolle_gb_check_entry($args = array()) {
	global $wpdb;
	global $current_user;

	if (!isset($args['entry_id']) || (int)$args['entry_id'] === 0) {
		return FALSE;
	}

	$entry_isChecked = (isset($args['uncheck']) && $args['uncheck'] === TRUE) ? 0 : 1;

	$sql = "
		UPDATE
			" . $wpdb->gwolle_gb_entries . "
		SET
			entry_isChecked = " . $entry_isChecked . ",
			entry_checkedBy = " . (int) $current_user -> data -> ID . "
		WHERE
			entry_id = " . (int) $args['entry_id'] . "
		LIMIT 1";
	$result = $wpdb->query($sql);
	if ($result == 1) {
		//  Write log entry
		$log = array();
		$log['subject'] = ($entry_isChecked === 1) ? 'entry-checked' : 'entry-unchecked';
		$log['subject_id'] = (int)$args['entry_id'];
		gwolle_gb_add_log_entry($log);
		return TRUE;
	}
	return FALSE;
}

