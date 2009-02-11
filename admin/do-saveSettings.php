<?php
	/*
	**	Save settings to the database.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $wpdb;
	global $current_user;
	
	if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user's not allowed to do this.
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	else {
		//	Moderation option
		if ($_POST['moderate_guestbook'] == 'on') { update_option('gwolle_gb-moderate-entries','true'); }
		else { update_option('gwolle_gb-moderate-entries','false'); }
		
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
		
		//	Akismet settings
		if ($_POST['akismet-active'] == 'on') {
			update_option('gwolle_gb-akismet-active','true');
		}
		else {
			update_option('gwolle_gb-akismet-active','false');
		}
		
		//	Entry icons settings
		if ($_POST['showEntryIcons'] == 'on') {
			update_option('gwolle_gb-showEntryIcons','true');
		}
		else {
			update_option('gwolle_gb-showEntryIcons','false');
		}
		
		//	Admin mail content
		if ($_POST['adminMailContent'] != $defaultMailText) {
			update_option('gwolle_gb-adminMailContent',$_POST['adminMailContent']);
		}
		
		//	Entries per page options
		if (is_numeric($_POST['entriesPerPage']) && $_POST['entriesPerPage'] > 0) {
			update_option('gwolle_gb-entriesPerPage',$_POST['entriesPerPage']);
		}
		
		//	Guestbook link option
		update_option('gwolle_gb-guestbookLink',$_POST['guestbookLink']);
		
		header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/settings.php&updated=true');
		exit;
	}
?>