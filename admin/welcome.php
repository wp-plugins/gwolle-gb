<?php
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entry_count.func.php');
		
	//	Calculate the number of entries
	$count['checked']    = gwolle_gb_get_entry_count(array(
    'entry_status' => 'checked'
  ));
	$count['unchecked']  = gwolle_gb_get_entry_count(array(
    'entry_status' => 'unchecked'
  ));
	$count['spam']       = gwolle_gb_get_entry_count(array(
    'entry_status' => 'spam'
  ));
	$count['all'] = $count['checked'] + $count['unchecked'] + $count['spam'];
	
	$msg = (isset($_REQUEST['msg'])) ? $_REQUEST['msg'] : FALSE;
?>

<div class="wrap">
	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php _e('Guestbook',$textdomain); ?></h2>
	<?php include(WP_PLUGIN_DIR.'/gwolle-gb/msg.php'); ?>
	<div id="dashboard-widgets-wrap" class="gwolle_gb-overview">
    <div id="dashboard-widgets" class="metabox-holder">
      <div id="post-body">
        <div id="dashboard-widgets-main-content">
          <div class="postbox-container" style="width:49%;">
            <div id='left-sortables' class='meta-box-sortables'>
              <div id="dashboard_right_now" class="postbox " >
                <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
                <h3 class='hndle'><span><?php _e('Welcome to the Guestbook!',$textdomain); ?></span></h3>
                <div class="inside">
                  <p class="sub"><?php _e('Overview',$textdomain); ?></p>
                  <div class="table">
                  	<table>
											<tbody>
												<tr class="first">
													<td class="first b"><a href="admin.php?page=gwolle-gb/entries.php">
														<?php echo $count['all']; ?>
													</a></td>
													<td class="t">
														<?php
															if ($count['all']==1) {
																_e('entry total',$textdomain);
															}
															else {
																_e('entries total',$textdomain);
															}
														?>
													</td>
													<td class="b"></td>
													<td class="last"></td>
												</tr>
												<tr>
													<td class="first b"><a href="admin.php?page=gwolle-gb/entries.php&amp;show=checked">
														<?php echo $count['checked']; ?>
													</a></td>
													<td class="t" style="color:#008000;">
														<?php
															if ($count['checked'] == 1) {
																_e('unlocked entry',$textdomain);
															}
															else {
																_e('unlocked entries',$textdomain);;
															}
														?>
													</td>
													<td class="b"></td>
													<td class="last"></td>
												</tr>
												<tr>
													<td class="first b"><a href="admin.php?page=gwolle-gb/entries.php&amp;show=unchecked">
														<?php echo $count['unchecked']; ?>
													</a></td>
													<td class="t" style="color:#FFA500;">
														<?php
															if ($count['unchecked'] == 1) {
																_e('new entry',$textdomain);
															}
															else {
																_e('new entries',$textdomain);
															}
														?>
													</td>
													<td class="b"></td>
													<td class="last"></td>
												</tr>
												<tr>
													<td class="first b"><a href="admin.php?page=gwolle-gb/entries.php&amp;show=spam">
														<?php echo $count['spam']; ?>
													</a></td>
													<td class="t" style="color:#FF0000;">
														<?php
															if ($count['spam'] == 1) {
																_e('spam entry',$textdomain);
															}
															else {
																_e('spam entries',$textdomain);
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
      								<a class="button rbutton" href="admin.php?page=gwolle-gb/editor.php"><strong><?php _e('Write admin entry',$textdomain); ?></strong></a>
      								<?php _e('Here you can manage the entries.',$textdomain); ?>
      							</p>
  									<span>
  										<?php echo str_replace('%1','<strong>' . $userLevelNames[(int)get_option('gwolle_gb-access-level')] . '</strong>',__('Only users with the role %1 have access to the guestbook backend.',$textdomain)); ?>
  									</span>
  								</div>
                </div><!-- 'inside'-DIV -->
              </div><!-- 'dashboard_right_now'-DIV -->
              <div id="ngg_lastdonators" class="postbox " >
                <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
                <h3 class='hndle'><span><?php _e('This plugin uses the following scripts/programs/images:',$textdomain); ?></span></h3>
                <div class="inside">
                  <div id="dashboard_server_settings" class="dashboard-widget-holder">
                    <div class="ngg-dashboard-widget">
                      <div class="dashboard-widget-content">
                	  		<ul class="settings">
      										<li><a href="http://akismet.com/tos/" target="_blank">Akismet</a></li>
      										<li><a href="http://miphp.net/blog/view/php4_akismet_class" target="_blank">Akismet PHP4-<?php _e('Class by',$textdomain); ?> Bret Kuhns</a></li>
      										<li><a href="http://www.achingbrain.net/stuff/php/akismet" target="_blank">Akismet PHP5-<?php _e('Class by',$textdomain); ?> Alex</a></li>
      										<li><a href="http://philipwilson.de/" target="_blank"><?php _e('Icons by',$textdomain); ?> Philip Wilson</a></li>
      										<li><a href="http://recaptcha.net/aboutus.html" target="_blank">Recaptcha</a></li>
      									</ul>
                		  </div><!-- 'dashboard-widget-content'-DIV -->
                    </div><!-- 'ngg-dashboard-widget'-DIV -->
                  </div><!-- 'dashboard_server_settings'-DIV -->
                </div><!-- 'inside'-DIV -->
              </div><!-- 'ngg_lastdonators'-DIV -->
            </div><!-- 'left_sortables'-DIV -->
          </div><!-- 'postbox-container'-DIV -->
          <div class="postbox-container" style="width:49%;">
				    <div id='right-sortables' class='meta-box-sortables'>
				      <?php if (get_option('gwolle_gb-checkForImport') == 'true') { ?>
  				      <?php
  				        //  Check if the 'dmsguestbook' table exists
  				        $result = mysql_query("
  				        SHOW
  				        TABLES
  				        LIKE '".$wpdb->prefix."dmsguestbook'");
  				        $foundTables = mysql_fetch_array($result);
  				        if ($foundTables[0] === $wpdb->prefix.'dmsguestbook') {
  				      ?>
    				      <div id="dashboard_primary" class="postbox " >
                    <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
                    <h3 class='hndle'><span><?php _e('Import of &quot;DMSGuestbook&quot; entries',$textdomain); ?></span></h3>
                    <div class="inside">
                      <?php _e('It looks like you have been using the plugin &quot;DMSGuestbook&quot;.<br>Do you want to import its entries into Gwolle-GB?',$textdomain); ?>
                      <br>
                      <p>
        								<a class="button rbutton" href="admin.php?page=gwolle-gb/gwolle-gb.php&amp;do=import&amp;what=dmsguestbook"><strong><?php _e('Sure, take me to the import.',$textdomain); ?></strong></a>
          						</p>
          						<span style="font-size:10px;"><?php echo str_replace('%1','admin.php?page=gwolle-gb/settings.php',__('You may disable this message at the <a href="%1">settings page</a> of Gwolle-GB.',$textdomain)); ?></span>
                    </div><!-- 'inside'-DIV -->
                  </div><!-- 'dashboard_primary'-DIV -->
                <?php } ?>
              <?php } ?>
              <div id="dashboard_primary" class="postbox " >
                <div class="handlediv" title="Klicken zum Umschalten"><br /></div>
                <h3 class='hndle'><span><?php _e('Help',$textdomain); ?></span></h3>
                <div class="inside">
                  <?php _e('This is how the guestbook will be displayed on your page',$textdomain); ?>:
                  <br><br>
									<ul>
										<li><?php _e('Create a new article or page.',$textdomain); ?></li>
										<li><?php _e("Choose a heading (doesn't matter) and set &quot;[gwolle-gb]&quot; (without the quotes) as the content.", $textdomain); ?></li>
										<li><?php _e("Of course, you should disable comments on that new content; otherwise, your visitors might get a little confused. ;)",$textdomain); ?></li>
									</ul>
                </div><!-- 'inside'-DIV -->
              </div><!-- 'dashboard_primary'-DIV -->
            </div><!-- 'right-sortables'-DIV -->
          </div><!-- 'postbox-container'-DIV -->
        </div><!-- 'dashboard-widgets-main-content'-DIV -->
      </div><!-- 'post-body'-DIV -->
    </div><!-- 'dashboard-widgets'-DIV -->
  </div><!-- 'dashboard-widgets-wrap'-DIV -->