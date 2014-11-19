<?php /*
 *
 *	import.php
 *	Lets the user import guestbook entries from other plugins.
 *  Currently supported:
 *  - DMSGuestbook (http://wordpress.org/plugins/dmsguestbook/)
 */

//	No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!');
}


function gwolle_gb_import() {

	if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?'));
	}

	// FIXME: use the right pagename
	if ( isset( $_POST['option_page']) &&  $_POST['option_page'] == 'gwolle_gb_options' ) { // different names

		if (isset($_POST['start_import'])) {
			?>
			<div class="wrap">
				<div id="icon-gwolle-gb"><br /></div>
				<h2>Import is currently disabled. It will come back in a future version.</h2>
			</div> <?php
			return;

			// Import guestbook entries from another plugin.
			// Supported options could be: DMSguestbook, Rizzi, WP-ViperGB, and Standard WordPress comments, from a selectable page.
			$supported = array('dmsguestbook');
			if (!in_array($_REQUEST['what'], $supported)) {
				// The requested plugin is not supported

			} else {
				global $wpdb;
				if ($_REQUEST['what'] == 'dmsguestbook') {
					// Import entries from DMSGuestbook
					if (isset($_POST['guestbook_number']) && is_numeric($_POST['guestbook_number'])) {
						// Get guestbook entries from the chosen guestbook
						// FIXME, cleanup first query
						$result_nr = $wpdb->query("
							SELECT
								*
							FROM
								" . $wpdb->prefix . "dmsguestbook
							WHERE
								guestbook = " . $_POST['guestbook_number'] . "
							ORDER BY
								date ASC
							");
						if ($result_nr === 0) {
							// The chosen guestbook does not contain any entries.
							header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-entries-to-import');
							exit ;
						} else {
							$result = $wpdb->get_results("
								SELECT
									*
								FROM
									" . $wpdb->prefix . "dmsguestbook
								WHERE
									guestbook = " . $_POST['guestbook_number'] . "
								ORDER BY
									date ASC
								", ARRAY_A);
							foreach ($result as $entry) {
								gwolle_gb_import_dmsgb_entry($entry);
							}
							header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=import-successful&count=' . $result_nr);
							exit ;
						}
					} elseif ($_POST['import-all'] == 'true') {
						//  Import all entries.
						// FIXME: cleanup first query
						$result_nr = $wpdb->query("
							SELECT
								*
							FROM
								" . $wpdb->prefix . "dmsguestbook
							ORDER BY
								date ASC
							");
						if ($result_nr === 0) {
							//  There are no entries to import.
							header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-entries-to-import');
							exit ;
						} else {
							$result = $wpdb->get_results("
								SELECT
									*
								FROM
									" . $wpdb->prefix . "dmsguestbook
								ORDER BY
									date ASC
								", ARRAY_A);
							foreach ($result as $entry) {
								gwolle_gb_import_dmsgb_entry($entry);
							}
							header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=import-successful&count=' . $result_nr);
							exit ;
						}
					} else {
						//  There are more than one guestbook and the user didn't choose one to import from.
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-guestbook-chosen');
						exit ;
					}
				}
			}
		}

	}

	?>
	<div class="wrap">
		<div id="icon-gwolle-gb"><br /></div>
		<h2>Import is currently disabled. It will come back in a future version.</h2>
	</div>
	<?php return; ?>

		<h2><?php
		if ($_REQUEST['what'] == 'dmsguestbook') {
			_e('Import guestbook entries from DMSGuestbook', GWOLLE_GB_TEXTDOMAIN);
		} else {
			_e('Import guestbook entries from other plugins', GWOLLE_GB_TEXTDOMAIN);
		}
		?></h2>

		<?php
		if ($_REQUEST['msg'] || $showMsg) {
			if ($_REQUEST['msg'] == 'no-guestbook-chosen') {
				$msgClass = 'error';
			}
			else {
				$msgClass = 'updated';
			}
			echo '<div id="message" class="' . $msgClass . ' fade"><p>';
			$msg['no-guestbook-chosen']   = __("You haven't chosen a guestbook. Please select one and try again.",GWOLLE_GB_TEXTDOMAIN);
			$msg['no-entries-to-import']  = __("<strong>Nothing to import.</strong> The guestbook you've chosen does not contain any entries.",GWOLLE_GB_TEXTDOMAIN);
			if ($_REQUEST['count'] == 1) {
				$msg['import-successful']   = __('One entry imported successfully.',GWOLLE_GB_TEXTDOMAIN);
			} else {
				$msg['import-successful']   = str_replace('%1',$_REQUEST['count'],__('%1 entries imported successfully.',GWOLLE_GB_TEXTDOMAIN));
			}
			echo $msg[$_REQUEST['msg']];
			echo $msg[$showMsg];
			echo '</p></div>';
		}


		if ($_REQUEST['what'] == 'dmsguestbook') {
			//  Does the table of DMSGuestbook exist?
			$sql = "
				SHOW
				TABLES
				LIKE '".$wpdb->prefix."dmsguestbook'";
			$foundTables = $wpdb->get_results($sql, ARRAY_A);
			if ($foundTables[0] === $wpdb->prefix.'dmsguestbook') {
				?>
				<form action="admin.php?<?php echo $_REQUEST['page']; ?>&amp;do=import&amp;what=dmsguestbook" method="POST">
					<?php
					//  Get the DMSGuestbook options from database
					$page_id_starttag = '<page_id>';
					$page_id_endtag = '</page_id>';
					$dmsguestbook_options = str_replace('\r\n','',get_option('DMSGuestbook_options'));
					//  Get the start position of the '<page_id>' tag
					$page_id_startposition = strpos($dmsguestbook_options, $page_id_starttag);
					//  Get the start position of the closing tag
					$page_id_endposition = strpos($dmsguestbook_options, $page_id_endtag);
					//  Try to get the page ids
					$page_id_string = substr($dmsguestbook_options, strlen($page_id_starttag), ($page_id_endposition-$page_id_startposition));

					$page_ids = explode(',',$page_id_string);

					if (count($page_ids) === 0 || $page_id_string == 0) {
						//  No guestbooks detected.
						echo '<div style="margin-bottom:20px;">'.__("Sorry, but I wasn't able to determine the pages at which your guestbook was displayed. You cannot choose the guestbook to import from.",GWOLLE_GB_TEXTDOMAIN).'</div>';
						echo '<input type="hidden" name="import-all" value="true">';
						//  Get entry count
						$count = $wpdb->query("
							SELECT
								id
							FROM
								" . $wpdb->prefix . "dmsguestbook
								");
						echo '<div style="margin-bottom:10px;font-weight:bold;">'.str_replace('%1', $count, __("%1 entries were found and will be imported.",GWOLLE_GB_TEXTDOMAIN)).'</div>';
					} else {
						echo '<div>'.str_replace('%1', count($page_ids), __('I was able to find %1 configured DMSGuestbooks. Please choose the guestbook you want to import entries from.',GWOLLE_GB_TEXTDOMAIN)).'</div>';
						?>
						<table style="margin-top:15px;margin-bottom:15px;" class="widefat">
							<thead>
								<tr>
									<th scope="col" >&nbsp;</th>
									<th scope="col" ><?php _e('Page title', GWOLLE_GB_TEXTDOMAIN); ?></th>
									<th scope="col" ><?php _e('Number of guestbook entries', GWOLLE_GB_TEXTDOMAIN); ?></th>
								</tr>
							</thead>

							<tbody>
								<?php
								for ($i = 0; $i < count($page_ids); $i++) {
									$guestbook_post = get_post($page_ids[$i]);

									//  Get entry count for this guestbook
									$data = $wpdb->get_results("
										SELECT
										COUNT(id) AS entry_count
										FROM
										" . $wpdb -> prefix . "dmsguestbook
										WHERE
										guestbook = " . $i . "
										GROUP BY
										guestbook
										", ARRAY_A);
									$entry_count = ($data !== FALSE) ? $data['entry_count'] : 0;
									// $entry_count = ($data !== FALSE) ? $data[0]['entry_count'] : 0; // FIXME: Test which one works after get_results change

									echo '<tr>';
									echo '<td><input type="radio" name="guestbook_number" value="' . $i . '"></td>';
									echo '<td>' . $guestbook_post->post_title . '</td>';
									echo '<td>' . $entry_count . ' (<a href="admin.php?page=Entries&guestbook=' . $i . '" title="' . __('Click here to view the entries of this guestbook...', GWOLLE_GB_TEXTDOMAIN) . '">' . __('Review entries', GWOLLE_GB_TEXTDOMAIN) . ' &raquo;</a>)</td>';
									echo '</tr>';
								}
								?>
							</tbody>
						</table>
						<?php
					} ?>

					<div>
						<?php _e('The importer will preserve the following data per entry:', GWOLLE_GB_TEXTDOMAIN); ?>
						<ul style="list-style-type:disc;padding-left:15px;">
							<li><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('E-Mail address', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('URL/Website', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('Date of the entry', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('IP address', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('"is spam" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
							<li><?php _e('"is checked" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
						</ul>
						<?php _e('However, data such as HTML formating and gravatars are not supported by Gwolle-GB and <strong>will not</strong> be imported.', GWOLLE_GB_TEXTDOMAIN); ?>
						<br />
						<?php _e('The importer does not delete any data, so you can go back whenever you want.<br>Please start the import by pressing "Start import".', GWOLLE_GB_TEXTDOMAIN); ?>
					</div>

					<p style="text-align:center;margin-top:10px;">
						<input name="start_import" type="submit" value="<?php _e('Start import', GWOLLE_GB_TEXTDOMAIN); ?>">
					</p>

				</form>

				<?php
			} else {
				//  Table of DMSGuestbook does not exist.
				_e("I'm sorry, but I wasn't able to find the table of DMSGuestbook. Please check your MySQL database and try again.",GWOLLE_GB_TEXTDOMAIN);
			}
		} else {
			//  User did not choose a plugin to import entries from.
			?>
			<div style="margin-top:10px;margin-bottom:10px;">
				<?php _e("You may want to import entries from another plugin. Click on the plugin's name to get more details on the import.", GWOLLE_GB_TEXTDOMAIN); ?>
			</div>

			<strong><?php _e('Supported plugins:', GWOLLE_GB_TEXTDOMAIN); ?></strong>
			<ul style="list-style-type:disc;padding-left:25px;margin-top:5px;">
				<?php //  Check if the 'dmsguestbook' table exists
				$foundTables = $wpdb->query("
					SHOW
					TABLES
					LIKE '" . $wpdb->prefix . "dmsguestbook'", ARRAY_A);
				if ($foundTables[0] === $wpdb->prefix . 'dmsguestbook') {
					echo '<li><a href="admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&amp;do=import&amp;what=dmsguestbook">DMSGuestbook</a></li>';
				} else {
					//  DMSGuestbook table could not be found, so we can't import from it.
					echo '<li>DMSGuestbook (' . str_replace('%1', $wpdb->prefix . 'dmsguestbook', __('Table %1 not found.', GWOLLE_GB_TEXTDOMAIN)) . ')</li>';
				}
				?>
			</ul>
			<?php
		} ?>

	</div>

	<?php
}

