<?php
  /**
   * gwolle_gb_check_entry
   * Checks/unchecks an entry
   */
  if (!function_exists('gwolle_gb_check_entry')) {
    function gwolle_gb_check_entry($args=array()) {
      global $wpdb;
      global $current_user;
//echo "Args: "; var_dump($args);

      if (!isset($args['entry_id']) || (int)$args['entry_id'] === 0) {
        return FALSE;
      }

      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }

      $entry_isChecked = (isset($args['uncheck']) && $args['uncheck'] === TRUE) ? 0 : 1;

      $sql = "
      UPDATE
        ".$wpdb->gwolle_gb_entries."
      SET
        entry_isChecked = ".$entry_isChecked.",
        entry_checkedBy = ".(int)$current_user->data->ID."
      WHERE
        entry_id = ".(int)$args['entry_id']."
      LIMIT 1";
      $result = $wpdb->query($sql);
      if ($result == 1) {
        //  Write log entry
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_add_log_entry.func.php');
        $log = array();
        $log['subject']     = ($entry_isChecked === 1) ? 'entry-checked' : 'entry-unchecked';
        $log['subject_id']  = (int)$args['entry_id'];
        gwolle_gb_add_log_entry($log);
        return TRUE;
      }
      return FALSE;
    }
  }