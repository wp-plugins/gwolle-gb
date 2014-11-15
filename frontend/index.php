<?php
/*
 **	Guestbook frontend
 */

global $current_user;
global $wpdb;

// Load settings, if not set
global $gwolle_gb_settings;
if (!isset($gwolle_gb_settings)) {
	include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_settings.func.php');
	gwolle_gb_get_settings();
}

if ($_REQUEST['gb_page'] == 'write') {//	Write mode
	include ('write.php');
} else {//	Read mode
	include ('read.php');
}


?>
