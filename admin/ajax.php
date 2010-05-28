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
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  //	Access level
  if (defined('GWOLLE_GB_ACCESS_LEVEL') === FALSE) {
    define('GWOLLE_GB_ACCESS_LEVEL', $gwolle_gb_settings['access-level']);
  }
	
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
      $entry_is_checked = ($_POST['new_state'] == 'checked') ? 1 : 0;
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
        //	Write a log entry on this.
				include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_add_log_entry.func.php');
				$log_subject = ($entry_is_checked === 0) ? 'entry-unchecked' : 'entry-checked';
				return gwolle_gb_add_log_entry(array(
				  'subject'     => $log_subject,
				  'subject_id'  => $entry_id
				));
        return TRUE;
      }
      return FALSE;
    }
  }
  
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/get_gwolle_gb_post_id.func.php');
	
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
      case 'search_gwolle_gb_post_ID':
        $gwolle_gb_post_id = get_gwolle_gb_post_id();
        if ($gwolle_gb_post_id === FALSE) {
          echo 'failure';
        }
        else {
          echo $gwolle_gb_post_id;
        }
        break;
    }
  }
?>