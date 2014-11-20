<?php

/*
 * gwolle_gb_get_entries
 * Function to get guestbook entries from the database.
 *
 * Parameter $args is an Array:
 * - num_entries		int: Number of requested entries
 * - offset				int: Start after this entry
 * - checked			string: 'checked' or 'unchecked', List the entries that are checked or not checked
 * - deleted			string: 'deleted' or 'notdeleted', List the entries that are deleted or not deleted
 * - spam				string: 'spam' or 'nospam', List the entries marked as spam or as no spam
 * - email				string: The emailaddress to search for
 * - entry_id			int: Show this single entry  FIXME: should this still be here? Why not just use new and set_data()
 *
 * Return:
 * - Array of objects of gwolle_gb_entry
 * - false  if no entries found.
 */

function gwolle_gb_get_entries($args = array()) {
	global $wpdb;

	$where = " 1 = %d";
	$values = Array(1);

	if ( !is_array($args) ) {
		return false;
	}

	if ( isset($args['checked']) ) {
		if ( $args['checked'] == 'checked' || $args['checked'] == 'unchecked' ) {
			$where .= "
				AND
				entry_isChecked = %d";
			if ( $args['checked'] == 'checked' ) {
				$values[] = 1;
			} else if ( $args['checked'] == 'unchecked' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['spam']) ) {
		if ( $args['spam'] == 'spam' || $args['spam'] == 'nospam' ) {
			$where .= "
				AND
				entry_isSpam = %d";
			if ( $args['spam'] == 'spam' ) {
				$values[] = 1;
			} else if ( $args['spam'] == 'nospam' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['deleted']) ) {
		if ( $args['deleted'] == 'deleted' || $args['deleted'] == 'notdeleted' ) {
			$where .= "
				AND
				entry_isDeleted = %d";
			if ( $args['deleted'] == 'deleted' ) {
				$values[] = 1;
			} else if ( $args['deleted'] == 'notdeleted' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['email']) ) {
		$where .= "
			AND
			entry_author_email = %s";
		$values[] = $args['email'];
	}
	if (isset($args['entry_id'])) {
		if ((int) $args['entry_id'] > 0) {
			$where .= "
				AND
				entry_id = %d";
			$values[] = $args['entry_id'];
		} else {
			return false;
		}
	}
	// Limit
	if ( is_admin() ) {
		$perpage_option = (int) get_option('gwolle_gb-entries_per_page', 20);
	} else {
		$perpage_option = (int) get_option('gwolle_gb-entriesPerPage', 20);
	}
	$num_entries = (isset($args['num_entries']) && (int)$args['num_entries'] > 0) ? (int)$args['num_entries'] : $perpage_option;

	if ( isset($args['offset']) && (int) $args['offset'] > 0 ) {
		$limit = $args['offset'] . ", " . $num_entries;
	} else {
		$limit = "0, " . $num_entries;
	}


	$tablename = $wpdb->prefix . "gwolle_gb_entries";

	$sql = "
			SELECT
				*
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			ORDER BY
				entry_date DESC
			LIMIT
				" . $limit . "
			;";

	$sql = $wpdb->prepare( $sql, $values );

	$datalist = $wpdb->get_results( $sql, ARRAY_A );

	//$wpdb->print_error();

	//echo "number of rows: " . $wpdb->num_rows;

	if ( count( $datalist ) == 0 ) {
		return false;
	} else {
		$entries = array();

		foreach ( $datalist as $data ) {

			// Use the fields that the setter method expects
			$item = array(
				'id' => (int) $data['entry_id'],
				'author_name' => stripslashes($data['entry_author_name']),
				'authoradminid' => (int) $data['entry_authorAdminId'],
				'author_email' => stripslashes($data['entry_author_email']),
				'author_origin' => stripslashes($data['entry_author_origin']),
				'author_website' => stripslashes($data['entry_author_website']),
				'author_ip' => $data['entry_author_ip'],
				'author_host' => $data['entry_author_host'],
				'content' => stripslashes($data['entry_content']),
				'date' => $data['entry_date'],
				'ischecked' => (int) $data['entry_isChecked'],
				'checkedby' => (int) $data['entry_checkedBy'],
				'isdeleted' => (int) $data['entry_isDeleted'],
				'isspam' => (int) $data['entry_isSpam']
			);

			$entry = new gwolle_gb_entry();

			$entry->set_data( $item );

			// Add entry to the array of all entries
			$entries[] = $entry;
		}

		if (isset($args['entry_id'])) {
			// Just return one entry
			return $entries[0];
		} else {
			return $entries;
		}
	}
	return false;
}




