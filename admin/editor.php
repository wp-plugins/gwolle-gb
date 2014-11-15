<?php
/*
 * Editor for editing entries and writing admin entries.
 */

//	No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_editor() {
	global $wpdb, $current_user;
	if (!get_option('gwolle_gb_version')) {
		gwolle_gb_installSplash();
	} else {
		if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		// FIXME: use the right pagename
		if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) { // different names
			if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
				die(__('Cheatin&#8217; uh?'));
			}
		}

		// FIXME; put here the posthandling from the do- files





		// If a entry_id has been submitted, check if it's a valid one.
		$gwolle_gb_function = 'add_entry';
		$entry_id = '';
		$entry = FALSE;

		if (isset($_REQUEST['entry_id'])) {
			$entry = gwolle_gb_get_entries_old(array('entry_id' => $_REQUEST['entry_id']));
			if ($entry !== FALSE) {
				$sectionHeading = __('Edit guestbook entry', GWOLLE_GB_TEXTDOMAIN);
				$gwolle_gb_function = 'edit_entry';
				$entry_id = $entry['entry_id'];
			} else {
				$errorMsg = __('Entry could not be found.', GWOLLE_GB_TEXTDOMAIN);
			}
		} else {
			$sectionHeading = __('New guestbook entry', GWOLLE_GB_TEXTDOMAIN);
		}

		if ($entry === FALSE) {
			$entry['entry_content'] = (isset($_SESSION['gwolle_gb']['entry']['content'])) ? $_SESSION['gwolle_gb']['entry']['content'] : '';
			$entry['entry_author_website'] = (isset($_SESSION['gwolle_gb']['entry']['website'])) ? $_SESSION['gwolle_gb']['entry']['website'] : '';
			$entry['entry_author_origin'] = (isset($_SESSION['gwolle_gb']['entry']['origin'])) ? $_SESSION['gwolle_gb']['entry']['origin'] : '';
		}
		?>

		<div class="wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php echo $sectionHeading; ?></h2>
			<?php
			include (GWOLLE_GB_DIR . '/msg.php');
			?>

			<form name="gwolle_gb_editor" id="gwolle_gb_editor" method="POST" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/gwolle-gb.php" accept-charset="UTF-8">
				<input type="hidden" name="gwolle_gb_function" value="<?php echo $gwolle_gb_function; ?>" />
				<input type="hidden" name="entry_id" value="<?php echo $entry['entry_id']; ?>" />
				<input type="hidden" name="return_to" value="entries" />

				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<div id='side-sortables' class='meta-box-sortables'>

							<div id="submitdiv" class="postbox">
								<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
								<h3 class='hndle'><span><?php _e('Options', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="submitbox" id="submitpost">
										<div id="minor-publishing">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section misc-pub-section-last">

													<?php // FIXME: redo these 2 sections */ ?>
													<label for="entry_isChecked" class="selectit">
														<input id="entry_isChecked" name="entry_isChecked" type="checkbox" <?php
															if ($entry['entry_isChecked'] == '1') { echo 'checked="checked"';}
															?> /> <?php _e('This entry is checked.', GWOLLE_GB_TEXTDOMAIN); ?>
													</label>
													<br />
													<span><?php
														if ($entry['entry_isSpam'] == '0') {
															_e('This entry is marked as Not Spam', GWOLLE_GB_TEXTDOMAIN);
														} else {
															_e('This entry is marked as Spam', GWOLLE_GB_TEXTDOMAIN);
														} ?>
													</span>

												</div>
											</div><!-- 'misc-publishing-actions' -->
											<div class="clear"></div>
										</div> <!-- minor-publishing -->

										<div id="major-publishing-actions">
											<div id="publishing-action">
												<?php
												if ($entry['entry_id']) { ?>
													<a class="submitdelete deletion"
														href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;gwolle_gb_function=trash_entry&amp;entry_id=<?php echo $entry['entry_id']; ?>"
														>
														<?php _e('Trash'); ?>
													</a>
													&nbsp;
													<?php
													if ($entry['entry_isSpam'] == '0') {
														?>
														<a class="submitdelete deletion"
															href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php&amp;gwolle_gb_function=mark_spam&amp;entry_id=<?php echo $entry['entry_id']; ?>&amp;show=editor"
															onClick="return confirm('<?php _e("You\'re about to mark this guestbook entry as spam. It will be sent to the Akismet team to help other people fighting spam. Entries marked as spam are automatically deleted after 15 days. Continue?", GWOLLE_GB_TEXTDOMAIN); ?>');"
															>
															<?php _e('Mark as Spam', GWOLLE_GB_TEXTDOMAIN); ?>
														</a>
														<?php
													} else {
														?>
														<a class="submitdelete deletion"
															href="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php&amp;gwolle_gb_function=unmark_spam&amp;entry_id=<?php echo $entry['entry_id']; ?>&amp;show=editor"
															onClick="return confirm('<?php _e("A message will be sent to the Akismet team that this entry is not spam. Continue?", GWOLLE_GB_TEXTDOMAIN); ?>');"
															>
															<?php _e('Unmark as Spam', GWOLLE_GB_TEXTDOMAIN); ?>
														</a>
														<?php
													}
												} ?>
												&nbsp;
												<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php _e('Save', GWOLLE_GB_TEXTDOMAIN); ?>" />
											</div> <!-- publishing-action -->
											<div class="clear"></div>
										</div><!-- 'major-publishing-actions' -->
									</div><!-- 'submitbox' -->
								</div><!-- 'inside' -->
							</div><!-- 'submitdiv' -->

							<div id="gwolle_gb-entry-details" class="postbox " >
								<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
								<h3 class='hndle'><span><?php _e('Details', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<p>
										<?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if ($entry['entry_author_name']) {
												echo stripslashes(htmlentities($entry['entry_author_name']));
											} else {
												echo '<strong>' . __('You', GWOLLE_GB_TEXTDOMAIN) . '</strong>';
											} ?>
										</span>
										<br />
										<?php _e('E-Mail', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen(str_replace(' ', '', $entry['entry_author_email'])) > 0) {
												echo stripslashes(htmlentities($entry['entry_author_email']));
											} else {
												echo '<i>(' . __('unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										<br />
										<?php _e('Written', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if ($entry['entry_date'] > 0) {
												echo date('d.m.Y, H:i', $entry['entry_date']) . ' ' . __("o'clock", GWOLLE_GB_TEXTDOMAIN);
											} else {
												echo '(' . __('not yet', GWOLLE_GB_TEXTDOMAIN) . ')';
											} ?>
										</span>
										<br />
										<?php _e("Author's IP-address", GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen($entry['entry_author_ip']) > 0) {
												echo '<a href="http://www.db.ripe.net/whois?form_type=simple&searchtext=' . $entry['entry_author_ip'] . '"
														title="' . __('Whois search for this IP', GWOLLE_GB_TEXTDOMAIN) . '" target="_blank">
															' . $entry['entry_author_ip'] . '
														</a>';
											} else {
												echo '<i>(' . __('unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										<br />
										<?php _e('Host', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen($entry['entry_author_host']) > 0) {
												echo $entry['entry_author_host'];
											} else {
												echo '<i>(' . __('unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										</p>
									</div> <!-- tagsdiv -->
								</div>
							</div><!-- postbox -->

							<div id="tagsdiv-post_tag" class="postbox">
								<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
								<h3 class='hndle'><span><?php _e('Entry log', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<div id="categories-pop" class="tabs-panel" style="max-height:400px;overflow:auto;"> <?php /* FIXME: place in CSS file */ ?>
											<ul>
											<?php
											if ($entry['entry_date'] > 0) {
												echo '<li>' . date('d.m.Y', $entry['entry_date']) . ': ' . __('Written', GWOLLE_GB_TEXTDOMAIN) . '</li>';

												$log_entries = gwolle_gb_get_log_entries(array('subject_id' => $entry['entry_id']));
												if ($log_entries !== FALSE) {
													foreach ($log_entries as $log_entry) {
														echo '<li>' . $log_entry['msg_html'] . '</li>';
													}
												}
											} else {
												echo '<li>(' . __('No entries yet.', GWOLLE_GB_TEXTDOMAIN) . ')</li>';
											}
											?>
											</ul>
										</div>
									</div>
								</div>
							</div><!-- postbox -->
						</div><!-- 'side-sortables' -->
					</div><!-- 'side-info-column' -->

					<div id="post-body">
						<div id="post-body-content">
							<div id='normal-sortables' class='meta-box-sortables'>
								<div id="authordiv" class="postbox " >
									<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
									<h3 class='hndle'><span><?php _e('Guestbook entry', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<textarea rows="10" cols="56" name="entry_content" tabindex="1"><?php echo gwolle_gb_output_to_input_field($entry['entry_content']); ?></textarea>
										<?php
										if (get_option('gwolle_gb-showLineBreaks') == 'false') {
											echo '<p>' . str_replace('%1', 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php', __('Line breaks will not be visible to the visitors due to your <a href="%1">settings</a>.', GWOLLE_GB_TEXTDOMAIN)) . '</p>';
										} ?>
									</div>
								</div>
								<div id="authordiv" class="postbox " >
									<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
									<h3 class='hndle'><span><?php _e('Homepage', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="entry_author_website" size="58" tabindex="2" value="<?php echo gwolle_gb_output_to_input_field($entry['entry_author_website']); ?>" id="entry_author_website" />
										<p><?php _e("Example: <code>http://www.google.com/</code> &#8212; don't forget the <code>http://</code>!", GWOLLE_GB_TEXTDOMAIN); ?></p>
									</div>
								</div>
								<div id="authordiv" class="postbox ">
									<div class="handlediv" title="Klicken zum Umschalten"><br /></div>
									<h3 class='hndle'><span><?php _e('Origin', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="entry_author_origin" size="58" tabindex="3" value="<?php echo gwolle_gb_output_to_input_field($entry['entry_author_origin']); ?>" id="entry_author_origin" />
									</div>
								</div>
							</div><!-- 'normal-sortables' -->
						</div><!-- 'post-body-content' -->
					</div>
					<br class="clear" />
				</div><!-- /poststuff -->
			</form>
		</div>

		<?php
	}
}

