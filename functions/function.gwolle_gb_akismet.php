<?php
/**
 * Akismet Function
 * Akismet API: http://akismet.com/development/api/
 * Copied and edited from Contact Form 7
 */


/*
 * $params: object $gwolle_gb_entry with a guestbook entry to be checked
 *          should be an instance of the gwolle_gb_entry class
 * Return: - true if the entry is considered spam by akismet
 *         - false if no spam, or no akismet functionality is found
 */

function gwolle_gb_isspam_akismet( $entry ) {

	$akismet_active = get_option( 'gwolle_gb-akismet-active', false );
	if ( !$akismet_active ) {
		// Akismet is not active, so we don't do anything
		return false;
	}


	if ( is_callable( array( 'Akismet', 'get_api_key' ) ) ) { // Akismet v3.0+
		$api_key = (bool) Akismet::get_api_key();
	} else if ( function_exists( 'akismet_get_key' ) ) {
		$api_key = (bool) akismet_get_key();
	}


	if ( !$api_key ) {
		// No api key, no glory
		return false;
	}

	if ( !is_object( $entry ) ) {
		// No object, no fuss
		return false;
	}


	$comment = array();

	$comment['comment_author'] = $entry->get_author_name();

	$comment['comment_author_email'] = $entry->get_author_email();

	$comment['comment_author_origin'] = $entry->get_author_origin();

	$comment['comment_author_url'] = $entry->get_author_website();

	$comment['comment_content'] = $entry->get_content();

	$comment['blog'] = get_option( 'home' );
	$comment['blog_lang'] = get_locale();
	$comment['blog_charset'] = get_option( 'blog_charset' );
	$comment['user_ip'] = preg_replace( '/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR'] );
	$comment['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
	if ( isset($_SERVER['HTTP_REFERER']) ) {
		$comment['referrer'] = $_SERVER['HTTP_REFERER'];
	}

	// http://blog.akismet.com/2012/06/19/pro-tip-tell-us-your-comment_type/
	$comment['comment_type'] = 'comment';

	$permalink = get_permalink( get_the_ID() );
	if ( $permalink ) {
		$comment['permalink'] = $permalink;
	}

	$ignore = array( 'HTTP_COOKIE', 'HTTP_COOKIE2', 'PHP_AUTH_PW' );
	foreach ( $_SERVER as $key => $value ) {
		if ( ! in_array( $key, (array) $ignore ) )
			$comment["$key"] = $value;
	}


	// Do the real check against Akismet
	return gwolle_gb_akismet_entry_check( $comment );
}


/*
 * Check the $comment against Akismet service
 *
 * Parameters:
 * $comment: Array with the comment
 *
 * Return: true or false
 */

function gwolle_gb_akismet_entry_check( $comment ) {
	global $akismet_api_host, $akismet_api_port;

	$query_string = '';

	foreach ( $comment as $key => $data ) {
		$query_string .= $key . '=' . urlencode( wp_unslash( (string) $data ) ) . '&';
	}

	if ( is_callable( array( 'Akismet', 'http_post' ) ) ) {
		// Akismet v3.0+
		$response = Akismet::http_post( $query_string, 'comment-check' );
	} else {
		$response = akismet_http_post( $query_string, $akismet_api_host, '/1.1/comment-check', $akismet_api_port );
	}

	if ( 'true' == $response[1] ) {
		return true;
	} else {
		return false;
	}

}


/*
 * Submit the entry as Spam to Akismet Service
 */

function gwolle_gb_akismet_submit_spam( $entry ) {
	// FIXME: Stub
}


/*
 * Submit the entry as Ham to Akismet Service
 */

function gwolle_gb_akismet_submit_ham( $entry ) {
	// FIXME: Stub
}



