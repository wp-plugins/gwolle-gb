<?php
/**
 * gwolle_gb_import_dmsgb_entry
 * Function to import an entry from DMSGuestbook
 */
if (!function_exists('gwolle_gb_import_dmsgb_entry')) {
	function gwolle_gb_import_dmsgb_entry($entry) {
		global $wpdb;
		global $current_user;
		get_currentuserinfo();

		$isChecked = ($entry['flag'] == 1) ? 0 : 1;
		$isSpam = ($entry['spam'] == 1) ? 1 : 0;

		//  Insert into Gwolle-DB entry table
		// FIXME, use $wpdb->prepare
		$wpdb->query("
			  INSERT
			  INTO
			    " . $wpdb -> gwolle_gb_entries . "
			  (
			    entry_author_name,
			    entry_author_email,
			    entry_author_website,
			    entry_author_ip,
			    entry_content,
			    entry_date,
			    entry_isChecked,
			    entry_isSpam
			  ) VALUES (
			    '" . stripslashes($entry['name']) . "',
			    '" . stripslashes($entry['email']) . "',
			    '" . stripslashes($entry['url']) . "',
			    '" . $entry['ip'] . "',
			    '" . strip_tags(stripslashes($entry['message'])) . "',
			    '" . $entry['date'] . "',
			    " . $isChecked . ",
			    " . $isSpam . "
			  )");

		//  Create a log item for the import
		$wpdb->query("
			  INSERT
			  INTO
			    " . $wpdb -> gwolle_gb_log . "
			  (
			    log_subject,
			    log_subjectId,
			    log_authorId,
			    log_date
			  ) VALUES (
			    'imported-from-dmsguestbook',
			    " . $wpdb->insert_id . ",
			    " . $current_user -> ID . ",
			    '" . mktime() . "'
			  )
			  ");
	}

}
?>