<?php

/*
 * gwolle_gb_add_log_entry()
 * Add a new log entry
 *
 * Parameters:
 *   - entry_id: (int)    the id of the entry
 *   - subject:  (string) one of the possible log_messages
 *
 * Return: (bool) true or false, depending on succes
 */

function gwolle_gb_add_log_entry( $entry_id, $subject ) {
	global $wpdb;

	if ( !isset($subject) || !isset($entry_id) || (int) $entry_id === 0 ) {
		return false;
	}

	$log_messages = array(
		'entry-unchecked',
		'entry-checked',
		'marked-as-spam',
		'marked-as-not-spam',
		'entry-edited',
		'imported-from-dmsguestbook',
		'entry-trashed',
		'entry-untrashed'
	);
	if ( !in_array( $subject, $log_messages ) ) {
		return false;
	}

	$result = $wpdb->query( $wpdb->prepare(
		"
		INSERT INTO $wpdb->gwolle_gb_log
		(
			subject,
			entry_id,
			author_id,
			date
		) VALUES (
			%s,
			%d,
			%d,
			%s
		)
		",
		array(
			addslashes( $subject ),
			intval( $entry_id ),
			intval( get_current_user_id() ),
			current_time( 'timestamp' )
		)
	) );

	if ($result == 1) {
		return true;
	}
	return false;
}


/*
 * gwolle_gb_get_log_entries
 * Function to get log entries.
 *
 * Parameter: (string) $entry_id: the id of the guestbook entry where the log belongs to
 *
 * Return: Array with log_entries, each is an Array:
 * id           => (int) id
 * subject      => (string) subject of the log, what happened
 * author_id    => (int) author_id of the user responsible for this log entry
 * date         => (string) log_date with timestamp
 * msg          => (string) subject of the log, what happened. In Human Readable form, translated
 * author_login => (string) display_name or login_name of the user as standard WP_User
 * msg_html     => (string) string of html-text ready for displayed
 *
 */

function gwolle_gb_get_log_entries( $entry_id ) {
	global $wpdb;

	if ( !isset($entry_id) || (int) $entry_id === 0 ) {
		return false;
	}

	//  Message to strings
	$log_messages = array(
		'entry-unchecked'             => __('Entry has been locked.',    GWOLLE_GB_TEXTDOMAIN),
		'entry-checked'               => __('Entry has been checked.',   GWOLLE_GB_TEXTDOMAIN),
		'marked-as-spam'              => __('Entry marked as spam.',     GWOLLE_GB_TEXTDOMAIN),
		'marked-as-not-spam'          => __('Entry marked as not spam.', GWOLLE_GB_TEXTDOMAIN),
		'entry-edited'                => __('Entry has been edited.',    GWOLLE_GB_TEXTDOMAIN),
		'imported-from-dmsguestbook'  => __('Imported from DMSGuestbook', GWOLLE_GB_TEXTDOMAIN),
		'entry-trashed'               => __('Entry has been trashed.',   GWOLLE_GB_TEXTDOMAIN),
		'entry-untrashed'             => __('Entry has been untrashed.', GWOLLE_GB_TEXTDOMAIN)
	);

	$where = " 1 = %d";
	$values = Array(1);
	$tablename = $wpdb->prefix . "gwolle_gb_log";

	$where .= "
		AND
			entry_id = %d";

	$values[] = $entry_id;

	$sql = "
			SELECT
				`id`,
				`subject`,
				`entry_id`,
				`author_id`,
				`date`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			ORDER BY
				date ASC
			;";

	$sql = $wpdb->prepare( $sql, $values );

	$entries = $wpdb->get_results( $sql, ARRAY_A );

	//$wpdb->print_error();
	//echo "number of rows: " . $wpdb->num_rows;

	if ( count($entries) == 0 ) {
		return false;
	}


	// Array to store the log entries
	$log_entries = array();

	foreach ( $entries as $entry ) {
		$log_entry = array(
			'id'        => (int) $entry['id'],
			'subject'   => stripslashes($entry['subject']),
			'entry_id'  => (int) $entry['entry_id'],
			'author_id' => (int) $entry['author_id'],
			'date'      => stripslashes($entry['date'])
		);

		$log_entry['msg'] = (isset($log_messages[$log_entry['subject']])) ? $log_messages[$log_entry['subject']] : $log_entry['subject'];

		// Get author's display name or login name if not already done.
		$userdata = get_userdata( $log_entry['author_id'] );
		if (is_object($userdata)) {
			if ( isset( $userdata->display_name ) ) {
				$log_entry['author_login'] = $userdata->display_name;
			} else {
				$log_entry['author_login'] = $userdata->user_login;
			}
		} else {
			$log_entry['author_login'] = '<i>' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . '</i>';
		}

		// Construct the message in HTML
		$log_entry['msg_html']  = date_i18n( get_option('date_format'), $log_entry['date']) . ", ";
		$log_entry['msg_html'] .= date_i18n( get_option('time_format'), $log_entry['date']);
		$log_entry['msg_html'] .= ': ' . $log_entry['msg'];

		if ( $log_entry['author_id'] == get_current_user_id() ) {
			$log_entry['msg_html'] .= ' (<strong>' . __('You', GWOLLE_GB_TEXTDOMAIN) . '</strong>)';
		} else {
			$log_entry['msg_html'] .= ' (' . $log_entry['author_login'] . ')';
		}

		$log_entries[] = $log_entry;
	}

	return $log_entries;
}


/*
 * gwolle_gb_del_log_entries()
 * Delete the log entries for a guestbook entry
 *
 * Parameters:
 *   - entry_id: (int) the id of the entry
 *
 * Return: (bool) true or false, depending on succes
 */

function gwolle_gb_del_log_entries( $entry_id ) {
		global $wpdb;

		$entry_id = intval( $entry_id );

		if ( $entry_id == 0 || $entry_id < 0 ) {
			return false;
		}

		$sql = "
			DELETE
			FROM
				$wpdb->gwolle_gb_log
			WHERE
				entry_id = %d";

		$values = array(
				$entry_id
			);

		$result = $wpdb->query(
				$wpdb->prepare( $sql, $values )
			);


		if ( $result > 0 ) {
			return true;
		}
		return false;

}



