<?php

/*
 * gwolle_gb_get_entries
 * Function to get guestbook entries from the database.
 *
 * Parameter $args is an Array:
 * - num_entries  int: Number of requested entries
 * - offset       int: Start after this entry
 * - checked      string: 'checked' or 'unchecked', List the entries that are checked or not checked
 * - trash        string: 'trash' or 'notrash', List the entries that are deleted or not deleted
 * - spam         string: 'spam' or 'nospam', List the entries marked as spam or as no spam
 * - email        string: The emailaddress to search for
 *
 * Return:
 * - Array of objects of gwolle_gb_entry
 * - false if no entries found.
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
				ischecked = %d";
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
				isspam = %d";
			if ( $args['spam'] == 'spam' ) {
				$values[] = 1;
			} else if ( $args['spam'] == 'nospam' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['trash']) ) {
		if ( $args['trash'] == 'trash' || $args['trash'] == 'notrash' ) {
			$where .= "
				AND
				istrash = %d";
			if ( $args['trash'] == 'trash' ) {
				$values[] = 1;
			} else if ( $args['trash'] == 'notrash' ) {
				$values[] = 0;
			}
		}
	}
	if ( isset($args['email']) ) {
		$where .= "
			AND
			author_email = %s";
		$values[] = $args['email'];
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
				`id`,
				`author_name`,
				`author_id`,
				`author_email`,
				`author_origin`,
				`author_website`,
				`author_ip`,
				`author_host`,
				`content`,
				`date`,
				`ischecked`,
				`checkedby`,
				`istrash`,
				`isspam`
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			ORDER BY
				date DESC
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
				'id' => (int) $data['id'],
				'author_name' => stripslashes($data['author_name']),
				'author_id' => (int) $data['author_id'],
				'author_email' => stripslashes($data['author_email']),
				'author_origin' => stripslashes($data['author_origin']),
				'author_website' => stripslashes($data['author_website']),
				'author_ip' => $data['author_ip'],
				'author_host' => $data['author_host'],
				'content' => stripslashes($data['content']),
				'date' => $data['date'],
				'ischecked' => (int) $data['ischecked'],
				'checkedby' => (int) $data['checkedby'],
				'istrash' => (int) $data['istrash'],
				'isspam' => (int) $data['isspam']
			);

			$entry = new gwolle_gb_entry();

			$entry->set_data( $item );

			// Add entry to the array of all entries
			$entries[] = $entry;
		}
		return $entries;
	}
	return false;
}




