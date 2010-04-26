<?php
  /**
   * gwolle_gb_get_log_entries
   * Function to get log entries.
   */
  
  if (!function_exists('gwolle_gb_get_log_entries')) {
    function gwolle_gb_get_log_entries($args) {
      global $wpdb;
      global $textdomain;
      global $current_user;
      
      if (!isset($args['subject_id']) || (int)$args['subject_id'] === 0) {
        return FALSE;
      }
      
      //  Message to strings
      $log_messages = array(
        'entry-unchecked'             => __('Entry has been locked.',$textdomain),
        'entry-checked'               => __('Entry has been unlocked.',$textdomain),
        'marked-as-spam'              => __('Entry marked as spam.',$textdomain),
        'marked-as-not-spam'          => __('Entry marked as not-spam.',$textdomain),
        'entry-edited'                => __('Entry has been edited.',$textdomain),
        'imported-from-dmsguestbook'  => __('Imported from DMSGuestbook',$textdomain)
      );
      
      $sql = "
      SELECT
        l.log_id AS id,
        l.log_subject AS subject,
        l.log_authorId AS author_id,
        l.log_date
      FROM
        ".$wpdb->prefix."gwolle_gb_log l
      WHERE
        l.log_subjectId = ".(int)$args['subject_id']."
      ORDER BY
        l.log_date ASC";
      $result = mysql_query($sql);
      if (mysql_num_rows($result) == 0) {
        return FALSE;
      }
      else {
        //  Array to store the log entry authors
        $userdata = array();
        
        //  Array to store the log entries
        $log_entries = array();
        
        //  Process entries
        while ($entry = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $log_entry = array(
            'id'        => (int)$entry['id'],
            'subject'   => stripslashes($entry['subject']),
            'author_id' => (int)$entry['author_id'],
            'log_date'  => stripslashes($entry['log_date'])
          );
          
          $log_entry['msg']       = (isset($log_messages[$log_entry['subject']])) ? $log_messages[$log_entry['subject']] : '?';
          
          //  Get author's login name if not already done.
          if (!isset($userdata[$log_entry['author_id']])) {
            $userdata[$log_entry['author_id']] = get_userdata($log_entry['author_id']);
            if (!is_object($userdata)) {
              $log_entry['author_login'] = '<i>'.__('unknown',$textdomain).'</i>';
            }
            else {
              $log_entry['author_login'] = $userdata[$log_entry['author_id']]->user_login;
            }
          }
          
          //  Construct the message in HTML
          $log_entry['msg_html']  = date('d.m.Y', $log_entry['log_date']).': '.$log_entry['msg'];
          if ($log_entry['author_id'] == $current_user->data->ID) {
            $log_entry['msg_html'] .= ' (<strong>'.__('You',$textdomain).'</strong>)';
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