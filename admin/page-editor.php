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
		// FIXME: do this on activation
		gwolle_gb_installSplash();
	} else {
		if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		$saved = false;

		if ( isset( $_POST) ) {
			if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
				die(__('Cheatin&#8217; uh?'));
			}


			// FIXME; put here the posthandling from the do- files




			// If a entry_id has been submitted, check if it's a valid one.
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
		}


		$sectionHeading = __('Edit guestbook entry', GWOLLE_GB_TEXTDOMAIN);

		if ( !isset($entry) || !is_object($entry) ) {
			$entry = new gwolle_gb_entry();
			if ( isset($_GET['entry_id']) ) {
				$entry_id = intval($_GET['entry_id']);
				if ($entry_id) {
					$entry->load($entry_id);
				} else {
					$sectionHeading = __('New guestbook entry', GWOLLE_GB_TEXTDOMAIN);
				}
			} else {
				$sectionHeading = __('New guestbook entry', GWOLLE_GB_TEXTDOMAIN);
			}
		}
		if ( WP_DEBUG ) { echo "entry: "; var_dump($entry); }
		?>

		<div class="wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php echo $sectionHeading; ?></h2>

			<?php
			if ( $saved ) {
				echo '
					<div id="message" class="updated fade">
						<p>' . __('Changes saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>
					</div>';
			}
			?>

			<form name="gwolle_gb_editor" id="gwolle_gb_editor" method="POST" action="" accept-charset="UTF-8">
				<input type="hidden" name="gwolle_gb_page" value="editor" />
				<input type="hidden" name="entry_id" value="<?php echo $entry->get_id(); ?>" />

				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<div id='side-sortables' class='meta-box-sortables'>

							<div id="submitdiv" class="postbox">
								<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
								<h3 class='hndle'><span><?php _e('Options', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="submitbox" id="submitpost">
										<div id="minor-publishing">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section misc-pub-section-last">

													<?php
													if ($entry->get_ischecked() == '1' && $entry->get_isspam() == '0' && $entry->get_isdeleted() == '0' ) {
														echo "<h3>" . __('This entry is Visible.', GWOLLE_GB_TEXTDOMAIN) . "</h3>";
													} else {
														echo "<h3>" . __('This entry is Not Visible.', GWOLLE_GB_TEXTDOMAIN) . "</h3>";
													} ?>

													<label for="ischecked" class="selectit">
														<input id="ischecked" name="ischecked" type="checkbox" <?php
															if ($entry->get_ischecked() == '1') {
																echo 'checked="checked"';
															}
															?> />
														<?php
														if ($entry->get_ischecked() == '0') {
															_e('This entry is Not Checked.', GWOLLE_GB_TEXTDOMAIN);
														} else {
															_e('This entry is Checked', GWOLLE_GB_TEXTDOMAIN);
														} ?>
													</label>

													<br />
													<label for="isspam" class="selectit">
														<input id="isspam" name="isspam" type="checkbox" <?php
															if ($entry->get_isspam() == '1') {
																echo 'checked="checked"';
															}
															?> />
														<?php
														if ($entry->get_isspam() == '0') {
															_e('This entry is Not marked as Spam.', GWOLLE_GB_TEXTDOMAIN);
														} else {
															_e('This entry is marked as Spam', GWOLLE_GB_TEXTDOMAIN);
														} ?>
													</label>

													<br />
													<label for="isdeleted" class="selectit">
														<input id="isdeleted" name="isdeleted" type="checkbox" <?php
															if ($entry->get_isdeleted() == '1') {
																echo 'checked="checked"';
															}
															?> />
														<?php
														if ($entry->get_isdeleted() == '0') {
															_e('This entry is Not in Trash.', GWOLLE_GB_TEXTDOMAIN);
														} else {
															_e('This entry is in Trash', GWOLLE_GB_TEXTDOMAIN);
														} ?>
													</label>

												</div>
											</div><!-- 'misc-publishing-actions' -->
											<div class="clear"></div>
										</div> <!-- minor-publishing -->

										<div id="major-publishing-actions">
											<div id="publishing-action">
												<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php _e('Save', GWOLLE_GB_TEXTDOMAIN); ?>" />
											</div> <!-- publishing-action -->
											<div class="clear"></div>
										</div><!-- 'major-publishing-actions' -->
									</div><!-- 'submitbox' -->
								</div><!-- 'inside' -->
							</div><!-- 'submitdiv' -->

							<div id="gwolle_gb-entry-details" class="postbox " >
								<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
								<h3 class='hndle'><span><?php _e('Details', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<p>
										<?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											// FIXME: use this formatting on frontend as well
											if ( $entry->get_author_name() ) {
												echo stripslashes(htmlentities( $entry->get_author_name() ));
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										<br />
										<?php _e('E-Mail', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen(str_replace( ' ', '', $entry->get_author_email() )) > 0) {
												echo stripslashes(htmlentities($entry->get_author_email()));
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										<br />
										<?php _e('Written', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if ( $entry->get_date() > 0 ) {
												echo date_i18n( get_option('date_format'), $entry->get_date() ) . ', ';
												echo date_i18n( get_option('time_format'), $entry->get_date() );
											} else {
												echo '(' . __('Not yet', GWOLLE_GB_TEXTDOMAIN) . ')';
											} ?>
										</span>
										<br />
										<?php _e("Author's IP-address", GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen( $entry->get_author_ip() ) > 0) {
												echo '<a href="http://www.db.ripe.net/whois?form_type=simple&searchtext=' . $entry->get_author_ip() . '"
														title="' . __('Whois search for this IP', GWOLLE_GB_TEXTDOMAIN) . '" target="_blank">
															' . $entry->get_author_ip() . '
														</a>';
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										<br />
										<?php _e('Host', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen( $entry->get_author_host() ) > 0) {
												echo $entry->get_author_host();
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span>
										</p>
									</div> <!-- tagsdiv -->
								</div>
							</div><!-- postbox -->

							<div id="tagsdiv-post_tag" class="postbox">
								<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
								<h3 class='hndle'><span><?php _e('Entry log', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<div id="categories-pop" class="tabs-panel" style="max-height:400px;overflow:auto;"> <?php /* FIXME: place in CSS file */ ?>
											<ul>
											<?php
											if ($entry->get_date() > 0) {
												echo '<li>';
												echo date_i18n( get_option('date_format'), $entry->get_date() ) . ', ';
												echo date_i18n( get_option('time_format'), $entry->get_date() );
												echo ': ' . __('Written', GWOLLE_GB_TEXTDOMAIN) . '</li>';

												$log_entries = gwolle_gb_get_log_entries(array( 'subject_id' => $entry->get_id() ));
												if ( is_array($log_entries) && count($log_entries) > 0 ) {
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
							<?php // FIXME: add labels ?>
							<div id='normal-sortables' class='meta-box-sortables'>
								<div id="authordiv" class="postbox " >
									<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
									<h3 class='hndle'><span><?php _e('Guestbook entry', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<textarea rows="10" cols="56" name="content" tabindex="1"><?php echo gwolle_gb_output_to_input_field( $entry->get_content() ); ?></textarea>
										<?php
										if (get_option('gwolle_gb-showLineBreaks') == 'false') {
											echo '<p>' . str_replace('%1', 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php', __('Line breaks will not be visible to the visitors due to your <a href="%1">settings</a>.', GWOLLE_GB_TEXTDOMAIN)) . '</p>';
										} ?>
									</div>
								</div>
								<div id="authordiv" class="postbox " >
									<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
									<h3 class='hndle'><span><?php _e('Homepage', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="author_website" size="58" tabindex="2" value="<?php echo gwolle_gb_output_to_input_field( $entry->get_author_website() ); ?>" id="author_website" />
										<p><?php _e("Example: <code>http://www.example.com/</code>", GWOLLE_GB_TEXTDOMAIN); ?></p>
									</div>
								</div>
								<div id="authordiv" class="postbox ">
									<div class="handlediv" title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><br /></div>
									<h3 class='hndle'><span><?php _e('Origin', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="author_origin" size="58" tabindex="3" value="<?php echo gwolle_gb_output_to_input_field( $entry->get_author_origin() ); ?>" id="author_origin" />
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

