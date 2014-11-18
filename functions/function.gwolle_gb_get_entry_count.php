<?php
/*
 * gwolle_gb_get_entry_count
 * Get the number of entries.
 *
 * Parameter $args is an Array:
 * - checked			string: 'checked' or 'unchecked', List the entries that are checked or not checked
 * - deleted			string: 'deleted' or 'notdeleted', List the entries that are deleted or not deleted
 * - spam				string: 'spam' or 'nospam', List the entries marked as spam or as no spam
 * - all				string: 'all', List all entries
 *
 * Return:
 * - Array of objects of gwolle_gb_entry
 * - false  if there's an error.
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

	$tablename = $wpdb->prefix . "gwolle_gb_entries";

	$sql = "
			SELECT
				COUNT(entry_id) AS entry_count
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
					COUNT(entry_id) AS entry_count
				FROM
					" . $tablename . ";";
		}
	} else {
		$sql = $wpdb->prepare( $sql, $values );
	}

	$data = $wpdb->get_results( $sql, ARRAY_A );

	return (int) $data[0]['entry_count'];

}

