<?php
	//	Link 'write a new entry...'
	echo '<div style="margin-bottom:10px;"><a href="' . $gb_link . '&amp;gb_page=write">&raquo; ' . __('Write a new entry.',$textdomain) . '</a></div>';
	
	if ($_REQUEST['msg']) {
		//	Output a requested message
		echo '<div class="msg">';
			$msg['entry-saved'] = __('Thanks for your entry.',$textdomain); if (get_option('gwolle_gb-moderate-entries')=='true') { $msg['entry-saved'] .= __('<br>We will review it and unlock it in a short while.',$textdomain); }
			$msg['error'] = __('Well, there has been an error querying the database.<br>Please try again later, thanks!',$textdomain);
			echo $msg[$_REQUEST['msg']];
		echo '</div>';
	}
	
	//	Calculate page count for all (checked) entries.
	$allEntries_result = mysql_query("
		SELECT *
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isChecked = '1'
			AND
			entry_isDeleted = '0'
	");
	$entriesPerPage = 20;
	$entriesCount = mysql_num_rows($allEntries_result);
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
	
	//	Create query string
	$query_string = "
		SELECT *
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isChecked = '1'
			AND
			entry_isDeleted = '0'
		ORDER BY
			entry_date DESC
	";
	
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
	
	$query_string .= " LIMIT " . $mysqlFirstRow . "," . $entriesPerPage;
	
	//	page navigation
	echo '<div id="page-navigation">';
		if ($pageNum > 1) {
			echo '<a href="' . $gb_link . '&amp;pageNum=' . round($pageNum-1) . '">&laquo;</a>';
		}
		if ($pageNum < 5) {
			if ($countPages < 4) { $showRange = $countPages; } else { $showRange = 6; }
			for ($i=1; $i<$showRange; $i++) {
				if ($i==$pageNum) {
					echo '<span>' . $i . '</span>';
				}
				else {
					echo '<a href="' . $gb_link . '&amp;pageNum=' . $i . '">' . $i . '</a>';
				}
			}
			
			if ($pageNum < $countPages-2) {
				$highDotsMade = true;	//	The dots next to the highest number have already been put out.
				echo '<span class="page-numbers dots">...</span>';
			}
		}
		elseif ($pageNum >= 5) {
			echo '<a href="' . $gb_link . '&amp;pageNum=1">1</a>';
			if ($pageNum-3 > 1) { echo '<span>...</span>'; }
			if ($pageNum + 2 < $countPages) { $minRange = $pageNum - 2; $showRange = $pageNum+2; } else { $minRange = $pageNum - 3; $showRange = $countPages - 1; }
			for ($i=$minRange; $i<=$showRange; $i++) {
				if ($i==$pageNum) {
					echo '<span>' . $i . '</span>';
				}
				else {
					echo '<a href="' . $gb_link . '&amp;pageNum=' . $i . '">' . $i . '</a>';
				}
			}
			if ($pageNum == $countPages) {
				echo '<span class="page-numbers current">' . $pageNum . '</span>';
			}
		}
		
		if ($pageNum < $countPages) {
			if ($pageNum+3 < $countPages && !$highDotsMade) { echo '<span class="page-numbers dots">...</span>'; }
			
			echo '<a href="' . $gb_link . '&amp;pageNum=' . $countPages . '">' . $countPages . '</a>';
			echo '<a href="' . $gb_link . '&amp;pageNum=' . round($pageNum+1) . '">&raquo;</a>';
		}
	echo '</div>';
	
	//	Get the entries for 'this' page from the database.
	global $wpdb;
	$entries_result = mysql_query($query_string);
	
	if ($entriesCount == 0) {
		_e('(no entries yet)',$textdomain);
	}
	else {
		while ($entry = mysql_fetch_array($entries_result)) {
			echo '<div'; if (!$notFirst) { $notFirst = true; echo ' id="first"'; } echo ' class="gb-entry '; if ($entry['entry_authorAdminId'] > 0) { echo 'admin-entry'; } echo '">';
				echo '<div class="author-info">';
					echo '<span class="author-name">';
						if (is_numeric($entry['entry_authorAdminId']) && $entry['entry_authorAdminId'] > 0) {
							//	This entry has been written by a staff member; get his/her username, if not already done.
							if (!$adminName[$entry['entry_authorAdminId']]) {
								$userdata = get_userdata($entry['entry_authorAdminId']);
								$adminName[$entry['entry_authorAdminId']] = $userdata->user_login;
							}
							echo __('Staff',$textdomain) . ' (<i>' . $adminName[$entry['entry_authorAdminId']] . '</i>)';
						}
						else {
							echo htmlentities(utf8_decode($entry['entry_author_name']));
						}
					echo '</span>';
					if (strlen(str_replace(' ','',$entry['entry_author_origin'])) > 0) {
						echo ' ' . __('from',$textdomain) . ' <span class="author-origin">' . htmlentities(utf8_decode($entry['entry_author_origin'])) . '</span>';
					}
					echo ' ' . __('wrote at',$textdomain) . ' ' . date('d.m.Y', $entry['entry_date']) . ':';
				echo '</div>';
				echo '<div class="entry-content">';
					echo stripslashes(htmlentities(utf8_decode($entry['entry_content'])));
				echo '</div>';
			echo '</div>';
		}
	}
?>