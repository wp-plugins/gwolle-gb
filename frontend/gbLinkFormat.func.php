<?php
  //  Make sure we don't REdeklare the function.
  if (!function_exists('gwolle_gb_formatGuestbookLink')) {
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
  }
?>