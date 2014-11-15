<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settings() {
	global $wpdb, $defaultMailText, $current_user;

	if (!get_option('gwolle_gb_version')) {
		gwolle_gb_installSplash();
	} else {
		$setting_page = (isset($_REQUEST['setting_page'])) ? $_REQUEST['setting_page'] : FALSE;
		$saved = false;
		if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) {
			if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
				die(__('Cheatin&#8217; uh?'));
			}

			// Array of settings configured using checkboxes
			$checkbox_settings = array('moderate-entries', 'akismet-active', 'showEntryIcons', 'showLineBreaks', 'checkForImport', 'showSmilies', 'linkAuthorWebsite');
			foreach ($checkbox_settings as $setting_name) {
				if (isset($_POST[$setting_name]) && $_POST[$setting_name] == 'on') {
					update_option('gwolle_gb-' . $setting_name, 'true');
				} else {
					update_option('gwolle_gb-' . $setting_name, 'false');
				}
				$saved = true;
			}

			// E-mail notification option
			if ( isset($_POST['notify_by_mail']) && $_POST['notify_by_mail'] == 'on' ) {
				// Turn the notification ON for the current user.
				$user_id = $current_user->data->ID;
				$user_ids = Array();

				$user_ids_old = get_option('gwolle_gb-notifyByMail', Array() );
				if ( count($user_ids_old) > 0 ) {
					$user_ids_old = explode( ",", $user_ids_old );
					foreach ( $user_ids_old as $user_id_old ) {
						if ( $user_id_old == $user_id ) {
							continue; // will be added again below the loop
						}
						if ( is_numeric($user_id_old) ) {
							$user_ids[] = $user_id_old;
						}
					}
				}
				$user_ids[] = $user_id;

				$user_ids = implode(",", $user_ids);
				update_option('gwolle_gb-notifyByMail', $user_ids);

				$saved = true;
			} elseif ( !isset($_POST['notify_by_mail']) ) {
				// Turn the notification OFF for the current user
				$user_id = $current_user->data->ID;
				$user_ids = Array();

				$user_ids_old = get_option('gwolle_gb-notifyByMail', Array() );
				if ( count($user_ids_old) > 0 ) {
					$user_ids_old = explode( ",", $user_ids_old );
					foreach ( $user_ids_old as $user_id_old ) {
						if ( $user_id_old == $user_id ) {
							continue;
						}
						if ( is_numeric($user_id_old) ) {
							$user_ids[] = $user_id_old;
						}
					}
				}

				$user_ids = implode(",", $user_ids);
				update_option('gwolle_gb-notifyByMail', $user_ids);
				$saved = true;
			}

			// Recaptcha settings
			// FIXME: sanitize value
			if ( isset($_POST['recaptcha-active']) && $_POST['recaptcha-active'] == 'on' ) {
				update_option('gwolle_gb-recaptcha-active', 'true');
				update_option('recaptcha-public-key', $_POST['recaptcha-public-key']);
				update_option('recaptcha-private-key', $_POST['recaptcha-private-key']);
				$saved = true;
			} else {
				update_option('gwolle_gb-recaptcha-active', 'false');
				$saved = true;
			}

			// Admin mail content
			// FIXME: sanitize value
			if ( isset($_POST['adminMailContent']) && $_POST['adminMailContent'] != get_option('gwolle_gb-defaultMailText') ) {
				update_option('gwolle_gb-adminMailContent', $_POST['adminMailContent']);
				$saved = true;
			}

			// Entries per page options
			// FIXME: sanitize value
			if ( isset($_POST['entriesPerPage']) && is_numeric($_POST['entriesPerPage']) && $_POST['entriesPerPage'] > 0 ) {
				update_option('gwolle_gb-entriesPerPage', $_POST['entriesPerPage']);
				$saved = true;
			}

			// Guestbook post ID
			// update_option('gwolle_gb-post_ID', (int)$_POST['post_ID']);

		}
		?>

		<div class="wrap">

			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Settings', GWOLLE_GB_TEXTDOMAIN); ?></h2>
			<?php
			if ($setting_page === FALSE) {

				if ( $saved ) {
					echo '
						<div id="message" class="updated fade">
							<p>' . __('Changes saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>
						</div>';
				}
				?>

				<form name="gwolle_gb_options" method="post" action="">

					<?php
					settings_fields( 'gwolle_gb_options' );
					do_settings_sections( 'gwolle_gb_options' ); ?>

					<table class="form-table">

						<tr valign="top">
							<th scope="row"><label for="post_ID"><?php _e('ID of the guestbook post', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<?php // Check if a post with that ID exists
								$value = ((int) get_option('gwolle_gb-post_ID') === 0) ? '' : (int) get_option('gwolle_gb-post_ID');
								if ((int) $value > 0) {
									$sql = "
										SELECT
											ID
										FROM
											" . $wpdb->posts . " p
										WHERE
											p.ID = " . $value . "
											AND
											p.post_content LIKE '%[gwolle-gb]%'
										LIMIT 1";
									$result = $wpdb->query($sql);
									if ( $result == 0 ) {
										// Post does not exist or does not contain the tag.
										$value = '';
									}
								}
								?>
								<input type="text" maxlength="11" style="width:110px;" name="post_ID" id="post_ID" value="<?php echo $value; ?>" class="regular-text" />
								<?php $post_id_img = ($value == '') ? 'entry-unchecked.jpg' : 'blank.gif';
								echo '<img id="post_id_status" style="height:10px;" src="' . GWOLLE_GB_URL . '/admin/gfx/' . $post_id_img . '" alt="' . __('Not set.', GWOLLE_GB_TEXTDOMAIN) . '" />';
								?>
								&nbsp;
								<a id="search_gwolle_gb_post_ID" href="javascript:void(0);" title="<?php _e('Click here to let Gwolle-GB search for the post.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Search now!', GWOLLE_GB_TEXTDOMAIN); ?></a>
								<br />
								<div style="display:none;margin-top:5px;" id="gwolle_gb_post_seach_result">Sorry, post not found. <?php echo convert_smilies(':('); ?></div>
								<span class="setting-description"><?php _e('This ID was used to generate the correct link to the guestbook read/write page at the frontend. It will however be refactored. Maybe it stays, maybe not. Just ignore it for now.', GWOLLE_GB_TEXTDOMAIN); ?></span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="moderate-entries"><?php _e('Moderate Guestbook', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<input <?php
									if (get_option( 'gwolle_gb-moderate-entries' ) == 'true') {
										echo 'checked="checked"';
									} ?>
									type="checkbox" name="moderate-entries" id="moderate-entries">
								<?php _e('Moderate entries before publishing them.', GWOLLE_GB_TEXTDOMAIN); ?>
								<br />
								<span class="setting-description">
									<?php _e("New entries have to be unlocked by an administrator before they are visible to the public.", GWOLLE_GB_TEXTDOMAIN); ?>
									<br />
									<?php _e("It's highly recommended that you turn this on, because your responsible for the content on your homepage.", GWOLLE_GB_TEXTDOMAIN); ?>
								</span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="blogname"><?php _e('Notification', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<?php
								// FIXME: use labels, not spans
								// Check if function mail() exists. If not, display a hint to the user.
								if (!function_exists('mail')) {
									echo '<p class="setting-description">' .
										__('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. You might want to install a WordPress plugin that uses SMTP instead of <code>mail()</code>. Or you can contact your hosting provider to change this.',GWOLLE_GB_TEXTDOMAIN)
										. '</p>';
								}
								$current_user_id = $current_user->data->ID;
								$currentUserNotification = false;
								$user_ids = get_option('gwolle_gb-notifyByMail' );
								if ( count($user_ids) > 0 ) {
									$user_ids = explode( ",", $user_ids );
									foreach ( $user_ids as $user_id ) {
										if ( $user_id == $current_user_id ) {
											$currentUserNotification = true;
										}
									}
								} ?>
								<input name="notify_by_mail" type="checkbox" id="notify_by_mail" <?php
									if ( $currentUserNotification ) {
										echo 'checked="checked"';
									} ?> >
								<span class="setting-description"><?php _e('Send me an e-mail when a new entry has been posted.', GWOLLE_GB_TEXTDOMAIN); ?></span>

								<div>
									<?php _e('The following users have subscribed to this service:', GWOLLE_GB_TEXTDOMAIN);


									if ( count($user_ids) == 0 ) {
										echo '<br /><i>(' . __('No subscriber yet', GWOLLE_GB_TEXTDOMAIN) . ')</i>';
									} else {
										echo '<ul style="font-size:10px;font-style:italic;list-style-type:disc;padding-left:14px;">';
										foreach ( $user_ids as $user_id ) {
											$user_info = get_userdata($user_id);
											if ($user_info === FALSE) {
												// Invalid $user_id
												continue;
											}
											echo '<li>';
											if ($user_info->ID == $current_user->data->ID) {
												echo '<strong>' . __('You', GWOLLE_GB_TEXTDOMAIN) . '</strong>';
											} else {
												echo $user_info->first_name . ' ' . $user_info->last_name;
											}
											echo ' (' . $user_info->user_email . ')';
											echo '</li>';
										}
										echo '</ul>';
									}
									?>
								</div>
							</td>
						</tr>


						<?php
						$recaptcha_publicKey = get_option('recaptcha-public-key');
						$recaptcha_privateKey = get_option('recaptcha-private-key');
						?>
						<tr valign="top">
							<th scope="row"><label for="recaptcha-settings">Recaptcha</label><br /><span class="setting-description"><a href="http://recaptcha.net/learnmore.html" title="<?php _e('Learn more about Recaptcha...', GWOLLE_GB_TEXTDOMAIN); ?>" target="_blank"><?php _e("What's that?", GWOLLE_GB_TEXTDOMAIN); ?></a></span></th>
							<td>
								<input name="recaptcha-active" <?php
									if (get_option( 'gwolle_gb-recaptcha-active' ) === TRUE) {
										echo 'checked="checked" ';
									}
									?> id="use-recaptcha" type="checkbox">
								<?php _e('Use Recaptcha', GWOLLE_GB_TEXTDOMAIN); ?>
								<br />
								<input name="recaptcha-public-key" type="text" id="recaptcha-public-key"  value="<?php echo $recaptcha_publicKey; ?>" class="regular-text" />
								<span class="setting-description"><?php _e('<strong>Public</strong> key of your Recaptcha account', GWOLLE_GB_TEXTDOMAIN); ?></span>
								<br />
								<input name="recaptcha-private-key" type="text" id="recaptcha-private-key"  value="<?php echo $recaptcha_privateKey; ?>" class="regular-text" />
								<span class="setting-description"><?php _e('<strong>Private</strong> key of your Recaptcha account', GWOLLE_GB_TEXTDOMAIN); ?></span>
								<br />
								<span class="setting-description"><?php _e('The keys can be found at your', GWOLLE_GB_TEXTDOMAIN); ?> <a href="https://admin.recaptcha.net/recaptcha/sites/" title="<?php _e('Go to my reCAPTCHA sites...', GWOLLE_GB_TEXTDOMAIN); ?>" target="_blank"><?php _e('reCAPTCHA sites overview', GWOLLE_GB_TEXTDOMAIN); ?></a>.</span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row">
								<label for="akismet-settings">Akismet</label>
								<br />
								<span class="setting-description">
									<a href="http://akismet.com/" title="<?php _e('Learn more about Akismet...', GWOLLE_GB_TEXTDOMAIN); ?>" target="_blank"><?php _e("What's that?", GWOLLE_GB_TEXTDOMAIN); ?></a>
								</span>
							</th>
							<td>
								<?php
								$current_plugins = get_option('active_plugins');
								$wordpress_api_key = get_option('wordpress_api_key');

								// Check wether Akismet is installed and activated or not.
								if (!in_array('akismet/akismet.php', $current_plugins)) {
									_e("Akismet helps you to fight spam. It's free and easy to install. Download and install it today to stop spam in your guestbook.", GWOLLE_GB_TEXTDOMAIN);
								} elseif (!$wordpress_api_key) {
									// Check if a Wordpress API key is defined and set in the database. We just assume it is valid
									echo str_replace('%1', 'options-general.php?page=akismet-key-config', __("Sorry, wasn't able to locate your <strong>WordPress API key. You can enter it at the <a href=\"%1\">Akismet configuration page</a>.", GWOLLE_GB_TEXTDOMAIN));
								} else {
									// Akismet is installed and a WordPress api key exists
									echo '<input ';
									if ( get_option( 'gwolle_gb-akismet-active', 'false' ) === 'true' ) {
										echo 'checked="checked" ';
									}
									echo 'name="akismet-active" id="akismet-active" type="checkbox" /> ' . __('Use Akismet', GWOLLE_GB_TEXTDOMAIN);
									echo '<br />';
									_e("The WordPress API key has been found, so you can start using Akismet right now.", GWOLLE_GB_TEXTDOMAIN);
								}
								?>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="showEntryIcons"><?php _e('Entry icons', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<input type="checkbox" <?php
									if ( get_option( 'gwolle_gb-showEntryIcons', 'false' ) === 'true' ) {
										echo 'checked="checked"';
									}
									?> name="showEntryIcons" /> <?php _e('Show entry icons', GWOLLE_GB_TEXTDOMAIN); ?>
								<br />
								<span class="setting-description"><?php _e('These icons are shown in every entry row of the admin list, so that you know its status (spam, checked and deleted).', GWOLLE_GB_TEXTDOMAIN); ?></span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="entriesPerPage"><?php _e('Entries per page', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<select name="entriesPerPage">
									<?php $entriesPerPage = get_option( 'gwolle_gb-entriesPerPage', 15 );
									$presets = array(5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 120, 150, 200, 250);
									for ($i = 0; $i < count($presets); $i++) {
										echo '<option value="' . $presets[$i] . '"';
										if ($presets[$i] == $entriesPerPage) {
											echo ' selected="selected"';
										}
										echo '>' . $presets[$i] . ' ' . __('Entries', GWOLLE_GB_TEXTDOMAIN) . '</option>';
									}
									?>
								</select>
								<br />
								<span class="setting-description"><?php _e('Number of entries shown on the frontend.', GWOLLE_GB_TEXTDOMAIN); ?></span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="entriesPerPage"><?php _e('Appearance', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<input type="checkbox" id="showLineBreaks" name="showLineBreaks"<?php
									if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
										echo ' checked="checked"';
									}
									?> />
								<label for="showLineBreaks"><?php _e('Show line breaks.', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<span class="setting-description"><?php _e('Show line breaks as the entry authors entered them. (May result in very long entries. Is turned off by default.)', GWOLLE_GB_TEXTDOMAIN); ?></span>
								<br />

								<input type="checkbox" id="showSmilies" name="showSmilies"<?php
									if ( get_option( 'gwolle_gb-showSmilies', 'false' ) === 'true' ) {
										echo ' checked="checked"';
									}
									?> />
								<label for="showSmilies"><?php _e('Display smilies as images.', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<span class="setting-description"><?php echo str_replace('%1', convert_smilies(':)'), __("Replaces smilies in entries like :) with their image %1. Uses the WP smiley replacer, so check on that one if you'd like to add new/more smilies.", GWOLLE_GB_TEXTDOMAIN)); ?></span>
								<br />

								<input type="checkbox" id="linkAuthorWebsite" name="linkAuthorWebsite"<?php
									if ( get_option( 'gwolle_gb-linkAuthorWebsite', 'false' ) === 'true' ) {
										echo ' checked="checked"';
									}
									?> />
								<label for="linkAuthorWebsite"><?php _e("Link authors' name to their website.", GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<span class="setting-description"><?php _e("The author of an entry can set his/her website. If this setting is checked, his/her name will be a link to that website.", GWOLLE_GB_TEXTDOMAIN); ?></span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="adminMailContent"><?php _e('Admin mail content', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<?php
								$adminMailContent = get_option('gwolle_gb-adminMailContent');
								if (!$adminMailContent) { // No text set by the user. Use the default text.
									$mailText = get_option( 'gwolle_gb-defaultMailText' );
								} else {
									$mailText = stripslashes($adminMailContent);
								} ?>
								<textarea name="adminMailContent" id="adminMailContent" style="width:400px;height:200px;" class="regular-text"><?php echo $mailText; ?></textarea>
								<br />
								<span class="setting-description">
									<?php _e('You can set the content of the mail that a notification subscriber gets on new entries. The following tags are supported:', GWOLLE_GB_TEXTDOMAIN);
									echo '<br />';
									$mailTags = array('user_email', 'entry_management_url', 'blog_name', 'blog_url', 'wp_admin_url', 'entry_content');
									for ($i = 0; $i < count($mailTags); $i++) {
										if ($i != 0) {
											echo '&nbsp;,&nbsp;';
										}
										echo '%' . $mailTags[$i] . '%';
									}
									?>
								</span>
							</td>
						</tr>


						<tr valign="top">
							<th scope="row"><label for="checkForImport"><?php _e('Check for import', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
							<td>
								<input <?php
									if (get_option( 'gwolle_gb-checkForImport', 'false' ) === 'true' ) {
										echo 'checked="checked"';
									}
									?> type="checkbox" name="checkForImport" id="checkForImport" />
								<?php _e('Check if there are other guestbook plugins installed to import their entries (deprecated, will be refactored).', GWOLLE_GB_TEXTDOMAIN); ?>
								<br />
								<span class="setting-description">
									<?php _e("If Gwolle-GB detects another guestbook plugin for WordPress it will prompt you to import that guestbooks's data.", GWOLLE_GB_TEXTDOMAIN); ?>
								</span>
							</td>
						</tr>


						<tr>
							<td colspan="" style="">&nbsp;</td>
							<td>
								<p class="submit">
									<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
								</p>
							</td>
						</tr>

					</table>
				</form>

				<!-- uninstall section -->
				<table style="margin-top:30px;" class="form-table">
					<tr valign="top" style="margin-top:30px;">
						<th scope="row" style="color:#FF0000;"><label for="blogdescription"><?php _e('Uninstall', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<?php _e('Uninstalling means that all database entries are removed (settings and entries).', GWOLLE_GB_TEXTDOMAIN);
							echo '<br />';
							_e('This can <strong>not</strong> be undone.', GWOLLE_GB_TEXTDOMAIN);
							?>
							<br />
							<a style="color:#ff0000;" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/settings.php&setting_page=uninstall">
								<?php _e("I'm aware of that, continue!", GWOLLE_GB_TEXTDOMAIN); ?> &raquo;
							</a>
						</td>
					</tr>
				</table>
				<?php
				// FIXME; make this a separate page?
			} elseif ($setting_page == 'uninstall') { ?>
				<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=<?php echo GWOLLE_GB_FOLDER; ?>/settings.php&amp;action=uninstall_gwolle_gb" method="POST">
					<?php _e("I really don't want to bother you; this page just exists to prevent you from accidentally deleting all your entries.<br />Please check the 'uninstall' checkbox and hit the button; all tables (including their rows) and all settings of Gwolle-GB will be deleted.<br /><br />Are you REALLY sure you wan't to continue? There's no 'undo'.", GWOLLE_GB_TEXTDOMAIN); ?>
					<br />
					<br />
					<input type="checkbox" name="uninstall_confirmed"> <?php _e("Yes, I'm absolutely sure of this. Proceed!", GWOLLE_GB_TEXTDOMAIN); ?>
					<br />
					<br />
					<input type="submit" class="button" value="<?php _e("Uninstall &raquo;", GWOLLE_GB_TEXTDOMAIN); ?>">
				</form>
				<?php
			} else {
				str_replace('%1',$_SERVER['PHP_SELF'] . '?page='.GWOLLE_GB_FOLDER.'/settings.php',__('Sorry, but the page you are looking for does not exists. Go back to the <a href="%1">settings page</a>', GWOLLE_GB_TEXTDOMAIN));
			}
			?>

		</div>
		<?php
	}
}
