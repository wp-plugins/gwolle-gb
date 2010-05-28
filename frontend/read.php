<?php
  /**
   * read.php
   * Reading mode of the guestbook frontend
   */
  
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entries.func.php');
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entry_count.func.php');
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_format_value_for_output.func.php');
  
  // Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  $msg = FALSE;
  //  Unset Gwolle-GB session data, if set
  if (isset($_SESSION['gwolle_gb'])) {
    if (isset($_SESSION['gwolle_gb']['msg'])) {
      $msg = $_SESSION['gwolle_gb']['msg'];
    }
    $_SESSION['gwolle_gb'] = array();
  }
  
	// Get links to guestbook page
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_link.func.php');
	$gb_links = gwolle_gb_get_link(array(
    'all' => TRUE
  ));
  
  //	Link 'write a new entry...'
	$output .= '
	<div style="margin-bottom:10px;">
    <a target="_self" href="'.$gb_links['write'].'">&raquo; ' . __('Write a new entry.',$textdomain) . '</a>
  </div>';
	
	if ($msg !== FALSE) {
    $output .= '
    <div class="msg">'.$msg.'</div>';
	}
	
	$entriesPerPage = (int)$gwolle_gb_settings['entriesPerPage'];
	if (!$entriesPerPage || $entriesPerPage < 1) {
		//	This option has not been set, or has manually been edited/deleted in the database. Use default value.
		$entriesPerPage = 20;
	}
	$entriesCount = gwolle_gb_get_entry_count(array(
    'entry_status' => 'checked'
  ));
	$countPages = round($entriesCount / $entriesPerPage);
	if ($countPages * $entriesPerPage < $entriesCount) {
		$countPages++;
	}
	
	if (!$_REQUEST['pageNum']) {
		$pageNum = 1;
	}
	else {
		$pageNum = $_REQUEST['pageNum'];
	}
	
	if ($pageNum > $countPages) {
		$pageNum = 1;
	}
	
	if ($pageNum == 1 && $entriesCount > 0) {
		$firstEntryNum = 1;
		$mysqlFirstRow = 0;
	}
	elseif ($entriesCount == 0) {
		$firstEntryNum = 0;
		$mysqlFirstRow = 0;
	}
	else {
		$firstEntryNum = ($pageNum-1)*$entriesPerPage+1;
		$mysqlFirstRow = $firstEntryNum-1;
	}
	
	$lastEntryNum = $pageNum * $entriesPerPage;
	if ($entriesCount == 0) {
		$lastEntryNum = 0;
	}
	elseif ($lastEntryNum > $entriesCount) {
		$lastEntryNum = $firstEntryNum + ($entriesCount - ($pageNum-1) * $entriesPerPage) - 1;
	}
	
	// Get the entries
	$entries = gwolle_gb_get_entries(array(
    'offset'      => $mysqlFirstRow,
    'show'        => 'checked',
    'num_entries' => $entriesPerPage
  ));
	
	//	page navigation
	$output .= '<div id="page-navigation">';
		if ($pageNum > 1) {
			$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=' . round($pageNum-1) . '">&laquo;</a>';
		}
		if ($pageNum < 5) {
			if ($countPages < 4) { $showRange = $countPages; } else { $showRange = 6; }
			for ($i=1; $i<$showRange; $i++) {
				if ($i==$pageNum) {
					$output .= '<span>' . $i . '</span>';
				}
				else {
					$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=' . $i . '">' . $i . '</a>';
				}
			}
			
			if ($pageNum < $countPages-2) {
				$highDotsMade = true;	//	The dots next to the highest number have already been put out.
				$output .= '<span class="page-numbers dots">...</span>';
			}
		}
		elseif ($pageNum >= 5) {
			$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=1">1</a>';
			if ($pageNum-3 > 1) { $output .= '<span>...</span>'; }
			if ($pageNum + 2 < $countPages) { $minRange = $pageNum - 2; $showRange = $pageNum+2; } else { $minRange = $pageNum - 3; $showRange = $countPages - 1; }
			for ($i=$minRange; $i<=$showRange; $i++) {
				if ($i==$pageNum) {
					$output .= '<span>' . $i . '</span>';
				}
				else {
					$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=' . $i . '">' . $i . '</a>';
				}
			}
			if ($pageNum == $countPages) {
				$output .= '<span class="page-numbers current">' . $pageNum . '</span>';
			}
		}
		
		if ($pageNum < $countPages) {
			if ($pageNum+3 < $countPages && !$highDotsMade) { $output .= '<span class="page-numbers dots">...</span>'; }
			
			$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=' . $countPages . '">' . $countPages . '</a>';
			$output .= '<a href="'.$gb_links['read'].'&amp;pageNum=' . round($pageNum+1) . '">&raquo;</a>';
		}
	$output .= '</div>';
	
	if ($entries === FALSE) {
		$output .= __('(no entries yet)',$textdomain);
	}
	else {
		//  Get option how to display the date
		$date_format = get_option('date_format');
		foreach($entries as $entry) {
			$output .= '<div'; if (!$notFirst) { $notFirst = true; $output .= ' id="first"'; } $output .= ' class="gb-entry '; if ($entry['entry_authorAdminId'] > 0) { $output .= 'admin-entry'; } $output .= '">';
				$output .= '<div class="author-info">';
					$output .= '<span class="author-name">'.$entry['entry_author_name_html'].'</span>';
					if (strlen(str_replace(' ','',$entry['entry_author_origin'])) > 0) {
						$output .= ' ' . __('from',$textdomain) . ' <span class="author-origin">' . gwolle_gb_format_value_for_output($entry['entry_author_origin']) . '</span>';
					}
					$output .= ' ' . __('wrote at',$textdomain) . ' ' . date($date_format, $entry['entry_date']) . ':';
				$output .= '</div>';
				$output .= '<div class="entry-content">';
				  $entry_content = gwolle_gb_format_value_for_output($entry['entry_content']);
				  if ($gwolle_gb_settings['showSmilies'] === TRUE) {
				    $entry_content = convert_smilies($entry_content);
				  }
					if ($gwolle_gb_settings['showLineBreaks'] === TRUE) {
						$output .= nl2br($entry_content);
					}
					else {
						$output .= $entry_content;
					}
				$output .= '</div>';
			$output .= '</div>';
		}
	}
?>