<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_pagination_admin
 * Pagination of the entries for the page-entries.php
 *
 * @args: $pageNum, int with the number of the requested page.
 *        $countPages, int with the total number of pages.
 *        $count, int with total number of entries. Relative to the $show variable.
 *        $show, string with the tab of the page that is shown.
 *  @return: $pagination, string with the html of the pagination.
 */
function gwolle_gb_pagination_admin( $pageNum, $countPages, $count, $show ) {

	$entries_per_page = get_option('gwolle_gb-entries_per_page', 20);

	// Calculate entry-args for query
	if ($pageNum == 1 && $count[$show] > 0) {
		$firstEntryNum = 1;
		$mysqlFirstRow = 0;
	} elseif ($count[$show] == 0) {
		$firstEntryNum = 0;
		$mysqlFirstRow = 0;
	} else {
		$firstEntryNum = ($pageNum - 1) * $entries_per_page + 1;
		$mysqlFirstRow = $firstEntryNum - 1;
	}

	// Calculate written text with info "Showing 1 – 25 of 54"
	$lastEntryNum = $pageNum * $entries_per_page;
	if ($count[$show] == 0) {
		$lastEntryNum = 0;
	} elseif ($lastEntryNum > $count[$show]) {
		$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum - 1) * $entries_per_page) - 1;
	}

	$pagination = '<div class="tablenav-pages">';

	$highDotsMade = false;

	$pagination .= '<span class="displaying-num">' . __('Showing:', 'gwolle-gb') .
		' ' . $firstEntryNum . ' &#8211; ' . $lastEntryNum . ' ' . __('of', 'gwolle-gb') . ' ' . $count[$show] . '</span>
		';
	if ($pageNum > 1) {
		$pagination .= '<a class="first page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum - 1) . '">&laquo;</a>';
	}

	if ($pageNum < 5) {
		if ($countPages < 4) {
			$showRange = $countPages;
		} else {
			$showRange = 6;
		}
		for ($i = 1; $i < ($showRange + 1); $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span class="page-numbers current">' . $i . '</span>';
			} else {
				$pagination .= '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
			}
		}

		if ($pageNum + 4 < $countPages) {
			$highDotsMade = true;
			// The dots next to the highest number have already been put out.
			$pagination .= '<span class="page-numbers dots">...</span>';
		}
	} elseif ($pageNum >= 5) {
		$pagination .= '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=1">1</a>';
		if ($countPages > 5) {
			$pagination .= '<span class="page-numbers dots">...</span>';
		}
		if ($pageNum + 2 < $countPages) {
			$minRange = $pageNum - 2;
			$showRange = $pageNum + 2;
		} else {
			$minRange = $pageNum - 3;
			$showRange = $countPages - 1;
		}
		for ($i = $minRange; $i <= $showRange; $i++) {
			if ($i == $pageNum) {
				$pagination .= '<span class="page-numbers current">' . $i . '</span>';
			} else {
				$pagination .= '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $countPages) {
			$pagination .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}
	}

	if ($pageNum < $countPages) {
		if (($pageNum + 4 < $countPages) && !$highDotsMade) {
			$pagination .= '<span class="page-numbers dots">...</span>';
			$highDotsMade = true;
		}
		if ( isset($highDotsMade) ) {
			$pagination .= '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $countPages . '">' . $countPages . '</a>';
		}
		$pagination .= '<a class="last page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum + 1) . '">&raquo;</a>';
	}

	$pagination .= "</div>";

	return $pagination;

}


