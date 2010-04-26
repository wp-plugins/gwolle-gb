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
        return TRUE;
      }
    }
	}
?>