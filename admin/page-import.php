<?php /*
 *
 *	import.php
 *	Lets the user import guestbook entries from other plugins.
 *  Currently supported:
 *  - DMSGuestbook (http://wordpress.org/plugins/dmsguestbook/)
 */

// No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }


function gwolle_gb_page_import() {
	global $wpdb;

	$gwolle_gb_errors = '';
	$gwolle_gb_messages = '';

	//if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		die(__('Cheatin&#8217; uh?'));
	}


	if ( isset( $_POST['gwolle_gb_page']) &&  $_POST['gwolle_gb_page'] == 'gwolle_gb_import' ) {

		if (isset($_POST['start_import_dms'])) {

			if ( isset($_POST['dmsguestbook']) && $_POST['dmsguestbook'] == 'on' ) {
				// Import all entries from DMSGuestbook
				// Does the table of DMSGuestbook exist?
				$sql = "
					SHOW
					TABLES
					LIKE '" . $wpdb->prefix . "dmsguestbook'";
				$foundTables = $wpdb->get_results( $sql, ARRAY_A );

				if ( isset($foundTables[0]) && in_array( $wpdb->prefix . 'dmsguestbook', $foundTables[0] ) ) {
					$result = $wpdb->get_results("
						SELECT
							`name`,
							`email`,
							`url`,
							`date`,
							`ip`,
							`message`,
							`spam`,
							`additional`,
							`flag`
						FROM
							" . $wpdb->prefix . "dmsguestbook
						ORDER BY
							date ASC
						", ARRAY_A);

					if ( is_array($result) && count($result) > 0 ) {

						$saved = 0;
						foreach ($result as $entry_data) {

							/* New Instance of gwolle_gb_entry. */
							$entry = new gwolle_gb_entry();

							/* Set the data in the instance */
							$entry->set_isspam( $entry_data["spam"] );
							$entry->set_ischecked( true );
							$entry->set_istrash( $entry_data["flag"] );
							$entry->set_content( $entry_data["message"] );
							$entry->set_date( $entry_data["date"] );
							$entry->set_author_name( $entry_data["name"] );
							$entry->set_author_email( $entry_data["email"] );
							$entry->set_author_ip( $entry_data["ip"] );
							$entry->set_author_website( $entry_data["url"] );

							/* Save the instance */
							$save = $entry->save();
							if ( $save ) {
								// We have been saved to the Database
								gwolle_gb_add_log_entry( $entry->get_id(), 'imported-from-dmsguestbook' );
								$saved++;
							}
						}
						if ( $saved == 0 ) {
							$gwolle_gb_errors = 'error';
							$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to import entries from DMSGuestbook successfully.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
						} else if ( $saved == 1 ) {
							$gwolle_gb_messages .= '<p>' . __("1 entry imported successfully from DMSGuestbook.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
						} else if ( $saved > 1 ) {
							$gwolle_gb_messages .= '<p>' . str_replace('%1', $saved, __('%1 entries imported successfully from DMSGuestbook.', GWOLLE_GB_TEXTDOMAIN)) . '</p>';
						}
					} else {
						$gwolle_gb_errors = 'error';
						$gwolle_gb_messages .= '<p>' . __("<strong>Nothing to import.</strong> The guestbook you've chosen does not contain any entries.", GWOLLE_GB_TEXTDOMAIN) . '</p>';

					}
				} else {
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to find the MySQL table of DMSGuestbook.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
				}
			} else {
				// The requested plugin is not supported
				$gwolle_gb_errors = 'error';
				$gwolle_gb_messages .= '<p>' . __("You haven't chosen a guestbook. Please select one and try again.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
			}
		} else if (isset($_POST['start_import_wp'])) {

			if ( isset($_POST['gwolle_gb_wp']) && $_POST['gwolle_gb_wp'] == 'on' ) {

				if ( isset($_POST['gwolle_gb_pageid']) ) {
					$page_id = intval($_POST['gwolle_gb_pageid']);

					if ( $page_id > 0 ) {
						$args = array(
							'status' => 'all',
							'post_id' => $page_id
						);
						$comments = get_comments( $args );

						if ( is_array($comments) && count($comments) > 0 ) {

							$saved = 0;
							foreach ( $comments as $comment ) {

								/* New Instance of gwolle_gb_entry. */
								$entry = new gwolle_gb_entry();

								/* Set the data in the instance */

								$entry->set_ischecked( $comment->comment_approved );
								$entry->set_content( $comment->comment_content );
								$entry->set_date( strtotime( $comment->comment_date ) );
								$entry->set_author_name( $comment->comment_author );
								$entry->set_author_email( $comment->comment_author_email );
								$entry->set_author_ip( $comment->comment_author_IP );
								$entry->set_author_website( $comment->comment_author_url );
								$entry->set_author_id( $comment->user_id );

								/* Save the instance */
								$save = $entry->save();
								if ( $save ) {
									// We have been saved to the Database
									gwolle_gb_add_log_entry( $entry->get_id(), 'imported-from-wp' );
									$saved++;
								}
							}
							if ( $saved == 0 ) {
								$gwolle_gb_errors = 'error';
								$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to import comments from that page successfully.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
							} else if ( $saved == 1 ) {
								$gwolle_gb_messages .= '<p>' . __("1 entry imported successfully from WordPress comments.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
							} else if ( $saved > 1 ) {
								$gwolle_gb_messages .= '<p>' . str_replace('%1', $saved, __('%1 entries imported successfully from WordPress comments.', GWOLLE_GB_TEXTDOMAIN)) . '</p>';
							}
						}
					} else {
						$gwolle_gb_errors = 'error';
						$gwolle_gb_messages .= '<p>' . __("<strong>Nothing to import.</strong> The page you've chosen does not have any comments.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
					}
				} else {
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("You haven't chosen a page. Please select one and try again.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
				}
			} else {
				// The requested plugin is not supported
				$gwolle_gb_errors = 'error';
				$gwolle_gb_messages .= '<p>' . __("You haven't chosen a guestbook. Please select one and try again.", GWOLLE_GB_TEXTDOMAIN) . '</p>';
			}

		}
	}


	/*
	 * Build the Page and the Form
	 */
	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h2><?php _e('Import guestbook entries.', GWOLLE_GB_TEXTDOMAIN); ?></h2>

		<?php
		if ( $gwolle_gb_messages ) {
			echo '
				<div id="message" class="updated fade ' . $gwolle_gb_errors . ' ">' .
					$gwolle_gb_messages .
				'</div>';
		}?>


		<form name="gwolle_gb_import" id="gwolle_gb_import" method="POST" action="#" accept-charset="UTF-8">
			<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_import" />

			<div id="poststuff" class="metabox-holder">

					<div id="post-body">
						<div id="post-body-content">
							<div id='normal-sortables' class='meta-box-sortables'>

								<div id="dmsdiv" class="postbox">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Import guestbook entries from DMSGuestbook', GWOLLE_GB_TEXTDOMAIN); ?></h3>
									<div class="inside"><?php
										// Does the table of DMSGuestbook exist?
										$sql = "
											SHOW
											TABLES
											LIKE '" . $wpdb->prefix . "dmsguestbook'";
										$foundTables = $wpdb->get_results( $sql, ARRAY_A );

										$count = 0;
										if ( isset($foundTables[0]) && in_array( $wpdb->prefix . 'dmsguestbook', $foundTables[0] ) ) {
											// Get entry count
											$sql = "
												SELECT
													COUNT(id) AS count
												FROM
													" . $wpdb->prefix . "dmsguestbook";

											$data = $wpdb->get_results( $sql, ARRAY_A );

											$count = (int) $data[0]['count'];
										}

										if ( isset($foundTables[0]) && in_array( $wpdb->prefix . 'dmsguestbook', $foundTables[0] ) ) { ?>
											<div>
												<?php echo str_replace( '%1', $count, __("%1 entries were found and will be imported.", GWOLLE_GB_TEXTDOMAIN) ); ?>
											</div>
											<div>
												<?php _e('The importer will preserve the following data per entry:', GWOLLE_GB_TEXTDOMAIN); ?>
												<ul class="ul-disc">
													<li><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('E-Mail address', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('URL/Website', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Date of the entry', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('IP address', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('"is spam" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
													<li><?php _e('"is checked" flag', GWOLLE_GB_TEXTDOMAIN); ?></li>
												</ul>
												<?php _e('However, data such as HTML formatting is not supported by Gwolle-GB and <strong>will not</strong> be imported.', GWOLLE_GB_TEXTDOMAIN); ?>
												<br />
												<?php _e('The importer does not delete any data, so you can go back whenever you want.', GWOLLE_GB_TEXTDOMAIN); ?>
											</div>

											<p>
												<label for="dmsguestbook" class="selectit">
													<input id="dmsguestbook" name="dmsguestbook" type="checkbox" />
													<?php _e('Import all entries from DMSGuestbook.', GWOLLE_GB_TEXTDOMAIN); ?>
												</label>
											</p>
											<p>
												<input name="start_import_dms" type="submit" class="button button-primary" value="<?php _e('Start import', GWOLLE_GB_TEXTDOMAIN); ?>">
											</p><?php
										} else {
											echo '<div>' . __('DMSGuestbook was not found.', GWOLLE_GB_TEXTDOMAIN) . '</div>';
										} ?>
									</div>
								</div>

								<div id="wp_comm_div" class="postbox">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Import guestbook entries from WordPress comments', GWOLLE_GB_TEXTDOMAIN); ?></h3>
									<div class="inside">
										<div>
											<?php _e('The importer will preserve the following data per entry:', GWOLLE_GB_TEXTDOMAIN); ?>
											<ul class="ul-disc">
												<li><?php _e('Name', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('User ID', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('E-Mail address', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('URL/Website', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('Date of the entry', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('IP address', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('Message', GWOLLE_GB_TEXTDOMAIN); ?></li>
												<li><?php _e('"approved" status', GWOLLE_GB_TEXTDOMAIN); ?></li>
											</ul>
											<?php _e('However, data such as HTML formatting is not supported by Gwolle-GB and <strong>will not</strong> be imported.', GWOLLE_GB_TEXTDOMAIN); ?>
											<br />
											<?php _e('The importer does not delete any data, so you can go back whenever you want.', GWOLLE_GB_TEXTDOMAIN); ?>
										</div>

										<p><label for="gwolle_gb_pageid"><?php _e('Select a page to import the comments from:', GWOLLE_GB_TEXTDOMAIN); ?></label><br />
											<select id="gwolle_gb_pageid" name="gwolle_gb_pageid">
											<option value="0"><?php _e('Select', GWOLLE_GB_TEXTDOMAIN); ?></option>
											<?php
											$args = array(
												'post_type'      => 'page',
												'nopaging'       => true,
												'posts_per_page' => -1,
												'order'          => 'ASC',
												'orderby'        => 'title'
											);

											$sel_query = new WP_Query( $args );
											if ( $sel_query->have_posts() ) {
												while ( $sel_query->have_posts() ) : $sel_query->the_post();
													$num_comments = get_comments_number( get_the_ID() ); // get_comments_number returns only a numeric value

													if ( $num_comments == 0 ) {
														$comments = __('No Comments');
													} elseif ( $num_comments > 1 ) {
														$comments = $num_comments . __(' Comments');
													} else {
														$comments = __('1 Comment');
													}

													echo '<option value="' . get_the_ID() . '">'. get_the_title() . ' (' . $comments . ')</option>';
												endwhile;
											}
											wp_reset_postdata(); ?>
											</select>
										</p>

										<p>
											<label for="gwolle_gb_wp" class="selectit">
												<input id="gwolle_gb_wp" name="gwolle_gb_wp" type="checkbox" />
												<?php _e('Import all entries from this page.', GWOLLE_GB_TEXTDOMAIN); ?>
											</label>
										</p>
										<p>
											<input name="start_import_wp" type="submit" class="button button-primary" value="<?php _e('Start import', GWOLLE_GB_TEXTDOMAIN); ?>">
										</p>

									</div>
								</div>

								<div id="gwollediv" class="postbox">
									<div class="handlediv"></div>
									<h3 class='hndle' title="<?php _e('Click to open or close', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Import guestbook entries from Gwolle-GB', GWOLLE_GB_TEXTDOMAIN); ?></h3>
									<div class="inside">

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

