<?php
/*
 *	Deletes guestbook entries
 */

//	No direct calls to this script
if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) {
	die('No direct calls allowed!');
}

// FIXME, just use a $_POST check in some action, and remove the form-action
delete_gwolle_gb_entry($_REQUEST['entry_id'], true, $_REQUEST['show']);
