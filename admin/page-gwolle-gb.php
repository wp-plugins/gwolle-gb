<?php
 /**
 * welcome.php
 * Shows the overview screen with the widget-like windows.
 * Thanks to Alex Rabe for writing clean and understandable code!
 * Also thanks to http://andrewferguson.net/2008/09/26/using-add_meta_box/ !
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


/* Metaboxes on the left */
function gwolle_gb_overview(){

	// Calculate the number of entries
	$count = Array();
	$count['checked']    = gwolle_gb_get_entry_count(array(
			'checked' => 'checked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['unchecked'] = gwolle_gb_get_entry_count(array(
			'checked' => 'unchecked',
			'trash'   => 'notrash',
			'spam'    => 'nospam'
		));
	$count['spam']    = gwolle_gb_get_entry_count(array( 'spam'  => 'spam'  ));
	$count['trash']   = gwolle_gb_get_entry_count(array( 'trash' => 'trash' ));
	$count['all']     = gwolle_gb_get_entry_count(array( 'all'   => 'all'   ));
	?>

	<div class="table table_content gwolle_gb">
		<h3><?php _e('Overview','gwolle-gb'); ?></h3>

		<table>
			<tbody>
				<tr class="first">
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php">
							<?php echo $count['all']; ?>
						</a>
					</td>

					<td class="t" style="color:#0000f0;">
						<?php
							if ($count['all']==1) {
								_e('Entry total','gwolle-gb');
							}
							else {
								_e('Entries total','gwolle-gb');
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked">
						<?php echo $count['checked']; ?>
					</a></td>
					<td class="t" style="color:#008000;">
						<?php
							if ($count['checked'] == 1) {
								_e('Unlocked entry','gwolle-gb');
							} else {
								_e('Unlocked entries','gwolle-gb');;
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked">
						<?php echo $count['unchecked']; ?>
					</a></td>
					<td class="t" style="color:#ff6f00;">
						<?php
							if ($count['unchecked'] == 1) {
								_e('New entry','gwolle-gb');
							} else {
								_e('New entries','gwolle-gb');
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam">
						<?php echo $count['spam']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php
							if ($count['spam'] == 1) {
								_e('Spam entry','gwolle-gb');
							} else {
								_e('Spam entries','gwolle-gb');
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=trash">
						<?php echo $count['trash']; ?>
					</a></td>
					<td class="t" style="color:#FF0000;">
						<?php
							if ($count['trash'] == 1) {
								_e('Trashed entry','gwolle-gb');
							} else {
								_e('Trashed entries','gwolle-gb');
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

			</tbody>
		</table>
	</div><!-- Table-DIV -->
	<div class="versions">
		<p>
			<?php
			$postid = gwolle_gb_get_postid();
			if ( $postid ) {
				$permalink = get_bloginfo('url') . '?p=' . $postid;
				?>
				<a class="button rbutton button button-primary" href="<?php echo $permalink; ?>"><?php esc_attr_e('View Guestbook','gwolle-gb'); ?></a>
				<?php
			} ?>

			<a class="button rbutton button button-primary" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php"><?php esc_attr_e('Write admin entry','gwolle-gb'); ?></a>
		</p>
	</div>
<?php }


function gwolle_gb_notification() {

	// Check if function mail() exists. If not, display a hint to the user.
	if (!function_exists('mail')) {
		echo '<p class="setting-description">' .
			__('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. You might want to install a WordPress plugin that uses SMTP instead of <code>mail()</code>. Or you can contact your hosting provider to change this.','gwolle-gb')
			. '</p>';
	}
	$current_user_id = get_current_user_id();;
	$currentUserNotification = false;
	$user_ids = get_option('gwolle_gb-notifyByMail' );
	if ( strlen($user_ids) > 0 ) {
		$user_ids = explode( ",", $user_ids );
		if ( is_array($user_ids) && !empty($user_ids) ) {
			foreach ( $user_ids as $user_id ) {
				if ( $user_id == $current_user_id ) {
					$currentUserNotification = true;
				}
			}
		}
	} ?>
	<p>
		<form name="gwolle_gb_welcome" method="post" action="">
			<?php
			settings_fields( 'gwolle_gb_options' );
			do_settings_sections( 'gwolle_gb_options' );
			?>
			<input name="notify_by_mail" type="checkbox" id="notify_by_mail" <?php
				if ( $currentUserNotification ) {
					echo 'checked="checked"';
				} ?> >
			<label for="notify_by_mail" class="setting-description"><?php _e('Send me an e-mail when a new entry has been posted.', 'gwolle-gb'); ?></label>
			<p class="submit">
				<input type="submit" name="Submit" class="button-primary" value="<?php esc_attr_e('Save setting', 'gwolle-gb'); ?>" />
			</p>
		</form>
	</p>
	<div>
		<?php _e('The following users have subscribed to this service:', 'gwolle-gb');

		if ( is_array($user_ids) && !empty($user_ids) ) {
			echo '<ul style="font-size:10px;font-style:italic;list-style-type:disc;padding-left:14px;">';
			foreach ( $user_ids as $user_id ) {
				$user_info = get_userdata($user_id);
				if ($user_info === FALSE) {
					// Invalid $user_id
					continue;
				}
				echo '<li>';
				if ( $user_info->ID == get_current_user_id() ) {
					echo '<strong>' . __('You', 'gwolle-gb') . '</strong>';
				} else {
					echo $user_info->first_name . ' ' . $user_info->last_name;
				}
				echo ' (' . $user_info->user_email . ')';
				echo '</li>';
			}
			echo '</ul>';
		} else {
			echo '<br /><i>(' . __('No subscriber yet', 'gwolle-gb') . ')</i>';
		}
		?>
	</div>
	<?php
}


function gwolle_gb_donate() {
	?>
	<p><?php _e('This plugin is being maintained by Marcel Pol from', 'gwolle-gb'); ?>
		<a href="http://zenoweb.nl" target="_blank" title="ZenoWeb">ZenoWeb</a>.
	</p>

	<h3><?php _e('Review this plugin.', 'gwolle-gb'); ?></h3>
	<p><?php _e('If this plugin has any value to you, then please leave a review at', 'gwolle-gb'); ?>
		<a href="https://wordpress.org/support/view/plugin-reviews/gwolle-gb" target="_blank" title="<?php esc_attr_e('The plugin page at wordpress.org.', 'gwolle-gb'); ?>">
			<?php _e('the plugin page at wordpress.org', 'gwolle-gb'); ?></a>.
	</p>

	<h3><?php _e('Donate to the EFF.', 'gwolle-gb'); ?></h3>
	<p><?php _e('The Electronic Frontier Foundation is one of the few organisations that wants to keep the internet a free place.', 'gwolle-gb'); ?></p>
	<p><a href="https://supporters.eff.org/donate" target="_blank" title="<?php esc_attr_e('Please donate to the EFF.', 'gwolle-gb'); ?>"><?php _e('Please donate to the EFF.', 'gwolle-gb'); ?></a></p>

	<h3><?php _e('Donate to the maintainer.', 'gwolle-gb'); ?></h3>
	<p><?php _e('If you rather want to donate to the maintainer of the plugin, you can donate through PayPal.', 'gwolle-gb'); ?></p>
	<p><?php _e('Donate through', 'gwolle-gb'); ?> <a href="https://www.paypal.com" target="_blank" title="<?php esc_attr_e('Donate to the maintainer.', 'gwolle-gb'); ?>"><?php _e('PayPal', 'gwolle-gb'); ?></a>
		<?php _e('to', 'gwolle-gb'); ?> marcel@timelord.nl.
	</p>
	<?php
}


/* Metaboxes on the right */
function gwolle_gb_overview_help() {
	echo '<h3>
	'.__('This is how you can get your guestbook displayed on your website:', 'gwolle-gb').'</h3>
	<ul class="ul-disc">
		<li>'.__('Create a new page.', 'gwolle-gb').'</li>
		<li>'.__("Choose a title and set &quot;[gwolle_gb]&quot; (without the quotes) as the content.", 'gwolle-gb').'</li>
		<li>'.__("It is probably a good idea to disable comments on that page; otherwise, your visitors might get a little confused.",'gwolle-gb').'</li>
	</ul>';
}


function gwolle_gb_overview_help_more() {
	echo '<h3>
	'.__('These entries will be visible for your visitors:', 'gwolle-gb').'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Checked.', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Spam.', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Trash.','gwolle-gb').'</li>
	</ul>';

	echo '<h3>
	'.__('The Main Menu counter counts the following entries:', 'gwolle-gb').'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Unchecked (You might want to moderate them).', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Spam (You might want to check them).', 'gwolle-gb').'</li>
		<li>'.__('Not marked as Trash (You decide what goes to the trash).','gwolle-gb').'</li>
	</ul>';
}


function gwolle_gb_overview_thanks() {
	echo '
	<ul class="settings">
		<li><a href="http://akismet.com/tos/" target="_blank">Akismet</a></li>
		<li><a href="http://markitup.jaysalvat.com/" target="_blank">MarkItUp</a></li>
		<li><a href="https://wordpress.org/plugins/really-simple-captcha/" target="_blank">Really Simple CAPTCHA plugin</a></li>
	</ul>';
}


/* Show the page */
function gwolle_gb_welcome() {

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}

	/* Save notification setting */
	$saved = false;
	if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) {

		// E-mail notification option
		if ( isset($_POST['notify_by_mail']) && $_POST['notify_by_mail'] == 'on' ) {
			// Turn the notification ON for the current user.
			$user_id = get_current_user_id();
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
		} elseif ( !isset($_POST['notify_by_mail']) ) {
			// Turn the notification OFF for the current user
			$user_id = get_current_user_id();
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

	}


	if (get_option('gwolle_gb_version', false) === false) {
		gwolle_gb_installSplash();
	} else {
		add_meta_box('dashboard_right_now', __('Welcome to the Guestbook!','gwolle-gb'), 'gwolle_gb_overview', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_notification', __('E-mail Notifications', 'gwolle-gb'), 'gwolle_gb_notification', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_donate', __('Review and Donate', 'gwolle-gb'), 'gwolle_gb_donate', 'gwolle_gb_welcome', 'left', 'core');

		add_meta_box('gwolle_gb_help', __('Help', 'gwolle-gb'), 'gwolle_gb_overview_help', 'gwolle_gb_welcome', 'right', 'core');
		add_meta_box('gwolle_gb_help_more', __('Help', 'gwolle-gb'), 'gwolle_gb_overview_help_more', 'gwolle_gb_welcome', 'right', 'core');
		add_meta_box('gwolle_gb_thanks', __('This plugin uses the following scripts and services:','gwolle-gb'), 'gwolle_gb_overview_thanks', 'gwolle_gb_welcome', 'right', 'core');

		?>
		<div class="wrap gwolle_gb">
			<div id="icon-gwolle-gb"><br /></div>
			<h1><?php _e('Gwolle Guestbook', 'gwolle-gb'); ?></h1>

			<?php
			if ( $saved ) {
				echo '
					<div id="message" class="updated fade notice is-dismissible">
						<p>' . __('Changes saved.', 'gwolle-gb') . '</p>
					</div>';
			} ?>

			<div id="dashboard-widgets-wrap" class="gwolle_gb_welcome">
				<div id="dashboard-widgets" class="metabox-holder">
					<div class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes('gwolle_gb_welcome', 'left', null); ?>
						</div>
					</div>
					<div class="postbox-container">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes('gwolle_gb_welcome', 'right', ''); ?>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}



