<?php

if (!function_exists('gwolle_gb_get_settings')) {
	/*
	 * gwolle_gb_get_settings
	 * Gets the Gwolle-GB settings from database and stores them in a global variable.
	 *
	 * $args is not being used anymore
	 * Return: true (always)
	 */

	function gwolle_gb_get_settings($args = array()) {
		global $gwolle_gb_settings;
		global $wpdb;


		// FIXME, set useful defaults, also in settingspage
		// FIXME, let all code use just the WP options through get_options.
		// FIXME, set these options on activation in the install function
		// Just the list of options
		$default = "";
		$gwolle_gb_settings['adminMailContent']	= get_option( 'gwolle_gb-adminMailContent', $default );
		$default = __("Hello,\n\nthere is a new guestbook entry at '%blog_name%'.\nYou can check it at %entry_management_url%.\n\nHave a nice day!\nYour Gwolle-GB-Mailer", GWOLLE_GB_TEXTDOMAIN);
		$gwolle_gb_settings['defaultMailText']	= get_option( 'gwolle_gb-defaultMailText', $default );
		$gwolle_gb_settings['akismet-active']	= get_option( 'gwolle_gb-akismet-active', false );
		$gwolle_gb_settings['checkForImport']	= get_option( 'gwolle_gb-checkForImport' );		// false
		$gwolle_gb_settings['entriesPerPage']	= get_option( 'gwolle_gb-entriesPerPage', 15 );	// Frontend option
		$gwolle_gb_settings['guestbookOnly']	= get_option( 'gwolle_gb-guestbookOnly' );		// false
		$gwolle_gb_settings['linkAuthorWebsite']= get_option( 'gwolle_gb-linkAuthorWebsite' );	// false
		$gwolle_gb_settings['moderate-entries']	= get_option( 'gwolle_gb-moderate-entries' );	// false
		$gwolle_gb_settings['notifyByMail-1']	= get_option( 'gwolle_gb-notifyByMail-1' );		// true
		$gwolle_gb_settings['post_ID']			= get_option( 'gwolle_gb-post_ID' );			// 0
		$gwolle_gb_settings['recaptcha-active']	= get_option( 'gwolle_gb-recaptcha-active' );	// false
		$gwolle_gb_settings['showLineBreaks']	= get_option( 'gwolle_gb-showLineBreaks' );		// false
		$gwolle_gb_settings['showSmilies']		= get_option( 'gwolle_gb-showSmilies', false );
		$gwolle_gb_settings['checkForImport']	= get_option( 'gwolle_gb-checkForImport', false );
		$gwolle_gb_settings['showEntryIcons']	= get_option( 'gwolle_gb-showEntryIcons', false );
		$gwolle_gb_settings['entries_per_page']	= get_option( 'gwolle_gb-entries_per_page', 15 );

		return TRUE;
	}

}
?>