<?php
  if (!function_exists('gwolle_gb_get_entries')) {
    /**
     * gwolle_gb_get_entries
     * Function to get query the database for guestbook entries.
     * Parameter:
     * - $args  arguments to specify the query
     */
    function gwolle_gb_get_entries($args=array()) {
      global $wpdb;
      global $textdomain;
      
      include(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_format_value_for_output.func.php');
      
      // Load settings, if not set
    	global $gwolle_gb_settings;
    	if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      $num_entries = (isset($args['num_entries']) && (int)$args['num_entries'] > 0) ? (int)$args['num_entries'] : $gwolle_gb_settings['entries_per_page'];
      
      $excerpt_length = (isset($args['excerpt_length']) && (int)$args['excerpt_length'] > 0) ? (int)$args['excerpt_length'] : 100;
      
      $where = " 1 = 1";
      
      if (isset($args['show'])) {
        switch($args['show']) {
          case 'checked':
            $where .= "
              AND
              e.entry_isChecked = 1";
            break;
          case 'unchecked':
            $where .= "
              AND
              e.entry_isChecked != 1";
            break;
          case 'spam':
            $where .= "
              AND
              e.entry_isSpam = 1";
            break;
        }
      }
      
      if (!isset($args['show_deleted'])) {
        $where .= "
          AND
          e.entry_isDeleted = 0";
      }
      if (isset($args['offset']) && (int)$args['offset'] > 0) {
        $limit = $args['offset'].", ".$num_entries;
      }
      else {
        $limit = "0, ".$num_entries;
      }
      
      if (isset($args['entry_id'])) {
        if ((int)$args['entry_id'] > 0) {
          $where .= "
            AND
            e.entry_id = ".(int)$args['entry_id'];
        }
        else {
          return FALSE;
        }
      }
      
      $sql = "
      SELECT
        e.entry_id,
        e.entry_author_name,
        e.entry_authorAdminId,
        e.entry_author_email,
        e.entry_author_origin,
        e.entry_author_website,
        e.entry_author_ip,
        e.entry_author_host,
        e.entry_content,
        e.entry_date,
        e.entry_isChecked,
        e.entry_checkedBy,
        e.entry_isDeleted,
        e.entry_isSpam
      FROM
        ".$wpdb->prefix."gwolle_gb_entries e
      WHERE
        ".$where."
      ORDER BY
        e.entry_date DESC
      LIMIT
        ".$limit;
      $result = mysql_query($sql);
      if (mysql_num_rows($result) == 0) {
        return FALSE;
      }
      else {
        $entries = array();
        $staff_member_names = array();
        $blogurl = get_bloginfo('wpurl');
        while ($data = mysql_fetch_array($result, MYSQL_ASSOC)) {
          $entry = array(
            'entry_id'  => (int)$data['entry_id'],
            'entry_author_name'     => stripslashes($data['entry_author_name']),
            'entry_authorAdminId'   => (int)$data['entry_authorAdminId'],
            'entry_author_email'    => stripslashes($data['entry_author_email']),
            'entry_author_origin'   => stripslashes($data['entry_author_origin']),
            'entry_author_website'  => stripslashes($data['entry_author_website']),
            'entry_author_ip'       => $data['entry_author_ip'],
            'entry_author_host'     => $data['entry_author_host'],
            'entry_content'         => stripslashes($data['entry_content']),
            'entry_date'            => $data['entry_date'],
            'entry_isChecked'       => (int)$data['entry_isChecked'],
            'entry_checkedBy'       => (int)$data['entry_checkedBy'],
            'entry_isDeleted'       => (int)$data['entry_isDeleted'],
            'entry_isSpam'          => (int)$data['entry_isSpam'],
            'entry_date_html'       => date('d.m.Y', $data['entry_date'])
          );
          
          //  Build excerpt
          $entry['excerpt'] = gwolle_gb_format_value_for_output(substr($entry['entry_content'],0,$excerpt_length));
		      if (strlen($entry['entry_content']) > $excerpt_length) {
		        $entry['excerpt'] .= '...';
		      }
		      
		      //  Get staff member's name if necessary
		      if ($entry['entry_authorAdminId'] > 0) {
						//	Dies ist ein Admin-Eintrag; hole den Benutzernamen, falls nicht geschehen.
						if (!isset($staff_member_names[$entry['entry_authorAdminId']])) {
							$userdata = get_userdata($entry['entry_authorAdminId']);
							if (!is_object($userdata)) {
                $staff_member_names[$entry['entry_authorAdminId']] = '<i>'.__('unknown').'</i>';
              }
              else {
                $staff_member_names[$entry['entry_authorAdminId']] = $userdata->user_login;
              }
						}
						if ($staff_member_names[$entry['entry_authorAdminId']] == '') {
						  $staff_member_names[$entry['entry_authorAdminId']] = '<strong>'.__('User not found',$textdomain).'</strong>';
						}
						$entry['entry_author_name_html'] = '<i>' . $staff_member_names[$entry['entry_authorAdminId']] . '</i>';
					}
					else {
						$entry['entry_author_name_html'] = gwolle_gb_format_value_for_output($entry['entry_author_name']);
					}
					
					// Link the author's website?
					if ($gwolle_gb_settings['linkAuthorWebsite'] === TRUE) {
            $website_url = $entry['entry_author_website'];
            if (strlen(str_replace('http://','',trim($website_url))) > 0) {
              if (strpos($website_url, 'http://') === FALSE || strpos($website_url, 'http://') !== 0) {
                $website_url = 'http://'.$website_url;
              }
              $entry['entry_author_name_html'] = '<a href="'.$website_url.'" target="_blank">'.$entry['entry_author_name_html'].'</a>';
            }
					}
					
					// Set the entry class (used for the icon)
					$entry['icon_class'] = ($entry['entry_isChecked'] === 1) ? 'checked' : 'unchecked';
					
					// Set spam icon if this entry is marked as spam
					$entry['spam_icon'] = ($entry['entry_isSpam'] === 1) ? '<img class="spam" alt="[spam]" src="'.$blogurl.'/wp-content/plugins/gwolle-gb/admin/gfx/entry-spam.jpg" />' : '';
		      
		      //  Add entry to the array of all entries
          array_push($entries, $entry);
        }
        if (isset($args['entry_id'])) {
          //  Just return one entry
          return $entries[0];
        }
        else {
          return $entries;
        }
      }
      return FALSE;
    }
  }
?>