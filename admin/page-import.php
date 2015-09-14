<?php /*
 *
 * import.php
 * Lets the user import guestbook entries from other plugins.
 * Currently supported:
 * - DMSGuestbook (http://wordpress.org/plugins/dmsguestbook/).
 * - WordPress coments from a page, post or just all.
 * - Gwolle-GB through a CSV-file.
 */

// No direct calls to this script
if ( strpos($_SERVER['PHP_SELF'], basename(__FILE__) )) {
	die('No direct calls allowed!');
}


function gwolle_gb_page_import() {
	global $wpdb;

	$gwolle_gb_errors = '';
	$gwolle_gb_messages = '';

	//if ( WP_DEBUG ) { echo "_POST: "; var_dump($_POST); }

	if ( function_exists('current_user_can') && !current_user_can('manage_options') ) {
		die(__('Cheatin&#8217; uh?', 'gwolle-gb'));
	}


	if ( isset( $_POST['gwolle_gb_page']) &&  $_POST['gwolle_gb_page'] == 'gwolle_gb_import' ) {

		if (isset($_POST['start_import_dms'])) {

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

				if ( is_array($result) && !empty($result) ) {

					$saved = 0;
					foreach ($result as $entry_data) {

						/* New Instance of gwolle_gb_entry. */
						$entry = new gwolle_gb_entry();

						/* Set the data in the instance */
						$entry->set_isspam( $entry_data["spam"] );
						$entry->set_ischecked( true );
						$entry->set_istrash( $entry_data["flag"] );
						$entry->set_content( $entry_data["message"] );
						$entry->set_datetime( $entry_data["date"] );
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
						$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to import entries from DMSGuestbook successfully.", 'gwolle-gb') . '</p>';
					} else if ( $saved == 1 ) {
						$gwolle_gb_messages .= '<p>' . __("1 entry imported successfully from DMSGuestbook.", 'gwolle-gb') . '</p>';
					} else if ( $saved > 1 ) {
						$gwolle_gb_messages .= '<p>' . sprintf( __('%d entries imported successfully from DMSGuestbook.', 'gwolle-gb'), $saved ) . '</p>';
					}
				} else {
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("<strong>Nothing to import.</strong> The guestbook you've chosen does not contain any entries.", 'gwolle-gb') . '</p>';
				}
			} else {
				$gwolle_gb_errors = 'error';
				$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to find the MySQL table of DMSGuestbook.", 'gwolle-gb') . '</p>';
			}

		} else if (isset($_POST['start_import_wp'])) {

			$args = array();

			if ( isset($_POST['gwolle_gb_importfrom']) && $_POST['gwolle_gb_importfrom'] == 'page' && isset($_POST['gwolle_gb_pageid']) && intval($_POST['gwolle_gb_pageid']) > 0 ) {
				$page_id = intval($_POST['gwolle_gb_pageid']);
				$args = array(
					'status' => 'all',
					'post_id' => $page_id
				);
			} else if ( isset($_POST['gwolle_gb_importfrom']) && $_POST['gwolle_gb_importfrom'] == 'post' && isset($_POST['gwolle_gb_postid']) && intval($_POST['gwolle_gb_postid']) > 0 ) {
				$post_id = intval($_POST['gwolle_gb_postid']);
				$args = array(
					'status' => 'all',
					'post_id' => $post_id
				);
			} else if ( isset($_POST['gwolle_gb_importfrom']) && $_POST['gwolle_gb_importfrom'] == 'all' ) {
				$args = array(
					'status' => 'all',
				);
			} else {
				$gwolle_gb_errors = 'error';
				$gwolle_gb_messages .= '<p>' . __("You haven't chosen how to import from WordPress comments. Please choose and try again.", 'gwolle-gb') . '</p>';
			}

			if ( is_array($args) && !empty($args) ) {
				$comments = get_comments( $args );

				if ( is_array($comments) && !empty($comments) ) {

					$saved = 0;
					foreach ( $comments as $comment ) {

						/* New Instance of gwolle_gb_entry. */
						$entry = new gwolle_gb_entry();

						/* Set the data in the instance */

						$entry->set_ischecked( $comment->comment_approved );
						$entry->set_content( $comment->comment_content );
						$entry->set_datetime( strtotime( $comment->comment_date ) );
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
						$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to import comments from that page successfully.", 'gwolle-gb') . '</p>';
					} else if ( $saved == 1 ) {
						$gwolle_gb_messages .= '<p>' . __("1 entry imported successfully from WordPress comments.", 'gwolle-gb') . '</p>';
					} else if ( $saved > 1 ) {
						$gwolle_gb_messages .= '<p>' . sprintf( __('%d entries imported successfully from WordPress comments.', 'gwolle-gb'), $saved ) . '</p>';
					}
				} else {
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("<strong>Nothing to import.</strong> There seem to be no comments on this page, post or at all.", 'gwolle-gb') . '</p>';
				}
			} else {
				if ( $gwolle_gb_errors != 'error' ) {
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("You haven't chosen how to import from WordPress comments. Please choose and try again.", 'gwolle-gb') . '</p>';
				}
			}

		} else if (isset($_POST['start_import_gwolle'])) {

			// if they DID upload a file...
			if($_FILES['start_import_gwolle_file']['name']) {
				if( !$_FILES['start_import_gwolle_file']['error'] ) { // if no errors...
					//now is the time to modify the future file name and validate the file
					// $new_file_name = strtolower( $_FILES['gwolle_gb_gwolle']['tmp_name'] ); //rename file
					if( $_FILES['start_import_gwolle_file']['size'] > (1024000) ) { //can't be larger than 1 MB
						$valid_file = false;
						$gwolle_gb_errors = 'error';
						$gwolle_gb_messages .= '<p>' . __("Your filesize is too large.", 'gwolle-gb') . '</p>';
					} else {
						if ( function_exists('finfo_open') ) {
							// Check MIME Type. Only PHP >= 5.3.0
							$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
							$mimetype = trim( finfo_file( $finfo, $_FILES['start_import_gwolle_file']['tmp_name'] ) );
							finfo_close($finfo);
						} else {
							// PHP 5.2 is insecure anyway?
							$mimetype = 'text/csv';
						}
						if ( !in_array( $mimetype,
								array(
									'csv' => 'text/csv',
									'txt' => 'text/plain',
									'xls' => 'application/excel',
									'ms'  => 'application/ms-excel',
									'vnd' => 'application/vnd.ms-excel',
								)
							) ) {
							$gwolle_gb_errors = 'error';
							$gwolle_gb_messages .= '<p>' . __("Invalid file format.", 'gwolle-gb') . ' (' . print_r($mimetype, true) . ')</p>';
						} else {
							$handle = fopen($_FILES['start_import_gwolle_file']['tmp_name'], "r");
							$row = 0;

							while (($data = fgetcsv($handle, 1000)) !== FALSE) {
								$num = count($data);
								if ($row == 0) {
									// Check the headerrow. $tesrow_old is version 1.4.1 and older.
									$testrow_1_0 = array(
										'id',
										'author_name',
										'author_email',
										'author_origin',
										'author_website',
										'author_ip',
										'author_host',
										'content',
										'date',
										'isspam',
										'ischecked',
										'istrash'
									);
									$testrow_1_4_1 = array(
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
										'istrash'
									);
									$testrow_1_4_8 = array(
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
									);
									if ( $data != $testrow_1_0 && $data != $testrow_1_4_1 && $data != $testrow_1_4_8 ) {
										$gwolle_gb_errors = 'error';
										$gwolle_gb_messages .= '<p>' . __("It seems your CSV file is from an export that is not compatible with this version of Gwolle-GB.", 'gwolle-gb') . '</p>';
										break;
									}
									$row++;
									continue;
								}

								if ( $num != 12 && $num != 13 ) {
									$gwolle_gb_errors = 'error';
									$gwolle_gb_messages .= '<p>' . __("Your data seems to be corrupt. Import failed.", 'gwolle-gb') . '</p>';
									break;
								}

								/* New Instance of gwolle_gb_entry. */
								$entry = new gwolle_gb_entry();

								/* Check if the date is a timestamp, else convert */
								if ( !is_numeric($data[8]) ) {
									$data[8] = strtotime($data[8]);
								}

								/* Set the data in the instance */
								// $entry->set_id( $data[0] ); // id of entry
								$entry->set_author_name( $data[1] );
								$entry->set_author_email( $data[2] );
								$entry->set_author_origin( $data[3] );
								$entry->set_author_website( $data[4] );
								$entry->set_author_ip( $data[5] );
								$entry->set_author_host( $data[6] );
								$entry->set_content( $data[7] );
								$entry->set_datetime( $data[8] );
								$entry->set_isspam( $data[9] );
								$entry->set_ischecked( $data[10] );
								$entry->set_istrash( $data[11] );
								if ( isset( $data[12] ) ) {
									$entry->set_admin_reply( $data[12] ); // admin_reply is only since 1.4.8
								}

								/* Save the instance */
								$save = $entry->save();
								if ( $save ) {
									// We have been saved to the Database
									gwolle_gb_add_log_entry( $entry->get_id(), 'imported-from-gwolle' );
									$row++;
								} else {
									$gwolle_gb_errors = 'error';
									$gwolle_gb_messages .= '<p>' . __("Your data seems to be corrupt. Import failed.", 'gwolle-gb') . '</p>';
									break;
								}

							}
							$row--; // minus the header

							if ( $row == 0 ) {
								$gwolle_gb_errors = 'error';
								$gwolle_gb_messages .= '<p>' . __("I'm sorry, but I wasn't able to import entries from the CSV file.", 'gwolle-gb') . '</p>';
							} else if ( $row == 1 ) {
								$gwolle_gb_messages .= '<p>' . __("1 entry imported successfully from the CSV file.", 'gwolle-gb') . '</p>';
							} else if ( $row > 1 ) {
								$gwolle_gb_messages .= '<p>' . sprintf( __('%d entries imported successfully from the CSV file.', 'gwolle-gb'), $row ) . '</p>';
							}

							fclose($handle);
						}
					}
				} else {
					// set that to be the returned message
					$gwolle_gb_errors = 'error';
					$gwolle_gb_messages .= '<p>' . __("Your upload triggered the following error:", 'gwolle-gb') . ' ' . $_FILES['gwolle_gb_gwolle']['error'] . '</p>';
				}
			}
		}
	}


	/*
	 * Build the Page and the Form
	 */
	?>
	<div class="wrap gwolle_gb">
		<div id="icon-gwolle-gb"><br /></div>
		<h1><?php _e('Import guestbook entries.', 'gwolle-gb'); ?></h1>

		<?php
		if ( $gwolle_gb_messages ) {
			echo '
				<div id="message" class="updated fade notice is-dismissible ' . $gwolle_gb_errors . ' ">' .
					$gwolle_gb_messages .
				'</div>';
		}?>


		<div id="poststuff" class="metabox-holder">

			<div id="post-body">
				<div id="post-body-content">
					<div id='normal-sortables' class='meta-box-sortables'>

						<div id="dmsdiv" class="postbox">
							<div class="handlediv"></div>
							<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', 'gwolle-gb'); ?>"><?php _e('Import guestbook entries from DMSGuestbook', 'gwolle-gb'); ?></h3>
							<div class="inside">
								<form name="gwolle_gb_import_dms" id="gwolle_gb_import_dms" method="POST" action="#" accept-charset="UTF-8">
									<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_import" />

									<?php
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
											<?php echo sprintf( __("%d entries were found and will be imported.", 'gwolle-gb'), $count ); ?>
										</div>
										<div>
											<?php _e('The importer will preserve the following data per entry:', 'gwolle-gb'); ?>
											<ul class="ul-disc">
												<li><?php _e('Name', 'gwolle-gb'); ?></li>
												<li><?php _e('E-Mail address', 'gwolle-gb'); ?></li>
												<li><?php _e('URL/Website', 'gwolle-gb'); ?></li>
												<li><?php _e('Date of the entry', 'gwolle-gb'); ?></li>
												<li><?php _e('IP address', 'gwolle-gb'); ?></li>
												<li><?php _e('Message', 'gwolle-gb'); ?></li>
												<li><?php _e('"is spam" flag', 'gwolle-gb'); ?></li>
												<li><?php _e('"is checked" flag', 'gwolle-gb'); ?></li>
											</ul>
											<?php _e('However, data such as HTML formatting is not supported by Gwolle-GB and <strong>will not</strong> be imported.', 'gwolle-gb'); ?>
											<br />
											<?php _e('The importer does not delete any data, so you can go back whenever you want.', 'gwolle-gb'); ?>
										</div>

										<p>
											<label for="gwolle_gb_dmsguestbook" class="selectit">
												<input id="gwolle_gb_dmsguestbook" name="gwolle_gb_dmsguestbook" type="checkbox" />
												<?php _e('Import all entries from DMSGuestbook.', 'gwolle-gb'); ?>
											</label>
										</p>
										<p>
											<input name="start_import_dms" id="start_import_dms" type="submit" class="button" disabled value="<?php esc_attr_e('Start import', 'gwolle-gb'); ?>">
										</p><?php
									} else {
										echo '<div>' . __('DMSGuestbook was not found.', 'gwolle-gb') . '</div>';
									} ?>
								</form>
							</div> <!-- inside -->
						</div> <!-- dmsdiv -->


						<div id="wp_comm_div" class="postbox">
							<div class="handlediv"></div>
							<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', 'gwolle-gb'); ?>"><?php _e('Import guestbook entries from WordPress comments', 'gwolle-gb'); ?></h3>
							<div class="inside">
								<form name="gwolle_gb_import_wp" id="gwolle_gb_import_wp" method="POST" action="#" accept-charset="UTF-8">
									<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_import" />

									<div>
										<?php _e('The importer will preserve the following data per entry:', 'gwolle-gb'); ?>
										<ul class="ul-disc">
											<li><?php _e('Name', 'gwolle-gb'); ?></li>
											<li><?php _e('User ID', 'gwolle-gb'); ?></li>
											<li><?php _e('E-Mail address', 'gwolle-gb'); ?></li>
											<li><?php _e('URL/Website', 'gwolle-gb'); ?></li>
											<li><?php _e('Date of the entry', 'gwolle-gb'); ?></li>
											<li><?php _e('IP address', 'gwolle-gb'); ?></li>
											<li><?php _e('Message', 'gwolle-gb'); ?></li>
											<li><?php _e('"approved" status', 'gwolle-gb'); ?></li>
										</ul>
										<?php _e('However, data such as HTML formatting is not supported by Gwolle-GB and <strong>will not</strong> be imported.', 'gwolle-gb'); ?>
										<br />
										<?php _e('Spam comments will not be imported.', 'gwolle-gb'); ?>
										<br />
										<?php _e('The importer does not delete any data, so you can go back whenever you want.', 'gwolle-gb'); ?>
									</div>

									<p><label for="gwolle_gb_pageid"><?php _e('Select a page to import the comments from:', 'gwolle-gb'); ?></label><br />
										<select id="gwolle_gb_pageid" name="gwolle_gb_pageid">
										<option value="0"><?php _e('Select', 'gwolle-gb'); ?></option>
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
												$args = array(
													'status'  => 'all',
													'post_id' => get_the_ID(),
													'count'   => true
												);
												$num_comments = get_comments($args);
												// get_comments_number returns only approved comments, and wp_count_comments seems to list spam too?

												if ( $num_comments == 0 ) {
													continue;
												} elseif ( $num_comments > 1 ) {
													$comments = $num_comments . __(' Comments', 'gwolle-gb');
												} else {
													$comments = __('1 Comment', 'gwolle-gb');
												}

												echo '<option value="' . get_the_ID() . '">'. get_the_title() . ' (' . $comments . ')</option>';
											endwhile;
										}
										wp_reset_postdata(); ?>
										</select>
									</p>

									<p><label for="gwolle_gb_postid"><?php _e('Select a post to import the comments from:', 'gwolle-gb'); ?></label><br />
										<select id="gwolle_gb_postid" name="gwolle_gb_postid">
										<option value="0"><?php _e('Select', 'gwolle-gb'); ?></option>
										<?php
										$args = array(
											'post_type'      => 'post',
											'nopaging'       => true,
											'posts_per_page' => -1,
											'order'          => 'ASC',
											'orderby'        => 'title'
										);

										$sel_query = new WP_Query( $args );
										if ( $sel_query->have_posts() ) {
											while ( $sel_query->have_posts() ) : $sel_query->the_post();
												$args = array(
													'status'  => 'all',
													'post_id' => get_the_ID(),
													'count'   => true
												);
												$num_comments = get_comments($args);

												if ( $num_comments == 0 ) {
													continue;
												} elseif ( $num_comments > 1 ) {
													$comments = $num_comments . __(' Comments', 'gwolle-gb');
												} else {
													$comments = __('1 Comment', 'gwolle-gb');
												}

												echo '<option value="' . get_the_ID() . '">'. get_the_title() . ' (' . $comments . ')</option>';
											endwhile;
										}
										wp_reset_postdata(); ?>
										</select>
									</p>

									<?php
									$args = array(
										'status'  => 'all',
										'count'   => true
									);
									$num_comments = get_comments($args); ?>

									<p><label for="gwolle_gb_importfrom"><?php _e('Select where to import the comments from:', 'gwolle-gb'); ?></label><br />
										<label><input type="radio" name="gwolle_gb_importfrom" id="gwolle_gb_importfrom" value="page" /><?php _e('Comments from selected page.', 'gwolle-gb'); ?></label><br />
										<label><input type="radio" name="gwolle_gb_importfrom" id="gwolle_gb_importfrom" value="post" /><?php _e('Comments from selected post.', 'gwolle-gb'); ?></label><br />
										<label><input type="radio" name="gwolle_gb_importfrom" id="gwolle_gb_importfrom" value="all" /><?php _e('All Comments', 'gwolle-gb'); echo " (" . $num_comments . ")."; ?></label><br />
									</p>

									<p>
										<input name="start_import_wp" id="start_import_wp" type="submit" class="button" disabled value="<?php esc_attr_e('Start import', 'gwolle-gb'); ?>">
									</p>
								</form>
							</div> <!-- inside -->
						</div> <!-- wp_comm_div -->

						<div id="gwollediv" class="postbox">
							<div class="handlediv"></div>
							<h3 class='hndle' title="<?php esc_attr_e('Click to open or close', 'gwolle-gb'); ?>"><?php _e('Import guestbook entries from Gwolle-GB', 'gwolle-gb'); ?></h3>
							<div class="inside">
								<form name="gwolle_gb_import_gwolle" id="gwolle_gb_import_gwolle" method="POST" action="#" accept-charset="UTF-8" enctype="multipart/form-data">
									<input type="hidden" name="gwolle_gb_page" value="gwolle_gb_import" />

									<p>
										<label for="start_import_gwolle_file" class="selectit"><?php _e('Select a CSV file with exported entries to import again:', 'gwolle-gb'); ?><br />
											<input id="start_import_gwolle_file" name="start_import_gwolle_file" type="file" />
										</label>
									</p>
									<p>
										<input name="start_import_gwolle" id="start_import_gwolle" type="submit" class="button" disabled value="<?php esc_attr_e('Start import', 'gwolle-gb'); ?>">
									</p>
								</form>
							</div> <!-- inside -->
						</div> <!-- gwollediv -->

					</div><!-- 'normal-sortables' -->
				</div><!-- 'post-body-content' -->
			</div><!-- 'post-body' -->

		</div> <!-- poststuff -->
	</div> <!-- wrap -->

	<?php
}

