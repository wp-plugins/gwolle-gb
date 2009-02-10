<?php
	/*
	**	Guestbook frontend
	*/
	
	global $current_user;
	global $wpdb;
	global $textdomain;
	
	//	Get the guestbook link.
	global $post;
	if (is_numeric(get_query_var('cat'))) {
		$gb_link = '?cat=' . get_query_var('cat');
	}
	else {
		$gb_link = '?p=' . $post->ID;
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