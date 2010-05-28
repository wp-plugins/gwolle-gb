<?php
  /**
   * gwolle_gb_get_page_nav
   * Calculates the page navigation.
   * Is used at the frontend as well as at the backend.
   * Returns:
   * An Array with the nav entries.
   */
  if (!function_exists('gwolle_gb_get_page_nav')) {
    function gwolle_gb_get_page_nav($args) {
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
    }
  }
?>