<?php
  if (!function_exists('gwolle_gb_check_entry')) {
    /**
     * gwolle_gb_check_entry
     * Checks an entry.
     * Returns:
     * - $entry   normalized entry data if no errors were found
     * - FALSE    if any errors where found.
     */
    function gwolle_gb_check_entry($args=array()) {
      global $wpdb;
      global $textdomain;
      global $current_user;
      
    	// Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      //  Delete Gwolle-GB session data, if set
      if (isset($_SESSION['gwolle_gb'])) {
        $_SESSION['gwolle_gb'] = array();
      }
      
      $action = (isset($args['action'])) ? $args['action'] : FALSE;
      
      //  Get data for the entry
      $entry = array();
      if (isset($args['entry']) && is_array($args['entry'])) {
        $entry['name']    = (isset($args['entry']['name'])) ? trim($args['entry']['name']) : '';
        $entry['email']   = (isset($args['entry']['email'])) ? trim($args['entry']['email']) : '';
        $entry['origin']  = (isset($args['entry']['origin'])) ? trim($args['entry']['origin']) : '';
        $entry['content'] = (isset($args['entry']['content'])) ? trim($args['entry']['content']) : '';
        $entry['website'] = (isset($args['entry']['website'])) ? trim($args['entry']['website']) : '';
      }
      elseif (isset($_POST)) {
        $entry['name']    = (isset($_POST['entry_author_name'])) ? trim($_POST['entry_author_name']) : '';
        $entry['email']   = (isset($_POST['entry_author_email'])) ? trim($_POST['entry_author_email']) : '';
        $entry['origin']  = (isset($_POST['entry_author_origin'])) ? trim($_POST['entry_author_origin']) : '';
        $entry['content'] = (isset($_POST['entry_content'])) ? trim($_POST['entry_content']) : '';
        $entry['website'] = (isset($_POST['entry_author_website'])) ? trim($_POST['entry_author_website']) : '';
      }
      else {
        return FALSE;
      }
      
      //  Array for error messages
      $error_messages = array();
      
      //  Array for the fields the errors occur in.
      $error_fields = array();
      
      
      if ($action == 'update') {
        /**
         * If the user comes from the editor and just wants to update
         * an entry the only thing we have to make sure is that
         * the 'content' field is not empty.
         *
         */
        $old_entry = (isset($args['old_entry'])) ? $args['old_entry'] : FALSE;
        $entry['entry_id']  = $old_entry['entry_id'];
        $entry['origin']    = (isset($_POST['entry_author_origin'])) ? trim($_POST['entry_author_origin']) : '';
        $entry['content']   = (isset($_POST['entry_content'])) ? trim($_POST['entry_content']) : '';
        $entry['website']   = (isset($_POST['entry_author_website'])) ? trim($_POST['entry_author_website']) : '';
        
        if (strlen($entry['content']) == 0) {
          $error_messages[] = __('Please write an entry.',$textdomain);
          $error_fields[] = 'content';
        }
        $entry['is_checked'] = (isset($_POST['entry_isChecked'])) ? 1 : 0;
      }
      elseif ($action == 'admin_entry') {
        /**
         * If the user wants to add an admin entry we just have to check
         * if the 'content' field is not empty.
         * We furthermore have to set the authorAdminId
         */
        $entry['origin']  = (isset($_POST['entry_author_origin'])) ? trim($_POST['entry_author_origin']) : '';
        $entry['content'] = (isset($_POST['entry_content'])) ? trim($_POST['entry_content']) : '';
        $entry['website'] = (isset($_POST['entry_author_website'])) ? trim($_POST['entry_author_website']) : '';
        if (strlen($entry['content']) == 0) {
          $error_messages[] = __('Please write an entry.',$textdomain);
          $error_fields[] = 'content';
        }
        $entry['authorAdminId'] = $current_user->ID;
        $entry['name']          = '';
        $entry['is_checked']    = (isset($_POST['entry_isChecked'])) ? 1 : 0;
        $entry['is_spam']       = 0;
      }
      else {
        /**
         * The request comes from the frontpage.
         * A visitor wants to add a new entry.
         * Check everything at the hardest settings.
         */
        $entry['authorAdminId'] = 0;
        $entry['is_checked']    = (isset($gwolle_gb_settings['moderate-entries']) && $gwolle_gb_settings['moderate-entries'] === FALSE) ? 1 : 0;
        $entry['ip']            = $_SERVER['REMOTE_ADDR'];
        $entry['host']          = gethostbyaddr($entry['ip']);
      
        /**
         * Challenge the user's reCaptcha input
         * against the server.
         */
      	if ($gwolle_gb_settings['recaptcha-active'] === TRUE) {
      		if (!function_exists('recaptcha_get_html')) {
      			//	Only include the reCAPTCHA-Lib when not already done, e.g. by another plugin.
      			require_once(WP_PLUGIN_DIR.'/gwolle-gb/frontend/recaptcha/recaptchalib.php');
      		}
      		$privatekey = get_option('recaptcha-private-key');
      		$resp = recaptcha_check_answer(
      		  $privatekey,
      		  $entry['ip'],
      		  $_POST["recaptcha_challenge_field"],
      		  $_POST["recaptcha_response_field"]
      		);
      		if (!$resp->is_valid) {
      		  $error_messages[] = __('The captcha has not been entered correctly.',$textdomain);
      		}
      	}
    	
    	  // Check if the 'name' field is empty
      	if (strlen($entry['name']) == 0) {
          $error_messages[] = __('Please enter a name.',$textdomain);
          $error_fields[] = 'name';
        }
      	
        //  Check if the 'content' field is empty
        if (strlen($entry['content']) == 0) {
          $error_messages[] = __('Please write an entry.',$textdomain);
          $error_fields[] = 'content';
        }
        
        /**
         * Check for double post using all
         * table fields but the date.
         */
        $sql = "
        SELECT
          entry_id
        FROM
          ".$wpdb->prefix."gwolle_gb_entries e
        WHERE
          e.entry_author_name   = '".addslashes($entry['name'])."'
          AND
          e.entry_author_email  = '".addslashes($entry['email'])."'
          AND
          e.entry_author_origin = '".addslashes($entry['origin'])."'
          AND
          e.entry_author_ip     = '".addslashes($entry['ip'])."'
          AND
          e.entry_content       = '".addslashes($entry['content'])."'
        LIMIT 1";
        $result = mysql_query($sql);
        if (mysql_num_rows($result) > 0) {
          //  This is a double post.
          $error_messages[] = __('Double post: An entry with the data you entered has already been saved.',$textdomain);
        }
        
        /**
         * Check for spam using Akismet, if this has been set in the
         * settings dialog of Gwolle-GB and if there's a Wordpress API key defined.
         */
        $entry['is_spam'] = 0;
        $wordpressApiKey = get_option('wordpress_api_key');
        if ($gwolle_gb_settings['akismet-active'] === TRUE && strlen($wordpressApiKey) > 0 && !isset($args['bypass_akismet'])) {
          if (version_compare(phpversion(),'5.0','>=')) {
            include(WP_PLUGIN_DIR.'/gwolle-gb/'.AKISMET_PHP5_CLASS_DIR.'/Akismet.class.php');
            
            $akismet = new Akismet($blogURL, $wordpressApiKey);
            $akismet->setCommentAuthor($entry['name']);
            $akismet->setCommentAuthorEmail($entry['email']);
            $akismet->setCommentAuthorURL($entry['website']);
            $akismet->setCommentContent($entry['content']);
            $akismet->setPermalink(get_bloginfo('wpurl')); //	what's this?
            
            if($akismet->isCommentSpam()) {
              //	Akismet detected spam.
              $entry['is_spam'] = 1;
            }
          }
          elseif (version_compare(phpversion(),'4.0','>=')) {
            //	Use the PHP4 class
            include(WP_PLUGIN_DIR.'/gwolle-gb/'.AKISMET_PHP4_CLASS_DIR.'/Akismet.class.php');
            $comment = array(
              'author'    => $entry['name'],
              'email'     => $entry['email'],
              'website'   => $entry['website'],
              'body'      => $entry['content'],
              'permalink' => get_bloginfo('wpurl')
            ); 
            $akismet = new Akismet(get_bloginfo('wpurl'), $wordpressApiKey, $comment);
            
            if ($akismet->isSpam()) {
              //	Akismet detected spam.
              $entry['is_spam'] = 1;
            }
          }
          if ($entry['is_spam'] === 1) {
            $error_messages[] = __("Your entry has been challenged against an anti-spam server and the result was positive.",$textdomain);
          }
        }
      }
      
      if (count($error_messages) > 0) {
        //  There were errors.
        $_SESSION['gwolle_gb']['error_messages']  = $error_messages;
        $_SESSION['gwolle_gb']['error_fields']    = $error_fields;
        $_SESSION['gwolle_gb']['entry']           = $entry;
        return FALSE;
      }
      else {
        //  No errors; return the normalized entry data.
        return $entry;
      }
    }
  }
?>