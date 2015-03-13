<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settings() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	}

	if (!get_option('gwolle_gb_version')) {
		// FIXME: do this on activation
		gwolle_gb_installSplash();
	} else {
		$active_tab = "gwolle_gb_forms";
		$saved = false;
		$uninstalled = false;
		//if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

		if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) {
			if ( isset( $_POST['gwolle_gb_tab'] ) ) {
				$active_tab = $_POST['gwolle_gb_tab'];

				switch ( $active_tab ) {
					case 'gwolle_gb_forms':

						if (isset($_POST['require_login']) && $_POST['require_login'] == 'on') {
							update_option('gwolle_gb-require_login', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-require_login', 'false');
							$saved = true;
						}

						$list = Array(
							'form_name_enabled',
							'form_name_mandatory',
							'form_city_enabled',
							'form_city_mandatory',
							'form_email_enabled',
							'form_email_mandatory',
							'form_homepage_enabled',
							'form_homepage_mandatory',
							'form_message_enabled',
							'form_message_mandatory',
							'form_antispam_enabled',
							'form_recaptcha_enabled'
							);
						$form_setting = Array();
						foreach ( $list as $item ) {
							if ( isset($_POST[$item]) && $_POST[$item] == 'on' ) {
								$form_setting[$item] = 'true';
							} else {
								$form_setting[$item] = 'false';
							}
						}
						$form_setting = serialize( $form_setting );
						update_option( 'gwolle_gb-form', $form_setting );
						$saved = true;
						break;
					case 'gwolle_gb_reading':

						// Entries per page options for Frontend
						if ( isset($_POST['entriesPerPage']) && is_numeric($_POST['entriesPerPage']) && $_POST['entriesPerPage'] > 0 ) {
							update_option('gwolle_gb-entriesPerPage', (int) $_POST['entriesPerPage']);
							$saved = true;
						}

						if (isset($_POST['showLineBreaks']) && $_POST['showLineBreaks'] == 'on') {
							update_option('gwolle_gb-showLineBreaks', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-showLineBreaks', 'false');
							$saved = true;
						}

						if (isset($_POST['showSmilies']) && $_POST['showSmilies'] == 'on') {
							update_option('gwolle_gb-showSmilies', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-showSmilies', 'false');
							$saved = true;
						}

						if (isset($_POST['linkAuthorWebsite']) && $_POST['linkAuthorWebsite'] == 'on') {
							update_option('gwolle_gb-linkAuthorWebsite', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-linkAuthorWebsite', 'false');
							$saved = true;
						}

						if (isset($_POST['admin_style']) && $_POST['admin_style'] == 'on') {
							update_option('gwolle_gb-admin_style', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-admin_style', 'false');
							$saved = true;
						}

						$list = Array(
							'read_avatar',
							'read_name',
							'read_city',
							'read_datetime',
							'read_date',
							'read_content',
							'read_editlink'
							);
						$read_setting = Array();
						foreach ( $list as $item ) {
							if ( isset($_POST[$item]) && $_POST[$item] == 'on' ) {
								$read_setting[$item] = 'true';
							} else {
								$read_setting[$item] = 'false';
							}
						}
						$read_setting = serialize( $read_setting );
						update_option( 'gwolle_gb-read', $read_setting );
						$saved = true;
						break;
					case 'gwolle_gb_admin':

						// Entries per page options for Admin
						if ( isset($_POST['entries_per_page']) && is_numeric($_POST['entries_per_page']) && $_POST['entries_per_page'] > 0 ) {
							update_option( 'gwolle_gb-entries_per_page', (int) $_POST['entries_per_page']);
							$saved = true;
						}

						if (isset($_POST['showEntryIcons']) && $_POST['showEntryIcons'] == 'on') {
							update_option('gwolle_gb-showEntryIcons', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-showEntryIcons', 'false');
							$saved = true;
						}

						break;
					case 'gwolle_gb_antispam':

						if (isset($_POST['moderate-entries']) && $_POST['moderate-entries'] == 'on') {
							update_option('gwolle_gb-moderate-entries', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-moderate-entries', 'false');
							$saved = true;
						}

						if (isset($_POST['akismet-active']) && $_POST['akismet-active'] == 'on') {
							update_option('gwolle_gb-akismet-active', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-akismet-active', 'false');
							$saved = true;
						}

						if ( isset($_POST['antispam-question']) ) {
							update_option('gwolle_gb-antispam-question', gwolle_gb_sanitize_input($_POST['antispam-question']));
							$saved = true;
						}
						if ( isset($_POST['antispam-answer']) ) {
							update_option('gwolle_gb-antispam-answer', gwolle_gb_sanitize_input($_POST['antispam-answer']));
							$saved = true;
						}

						if ( isset($_POST['recaptcha-public-key']) ) {
							update_option('recaptcha-public-key', gwolle_gb_sanitize_input($_POST['recaptcha-public-key']));
							$saved = true;
						}
						if ( isset($_POST['recaptcha-private-key']) ) {
							update_option('recaptcha-private-key', gwolle_gb_sanitize_input($_POST['recaptcha-private-key']));
							$saved = true;
						}

						break;
					case 'gwolle_gb_mail':

						if ( isset($_POST['adminMailContent']) ) {
							$mail_content = gwolle_gb_sanitize_input( $_POST['adminMailContent'] );
							update_option('gwolle_gb-adminMailContent', $mail_content);
							$saved = true;
						}

						if ( isset($_POST['admin_mail_from']) && $_POST['admin_mail_from'] != get_option('gwolle_gb-mail-from') ) {
							$admin_mail_from = gwolle_gb_sanitize_input( $_POST['admin_mail_from'] );
							if ( filter_var( $admin_mail_from, FILTER_VALIDATE_EMAIL ) ) {
								// Valid Email address.
								update_option('gwolle_gb-mail-from', $admin_mail_from);
								$saved = true;
							}
						}

						break;
					case 'gwolle_gb_uninstall':

						if (isset($_POST['uninstall_confirmed']) && $_POST['uninstall_confirmed'] == 'on') {
							// uninstall the plugin -> delete all tables and preferences of the plugin
							uninstall_gwolle_gb();
							$uninstalled = true;
						} else {
							// Uninstallation not confirmed.
						}

						break;
					default:
						// Just load the first tab
						$active_tab = "gwolle_gb_forms";
				}
			}
		} ?>

		<div class="wrap gwolle_gb">

			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Settings', GWOLLE_GB_TEXTDOMAIN); ?></h2>
			<?php

			if ( $saved ) {
				echo '
					<div id="message" class="updated fade">
						<p>' . __('Changes saved.', GWOLLE_GB_TEXTDOMAIN) . '</p>
					</div>';
			}
			?>

			<?php /* The rel attribute will be the form that becomes active */ ?>
			<h2 class="nav-tab-wrapper gwolle-nav-tab-wrapper">
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_forms')     { echo "nav-tab-active";} ?>" rel="gwolle_gb_forms"><?php _e('Form', GWOLLE_GB_TEXTDOMAIN); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_reading')   { echo "nav-tab-active";} ?>" rel="gwolle_gb_reading"><?php _e('Reading', GWOLLE_GB_TEXTDOMAIN); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_admin')     { echo "nav-tab-active";} ?>" rel="gwolle_gb_admin"><?php _e('Admin', GWOLLE_GB_TEXTDOMAIN); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_antispam')  { echo "nav-tab-active";} ?>" rel="gwolle_gb_antispam"><?php _e('Anti-spam', GWOLLE_GB_TEXTDOMAIN); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_mail')      { echo "nav-tab-active";} ?>" rel="gwolle_gb_mail"><?php _e('E-mail', GWOLLE_GB_TEXTDOMAIN); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_uninstall') { echo "nav-tab-active";} ?>" rel="gwolle_gb_uninstall"><?php _e('Uninstall', GWOLLE_GB_TEXTDOMAIN); ?></a>
			</h2>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_forms <?php if ($active_tab == 'gwolle_gb_forms') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_forms" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<tr valign="top">
						<th scope="row"><label for="require_login"><?php _e('Require Login', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" id="require_login" name="require_login" <?php
								if ( get_option( 'gwolle_gb-require_login', 'false' ) === 'true' ) {
									echo 'checked="checked"';
								}
								?> />
							<label for="require_login"><?php _e('Require user to be logged in.', GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php _e('Only allow logged-in users to add a guestbook entry.', GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>

					</tbody>
				</table>
				<table class="form-table">
					<tbody>

					<?php $form_setting = gwolle_gb_get_setting( 'form' ); ?>

					<tr valign="top">
						<td colspan="3"><h3><?php _e('Configure the form that is shown to visitors.', GWOLLE_GB_TEXTDOMAIN); ?></h3></td>
					</tr>

					<tr valign="top">
						<th scope="row"><label for="form_name_enabled"><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_name_enabled" name="form_name_enabled"<?php
								if ( isset($form_setting['form_name_enabled']) && $form_setting['form_name_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_name_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<input type="checkbox" id="form_name_mandatory" name="form_name_mandatory"<?php
								if ( isset($form_setting['form_name_mandatory']) && $form_setting['form_name_mandatory']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_name_mandatory"><?php _e('Mandatory', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="form_city_enabled"><?php _e('City', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_city_enabled" name="form_city_enabled"<?php
								if ( isset($form_setting['form_city_enabled']) && $form_setting['form_city_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_city_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<input type="checkbox" id="form_city_mandatory" name="form_city_mandatory"<?php
								if ( isset($form_setting['form_city_mandatory']) && $form_setting['form_city_mandatory']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_city_mandatory"><?php _e('Mandatory', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="form_email_enabled"><?php _e('Email', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_email_enabled" name="form_email_enabled"<?php
								if ( isset($form_setting['form_email_enabled']) && $form_setting['form_email_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_email_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<input type="checkbox" id="form_email_mandatory" name="form_email_mandatory"<?php
								if ( isset($form_setting['form_email_mandatory']) && $form_setting['form_email_mandatory']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_email_mandatory"><?php _e('Mandatory', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for=""form_homepage_enabled><?php _e('Website', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_homepage_enabled" name="form_homepage_enabled"<?php
								if ( isset($form_setting['form_homepage_enabled']) && $form_setting['form_homepage_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_homepage_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<input type="checkbox" id="form_homepage_mandatory" name="form_homepage_mandatory"<?php
								if ( isset($form_setting['form_homepage_mandatory']) && $form_setting['form_homepage_mandatory']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_homepage_mandatory"><?php _e('Mandatory', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="form_message_enabled"><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_message_enabled" name="form_message_enabled"<?php
								if ( isset($form_setting['form_message_enabled']) && $form_setting['form_message_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_message_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<input type="checkbox" id="form_message_mandatory" name="form_message_mandatory"<?php
								if ( isset($form_setting['form_message_mandatory']) && $form_setting['form_message_mandatory']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_message_mandatory"><?php _e('Mandatory', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="form_antispam_enabled"><?php _e('Custom Anti-spam', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_antispam_enabled" name="form_antispam_enabled"<?php
								if ( isset($form_setting['form_antispam_enabled']) && $form_setting['form_antispam_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_antispam_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<?php _e('When enabled it is mandatory.', GWOLLE_GB_TEXTDOMAIN); ?>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="form_recaptcha_enabled"><?php _e('reCAPTCHA', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="form_recaptcha_enabled" name="form_recaptcha_enabled"<?php
								if ( isset($form_setting['form_recaptcha_enabled']) && $form_setting['form_recaptcha_enabled']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="form_recaptcha_enabled"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
						<td>
							<?php _e('When enabled it is mandatory.', GWOLLE_GB_TEXTDOMAIN); ?>
						</td>
					</tr>
					<tr>
						<td colspan="3">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_reading <?php if ($active_tab == 'gwolle_gb_reading') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_reading" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<tr valign="top">
						<th scope="row"><label for="entriesPerPage"><?php _e('Entries per page on the frontend', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<select name="entriesPerPage" id="entriesPerPage">
								<?php $entriesPerPage = get_option( 'gwolle_gb-entriesPerPage', 20 );
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
						<th scope="row"><label for="showLineBreaks"><?php _e('Line breaks', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" id="showLineBreaks" name="showLineBreaks"<?php
								if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="showLineBreaks"><?php _e('Show line breaks.', GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php _e('Show line breaks as the entry authors entered them. (May result in very long entries. Is turned off by default.)', GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="showSmilies"><?php _e('Smileys', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" id="showSmilies" name="showSmilies"<?php
								if ( get_option( 'gwolle_gb-showSmilies', 'true' ) === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="showSmilies"><?php _e('Display smileys as images.', GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php echo sprintf( __("Replaces smileys in entries like :) with their image %s. Uses the WP smiley replacer, so check on that one if you'd like to add new/more smileys.", GWOLLE_GB_TEXTDOMAIN), convert_smilies(':)')); ?></span>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="linkAuthorWebsite"><?php _e('Links', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" id="linkAuthorWebsite" name="linkAuthorWebsite"<?php
								if ( get_option( 'gwolle_gb-linkAuthorWebsite', 'true' ) === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="linkAuthorWebsite"><?php _e("Link authors' name to their website.", GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php _e("The author of an entry can set his/her website. If this setting is checked, his/her name will be a link to that website.", GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="admin_style"><?php _e('Admin Entry Styling', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" id="admin_style" name="admin_style"<?php
								if ( get_option( 'gwolle_gb-admin_style', 'true' ) === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="admin_style"><?php _e("Admin entries get a special CSS styling.", GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php _e("Admin entries get a special CSS styling. It will get a lightgrey background.", GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<?php $read_setting = gwolle_gb_get_setting( 'read' ); ?>

					<tr valign="top">
						<td colspan="2"><h3><?php _e('Configure the parts of the entries that are shown to visitors.', GWOLLE_GB_TEXTDOMAIN); ?></h3></td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="read_avatar"><?php _e('Avatar', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_avatar" name="read_avatar"<?php
								if ( isset($read_setting['read_avatar']) && $read_setting['read_avatar']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_avatar"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="read_name"><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_name" name="read_name"<?php
								if ( isset($read_setting['read_name']) && $read_setting['read_name']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_name"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><label for="read_city"><?php _e('City', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_city" name="read_city"<?php
								if ( isset($read_setting['read_city']) && $read_setting['read_city']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_city"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><label for="read_datetime"><?php _e('Date and Time', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_datetime" name="read_datetime"<?php
								if ( isset($read_setting['read_datetime']) && $read_setting['read_datetime']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_datetime"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label><br />
							<span class="setting-description"><?php _e("Setting this will show the date and the time of the entry.", GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><label for="read_date"><?php _e('Date', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_date" name="read_date"<?php
								if ( isset($read_setting['read_date']) && $read_setting['read_date']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_date"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label><br />
							<span class="setting-description"><?php _e("Setting this will show the date of the entry. If Date and Time above are enabled, that setting has preference.", GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><label for="read_content"><?php _e('Content', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_content" name="read_content"<?php
								if ( isset($read_setting['read_content']) && $read_setting['read_content']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_content"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><label for="read_editlink"><?php _e('Edit link', GWOLLE_GB_TEXTDOMAIN); ?>:</label></th>
						<td>
							<input type="checkbox" id="read_editlink" name="read_editlink"<?php
								if ( isset($read_setting['read_editlink']) && $read_setting['read_editlink']  === 'true' ) {
									echo ' checked="checked"';
								}
								?> />
							<label for="read_editlink"><?php _e('Enabled', GWOLLE_GB_TEXTDOMAIN); ?></label><br />
							<span class="setting-description"><?php _e("A link to the editor will be added to the content. Only visible for moderators.", GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_admin <?php if ($active_tab == 'gwolle_gb_admin') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_admin" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<tr valign="top">
						<th scope="row"><label for="entries_per_page"><?php _e('Entries per page in the admin', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<select name="entries_per_page" id="entries_per_page">
								<?php $entries_per_page = get_option( 'gwolle_gb-entries_per_page', 20 );
								$presets = array(5, 10, 15, 20, 25, 30, 40, 50, 60, 70, 80, 90, 100, 120, 150, 200, 250);
								for ($i = 0; $i < count($presets); $i++) {
									echo '<option value="' . $presets[$i] . '"';
									if ($presets[$i] == $entries_per_page) {
										echo ' selected="selected"';
									}
									echo '>' . $presets[$i] . ' ' . __('Entries', GWOLLE_GB_TEXTDOMAIN) . '</option>';
								}
								?>
							</select>
							<br />
							<span class="setting-description"><?php _e('Number of entries shown in the admin.', GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><label for="showEntryIcons"><?php _e('Entry icons', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" <?php
								if ( get_option( 'gwolle_gb-showEntryIcons', 'true' ) === 'true' ) {
									echo 'checked="checked"';
								}
								?> name="showEntryIcons" id="showEntryIcons" /><label for="showEntryIcons"><?php _e('Show entry icons', GWOLLE_GB_TEXTDOMAIN); ?></label>
							<br />
							<span class="setting-description"><?php _e('These icons are shown in every entry row of the admin list, so that you know its status (checked, spam and trash).', GWOLLE_GB_TEXTDOMAIN); ?></span>
						</td>
					</tr>


					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_antispam <?php if ($active_tab == 'gwolle_gb_antispam') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_antispam" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<tr valign="top">
						<th scope="row"><label for="moderate-entries"><?php _e('Moderate Guestbook', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input <?php
								if (get_option( 'gwolle_gb-moderate-entries', 'true') == 'true') {
									echo 'checked="checked"';
								} ?>
								type="checkbox" name="moderate-entries" id="moderate-entries">
							<label for="moderate-entries">
								<?php _e('Moderate entries before publishing them.', GWOLLE_GB_TEXTDOMAIN); ?>
							</label>
							<br />
							<span class="setting-description">
								<?php _e("New entries have to be unlocked by a moderator before they are visible to the public.", GWOLLE_GB_TEXTDOMAIN); ?>
								<br />
								<?php _e("It is recommended that you turn this on, because you are responsible for the content on your website.", GWOLLE_GB_TEXTDOMAIN); ?>
							</span>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row">
							<label for="akismet-active">Akismet</label>
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
								echo sprintf( __("Sorry, wasn't able to locate your <strong>WordPress API key</strong>. You can enter it at the <a href=\"%s\">Akismet configuration page</a>.", GWOLLE_GB_TEXTDOMAIN), 'options-general.php?page=akismet-key-config' );
							} else {
								// Akismet is installed and a WordPress api key exists
								echo '<input ';
								if ( get_option( 'gwolle_gb-akismet-active', 'false' ) === 'true' ) {
									echo 'checked="checked" ';
								}
								echo 'name="akismet-active" id="akismet-active" type="checkbox" />
									<label for="akismet-active">
									' . __('Use Akismet', GWOLLE_GB_TEXTDOMAIN) . '
									</label><br />';
								_e("The WordPress API key has been found, so you can start using Akismet right now.", GWOLLE_GB_TEXTDOMAIN);
							}
							?>
						</td>
					</tr>


					<?php
					$antispam_question = get_option('gwolle_gb-antispam-question');
					$antispam_answer   = get_option('gwolle_gb-antispam-answer');
					?>
					<tr valign="top">
						<th scope="row"><label for="antispam-question"><?php _e('Custom Anti-Spam Security Question', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<div>
								<input name="antispam-question" type="text" id="antispam-question" value="<?php echo $antispam_question; ?>" class="regular-text" placeholder="<?php _e('12 + six =', GWOLLE_GB_TEXTDOMAIN); ?>" />
								<label for="antispam-question" class="setting-description"><?php _e('Custom security question to battle spam.', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<input name="antispam-answer" type="text" id="antispam-answer" value="<?php echo $antispam_answer; ?>" class="regular-text" placeholder="<?php _e('18', GWOLLE_GB_TEXTDOMAIN); ?>" />
								<label for="antispam-answer" class="setting-description"><?php _e('The answer to your security question.', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<span class="setting-description"><?php _e('You can ask your visitors to answer a custom security question, so only real people can post an entry.', GWOLLE_GB_TEXTDOMAIN); ?></span>
							</div>
						</td>
					</tr>


					<?php
					$recaptcha_publicKey = get_option('recaptcha-public-key');
					$recaptcha_privateKey = get_option('recaptcha-private-key');
					?>
					<tr valign="top">
						<th scope="row"><label for="recaptcha-public-key">reCAPTCHA</label><br />
							<span class="setting-description">
								<a href="http://www.google.com/recaptcha/intro/index.html" title="<?php _e('Learn more about reCAPTCHA...', GWOLLE_GB_TEXTDOMAIN); ?>" target="_blank"><?php _e("What's that?", GWOLLE_GB_TEXTDOMAIN); ?></a>
							</span>
						</th>
						<td>
							<div
								<?php
								if ( !class_exists('ReCaptcha') && class_exists('ReCaptchaResponse') ) {
									echo 'style="display:none;"';
								} ?>
								>
								<input name="recaptcha-public-key" type="text" id="recaptcha-public-key" value="<?php echo $recaptcha_publicKey; ?>" class="regular-text" />
								<label for="recaptcha-public-key" class="setting-description"><?php _e('<strong>Site (Public)</strong> key of your reCAPTCHA account', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<input name="recaptcha-private-key" type="text" id="recaptcha-private-key" value="<?php echo $recaptcha_privateKey; ?>" class="regular-text" />
								<label for="recaptcha-private-key" class="setting-description"><?php _e('<strong>Secret</strong> key of your reCAPTCHA account', GWOLLE_GB_TEXTDOMAIN); ?></label>
								<br />
								<span class="setting-description"><?php _e('The keys can be found at your', GWOLLE_GB_TEXTDOMAIN); ?> <a href="https://www.google.com/recaptcha/intro/index.html" title="<?php _e('Go to my reCAPTCHA sites...', GWOLLE_GB_TEXTDOMAIN); ?>" target="_blank"><?php _e('reCAPTCHA sites overview', GWOLLE_GB_TEXTDOMAIN); ?></a>.</span>
							</div>
							<?php
							if ( class_exists('ReCaptcha') && class_exists('ReCaptchaResponse') ) { ?>
								<p class="setting-description"><?php _e('<strong>Warning:</strong> Apparently you already use a reCAPTCHA library in your theme or another plugin. The reCAPTCHA library in Gwolle-GB will not be loaded, and the found one will be used instead. This might give unexpected results.', GWOLLE_GB_TEXTDOMAIN); ?></p><?php
							} else if ( !class_exists('ReCaptcha') && class_exists('ReCaptchaResponse') ) { ?>
								<p class="setting-description"><?php _e('<strong>Warning:</strong> Apparently you already use a reCAPTCHA library in your theme or another plugin. However, this is an old and incompatible version, so reCAPTCHA will not be used for Gwolle-GB.', GWOLLE_GB_TEXTDOMAIN); ?></p><?php
							} ?>
						</td>
					</tr>


					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_mail <?php if ($active_tab == 'gwolle_gb_mail') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_mail" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<tr valign="top">
						<th scope="row"><label for="adminMailContent"><?php _e('Admin mail content', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<?php
							$adminMailContent = get_option('gwolle_gb-adminMailContent', false);
							if (!$adminMailContent) { // No text set by the user. Use the default text.
								$mailText = __("Hello,\n\nThere is a new guestbook entry at '%blog_name%'.\nYou can check it at %entry_management_url%.\n\nHave a nice day!\nYour Gwolle-GB-Mailer", GWOLLE_GB_TEXTDOMAIN);
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
						<th scope="row"><label for="admin_mail_from"><?php _e('Admin mail from address', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input name="admin_mail_from" id="admin_mail_from" class="regular-text" value="<?php echo get_option('gwolle_gb-mail-from', false); ?>" placeholder="info@example.com" />
							<br />
							<span class="setting-description">
								<?php
								_e('You can set the email address that is used for the From header of the mail that a notification subscriber gets on new entries.', GWOLLE_GB_TEXTDOMAIN);
								echo '<br />';
								_e('By default the main admin address is used from General >> Settings.', GWOLLE_GB_TEXTDOMAIN);
								?>
							</span>
						</td>
					</tr>


					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_uninstall <?php if ($active_tab == 'gwolle_gb_uninstall') { echo "active";} ?>" method="post" action="">
				<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_uninstall" />
				<?php
				settings_fields( 'gwolle_gb_options' );
				do_settings_sections( 'gwolle_gb_options' ); ?>
				<table class="form-table">
					<tbody>

					<?php
					if ( $uninstalled == true ) { ?>
						<tr valign="top">
							<th scope="row"><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?></th>
							<td>
								<div id="message" class="updated error fade">
									<p><?php _e('The entries and settings have been removed.', GWOLLE_GB_TEXTDOMAIN); ?></p>
									<p><?php _e('The plugin is deactivated.', GWOLLE_GB_TEXTDOMAIN); ?></p>
									<p><?php echo __('You can now go to your', GWOLLE_GB_TEXTDOMAIN) . ' <a href="' . admin_url() . '">' . __('dashboard.', GWOLLE_GB_TEXTDOMAIN) . '</a>'; ?>
								</div>
							</td>
						</tr>
						<?php
					}
					?>


					<tr valign="top">
						<th scope="row" style="color:#FF0000;"><label for="blogdescription"><?php _e('Uninstall', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<?php _e('Uninstalling means that all database entries are removed (settings and entries).', GWOLLE_GB_TEXTDOMAIN);
							echo '<br />';
							_e('This can <strong>not</strong> be undone.', GWOLLE_GB_TEXTDOMAIN);
							?>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row" style="color:#FF0000;"><label for="uninstall_confirmed"><?php _e('Confirm', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" name="uninstall_confirmed" id="uninstall_confirmed">
							<label for="uninstall_confirmed"><?php _e("Yes, I'm absolutely sure of this. Proceed!", GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row" style="color:#FF0000;"><label for="delete_recaptchaKeys"><?php _e('reCAPTCHA', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
						<td>
							<input type="checkbox" name="delete_recaptchaKeys" id="delete_recaptchaKeys">
							<label for="delete_recaptchaKeys"><?php _e("Also delete the reCAPTCHA keys", GWOLLE_GB_TEXTDOMAIN); ?></label>
						</td>
					</tr>


					<tr>
						<td colspan="2">
							<p class="submit">
								<input type="submit" name="Submit" class="button-primary" value="<?php _e('Uninstall &raquo;', GWOLLE_GB_TEXTDOMAIN); ?>" />
							</p>
						</td>
					</tr>

					</tbody>
				</table>
			</form>

		</div> <!-- wrap -->
		<?php
	}
}
