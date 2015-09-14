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
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
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
						/* Form Settings */

						if (isset($_POST['require_login']) && $_POST['require_login'] == 'on') {
							update_option('gwolle_gb-require_login', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-require_login', 'false');
							$saved = true;
						}

						if (isset($_POST['labels_float']) && $_POST['labels_float'] == 'on') {
							update_option('gwolle_gb-labels_float', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-labels_float', 'false');
							$saved = true;
						}

						// Always save it, even when empty, for MultiLingual plugins.
						$header = gwolle_gb_sanitize_input( $_POST['header'] );
						update_option('gwolle_gb-header', $header);
						$saved = true;

						$notice = gwolle_gb_sanitize_input( $_POST['notice'] );
						update_option('gwolle_gb-notice', $notice);
						$saved = true;

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
							'form_bbcode_enabled',
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
						/* Reading Settings */

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

						if ( isset($_POST['excerpt_length']) && is_numeric($_POST['excerpt_length']) ) {
							update_option('gwolle_gb-excerpt_length', (int) $_POST['excerpt_length']);
							if ($_POST['excerpt_length'] > 0) {
								// Will not be shown anyway with wp_trim_words()
								update_option('gwolle_gb-showLineBreaks', 'false');
							}
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

						if (isset($_POST['paginate_all']) && $_POST['paginate_all'] == 'on') {
							update_option('gwolle_gb-paginate_all', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-paginate_all', 'false');
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
						/* Admin Settings */

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
						/* Anti-Spam Settings */

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

						break;
					case 'gwolle_gb_mail':
						/* Mail Settings */

						if ( isset($_POST['admin_mail_from']) && $_POST['admin_mail_from'] != gwolle_gb_sanitize_output( get_option('gwolle_gb-mail-from') ) ) {
							$admin_mail_from = gwolle_gb_sanitize_input( $_POST['admin_mail_from'] );
							if ( filter_var( $admin_mail_from, FILTER_VALIDATE_EMAIL ) ) {
								// Valid Email address.
								update_option('gwolle_gb-mail-from', $admin_mail_from);
								$saved = true;
							}
						}

						if ( isset($_POST['unsubscribe']) && $_POST['unsubscribe'] > 0 ) {
							$user_id = (int) $_POST['unsubscribe'];
							$user_ids = Array();

							$user_ids_old = get_option('gwolle_gb-notifyByMail' );
							if ( strlen($user_ids_old) > 0 ) {
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

						if ( isset($_POST['subscribe']) && $_POST['subscribe'] > 0 ) {
							$user_id = (int) $_POST['subscribe'];
							$user_ids = Array();

							$user_ids_old = get_option('gwolle_gb-notifyByMail' );
							if ( strlen($user_ids_old) > 0 ) {
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
							$user_ids[] = $user_id; // Really add it.

							$user_ids = implode(",", $user_ids);
							update_option('gwolle_gb-notifyByMail', $user_ids);
							$saved = true;
						}

						if ( isset($_POST['adminMailContent']) ) {
							$mail_content = gwolle_gb_sanitize_input( $_POST['adminMailContent'] );
							update_option('gwolle_gb-adminMailContent', $mail_content);
							$saved = true;
						}

						if (isset($_POST['mail_author']) && $_POST['mail_author'] == 'on') {
							update_option('gwolle_gb-mail_author', 'true');
							$saved = true;
						} else {
							update_option('gwolle_gb-mail_author', 'false');
							$saved = true;
						}

						if ( isset($_POST['authorMailContent']) ) {
							$mail_content = gwolle_gb_sanitize_input( $_POST['authorMailContent'] );
							update_option('gwolle_gb-authorMailContent', $mail_content);
							$saved = true;
						}

						if ( isset($_POST['gwolle_gb-mail_admin_replyContent']) ) {
							$mail_content = gwolle_gb_sanitize_input( $_POST['gwolle_gb-mail_admin_replyContent'] );
							update_option('gwolle_gb-mail_admin_replyContent', $mail_content);
							$saved = true;
						}

						break;
					case 'gwolle_gb_uninstall':
						/* Uninstall */

						if (isset($_POST['gwolle_gb_uninstall_confirmed']) && $_POST['gwolle_gb_uninstall_confirmed'] == 'on') {
							// uninstall the plugin -> delete all tables and preferences of the plugin
							gwolle_gb_uninstall();
							$uninstalled = true;
						} else {
							// Uninstallation not confirmed.
						}

						break;
					default:
						/* Just load the first tab */
						$active_tab = "gwolle_gb_forms";
				}
			}
		} ?>

		<div class="wrap gwolle_gb">

			<div id="icon-gwolle-gb"><br /></div>
			<h1><?php _e('Settings', 'gwolle-gb'); ?></h1>

			<?php
			if ( $saved ) {
				echo '
					<div id="message" class="updated fade notice is-dismissible">
						<p>' . __('Changes saved.', 'gwolle-gb') . '</p>
					</div>';
			} ?>

			<?php /* The rel attribute will be the form that becomes active */ ?>
			<h2 class="nav-tab-wrapper gwolle-nav-tab-wrapper">
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_forms')     { echo "nav-tab-active";} ?>" rel="gwolle_gb_forms"><?php _e('Form', 'gwolle-gb'); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_reading')   { echo "nav-tab-active";} ?>" rel="gwolle_gb_reading"><?php _e('Reading', 'gwolle-gb'); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_admin')     { echo "nav-tab-active";} ?>" rel="gwolle_gb_admin"><?php _e('Admin', 'gwolle-gb'); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_antispam')  { echo "nav-tab-active";} ?>" rel="gwolle_gb_antispam"><?php _e('Anti-spam', 'gwolle-gb'); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_mail')      { echo "nav-tab-active";} ?>" rel="gwolle_gb_mail"><?php _e('E-mail', 'gwolle-gb'); ?></a>
				<a href="#" class="nav-tab <?php if ($active_tab == 'gwolle_gb_uninstall') { echo "nav-tab-active";} ?>" rel="gwolle_gb_uninstall"><?php _e('Uninstall', 'gwolle-gb'); ?></a>
			</h2>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_forms <?php if ($active_tab == 'gwolle_gb_forms') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_form(); ?>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_reading <?php if ($active_tab == 'gwolle_gb_reading') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_reading(); ?>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_admin <?php if ($active_tab == 'gwolle_gb_admin') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_admin(); ?>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_antispam <?php if ($active_tab == 'gwolle_gb_antispam') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_antispam(); ?>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_mail <?php if ($active_tab == 'gwolle_gb_mail') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_email(); ?>
			</form>


			<form name="gwolle_gb_options" class="gwolle_gb_options gwolle_gb_uninstall <?php if ($active_tab == 'gwolle_gb_uninstall') { echo "active";} ?>" method="post" action="">
				<?php gwolle_gb_page_settingstab_uninstall( $uninstalled ); ?>
			</form>

		</div> <!-- wrap -->
		<?php
	}
}
