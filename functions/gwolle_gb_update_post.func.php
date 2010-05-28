<?php
  /*
  if (!function_exists('gwolle_gb_update_post')) {
    function gwolle_gb_update_post($args=array()) {
      global $wpdb;
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      if (!isset($args['entry_id']) || (int)$args['entry_id'] === 0) {
        return FALSE;
      }
      else {
        //  Check if this entry exists
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entries.func.php');
        $old_entry = gwolle_gb_get_entries(array(
          'entry_id'  => $args['entry_id']
        ));
        if ($old_entry === FALSE) {
          //  Entry does not exist
          return FALSE;
        }
      }
      
        if (isset($args['bypass_recaptcha'])) {
          $arguments['bypass_recaptcha'] = $args['bypass_recaptcha'];
        }
        if (isset($args['bypass_akismet'])) {
          $arguments['bypass_akismet'] = $args['bypass_akismet'];
        }
        if (isset($args['entry'])) {
          $arguments['entry'] = $args['entry'];
        }
        //  Normalize and check post data
        $entry = gwolle_gb_check_entry($arguments);
        if ($entry === FALSE) {
          //  Check failed
          return FALSE;
        }
        else {
          //  Check was successful
          $sql = "
          UPDATE
            ".$wpdb->prefix."gwolle_gb_entries e
          SET
            e.entry_author_name = '".addslashes($entry['name'])."',
            e.entry_author_name = '".addslashes($entry['name'])."',
            e.entry_author_name = '".addslashes($entry['name'])."',
            e.entry_author_name = '".addslashes($entry['name'])."',
           
        }
      }
            
      
      
      return FALSE;
    }
  }
  */
?>