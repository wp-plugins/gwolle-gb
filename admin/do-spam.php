<?php
	/*
	**	Handles spam/no spam-requests for guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	include('spam.func.php');
	
	if ($_REQUEST['action'] == 'markSpam') {
		$markAs = 'spam';
	}
	else {
		$markAs = 'no-spam';
	}
	
	spam_gwolle_gb_entry($_REQUEST['entry_id'],$markAs,true,$_REQUEST['show']);
?>