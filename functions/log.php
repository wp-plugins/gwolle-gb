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
		'imported-from-wp',
		'imported-from-gwolle',
		'exported-to-csv',
		'entry-trashed',
		'entry-untrashed',
		'admin-reply-added',
		'admin-reply-updated',
		'admin-reply-removed'
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
			datetime
		) VALUES (
			%s,
			%d,
			%d,
			%d
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
 *   id           => (int) id
 *   subject      => (string) subject of the log, what happened
 *   author_id    => (int) author_id of the user responsible for this log entry
 *   datetime     => (int) log_date with timestamp
 *   msg          => (string) subject of the log, what happened. In Human Readable form, translated
 *   author_login => (string) display_name or login_name of the user as standard WP_User
 *   msg_html     => (string) string of html-text ready for displayed
 *
 */

function gwolle_gb_get_log_entries( $entry_id ) {
	global $wpdb;

	if ( !isset($entry_id) || (int) $entry_id === 0 ) {
		return false;
	}

	// Message to strings
	$log_messages = array(
		'entry-unchecked'             => __('Entry has been locked.',    'gwolle-gb'),
		'entry-checked'               => __('Entry has been checked.',   'gwolle-gb'),
		'marked-as-spam'              => __('Entry marked as spam.',     'gwolle-gb'),
		'marked-as-not-spam'          => __('Entry marked as not spam.', 'gwolle-gb'),
		'entry-edited'                => __('Entry has been edited.',    'gwolle-gb'),
		'imported-from-dmsguestbook'  => __('Imported from DMSGuestbook', 'gwolle-gb'),
		'imported-from-wp'            => __('Imported from WordPress comments', 'gwolle-gb'),
		'imported-from-gwolle'        => __('Imported from Gwolle-GB', 'gwolle-gb'),
		'exported-to-csv'             => __('Exported to CSV file', 'gwolle-gb'),
		'entry-trashed'               => __('Entry has been trashed.',   'gwolle-gb'),
		'entry-untrashed'             => __('Entry has been untrashed.', 'gwolle-gb'),
		'admin-reply-added'           => __('Admin reply has been added.', 'gwolle-gb'),
		'admin-reply-updated'         => __('Admin reply has been updated.', 'gwolle-gb'),
		'admin-reply-removed'         => __('Admin reply has been removed.', 'gwolle-gb')
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
				`datetime`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			ORDER BY
				datetime ASC
			;";

	$sql = $wpdb->prepare( $sql, $values );

	$entries = $wpdb->get_results( $sql, ARRAY_A );

	//$wpdb->print_error();
	//echo "number of rows: " . $wpdb->num_rows;

	if ( is_array($entries) && !empty($entries) ) {

		// Array to store the log entries
		$log_entries = array();

		foreach ( $entries as $entry ) {
			$log_entry = array(
				'id'        => (int) $entry['id'],
				'subject'   => stripslashes($entry['subject']),
				'entry_id'  => (int) $entry['entry_id'],
				'author_id' => (int) $entry['author_id'],
				'datetime'  => (int) $entry['datetime']
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
				$log_entry['author_login'] = '<i>' . __('Unknown', 'gwolle-gb') . '</i>';
			}

			// Construct the message in HTML
			$log_entry['msg_html']  = date_i18n( get_option('date_format'), $log_entry['datetime']) . ", ";
			$log_entry['msg_html'] .= date_i18n( get_option('time_format'), $log_entry['datetime']);
			$log_entry['msg_html'] .= ': ' . $log_entry['msg'];

			if ( $log_entry['author_id'] == get_current_user_id() ) {
				$log_entry['msg_html'] .= ' (<strong>' . __('You', 'gwolle-gb') . '</strong>)';
			} else {
				$log_entry['msg_html'] .= ' (' . $log_entry['author_login'] . ')';
			}

			$log_entries[] = $log_entry;
		}

		return $log_entries;
	}
	return false;
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



