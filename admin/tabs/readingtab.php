<?php
/*
 * Settings page for the guestbook
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_settingstab_reading() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	} ?>

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
			<th scope="row"><label for="excerpt_length"><?php _e('Length of the entry content', GWOLLE_GB_TEXTDOMAIN); ?></label></th>
			<td>
				<select name="excerpt_length" id="excerpt_length">
					<?php
					$excerpt_length = get_option( 'gwolle_gb-excerpt_length', 0 );
					$presets = array( 20, 40, 60, 80, 100, 120, 150, 200, 300 );
					echo '<option value="0"';
					if ( 0 == $excerpt_length ) {
						echo ' selected="selected"';
					}
					echo '>' . __('Unlimited Words', GWOLLE_GB_TEXTDOMAIN) . '</option>';

					foreach ( $presets as $preset ) {
						echo '<option value="' . $preset . '"';
						if ($preset == $excerpt_length) {
							echo ' selected="selected"';
						}
						echo '>' . $preset . ' ' . __('Words', GWOLLE_GB_TEXTDOMAIN) . '</option>';
					}
					?>
				</select>
				<br />
				<span class="setting-description"><?php _e('Maximum length of the entry content in words.', GWOLLE_GB_TEXTDOMAIN); ?></span>
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

	<?php
}


