<?php
  /**
   * gwolle_gb_get_log_entries
   * Function to get log entries.
   */

  if (!function_exists('gwolle_gb_get_log_entries')) {
    function gwolle_gb_get_log_entries($args) {
      global $wpdb;
      global $current_user;

      if (!isset($args['subject_id']) || (int)$args['subject_id'] === 0) {
        return FALSE;
      }

      //  Message to strings
      $log_messages = array(
        'entry-unchecked'             => __('Entry has been locked.',GWOLLE_GB_TEXTDOMAIN),
        'entry-checked'               => __('Entry has been unlocked.',GWOLLE_GB_TEXTDOMAIN),
        'marked-as-spam'              => __('Entry marked as spam.',GWOLLE_GB_TEXTDOMAIN),
        'marked-as-not-spam'          => __('Entry marked as not-spam.',GWOLLE_GB_TEXTDOMAIN),
        'entry-edited'                => __('Entry has been edited.',GWOLLE_GB_TEXTDOMAIN),
        'imported-from-dmsguestbook'  => __('Imported from DMSGuestbook',GWOLLE_GB_TEXTDOMAIN),
        'entry-trashed'               => __('Entry has been trashed.',GWOLLE_GB_TEXTDOMAIN),
        'entry-untrashed'             => __('Entry has been untrashed.',GWOLLE_GB_TEXTDOMAIN)
      );

      $sql = "
      SELECT
        l.log_id AS id,
        l.log_subject AS subject,
        l.log_authorId AS author_id,
        l.log_date
      FROM
        ".$wpdb->gwolle_gb_log." l
      WHERE
        l.log_subjectId = ".(int)$args['subject_id']."
      ORDER BY
        l.log_date ASC";
      $result = $wpdb->query($sql);
      if ($wpdb->num_rows == 0) {
        return FALSE;
      }
      else {
        //  Array to store the log entry authors
        $userdata = array();

        //  Array to store the log entries
        $log_entries = array();

        //  Process entries
        while ($entry = $wpdb->get_results($sql, ARRAY_A)) {
          $log_entry = array(
            'id'        => (int)$entry['id'],
            'subject'   => stripslashes($entry['subject']),
            'author_id' => (int)$entry['author_id'],
            'log_date'  => stripslashes($entry['log_date'])
          );

          $log_entry['msg']       = (isset($log_messages[$log_entry['subject']])) ? $log_messages[$log_entry['subject']] : $log_entry['subject'];

          //  Get author's login name if not already done.
          if (!isset($userdata[$log_entry['author_id']])) {
            $userdata[$log_entry['author_id']] = get_userdata($log_entry['author_id']);
            if (!is_object($userdata)) {
              $log_entry['author_login'] = '<i>'.__('unknown',GWOLLE_GB_TEXTDOMAIN).'</i>';
            }
            else {
              $log_entry['author_login'] = $userdata[$log_entry['author_id']]->user_login;
            }
          }

          //  Construct the message in HTML
          $log_entry['msg_html']  = date('d.m.Y', $log_entry['log_date']).': '.$log_entry['msg'];
          if ($log_entry['author_id'] == $current_user->data->ID) {
            $log_entry['msg_html'] .= ' (<strong>'.__('You',GWOLLE_GB_TEXTDOMAIN).'</strong>)';
          }
          else {
            $log_entry['msg_html'] .= ' ('.$log_entry['author_login'].')';
          }

          array_push($log_entries, $log_entry);
        }
        return $log_entries;
      }
      return FALSE;
    }
  }
?>