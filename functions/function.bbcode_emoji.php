<?php


/*
 * Parse the BBcode into HTML for output.
 */
function gwolle_gb_bbcode_parse( $str ){
	$bb[] = "#\[b\](.*?)\[/b\]#si";
	$html[] = "<strong>\\1</strong>";
	$bb[] = "#\[i\](.*?)\[/i\]#si";
	$html[] = "<i>\\1</i>";
	$bb[] = "#\[u\](.*?)\[/u\]#si";
	$html[] = "<u>\\1</u>";
	$bb[] = "#\[ul\](.*?)\[/ul\]#si";
	$html[] = "<ul>\\1</ul>";
	$bb[] = "#\[ol\](.*?)\[/ol\]#si";
	$html[] = "<ol>\\1</ol>";
	$bb[] = "#\[li\](.*?)\[/li\]#si";
	$html[] = "<li>\\1</li>";
	$str = preg_replace($bb, $html, $str);

	$pattern="#\[url href=([^\]]*)\]([^\[]*)\[/url\]#i";
	$replace='<a href="\\1" target="_blank" rel="nofollow">\\2</a>';
	$str=preg_replace($pattern, $replace, $str);

	$pattern="#\[img\]([^\[]*)\[/img\]#i";
	$replace='<img src="\\1" alt=""/>';
	$str=preg_replace($pattern, $replace, $str);

	//$str=nl2br($str);
	return $str;
}


/*
 * Strip the BBcode from the output.
 */
function gwolle_gb_bbcode_strip( $str ){
	$bb[] = "#\[b\](.*?)\[/b\]#si";
	$html[] = "\\1";
	$bb[] = "#\[i\](.*?)\[/i\]#si";
	$html[] = "\\1";
	$bb[] = "#\[u\](.*?)\[/u\]#si";
	$html[] = "\\1";
	$bb[] = "#\[ul\](.*?)\[/ul\]#si";
	$html[] = "\\1";
	$bb[] = "#\[ol\](.*?)\[/ol\]#si";
	$html[] = "\\1";
	$bb[] = "#\[li\](.*?)\[/li\]#si";
	$html[] = "\\1";
	$str = preg_replace($bb, $html, $str);

	$pattern="#\[url href=([^\]]*)\]([^\[]*)\[/url\]#i";
	$replace='\\1';
	$str=preg_replace($pattern, $replace, $str);

	$pattern="#\[img\]([^\[]*)\[/img\]#i";
	$replace='';
	$str=preg_replace($pattern, $replace, $str);

	return $str;
}


/*
 * Get the list of Emoji for the form.
 */
function gwolle_gb_get_emoji() {
	$emoji = '
		<a title="😄">😄</a><a title="😃">😃</a><a title="😀">😀</a>
		<a title="😊">😊</a><a title="😉">😉</a><a title="😍">😍</a>
		<a title="😘">😘</a><a title="😚">😚</a><a title="😗">😗</a>
		<a title="😜">😜</a><a title="😝">😝</a><a title="😛">😛</a>
		<a title="😳">😳</a><a title="😁">😁</a><a title="😔">😔</a>
		<a title="😌">😌</a><a title="😒">😒</a><a title="😞">😞</a>
		<a title="😣">😣</a><a title="😢">😢</a><a title="😂">😂</a>
		<a title="😭">😭</a><a title="😪">😪</a><a title="😥">😥</a>
		<a title="😰">😰</a><a title="😅">😅</a><a title="😓">😓</a>
		<a title="😩">😩</a><a title="😫">😫</a><a title="😱">😱</a>
		<a title="😠">😠</a><a title="😡">😡</a><a title="😤">😤</a>
		<a title="😖">😖</a><a title="😆">😆</a><a title="😋">😋</a>
		<a title="😷">😷</a><a title="😎">😎</a><a title="😴">😴</a>
		<a title="😲">😲</a><a title="😧">😧</a><a title="😈">😈</a>
		<a title="👿">👿</a><a title="😮">😮</a><a title="😬">😬</a>
		<a title="😐">😐</a><a title="😕">😕</a><a title="😯">😯</a>
		<a title="😶">😶</a><a title="😇">😇</a><a title="😏">😏</a>
		<a title="😑">😑</a><a title="👲">👲</a><a title="👮">👮</a>
		<a title="💂">💂</a><a title="👶">👶</a><a title="❤">❤</a>
		<a title="💔">💔</a><a title="💕">💕</a><a title="💖">💖</a>
		<a title="💞">💞</a><a title="💘">💘</a><a title="💌">💌</a>
		<a title="💋">💋</a><a title="💍">💍</a>
		';
	return $emoji;
	}


/*
 * Convert to 3byte Emoji for storing in db, if db-charset is only utf8mb3.
 *
 * $Args: - string, text string to encode
 *        - field, the database field that is used for that string, will be checked on charset.
 *
 * Return: string, encoded or not.
 */
function gwolle_gb_maybe_encode_emoji( $string, $field ) {
	global $wpdb;
	if ( method_exists($wpdb, 'get_col_charset') ){
		$charset = $wpdb->get_col_charset( $wpdb->gwolle_gb_entries, $field );
		if ( 'utf8' === $charset && function_exists('wp_encode_emoji') ) {
			$string = wp_encode_emoji( $string );
		}
	}
	return $string;
}

