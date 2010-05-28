<?php
  if (!function_exists('gwolle_gb_get_settings')) {
    /**
     * gwolle_gb_get_settings
     * Gets the Gwolle-GB settings from database
     * and stores them in a global variable.
     * Return:
     * - TRUE, if settings were loaded successfully
     * - FALSE, if loading settings failed
     */
    function gwolle_gb_get_settings($args=array()) {
      global $gwolle_gb_settings;
      global $wpdb;
      global $textdomain;
      
      $sql = "
      SELECT
        REPLACE(o.option_name, 'gwolle_gb-', '') AS name,
        o.option_value AS value
      FROM
        ".$wpdb->options." o
      WHERE
        o.option_name LIKE 'gwolle_gb-%'
      ORDER BY
        o.option_name";
      $result = mysql_query($sql);
      if (mysql_num_rows($result) == 0) {
        return FALSE;
      }
      else {
        while($option = mysql_fetch_array($result, MYSQL_ASSOC)) {
          if (in_array($option['value'], array('true', 'false'))) {
            $value = ($option['value'] == 'true') ? TRUE : FALSE;
          }
          else {
            $value = $option['value'];
          }
          $gwolle_gb_settings[$option['name']] = $value;
        }
        
        /**
         *  Now add some hard coded settings.
         */
        //  Entries per page (backend)
        $gwolle_gb_settings['entries_per_page'] = 15;
        //  Default mail text
        $gwolle_gb_settings['defaultMailText'] = __("Hello,\n\nthere is a new guestbook entry at '%blog_name%'.\nYou can check it at %entry_management_url%.\n\nHave a nice day!\nYour Gwolle-GB-Mailer",$textdomain);
        
        if (defined('GWOLLE_GB_ACCESS_LEVEL') === FALSE) {
          define('GWOLLE_GB_ACCESS_LEVEL', $gwolle_gb_settings['access-level']);
        }
        
        return TRUE;
      }
    }
	}
?>