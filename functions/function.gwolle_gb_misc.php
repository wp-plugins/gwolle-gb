<?php


// Function to format a form value for an input field (strip '<' etc.)
function gwolle_gb_output_to_input_field($value) {
	$value = stripslashes($value);
	$value = html_entity_decode($value);
	$value = htmlspecialchars($value);
	return $value;
}


// Function to format entry values for output
function gwolle_gb_format_value_for_output($value) {
	$value = html_entity_decode($value);
	$value = stripslashes($value);
	$value = htmlspecialchars($value);
	return $value;
}


/*
 * Function to format values for beeing send by mail.
 * Since users can input malicious code we have to make
 * sure that this code is being taken care of.
 */
function gwolle_gb_format_values_for_mail($value) {
	$value = str_replace('<', '{', $value);
	$value = str_replace('>', '}', $value);
	return $value;
}


/*
 * Function to build the excerpt
 *
 * Args: $content: (string) content of the entry to be shortened
 *       $excerpt_length: (int) the maximum length to return in number of characters
 *
 * Return: $excerpt: (string) the shortened content
 */
function gwolle_gb_get_excerpt($content, $excerpt_length) {
	$excerpt = gwolle_gb_format_value_for_output( substr($content, 0, $excerpt_length ));
	if (strlen( $content ) > $excerpt_length) {
		$excerpt .= '...';
	}
	if (trim($excerpt) == '') {
		$excerpt = '<i style="color:red;">' . __('No content to display. This entry is empty.', GWOLLE_GB_TEXTDOMAIN) . '</i>';
	}
	return $excerpt;
}


/*
 * Get Author name in the right format as html
 *
 * Args: $entry object
 *
 * Return: $author_name_html string with html
 */
function gwolle_gb_get_author_name_html($entry) {

	$author_name = trim( $entry->get_author_name() );
	$author_name_html = trim( $entry->get_author_name() );

	// Registered User;
	$authoradminid = $entry->get_authoradminid();
	$is_moderator = gwolle_gb_is_moderator( $authoradminid );
	if ( $is_moderator ) {
		$author_name = $is_moderator; // overwrite name in entry with name of registered user
		$author_name_html = '<i>' . $is_moderator . '</i>'; // overwrite name in entry with name of registered user
			} else {
		$author_name_html = gwolle_gb_format_value_for_output( $author_name_html );
	}

	// Link the author website?
	if ( get_option('gwolle_gb-linkAuthorWebsite') === 'true' ) {
		$author_website = trim( $entry->get_author_website() );
		if ($author_website) {
			$pattern = '/^http/';
			if ( !preg_match($pattern, $author_website, $matches) ) {
				$author_website = "http://" . $author_website;
			}
			$author_name_html = '<a href="' . $author_website . '" target="_blank" title="' . $author_name . '">' . $author_name_html . '</a>';
		}
	}
	return $author_name_html;
}


/*
 * Is User alowed to manage comments
 *
 * Args: $user_id
 *
 * Return:
 * - user_nicename or user_login if allowed
 * - false if not allowed
 */
function gwolle_gb_is_moderator($user_id) {

	if ( $user_id > 0 ) {
		if ( function_exists('user_can') && user_can( $user_id, 'moderate_comments' ) ) {
			// Only moderators
			$userdata = get_userdata( $user_id );
			if (is_object($userdata)) {
				if ( isset( $userdata->display_name ) ) {
					return $userdata->display_name;
				} else {
					return $userdata->user_login;
				}
			}
		}
	}
	return false;
}




