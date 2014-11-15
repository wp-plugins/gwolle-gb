<?php
	/*
	 * Save new entries to the database, when valid.
	 * Obligatory fields:
	 * - name
	 * - entry
	 * ... and a negative Akismet result (= no spam) and a correct captcha; both only when turned on in the settings panel.
	 */

	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }

	global $wpdb;

	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
		gwolle_gb_get_settings();
	}

	$entry_id = gwolle_gb_save_entry();

	// Get links to guestbook page
	$gb_links = gwolle_gb_get_link(array( 'all' => TRUE ));

	if ($entry_id === FALSE) {
		header('Location: '.$gb_links['write']);
		exit;
	} else {
		$msg = __('Thanks for your entry.',GWOLLE_GB_TEXTDOMAIN);
		if ($gwolle_gb_settings['moderate-entries'] === TRUE) {
			$msg .= __('<br>We will review it and unlock it in a short while.',GWOLLE_GB_TEXTDOMAIN);
		}
		$_SESSION['gwolle_gb']['msg'] = $msg;
		header('Location: '.$gb_links['read']);
		exit;
	}
