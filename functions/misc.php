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
	$input = preg_replace('/"/', '&quot;', $input);
	$input = preg_replace('/\'/', '&#39;', $input);
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
	// Still wanting this encoded
	$output = preg_replace('/"/', '&quot;', $output);
	$output = preg_replace('/\'/', '&#39;', $output);
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
	$value = str_replace('&quot;','\"', $value);
	$value = str_replace('&#039;', '\'', $value);
	$value = str_replace('&#39;', '\'', $value);
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
		$excerpt = '<i style="color:red;">' . __('No content to display. This entry is empty.', 'gwolle-gb') . '</i>';
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

	$author_name = gwolle_gb_sanitize_output( trim( $entry->get_author_name() ) );

	// Registered User gets italic font-style
	$author_id = $entry->get_author_id();
	$is_moderator = gwolle_gb_is_moderator( $author_id );
	if ( $is_moderator ) {
		$author_name_html = '<i>' . $author_name . '</i>';
	} else {
		$author_name_html = $author_name;
	}

	// Link the author website if set in options
	if ( get_option('gwolle_gb-linkAuthorWebsite', 'true') === 'true' ) {
		$author_website = trim( $entry->get_author_website() );
		if ($author_website) {
			$pattern = '/^http/';
			if ( !preg_match($pattern, $author_website, $matches) ) {
				$author_website = "http://" . $author_website;
			}
			$author_name_html = '<a href="' . $author_website . '" target="_blank"
				title="' . __( 'Visit the website of', 'gwolle-gb' ) . ' ' . $author_name . ': ' . $author_website . '">' . $author_name_html . '</a>';
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
 * Get all the users with capability 'moderate_comments'.
 *
 * Return: Array with User objects.
 */
function gwolle_gb_get_moderators() {

	$users_query = new WP_User_Query( array(
		'fields'  => 'all',
		'orderby' => 'display_name'
		) );
	$users = $users_query->get_results();

	$moderators = array();

	if ( is_array($users) && !empty($users) ) {
		foreach ( $users as $user_info ) {

			if ($user_info === FALSE) {
				// Invalid $user_id
				continue;
			}

			// No capability
			if ( !user_can( $user_info, 'moderate_comments' ) ) {
				continue;
			}

			$moderators[] = $user_info;
		}
	}

	return $moderators;
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

	$provided = array('form', 'read');
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
					'form_bbcode_enabled'     => 'false',
					'form_antispam_enabled'   => 'false',
					'form_recaptcha_enabled'  => 'false'
					);
				$setting = get_option( 'gwolle_gb-form', Array() );
				if ( is_string( $setting ) ) {
					$setting = maybe_unserialize( $setting );
				}
				if ( is_array($setting) && !empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;
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
				if ( is_array($setting) && !empty($setting) ) {
					$setting = array_merge( $defaults, $setting );
					return $setting;
				}
				return $defaults;
				break;
			default:
				return false;
				break;
		}
	}
	return false;
}


/*
 * Uses intermittent meta_key to determine the permalink. See hooks.php
 * return (int) postid if found, else 0.
 */
function gwolle_gb_get_postid() {

	$the_query = new WP_Query( array(
		'post_type' => 'any',
		'ignore_sticky_posts' => true,
		'meta_query' => array(
			array(
				'key' => 'gwolle_gb_read',
				'value' => 'true',
			),
		)
	));
	if ( $the_query->have_posts() ) {
		while ( $the_query->have_posts() ) : $the_query->the_post();
			$postid = get_the_ID();
			return $postid;
			break; // only one postid is needed.
		endwhile;
		wp_reset_postdata();
	}
	return 0;

}


/*
 * Clear the cache of the most common Cache plugins.
 */
function gwolle_gb_clear_cache() {

	/* Cachify */
	if ( class_exists('Cachify') ) {
		$cachify = new Cachify();
		if ( method_exists($cachify, 'flush_total_cache') ) {
			$cachify->flush_total_cache(true);
		}
	}

	/* W3 Total Cache */
	if ( function_exists('w3tc_pgcache_flush') ) {
		w3tc_pgcache_flush();
	}

	/* WP Fastest Cache */
	if ( class_exists('WpFastestCache') ) {
		$WpFastestCache = new WpFastestCache();
		if ( method_exists($WpFastestCache, 'deleteCache') ) {
			$WpFastestCache->deleteCache();
		}
	}

	/* WP Super Cache */
	if ( function_exists('wp_cache_clear_cache') ) {
		$GLOBALS["super_cache_enabled"] = 1;
		wp_cache_clear_cache();
	}

}


/*
 * Delete author_id (and maybe checkedby) after deletion of user.
 * Will trim down db requests, because non-existent user do not get cached.
 */
function gwolle_gb_deleted_user( $user_id ) {
	$entries = gwolle_gb_get_entries(array(
		'author_id' => $user_id,
		'num_entries' => -1
	));
	if ( is_array( $entries ) && !empty( $entries ) ) {
		foreach ( $entries as $entry ) {
			// method will take care of it...
			$save = $entry->save();
		}
	}
}
add_action( 'deleted_user', 'gwolle_gb_deleted_user' );


/*
 * Taken from wp-admin/includes/template.php touch_time()
 * Adapted for simplicity.
 */
function gwolle_gb_touch_time( $entry ) {
	global $wp_locale;

	$date = $entry->get_datetime();
	if ( !$date ) {
		$date = current_time('timestamp');
	}

	$dd = date( 'd', $date );
	$mm = date( 'm', $date );
	$yy = date( 'Y', $date );
	$hh = date( 'H', $date );
	$mn = date( 'i', $date );

	// Day
	echo '<label><span class="screen-reader-text">' . __( 'Day', 'gwolle-gb' ) . '</span><input type="text" id="dd" name="dd" value="' . $dd . '" size="2" maxlength="2" autocomplete="off" /></label>';

	// Month
	echo '<label for="mm"><span class="screen-reader-text">' . __( 'Month', 'gwolle-gb' ) . '</span><select id="mm" name="mm">\n';
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		echo "\t\t\t" . '<option value="' . $monthnum . '" ' . selected( $monthnum, $mm, false ) . '>';
		/* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
		echo sprintf( __( '%1$s-%2$s', 'gwolle-gb' ), $monthnum, $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) ) . "</option>\n";
	}
	echo '</select></label>';

	// Year
	echo '<label for="yy"><span class="screen-reader-text">' . __( 'Year', 'gwolle-gb' ) . '</span><input type="text" id="yy" name="yy" value="' . $yy . '" size="4" maxlength="4" autocomplete="off" /></label>';
	echo '<br />';
	// Hour
	echo '<label for="hh"><span class="screen-reader-text">' . __( 'Hour', 'gwolle-gb' ) . '</span><input type="text" id="hh" name="hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" /></label>:';
	// Minute
	echo '<label for="mn"><span class="screen-reader-text">' . __( 'Minute', 'gwolle-gb' ) . '</span><input type="text" id="mn" name="mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" /></label>';
	?>

	<div class="gwolle_gb_timestamp">
		<!-- Clicking OK will place a timestamp here. -->
		<input type="hidden" id="gwolle_gb_timestamp" name="gwolle_gb_timestamp" value="" />
	</div>

	<p>
		<a href="#" class="gwolle_gb_save_timestamp hide-if-no-js button" title="<?php _e('Save the date and time', 'gwolle-gb'); ?>">
			<?php _e('Save Date', 'gwolle-gb'); ?>
		</a>
		<a href="#" class="gwolle_gb_cancel_timestamp hide-if-no-js button-cancel" title="<?php _e('Cancel saving date and time', 'gwolle-gb'); ?>">
			<?php _e('Cancel', 'gwolle-gb'); ?>
		</a>
	</p>
	<?php
}
