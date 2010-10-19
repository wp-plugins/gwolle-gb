<?php
  /**
   * gwolle_gb_mark_spam
   * Marks an entry as spam/no spam
   */
  if (!function_exists('gwolle_gb_mark_spam')) {
    function gwolle_gb_mark_spam($args=array()) {
      global $wpdb;
      global $current_user;
      
      //  Check permission
      if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
  			//	The current user's not allowed to do this.
  			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&msg=no-permission');
  			exit;
  		}
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      //  Is Askismet activated for the Gwolle-GB plugin?
      if ($gwolle_gb_settings['akismet-active'] === FALSE) {
        $_SESSION['gwolle_gb']['msg'] = 'akismet-not-activated';
        header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php');
        exit;
      }
      
      //  Is the WordPress API key present?
      $wordpress_api_key = get_option('wordpress_api_key');
      if (!$wordpress_api_key) {
        $_SESSION['gwolle_gb']['msg'] = 'check-akismet-configuration';
        header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php');
        exit;
      }
      
      if (!isset($args['entry_id']) || (int)$args['entry_id'] === 0) {
        return FALSE;
      }
      //  Check if the entry exists
      include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
      $entry = gwolle_gb_get_entries(array(
        'entry_id'  => $args['entry_id']
      ));
      if ($entry === FALSE) {
        return FALSE;
      }
      
      //  Generate the Akismet object
      if (version_compare(phpversion(),'5.0','>=')) {	//	Use the PHP5 class			
				if (!class_exists('Akismet')) {
					//	Only include if the Akismet class doesn't already exist.
					include(AKISMET_PHP5_CLASS_DIR.'/Akismet.class.php');
				}
 				
 				$akismet = new Akismet(get_bloginfo('wpurl'), $wordpress_api_key);
 				$akismet->setCommentAuthor($entry['entry_author_name']);
 				$akismet->setCommentAuthorEmail($entry['entry_author_email']);
 				$akismet->setCommentAuthorURL($entry['entry_author_website']);
 				$akismet->setCommentContent($entry['entry_content']);
 				$akismet->setPermalink(get_bloginfo('wpurl'));
			}
			elseif (version_compare(phpversion(),'4.0','>=')) {	//	Use the PHP4 class
				if (!class_exists('Akismet')) {
					//	Only include if the Akismet class doesn't already exist.
					include(AKISMET_PHP4_CLASS_DIR.'/Akismet.class.php');
				}
				$comment = array(
					'author'     => $entry['entry_author_name'],
					'email'      => $entry['entry_author_email'],
					'website'    => $entry['entry_author_website'],
					'body'       => $entry['entry_content'],
					'permalink'  => get_bloginfo('wpurl')
				); 
				$akismet = new Akismet(get_bloginfo('wpurl'), $wordpress_api_key, $comment);
			}
			
			// Include the function to generate a log entry
			include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_add_log_entry.func.php');
			
			// What shall we do with the entry?
			if (isset($args['no_spam']) && $args['no_spam'] === TRUE) {
        //  This is no spam.
        if ($akismet) {
          $akismet->submitHam();
        }
        $sql = "
        UPDATE
          ".$wpdb->gwolle_gb_entries."
        SET
          entry_isSpam = 0
        WHERE
          entry_id = ".(int)$args['entry_id']."
        LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_affected_rows() == 1) {
          gwolle_gb_add_log_entry(array(
            'subject'     => 'marked-as-not-spam',
            'subject_id'  => $args['entry_id']
          ));
          return TRUE;
        }
      }
      else {
        //  This is spam
        if ($akismet) {
          $akismet->submitSpam();
        }
        $sql = "
        UPDATE
          ".$wpdb->gwolle_gb_entries."
        SET
          entry_isSpam = 1
        WHERE
          entry_id = ".(int)$args['entry_id']."
        LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_affected_rows() == 1) {
          gwolle_gb_add_log_entry(array(
            'subject'     => 'marked-as-spam',
            'subject_id'  => $args['entry_id']
          ));
          return TRUE;
        }
      }
      //  Everything else fails and returns FALSE.
      return FALSE;
    }
  }