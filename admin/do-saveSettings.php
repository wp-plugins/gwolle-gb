<?php
	/*
	**	Save settings to the database.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $wpdb;
	global $current_user;
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
	
	if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user's not allowed to do this.
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	else {
    //  Array of settings configured using checkboxes
    $checkbox_settings = array(
      'moderate-entries',
      'akismet-active',
      'showEntryIcons',
      'showLineBreaks',
      'guestbookOnly',
      'checkForImport',
      'showSmilies',
      'linkAuthorWebsite'
    );
    foreach($checkbox_settings as $setting_name) {
      if ($_POST[$setting_name] == 'on') {
  			update_option('gwolle_gb-'.$setting_name,'true');
  		}
  		else {
  			update_option('gwolle_gb-'.$setting_name,'false');
  		}
    }
		
		//	Access control
		if (is_numeric($_POST['access_level']) && $_POST['access_level'] >= 0 && $_POST['access_level'] <= 10) {
			update_option('gwolle_gb-access-level',$_POST['access_level']);
		}
		
		//	e-mail notification option
		if($_POST['notify_by_mail'] == 'on' && !get_option('gwolle_gb-notifyByMail-' . $current_user->data->ID)) {
			//	Turn on notification for the current user.
			add_option('gwolle_gb-notifyByMail-' . $current_user->data->ID, 'true');
		}
		elseif ($_POST['notify_by_mail'] != 'on' && get_option('gwolle_gb-notifyByMail-' . $current_user->data->ID)) {
			//	Turn the notification OFF for the current user
			delete_option('gwolle_gb-notifyByMail-' . $current_user->data->ID);
		}
		
		//	Notify on all entries.
		if ($_POST['notify_by_mail'] == 'on' && $_POST['notifyAll'] == 'on' && !get_option('gwolle_gb-notifyAll-' . $current_user->data->ID)) {
			add_option('gwolle_gb-notifyAll-' . $current_user->data->ID, 'true');
		}
		elseif (!get_option('gwolle_gb-notifyByMail-' . $current_user->data->ID) || $_POST['notifyAll'] != 'on') {
			delete_option('gwolle_gb-notifyAll-' . $current_user->data->ID);
		}
		
		//	Recaptcha settings
		if ($_POST['recaptcha-active'] == 'on') {
			update_option('gwolle_gb-recaptcha-active','true');
			update_option('recaptcha-public-key',$_POST['recaptcha-public-key']);
			update_option('recaptcha-private-key',$_POST['recaptcha-private-key']);
		}
		else {
			update_option('gwolle_gb-recaptcha-active','false');
		}
		
		//	Admin mail content
		if ($_POST['adminMailContent'] != $gwolle_gb_settings['defaultMailText']) {
			update_option('gwolle_gb-adminMailContent',$_POST['adminMailContent']);
		}
		
		//	Entries per page options
		if (is_numeric($_POST['entriesPerPage']) && $_POST['entriesPerPage'] > 0) {
			update_option('gwolle_gb-entriesPerPage',$_POST['entriesPerPage']);
		}
		
		//	Guestbook post ID
		update_option('gwolle_gb-post_ID', (int)$_POST['post_ID']);
		
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/settings.php&updated=true');
		exit;
	}
?>