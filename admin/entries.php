<?php
	/*
	 *	entries.php
	 *	Displays the guestbook entries in a list.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_format_value_for_output.func.php');
  include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entry_count.func.php');
  include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
  
  // Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
	
	//	Get entry counts
	$count['checked']    = gwolle_gb_get_entry_count(array(
    'entry_status' => 'checked'
  ));
	$count['unchecked']  = gwolle_gb_get_entry_count(array(
    'entry_status' => 'unchecked'
  ));
	$count['spam']       = gwolle_gb_get_entry_count(array(
    'entry_status' => 'spam'
  ));
  $count['trash']      = gwolle_gb_get_entry_count(array(
    'entry_status' => 'trash'
  ));
	$count['all'] = $count['checked'] + $count['unchecked'] + $count['spam'];
	
	$show = (isset($_REQUEST['show']) && in_array($_REQUEST['show'], array(
    'checked',
    'unchecked',
    'spam',
    'trash'
  ))) ? $_REQUEST['show'] : 'all';
  
  //  If Akimet has not been activated yet and the user is looking at the spam tell him to activate Akismet.
	if ($show == 'spam' && get_option('gwolle_gb-akismet-active') != 'true') { $showMsg = 'akismet-not-activated'; }
	
	// Check if the requested page number is an integer > 0
	$pageNum = (isset($_REQUEST['pageNum']) && $_REQUEST['pageNum'] && (int)$_REQUEST['pageNum'] > 0) ? (int)$_REQUEST['pageNum'] : 1;
	
	//	Calculate the number of pages.
	$countPages = round($count[$show] / 15);
	if ($countPages * $gwolle_gb_settings['entries_per_page'] < $count[$show]) {
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
		$firstEntryNum = ($pageNum-1)*$gwolle_gb_settings['entries_per_page']+1;
		$mysqlFirstRow = $firstEntryNum-1;
	}
	
	$lastEntryNum = $pageNum * $gwolle_gb_settings['entries_per_page'];
	if ($count[$show] == 0) {
		$lastEntryNum = 0;
	}
	elseif ($lastEntryNum > $count[$show]) {
		$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum-1) * $gwolle_gb_settings['entries_per_page']) - 1;
	}
	
	// Get the entries
	$entries = gwolle_gb_get_entries(array(
    'offset'  => $mysqlFirstRow,
    'show'    => $show
  ));
?>

<div class="wrap">
	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php _e('Guestbook entries',GWOLLE_GB_TEXTDOMAIN); ?></h2>
	<?php include(GWOLLE_GB_DIR.'/msg.php'); ?>

	<form action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page']; ?>&amp;do=massEdit" method="POST">
		<!-- the following fields give us some information we're going to use processing the mass edit -->
		<input type="hidden" name="pageNum" value="<?php echo $pageNum; ?>">
		<input type="hidden" name="entriesOnThisPage" value="<?php $wpdb->num_rows; ?>">
		<input type="hidden" name="show" value="<?php echo $show; ?>">
		
		<ul class="subsubsub">
			<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php' <?php if ($show == 'all') { echo 'class="current"'; } ?>><?php _e('All',GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['all']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked' <?php if ($show == 'checked') { echo 'class="current"'; } ?>><?php _e('Unlocked',GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['checked']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked' <?php if ($show == 'unchecked') { echo 'class="current"'; } ?>><?php _e('New',GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['unchecked']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam' <?php if ($show == 'spam') { echo 'class="current"'; } ?>><?php _e('Spam',GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['spam']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=trash' <?php if ($show == 'trash') { echo 'class="current"'; } ?>><?php _e('Trash'); ?> <span class="count">(<?php echo $count['trash']; ?>)</span></a></li>
		</ul>
		<div class="tablenav">
			<div class="alignleft actions">
				<?php
					$massEditControls = '<select name="massEditAction1">';
            $massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions',GWOLLE_GB_TEXTDOMAIN) . '</option>';
            if ($show == 'trash') {
              $massEditControls .= '
              <option value="untrash">Widerherstellen</option>
              <option value="remove">Endgültig entfernen</option>';
            }
            else {
  						if ($show != 'checked') { $massEditControls .= '<option value="check">' . __('Mark as checked',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
  						if ($show != 'unchecked') { $massEditControls .= '<option value="uncheck">' . __('Mark as not checked',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
  						if ($show != 'spam') { $massEditControls .= '<option value="spam">' . __('Mark as spam',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
  						$massEditControls .= '<option value="no-spam">' . __('Mark as not spam',GWOLLE_GB_TEXTDOMAIN) . '</option>';
  						$massEditControls .= '<option value="trash">' . __('Trash') . '</option>';
  				  }
					$massEditControls .= '</select>';
					$massEditControls .= '<input type="submit" value="' . __('Apply',GWOLLE_GB_TEXTDOMAIN) . '" name="doaction" id="doaction" class="button-secondary action" />';
					//	It makes no sense to show the mass edit controls when there are no entries to edit. ;)
					if ($entries !== FALSE) { echo $massEditControls; }
				?>
			</div>
	
			<div class="tablenav-pages">
				<span class="displaying-num"><?php _e('Showing:',GWOLLE_GB_TEXTDOMAIN); echo ' ' . $firstEntryNum . ' &#8211; ' . $lastEntryNum . ' ' . __('of',GWOLLE_GB_TEXTDOMAIN) . ' ' . $count[$show]; ?></span>
				<?php
					if ($pageNum > 1) {
						echo '<a class="first page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=' . round($pageNum-1) . '">&laquo;</a>';
					}
					if ($pageNum < 5) {
						if ($countPages < 4) {
						  $showRange = $countPages;
						}
						else {
						  $showRange = 6;
						}
						for ($i=1; $i<$showRange; $i++) {
							if ($i==$pageNum) {
								echo '<span class="page-numbers current">' . $i . '</span>';
							}
							else {
								echo '<a class="page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
							}
						}
						
						if ($pageNum+4 < $countPages) {
							$highDotsMade = true;	//	The dots next to the highest number have already been put out.
							echo '<span class="page-numbers dots">...</span>';
						}
					}
					elseif ($pageNum >= 5) {
						echo '<a class="page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=1">1</a>';
						if ($countPages > 5) {
							 echo '<span class="page-numbers dots">...</span>';
						}
						if ($pageNum + 2 < $countPages) {
						  $minRange = $pageNum - 2; 
						  $showRange = $pageNum+2;
						}
						else {
						  $minRange = $pageNum - 3;
						  $showRange = $countPages - 1;
						}
						for ($i=$minRange; $i<=$showRange; $i++) {
							if ($i==$pageNum) {
								echo '<span class="page-numbers current">' . $i . '</span>';
							}
							else {
								echo '<a class="page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
							}
						}
						if ($pageNum == $countPages) {
							echo '<span class="page-numbers current">' . $pageNum . '</span>';
						}
					}
					
					if ($pageNum < $countPages) {
						if (($pageNum+4 < $countPages) && !$highDotsMade) {
						  echo '<span class="page-numbers dots">...</span>';
						  $highDotsMade = true;
						}
						if ($highDotsMade) {
						  echo '<a class="page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=' . $countPages . '">' . $countPages . '</a>';
						}
						echo '<a class="last page-numbers" href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&show=' . $show . '&pageNum=' . round($pageNum+1) . '">&raquo;</a>';
					}
				?>
			</div>
			
			<table class="widefat">
				<thead>
					<tr>
						<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-top" id="check-all-top" type="checkbox"></th>
						<th scope="col" ><?php _e('ID',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<?php	if ($gwolle_gb_settings['showEntryIcons'] === TRUE && $show !== 'trash') { ?>
							<th scope="col">&nbsp;</th><!-- this is the icon-column -->
						<?php } ?>
						<th scope="col" ><?php _e('Date',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Entry (excerpt)',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Author',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Action',GWOLLE_GB_TEXTDOMAIN); ?></th>
					</tr>
				</thead>
				
				<tfoot>
					<tr>
						<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-bottom" id="check-all-bottom" type="checkbox"></th>
						<th scope="col" ><?php _e('ID',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<?php	if ($gwolle_gb_settings['showEntryIcons'] === TRUE && $show !== 'trash') { ?>
							<th scope="col">&nbsp;</th><!-- this is the icon-column -->
						<?php } ?>
						<th scope="col" ><?php _e('Date',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Entry (excerpt)',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Author',GWOLLE_GB_TEXTDOMAIN); ?></th>
						<th scope="col" ><?php _e('Action',GWOLLE_GB_TEXTDOMAIN); ?></th>
					</tr>
				</tfoot>
		
		
				<tbody>
					<?php
						$rowOdd = true;
						$html_output = '';
						if ($entries === FALSE) {
						  $colspan = ($gwolle_gb_settings['showEntryIcons'] === TRUE) ? 7 : 6;
							$html_output .= '
							<tr>
                <td colspan="'.$colspan.'" align="center">
                  <strong>'.__('No entries found.',GWOLLE_GB_TEXTDOMAIN).'</strong>
                </td>
              </tr>';
						}
						else {
  						foreach($entries as $entry) {
  							
  							//	rows have a different color.
  							if ($rowOdd) {
                  $rowOdd = false;
                  $class = ' alternate';
                }
                else {
                  $rowOdd = true;
                  $class = '';
                }
                
                //  Attach 'spam' to class if the entry's spam
                if ($entry['entry_isSpam'] === 1) {
                  $class .= ' spam';
                }
                
  							$html_output .= '
  							<tr id="entry_'.$entry['entry_id'].'" class="entry '.$class.'">
                  <td class="check">
                    <input name="check-'.$entry['entry_id'].'" id="check-'.$entry['entry_id'].'" type="checkbox">
                  </td>
                  <td class="id">'.$entry['entry_id'].'</td>';
  								if ($gwolle_gb_settings['showEntryIcons'] === TRUE && $show !== 'trash') {
  									$html_output .= '
                    <td class="entry-'.$entry['icon_class'].'">&nbsp;</td>';
  								}
  								$html_output .= '
  								<td>'.$entry['entry_date_html'].'</td>
  								<td>
  								  '.$entry['spam_icon'].'
    							  <label for="check-' . $entry['entry_id'] . '">'.$entry['excerpt'].'</label>
  								</td>
  								<td>'.$entry['entry_author_name_html'].'</td>
  								<td>';
  								  if ($show == 'trash') {
  								    $html_output .= '
  								    <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&gwolle_gb_function=untrash_entry&entry_id='.$entry['entry_id'].'&show=trash">'.__('Recover',GWOLLE_GB_TEXTDOMAIN).'</a>
  								    <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&gwolle_gb_function=delete_entry&entry_id='.$entry['entry_id'].'&show=trash" onClick="return confirm(\''.__("You\'re about to delete this guestbook entry. This can\'t be undone. Are you still sure you want to continue?",GWOLLE_GB_TEXTDOMAIN).'\');">'.__('Delete',GWOLLE_GB_TEXTDOMAIN).'</a>';
  								  }
  								  else {
  								    $html_output .= '
  								    <a href="'.$_SERVER['PHP_SELF'].'?page='.GWOLLE_GB_FOLDER.'/editor.php&amp;entry_id='.$entry['entry_id'].'">'.__('Details',GWOLLE_GB_TEXTDOMAIN).'&nbsp;&raquo;</a>&nbsp;';
  								  }
  								  echo '
  								</td>
                </tr>';
  							
  							// Quick-Editor
  							/*
  							echo '
                <tr style="display:none;" class="inline-edit-row inline-edit-row-post quick-edit-row quick-edit-row-post alternate inline-editor" id="quickedit_'.$entry['entry_id'].'">
                  <td style="border-top:0px;" colspan="'; if ($gwolle_gb_settings['showEntryIcons']) { echo 7; } else { echo 6; } echo '">
  								  <h4>QUICKEDIT</h4>
  								  <fieldset>
  								  
  								  </fieldset>
  								</td>
                </tr>';
                */
  						}
  				  }
						echo $html_output;
					?>
				</tbody>
		
			</table>
			
			<div class="tablenav">
				<div class="alignleft actions">
					<?php
						$massEditControls = '<select name="massEditAction2">';
							$massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions',GWOLLE_GB_TEXTDOMAIN) . '</option>';
							if ($show != 'checked') { $massEditControls .= '<option value="check">' . __('Mark as checked',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
							if ($show != 'unchecked') { $massEditControls .= '<option value="uncheck">' . __('Mark as not checked',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
							if ($show != 'spam') { $massEditControls .= '<option value="spam">' . __('Mark as spam',GWOLLE_GB_TEXTDOMAIN) . '</option>'; }
							$massEditControls .= '<option value="no-spam">' . __('Mark as not spam',GWOLLE_GB_TEXTDOMAIN) . '</option>';
							$massEditControls .= '<option value="delete">' . __('Delete',GWOLLE_GB_TEXTDOMAIN) . '</option>';
						$massEditControls .= '</select>';
						$massEditControls .= '<input type="submit" value="' . __('Apply',GWOLLE_GB_TEXTDOMAIN) . '" name="doaction" id="doaction" class="button-secondary action" />';
						//	It makes no sense to show the mass edit controls when there are no entries to edit. ;)
						if ($entries !== FALSE) { echo $massEditControls; }
					?>
					<br class="clear" />
				</div>
				<br class="clear" />
			</div>
			
		</div>
	</form>


	
</div>