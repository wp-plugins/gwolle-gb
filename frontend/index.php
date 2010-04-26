<?php
	/*
	**	Guestbook frontend
	*/
	
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/frontend/gbLinkFormat.func.php');
	
	global $current_user;
	global $wpdb;
	global $textdomain;
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
	
	//	Lets get the link to the page the guestbook is set up on.
	$manualGuestbookLink = $gwolle_gb_settings['guestbookLink'];
	if (strlen($manualGuestbookLink) > 0) {
		//	The guestbook link has been set manually.
		$gb_link = gwolle_gb_formatGuestbookLink($manualGuestbookLink);
	}
	else {
		/*
		**	Let's try to detect the guestbook link automatically.
		**	It's important to remove 'gb_page=write' and 'gb_page=read'
		**	from the REQUEST_URI, because it may already be appended.
		*/
		$gb_link = str_replace('gb_page=write','',$_SERVER['REQUEST_URI']);
		$gb_link = str_replace('gb_page=read','',$gb_link);
		$gb_link = gwolle_gb_formatGuestbookLink($gb_link);
	}
	
	if ($_REQUEST['gb_page'] == 'write') {
		//	Write mode
		include('write.php');
	}
	else {
		//	Read mode
		include('read.php');
	}
?>