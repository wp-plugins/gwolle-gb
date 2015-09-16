<?php
/*
 * File: gwolle_gb-entry.php
 * Template with function: gwolle_gb_entry_template()
 *
 * By default this file will be loaded from /wp-content/plugins/gwolle-gb-frontend/gwolle_gb-entry.php.
 * If you place it in your childtheme or parenttheme, it will be overridden.
 * Make sure you only return values, and not to use echo statements.
 *
 *
 * $args: $entry, instance of gwolle_gb_entry.
 *        $first, boolean with true if it is the first entry.
 *        $counter,int with the number of the entry. (since 1.4.7)
 *
 * return: string, html with a single guestbook entry.
 */


if ( !function_exists('gwolle_gb_entry_template') ) {
	function gwolle_gb_entry_template( $entry, $first, $counter ) {

		$html5 = current_theme_supports( 'html5' );

		// Get the needed settings.
		$form_setting = gwolle_gb_get_setting( 'form' );
		$read_setting = gwolle_gb_get_setting( 'read' );

		// Main Author div
		$entry_output = '<div class="';
		$entry_output .= ' gb-entry';
		$entry_output .= ' gb-entry_' . $entry->get_id();
		$entry_output .= ' gb-entry-count_' . $counter;
		if ( is_int( $counter / 2 ) ) {
			$entry_output .= ' gwolle_gb_even';
		} else {
			$entry_output .= ' gwolle_gb_uneven';
		}
		if ( $first == true ) {
			$entry_output .= ' gwolle_gb_first';
		}

		if ( get_option( 'gwolle_gb-admin_style', 'true' ) === 'true' ) {
			$author_id = $entry->get_author_id();
			$is_moderator = gwolle_gb_is_moderator( $author_id );
			if ( $is_moderator ) {
				$entry_output .= ' admin-entry';
			}
		}
		$entry_output .= '">';

		if ( $html5 ) {
			$entry_output .= '<article>';
		}

		// Use this filter to just add something
		$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_before', '', $entry );

		// Author Info
		$entry_output .= '<div class="gb-author-info">';

		// Author Avatar
		if ( isset($read_setting['read_avatar']) && $read_setting['read_avatar']  === 'true' ) {
			$avatar = get_avatar( $entry->get_author_email(), 32, '', $entry->get_author_name() );
			if ($avatar) {
				$entry_output .= '<span class="gb-author-avatar">' . $avatar . '</span>';
			}
		}

		// Author Name
		if ( isset($read_setting['read_name']) && $read_setting['read_name']  === 'true' ) {
			$author_name_html = gwolle_gb_get_author_name_html($entry);
			$entry_output .= '<span class="gb-author-name">' . $author_name_html . '</span>';
		}

		// Author Origin
		if ( isset($read_setting['read_city']) && $read_setting['read_city']  === 'true' ) {
			$origin = $entry->get_author_origin();
			if ( strlen(str_replace(' ', '', $origin)) > 0 ) {
				$entry_output .= '<span class="gb-author-origin"> ' . __('from', 'gwolle-gb') . ' ' . gwolle_gb_sanitize_output($origin) . '</span>';
			}
		}

		// Entry Date and Time
		if ( ( isset($read_setting['read_datetime']) && $read_setting['read_datetime']  === 'true' ) || ( isset($read_setting['read_date']) && $read_setting['read_date']  === 'true' ) ) {
			$entry_output .= '<span class="gb-datetime">
						<span class="gb-date"> ';
			if ( isset($read_setting['read_name']) && $read_setting['read_name']  === 'true' ) {
				$entry_output .= __('wrote on', 'gwolle-gb') . ' ';
			}
			$entry_output .= date_i18n( get_option('date_format'), $entry->get_datetime() ) . '</span>';
			if ( isset($read_setting['read_datetime']) && $read_setting['read_datetime']  === 'true' ) {
				$entry_output .= '<span class="gb-time"> ' . __('on', 'gwolle-gb') . ' ' . trim(date_i18n( get_option('time_format'), $entry->get_datetime() )) . '</span>';
			}
			$entry_output .= ':</span> ';
		}

		$entry_output .= '</div>'; // <div class="gb-author-info">

		// Main Content
		if ( isset($read_setting['read_content']) && $read_setting['read_content']  === 'true' ) {
			$entry_output .= '<div class="gb-entry-content">';
			$entry_content = gwolle_gb_sanitize_output( $entry->get_content() );
			if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
				$entry_content = convert_smilies($entry_content);
			}
			if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
				$entry_content = nl2br($entry_content);
			}
			$excerpt_length = (int) get_option( 'gwolle_gb-excerpt_length', 0 );
			if ( $excerpt_length > 0 ) {
				$entry_content = wp_trim_words( $entry_content, $excerpt_length, '...' );
				// FIXME: add readmore link
			}
			if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
				$entry_content = gwolle_gb_bbcode_parse($entry_content);
			} else {
				$entry_content = gwolle_gb_bbcode_strip($entry_content);
			}
			$entry_output .= $entry_content;


			// Edit Link for Moderators
			if ( function_exists('current_user_can') && current_user_can('moderate_comments') ) {
				$entry_output .= '
					<a class="gwolle_gb_edit_link" href="' . admin_url('admin.php?page=' . GWOLLE_GB_FOLDER . '/editor.php&amp;entry_id=' . $entry->get_id() ) . '" title="' . __('Edit entry', 'gwolle-gb') . '">' . __('Edit', 'gwolle-gb') . '</a>';
			}

			// Use this filter to just add something
			$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_content', '', $entry );

			$entry_output .= '</div>
			';

			/* Admin Reply */
			$admin_reply_content = gwolle_gb_sanitize_output( $entry->get_admin_reply() );
			if ( $admin_reply_content != '' ) {

				$class = '';
				if ( get_option( 'gwolle_gb-admin_style', 'true' ) === 'true' ) {
					$class = ' admin-entry';
				}

				$admin_reply = '<div class="gb-entry-admin_reply' . $class . '">';

				/* Admin Reply Author */
				$admin_reply .= '<div class="gb-admin_reply_uid">';
				$admin_reply_name = gwolle_gb_is_moderator( $entry->get_admin_reply_uid() );
				if ( isset($read_setting['read_name']) && $read_setting['read_name']  === 'true' && $admin_reply_name ) {
					$admin_reply .= '<strong>' . __('Admin Reply by:', 'gwolle-gb') . '</strong>
						' . $admin_reply_name;
				} else {
					$admin_reply .= '<strong>' . __('Admin Reply:', 'gwolle-gb') . '</strong>';
				}
				$admin_reply .= '</div> ';

				/* Admin Reply Content */
				if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
					$admin_reply_content = convert_smilies($admin_reply_content);
				}
				if ( get_option( 'gwolle_gb-showLineBreaks', 'false' ) === 'true' ) {
					$admin_reply_content = nl2br($admin_reply_content);
				}
				if ( $excerpt_length > 0 ) {
					$admin_reply_content = wp_trim_words( $admin_reply_content, $excerpt_length, '...' );
				}
				if ( isset($form_setting['form_bbcode_enabled']) && $form_setting['form_bbcode_enabled']  === 'true' ) {
					$admin_reply_content = gwolle_gb_bbcode_parse($admin_reply_content);
				} else {
					$admin_reply_content = gwolle_gb_bbcode_strip($admin_reply_content);
				}
				$admin_reply .= '<div class="gb-admin_reply_content">
					' . $admin_reply_content . '
					</div>';

				$admin_reply .= '</div>';

				$entry_output .= $admin_reply;
			}
		}

		// Use this filter to just add something
		$entry_output .= apply_filters( 'gwolle_gb_entry_read_add_after', '', $entry );

		if ( $html5 ) {
			$entry_output .= '</article>';
		}

		$entry_output .= '</div>
			';

		return $entry_output;
	}
}


