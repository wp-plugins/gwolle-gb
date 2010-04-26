<?php
  /**
   * ajax.php
   * Processes AJAX requests.
   */
  
  //  Set charset to UTF-8
  header("Content-Type: text/html; charset=utf-8");
  
  include('../../../../wp-load.php');
  
  // Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  //	Access level
	define('GWOLLE_GB_ACCESS_LEVEL', $gwolle_gb_settings['access-level']);
	
	if (!function_exists('set_entry_checked_state')) {
    function set_entry_checked_state() {
      global $wpdb;
      global $current_user;
      if (!isset($_POST['id']) || !isset($_POST['new_state'])) {
        return FALSE;
      }
      $entry_id = (int)$_POST['id'];
      if ($entry_id === 0) {
        return FALSE;
      }
      $entry_is_checked = ($_POST['new_state'] == 'checked') ? '1' : '0';
      $sql = "
      UPDATE
        ".$wpdb->prefix."gwolle_gb_entries
      SET
        entry_isChecked = ".$entry_is_checked."
      WHERE
        entry_id = ".$entry_id."
      LIMIT 1";
      $result = mysql_query($sql);
      if (mysql_affected_rows() == 1) {
        //  Write a log entry
        $log_subject = ($_POST['new_state'] == 'checked') ? 'entry-checked' : 'entry-unchecked';
        $log_sql = "
        INSERT
        INTO
          ".$wpdb->prefix."gwolle_gb_log
        (
          log_subject,
          log_subjectId,
          log_authorId,
          log_date
        ) VALUES (
          '".$log_subject."',
          ".$entry_id.",
          ".$current_user->data->ID.",
          '".mktime()."'
        )";
        $log_result = mysql_query($log_sql);
        return TRUE;
      }
      return FALSE;
    }
  }
	
	global $current_user;
	if (is_user_logged_in() && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
    if (!isset($_POST['func'])) {
      exit;
    }
    $function = $_POST['func'];
    switch($function) {
      case 'set_entry_checked_state':
        if (set_entry_checked_state() === TRUE) {
          echo 'success';
        }
        else {
          echo 'failure';
        }
        break;
    }
  }
?>