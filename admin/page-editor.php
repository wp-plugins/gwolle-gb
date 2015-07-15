<?php
/*
 * Editor for editing entries and writing admin entries.
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_editor() {

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	} ?>

	<!-- Do not replace Emoji with <img> elements in textarea, it screws saving the entry -->
	<script type="text/javascript">
		window._wpemojiSettings = '';
	</script>

	<?php
	if (!get_option('gwolle_gb_version')) {
		// FIXME: do this on activation
		gwolle_gb_installSplash();
	} else {

		$gwolle_gb_errors = '';
		$gwolle_gb_messages = '';

		$sectionHeading = __('Edit guestbook entry', GWOLLE_GB_TEXTDOMAIN);

		// Always fetch the requested entry, so we can compare the $entry and the $_POST.
		$entry = new gwolle_gb_entry();

		if ( isset($_POST['entry_id']) ) { // _POST has preference over _GET
			$entry_id = intval($_POST['entry_id']);
		} else if ( isset($_GET['entry_id']) ) {
			$entry_id = intval($_GET['entry_id']);
		}
		if ( isset($entry_id) && $entry_id > 0 ) {
			$result = $entry->load( $entry_id );
			if ( !$result ) {
				$gwolle_gb_messages .= '<p class="error">' . __('Entry could not be found.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
				$gwolle_gb_errors = 'error';
				$sectionHeading = __('Guestbook entry (error)', GWOLLE_GB_TEXTDOMAIN);
			}
		} else {
			$sectionHeading = __('New guestbook entry', GWOLLE_GB_TEXTDOMAIN);
		}


		/*
		 * Handle the $_POST
		 */
		if ( isset($_POST['gwolle_gb_page']) && $_POST['gwolle_gb_page'] == 'editor' && $gwolle_gb_errors == '' ) {

			if ( !isset($_POST['entry_id']) || $_POST['entry_id'] != $entry->get_id() ) {
				$gwolle_gb_messages .= '<p class="error">' . __('Something strange happened.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
				$gwolle_gb_errors = 'error';
			} else if ( $_POST['entry_id'] > 0 && $entry->get_id() > 0 ) {

				/* Check for changes, and update accordingly. This is on an Existing Entry */
				$changed = false;

				/* Set as checked or unchecked, and by whom */
				if ( isset($_POST['ischecked']) && $_POST['ischecked'] == 'on' ) {
					if ( $_POST['ischecked'] == 'on' && $entry->get_ischecked() == 0 ) {
						$entry->set_ischecked( true );
						$user_id = get_current_user_id(); // returns 0 if no current user
						$entry->set_checkedby( $user_id );
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-checked' );
						$changed = true;
					}
				} else if ( $entry->get_ischecked() == 1 ) {
					$entry->set_ischecked( false );
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-unchecked' );
					$changed = true;
				}

				/* Set as spam or not, and submit as ham or spam to Akismet service */
				if ( isset($_POST['isspam']) && $_POST['isspam'] == 'on' ) {
					if ( $_POST['isspam'] == 'on' && $entry->get_isspam() == 0 ) {
						$entry->set_isspam( true );
						$result = gwolle_gb_akismet( $entry, 'submit-spam' );
						if ( $result ) {
							$gwolle_gb_messages .= '<p>' . __('Submitted as Spam to the Akismet service.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
						}
						gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-spam' );
						$changed = true;
					}
				} else if ( $entry->get_isspam() == 1 ) {
					$entry->set_isspam( false );
					$result = gwolle_gb_akismet( $entry, 'submit-ham' );
					if ( $result ) {
						$gwolle_gb_messages .= '<p>' . __('Submitted as Ham to the Akismet service.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
					gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-not-spam' );
					$changed = true;
				}

				/* Set as trash or not */
				if ( isset($_POST['istrash']) && $_POST['istrash'] == 'on' ) {
					if ( $_POST['istrash'] == 'on' && $entry->get_istrash() == 0 ) {
						$entry->set_istrash( true );
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
						$changed = true;
					}
				} else if ( $entry->get_istrash() == 1 ) {
					$entry->set_istrash( false );
					gwolle_gb_add_log_entry( $entry->get_id(), 'entry-untrashed' );
					$changed = true;
				}

				/* Check if the content changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_content']) && $_POST['gwolle_gb_content'] != '' ) {
					if ( $_POST['gwolle_gb_content'] != $entry->get_content() ) {
						$entry_content = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_content'], 'content' );
						$entry->set_content( $entry_content );
						$changed = true;
					}
				}

				/* Check if the website changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_author_website']) ) {
					if ( $_POST['gwolle_gb_author_website'] != $entry->get_author_website() ) {
						$entry->set_author_website( $_POST['gwolle_gb_author_website'] );
						$changed = true;
					}
				}

				/* Check if the author_origin changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_author_origin']) ) {
					if ( $_POST['gwolle_gb_author_origin'] != $entry->get_author_origin() ) {
						$entry_origin = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_author_origin'], 'author_origin' );
						$entry->set_author_origin( $entry_origin );
						$changed = true;
					}
				}

				/* Check if the author_name changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_author_name']) ) {
					if ( $_POST['gwolle_gb_author_name'] != $entry->get_author_name() ) {
						$entry_name = gwolle_gb_maybe_encode_emoji( $_POST['gwolle_gb_author_name'], 'author_name' );
						$entry->set_author_name( $entry_name );
						$changed = true;
					}
				}

				/* Check if the datetime changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_timestamp']) && is_numeric($_POST['gwolle_gb_timestamp']) ) {
					if ( $_POST['gwolle_gb_timestamp'] != $entry->get_datetime() ) {
						$entry->set_datetime( (int) $_POST['gwolle_gb_timestamp'] );
						$changed = true;
					}
				}

				if ( $changed ) {
					$result = $entry->save();
					if ($result ) {
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-edited' );
						$gwolle_gb_messages .= '<p>' . __('Changes saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('Error happened during saving.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
						$gwolle_gb_errors = 'error';
					}
				} else {
					$gwolle_gb_messages .= '<p>' . __('Entry was not changed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';

				}

				/* Remove permanently */
				if ( isset($_POST['istrash']) && $_POST['istrash'] == 'on' && isset($_POST['remove']) && $_POST['remove'] == 'on' ) {
					if ( $entry->get_istrash() == 1 ) {
						$entry->delete();
						$entry->set_id(0);
						$changed = true;
						// Overwrite any other message, only removal is relevant.
						$gwolle_gb_messages = '<p>' . __('Entry removed.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				}

			} else if ( $_POST['entry_id'] == 0 && $entry->get_id() == 0 ) {

				/* Check for input, and save accordingly. This is on a New Entry, so no logging */
				$saved = false;
				$data = Array();

				/* Set as checked anyway, new entry is always by an admin */
				$data['ischecked'] = true;
				$user_id = get_current_user_id(); // returns 0 if no current user
				$data['checkedby'] = $user_id;
				$data['author_id'] = $user_id;

				/* Set metadata of the admin */
				$userdata = get_userdata( $user_id );

				if (is_object($userdata)) {
					if ( isset( $userdata->display_name ) ) {
						$author_name = $userdata->display_name;
					} else {
						$author_name = $userdata->user_login;
					}
					$author_email = $userdata->user_email;
				}
				$data['author_name'] = $author_name;
				$data['author_name'] = gwolle_gb_maybe_encode_emoji( $data['author_name'], 'author_name' );
				$data['author_email'] = $author_email;

				/* Set as Not Spam */
				$data['isspam'] = false;

				/* Do not set as trash */
				$data['istrash'] = false;

				/* Check if the content is filled in, and update accordingly */
				if ( isset($_POST['gwolle_gb_content']) && $_POST['gwolle_gb_content'] != '' ) {
					$data['content'] = $_POST['gwolle_gb_content'];
					$data['content'] = gwolle_gb_maybe_encode_emoji( $data['content'], 'content' );
					$saved = true;
				} else {
					$form_setting = gwolle_gb_get_setting( 'form' );
					if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' && isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
						$gwolle_gb_messages .= '<p>' . __('Entry has no content, even though that is mandatory.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
						$gwolle_gb_errors = 'error';
					} else {
						$data['content'] = '';
						$saved = true;
					}
				}

				/* Check if the website changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_author_website']) ) {
					if ( $_POST['gwolle_gb_author_website'] != '' ) {
						$data['author_website'] = $_POST['gwolle_gb_author_website'];
					} else {
						$data['author_website'] = home_url();
					}
				}

				/* Check if the author_origin changed, and update accordingly */
				if ( isset($_POST['gwolle_gb_author_origin']) ) {
					if ( $_POST['gwolle_gb_author_origin'] != '' ) {
						$data['author_origin'] = $_POST['gwolle_gb_author_origin'];
						$data['author_origin'] = gwolle_gb_maybe_encode_emoji( $data['author_origin'], 'author_origin' );
					}
				}

				/* Network Information */
				$entry->set_author_ip( $_SERVER['REMOTE_ADDR'] );
				$entry->set_author_host( gethostbyaddr( $_SERVER['REMOTE_ADDR'] ) );

				$result1 = $entry->set_data( $data );
				if ( $saved ) {
					$result2 = $entry->save();
					if ( $result1 && $result2 ) {
						$gwolle_gb_messages .= '<p>' . __('Entry saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
					} else {
						$gwolle_gb_messages .= '<p>' . __('Error happened during saving.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
						$gwolle_gb_errors = 'error';
					}
				} else {
					$gwolle_gb_messages .= '<p>' . __('Entry was not saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>';

				}

			}
		}

		// FIXME: reload the entry, just for consistency?

		/*
		 * Build the Page and the Form
		 */
		?>
		<div class="wrap gwolle_gb">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php echo $sectionHeading; ?></h2>

			<?php
			if ( $gwolle_gb_messages ) {
				echo '
					<div id="message" class="updated fade notice is-dismissible ' . $gwolle_gb_errors . ' ">' .
						$gwolle_gb_messages .
					'</div>';
			}
			?>

			<form name="gwolle_gb_editor" id="gwolle_gb_editor" method="POST" action="" accept-charset="UTF-8">
				<input type="hidden" name="gwolle_gb_page" value="editor" />
				<input type="hidden" name="entry_id" value="<?php echo $entry->get_id(); ?>" />

				<div id="poststuff" class="metabox-holder has-right-sidebar">
					<div id="side-info-column" class="inner-sidebar">
						<div id='side-sortables' class='meta-box-sortables'>

							<?php
							$class = '';
							// Attach 'spam' to class if the entry is spam
							if ( $entry->get_isspam() === 1 ) {
								$class .= ' spam';
							} else {
								$class .= ' nospam';
							}

							// Attach 'trash' to class if the entry is in trash
							if ( $entry->get_istrash() === 1 ) {
								$class .= ' trash';
							} else {
								$class .= ' notrash';
							}

							// Attach 'checked/unchecked' to class
							if ( $entry->get_ischecked() === 1 ) {
								$class .= ' checked';
							} else {
								$class .= ' unchecked';
							}

							// Attach 'visible/invisible' to class
							if ( $entry->get_isspam() === 1 || $entry->get_istrash() === 1 || $entry->get_ischecked() === 0 ) {
								$class .= ' invisible';
							} else {
								$class .= ' visible';
							}

							// Add admin-entry class to an entry from an admin
							$author_id = $entry->get_author_id();
							$is_moderator = gwolle_gb_is_moderator( $author_id );
							if ( $is_moderator ) {
								$class .= ' admin-entry';
							} ?>

							<?php
							$postid = gwolle_gb_get_postid();
							if ( $postid ) {
								$permalink = get_bloginfo('url') . '?p=' . $postid;
								?>
								<div id="tagsdiv-post_tag" class="postbox">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('View Frontend', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<div class="tagsdiv" id="post_tag">
											<div id="categories-pop" class="tabs-panel gwolle_gb_frontend">
												<a class="button rbutton button" href="<?php echo $permalink; ?>"><?php esc_attr_e('View Guestbook',GWOLLE_GB_TEXTDOMAIN); ?></a>
											</div>
										</div>
									</div>
								</div>
								<?php
							} ?>

							<div id="submitdiv" class="postbox">
								<div class="handlediv"></div>
								<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Options', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="submitbox" id="submitpost">
										<div id="minor-publishing">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section misc-pub-section-last">

													<?php
													// Optional Icon column where CSS is being used to show them or not
													if ( get_option('gwolle_gb-showEntryIcons', 'true') === 'true' ) { ?>
														<span class="entry-icons <?php echo $class; ?>">
															<span class="visible-icon"></span>
															<span class="invisible-icon"></span>
															<span class="spam-icon"></span>
															<span class="trash-icon"></span>
															<span class="gwolle_gb_ajax"></span>
														</span>
														<?php
													}

													if ( $entry->get_id() == 0 ) {
														echo '<h3 class="h3_invisible">' . __('This entry is not yet visible.', GWOLLE_GB_TEXTDOMAIN) . '</h3>';
													} else {
														if ($entry->get_ischecked() == 1 && $entry->get_isspam() == 0 && $entry->get_istrash() == 0 ) {
															echo '
																<h3 class="h3_visible">' . __('This entry is Visible.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
																<h3 class="h3_invisible" style="display:none;">' . __('This entry is Not Visible.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
																';
														} else {
															echo '
																<h3 class="h3_visible" style="display:none;">' . __('This entry is Visible.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
																<h3 class="h3_invisible">' . __('This entry is Not Visible.', GWOLLE_GB_TEXTDOMAIN) . '</h3>
																';
														}
													} ?>

													<label for="ischecked" class="selectit">
														<input id="ischecked" name="ischecked" type="checkbox" <?php
															if ($entry->get_ischecked() == '1' || $entry->get_id() == 0) {
																echo 'checked="checked"';
															}
															?> />
														<?php _e('Checked', GWOLLE_GB_TEXTDOMAIN); ?>
													</label>

													<br />
													<label for="isspam" class="selectit">
														<input id="isspam" name="isspam" type="checkbox" <?php
															if ($entry->get_isspam() == '1') {
																echo 'checked="checked"';
															}
															?> />
														<?php _e('Spam', GWOLLE_GB_TEXTDOMAIN); ?>
													</label>

													<br />
													<label for="istrash" class="selectit">
														<input id="istrash" name="istrash" type="checkbox" <?php
															if ($entry->get_istrash() == '1') {
																echo 'checked="checked"';
															}
															?> />
														<?php _e('Trash', GWOLLE_GB_TEXTDOMAIN); ?>
													</label>

													<?php
													if ($entry->get_istrash() == '1') { ?>
														<br />
														<label for="remove" class="selectit">
															<input id="remove" name="remove" type="checkbox" />
															<?php _e('Remove this entry Permanently.', GWOLLE_GB_TEXTDOMAIN); ?>
														</label>
													<?php } ?>

												</div>
											</div><!-- 'misc-publishing-actions' -->
											<div class="clear"></div>
										</div> <!-- minor-publishing -->

										<div id="major-publishing-actions">
											<div id="publishing-action">
												<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php esc_attr_e('Save', GWOLLE_GB_TEXTDOMAIN); ?>" />
											</div> <!-- publishing-action -->
											<div class="clear"></div>
										</div><!-- 'major-publishing-actions' -->
									</div><!-- 'submitbox' -->
								</div><!-- 'inside' -->
							</div><!-- 'submitdiv' -->

							<?php
							if ( $entry->get_id() > 0 ) { ?>
							<div id="submitdiv" class="postbox">
								<div class="handlediv"></div>
								<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Actions', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="submitbox" id="submitpost">
										<div id="minor-publishing">
											<div id="misc-publishing-actions">
												<div class="misc-pub-section misc-pub-section-last">

													<?php echo '
													<div class="gwolle_gb_actions ' . $class . '">
														<span class="gwolle_gb_check">
															<a id="check_' . $entry->get_id() . '" href="#" class="vim-a" title="' . __('Check entry', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Check', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span>
														<span class="gwolle_gb_uncheck">
															<a id="uncheck_' . $entry->get_id() . '" href="#" class="vim-u" title="' . __('Uncheck entry', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Uncheck', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span>
														<span class="gwolle_gb_spam">&nbsp;|&nbsp;
															<a id="spam_' . $entry->get_id() . '" href="#" class="vim-s vim-destructive" title="' . __('Mark entry as spam.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Spam', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span>
														<span class="gwolle_gb_unspam">&nbsp;|&nbsp;
															<a id="unspam_' . $entry->get_id() . '" href="#" class="vim-a" title="' . __('Mark entry as not-spam.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Not spam', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span>
														<span class="gwolle_gb_trash">&nbsp;|&nbsp;
															<a id="trash_' . $entry->get_id() . '" href="#" class="vim-d vim-destructive" title="' . __('Move entry to trash.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Trash', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span>
														<span class="gwolle_gb_untrash">&nbsp;|&nbsp;
															<a id="untrash_' . $entry->get_id() . '" href="#" class="vim-d" title="' . __('Recover entry from trash.', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Untrash', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span><br />
														<span class="gwolle_gb_ajax">
															<a id="ajax_' . $entry->get_id() . '" href="#" class="ajax vim-d vim-destructive" title="' . __('Please wait...', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Wait...', GWOLLE_GB_TEXTDOMAIN) . '</a>
														</span><br />
													</div>
													'; ?>

												</div>
											</div><!-- 'misc-publishing-actions' -->
											<div class="clear"></div>
										</div> <!-- minor-publishing -->
									</div><!-- 'submitbox' -->
								</div><!-- 'inside' -->
							</div><!-- 'submitdiv' -->
							<?php } ?>

							<div id="gwolle_gb-entry-details" class="postbox " >
								<div class="handlediv"></div>
								<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Details', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<p>
										<?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if ( $entry->get_author_name() ) {
												echo gwolle_gb_sanitize_output( $entry->get_author_name() );
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span><br />
										<?php _e('E-Mail', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen(str_replace( ' ', '', $entry->get_author_email() )) > 0) {
												echo gwolle_gb_sanitize_output( $entry->get_author_email() );
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span><br />
										<?php _e('Written', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if ( $entry->get_datetime() > 0 ) {
												echo date_i18n( get_option('date_format'), $entry->get_datetime() ) . ', ';
												echo date_i18n( get_option('time_format'), $entry->get_datetime() );
											} else {
												echo '(' . __('Not yet', GWOLLE_GB_TEXTDOMAIN) . ')';
											} ?>
										</span><br />
										<?php _e("Author's IP-address", GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen( $entry->get_author_ip() ) > 0) {
												echo '<a href="http://www.db.ripe.net/whois?form_type=simple&searchtext=' . $entry->get_author_ip() . '"
														title="' . __('Whois search for this IP', GWOLLE_GB_TEXTDOMAIN) . '" target="_blank">
															' . $entry->get_author_ip() . '
														</a>';
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span><br />
										<?php _e('Host', GWOLLE_GB_TEXTDOMAIN); ?>: <span><?php
											if (strlen( $entry->get_author_host() ) > 0) {
												echo $entry->get_author_host();
											} else {
												echo '<i>(' . __('Unknown', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
											} ?>
										</span><br />
										<span class="gwolle_gb_edit_meta">
											<a href="#" title="<?php _e('Edit metadata', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Edit', GWOLLE_GB_TEXTDOMAIN); ?></a>
										</span>
										</p>

										<div class="gwolle_gb_edit_meta_inputs">
											<label for="gwolle_gb_author_name"><?php _e('Author', GWOLLE_GB_TEXTDOMAIN); ?>: </label><br />
											<input type="text" name="gwolle_gb_author_name" size="24" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_name() ); ?>" id="gwolle_gb_author_name" />

											<span><?php _e('Date and time', GWOLLE_GB_TEXTDOMAIN); ?>: </span><br />
											<div class="gwolle_gb_date"><?php
												gwolle_gb_touch_time( $entry ); ?>
											</div>
										</div>

									</div> <!-- tagsdiv -->
								</div>
							</div><!-- postbox -->

							<div id="tagsdiv-post_tag" class="postbox">
								<div class="handlediv"></div>
								<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Entry log', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
								<div class="inside">
									<div class="tagsdiv" id="post_tag">
										<div id="categories-pop" class="tabs-panel gwolle_gb_log">
											<ul>
											<?php
											if ($entry->get_datetime() > 0) {
												echo '<li>';
												echo date_i18n( get_option('date_format'), $entry->get_datetime() ) . ', ';
												echo date_i18n( get_option('time_format'), $entry->get_datetime() );
												echo ': ' . __('Written', GWOLLE_GB_TEXTDOMAIN) . '</li>';

												$log_entries = gwolle_gb_get_log_entries( $entry->get_id() );
												if ( is_array($log_entries) && !empty($log_entries) ) {
													foreach ($log_entries as $log_entry) {
														echo '<li class="log_id_' . $log_entry['id'] . '">' . $log_entry['msg_html'] . '</li>';
													}
												}
											} else {
												echo '<li>(' . __('No log yet.', GWOLLE_GB_TEXTDOMAIN) . ')</li>';
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
								<div id="contentdiv" class="postbox" >
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Guestbook entry', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<textarea rows="10" cols="56" name="gwolle_gb_content" id="gwolle_gb_content" tabindex="1"><?php echo gwolle_gb_sanitize_output( $entry->get_content() ); ?></textarea>
										<?php
										if (get_option('gwolle_gb-showLineBreaks', 'false') == 'false') {
											echo '<p>' . sprintf( __('Line breaks will not be visible to the visitors due to your <a href="%s">settings</a>.', GWOLLE_GB_TEXTDOMAIN), 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php' ) . '</p>';
										}
										$form_setting = gwolle_gb_get_setting( 'form' );
										if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
											wp_enqueue_script( 'markitup', plugins_url('../frontend/markitup/jquery.markitup.js', __FILE__), 'jquery', '1.1.14', false );
											wp_enqueue_script( 'markitup_set', plugins_url('../frontend/markitup/set.js', __FILE__), 'jquery', '1.1.14', false );
											wp_enqueue_style('gwolle_gb_markitup_css', plugins_url('../frontend/markitup/style.css', __FILE__), false, '1.1.14',  'screen');
										} ?>
									</div>
								</div>
								<div id="authordiv" class="postbox " >
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Website', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="gwolle_gb_author_website" size="58" tabindex="2" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_website() ); ?>" id="author_website" />
										<p><?php _e("Example: <code>http://www.example.com/</code>", GWOLLE_GB_TEXTDOMAIN); ?></p>
									</div>
								</div>
								<div id="authordiv" class="postbox ">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><span><?php _e('Origin', GWOLLE_GB_TEXTDOMAIN); ?></span></h3>
									<div class="inside">
										<input type="text" name="gwolle_gb_author_origin" size="58" tabindex="3" value="<?php echo gwolle_gb_sanitize_output( $entry->get_author_origin() ); ?>" id="author_origin" />
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

