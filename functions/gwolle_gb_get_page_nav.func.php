<?php
/*
 * gwolle_gb_get_page_nav
 * Calculates the page navigation.
 * Is used at the frontend as well as at the backend.
 * Returns:
 * An Array with the nav entries.
 */


// exit;
// KILL this file

if (!function_exists('gwolle_gb_get_page_nav')) {
	function gwolle_gb_get_page_nav($args) {
		// Load settings, if not set
		global $gwolle_gb_settings;
		if (!isset($gwolle_gb_settings)) {
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_settings.func.php');
			gwolle_gb_get_settings();
		}
	}

}
?>