<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_frontend_read
 * Reading mode of the guestbook frontend
 */

function gwolle_gb_frontend_read( $shortcode_atts ) {

	$output = '';

	$entriesPerPage = (int) get_option('gwolle_gb-entriesPerPage', 20);

	$entriesCount = gwolle_gb_get_entry_count(
		array(
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam',
			'book_id' => $shortcode_atts['book_id']
		)
	);

	$countPages = ceil( $entriesCount / $entriesPerPage );

	$pageNum = 1;
	if ( isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) ) {
		$pageNum = intval($_GET['pageNum']);
	}

	if ( $pageNum > $countPages ) {
		// Page doesnot exist
		$pageNum = 1;
	}

	if ($pageNum == 1 && $entriesCount > 0) {
		$firstEntryNum = 1;
		$mysqlFirstRow = 0;
	} elseif ($entriesCount == 0) {
		$firstEntryNum = 0;
		$mysqlFirstRow = 0;
	} else {
		$firstEntryNum = ($pageNum - 1) * $entriesPerPage + 1;
		$mysqlFirstRow = $firstEntryNum - 1;
	}


	/* Get the entries for the frontend */
	if ( isset($_GET['show_all']) && $_GET['show_all'] == 'true' ) {
		$entries = gwolle_gb_get_entries(
			array(
				'offset'      => 0,
				'num_entries' => -1,
				'checked'     => 'checked',
				'trash'       => 'notrash',
				'spam'        => 'nospam',
				'book_id'     => $shortcode_atts['book_id']
			)
		);
		$pageNum = 0; // do not have it set to 1, this way the '1' will be clickable too.
	} else {
		$entries = gwolle_gb_get_entries(
			array(
				'offset'      => $mysqlFirstRow,
				'num_entries' => $entriesPerPage,
				'checked'     => 'checked',
				'trash'       => 'notrash',
				'spam'        => 'nospam',
				'book_id'     => $shortcode_atts['book_id']
			)
		);
	}


	/* Page navigation on top */
	$pagination = gwolle_gb_pagination_frontend( $pageNum, $countPages );
	$output .= $pagination;


	/* Entries from the template */
	if ( !is_array($entries) || empty($entries) ) {
		$output .= __('(no entries yet)', 'gwolle-gb');
	} else {
		$first = true;

		$output .= '<div id="gwolle_gb_entries">';

		// Try to load and require_once the template from the themes folders.
		if ( locate_template( array('gwolle_gb-entry.php'), true, true ) == '') {

			$output .= '<!-- Gwolle-GB Entry: Default Template Loaded -->
				';

			// No template found and loaded in the theme folders.
			// Load the template from the plugin folder.
			require_once('gwolle_gb-entry.php');

		} else {

			$output .= '<!-- Gwolle-GB Entry: Custom Template Loaded -->
				';

		}

		$counter = 0;
		foreach ($entries as $entry) {
			$counter++;

			// Run the function from the template to get the entry.
			$entry_output = gwolle_gb_entry_template( $entry, $first, $counter );

			$first = false;

			// Add a filter for each entry, so devs can add or remove parts.
			$output .= apply_filters( 'gwolle_gb_entry_read', $entry_output, $entry );

		}

		$output .= '</div>';

	}


	/* Page navigation on bottom */
	$output .= $pagination;


	// Add filter for the complete output.
	$output = apply_filters( 'gwolle_gb_entries_read', $output);

	return $output;
}

