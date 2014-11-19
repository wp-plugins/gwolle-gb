<?php
/*
 * entries.php
 * Displays the guestbook entries in a list.
 */

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_entries() {
	global $wpdb;

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?'));
	}

	if (!get_option('gwolle_gb_version')) {
		// FIXME: do this on activation
		gwolle_gb_installSplash();
	} else {

		$gwolle_gb_errors = '';
		$gwolle_gb_messages = '';

		if ( isset($_POST['gwolle_gb_page']) && $_POST['gwolle_gb_page'] == 'entries' ) {
			$action = '';
			if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'check' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'check' ) ) {
				$action = 'check';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'uncheck' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'uncheck' ) ) {
				$action = 'uncheck';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'spam' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'spam' ) ) {
				$action = 'spam';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'no-spam' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'no-spam' ) ) {
				$action = 'no-spam';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'akismet' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'akismet' ) ) {
				$action = 'akismet';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'trash' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'trash' ) ) {
				$action = 'trash';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'untrash' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'untrash' ) ) {
				$action = 'untrash';
			} else if ( ( isset($_POST['massEditAction1']) && $_POST['massEditAction1'] == 'remove' ) || ( isset($_POST['massEditAction2']) && $_POST['massEditAction2'] == 'remove' ) ) {
				$action = 'remove';
			}

			if ( $action != '' ) {
				// Initialize variables to generate messages with
				$entries_handled = 0;
				$entries_not_handled = 0;
				$akismet_spam = 0;
				$akismet_not_spam = 0;
				$akismet_already_spam = 0;
				$akismet_already_not_spam = 0;

				/* Handle the $_POST entries */
				foreach( array_keys($_POST) as $postElementName ) {
					if (strpos($postElementName, 'check') > -1 && !strpos($postElementName, '-all-') && $_POST[$postElementName] == 'on') {
						$entry_id = str_replace('check-','',$postElementName);
						$entry_id = intval($entry_id);
						if ( isset($entry_id) && $entry_id > 0 ) {
							$entry = new gwolle_gb_entry();
							$result = $entry->load( $entry_id );
							if ( $result ) {

								if ( $action == 'check' ) {
									if ( $entry->get_ischecked() == 0 ) {
										$entry->set_ischecked( true );
										$user_id = get_current_user_id(); // returns 0 if no current user
										$entry->set_checkedby( $user_id );
										gwolle_gb_add_log_entry( $entry->get_id(), 'entry-checked' );
										$result = $entry->save();
										if ($result ) {
											$entries_handled++;
										} else {
											$entries_not_handled++;
										}
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'uncheck' ) {
									if ( $entry->get_ischecked() == 1 ) {
										$entry->set_ischecked( false );
										$user_id = get_current_user_id(); // returns 0 if no current user
										$entry->set_checkedby( $user_id );
										gwolle_gb_add_log_entry( $entry->get_id(), 'entry-unchecked' );
										$result = $entry->save();
										if ($result ) {
											$entries_handled++;
										} else {
											$entries_not_handled++;
										}
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'spam' ) {
									if ( $entry->get_isspam() == 0 ) {
										$entry->set_isspam( true );
										gwolle_gb_akismet( $entry, 'submit-spam' );
										gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-spam' );
										$entries_handled++;
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'no-spam' ) {
									if ( $entry->get_isspam() == 1 ) {
										$entry->set_isspam( false );
										gwolle_gb_akismet( $entry, 'submit-ham' );
										gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-not-spam' );
										$result = $entry->save();
										if ($result ) {
											$entries_handled++;
										} else {
											$entries_not_handled++;
										}
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'akismet' ) {
									/* Check for spam and set accordingly */
									$isspam = gwolle_gb_akismet( $entry, 'comment-check' );
									if ( $isspam ) {
										// Returned true, so considered spam
										if ( $entry->get_isspam() == 0 ) {
											$entry->set_isspam( true );
											gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-spam' );
											$result = $entry->save();
											if ($result ) {
												$akismet_spam++;
											} else {
												$akismet_not_spam++;
											}
										} else {
											$akismet_already_spam++;
										}
									} else {
										if ( $entry->get_isspam() == 1 ) {
											$entry->set_isspam( false );
											gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-not-spam' );
											$result = $entry->save();
											if ($result ) {
												$akismet_not_spam++;
											} else {
												$akismet_spam++;
											}
										} else {
											$akismet_already_not_spam++;
										}
									}
								} else if ( $action == 'trash' ) {
									if ( $entry->get_isdeleted() == 0 ) {
										$entry->set_isdeleted( true );
										gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
										$result = $entry->save();
										if ($result ) {
											$entries_handled++;
										} else {
											$entries_not_handled++;
										}
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'untrash' ) {
									if ( $entry->get_isdeleted() == 1 ) {
										$entry->set_isdeleted( false );
										gwolle_gb_add_log_entry( $entry->get_id(), 'entry-untrashed' );
										$result = $entry->save();
										if ($result ) {
											$entries_handled++;
										} else {
											$entries_not_handled++;
										}
									} else {
										$entries_not_handled++;
									}
								} else if ( $action == 'remove' ) {
									// FIXME: Stub
								}
							} else { // no result on load()
								$entries_not_handled++;
							}
						} else { // entry_id is not set or not > 0
							$entries_not_handled++;
						}
					} // no entry with the check-'entry_id' input, continue
				} // foreach


				/* Construct Message */
				if ( $action == 'check' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry checked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries checked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries checked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'uncheck' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry unchecked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries unchecked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries unchecked.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'spam' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry marked as spam and submitted to Akismet as spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries marked as spam and submitted to Akismet as spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries marked as spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'no-spam' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry marked as not spam and submitted to Akismet as ham.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries marked as not spam and submitted to Akismet as ham.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries marked as not spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'akismet' ) {
					if ( $akismet_spam == 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_spam . " " . __('entry considered spam and marked as such.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $akismet_spam > 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_spam . " " . __('entries considered spam and marked as such.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
					if ( $akismet_not_spam == 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_not_spam . " " . __('entry not considered spam and marked as such.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $akismet_not_spam > 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_not_spam . " " . __('entries not considered spam and marked as such.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
					if ( $akismet_already_spam == 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_already_spam . " " . __('entry already considered spam and not changed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $akismet_already_spam > 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_already_spam . " " . __('entries already considered spam and not changed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
					if ( $akismet_already_not_spam == 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_already_not_spam . " " . __('entry already considered not spam and not changed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $akismet_already_not_spam > 1 ) {
						$gwolle_gb_messages .= '<p>' . $akismet_already_not_spam . " " . __('entries already considered not spam and not changed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'trash' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry moved to trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries moved to trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries moved to trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'untrash' ) {
					if ( $entries_handled == 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entry recovered from trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else if ( $entries_handled > 1 ) {
						$gwolle_gb_messages .= '<p>' . $entries_handled . " " . __('entries recovered from trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('No entries recovered from trash.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else if ( $action == 'remove' ) {
					// FIXME: Stub
					$gwolle_gb_messages .= '<p>' . __('Removing entries is not yet implemented.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					$gwolle_gb_errors = 'error';
				}
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

		// If Akimet has not been activated yet and the user is looking at the spam tell him to activate Akismet.
		if ($show == 'spam' && get_option('gwolle_gb-akismet-active') != 'true') {
			$gwolle_gb_messages = '<p>' . __('Please activate Akismet if you want to battle spam.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
		}

		// Check if the requested page number is an integer > 0
		$pageNum = (isset($_REQUEST['pageNum']) && $_REQUEST['pageNum'] && (int) $_REQUEST['pageNum'] > 0) ? (int) $_REQUEST['pageNum'] : 1;

		// Pagination: Calculate the number of pages.
		$countPages = ceil( $count[$show] / get_option('gwolle_gb-entries_per_page', 20) );

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
			$firstEntryNum = ($pageNum - 1) * get_option('gwolle_gb-entries_per_page', 20) + 1;
			$mysqlFirstRow = $firstEntryNum - 1;
		}

		// Calculate written text with info "Showing 1 – 25 of 54"
		$lastEntryNum = $pageNum * get_option('gwolle_gb-entries_per_page', 20);
		if ($count[$show] == 0) {
			$lastEntryNum = 0;
		} elseif ($lastEntryNum > $count[$show]) {
			$lastEntryNum = $firstEntryNum + ($count[$show] - ($pageNum - 1) * get_option('gwolle_gb-entries_per_page', 20)) - 1;
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

		<div class="wrap gwolle_gb">
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

			<form name="gwolle_gb_entries" id="gwolle_gb_entries" action="" method="POST" accept-charset="UTF-8">
				<input type="hidden" name="gwolle_gb_page" value="entries" />
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
								<option value="untrash">' . __('Recover from trash', GWOLLE_GB_TEXTDOMAIN) . '</option>
								<option value="remove">' . __('Remove permanently', GWOLLE_GB_TEXTDOMAIN) . '</option>';
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
							if ( get_option('gwolle_gb-akismet-active') == 'true' ) {
								$massEditControls .= '<option value="akismet">' . __('Check with Akismet', GWOLLE_GB_TEXTDOMAIN) . '</option>';
							}
							$massEditControls .= '<option value="trash">' . __('Move to trash', GWOLLE_GB_TEXTDOMAIN) . '</option>';
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
									} */
									$html_output .= '
											<a href="' . $_SERVER['PHP_SELF'] . '?page=' . GWOLLE_GB_FOLDER . '/editor.php&amp;entry_id=' . $entry->get_id() . '">' . __('Details', GWOLLE_GB_TEXTDOMAIN) . '&nbsp;&raquo;</a>&nbsp;
										</td>
									</tr>';
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
							// FIXME: place pagination here as well
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


