<?php
	/*
	**	Guestbook frontend
	*/
	
	global $current_user;
	global $wpdb;
	global $textdomain;
	
	function gwolle_gb_formatGuestbookLink($gb_link) {
		/*
		**	This function tries to format the given $link into an address
		**	that can be used by the guestbook.
		*/
		$lastCharPos = strlen($gb_link) - 1;
		if ($gb_link[$lastCharPos] == '/') {
			//	The last char of the guestbook link is a slash. Only append a '?'.
			$gb_link .= '?';
		}
		elseif (strpos($gb_link,'?') > -1) {
			//	The '?' has already been entered. Append '&', if not already the last char.
			if ($gb_link[$lastCharPos] != '&') {
				$gb_link .= '&';
			}
		}
		elseif (!strpos($gb_link,'?') && $gb_link[$lastCharPos] != '/') {
			//	No '?' and no '/' entered. Append '/?'.
			$gb_link .= '/?';
		}
		return $gb_link;
	}
	
	//	Lets get the link to the page the guestbook is set up on.
	$manualGuestbookLink = get_option('gwolle_gb-guestbookLink');
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