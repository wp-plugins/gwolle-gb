<?php
	/*
	**	Settings page for the guestbook
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $current_user;
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  $setting_page = (isset($_REQUEST['setting_page'])) ? $_REQUEST['setting_page'] : FALSE;
?>

<div class="wrap">

	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php _e('Settings',$textdomain); ?></h2>
  <?php
    if ($setting_page === FALSE) {
      include(WP_PLUGIN_DIR.'/gwolle-gb/msg.php');
  ?>
	
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/settings.php&amp;action=saveSettings">
			
			<table class="form-table">
        
        <tr valign="top">
					<th scope="row"><label for="post_ID"><?php _e('ID of the guestbook post',$textdomain); ?></label></th>
					<td>
            <?php
              //  Check if a post with that ID exists
              $value = ((int)$gwolle_gb_settings['post_ID'] === 0) ? '' : (int)$gwolle_gb_settings['post_ID'];
              if ((int)$value > 0) {
                $sql = "
                SELECT
                  ID
                FROM
                  ".$wpdb->posts." p
                WHERE
                  p.ID = ".$value."
                  AND
                  p.post_content LIKE '%[gwolle-gb]%'
                LIMIT 1";
                $result = mysql_query($sql);
                if (mysql_num_rows($result) == 0) {
                  //  Post does not exist or does not contain the tag.
                  $value = '';
                }
              }
            ?>
            <input type="text" maxlength="11" style="width:110px;" name="post_ID" id="post_ID" value="<?php echo $value; ?>" class="regular-text" />
            <?php
              $post_id_img = ($value == '') ? 'entry-unchecked.jpg' : 'blank.gif';
              echo '<img id="post_id_status" style="height:10px;" src="'.WP_PLUGIN_URL.'/gwolle-gb/admin/gfx/'.$post_id_img.'" alt="'.__('Not set.', $textdomain).'" />';
						?>
						&nbsp;<a id="search_gwolle_gb_post_ID" href="javascript:void(0);" title="<?php _e('Click here to let Gwolle-GB search for the post.',$textdomain); ?>"><?php _e('Search now!', $textdomain); ?></a>
						<br />
						<div style="display:none;margin-top:5px;" id="gwolle_gb_post_seach_result">Sorry, post not found. <?php echo convert_smilies(':('); ?></div>
						<span class="setting-description"><?php _e('This ID is used to generate the correct link to the guestbook read/write page at the frontend.',$textdomain); ?></span>
					</td>
				</tr>
			
				<tr valign="top">
					<th scope="row"><label for="moderate-entries"><?php _e('Moderate Guestbook',$textdomain); ?></label></th>
					<td>
						<input <?php if ($gwolle_gb_settings['moderate-entries'] === TRUE) { echo 'checked="checked"'; } ?> type="checkbox" name="moderate-entries" id="moderate-entries"> <?php _e('Moderate entries before publishing them.',$textdomain); ?>
						<br />
						<span class="setting-description">
							<?php _e("New entries have to be unlocked by an administrator before they are visible to the public.",$textdomain); ?>
							<br />
							<?php _e("It's highly recommended that you turn this on, because your responsible for the content on your homepage.",$textdomain); ?>
						</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="access_control"><?php _e('Access control',$textdomain); ?></label></th>
					<td>
						<select name="access_level">
							<?php
								global $userLevelNames;
								for ($i=10; $i>=0; $i--) {
									if (strlen($i) == 1) { $zahl = '0' . $i; } else { $zahl = $i; }
									echo '<option'; if (get_option('gwolle_gb-access-level') == $i) { echo ' selected="selected"'; } echo ' value="' . $i . '">' . $zahl . ' - ' . $userLevelNames[$i] . '</option>';
								}
							?>
						</select>
						<br />
						<span class="setting-description"><?php _e('Choose the userlevel that has access to the guestbook backend.',$textdomain); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="blogname"><?php _e('Notification',$textdomain); ?></label></th>
					<td>
						<?php
							//	Check if function mail() exists. If not, display a hint to the user.
							if (!function_exists('mail')) {
								echo '<span class="setting-description">' . __('Sorry, but the function <code>mail()</code> required to notify you by mail is not enabled in your PHP configuration. Please contact your hosting provider to change this.',$textdomain) . '</span>';
							}
							else {
						?>
								<input name="notify_by_mail" type="checkbox" id="notify_by_mail" <?php if (isset($gwolle_gb_settings['notifyByMail-'.$current_user->data->ID]) && $gwolle_gb_settings['notifyByMail-'.$current_user->data->ID] === TRUE) { $currentUserNotification = true; echo 'checked="checked"'; } ?>>
								<span class="setting-description"><?php _e('Send me an e-mail when a new entry has been posted.',$textdomain); ?></span>
								<br />
								<input name="notifyAll" type="checkbox" id="notifyAll" <?php if (isset($gwolle_gb_settings['notifyAll-'.$current_user->data->ID]) && $gwolle_gb_settings['notifyAll-'.$current_user->data->ID] === TRUE) { echo 'checked="checked"'; } ?>>
								<span class="setting-description"><?php _e('E-Mail me no matter the new entry is spam or not.',$textdomain); ?></span>
								
								<div>
									<?php
										_e('The following users have subscribed to this service:',$textdomain);
			
										//	Get users from database who have subscribed to the notification service.
										$notifyUser_result = mysql_query("
											SELECT *
											FROM
												" . $wpdb->prefix . "options
											WHERE
												option_name LIKE 'gwolle_gb-notifyByMail-%'
											ORDER BY
												option_name
										");
										if (mysql_num_rows($notifyUser_result) == 0) {
											echo '<br /><i>(' . __('no subscriber yet',$textdomain) . ')</i>';
										}
										else {
											echo '<ul style="font-size:10px;font-style:italic;list-style-type:disc;padding-left:14px;">';
												while ($option = mysql_fetch_array($notifyUser_result)) {
												  $user_id = (int)str_replace('gwolle_gb-notifyByMail-','',$option['option_name']);
													$user_info = get_userdata($user_id);
													if ($user_info === FALSE) {
                            //  Invalid $user_id
                            continue;
                          }
													echo '<li>';
														if ($user_info->ID == $current_user->data->ID) {
															echo '<strong>' . __('You',$textdomain) . '</strong>';
														}
														else {
															echo $user_info->first_name . ' ' . $user_info->last_name;
														}
														echo ' (' . $user_info->user_email . ')';
													echo '</li>';
												}
											echo '</ul>';
										}
									?>
								</div>
						<?php
							}
						?>
					</td>
				</tr>
				
				<?php
					$recaptcha_publicKey = get_option('recaptcha-public-key');
					$recaptcha_privateKey = get_option('recaptcha-private-key');
				?>
				<tr valign="top">
					<th scope="row"><label for="recaptcha-settings">Recaptcha</label><br /><span class="setting-description"><a href="http://recaptcha.net/learnmore.html" title="<?php _e('Learn more about Recaptcha...',$textdomain); ?>" target="_blank"><?php _e("What's that?",$textdomain); ?></a></span></th>
					<td>
						<input name="recaptcha-active" <?php if ($gwolle_gb_settings['recaptcha-active'] === TRUE) { echo 'checked="checked" '; } ?>id="use-recaptcha" type="checkbox"> <?php _e('Use Recaptcha',$textdomain); ?>
						<br />
						<input name="recaptcha-public-key" type="text" id="recaptcha-public-key"  value="<?php echo $recaptcha_publicKey; ?>" class="regular-text" />
						<span class="setting-description"><?php _e('<strong>Public</strong> key of your Recaptcha account',$textdomain); ?></span>
						<br />
						<input name="recaptcha-private-key" type="text" id="recaptcha-private-key"  value="<?php echo $recaptcha_privateKey; ?>" class="regular-text" />
						<span class="setting-description"><?php _e('<strong>Private</strong> key of your Recaptcha account',$textdomain); ?></span>
						<br />
						<span class="setting-description"><?php _e('The keys can be found at your',$textdomain); ?> <a href="https://admin.recaptcha.net/recaptcha/sites/" title="<?php _e('Go to my reCAPTCHA sites...',$textdomain); ?>" target="_blank"><?php _e('reCAPTCHA sites overview',$textdomain); ?></a>.</span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="akismet-settings">Akismet</label><br /><span class="setting-description"><a href="http://akismet.com/" title="<?php _e('Learn more about Akismet...',$textdomain); ?>" target="_blank"><?php _e("What's that?",$textdomain); ?></a></span></th>
					<td>
						<?php
							$current_plugins = get_option('active_plugins');
							$wordpress_api_key = get_option('wordpress_api_key');
							$gwolle_wordpress_api_key = get_option('gwolle_gb-wordpress-api-key');
							
							
							//	Check Wordpress API key if <> the cached key
							if ($wordpress_api_key != $gwolle_wordpress_api_key) {
								if (in_array('akismet/akismet.php',$current_plugins) && $wordpress_api_key) {
									if (version_compare(phpversion(),'5.0','>=')) {
										//	PHP version >= 5 installed. Use the PHP5 class for Akismet.
										include('../wp-content/plugins/gwolle-gb/' . AKISMET_PHP5_CLASS_DIR . '/Akismet.class.php');
										$akismet = new Akismet(get_bloginfo('wpurl'), $wordpress_api_key);
										$apiKeyValid = ($akismet->isKeyValid()) ? TRUE : FALSE;
										
									}
									elseif (version_compare(phpversion(),'4.0','>=')) {
										//	Use the PHP4 Akismet class
										include('../wp-content/plugins/gwolle-gb/' . AKISMET_PHP4_CLASS_DIR . '/Akismet.class.php');
										$comment = array(
											'author' => 'viagra-test-123',
											'email' => 'test@example.com',
											'website' => 'http://www.example.com/',
											'body' => 'This is a test comment',
											'permalink' => get_bloginfo('wpurl')
										); 
										$akismet = new Akismet(get_bloginfo('wpurl'), $wordpress_api_key, $comment);
										$apiKeyValid = (!$akismet->isError('AKISMET_INVALID_KEY')) ? TRUE : FALSE;
									}
								}
							}
							else {
								$apiKeyValid = TRUE;
							}
							
							//	Check which class is used
							if (version_compare(phpversion(),'5.0','>=')) {
								$classUsed = str_replace('%1','http://www.achingbrain.net/stuff/php/akismet',__('Using the <a href="%1" target="_blank">Akismet PHP5-Class by Alex</a>.',$textdomain));
							}
							elseif (version_compare(phpversion(),'4.0','>=')) {
								$classUsed = str_replace('%1','http://miphp.net/blog/view/php4_akismet_class',__('Using the <a href="%1" target="_blank">Akismet PHP4-Class by Bret Kuhns</a>.',$textdomain));
							}
							
							//	Check wether Akismet is installed or not.
							if (!in_array('akismet/akismet.php', $current_plugins)) {
								_e("Akismet helps you to fight spam. It's free and easy to install. Download and install it today to stop spam in your guestbook.",$textdomain);
							}
							//	Check PHP-Version. Only 4.x and higher can use the PHP classes that come with Gwolle-GB.
							elseif (version_compare(phpversion(),'4.0','<')) {
								echo str_replace('%1',phpversion(),__("I'm sorry, but it seems you're running an PHP version prior 4.x. The Akismet classes that come with Gwolle-GB are only compatible to 4.x and higher. To be able to use Akismet you should consider updating your server. (The System says your PHP version is %1.)",$textdomain));
							}
							//	Check if a Wordpress API key is defined and set in the database.
							elseif (!$wordpress_api_key) {
								echo str_replace('%1','plugins.php?page=akismet-key-config',__("Sorry, wasn't able to locate your <strong>Wordpress API key. You can enter it at the <a href=\"%1\">Akismet configuration page</a>.",$textdomain));
							}
							//	The API key doesn't seem to be valid
							elseif ($apiKeyValid === FALSE) {
								echo str_replace('%1','plugins.php?page=akismet-key-config',__("There seems to be something wrong with your Wordpress API key; wasn't able to validate it correctly. Please check it via <a href=\"%1\">Akismet configuration page</a>.",$textdomain));
							}
							else {
								//	Akismet is installed, PHP-Version is 4.x or higher and a Wordpress api key exists
								echo '<input '; if ($gwolle_gb_settings['akismet-active'] === TRUE) { echo 'checked="checked" '; } echo 'name="akismet-active" id="akismet-active" type="checkbox" /> ' . __('Use Akismet',$textdomain) . ' <i>(' . $classUsed . ')</i>';
								echo '<br />';
								_e("I've found the Wordpress API key, so you can start using Akismet right now.",$textdomain);
							}
						?>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="showEntryIcons"><?php _e('Entry icons',$textdomain); ?></label></th>
					<td>
						<input type="checkbox" <?php if ($gwolle_gb_settings['showEntryIcons'] === TRUE) { echo 'checked="checked"'; } ?> name="showEntryIcons" /> <?php _e('Show entry icons',$textdomain); ?>
						<br />
						<span class="setting-description"><?php _e('These icons are shown in every entry row, so that you know its entry status (spam, locked or unlocked).',$textdomain); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="entriesPerPage"><?php _e('Entries per page',$textdomain); ?></label></th>
					<td>
						<select name="entriesPerPage">
							<?php
								$entriesPerPage = get_option('gwolle_gb-entriesPerPage');
								$presets = array(5,10,15,20,25,30,40,50,60,70,80,90,100,120,150,200,250);
								for ($i=0; $i<count($presets); $i++) {
									echo '<option value="' . $presets[$i] . '"'; if ($presets[$i] == $entriesPerPage) { echo ' selected="selected"'; } echo '>' . $presets[$i] . ' ' . __('Entries',$textdomain) . '</option>';
								}
							?>
						</select>
						<br />
						<span class="setting-description"><?php _e('Number of entries shown on the frontend in reading mode.',$textdomain); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="entriesPerPage"><?php _e('Appearance',$textdomain); ?></label></th>
					<td>
						<input type="checkbox" id="showLineBreaks" name="showLineBreaks"<?php if ($gwolle_gb_settings['showLineBreaks'] === TRUE) { echo ' checked="checked"'; } ?> /> <label for="showLineBreaks"><?php _e('Show line breaks.',$textdomain); ?></label>
						<br />
						<span class="setting-description"><?php _e('Show line breaks as the entry authors entered them. (May result in very long entries. Is turned off by default.)',$textdomain); ?></span>
						<br />
						<input type="checkbox" id="guestbookOnly" name="guestbookOnly"<?php if ($gwolle_gb_settings['guestbookOnly'] === TRUE) { echo ' checked="checked"'; } ?> /> <label for="guestbookOnly"><?php _e('Display the guestbook only, not the text around it.',$textdomain); ?></label>
						<br />
						<span class="setting-description"><?php _e('If you wanted to have content prepended/appended to the guestbook, such as a warm "Welcome, please leave me an entry.", turn this off.',$textdomain); ?></span>
						<br />
						<input type="checkbox" id="showSmilies" name="showSmilies"<?php if ($gwolle_gb_settings['showSmilies'] === TRUE) { echo ' checked="checked"'; } ?> /> <label for="showSmilies"><?php _e('Display smilies as images.',$textdomain); ?></label>
						<br />
						<span class="setting-description"><?php echo str_replace('%1',convert_smilies(':)'),__("Replaces smilies in entries like :) with their image %1. Uses the WP smiley replacer, so check on that one if you'd like to add new/more smilies.",$textdomain)); ?></span>
						<br />
						<input type="checkbox" id="showSmilies" name="linkAuthorWebsite"<?php if ($gwolle_gb_settings['linkAuthorWebsite'] === TRUE) { echo ' checked="checked"'; } ?> /> <label for="showSmilies"><?php _e("Link authors' name to their website.",$textdomain); ?></label>
						<br />
						<span class="setting-description"><?php echo str_replace('%1',convert_smilies(':)'),__("The author of an entry can set his/her website. If this setting is checked, his/her name will be linked to that website.",$textdomain)); ?></span>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row"><label for="adminMailContent"><?php _e('Admin mail content',$textdomain); ?></label></th>
					<td>
						<?php
							$adminMailContent = get_option('gwolle_gb-adminMailContent');
							if (!$adminMailContent) { //	No text set by the user. Use the default text.
								$mailText = $gwolle_gb_settings['defaultMailText'];
							}
							else {
								$mailText = stripslashes($adminMailContent);
							}
						?>
						<textarea name="adminMailContent" id="adminMailContent" style="width:400px;height:200px;" class="regular-text"><?php echo $mailText; ?></textarea>
						<br />
						<span class="setting-description">
							<?php
								_e('You can set the content of the mail a notification subscriber gets on new entries. The following tags are supported:',$textdomain);
								echo '<br />';
								$mailTags = array('user_email','entry_management_url','blog_name','blog_url','wp_admin_url','entry_content');
								for ($i=0; $i<count($mailTags); $i++) { if ($i!=0) { echo '&nbsp;,&nbsp;'; } echo '%' . $mailTags[$i] . '%'; }
							?>
						</span>
					</td>
				</tr>
		    
		    <tr valign="top">
					<th scope="row"><label for="checkForImport"><?php _e('Check for import',$textdomain); ?></label></th>
					<td>
						<input <?php if ($gwolle_gb_settings['checkForImport'] === TRUE) { echo 'checked="checked"'; } ?> type="checkbox" name="checkForImport" id="checkForImport" /> <?php _e('Check if there are other guestbook plugins installed to import their entries.',$textdomain); ?>
						<br />
						<span class="setting-description">
							<?php _e("If Gwolle-GB detects another guestbook plugin for WordPress it'll prompt you to import that guestbooks's data.",$textdomain); ?>
						</span>
					</td>
				</tr>
		    
				<tr>
					<td colspan="" style="">&nbsp;</td>
					<td>
						<p class="submit">
							<input type="submit" name="Submit" class="button-primary" value="<?php _e('Save settings',$textdomain); ?>" />
						</p>
					</td>
				</tr>
				
				<?php /* The following is just the standard row for WordPress, just that we can use copy & paste for new setting rows. I'm just as lazy as you are. ;) */ ?>
				<!--
				<tr valign="top">
					<th scope="row"><label for="blogdescription">Slogan</label></th>
					<td>
						<input name="blogdescription" type="text" id="blogdescription"  value="Ein weiteres tolles WordPress-Blog" class="regular-text" />
						<span class="setting-description">Kurzer Untertitel des Weblogs.</span>
					</td>
				</tr>
				-->
				
			</table>
		</form>
		
		<!-- uninstall section -->
		<table style="margin-top:30px;" class="form-table">
			<tr valign="top" style="margin-top:30px;">
				<th scope="row" style="color:#FF0000;"><label for="blogdescription"><?php _e('Uninstall',$textdomain); ?></label></th>
				<td>
					<?php
						_e('Uninstalling means that all database entries are removed (settings and entries).',$textdomain);
						echo '<br />';
						_e('This can <strong>not</strong> be undone.',$textdomain);
					?>
					<br />
					<a style="color:#ff0000;" href="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page']; ?>&amp;setting_page=uninstall"><?php _e("I'm aware of that, continue!",$textdomain); ?> &raquo;</a>
				</td>
			</tr>
		</table>
	<?php } elseif ($setting_page == 'uninstall') { ?>
		<form action="<?php echo $_SERVER['PHP_SELF']; ?>?page=gwolle-gb/settings.php&amp;action=uninstall_gwolle_gb" method="POST">
			<?php _e("I really don't want to bother you; this page just exists to prevent you from accidentally deleting all your entries.<br />Please check the 'uninstall' checkbox and hit the button; all tables (including their rows) and all settings of Gwolle-GB will be deleted.<br /><br />Are you REALLY sure you wan't to continue? There's no 'undo'.",$textdomain); ?>
			<br />
			<br />
			<input type="checkbox" name="uninstall_confirmed"> <?php _e("Yes, I'm absolutely sure of this. Proceed!",$textdomain); ?>
			<br />
			<br />
			<input type="submit" class="button" value="<?php _e("Uninstall &raquo;",$textdomain); ?>">
		</form>
	<?php
		}
		else {
			str_replace('%1',$_SERVER['PHP_SELF'] . '?page=gwolle-gb/settings.php',__('Sorry, but the page you\'re looking for does not exists. Go back to the <a href="%1">settings page</a>', $textdomain));
		}
	?>
			
			
</div>