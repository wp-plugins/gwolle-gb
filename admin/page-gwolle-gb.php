<?php
 /**
 * welcome.php
 * Shows the overview screen with the widget-like windows.
 * Thanks to Alex Rabe for writing clean and understandable code!
 * Also thanks to http://andrewferguson.net/2008/09/26/using-add_meta_box/ !
 */

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }

function gwolle_gb_overview(){

	// Calculate the number of entries
	$count = Array();
	$count['checked']    = gwolle_gb_get_entry_count(array(
			'checked' => 'checked',
			'deleted' => 'notdeleted',
			'spam'    => 'nospam'
		));
	$count['unchecked'] = gwolle_gb_get_entry_count(array(
			'checked' => 'unchecked',
			'deleted' => 'notdeleted',
			'spam'    => 'nospam'
		));
	$count['spam']    = gwolle_gb_get_entry_count(array( 'spam' => 'spam' ));
	$count['trash']   = gwolle_gb_get_entry_count(array( 'deleted' => 'deleted' ));
	$count['all']     = gwolle_gb_get_entry_count(array( 'all' => 'all' ));
	?>

	<div class="table table_content gwolle_gb">
		<h3><?php _e('Overview',GWOLLE_GB_TEXTDOMAIN); ?></h3>

		<table>
			<tbody>
				<tr class="first">
					<td class="first b">
						<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php">
							<?php echo $count['all']; ?>
						</a>
					</td>

					<td class="t">
						<?php
							if ($count['all']==1) {
								_e('Entry total',GWOLLE_GB_TEXTDOMAIN);
							}
							else {
								_e('Entries total',GWOLLE_GB_TEXTDOMAIN);
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
								_e('Unlocked entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Unlocked entries',GWOLLE_GB_TEXTDOMAIN);;
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
								_e('New entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('New entries',GWOLLE_GB_TEXTDOMAIN);
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
								_e('Spam entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Spam entries',GWOLLE_GB_TEXTDOMAIN);
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
								_e('Trashed entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Trashed entries',GWOLLE_GB_TEXTDOMAIN);
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
			<a class="button rbutton button button-primary" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php"><?php _e('Write admin entry',GWOLLE_GB_TEXTDOMAIN); ?></a>
		</p>
	</div>
<?php }


function gwolle_gb_overview_help() {
	echo '<h3>
	'.__('This is how you can get your guestbook displayed on your website:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul>
		<li>'.__('Create a new page.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("Choose a title and set &quot;[gwolle_gb]&quot; (without the quotes) as the content.", GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("It is probably a good idea to disable comments on that page; otherwise, your visitors might get a little confused.",GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';
}


function gwolle_gb_overview_help_more() {
	echo '<h3>
	'.__('These entries will be visible for your visitors:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Checked.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Spam.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Trash.',GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';

	echo '<h3>
	'.__('The Main Menu counter counts the following entries:', GWOLLE_GB_TEXTDOMAIN).'</h3>
	<ul class="ul-disc">
		<li>'.__('Marked as Unchecked (You might want to moderate them).', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Spam (You might want to check them).', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__('Not marked as Trash (You decide what goes to the trash).',GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';
}


function gwolle_gb_overview_thanks() {
	echo '
	<ul class="settings">
		<li><a href="http://akismet.com/tos/" target="_blank">Akismet</a></li>
		<li><a href="http://philipwilson.de/" target="_blank">'.__('Icons by',GWOLLE_GB_TEXTDOMAIN).' Philip Wilson</a></li>
		<li><a href="http://www.google.com/recaptcha/intro/index.html" target="_blank">reCAPTCHA</a></li>
	</ul>';
}


/* Show the page */
function gwolle_gb_welcome() {
	global $wpdb;

	if (get_option('gwolle_gb_version', false) === false) {
		gwolle_gb_installSplash();
	} else {
		add_meta_box('dashboard_right_now', __('Welcome to the Guestbook!',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_thanks', __('This plugin uses the following scripts/programs/images:',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_thanks', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_help', __('Help', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_help', 'gwolle_gb_welcome', 'right', 'core');
		add_meta_box('gwolle_gb_help_more', __('Help', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_help_more', 'gwolle_gb_welcome', 'right', 'core');

		?>
		<div class="wrap gwolle_gb-wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Gwolle Guestbook', GWOLLE_GB_TEXTDOMAIN); ?></h2>
			<div id="dashboard-widgets-wrap" class="gwolle_gb_welcome">
				<div id="dashboard-widgets" class="metabox-holder">
					<div class="postbox-container" style="width:49%;">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<?php do_meta_boxes('gwolle_gb_welcome', 'left', null); ?>
						</div>
					</div>
					<div class="postbox-container" style="width:49%;">
						<div id="normal-sortables" class="meta-box-sortables ui-sortable">
							<div id="dashboard-widgets-main-content" class="has-sidebar-content">
								<?php do_meta_boxes('gwolle_gb_welcome', 'right', ''); ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}



