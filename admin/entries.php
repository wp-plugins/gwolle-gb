<?php
	/*
	 *	entries.php
	 *	Displays the guestbook entries in a list.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	//	Show icons in entry rows?
	if (get_option('gwolle_gb-showEntryIcons') == 'true') { $showIcons = true; }
	
	//	Get entry counts
	$checkedEntries_result = mysql_query("
		SELECT entry_id
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isChecked = '1'
			AND
			entry_isDeleted = '0'
			AND
			entry_isSpam = '0'
	");
	$count['checked'] = mysql_num_rows($checkedEntries_result);
	
	$uncheckedEntries_result = mysql_query("
		SELECT entry_id
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isChecked != '1'
			AND
			entry_isDeleted = '0'
			AND
			entry_isSpam = '0'
	");
	$count['unchecked'] = mysql_num_rows($uncheckedEntries_result);
	
	$spamEntries_result = mysql_query("
		SELECT entry_id
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isSpam = '1'
			AND
			entry_isDeleted = '0'
	");
	$count['spam'] = mysql_num_rows($spamEntries_result);
	
	$count['all'] = $count['checked'] + $count['unchecked'] + $count['spam'];
	
	if ($_REQUEST['show'] == 'checked' || $_REQUEST['show'] == 'unchecked' || $_REQUEST['show'] == 'spam') { $show = $_REQUEST['show']; } else { $show = 'all'; }
	if ($show == 'spam' && get_option('gwolle_gb-akismet-active') != 'true') { $showMsg = 'akismet-not-activated'; }
	
	if (!$_REQUEST['pageNum']) {
		$pageNum = 1;
	}
	else {
		$pageNum = $_REQUEST['pageNum'];
	}
	
	//	Create query string.
	$query_string = "
		SELECT *
		FROM
			" . $wpdb->prefix . "gwolle_gb_entries
		WHERE
			entry_isDeleted = '0'
	";
	
	if ($show == 'checked') {
		$query_string .= " AND entry_isChecked = '1' AND entry_isSpam != '1'";
	}
	elseif ($show == 'unchecked') {
		$query_string .= " AND entry_isChecked != '1' AND entry_isSpam != '1'";
	}
	elseif ($show == 'spam') {
		$query_string .= " AND entry_isSpam = '1' ";
	}
	
	$query_string .= "
		ORDER BY
			entry_date DESC
	";
	
	$entriesPerPage = 15;
	
	//	Calculate the number of pages.
	$countPages = round($count[$show] / 15);
	if ($countPages * $entriesPerPage < $count[$show]) {
		$countPages++;
	}
	
	if ($pageNum > $countPages) {
		$pageNum = 1;
	}
	
	if ($pageNum == 1 && $count[$show] > 0) {
		$firstEntryNum = 1;
		$mysqlFirstRow = 0;
	}
	elseif ($count[$show] == 0) {
		$firstEntryNum = 0;
		$mysqlFirstRow = 0;
	}
	else {
		$firstEntryNum = ($pageNum-1)*$entriesPerPage+1;
		$mysqlFirstRow = $firstEntryNum-1;
	}
	
	$lastEntryNum = $pageNum * $entriesPerPage;
	if ($count[$show] == 0) {
		$lastEntryNum = 0;
	}
	elseif ($lastEntryNum > $count[$show]) {
		$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum-1) * $entriesPerPage) - 1;
	}
	
	$query_string .= " LIMIT " . $mysqlFirstRow . "," . $entriesPerPage;
?>

<div class="wrap">
	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php _e('Guestbook entries',$textdomain); ?></h2>
	
	<?php
		if ($_REQUEST['msg'] || $showMsg) {
			echo '<div id="message" class="updated fade"><p>';
				$msg['deleted'] = __('Entry successfully deleted.',$textdomain);
				$msg['errro-deleting'] = __('An error occured while trying to delete the entry.',$textdomain);
				$msg['akismet-not-activated'] = str_replace('%1',$_SERVER['PHP_SELF'] . '?page=gwolle-gb/settings.php',__('Please activate the use of Akismet on your <a href="%1">Gwolle-GB configuration page</a>. Thanks!',$textdomain));
				echo $msg[$_REQUEST['msg']];
				echo $msg[$showMsg];
			echo '</p></div>';
		}
	?>
	
	<div class="tablenav">
		<div class="alignleft actions">
			<ul class="subsubsub">
				<li><a href='admin.php?page=gwolle-gb/entries.php' <?php if ($show == 'all') { echo 'class="current"'; } ?>><?php _e('All',$textdomain); ?> <span class="count">(<?php echo $count['all']; ?>)</span></a> |</li>
				<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=checked' <?php if ($show == 'checked') { echo 'class="current"'; } ?>><?php _e('Unlocked',$textdomain); ?> <span class="count">(<?php echo $count['checked']; ?>)</span></a> |</li>
				<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=unchecked' <?php if ($show == 'unchecked') { echo 'class="current"'; } ?>><?php _e('New',$textdomain); ?> <span class="count">(<?php echo $count['unchecked']; ?>)</span></a> |</li>
				<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=spam' <?php if ($show == 'spam') { echo 'class="current"'; } ?>><?php _e('Spam',$textdomain); ?> <span class="count">(<?php echo $count['spam']; ?>)</span></a></li>
			</ul>
		</div>

		<div class="tablenav-pages">
			<span class="displaying-num"><?php _e('Showing:',$textdomain); echo ' ' . $firstEntryNum . ' &#8211; ' . $lastEntryNum . ' ' . __('of',$textdomain) . ' ' . $count[$show]; ?></span>
			<?php
				if ($pageNum > 1) {
					echo '<a class="first page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=' . round($pageNum-1) . '">&laquo;</a>';
				}
				if ($pageNum < 5) {
					if ($countPages < 4) { $showRange = $countPages; } else { $showRange = 6; }
					for ($i=1; $i<$showRange; $i++) {
						if ($i==$pageNum) {
							echo '<span class="page-numbers current">' . $i . '</span>';
						}
						else {
							echo '<a class="page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
						}
					}
					
					if ($pageNum < $countPages-2) {
						$highDotsMade = true;	//	The dots next to the highest number have already been put out.
						echo '<span class="page-numbers dots">...</span>';
					}
				}
				elseif ($pageNum >= 5) {
					echo '<a class="page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=1">1</a>';
					echo '<span class="page-numbers dots">...</span>';
					if ($pageNum + 2 < $countPages) { $minRange = $pageNum - 2; $showRange = $pageNum+2; } else { $minRange = $pageNum - 3; $showRange = $countPages - 1; }
					for ($i=$minRange; $i<=$showRange; $i++) {
						if ($i==$pageNum) {
							echo '<span class="page-numbers current">' . $i . '</span>';
						}
						else {
							echo '<a class="page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
						}
					}
					if ($pageNum == $countPages) {
						echo '<span class="page-numbers current">' . $pageNum . '</span>';
					}
				}
				
				if ($pageNum < $countPages) {
					if (($pageNum+3 < $countPages) && !$highDotsMade) { echo '<span class="page-numbers dots">...</span>'; }
					
					echo '<a class="page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=' . $countPages . '">' . $countPages . '</a>';
					echo '<a class="last page-numbers" href="admin.php?page=gwolle-gb/entries.php&show=' . $show . '&pageNum=' . round($pageNum+1) . '">&raquo;</a>';
				}
			?>
		</div>
		
		<table class="widefat">
			<thead>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
					<th scope="col" ><?php _e('ID',$textdomain); ?></th>
					<?php	if ($showIcons) { ?>
						<th scope="col">&nbsp;</th><!-- this is the icon-column -->
					<?php } ?>
					<th scope="col" ><?php _e('Date',$textdomain); ?></th>
					<th scope="col" ><?php _e('Entry (excerpt)',$textdomain); ?></th>
					<th scope="col" ><?php _e('Author',$textdomain); ?></th>
					<th scope="col" ><?php _e('Action',$textdomain); ?></th>
				</tr>
			</thead>
			
			<tfoot>
				<tr>
					<th scope="col" class="manage-column column-cb check-column"><input type="checkbox"></th>
					<th scope="col" ><?php _e('ID',$textdomain); ?></th>
					<?php	if ($showIcons) { ?>
						<th scope="col">&nbsp;</th><!-- this is the icon-column -->
					<?php } ?>
					<th scope="col" ><?php _e('Date',$textdomain); ?></th>
					<th scope="col" ><?php _e('Entry (excerpt)',$textdomain); ?></th>
					<th scope="col" ><?php _e('Author',$textdomain); ?></th>
					<th scope="col" ><?php _e('Action',$textdomain); ?></th>
				</tr>
			</tfoot>
	
	
			<tbody>
				<?php
					//	Load entries.
					$entries_result = mysql_query($query_string);
					
					$rowOdd = true;
					while ($entry = mysql_fetch_array($entries_result)) {
						if ($showIcons) {
							//	Choose icon for entry.
							if ($show == 'all') {
								if ($entry['entry_isChecked'] == 1) { $entryClass = 'checked'; }
								elseif ($entry['entry_isChecked'] != 1) {
									if ($entry['entry_isSpam'] == 1) { $entryClass = 'spam'; }
									else { $entryClass = 'unchecked'; }
								}
							}
							else {
								$entryClass = $show;
							}
						}
						
						//	rows have a different color.
						if ($rowOdd) { $rowOdd = false; $class = 'alternate'; } else { $rowOdd = true; $class = ''; }
						echo '<tr class="' . $class . '">';
							echo '<td class="check"><input type="checkbox"></td>';
							echo '<td class="id">' . $entry['entry_id'] . '</td>';
							if ($showIcons) {
								echo '<td class="entry-' . $entryClass . '">&nbsp;</td>';
							}
							echo '<td>';
								echo date('d.m.Y',$entry['entry_date']);
							echo '</td>';
							echo '<td>';
								echo stripslashes(htmlentities(utf8_decode(substr($entry['entry_content'], 0, 100))));
								if (strlen($entry['entry_content']) > 100) { echo '...'; }
							echo '</td>';
							echo '<td>';
								if (is_numeric($entry['entry_authorAdminId']) && $entry['entry_authorAdminId'] > 0) {
									//	Dies ist ein Admin-Eintrag; hole den Benutzernamen, falls nicht geschehen.
									if (!$adminName[$entry['entry_authorAdminId']]) {
										$userdata = get_userdata($entry['entry_authorAdminId']);
										$adminName[$entry['entry_authorAdminId']] = $userdata->user_login;
									}
									echo '<i>' . $adminName[$entry['entry_authorAdminId']] . '</i>';
								}
								else {
									echo htmlentities(utf8_decode($entry['entry_author_name']));
								}
							echo '</td>';
							echo '<td>';
								echo '<a class="submitdelete deletion" href="' . $_SERVER['PHP_SELF'] . '?page=gwolle-gb/editor.php&amp;action=delete&amp;entry_id=' . $entry['entry_id'] . '" onClick="return confirm(\'' . __("You\'re about to delete this guestbook entry. This can\'t be undone. Are you still sure you want to continue?",$textdomain) . '\');">' . __('Delete',$textdomain) . '</a>&nbsp;|&nbsp;';
								echo '<a href="' . $_SERVER['PHP_SELF'] . '?page=gwolle-gb/editor.php&amp;entry_id=' . $entry['entry_id'] . '">' . __('Details',$textdomain) . '</a>&nbsp;';
							echo '</td>';
						echo '</tr>';
						
						//	editor-row (not visible; maybe we use this in one of the next releases)
						/*
						echo '<tr><td style="border-top:0px;" colspan="5">';
							echo 'editor...';
						echo '</td></tr>';
						*/
					}
					if (mysql_num_rows($entries_result) == 0) {
						echo '<tr><td colspan="5" align="center"><strong>' . __('No entries found.',$textdomain) . '</strong></td></tr>';
					}
				?>
			</tbody>
	
		</table>
		
	</div>


	
</div>