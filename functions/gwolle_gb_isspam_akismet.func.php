<?php
/**
 * Akismet Function
 * Akismet API: http://akismet.com/development/api/
 * Copied from Contact Form 7
 */


/*
 * $params: array with a guestbook entry to be checked
 * Return: true if the entry is considered spam by akismet
 *         false if no spam, or no akismet functionality is found
 * Plan: to be replaced with functions.gwolle_gb_isspam_akismet.php which uses the new class
 */
function gwolle_gb_isspam_akismet_old( $params ) {

	if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) { // Akismet v3.0+
		$api_key = (bool) Akismet::get_api_key();
	} else if ( function_exists( 'akismet_get_key' ) ) {
		$api_key = (bool) akismet_get_key();
	}

	if ( !$api_key ) {
		// no api key, no glory
		return false;
	}

	$c = array();

	// FIXME; check for the correct fieldnames
	if ( ! empty( $params['name'] ) )
		$c['comment_author'] = $params['name'];

	if ( ! empty( $params['email'] ) )
		$c['comment_author_email'] = $params['email'];

	if ( ! empty( $params['origin'] ) )
		$c['comment_author_origin'] = $params['origin'];

	if ( ! empty( $params['website'] ) )
		$c['comment_author_url'] = $params['website'];

	if ( ! empty( $params['content'] ) )
		$c['comment_content'] = $params['content'];

	$c['blog'] = get_option( 'home' );
	$c['blog_lang'] = get_locale();
	$c['blog_charset'] = get_option( 'blog_charset' );
	$c['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$c['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	$c['referrer'] = $_SERVER['HTTP_REFERER'];

	// http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	$c['comment_type'] = 'comment';

	if ( $permalink = get_permalink() )
		$c['permalink'] = $permalink;

	$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );
	foreach ( $_SERVER as $key => $value ) {
		if ( ! in_array( $key, (array) $ignore ) )
			$c["$key"] = $value;
	}

	// in functions/function.gwolle_gb_isspam_akismet.php
	return gwolle_gb_akismet_entry_check( $c );
}


