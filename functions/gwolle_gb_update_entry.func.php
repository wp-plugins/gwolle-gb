<?php
  if (!function_exists('gwolle_gb_update_entry')) {
    /**
     * gwolle_gb_update_entry
     * Updates an entry.
     * Returns:
     * - FALSE      if any errors occur
     * - TRUE       if no errors occur
     */
    function gwolle_gb_update_entry($args=array()) {
      global $wpdb;
      global $current_user;
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      //  We need the old entry data as an argument.
      if (!isset($args['old_entry'])) {
        return FALSE;
      }
      
      //  Check entry
      include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_check_entry.func.php');
      $entry = gwolle_gb_check_entry(array(
        'action'    => 'update',
        'old_entry' => $args['old_entry']
      ));
      if ($entry === FALSE) {
        //  There are errors in this entry.
        return FALSE;
      }
      else {
        //  No errors. $entry contains the normalized entry data.
        $sql = "
        UPDATE
          ".$wpdb->prefix."gwolle_gb_entries
        SET
          entry_author_origin   = '".addslashes($entry['origin'])."',
          entry_author_website  = '".addslashes($entry['website'])."',
          entry_content         = '".addslashes($entry['content'])."',
          entry_isChecked       = ".(int)$entry['is_checked']."
        WHERE
          entry_id = ".$entry['entry_id']."
        LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_affected_rows() == -1) {
          return FALSE;
        }
        else {  // Entry saved successfully.
  				return $entry['entry_id'];
        }
      }
      return FALSE;
    }
  }
?>