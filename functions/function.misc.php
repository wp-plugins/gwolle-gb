<?php


/*
 * Function to sanitize values from input fields for the database.
 * $input: string
 */
function gwolle_gb_sanitize_input($input) {
	$input = strval($input);
	$input = trim($input);
	$input = strip_tags($input);
	$input = stripslashes($input); // Make sure we're not just adding lots of slashes.
	$input = htmlspecialchars($input, ENT_COMPAT, 'UTF-8');
	$input = addslashes($input);
	return $input;
}


/*
 * Function to sanitize values for output in a form or div.
 * $input: string
 */
function gwolle_gb_sanitize_output($output) {
	$output = strval($output);
	$output = trim($output);
	$output = strip_tags($output);
	$output = stripslashes($output);
	$output = html_entity_decode($output, ENT_COMPAT, 'UTF-8'); // the opposite of htmlentities, for backwards compat
	$output = htmlspecialchars_decode($output, ENT_COMPAT);
	return $output;
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
 *       $excerpt_length: (int) the maximum length to return in number of words (uses wp_trim_words)
 *
 * Return: $excerpt: (string) the shortened content
 */
function gwolle_gb_get_excerpt( $content, $excerpt_length = 20 ) {
	$excerpt = wp_trim_words( $content, $excerpt_length, '...' );
	$excerpt = gwolle_gb_sanitize_output( $excerpt );
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
	$author_id = $entry->get_author_id();
	$is_moderator = gwolle_gb_is_moderator( $author_id );
	if ( $is_moderator ) {
		$author_name = $is_moderator; // overwrite name in entry with name of registered user
		$author_name_html = '<i>' . $is_moderator . '</i>'; // overwrite name in entry with name of registered user
	} else {
		$author_name_html = gwolle_gb_sanitize_output( $author_name_html );
	}

	// Link the author website?
	if ( get_option('gwolle_gb-linkAuthorWebsite', 'true') === 'true' ) {
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


/*
 * Get the setting for Gwolle-GB that is saved as serialized data.
 *
 * Args: $request, string with value 'form' or 'read'.
 *
 * Return:
 * - Array with settings for that request.
 * - or false if no setting.
 */
function gwolle_gb_get_setting($request) {

	$provided = array('form', 'read', 'widget');
	if ( in_array( $request, $provided ) ) {
		switch ( $request ) {
			case 'form':
				$defaults = Array(
					'form_name_enabled'       => 'true',
					'form_name_mandatory'     => 'true',
					'form_city_enabled'       => 'true',
					'form_city_mandatory'     => 'false',
					'form_email_enabled'      => 'true',
					'form_email_mandatory'    => 'true',
					'form_homepage_enabled'   => 'true',
					'form_homepage_mandatory' => 'false',
					'form_message_enabled'    => 'true',
					'form_message_mandatory'  => 'true',
					'form_antispam_enabled'   => 'false',
					'form_recaptcha_enabled'  => 'false'
					);
				$setting = get_option( 'gwolle_gb-form', Array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				$setting = array_merge( $defaults, $setting );
				return $setting;
				break;
			case 'read':
				if ( get_option('show_avatars') ) {
					$avatar = 'true';
				} else {
					$avatar = 'false';
				}

				$defaults = Array(
					'read_avatar'   => $avatar,
					'read_name'     => 'true',
					'read_city'     => 'true',
					'read_datetime' => 'true',
					'read_date'     => 'false',
					'read_content'  => 'true',
					'read_editlink' => 'true'
					);
				$setting = get_option( 'gwolle_gb-read', Array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				$setting = array_merge( $defaults, $setting );
				return $setting;
				break;
			default:
				return false;
				break;
		}
	}
	return false;
}


