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
	global $userLevelNames;

	// Calculate the number of entries
	$count = Array();
	$count['visible']    = gwolle_gb_get_entry_count(
		array(
			'checked' => 'checked',
			'deleted' => 'notdeleted',
			'spam' => 'nospam'
		)
	);
	$count['checked']    = gwolle_gb_get_entry_count(array( 'checked' => 'checked' ));
	$count['unchecked']  = gwolle_gb_get_entry_count(array( 'checked' => 'unchecked' ));
	$count['spam']       = gwolle_gb_get_entry_count(array( 'spam' => 'spam' ));
	$count['all']        = gwolle_gb_get_entry_count(array( 'all' => 'all' ));
	?>

	<div class="table table_content">
		<p class="sub"><?php _e('Overview',GWOLLE_GB_TEXTDOMAIN); ?></p>

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
								_e('entry total',GWOLLE_GB_TEXTDOMAIN);
							}
							else {
								_e('entries total',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>

				<tr>
					<td class="first b"><a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=visible">
						<?php echo $count['visible']; ?>
					</a></td>
					<td class="t" style="color:#008000;">
						<?php
							if ($count['visible'] == 1) {
								_e('Visible entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Visible entries',GWOLLE_GB_TEXTDOMAIN);;
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>


				<tr>
					<td class="first b"><a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=checked">
						<?php echo $count['checked']; ?>
					</a></td>
					<td class="t" style="color:#008000;">
						<?php
							if ($count['checked'] == 1) {
								_e('Checked entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Checked entries',GWOLLE_GB_TEXTDOMAIN);;
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>
				<tr>
					<td class="first b"><a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked">
						<?php echo $count['unchecked']; ?>
					</a></td>
					<td class="t" style="color:#FFA500;">
						<?php
							if ($count['unchecked'] == 1) {
								_e('Unchecked entry',GWOLLE_GB_TEXTDOMAIN);
							} else {
								_e('Unchecked entries',GWOLLE_GB_TEXTDOMAIN);
							}
						?>
					</td>
					<td class="b"></td>
					<td class="last"></td>
				</tr>
				<tr>
					<td class="first b"><a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=spam">
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
			</tbody>
		</table>
	</div><!-- Table-DIV -->
	<div class="versions">
		<p>
			<a class="button rbutton" href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php"><strong><?php _e('Write admin entry',GWOLLE_GB_TEXTDOMAIN); ?></strong></a>
			<?php _e('Here you can manage the entries.',GWOLLE_GB_TEXTDOMAIN); ?>
		</p>
		<span>
			<?php echo str_replace('%1','<strong>' . $userLevelNames[(int)get_option('gwolle_gb-access-level')] . '</strong>',__('Only users with the role %1 have access to the guestbook backend.',GWOLLE_GB_TEXTDOMAIN)); ?>
		</span>
	</div>
<?php }

/*
function gwolle_gb_overview_news(){
	require_once(ABSPATH . WPINC . '/rss.php');
	?>
	<div class="rss-widget">
	<?php
	$rss = @fetch_rss('http://www.wolfgangtimme.de/blog/category/gwolle-gb/feed/');
	if (isset($rss->items) && count($rss->items) !== 0) {
	$rss->items = array_slice($rss->items, 0, 3);
	echo '
	<ul>';
	foreach($rss->items as $item) {
	?>
	<li>
	<a class="rsswidget" title="" href='<?php echo wp_filter_kses($item['link']); ?>'>
	<?php echo wp_specialchars($item['title']); ?>
	</a>
	<span class="rss-date">
	<?php echo date("F jS, Y", strtotime($item['pubdate'])); ?>
	</span>
	<div class="rssSummary">
	<strong><?php echo human_time_diff(strtotime($item['pubdate'], time())); ?></strong> - <?php echo $item['description']; ?>
	</div>
	</li>
	<?php
	}
	echo '
	</ul>';
	}
	else {
	?>
	<p>
	<?php printf(__('Newsfeed could not be loaded.  Check the <a href="%s">front page</a> to check for updates.', GWOLLE_GB_TEXTDOMAIN), 'http://wolfgangtimme.de/blog/') ?>
	</p>
	<?php
	}
	?>
	</div>
	<?php
}
*/

function gwolle_gb_overview_help() {
	echo '
	'.__('This is how the guestbook will be displayed on your page', GWOLLE_GB_TEXTDOMAIN).':
	<br />
	<br />
	<ul>
		<li>'.__('Create a new post or page.', GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("Choose a heading (doesn't matter) and set &quot;[gwolle_gb]&quot; (without the quotes) as the content.", GWOLLE_GB_TEXTDOMAIN).'</li>
		<li>'.__("It is probably a good idea to disable comments on that post or page; otherwise, your visitors might get a little confused.",GWOLLE_GB_TEXTDOMAIN).'</li>
	</ul>';
}

function gwolle_gb_overview_thanks() {
	echo '
	<ul class="settings">
		<li><a href="http://akismet.com/tos/" target="_blank">Akismet</a></li>
		<li><a href="http://philipwilson.de/" target="_blank">'.__('Icons by',GWOLLE_GB_TEXTDOMAIN).' Philip Wilson</a></li>
		<li><a href="http://recaptcha.net/aboutus.html" target="_blank">Recaptcha</a></li>
	</ul>';
}

function gwolle_gb_overview_import() {
	global $wpdb;
	//  Check if the 'dmsguestbook' table exists
	$sql = "
		SHOW
		TABLES
		LIKE '".$wpdb->prefix."dmsguestbook'";
	$foundTables = $wpdb->get_results($sql, ARRAY_A);
	if (isset($foundTables[0])) {
		if ($foundTables[0] === $wpdb->prefix.'dmsguestbook') {
			echo '
			'.__('It looks like you have been using the plugin &quot;DMSGuestbook&quot;.<br>Do you want to import its entries into Gwolle-GB?',GWOLLE_GB_TEXTDOMAIN).'
			<br />
			<p>
			<a class="button rbutton" href="admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&amp;do=import&amp;what=dmsguestbook">
			<strong>'.__('Sure, take me to the import.',GWOLLE_GB_TEXTDOMAIN).'</strong>
			</a>
			</p>
			<span style="font-size:10px;">
			'.str_replace('%1','admin.php?page='.GWOLLE_GB_FOLDER.'/settings.php',__('You may disable this message at the <a href="%1">settings page</a> of Gwolle-GB.',GWOLLE_GB_TEXTDOMAIN)).'
			</span>';
			}
			else {
			echo '
			'.__('Nothing to import here.',GWOLLE_GB_TEXTDOMAIN).'
			<br />
			<br />
			'.__('If you had another guestbook plugin (e. g. DMSGuestbook) installed its entries could be imported into Gwolle-GB with just a few clicks.',GWOLLE_GB_TEXTDOMAIN).'
			<br />
			<br />
			'.str_replace('%1','admin.php?page='.GWOLLE_GB_FOLDER.'/settings.php',__('You may disable this message at the <a href="%1">settings page</a> of Gwolle-GB.',GWOLLE_GB_TEXTDOMAIN));
		}
	}
}

/* Show the page */
function gwolle_gb_welcome() {
	global $wpdb;
	global $userLevelNames;

	//  Process request variables
	$do = (isset($_REQUEST['do'])) ? $_REQUEST['do'] : '';

	if (get_option('gwolle_gb_version') === FALSE) {
		gwolle_gb_installSplash();
	} elseif ($do == 'import') {
		gwolle_gb_import();
	} else {
		add_meta_box('dashboard_right_now', __('Welcome to the Guestbook!',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview', 'gwolle_gb_welcome', 'left', 'core');
		add_meta_box('gwolle_gb_thanks', __('This plugin uses the following scripts/programs/images:',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_thanks', 'gwolle_gb_welcome', 'left', 'core');
		if (get_option('gwolle_gb-checkForImport') == 'true') {
			add_meta_box('gwolle_gb_import', __('Import', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_import', 'gwolle_gb_welcome', 'right', 'core');
		}
		add_meta_box('gwolle_gb_help', __('Help', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_help', 'gwolle_gb_welcome', 'right', 'core');
		//add_meta_box('dashboard_primary', __('Latest News', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_overview_news', 'gwolle_gb_welcome', 'right', 'core');

		?>
		<div class="wrap gwolle_gb-wrap">
			<div id="icon-gwolle-gb"><br /></div>
			<h2><?php _e('Guestbook', GWOLLE_GB_TEXTDOMAIN); ?></h2>
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



