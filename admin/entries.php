<?php
	/*
	 *	entries.php
	 *	Displays the guestbook entries in a list.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_format_value_for_output.func.php');
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entry_count.func.php');
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entries.func.php');
  
  // Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
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
	$count['all'] = $count['checked'] + $count['unchecked'] + $count['spam'];
	
	$show = (isset($_REQUEST['show']) && in_array($_REQUEST['show'], array(
    'checked',
    'unchecked',
    'spam'
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
	<h2><?php _e('Guestbook entries',$textdomain); ?></h2>
	<?php include(WP_PLUGIN_DIR.'/gwolle-gb/msg.php'); ?>

	<form action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page']; ?>&amp;do=massEdit" method="POST">
		<!-- the following fields give us some information we're going to use processing the mass edit -->
		<input type="hidden" name="pageNum" value="<?php echo $pageNum; ?>">
		<input type="hidden" name="entriesOnThisPage" value="<?php mysql_num_rows($entries_result); ?>">
		<input type="hidden" name="show" value="<?php echo $show; ?>">
		
		<ul class="subsubsub">
			<li><a href='admin.php?page=gwolle-gb/entries.php' <?php if ($show == 'all') { echo 'class="current"'; } ?>><?php _e('All',$textdomain); ?> <span class="count">(<?php echo $count['all']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=checked' <?php if ($show == 'checked') { echo 'class="current"'; } ?>><?php _e('Unlocked',$textdomain); ?> <span class="count">(<?php echo $count['checked']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=unchecked' <?php if ($show == 'unchecked') { echo 'class="current"'; } ?>><?php _e('New',$textdomain); ?> <span class="count">(<?php echo $count['unchecked']; ?>)</span></a> |</li>
			<li><a href='admin.php?page=gwolle-gb/entries.php&amp;show=spam' <?php if ($show == 'spam') { echo 'class="current"'; } ?>><?php _e('Spam',$textdomain); ?> <span class="count">(<?php echo $count['spam']; ?>)</span></a></li>
		</ul>
		<div class="tablenav">
			<div class="alignleft actions">
				<?php
					$massEditControls = '<select name="massEditAction1">';
						$massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions',$textdomain) . '</option>';
						if ($show != 'checked') { $massEditControls .= '<option value="check">' . __('Mark as checked',$textdomain) . '</option>'; }
						if ($show != 'unchecked') { $massEditControls .= '<option value="uncheck">' . __('Mark as not checked',$textdomain) . '</option>'; }
						if ($show != 'spam') { $massEditControls .= '<option value="spam">' . __('Mark as spam',$textdomain) . '</option>'; }
						$massEditControls .= '<option value="no-spam">' . __('Mark as not spam',$textdomain) . '</option>';
						$massEditControls .= '<option value="delete">' . __('Delete',$textdomain) . '</option>';
					$massEditControls .= '</select>';
					$massEditControls .= '<input type="submit" value="' . __('Apply',$textdomain) . '" name="doaction" id="doaction" class="button-secondary action" />';
					//	It makes no sense to show the mass edit controls when there are no entries to edit. ;)
					if ($entries !== FALSE) { echo $massEditControls; }
				?>
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
						<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-top" id="check-all-top" type="checkbox"></th>
						<th scope="col" ><?php _e('ID',$textdomain); ?></th>
						<?php	if ($gwolle_gb_settings['showEntryIcons'] === TRUE) { ?>
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
						<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-bottom" id="check-all-bottom" type="checkbox"></th>
						<th scope="col" ><?php _e('ID',$textdomain); ?></th>
						<?php	if ($gwolle_gb_settings['showEntryIcons'] === TRUE) { ?>
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
						$rowOdd = true;
						$html_output = '';
						if ($entries === FALSE) {
						  $colspan = ($gwolle_gb_settings['showEntryIcons'] === TRUE) ? 7 : 6;
							$html_output .= '
							<tr>
                <td colspan="'.$colspan.'" align="center">
                  <strong>'.__('No entries found.',$textdomain).'</strong>
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
  								if ($gwolle_gb_settings['showEntryIcons'] === TRUE) {
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
  								<td>
  								  <a href="'.$_SERVER['PHP_SELF'].'?page=gwolle-gb/editor.php&amp;entry_id='.$entry['entry_id'].'">'.__('Details',$textdomain).'&nbsp;&raquo;</a>&nbsp;
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
							$massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions',$textdomain) . '</option>';
							if ($show != 'checked') { $massEditControls .= '<option value="check">' . __('Mark as checked',$textdomain) . '</option>'; }
							if ($show != 'unchecked') { $massEditControls .= '<option value="uncheck">' . __('Mark as not checked',$textdomain) . '</option>'; }
							if ($show != 'spam') { $massEditControls .= '<option value="spam">' . __('Mark as spam',$textdomain) . '</option>'; }
							$massEditControls .= '<option value="no-spam">' . __('Mark as not spam',$textdomain) . '</option>';
							$massEditControls .= '<option value="delete">' . __('Delete',$textdomain) . '</option>';
						$massEditControls .= '</select>';
						$massEditControls .= '<input type="submit" value="' . __('Apply',$textdomain) . '" name="doaction" id="doaction" class="button-secondary action" />';
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