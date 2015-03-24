<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settingstab_email() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_mail" />
	<?php
	settings_fields( 'gwolle_gb_options' );
	do_settings_sections( 'gwolle_gb_options' ); ?>
	<table class="form-table">
		<tbody>

		<tr valign="top">
			<th scope="row"><label for="admin_mail_from"><?php _e('Admin mail from address', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<input type="text" name="admin_mail_from" id="admin_mail_from" class="regular-text" value="<?php echo get_option('gwolle_gb-mail-from', false); ?>" placeholder="info@example.com" />
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

		<tr valign="top">
			<th scope="row"><label for="unsubscribe"><?php _e('Unsubscribe moderators', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<?php
				// Check if function mail() exists. If not, display a hint to the user.
				if (!function_exists('mail')) {
					echo '<p class="setting-description">' .
						__('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. You might want to install a WordPress plugin that uses SMTP instead of <code>mail()</code>. Or you can contact your hosting provider to change this.',GWOLLE_GB_TEXTDOMAIN)
						. '</p>';
				} ?>
				<select name="unsubscribe" id="unsubscribe">
					<option value="0"><?php _e('Unsubscribe User', GWOLLE_GB_TEXTDOMAIN); ?></option>
					<?php
					$user_ids = get_option('gwolle_gb-notifyByMail' );
					if ( strlen($user_ids) > 0 ) {
						$user_ids = explode( ",", $user_ids );
						if ( is_array($user_ids) && !empty($user_ids) ) {
							foreach ( $user_ids as $user_id ) {

								$user_info = get_userdata($user_id);
								if ($user_info === FALSE) {
									// Invalid $user_id
									continue;
								}
								$username = $user_info->first_name . ' ' . $user_info->last_name . ' (' . $user_info->user_email . ')';
								if ( $user_info->ID == get_current_user_id() ) {
									$username .= ' ' . __('You', GWOLLE_GB_TEXTDOMAIN);
								}
								echo '<option value="' . $user_id . '">' . $username . '</option>';
							}
						}
					} ?>
				</select><br />
				<label for="unsubscribe"><?php _e('These users have subscribed to the notification emails.', GWOLLE_GB_TEXTDOMAIN); ?><br />
				<?php _e('Select a user if you want that user to unsubscribe from the notification emails.', GWOLLE_GB_TEXTDOMAIN); ?></label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="subscribe"><?php _e('Subscribe moderators', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<select name="subscribe" id="subscribe">
					<option value="0"><?php _e('Subscribe User', GWOLLE_GB_TEXTDOMAIN); ?></option>
					<?php
					$users = array();
					$roles = array('administrator', 'editor', 'author');

					foreach ($roles as $role) :
						$users_query = new WP_User_Query( array(
							'fields' => 'all',
							'role' => $role,
							'orderby' => 'display_name'
							) );
						$results = $users_query->get_results();
						if ($results) $users = array_merge($users, $results);
					endforeach;

					if ( is_array($users) && !empty($users) ) {
						foreach ( $users as $user_info ) {

							if ($user_info === FALSE) {
								// Invalid $user_id
								continue;
							}

							// Test if already subscribed
							if ( is_array($user_ids) && !empty($user_ids) ) {
								if ( in_array($user_info->ID, $user_ids) ) {
									continue;
								}
							}

							// No capability
							if ( !user_can( $user_info, 'moderate_comments' ) ) {
								continue;
							}

							$username = $user_info->first_name . ' ' . $user_info->last_name . ' (' . $user_info->user_email . ')';
							if ( $user_info->ID == get_current_user_id() ) {
								$username .= ' ' . __('You', GWOLLE_GB_TEXTDOMAIN);
							}
							echo '<option value="' . $user_info->ID . '">' . $username . '</option>';
						}
					} ?>
				</select><br />
				<label for="subscribe"><?php _e('You can subscribe a moderator to the notification emails.', GWOLLE_GB_TEXTDOMAIN); ?><br />
				<?php _e('Select a user that you want subscribed to the notification emails.', GWOLLE_GB_TEXTDOMAIN); ?>
				<?php _e("You will only see users with the roles of Administrator, Editor and Author, who have the capability 'moderate_comments' .", GWOLLE_GB_TEXTDOMAIN); ?>
				</label>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="adminMailContent"><?php _e('Admin mail content', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<?php
				$mailText = gwolle_gb_sanitize_output( get_option('gwolle_gb-adminMailContent', false) );
				if (!$mailText) { // No text set by the user. Use the default text.
					$mailText = __("
Hello,

There is a new guestbook entry at '%blog_name%'.
You can check it at %entry_management_url%.

Have a nice day.
Your Gwolle-GB-Mailer


Website address: %blog_url%
User name: %user_name%
User email: %user_email%
Entry status: %status%
Entry content:
%entry_content%
"
, GWOLLE_GB_TEXTDOMAIN);
							} ?>
				<textarea name="adminMailContent" id="adminMailContent" style="width:400px;height:300px;" class="regular-text"><?php echo $mailText; ?></textarea>
				<br />
				<span class="setting-description">
					<?php _e('You can set the content of the mail that a notification subscriber gets on new entries. The following tags are supported:', GWOLLE_GB_TEXTDOMAIN);
					echo '<br />';
					$mailTags = array('user_email', 'user_name', 'entry_management_url', 'blog_name', 'blog_url', 'wp_admin_url', 'entry_content', 'status');
					for ($i = 0; $i < count($mailTags); $i++) {
						if ($i != 0) {
							echo ', ';
						}
						echo '%' . $mailTags[$i] . '%';
					}
					?>
				</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="mail_author"><?php _e('Mail Author', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb-mail_author', 'false') == 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="mail_author" id="mail_author">
				<label for="mail_author">
					<?php _e('Mail the author with a confirmation email.', GWOLLE_GB_TEXTDOMAIN); ?>
				</label>
				<br />
				<span class="setting-description">
					<?php _e("The author of the guestbook entry will receive an email after posting. It will have a copy of the entry.", GWOLLE_GB_TEXTDOMAIN); ?>
				</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="authorMailContent"><?php _e('Author mail content', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<?php
				$mailText = gwolle_gb_sanitize_output( get_option('gwolle_gb-authorMailContent', false) );
				if (!$mailText) { // No text set by the user. Use the default text.
					$mailText = __("
Hello,

You have just posted a new guestbook entry at '%blog_name%'.

Have a nice day.
The editors at %blog_name%.


Website address: %blog_url%
User name: %user_name%
User email: %user_email%
Entry content:
%entry_content%
"
, GWOLLE_GB_TEXTDOMAIN);
							} ?>
				<textarea name="authorMailContent" id="authorMailContent" style="width:400px;height:300px;" class="regular-text"><?php echo $mailText; ?></textarea>
				<br />
				<span class="setting-description">
					<?php _e('You can set the content of the mail that the author of the entry will receive. The following tags are supported:', GWOLLE_GB_TEXTDOMAIN);
					echo '<br />';
					$mailTags = array('user_email', 'user_name', 'blog_name', 'blog_url', 'entry_content');
					for ($i = 0; $i < count($mailTags); $i++) {
						if ($i != 0) {
							echo ', ';
						}
						echo '%' . $mailTags[$i] . '%';
					}
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

	<?php
}


