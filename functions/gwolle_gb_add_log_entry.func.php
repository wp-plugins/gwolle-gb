<?php
  /**
   * gwolle_gb_add_log_entry
   * Adds a new log entry
   */
  if (!function_exists('gwolle_gb_add_log_entry')) {
    function gwolle_gb_add_log_entry($args) {
      global $wpdb;
      global $current_user;
      
      if (!isset($args['subject']) || !isset($args['subject_id']) || (int)$args['subject_id'] === 0) {
        return FALSE;
      }
      
      $sql = "
			INSERT
			INTO
				" . $wpdb->prefix . "gwolle_gb_log
			(
				log_subject,
				log_subjectId,
				log_authorId,
				log_date
			) VALUES (
				'".addslashes($args['subject'])."',
				".(int)$args['subject_id'].",
				".(int)$current_user->data->ID.",
				'".mktime()."'
			)";
			$result = mysql_query($sql);
			if (mysql_affected_rows() == 1) {
				return TRUE;
			}
			return FALSE;
    }
  }
?>