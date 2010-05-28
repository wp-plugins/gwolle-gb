<?php
	/*
	**	Editor for editing entries and writing admin entries.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_output_to_input_field.func.php');
	//	If a entry_id has been submitted, check if it's a valid one.
	$gwolle_gb_function  = 'add_entry';
	$entry_id            = '';
	$entry = FALSE;
	if (isset($_REQUEST['entry_id'])) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entries.func.php');
    $entry = gwolle_gb_get_entries(array(
      'entry_id' => $_REQUEST['entry_id']
    ));
    if ($entry !== FALSE) {
			$sectionHeading      = __('Edit guestbook entry',$textdomain);
			$gwolle_gb_function  = 'edit_entry';
			$entry_id            = $entry['entry_id'];
		}
		else {
			$errorMsg = __('Entry could not be found.',$textdomain);
		}
	}
	else {
		$sectionHeading = __('New guestbook entry', $textdomain);
	}
	
	if ($entry === FALSE) {
    $entry['entry_content']         = (isset($_SESSION['gwolle_gb']['entry']['content'])) ? $_SESSION['gwolle_gb']['entry']['content'] : '';
    $entry['entry_author_website']  = (isset($_SESSION['gwolle_gb']['entry']['website'])) ? $_SESSION['gwolle_gb']['entry']['website'] : '';
    $entry['entry_author_origin']   = (isset($_SESSION['gwolle_gb']['entry']['origin'])) ? $_SESSION['gwolle_gb']['entry']['origin'] : '';
  }
?>

<div class="wrap">

	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php echo $sectionHeading; ?></h2>
	<?php include(WP_PLUGIN_DIR.'/gwolle-gb/msg.php'); ?>
  
  <form name="gwolle_gb_editor" id="gwolle_gb_editor" method="POST" action="<?php bloginfo('wpurl'); ?>/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php" accept-charset="UTF-8">
    <input type="hidden" name="gwolle_gb_function" value="<?php echo $gwolle_gb_function; ?>" />
    <input type="hidden" name="entry_id" value="<?php echo $entry_id; ?>" />
    <input type="hidden" name="return_to" value="entries" />
    
    <div id="poststuff" class="metabox-holder has-right-sidebar">
      <div id="side-info-column" class="inner-sidebar">
        <div id='side-sortables' class='meta-box-sortables'>
          <div id="submitdiv" class="postbox " >
            <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
            <h3 class='hndle'><span><?php _e('Options',$textdomain); ?></span></h3>
            <div class="inside">
              <div class="submitbox" id="submitpost">
                <div id="minor-publishing">
                  <div id="misc-publishing-actions">
                    <div class="misc-pub-section misc-pub-section-last">
											<label for="entry_isChecked" class="selectit">
		                    <input id="entry_isChecked" name="entry_isChecked" type="checkbox" <?php if ($entry['entry_isChecked'] == '1') { echo 'checked="checked"'; } ?> /> <?php _e('This entry is checked.',$textdomain); ?>
		                  </label>
										</div>
                  </div><!-- 'misc-publishing-actions' -->
                  
                  <div class="clear"></div>
                </div>
                
                <div id="major-publishing-actions">
                  <div id="publishing-action">
                    
                    <?php if ($entry['entry_id']) { ?>
											<a class="submitdelete deletion" href="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/entries.php&amp;gwolle_gb_function=delete_entry&amp;entry_id=<?php echo $entry['entry_id']; ?>" onClick="return confirm('<?php _e("You\'re about to delete this guestbook entry. This can\'t be undone. Are you still sure you want to continue?",$textdomain); ?>');"><?php _e('Delete',$textdomain); ?></a>
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
                </div><!-- 'major-publishing-actions' -->
              </div><!-- 'submitbox' -->
            </div><!-- 'inside' -->
          </div><!-- 'submitdiv' -->

          <div id="gwolle_gb-entry-details" class="postbox " >
            <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
            <h3 class='hndle'><span><?php _e('Details',$textdomain); ?></span></h3>
            <div class="inside">
              <div class="tagsdiv" id="post_tag">
                <?php _e('Author',$textdomain); ?>: <span><?php if ($entry['entry_author_name']) { echo stripslashes(htmlentities($entry['entry_author_name'])); } else { echo '<strong>' . __('You',$textdomain) . '</strong>'; } ?></span>
								<br><br>
								<?php _e('E-Mail',$textdomain); ?>: <span><?php if (strlen(str_replace(' ','',$entry['entry_author_email'])) > 0) { echo stripslashes(htmlentities($entry['entry_author_email'])); } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
								<br><br>
								<?php _e('Written',$textdomain); ?>: <span><?php if ($entry['entry_date'] > 0) { echo date('d.m.Y, H:i',$entry['entry_date']) . ' ' . __("o'clock",$textdomain); } else { echo '(' . __('not yet',$textdomain) . ')'; } ?></span>
								<br><br>
								<?php _e("Author's IP-address",$textdomain); ?>: <span><?php if (strlen($entry['entry_author_ip']) > 0) { echo '<a href="http://www.db.ripe.net/whois?form_type=simple&searchtext='.$entry['entry_author_ip'].'" title="'.__('Whois search for this IP',$textdomain).'" target="_blank">'.$entry['entry_author_ip'].'</a>'; } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
								<br><br>
								<?php _e('Host',$textdomain); ?>: <span><?php if (strlen($entry['entry_author_host']) > 0) { echo $entry['entry_author_host']; } else { echo '<i>(' . __('unknown',$textdomain) . ')</i>'; } ?></span>
              </div>
            </div>
          </div><!-- Seitenbox-Ende -->
          
          <div id="tagsdiv-post_tag" class="postbox " >
            <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
            <h3 class='hndle'><span><?php _e('Entry log',$textdomain); ?></span></h3>
            <div class="inside">
              <div class="tagsdiv" id="post_tag">
                <div id="categories-pop" class="tabs-panel" style="height:150px;overflow:auto;">
                  <ul>
                    <?php
                    	if ($entry['entry_date'] > 0) {
                    		echo '<li>' . date('d.m.Y',$entry['entry_date']) . ': ' . __('Written',$textdomain) . '</li>';
                    		
                    		include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_log_entries.func.php');
                    		$log_entries = gwolle_gb_get_log_entries(array(
                    		  'subject_id'  => $entry['entry_id']
                    		));
                    		if ($log_entries !== FALSE) {
                    		  foreach($log_entries as $log_entry) {
                    		    echo '
                    		    <li>'.$log_entry['msg_html'].'</li>';
                    		  }
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
          </div><!-- Seitenbox-Ende -->
        </div><!-- 'side-sortables' -->
      </div><!-- 'side-info-column' -->
      
      <div id="post-body">
        <div id="post-body-content">
          <div id='normal-sortables' class='meta-box-sortables'>
            <div id="authordiv" class="postbox " >
              <div class="handlediv" title="Klicken zum Umschalten"><br /></div><h3 class='hndle'><span><?php _e('Guestbook entry',$textdomain); ?></span></h3>
              <div class="inside">
                <textarea rows="10" cols="56" name="entry_content" tabindex="1"><?php echo gwolle_gb_output_to_input_field($entry['entry_content']); ?></textarea>
                <?php if (get_option('gwolle_gb-showLineBreaks')=='false') { echo '<p>' . str_replace('%1','admin.php?page=gwolle-gb/settings.php',__('Line breaks will not be visible to the visitors due to your <a href="%1">settings</a>.',$textdomain)) . '</p>'; } ?>
              </div>
            </div>
            <div id="authordiv" class="postbox " >
              <div class="handlediv" title="Klicken zum Umschalten"><br /></div><h3 class='hndle'><span><?php _e('Homepage',$textdomain); ?></span></h3>
              <div class="inside">
                <input type="text" name="entry_author_website" size="58" tabindex="2" value="<?php echo gwolle_gb_output_to_input_field($entry['entry_author_website']); ?>" id="entry_author_website" />
                <p><?php _e("Example: <code>http://www.google.com/</code> &#8212; don't forget the <code>http://</code>!",$textdomain); ?></p>
              </div>
            </div>
            <div id="authordiv" class="postbox " >
              <div class="handlediv" title="Klicken zum Umschalten"><br /></div><h3 class='hndle'><span><?php _e('Origin',$textdomain); ?></span></h3>
              <div class="inside">
                <input type="text" name="entry_author_origin" size="58" tabindex="3" value="<?php echo gwolle_gb_output_to_input_field($entry['entry_author_origin']); ?>" id="entry_author_origin" />
              </div>
            </div>
          </div><!-- 'normal-sortables' -->
        </div><!-- 'post-body-content' -->
      </div>
      <br class="clear" />
    </div><!-- /poststuff -->
  </form>
</div>