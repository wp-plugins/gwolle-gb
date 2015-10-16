<?php

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/*
 * gwolle_gb_pagination_frontend
 * Pagination of the entries for the guestbook frontend
 *
 * @args: $pageNum, int with the number of the requested page.
 *        $countPages, int with the total number of pages.
 *  @return: $pagination, string with the html of the pagination.
 */
function gwolle_gb_pagination_frontend( $pageNum, $countPages ) {

	$permalink = get_permalink(get_the_ID());

	$pagination = '<div class="page-navigation">';
	if ($pageNum > 1) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum - 1), $permalink ) . '" title="' . __('Previous page', 'gwolle-gb') . '">&laquo;</a>';
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
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . " " . $i . '">' . $i . '</a>';
			}
		}

		if ( $countPages > 6 ) {
			if ( $countPages > 7 && ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . add_query_arg( 'pageNum', $countPages, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . " " . $countPages . '">' . $countPages . '</a>';
		}
		if ($pageNum < $countPages) {
			$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum + 1), $permalink ) . '" title="' . __('Next page', 'gwolle-gb') . '">&raquo;</a>';
		}
	} elseif ($pageNum >= 5) {
		$pagination .= '<a href="' . add_query_arg( 'pageNum', 1, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . ' 1">1</a>';
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
				$pagination .= '<a href="' . add_query_arg( 'pageNum', $i, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . " " . $i . '">' . $i . '</a>';
			}
		}
		if ($pageNum == $countPages) {
			$pagination .= '<span class="page-numbers current">' . $pageNum . '</span>';
		}

		if ($pageNum < $countPages) {
			if ( ($pageNum + 3) < $countPages ) {
				$pagination .= '<span class="page-numbers dots">...</span>';
			}
			$pagination .= '<a href="' . add_query_arg( 'pageNum', $countPages, $permalink ) . '" title="' . __('Page', 'gwolle-gb') . " " . $countPages . '">' . $countPages . '</a>';
			$pagination .= '<a href="' . add_query_arg( 'pageNum', round($pageNum + 1), $permalink ) . '" title="' . __('Next page', 'gwolle-gb') . '">&raquo;</a>';
		}
	}
	// 'All' link
	if ( $countPages >= 2 && get_option( 'gwolle_gb-paginate_all', 'false' ) === 'true' ) {
		if ( isset($_GET['show_all']) && $_GET['show_all'] == 'true' ) {
			$pagination .= '<span>' . __('All', 'gwolle-gb') . '</span>';
		} else {
			$pagination .= '<a href="' . add_query_arg( 'show_all', 'true', $permalink ) . '" title="' . __('All entries', 'gwolle-gb') . '">' . __('All', 'gwolle-gb') . '</a>';
		}
	}

	$pagination .= '</div>
		';

	if ($countPages > 1) {
		return $pagination;
	}

}

