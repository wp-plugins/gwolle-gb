<?php
	/*
	**	Guestbook frontend
	*/
	
	global $current_user;
	global $wpdb;
	global $textdomain;
	
	//	Get link to the guestbook (using category or page?)
	if (is_numeric(get_query_var('cat'))) {
		$gb_link = '?cat=' . get_query_var('cat');
	}
	else {
		$gb_link = '?p=' . get_query_var('p');
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