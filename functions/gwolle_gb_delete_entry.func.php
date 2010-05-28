<?php
  if (!function_exists('gwolle_gb_delete_entry')) {
    /**
     * gwolle_gb_delete_entry
     * Deletes an entry.
     * Returns:
     * - FALSE      if any errors occur
     * - TRUE       if no errors occur
     */
    function gwolle_gb_delete_entry($args=array()) {
      global $wpdb;
      global $current_user;
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      //  We need the old entry data as an argument.
      if (!isset($args['entry_id'])) {
        return FALSE;
      }
      $entry_id = (int)$args['entry_id'];
      
      $sql = "
      UPDATE
        ".$wpdb->prefix."gwolle_gb_entries
      SET
        entry_isDeleted   = 1
      WHERE
        entry_id = ".$entry_id."
      LIMIT 1";
      $result = mysql_query($sql);
      if (mysql_affected_rows() == 1) {
        return TRUE;
      }
      return FALSE;
    }
  }
?>