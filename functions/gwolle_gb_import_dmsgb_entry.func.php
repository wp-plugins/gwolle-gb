<?php
  /**
   * gwolle_gb_import_dmsgb_entry
   * Function to import an entry from DMSGuestbook
   */
  if (!function_exists('gwolle_gb_import_dmsgb_entry')) {
    function gwolle_gb_import_dmsgb_entry($entry) {
      global $wpdb;
      global $current_user;
      get_currentuserinfo();
      
      
      $isChecked = ($entry['flag'] == 1) ? 0 : 1;
      $isSpam = ($entry['spam'] == 1) ? 1 : 0;
    
      //  Insert into Gwolle-DB entry table
      mysql_query("
      INSERT
      INTO
        ".$wpdb->prefix."gwolle_gb_entries
      (
        entry_author_name,
        entry_author_email,
        entry_author_website,
        entry_author_ip,
        entry_content,
        entry_date,
        entry_isChecked,
        entry_isSpam
      ) VALUES (
        '".mysql_real_escape_string(stripslashes($entry['name']))."',
        '".mysql_real_escape_string(stripslashes($entry['email']))."',
        '".mysql_real_escape_string(stripslashes($entry['url']))."',
        '".$entry['ip']."',
        '".mysql_real_escape_string(strip_tags(stripslashes($entry['message'])))."',
        '".$entry['date']."',
        ".$isChecked.",
        ".$isSpam."
      )");
      
      //  Create a log item for the import
      mysql_query("
      INSERT
      INTO
        ".$wpdb->prefix."gwolle_gb_log
      (
        log_subject,
        log_subjectId,
        log_authorId,
        log_date
      ) VALUES (
        'imported-from-dmsguestbook',
        ".mysql_insert_id().",
        ".$current_user->ID.",
        '".mktime()."'
      )
      ");
    }
  }
?>