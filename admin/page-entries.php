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
		// FIXME: do this on activation
		gwolle_gb_installSplash();
	} else {
		if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		$gwolle_gb_errors = '';
		$gwolle_gb_messages = '';

		// FIXME: use the right pagename
		if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) { // different names
			if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
				die(__('Cheatin&#8217; uh?'));
			}

			// FIXME; put here the posthandling from ...

			$entriesEdited = 0;
			foreach( array_keys($_POST) as $postElementName ) {
				if (strpos($postElementName, 'check') > -1 && !strpos($postElementName, '-all-') && $_POST[$postElementName] == 'on') {
					$entry_id = str_replace('check-','',$postElementName);
				}
				$entriesEdited++;
			}


		}


		// Get entry counts
		$count = Array();
		$count['checked'] = gwolle_gb_get_entry_count(
			array(
				'checked' => 'checked',
				'deleted' => 'notdeleted',
				'spam'    => 'nospam'
			)
		);
		$count['unchecked'] = gwolle_gb_get_entry_count(array(
				'checked' => 'unchecked',
				'deleted' => 'notdeleted',
				'spam'    => 'nospam'
			));
		$count['spam']  = gwolle_gb_get_entry_count(array( 'spam' => 'spam' ));
		$count['trash'] = gwolle_gb_get_entry_count(array( 'deleted' => 'deleted' ));
		$count['all']   = gwolle_gb_get_entry_count(array( 'all' => 'all' ));


		$show = (isset($_REQUEST['show']) && in_array($_REQUEST['show'], array('checked', 'unchecked', 'spam', 'trash'))) ? $_REQUEST['show'] : 'all';

		//  If Akimet has not been activated yet and the user is looking at the spam tell him to activate Akismet.
		if ($show == 'spam' && get_option('gwolle_gb-akismet-active') != 'true') {
			$gwolle_gb_messages = '<p>' . __('Please activate Akismet if you want to battle spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
		}

		// Check if the requested page number is an integer > 0
		$pageNum = (isset($_REQUEST['pageNum']) && $_REQUEST['pageNum'] && (int) $_REQUEST['pageNum'] > 0) ? (int) $_REQUEST['pageNum'] : 1;

		// Pagination: Calculate the number of pages.
		$countPages = ceil( $count[$show] / get_option('gwolle_gb-entries_per_page', 15) );

		if ($pageNum > $countPages) {
			$pageNum = 1; // page doesnot exist, return to first page
		}

		// Calculate entry-args for query
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

		// Calculate written text with info "Showing 1 – 25 of 54"
		$lastEntryNum = $pageNum * get_option('gwolle_gb-entries_per_page');
		if ($count[$show] == 0) {
			$lastEntryNum = 0;
		} elseif ($lastEntryNum > $count[$show]) {
			$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum - 1) * get_option('gwolle_gb-entries_per_page')) - 1;
		}

		if ( WP_DEBUG ) { echo "mysqlFirstRow on $show: "; var_dump($mysqlFirstRow); }
		// FIXME: buggy paging on page 1 - 4 of "all"
		// Get the entries
		if ( $show == 'checked' ) {
			$entries = gwolle_gb_get_entries(array(
				'num_entries' => get_option('gwolle_gb-entries_per_page', 20),
				'offset'  => $mysqlFirstRow,
				'checked' => 'checked',
				'deleted' => 'notdeleted',
				'spam'    => 'nospam'
			));
		} else if ( $show == 'unchecked' ) {
			$entries = gwolle_gb_get_entries(array(
				'num_entries' => get_option('gwolle_gb-entries_per_page', 20),
				'offset'  => $mysqlFirstRow,
				'checked' => 'unchecked',
				'deleted' => 'notdeleted',
				'spam'    => 'nospam'
			));
		} else if ( $show == 'spam' ) {
			$entries = gwolle_gb_get_entries(array(
				'num_entries' => get_option('gwolle_gb-entries_per_page', 20),
				'offset'  => $mysqlFirstRow,
				'spam' => 'spam'
			));
		} else if ( $show == 'trash' ) {
			$entries = gwolle_gb_get_entries(array(
				'num_entries' => get_option('gwolle_gb-entries_per_page', 20),
				'offset'  => $mysqlFirstRow,
				'deleted' => 'deleted'
			));
		} else {
			$entries = gwolle_gb_get_entries(array(
				'num_entries' => get_option('gwolle_gb-entries_per_page', 20),
				'offset'  => $mysqlFirstRow,
				'all' => 'all'
			));
		}
		?>

		<div class="wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Guestbook entries', GWOLLE_GB_TEXTDOMAIN); ?></h2>

			<?php
			if ( $gwolle_gb_messages ) {
				echo '
					<div id="message" class="updated fade ' . $gwolle_gb_errors . ' ">' .
						$gwolle_gb_messages .
					'</div>';
			}
			// FIXME: add a searchform someday? ?>

			<form name="gwolle_gb_entries" action="" method="POST">
				<!-- the following fields give us some information we're going to use processing the mass edit -->
				<input type="hidden" name="pageNum" value="<?php echo $pageNum; ?>">
				<input type="hidden" name="entriesOnThisPage" value="<?php $wpdb->num_rows; ?>">
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
						$massEditControls_select = '<select name="massEditAction1">';
						$massEditControls = '<option value="-1" selected="selected">' . __('Mass edit actions', GWOLLE_GB_TEXTDOMAIN) . '</option>';
						if ($show == 'trash') {
							$massEditControls .= '
								<option value="untrash">' . __('Recover from Trash', GWOLLE_GB_TEXTDOMAIN) . '</option>
								<option value="remove">' . __('Remove Permanently', GWOLLE_GB_TEXTDOMAIN) . '</option>';
						} else {
							if ($show != 'checked') {
								$massEditControls .= '<option value="check">' . __('Mark as Checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'unchecked') {
								$massEditControls .= '<option value="uncheck">' . __('Mark as not Checked', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							if ($show != 'spam') {
								$massEditControls .= '<option value="spam">' . __('Mark as Spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							$massEditControls .= '<option value="no-spam">' . __('Mark as not Spam', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							if ( get_option('gwolle_gb-akismet-active') == 'true' ) {
								$massEditControls .= '<option value="akismet">' . __('Check with Akismet', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							$massEditControls .= '<option value="trash">' . __('Move to Trash', GWOLLE_GB_TEXTDOMAIN) . '</option>';
						}
						$massEditControls .= '</select>';
						$massEditControls .= '<input type="submit" value="' . __('Apply', GWOLLE_GB_TEXTDOMAIN) . '" name="doaction" id="doaction" class="button-secondary action" />';
						// It makes no sense to show the mass edit controls when there are no entries to edit. ;)
						if ( is_array($entries) && count($entries) > 0 ) {
							echo $massEditControls_select . $massEditControls;
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
							for ($i = 1; $i < ($showRange + 1); $i++) {
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
								<th scope="col" ><?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?></th>
								<th scope="col" ><?php _e('Entry (excerpt)', GWOLLE_GB_TEXTDOMAIN); ?></th>
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
							if ( !is_array($entries) || count($entries) === 0 ) {
								$colspan = (get_option('gwolle_gb-showEntryIcons') === 'true') ? 7 : 6;
								$html_output .= '
									<tr>
										<td colspan="' . $colspan . '" align="center">
											<strong>' . __('No entries found.', GWOLLE_GB_TEXTDOMAIN) . '</strong>
										</td>
									</tr>';
							} else {
								foreach ($entries as $entry) {

									// rows have a different color.
									if ($rowOdd) {
										$rowOdd = false;
										$class = ' alternate';
									} else {
										$rowOdd = true;
										$class = '';
									}

									// Attach 'spam' to class if the entry is spam
									if ( $entry->get_isspam() === 1 ) {
										$class .= ' spam';
									}

									// Attach 'trash' to class if the entry is in trash
									if ( $entry->get_isdeleted() === 1 ) {
										$class .= ' trash';
									}

									// Attach 'visible/invisible' to class
									if ( $entry->get_isspam() === 1 || $entry->get_isdeleted() === 1 || $entry->get_ischecked() === 0 ) {
										$class .= ' invisible';
									} else {
										$class .= ' visible';
									}


									// Checkbox and ID columns
									$html_output .= '
										<tr id="entry_' . $entry->get_id() . '" class="entry ' . $class . '">
											<td class="check">
												<input name="check-' . $entry->get_id() . '" id="check-' . $entry->get_id() . '" type="checkbox">
											</td>
											<td class="id">' . $entry->get_id() . '</td>';

									// Optional Icon column where CSS is being used to show them or not
									if ( get_option('gwolle_gb-showEntryIcons') === 'true' ) {
										$html_output .= '
											<td class="entry-icons">
												<span class="visible-icon"></span>
												<span class="invisible-icon"></span>
												<span class="spam-icon"></span>
												<span class="trash-icon"></span>
											</td>';
									}

									// Date column
									$html_output .= '
										<td>' . date_i18n( get_option('date_format'), $entry->get_date() ) . ', ' .
											date_i18n( get_option('time_format'), $entry->get_date() ) .
										'</td>';

									// Author column
									$author_name_html = gwolle_gb_get_author_name_html($entry);
									$html_output .= '
										<td><span class="author-name">' . $author_name_html . '</span>' .
										'</td>';

									// Excerpt column
									$html_output .= '
										<td>
											<label for="check-' . $entry->get_id() . '">';
									$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), 100 );
									if ( get_option('gwolle_gb-showSmilies') === 'true' ) {
										$entry_content = convert_smilies($entry_content);
									}
									$html_output .= $entry_content . '</label>
										</td>';

									// Actions column
									$html_output .= '
										<td>';
									// disabled for now, never use GET for deleting
									/*if ($show == 'trash__') {
										$html_output .= '
											<a href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&gwolle_gb_function=untrash_entry&entry_id=' . $entry->get_id() . '&show=trash">' . __('Recover', GWOLLE_GB_TEXTDOMAIN) . '</a>
											<a href="admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&gwolle_gb_function=delete_entry&entry_id=' . $entry->get_id() . '&show=trash" onClick="return confirm(\'' . __("You are about to delete this guestbook entry. This can not be undone. Are you still sure you want to continue?", GWOLLE_GB_TEXTDOMAIN) . '\');">' . __('Delete', GWOLLE_GB_TEXTDOMAIN) . '</a>';
									} else {*/
										$html_output .= '
											<a href="' . $_SERVER['PHP_SELF'] . '?page=' . GWOLLE_GB_FOLDER . '/editor.php&amp;entry_id=' . $entry->get_id() . '">' . __('Details', GWOLLE_GB_TEXTDOMAIN) . '&nbsp;&raquo;</a>&nbsp;';
									//}
									echo '</td></tr>';

								}
							}
							echo $html_output;
							?>
						</tbody>

					</table>

					<div class="tablenav">
						<div class="alignleft actions">
							<?php
							$massEditControls_select = '<select name="massEditAction2">';
							// It makes no sense to show the mass edit controls when there are no entries to edit. ;)
							if ( is_array($entries) && count($entries) > 0 ) {
								echo $massEditControls_select . $massEditControls;
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


