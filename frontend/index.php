<?php
	/*
	**	Guestbook frontend
	*/
	
	global $current_user;
	global $wpdb;
	global $textdomain;
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
    
  if ($_REQUEST['gb_page'] == 'write') {  //	Write mode
		include('write.php');
	}
	else { //	Read mode
		include('read.php');
	}
?>