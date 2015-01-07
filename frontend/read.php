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
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		)
	);

	$countPages = ceil( $entriesCount / $entriesPerPage );

	$pageNum = 1;
	if ( isset($_GET['pageNum']) && is_numeric($_GET['pageNum']) ) {
		$pageNum = $_GET['pageNum'];
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
			'offset' => $mysqlFirstRow,
			'num_entries' => $entriesPerPage,
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		)
	);

	/* Page navigation */
	$pagination = '<div class="page-navigation">';
	if ($pageNum > 1) {
		$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . round($pageNum - 1) . '" title="' . __('Previous page', GWOLLE_GB_TEXTDOMAIN) . '">&laquo;</a>';
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
				$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . $i . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $i . '">' . $i . '</a>';
			}
		}

		if ( $countPages > 6 ) {
			if ( $countPages > 7 && ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . $countPages . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $countPages . '">' . $countPages . '</a>';
		}
		if ($pageNum < $countPages) {
			$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . round($pageNum + 1) . '" title="' . __('Next page', GWOLLE_GB_TEXTDOMAIN) . '">&raquo;</a>';
		}
	} elseif ($pageNum >= 5) {
		$pagination .= '<a href="' . $page_link . '&amp;pageNum=1" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . ' 1">1</a>';
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
				$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . $i . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $countPages) {
			$pagination .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}

		if ($pageNum < $countPages) {
			if ( ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . $countPages . '" title="' . __('Page', GWOLLE_GB_TEXTDOMAIN) . " " . $countPages . '">' . $countPages . '</a>';
			$pagination .= '<a href="' . $page_link . '&amp;pageNum=' . round($pageNum + 1) . '" title="' . __('Next page', GWOLLE_GB_TEXTDOMAIN) . '">&raquo;</a>';
		}
	}
	$pagination .= '</div>';
	$output .= $pagination;


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
			$author_id = $entry->get_author_id();
			$is_moderator = gwolle_gb_is_moderator( $author_id );
			if ( $is_moderator ) {
				$output .= ' admin-entry ';
			}
			$output .= '">';

			// Author Info
			$output .= '<div class="gb-author-info">';

			// Author Avatar
			if ( get_option('show_avatars') ) {
				$avatar = get_avatar( $entry->get_author_email(), 32, '', $entry->get_author_name() );
				if ($avatar) {
					$output .= '<span class="gb-author-avatar">' . $avatar . '</span>';
				}
			}

			$author_name_html = gwolle_gb_get_author_name_html($entry);
			$output .= '<span class="gb-author-name">' . $author_name_html . '</span>';

			// Author Origin
			$origin = $entry->get_author_origin();
			if ( strlen(str_replace(' ', '', $origin)) > 0 ) {
				$output .= ' ' . __('from', GWOLLE_GB_TEXTDOMAIN) . ' <span class="gb-author-origin">' . gwolle_gb_format_value_for_output($origin) . '</span>';
			}

			// Entry Date and Time
			$output .= ' ' . __('wrote at', GWOLLE_GB_TEXTDOMAIN) . ' ' . date_i18n( get_option('date_format'), $entry->get_date() ) . ', ' .
				trim(date_i18n( get_option('time_format'), $entry->get_date() )) . ': ';
			$output .= '</div>';

			// Main Content
			$output .= '<div class="gb-entry-content">';
			$entry_content = gwolle_gb_format_value_for_output( $entry->get_content() );
			if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
				$entry_content = convert_smilies($entry_content);
			}
			if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
				$output .= nl2br($entry_content);
			} else {
				$output .= $entry_content;
			}

			// Edit Link for Moderators
			if ( function_exists('current_user_can') && current_user_can('moderate_comments') ) {
				$output .= '
					<a class="gwolle_gb_edit_link" href="' . admin_url('admin.php?page=' . GWOLLE_GB_FOLDER . '/editor.php&entry_id=' . $entry->get_id() ) . '" title="' . __('Edit entry', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Edit', GWOLLE_GB_TEXTDOMAIN) . '</a>';
			}
			$output .= '</div>';

			$output .= '</div>';
		}
	}

	$output .= $pagination;

	return $output;
}

