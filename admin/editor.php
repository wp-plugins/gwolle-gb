<?php
	/*
	**	Editor for editing entries and writing admin entries.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	//	If a entry_id has been submitted, check if it's a valid one.
	if (is_numeric($_REQUEST['entry_id']) && $_REQUEST['entry_id'] > 0) {
		$entry_result = mysql_query("
			SELECT *
			FROM
				" . $wpdb->prefix . "gwolle_gb_entries
			WHERE
				entry_id = '" . $_REQUEST['entry_id'] . "'
			LIMIT 1
		");
		if (mysql_num_rows($entry_result) == 1) {
			$sectionHeading = __('Edit guestbook entry',$textdomain);
			$entry = mysql_fetch_array($entry_result);
		}
		else {
			$errorMsg = __('Entry could not be found.',$textdomain);
		}
	}
	else {
		$sectionHeading = __('New guestbook entry', $textdomain);
	}
?>

<div class="wrap">

	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php echo $sectionHeading; ?></h2>
	
	<?php
		if ($_REQUEST['updated']) {
			echo '<div id="message" class="updated fade"><p>' . __('Changes saved.',$textdomain) . '</p></div>';
		}
		elseif ($_REQUEST['error']) {
			echo '<div id="message" class="error fade"><p>' . __('An error occurred while saving your changes.',$textdomain) . '</p></div>';
		}
		elseif ($entry['entry_isSpam'] == '1') {
			//	Notify that this entry is marked as spam.
			echo '<div id="message" class="error fade"><p>' . __('<strong>Attention:</strong> This entry is marked as spam!',$textdomain) . '</p></div>';
		}
		elseif ($_REQUEST['msg'] == 'successfully-unmarkedSpam') {
			echo '<div id="message" class="updated fade"><p>' . __('Entry successfully classified as not-spam.',$textdomain) . '</p></div>';
		}
	?>

	<form name="editlink" id="editlink" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/editor.php">
		<?php if ($entry['entry_id']) { ?>
			<input type="hidden" id="entry_id" name="entry_id" value="<?php echo $entry['entry_id']; ?>">
		<?php } else { ?>
			<input type="hidden" id="action" name="action" value="newEntry">
		<?php } ?>
		
		<div id="poststuff" class="metabox-holder">
			<div id="side-info-column" class="inner-sidebar">
				<div id='side-sortables' class='meta-box-sortables'>
					<div id="linksubmitdiv" class="postbox " >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span><?php _e('Save',$textdomain); ?></span></h3>
						<div class="inside">
							<div class="submitbox" id="submitlink">
								<div id="minor-publishing">
									<div style="display:none;">
										<input type="submit" name="save" value="<?php _e('Options',$textdomain); ?>">
									</div>
									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-section-last">
											<label for="entry_isChecked" class="selectit"><input id="entry_isChecked" name="entry_isChecked" type="checkbox" <?php if ($entry['entry_isChecked'] == '1') { echo 'checked="checked"'; } ?>> <?php _e('This entry is checked.',$textdomain); ?></label>
										</div>
									</div>
								</div>
								<div id="major-publishing-actions">
									<div id="publishing-action">
										<?php if ($entry['entry_id']) { ?>
											<a class="submitdelete deletion" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/editor.php&amp;action=delete&amp;entry_id=<?php echo $entry['entry_id']; ?>" onClick="return confirm('<?php _e("You\'re about to delete this guestbook entry. This can\'t be undone. Are you still sure you want to continue?",$textdomain); ?>');"><?php _e('Delete',$textdomain); ?></a>
											&nbsp;
											<?php
												if ($entry['entry_isSpam'] == '0') {
											?>
													<a class="submitdelete deletion" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/editor.php&amp;action=markSpam&amp;entry_id=<?php echo $entry['entry_id']; ?>&amp;show=editor" onClick="return confirm('<?php _e("You\'re about to mark this guestbook entry as spam. It will be sent to the Akismet team to help other people fighting spam. Entries marked as spam are automatically deleted after 15 days. Continue?",$textdomain); ?>');"><?php _e('Spam!',$textdomain); ?></a>
											<?php
												}
												else {
											?>
													<a class="submitdelete deletion" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/editor.php&amp;action=unmarkSpam&amp;entry_id=<?php echo $entry['entry_id']; ?>&amp;show=editor" onClick="return confirm('<?php _e("A message will be sent to the Akismet team that this entry is not spam. Continue?",$textdomain); ?>');"><?php _e('No Spam!',$textdomain); ?></a>
											<?php
												}
											}
										?>
										&nbsp;
										<input name="save" type="submit" class="button-primary" id="publish" tabindex="4" accesskey="p" value="<?php _e('Save',$textdomain); ?>" />
									</div>
									<div class="clear"></div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
					
					<!-- info-div-->
					<div id="linksubmitdiv" class="postbox " >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span><?php _e('Details',$textdomain); ?></span></h3>
						<div class="inside">
							<div class="submitbox" id="submitlink">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-section-last">
											<?php _e('Author',$textdomain); ?>: <span><?php if ($entry['entry_author_name']) { echo $entry['entry_author_name']; } else { echo '<strong>' . __('You',$textdomain) . '</strong>'; } ?></span>
											<br><br>
											<?php _e('E-Mail',$textdomain); ?>: <span><?php if (strlen(str_replace(' ','',$entry['entry_author_email'])) > 0) { echo stripslashes(htmlentities($entry['entry_author_email'])); } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
											<br><br>
											<?php _e('Written',$textdomain); ?>: <span><?php if ($entry['entry_date'] > 0) { echo date('d.m.Y, H:i',$entry['entry_date']) . ' ' . __("o'clock",$textdomain); } else { echo '(' . __('not yet',$textdomain) . ')'; } ?></span>
											<br><br>
											<?php _e("Author's IP-address",$textdomain); ?>: <span><?php if (strlen($entry['entry_author_ip']) > 0) { echo $entry['entry_author_ip']; } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
											<br><br>
											<?php _e('Host',$textdomain); ?>: <span><?php if (strlen($entry['entry_author_host']) > 0) { echo $entry['entry_author_host']; } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
					
					<!-- log-div -->
					<div id="linksubmitdiv" class="postbox " >
						<div class="handlediv" title="Click to toggle"><br /></div>
						<h3 class='hndle'><span><?php _e('Log',$textdomain); ?></span></h3>
						<div class="inside">
							<div class="submitbox" id="submitlink">
								<div id="minor-publishing">
									<div id="misc-publishing-actions">
										<div class="misc-pub-section misc-pub-section-last">
											<ul>
												<?php
													if ($entry['entry_date'] > 0) {
														echo '<li>' . date('d.m.Y',$entry['entry_date']) . ': ' . __('Written',$textdomain) . '</li>';
														
														$msg['entry-unchecked'] = __('Entry has been locked.',$textdomain);
														$msg['entry-checked'] = __('Entry has been unlocked.',$textdomain);
														$msg['marked-as-spam'] = __('Entry marked as spam.',$textdomain);
														$msg['marked-as-not-spam'] = __('Entry marked as not-spam.',$textdomain);
														
														//	Get all log entries for this entry from the database.
														$log_result = mysql_query("
															SELECT *
															FROM
																" . $wpdb->prefix . "gwolle_gb_log
															WHERE
																log_subjectId = '" . $_REQUEST['entry_id'] . "'
															ORDER BY
																log_date ASC
														");
														while ($log = mysql_fetch_array($log_result)) {
															if ($log['log_authorId'] == $current_user->data->ID) {
																$author = '<strong>' . __('You',$textdomain) . '</strong>';
															}
															else {
																if (!$userdata[$log['log_authorId']]) {
																	//	Get userdata of the author, if not already done.
																	$userdata[$log['log_authorId']] = get_userdata($log['log_authorId']);
																}
																$author = $userdata[$log['log_authorId']]->user_login;
															}
															echo '<li>' . date('d.m.Y', $log['log_date']) . ': ' . $msg[$log['log_subject']] . ' (' . $author . ')</li>';
														}
													}
													else {
														echo '<li>(' . __('No entries yet.',$textdomain) . ')</li>';
													}
												?>
											</ul>
										</div>
									</div>
								</div>
								<div class="clear"></div>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
				
					<div id="contentdiv" class="stuffbox">
						<h3><label for="link_name"><?php _e('Guestbook entry',$textdomain); ?></label></h3>
						<div class="inside">
							<textarea rows="10" cols="56" name="entry_content"><?php echo stripslashes(htmlentities(utf8_decode($entry['entry_content']))); ?></textarea>
						</div>
					</div>
					
					<div id="homepagediv" class="stuffbox">
						<h3><label for="link_url"><?php _e('Homepage',$textdomain); ?></label></h3>
						<div class="inside">
							<input type="text" name="entry_author_website" size="58" tabindex="1" value="<?php echo stripslashes(htmlentities($entry['entry_author_website'])); ?>" id="entry_author_website">
	    				<p><?php _e("Example: <code>http://www.google.com/</code> &#8212; don't forget the <code>http://</code>!",$textdomain); ?></p>
						</div>
					</div>
					
					<div id="origindiv" class="stuffbox">
						<h3><label for="link_description"><?php _e('Origin',$textdomain); ?></label></h3>
						<div class="inside">
							<input type="text" name="entry_author_origin" size="58" tabindex="1" value="<?php echo stripslashes(htmlentities($entry['entry_author_origin'])); ?>" id="entry_author_origin">
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</form>
</div>
