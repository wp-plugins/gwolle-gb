<?php
/*
 * entries.php
 * Displays the guestbook entries in a list.
 */

//	No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_entries() {
	global $wpdb;

	if (!get_option('gwolle_gb_version')) {
		gwolle_gb_installSplash();
	} else {

		//if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		// FIXME: use the right pagename
		if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) { // different names
			if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
				die(__('Cheatin&#8217; uh?'));
			}
		}

		// FIXME; put here the posthandling from ...





		// Get entry counts
		// FIXME: make sure the 'visible' works on this page
		$count = Array();
		$count['visible']	= gwolle_gb_get_entry_count(
			array(
				'checked' => 'checked',
				'deleted' => 'notdeleted',
				'spam' => 'nospam'
			)
		);
		$count['checked']	= gwolle_gb_get_entry_count(array( 'checked' => 'checked' ));
		$count['unchecked']	= gwolle_gb_get_entry_count(array( 'checked' => 'unchecked' ));
		$count['spam']		= gwolle_gb_get_entry_count(array( 'spam' => 'spam' ));
		$count['trash']		= gwolle_gb_get_entry_count(array( 'deleted' => 'deleted' ));
		$count['all']		= gwolle_gb_get_entry_count(array( 'all' => 'all' ));

		// FIXME: for now the old counters
		$count['checked']    = gwolle_gb_get_entry_count_old(array( 'entry_status' => 'checked' ));
		$count['unchecked']  = gwolle_gb_get_entry_count_old(array( 'entry_status' => 'unchecked' ));
		$count['spam']       = gwolle_gb_get_entry_count_old(array( 'entry_status' => 'spam' ));
		$count['trash']      = gwolle_gb_get_entry_count_old(array( 'entry_status' => 'trash' ));
		$count['all'] = $count['checked'] + $count['unchecked'] + $count['spam'];



		$show = (isset($_REQUEST['show']) && in_array($_REQUEST['show'], array('visible', 'checked', 'unchecked', 'spam', 'trash'))) ? $_REQUEST['show'] : 'all';

		//  If Akimet has not been activated yet and the user is looking at the spam tell him to activate Akismet.
		if ($show == 'spam' && get_option('gwolle_gb-akismet-active') != 'true') {
			$showMsg = 'akismet-not-activated';
		}

		// Check if the requested page number is an integer > 0
		$pageNum = (isset($_REQUEST['pageNum']) && $_REQUEST['pageNum'] && (int)$_REQUEST['pageNum'] > 0) ? (int)$_REQUEST['pageNum'] : 1;

		// Pagination: Calculate the number of pages.
		$countPages = round($count[$show] / 15);
		if ($countPages * get_option('gwolle_gb-entries_per_page', 15) < $count[$show]) {
			$countPages++;
		}

		if ($pageNum > $countPages) {
			$pageNum = 1;
		}

		if ($pageNum == 1 && $count[$show] > 0) {
			$firstEntryNum = 1;
			$mysqlFirstRow = 0;
		} elseif ($count[$show] == 0) {
			$firstEntryNum = 0;
			$mysqlFirstRow = 0;
		} else {
			$firstEntryNum = ($pageNum - 1) * get_option('gwolle_gb-entries_per_page', 15) + 1;
			$mysqlFirstRow = $firstEntryNum - 1;
		}

		$lastEntryNum = $pageNum * get_option('gwolle_gb-entries_per_page');
		if ($count[$show] == 0) {
			$lastEntryNum = 0;
		} elseif ($lastEntryNum > $count[$show]) {
			$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum - 1) * get_option('gwolle_gb-entries_per_page')) - 1;
		}

		// Get the entries
		$entries = gwolle_gb_get_entries_old(array('offset' => $mysqlFirstRow, 'show' => $show));
		?>

		<div class="wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Guestbook entries', GWOLLE_GB_TEXTDOMAIN); ?></h2>
			<?php include (GWOLLE_GB_DIR . '/msg.php'); ?>

			<form action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page']; ?>&amp;do=massEdit" method="POST">
				<!-- the following fields give us some information we're going to use processing the mass edit -->
				<input type="hidden" name="pageNum" value="<?php echo $pageNum; ?>">
				<input type="hidden" name="entriesOnThisPage" value="<?php $wpdb -> num_rows; ?>">
				<input type="hidden" name="show" value="<?php echo $show; ?>">

				<ul class="subsubsub">
					<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php' <?php
						if ($show == 'all') { echo 'class="current"'; }
						?>>
						<?php _e('All', GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['all']; ?>)</span></a> |
					</li>
					<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked' <?php
						if ($show == 'checked') { echo 'class="current"'; }
						?>>
						<?php _e('Unlocked', GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['checked']; ?>)</span></a> |
					</li>
					<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked' <?php
						if ($show == 'unchecked') { echo 'class="current"'; }
						?>><?php _e('New', GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['unchecked']; ?>)</span></a> |
					</li>
					<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam' <?php
						if ($show == 'spam') { echo 'class="current"'; }
						?>><?php _e('Spam', GWOLLE_GB_TEXTDOMAIN); ?> <span class="count">(<?php echo $count['spam']; ?>)</span></a> |
					</li>
					<li><a href='admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=trash' <?php
						if ($show == 'trash') { echo 'class="current"'; }
						?>><?php _e('Trash'); ?> <span class="count">(<?php echo $count['trash']; ?>)</span></a>
					</li>
				</ul>
				<div class="tablenav">
					<div class="alignleft actions">
						<?php
						$massEditControls = '<select name="massEditAction1">';
						$massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions', GWOLLE_GB_TEXTDOMAIN) . '</option>';
						if ($show == 'trash') {
							$massEditControls .= '
								<option value="untrash">Widerherstellen</option>
								<option value="remove">Endgültig entfernen</option>';
						} else {
							if ($show != 'checked') {
								$massEditControls .= '<option value="check">' . __('Mark as checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'unchecked') {
								$massEditControls .= '<option value="uncheck">' . __('Mark as not checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'spam') {
								$massEditControls .= '<option value="spam">' . __('Mark as spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							$massEditControls .= '<option value="no-spam">' . __('Mark as not spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							$massEditControls .= '<option value="delete">' . __('Move to Trash', GWOLLE_GB_TEXTDOMAIN) . '</option>';
						}
						$massEditControls .= '</select>';
						$massEditControls .= '<input type="submit" value="' . __('Apply', GWOLLE_GB_TEXTDOMAIN) . '" name="doaction" id="doaction" class="button-secondary action" />';
						// It makes no sense to show the mass edit controls when there are no entries to edit. ;)
						if ($entries !== FALSE) {
							echo $massEditControls;
						}
						?>
					</div>

					<div class="tablenav-pages">
						<span class="displaying-num"><?php _e('Showing:', GWOLLE_GB_TEXTDOMAIN);
							echo ' ' . $firstEntryNum . ' &#8211; ' . $lastEntryNum . ' ' . __('of', GWOLLE_GB_TEXTDOMAIN) . ' ' . $count[$show]; ?>
						</span>
						<?php
						if ($pageNum > 1) {
							echo '<a class="first page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum - 1) . '">&laquo;</a>';
						}
						if ($pageNum < 5) {
							if ($countPages < 4) {
								$showRange = $countPages;
							} else {
								$showRange = 6;
							}
							for ($i = 1; $i < $showRange; $i++) {
								if ($i == $pageNum) {
									echo '<span class="page-numbers current">' . $i . '</span>';
								} else {
									echo '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
								}
							}

							if ($pageNum + 4 < $countPages) {
								$highDotsMade = true;
								//	The dots next to the highest number have already been put out.
								echo '<span class="page-numbers dots">...</span>';
							}
						} elseif ($pageNum >= 5) {
							echo '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=1">1</a>';
							if ($countPages > 5) {
								echo '<span class="page-numbers dots">...</span>';
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
									echo '<span class="page-numbers current">' . $i . '</span>';
								} else {
									echo '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $i . '">' . $i . '</a>';
								}
							}
							if ($pageNum == $countPages) {
								echo '<span class="page-numbers current">' . $pageNum . '</span>';
							}
						}

						if ($pageNum < $countPages) {
							if (($pageNum + 4 < $countPages) && !$highDotsMade) {
								echo '<span class="page-numbers dots">...</span>';
								$highDotsMade = true;
							}
							if ( isset($highDotsMade) ) {
								echo '<a class="page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . $countPages . '">' . $countPages . '</a>';
							}
							echo '<a class="last page-numbers" href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&show=' . $show . '&pageNum=' . round($pageNum + 1) . '">&raquo;</a>';
						}
						?>
					</div>

					<table class="widefat">
						<thead>
							<tr>
								<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-top" id="check-all-top" type="checkbox"></th>
								<th scope="col" ><?php _e('ID', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<?php
								if (get_option('gwolle_gb-showEntryIcons') === 'true' && $show !== 'trash') { ?>
									<th scope="col">&nbsp;</th><!-- this is the icon-column -->
								<?php
								} ?>
								<th scope="col" ><?php _e('Date', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Entry (excerpt)', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Action', GWOLLE_GB_TEXTDOMAIN); ?></th>
							</tr>
						</thead>

						<tfoot>
							<tr>
								<th scope="col" class="manage-column column-cb check-column"><input style="display:none;" name="check-all-bottom" id="check-all-bottom" type="checkbox"></th>
								<th scope="col" ><?php _e('ID', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<?php
								if (get_option('gwolle_gb-showEntryIcons') === 'true' && $show !== 'trash') { ?>
									<th scope="col">&nbsp;</th><!-- this is the icon-column -->
								<?php
								} ?>
								<th scope="col" ><?php _e('Date', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Entry (excerpt)', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Action', GWOLLE_GB_TEXTDOMAIN); ?></th>
							</tr>
						</tfoot>


						<tbody>
							<?php $rowOdd = true;
							$html_output = '';
							if ($entries === FALSE) {
								$colspan = (get_option('gwolle_gb-showEntryIcons') === 'true') ? 7 : 6;
								$html_output .= '
									<tr>
										<td colspan="' . $colspan . '" align="center">
											<strong>' . __('No entries found.', GWOLLE_GB_TEXTDOMAIN) . '</strong>
										</td>
									</tr>';
							} else {
								foreach ($entries as $entry) {

									//	rows have a different color.
									if ($rowOdd) {
										$rowOdd = false;
										$class = ' alternate';
									} else {
										$rowOdd = true;
										$class = '';
									}

									//  Attach 'spam' to class if the entry's spam
									if ($entry['entry_isSpam'] === 1) {
										$class .= ' spam';
									}

									$html_output .= '
										<tr id="entry_' . $entry['entry_id'] . '" class="entry ' . $class . '">
											<td class="check">
												<input name="check-' . $entry['entry_id'] . '" id="check-' . $entry['entry_id'] . '" type="checkbox">
											</td>
										<td class="id">' . $entry['entry_id'] . '</td>';
									if (get_option('gwolle_gb-showEntryIcons') === TRUE && $show !== 'trash') {
										$html_output .= '
											<td class="entry-' . $entry['icon_class'] . '">&nbsp;</td>';
									}
									// FIXME: use date_i18n for localised date, see frontend/read.php
									// FIXME: add option to show time as well
									$html_output .= '
										<td>' . $entry['entry_date_html'] . '</td>
										<td>' . $entry['spam_icon'] . '
											<label for="check-' . $entry['entry_id'] . '">' . $entry['excerpt'] . '</label>
										</td>
										<td>' . $entry['entry_author_name_html'] . '</td>
										<td>';
									if ($show == 'trash') {
										$html_output .= '
											<a href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&gwolle_gb_function=untrash_entry&entry_id=' . $entry['entry_id'] . '&show=trash">' . __('Recover', GWOLLE_GB_TEXTDOMAIN) . '</a>
											<a href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&gwolle_gb_function=delete_entry&entry_id=' . $entry['entry_id'] . '&show=trash" onClick="return confirm(\'' . __("You are about to delete this guestbook entry. This can not be undone. Are you still sure you want to continue?", GWOLLE_GB_TEXTDOMAIN) . '\');">' . __('Delete', GWOLLE_GB_TEXTDOMAIN) . '</a>';
									} else {
										$html_output .= '
											<a href="' . $_SERVER['PHP_SELF'] . '?page=' . GWOLLE_GB_FOLDER . '/editor.php&amp;entry_id=' . $entry['entry_id'] . '">' . __('Details', GWOLLE_GB_TEXTDOMAIN) . '&nbsp;&raquo;</a>&nbsp;';
									}
									echo '</td></tr>';

									// Quick-Editor
									/*
									 echo '
									 <tr style="display:none;" class="inline-edit-row inline-edit-row-post quick-edit-row quick-edit-row-post alternate inline-editor" id="quickedit_'.$entry['entry_id'].'">
									 <td style="border-top:0px;" colspan="'; if (get_option('gwolle_gb-showEntryIcons')) { echo 7; } else { echo 6; } echo '">
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
							$massEditControls .= '<option value="-1" selected="selected">' . __('Mass edit actions', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							if ($show != 'checked') {
								$massEditControls .= '<option value="check">' . __('Mark as checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'unchecked') {
								$massEditControls .= '<option value="uncheck">' . __('Mark as not checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'spam') {
								$massEditControls .= '<option value="spam">' . __('Mark as spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							$massEditControls .= '<option value="no-spam">' . __('Mark as not spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							$massEditControls .= '<option value="delete">' . __('Move to Trash', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							$massEditControls .= '</select>';
							$massEditControls .= '<input type="submit" value="' . __('Apply', GWOLLE_GB_TEXTDOMAIN) . '" name="doaction" id="doaction" class="button-secondary action" />';
							// It makes no sense to show the mass edit controls when there are no entries to edit. ;)
							if ($entries !== FALSE) {
								echo $massEditControls;
							}
							?>
							<br class="clear" />
						</div>
						<br class="clear" />
					</div>

				</div>
			</form>

		</div>

		<?php
	}
}


