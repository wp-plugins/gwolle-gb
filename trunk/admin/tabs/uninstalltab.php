<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}

/*
 * $uninstalled is a bool, if the install has been done already. In that case, show messages.
 */

function gwolle_gb_page_settingstab_uninstall( $uninstalled ) {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	} ?>

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

	<?php
}


