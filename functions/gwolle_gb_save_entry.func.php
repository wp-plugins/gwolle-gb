<?php
  if (!function_exists('gwolle_gb_save_entry')) {
    /**
     * gwolle_gb_save_entry
     * Saves an entry to the database.
     * Returns:
     * - FALSE      if any error occurs
     * - $entry_id  of the new entry
     */
    function gwolle_gb_save_entry($args=array()) {
      global $wpdb;
      global $current_user;
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      $action = (isset($args['action'])) ? $args['action'] : FALSE;
      
      //  Check entry
      include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_check_entry.func.php');
      $check_args = (isset($args) && count($args) > 0) ? $args : array();
      $entry = gwolle_gb_check_entry($check_args);
      if ($entry === FALSE) {
        //  There are errors in this entry.
        return FALSE;
      }
      else {
        
        //  No errors. $entry contains the normalized entry data.
        $sql = "
        INSERT
        INTO
          ".$wpdb->prefix."gwolle_gb_entries
        (
          entry_author_name,
          entry_authorAdminId,
          entry_author_email,
          entry_author_origin,
          entry_author_website,
          entry_author_ip,
          entry_author_host,
          entry_content,
          entry_date,
          entry_isSpam,
          entry_isChecked
        ) VALUES (
          '".addslashes($entry['name'])."',
          ".$entry['authorAdminId'].",
          '".addslashes($entry['email'])."',
          '".addslashes($entry['origin'])."',
          '".addslashes($entry['website'])."',
          '".addslashes($entry['ip'])."',
          '".addslashes($entry['host'])."',
          '".addslashes($entry['content'])."',
          '".mktime()."',
          ".$entry['is_checked'].",
          ".$entry['is_spam']."
        )";
        $result = mysql_query($sql);
        if (mysql_affected_rows() > 0) {  // Entry saved successfully.
          $entry['id'] = mysql_insert_id();
          if ($action === FALSE) {
            //  Send the notification mail(s)
            $recipients_sql = "
  					SELECT
  					  o.option_name,
  					  o.option_value
  					FROM
  						".$wpdb->prefix."options o
  					WHERE
  						o.option_name LIKE 'gwolle_gb-notifyByMail-%'";
            $recipients_result = mysql_query($recipients_sql);
            while ($recipient = mysql_fetch_array($recipients_result, MYSQL_ASSOC)) {
  						$userdata = get_userdata(str_replace('gwolle_gb-notifyByMail-','',$recipient['option_name']));
  						if (($entry['is_spam'] === 1 && (isset($gwolle_gb_settings['notifyAll-'.$userdata->ID]) && $gwolle_gb_settings['notifyAll-'.$userdata->ID] === TRUE)) || $entry['is_spam'] === 0) {
  							$subscriber[]['user_email'] = $userdata->user_email;
  						}
  					}
  					
  					@ini_set('sendmail_from', get_bloginfo('admin_mail'));
  				
  					//	Set the mail content
  					$mailTags = array('user_email','entry_management_url','blog_name','blog_url','wp_admin_url');
  					$mail_body = stripslashes($gwolle_gb_settings['adminMailContent']);
  					if (!$mail_body) {
              $mail_body = $gwolle_gb_settings['defaultMailText'];
            }
  					
  					$subject = '['.get_bloginfo('name').'] '.__('New guestbook entry', $textdomain);
  					$header = "";
  					$header .= "From: Gwolle-Gb-Mailer <".get_bloginfo('admin_email').">\r\n";
  					$header .= "Content-Type: text/plain; charset=UTF-8\r\n";  //  Encoding of the mail
  					
  					$info['blog_name'] = get_bloginfo('name');
  					$info['blog_url'] = get_bloginfo('wpurl');
  					$info['wp_admin_url'] = $info['blog_url'] . '/wp-admin';
  					$info['entry_management_url'] = $info['wp_admin_url'] . '/admin.php?page=gwolle-gb/editor.php&entry_id='.$entry['id'];
  					//	The last tags are bloginfo-based
  					for ($tagNum=1; $tagNum<count($mailTags); $tagNum++) {
  						$mail_body = str_replace('%' . $mailTags[$tagNum] . '%',$info[$mailTags[$tagNum]], $mail_body);
  					}
  					
  					if (!function_exists('gwolle_gb_formatValuesForMail')) {
              /**
               * Function to format values for beeing send by mail.
               * Since users can input malicious code we have to make
               * sure that this code is beeing taken care of.
               */
              function gwolle_gb_formatValuesForMail($value) {
                $value = str_replace('<','{',$value);
                $value = str_replace('>','}',$value);
                return $value;
              }
            }
  					
  					for ($i=0; $i<count($subscriber); $i++) {
  						$mailBody[$i] = $mail_body;
  						$mailBody[$i] = str_replace('%user_email%', $subscriber[$i]['user_email'], $mailBody[$i]);
  						$mailBody[$i] = str_replace('%entry_content%',gwolle_gb_formatValuesForMail($entry['content']),$mailBody[$i]);
  						
  						wp_mail($subscriber[$i]['user_email'], $subject, $mailBody[$i], $header);
  					}
  				}
  				return $entry['id'];
        }
      }
      return FALSE;
    }
  }
?>