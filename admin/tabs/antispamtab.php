<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settingstab_antispam() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	} ?>

	<input type="hidden" id="gwolle_gb_tab" name="gwolle_gb_tab" value="gwolle_gb_antispam" />
	<?php
	settings_fields( 'gwolle_gb_options' );
	do_settings_sections( 'gwolle_gb_options' ); ?>
	<table class="form-table">
		<tbody>

		<tr valign="top">
			<th scope="row"><label for="moderate-entries"><?php _e('Moderate Guestbook', 'gwolle-gb'); ?></label></th>
			<td>
				<input <?php
					if (get_option( 'gwolle_gb-moderate-entries', 'true') == 'true') {
						echo 'checked="checked"';
					} ?>
					type="checkbox" name="moderate-entries" id="moderate-entries">
				<label for="moderate-entries">
					<?php _e('Moderate entries before publishing them.', 'gwolle-gb'); ?>
				</label>
				<br />
				<span class="setting-description">
					<?php _e("New entries have to be unlocked by a moderator before they are visible to the public.", 'gwolle-gb'); ?>
					<br />
					<?php _e("It is recommended that you turn this on, because you are responsible for the content on your website.", 'gwolle-gb'); ?>
				</span>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="akismet-active">Akismet</label>
				<br />
				<span class="setting-description">
					<a href="http://akismet.com/" title="<?php _e('Learn more about Akismet...', 'gwolle-gb'); ?>" target="_blank"><?php _e("What's that?", 'gwolle-gb'); ?></a>
				</span>
			</th>
			<td>
				<?php
				$current_plugins = get_option('active_plugins');
				$wordpress_api_key = get_option('wordpress_api_key');

				// Check wether Akismet is installed and activated or not.
				if (!in_array('akismet/akismet.php', $current_plugins)) {
					// Akismet is not installed and activated. Show notice with suggestion to install it.
					_e("Akismet helps you to fight spam. It's free and easy to install. Download and install it today to stop spam in your guestbook.", 'gwolle-gb');
				} elseif (!$wordpress_api_key) {
					// No WordPress API key is defined and set in the database.
					echo sprintf( __("Sorry, wasn't able to locate your <strong>WordPress API key</strong>. You can enter it at the <a href=\"%s\">Akismet configuration page</a>.", 'gwolle-gb'), 'options-general.php?page=akismet-key-config' );
				} else {
					// Akismet is installed and activated and a WordPress API key exists (we just assume it is valid).
					echo '<input ';
					if ( get_option( 'gwolle_gb-akismet-active', 'false' ) === 'true' ) {
						echo 'checked="checked" ';
					}
					echo 'name="akismet-active" id="akismet-active" type="checkbox" />
						<label for="akismet-active">
						' . __('Use Akismet', 'gwolle-gb') . '
						</label><br />';
					_e("The WordPress API key has been found, so you can start using Akismet right now.", 'gwolle-gb');
				}
				?>
			</td>
		</tr>

		<?php
		$antispam_question = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-question') );
		$antispam_answer   = gwolle_gb_sanitize_output( get_option('gwolle_gb-antispam-answer') );
		?>
		<tr valign="top">
			<th scope="row"><label for="antispam-question"><?php _e('Custom Anti-Spam Security Question', 'gwolle-gb'); ?></label></th>
			<td>
				<div>
					<input name="antispam-question" type="text" id="antispam-question" value="<?php echo $antispam_question; ?>" class="regular-text" placeholder="<?php _e('12 + six =', 'gwolle-gb'); ?>" />
					<label for="antispam-question" class="setting-description"><?php _e('Custom security question to battle spam.', 'gwolle-gb'); ?></label>
					<br />
					<input name="antispam-answer" type="text" id="antispam-answer" value="<?php echo $antispam_answer; ?>" class="regular-text" placeholder="<?php _e('18', 'gwolle-gb'); ?>" />
					<label for="antispam-answer" class="setting-description"><?php _e('The answer to your security question.', 'gwolle-gb'); ?></label>
					<br />
					<span class="setting-description"><?php _e('You can ask your visitors to answer a custom security question, so only real people can post an entry.', 'gwolle-gb'); ?></span>
				</div>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('CAPTCHA', 'gwolle-gb'); ?></th>
			<td>
				<div>
					<span class="setting-description">
						<?php _e('A CAPTCHA is a way to have visitors fill in a field with a few letters or numbers. It is a way to make sure that you have a human visitor and not a spambot. Not every visitor will appreciate it though, some will consider it unfriendly.', 'gwolle-gb'); ?>
						<br /><br />
						<?php _e('For the CAPTCHA you need the plugin', 'gwolle-gb'); ?>
						<a href="https://wordpress.org/plugins/really-simple-captcha/" title="<?php _e('Really Simple CAPTCHA plugin at wordpress.org', 'gwolle-gb'); ?>" target="_blank"><?php _e('Really Simple CAPTCHA', 'gwolle-gb'); ?></a>
						<?php _e('installed and activated', 'gwolle-gb'); ?>.
						<?php
						if ( class_exists('ReallySimpleCaptcha') ) {
							echo '<br />';
							_e('This plugin is installed and activated, so the CAPTCHA is ready to be used.', 'gwolle-gb');
						} ?>
						<br /><br />
						<?php _e('If you use any caching plugin together with this CAPTCHA, page caching will be disabled for the page that the CAPTCHA is shown on. This is to prevent errors and to have a fresh CAPCHA image each time.', 'gwolle-gb'); ?>
					</span>
				</div>
			</td>
		</tr>

		<tr>
			<td colspan="2">
				<p class="submit">
					<input type="submit" name="gwolle_gb_settings_antispam" id="gwolle_gb_settings_antispam" class="button-primary" value="<?php esc_attr_e('Save settings', 'gwolle-gb'); ?>" />
				</p>
			</td>
		</tr>

		</tbody>
	</table>

	<?php
}


