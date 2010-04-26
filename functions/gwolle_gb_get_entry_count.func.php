<?php
  if (!function_exists('gwolle_gb_get_entry_count')) {
    /**
     * gwolle_gb_get_entry_count
     * Get the number of entries.
     * Parameters:
     * $args  Array with arguments
     */
    function gwolle_gb_get_entry_count($args) {
      global $wpdb;
      if (!isset($args['entry_status']) || !in_array($args['entry_status'], array(
        'all',
        'unchecked',
        'checked',
        'spam'
      ))) {
        return FALSE;
      }
      
      $where = "1 = 1";
      
      switch($args['entry_status']) {
        case 'unchecked':
          $where .= "
          AND
          entry_isChecked = 0";
          break;
        case 'checked':
          $where .= "
          AND
          entry_isChecked = 1";
          break;
        case 'spam':
          $where .= "
          AND
          entry_isSpam = 1";
          break;
      }
      
      $sql = "
      SELECT
        COUNT(entry_id) AS entry_count
      FROM
        ".$wpdb->prefix."gwolle_gb_entries e
      WHERE
        ".$where."
        AND
        e.entry_isDeleted = 0";
      $result = mysql_query($sql);
      $data = mysql_fetch_array($result, MYSQL_ASSOC);
      return (int)$data['entry_count'];
    }
  }
?>