<?php /*
 *
 *	export.php
 *	Lets the user export guestbook entries to a CSV file.
 *
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_export() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', GWOLLE_GB_TEXTDOMAIN));
	}


	/*
	 * Build the Page and the Form
	 */
	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php _e('Export guestbook entries.', GWOLLE_GB_TEXTDOMAIN); ?></h1>

		<form name="gwolle_gb_export" id="gwolle_gb_export" method="POST" action="#" accept-charset="UTF-8">
			<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_export" />

			<div id="poststuff" class="metabox-holder">

					<div id="post-body">
						<div id="post-body-content">
							<div id='normal-sortables' class='meta-box-sortables'>

								<div id="gwolle_gb_export_postbox" class="postbox">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Export guestbook entries from Gwolle-GB', GWOLLE_GB_TEXTDOMAIN); ?></h3>
									<div class="inside">
										<div>
											<?php
											$count = gwolle_gb_get_entry_count( array( 'all' => 'all' ) );
											if ( $count == 0 ) { ?>
												<p>
													<?php _e("No entries were found.", GWOLLE_GB_TEXTDOMAIN); ?>
												</p><?php
											} else {
												?>
												<p>
													<?php echo sprintf( __("%d entries were found and will be exported.", GWOLLE_GB_TEXTDOMAIN), $count ); ?>
												</p>
												<p>
													<?php _e('The exporter will preserve the following data per entry:', GWOLLE_GB_TEXTDOMAIN); ?>
												</p>
												<ul class="ul-disc">
													<li><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('E-Mail address', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('URL/Website', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Origin', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Date of the entry', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('IP address', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Host address', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('"is checked" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('"is spam" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('"is trash" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Admin Reply', GWOLLE_GB_TEXTDOMAIN); ?></li>
												</ul>
												<?php _e('The exporter does not delete any data, so your data will still be here.', GWOLLE_GB_TEXTDOMAIN); ?>

												<p>
													<label for="start_export_enable" class="selectit">
														<input id="start_export_enable" name="start_export_enable" type="checkbox" />
														<?php _e('Export all entries from this website.', GWOLLE_GB_TEXTDOMAIN); ?>
													</label>
												</p>
												<p>
													<input name="gwolle_gb_start_export" id="gwolle_gb_start_export" type="submit" class="button" disabled value="<?php esc_attr_e('Start export', GWOLLE_GB_TEXTDOMAIN); ?>">
												</p>
												<?php
											} ?>
										</div>
									</div>
								</div>

							</div><!-- 'normal-sortables' -->
						</div><!-- 'post-body-content' -->

					</div>
				</div>
			</div>
		</form>
	</div>

	<?php
}


add_action('admin_init', 'gwolle_gb_export_action');
function gwolle_gb_export_action() {
	if ( is_admin() ) {
		if ( isset( $_POST['gwolle_gb_page']) &&  $_POST['gwolle_gb_page'] == 'gwolle_gb_export' ) {
			gwolle_gb_export_callback();
		}
	}
}


/*
 * Callback function for request generated from the Export page
 */

function gwolle_gb_export_callback() {

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		echo "error, no permission.";
		die();
	}

	$entries = gwolle_gb_get_entries(array(
				'num_entries' => -1,
				'all' => 'all'
			));

	if ( is_array($entries) && !empty($entries) ) {

		// Clean everything before here
		ob_end_clean();

		// output headers so that the file is downloaded rather than displayed
		header('Content-Type: text/csv; charset=utf-8');
		header('Content-Disposition: attachment; filename=gwolle_gb_export_' . GWOLLE_GB_VER . '_' . date('Y-m-d_H-i') . '.csv');

		// create a file pointer connected to the output stream
		$output = fopen('php://output', 'w');

		// output the column headings
		fputcsv($output, array(
				'id',
				'author_name',
				'author_email',
				'author_origin',
				'author_website',
				'author_ip',
				'author_host',
				'content',
				'datetime',
				'isspam',
				'ischecked',
				'istrash',
				'admin_reply'
			));

		$saved = 0;
		foreach ( $entries as $entry ) {

			$row = Array();

			$row[] = $entry->get_id();
			$row[] = addslashes($entry->get_author_name());
			$row[] = addslashes($entry->get_author_email());
			$row[] = addslashes($entry->get_author_origin());
			$row[] = addslashes($entry->get_author_website());
			$row[] = $entry->get_author_ip();
			$row[] = $entry->get_author_host();
			$row[] = addslashes($entry->get_content());
			$row[] = $entry->get_datetime();
			$row[] = $entry->get_isspam();
			$row[] = $entry->get_ischecked();
			$row[] = $entry->get_istrash();
			$row[] = $entry->get_admin_reply();

			fputcsv($output, $row);

			gwolle_gb_add_log_entry( $entry->get_id(), 'exported-to-csv' );
			$saved++;

		}

		fclose($output);
		die();
	}

	echo "(Gwolle-GB) Error, no entries.";
	die();
}


