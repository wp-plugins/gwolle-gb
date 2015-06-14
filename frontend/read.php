<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_frontend_read
 * Reading mode of the guestbook frontend
 */

function gwolle_gb_frontend_read() {

	$output = '';

	$permalink = get_permalink(get_the_ID());

	$entriesPerPage = (int) get_option('gwolle_gb-entriesPerPage', 20);

	$entriesCount = gwolle_gb_get_entry_count(
		array(
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
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

	$lastEntryNum = $pageNum * $entriesPerPage;
	if ($entriesCount == 0) {
		$lastEntryNum = 0;
	} elseif ($lastEntryNum > $entriesCount) {
		$lastEntryNum = $firstEntryNum + ($entriesCount - ($pageNum - 1) * $entriesPerPage) - 1;
	}

	/* Make an optional extra page, with a Get-parameter like show_all=true, which shows all the entries.
	 * This would need a settings option, which is off by default.
	 * https://wordpress.org/support/topic/show-all-posts-6?replies=1
	 */

	/* Get the entries for the frontend */
	$entries = gwolle_gb_get_entries(
		array(
			'offset'      => $mysqlFirstRow,
			'num_entries' => $entriesPerPage,
			'checked'     => 'checked',
			'trash'       => 'notrash',
			'spam'        => 'nospam'
		)
	);

	/* Page navigation */
	$pagination = '<div class="page-navigation">';
	if ($pageNum > 1) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum - 1), $permalink ) . '" title="' . __('Previous page', GWOLLE_GB_TEXTDOMAIN) . '">&laquo;</a>';
	}
	if ($pageNum < 5) {
		if ($countPages < 5) {
			$showRange = $countPages;
		} else {
			$showRange = 5;
		}

		for ($i = 1; $i < ($showRange + 1); $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span>' . $i . '</span>';
			} else {
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $i . '">' . $i . '</a>';
			}
		}

		if ( $countPages > 6 ) {
			if ( $countPages > 7 && ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . add_query_arg( 'pageNum', $countPages, $permalink ) . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $countPages . '">' . $countPages . '</a>';
		}
		if ($pageNum < $countPages) {
			$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum + 1), $permalink ) . '" title="' . __('Next page', GWOLLE_GB_TEXTDOMAIN) . '">&raquo;</a>';
		}
	} elseif ($pageNum >= 5) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', 1, $permalink ) . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . ' 1">1</a>';
		if ( ($pageNum - 4) > 1) {
			$pagination .= '<span class="page-numbers dots">...</span>';
		}
		if ( ($pageNum + 2) < $countPages) {
			$minRange = $pageNum - 2;
			$showRange = $pageNum + 2;
		} else {
			$minRange = $pageNum - 3;
			$showRange = $countPages - 1;
		}
		for ($i = $minRange; $i <= $showRange; $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span>' . $i . '</span>';
			} else {
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $countPages) {
			$pagination .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}

		if ($pageNum < $countPages) {
			if ( ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . add_query_arg( 'pageNum', $countPages, $permalink ) . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $countPages . '">' . $countPages . '</a>';
			$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum + 1), $permalink ) . '" title="' . __('Next page', GWOLLE_GB_TEXTDOMAIN) . '">&raquo;</a>';
		}
	}
	$pagination .= '</div>
		';
	if ($countPages > 1) {
		$output .= $pagination;
	}

	/* Entries */
	if ( !is_array($entries) || empty($entries) ) {
		$output .= __('(no entries yet)', GWOLLE_GB_TEXTDOMAIN);
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

		foreach ($entries as $entry) {

			// Run the function from the template to get the entry.
			$entry_output = gwolle_gb_entry_template( $entry, $first );

			$first = false;

			// Add a filter for each entry, so devs can add or remove parts.
			$output .= apply_filters( 'gwolle_gb_entry_read', $entry_output);

		}

		$output .= '</div>';

	}

	if ($countPages > 1) {
		$output .= $pagination;
	}

	// Add filter for the complete output.
	$output = apply_filters( 'gwolle_gb_entries_read', $output);

	return $output;
}

