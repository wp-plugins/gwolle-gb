<?php
	/*
	**	Deletes guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/admin/delete_entry.func.php');
	
	delete_gwolle_gb_entry($_REQUEST['entry_id'],true,$_REQUEST['show']);
?>