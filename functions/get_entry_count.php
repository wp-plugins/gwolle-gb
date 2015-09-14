<?php
/*
 * gwolle_gb_get_entry_count
 * Get the number of entries.
 *
 * Parameter $args is an Array:
 * - checked  string: 'checked' or 'unchecked', List the entries that are checked or not checked
 * - trash    string: 'trash' or 'notrash', List the entries that are deleted or not deleted
 * - spam     string: 'spam' or 'nospam', List the entries marked as spam or as no spam
 * - all      string: 'all', List all entries
 * - book_id  int: Only entries from this book. Default in the shortcode is 1 (since 1.5.1).
 *
 * Return:
 * - int with the count of the entries
 * - false if there's an error.
 */

function gwolle_gb_get_entry_count($args) {

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
	if ( isset( $args['book_id']) ) {
		$where .= "
			AND
			book_id = %d";
		$values[] = (int) $args['book_id'];
	}

	$tablename = $wpdb->prefix . "gwolle_gb_entries";

	$sql = "
			SELECT
				COUNT(id) AS count
			FROM
				" . $tablename . "
			WHERE
				" . $where . "
			;";

	// If All is set, do not use $wpdb->prepare()
	if ( isset($args['all']) ) {
		if ( $args['all'] == 'all' ) {
			$sql = "
				SELECT
					COUNT(id) AS count
				FROM
					" . $tablename . ";";
		}
	} else {
		$sql = $wpdb->prepare( $sql, $values );
	}

	$data = $wpdb->get_results( $sql, ARRAY_A );

	return (int) $data[0]['count'];

}

