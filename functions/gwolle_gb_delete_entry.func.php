<?php
	/*
	 * gwolle_gb_delete_entry
	 * Deletes an entry from the database.
	 * Returns:
	 * - FALSE      if any errors occur
	 * - TRUE       if no errors occur
	 */

if (!function_exists('gwolle_gb_delete_entry')) {
	function gwolle_gb_delete_entry($args = array()) {
		global $wpdb;
		global $current_user;
		global $gwolle_gb_settings;

		// Load settings, if not set
		if (!isset($gwolle_gb_settings)) {
			gwolle_gb_get_settings();
		}

		//  We need the old entry data as an argument.
		if (!isset($args['entry_id'])) {
			return FALSE;
		}

		$sql = "
			DELETE
			FROM
				" . $wpdb -> gwolle_gb_entries . "
			WHERE
				entry_id = " . (int)$args['entry_id'] . "
			LIMIT 1";
		$result = $wpdb->query($sql);
		if ($result == 1) {
			return TRUE;
		}
		return FALSE;
	}

}
