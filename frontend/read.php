<?php

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }


/*
 * gwolle_gb_frontend_read
 * Reading mode of the guestbook frontend
 */

function gwolle_gb_frontend_read() {

	$output = '';

	// Get permalink of the guestbookpage so we can work with it.
	$page_link = get_permalink( get_the_ID() );
	$pattern = '/\?/';
	if ( !preg_match($pattern, $page_link, $matches, PREG_OFFSET_CAPTURE, 3) ) {
		// Append with a slash and questionmark, so we can add parameters
		$page_link .= '/?';
	}


	$entriesPerPage = (int) get_option('gwolle_gb-entriesPerPage', 20);

	$entriesCount = gwolle_gb_get_entry_count(
		array(
			'checked' => 'checked',
			'deleted' => 'notdeleted',
			'spam' => 'nospam'
		)
	);

	$countPages = round($entriesCount / $entriesPerPage);
	if ($countPages * $entriesPerPage < $entriesCount) {
		$countPages++;
	}

	$pageNum = 1;
	if ( isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) ) {
		$pageNum = $_GET['pageNum'];
	}

	if ($pageNum > $countPages) {
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


	/* Get the entries for the frontend */
	$entries = gwolle_gb_get_entries(
		array(
			'offset' => $mysqlFirstRow,
			'num_entries' => $entriesPerPage,
			'checked' => 'checked',
			'deleted' => 'notdeleted',
			'spam' => 'nospam'
		)
	);

	// FIXME: pagination is broken on frontend on page 3 of 3
	/* Page navigation */
	$output .= '<div id="page-navigation">';
	if ($pageNum > 1) {
		$output .= '<a href="' . $page_link . '&amp;pageNum=' . round($pageNum - 1) . '">&laquo;</a>';
	}
	if ($pageNum < 5) {
		if ($countPages < 4) { $showRange = $countPages;
		} else { $showRange = 6;
		}
		for ($i = 1; $i < $showRange; $i++) {
			if ($i == $pageNum) {
				$output .= '<span>' . $i . '</span>';
			} else {
				$output .= '<a href="' . $page_link . '&amp;pageNum=' . $i . '">' . $i . '</a>';
			}
		}

		if ($pageNum < $countPages - 2) {
			$highDotsMade = true;
			/* The dots next to the highest number have already been put out. */
			$output .= '<span class="page-numbers dots">...</span>';
		}
	} elseif ($pageNum >= 5) {
		$output .= '<a href="' . $gb_links['read'] . '&amp;pageNum=1">1</a>';
		if ($pageNum - 3 > 1) { $output .= '<span>...</span>';
		}
		if ($pageNum + 2 < $countPages) { $minRange = $pageNum - 2;
			$showRange = $pageNum + 2;
		} else { $minRange = $pageNum - 3;
			$showRange = $countPages - 1;
		}
		for ($i = $minRange; $i <= $showRange; $i++) {
			if ($i == $pageNum) {
				$output .= '<span>' . $i . '</span>';
			} else {
				$output .= '<a href="' . $page_link . '&amp;pageNum=' . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $countPages) {
			$output .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}
	}

	if ($pageNum < $countPages) {
		if ($pageNum + 3 < $countPages && !$highDotsMade) {
			$output .= '<span class="page-numbers dots">...</span>';
		}
		$output .= '<a href="' . $page_link . '&amp;pageNum=' . $countPages . '">' . $countPages . '</a>';
		$output .= '<a href="' . $page_link . '&amp;pageNum=' . round($pageNum + 1) . '">&raquo;</a>';
	}
	$output .= '</div>';


	/* Entries */
	if ( !is_array($entries) || count($entries) == 0 ) {
		$output .= __('(no entries yet)', GWOLLE_GB_TEXTDOMAIN);
	} else {
		$first = true;
		foreach ($entries as $entry) {
			// Main Author div
			$output .= '<div class="';
			if ($first == true) {
				$first = false;
				$output .= ' first ';
			}
			$output .= ' gb-entry ';
			$output .= ' gb-entry_' . $entry->get_id() . ' ';
			$authoradminid = $entry->get_authoradminid();
			$is_moderator = gwolle_gb_is_moderator( $authoradminid );
			if ( $is_moderator ) {
				$output .= ' admin-entry ';
			}
			$output .= '">';

			// Author Info
			$output .= '<div class="author-info">';

			// Author Avatar
			if ( get_option('show_avatars') ) {
				$avatar = get_avatar( $entry->get_author_email(), 32, '', $entry->get_author_name() );
				if ($avatar) {
					$output .= '<span class="author-avatar">' . $avatar . '</span>';
				}
			}

			$author_name_html = gwolle_gb_get_author_name_html($entry);
			$output .= '<span class="author-name">' . $author_name_html . '</span>';

			// Author Origin
			$origin = $entry->get_author_origin();
			if ( strlen(str_replace(' ', '', $origin)) > 0 ) {
				$output .= ' ' . __('from', GWOLLE_GB_TEXTDOMAIN) . ' <span class="author-origin">' . gwolle_gb_format_value_for_output($origin) . '</span>';
			}

			// Entry Date and Time
			$output .= ' ' . __('wrote at', GWOLLE_GB_TEXTDOMAIN) . ' ' . date_i18n( get_option('date_format'), $entry->get_date() ) . ', ' .
				date_i18n( get_option('time_format'), $entry->get_date() ) . ': ';
			$output .= '</div>';

			// Main Content
			$output .= '<div class="entry-content">';
			$entry_content = gwolle_gb_format_value_for_output( $entry->get_content() );
			if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
				$entry_content = convert_smilies($entry_content);
			}
			if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
				$output .= nl2br($entry_content);
			} else {
				$output .= $entry_content;
			}
			$output .= '</div>';

			$output .= '</div>';
		}
	}

	return $output;
}

