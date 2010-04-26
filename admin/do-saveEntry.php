<?php
	/*
	**	Applies changes to guestbook entries
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $current_user;
	global $wpdb;
	
	if (!current_user_can('level_'.GWOLLE_GB_ACCESS_LEVEL)) {
		//	The current user's not allowed to do this
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
		exit;
	}
	else {
		//	The current user's allowed; proceed with saving the changes to the database.
		$entry_id = (isset($_REQUEST['entry_id']) && (int)$_REQUEST['entry_id'] > 0) ? (int)$_REQUEST['entry_id'] : FALSE;
		if ($entry_id !== FALSE) {
		  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_entries.func.php');
		  $entry = gwolle_gb_get_entries(array(
		    'entry_id' => $entry_id
		  ));
			if ($entry === FALSE) {
				//	Entry does not exist; redirect to entry list and prompt with an error message.
				header('Location: ' . get_bloginfo('wpurl')  . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=entry-not-found');
				exit;
			}
			else {
        $entry_isChecked = (isset($_POST['entry_isChecked'])) ? 1 : 0;
		    include_once(WP_PLUGIN_DIR.'/gwolle-gb/admin/check_entry.func.php');
				if ($entry_isChecked === 1 && $entry['entry_isChecked'] === 0) {
					//	User wants this entry to be checked.
					$changedCheckedStatus = check_gwolle_gb_entry($entry_id);
				}
				elseif ($entry_isChecked === 0 && $entry['entry_isChecked'] === 1) {
					//	User wants to uncheck this entry.
					$changedCheckedStatus = check_gwolle_gb_entry($entry_id, 'unchecked');
				}
				
				//  Process data
				$entry_content        = trim($_POST['entry_content']);
				$entry_author_origin  = trim($_POST['entry_author_origin']);
				$entry_author_website = trim($_POST['entry_author_website']);
				
				//  Only update if there is any data that has been changed
				if (
				  $entry['entry_content']         != $entry_content
				  ||
				  $entry['entry_author_origin']   != $entry_author_origin
				  ||
				  $entry['entry_author_website']  != $entry_author_website
				) {
  				$update_result = mysql_query("
  					UPDATE
  						" . $wpdb->prefix . "gwolle_gb_entries e
  					SET
  						e.entry_content = '" . mysql_real_escape_string($_POST['entry_content']) . "',
  						e.entry_author_origin = '" . mysql_real_escape_string($_POST['entry_author_origin']) . "',
  						e.entry_author_website = '" . mysql_real_escape_string($_POST['entry_author_website']) . "'
  					WHERE
  						e.entry_id = ".$entry_id."
  					LIMIT 1
  				");
  				if (mysql_affected_rows() === 1) {
  				  //	Write a log entry on this.
    				include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_add_log_entry.func.php');
    				gwolle_gb_add_log_entry(array(
    				  'subject'     => 'entry-edited',
    				  'subject_id'  => $entry_id
    				));
    				$msg = 'updated=true';
    		  }
    		  else {
    		    $msg = 'updated=true';
    		  }
    		}
  		  elseif ($changedCheckedStatus === TRUE) {
  		    $msg = 'updated=true';
  		  }
  		  else {
				  $msg = 'updated=false';
				}
				header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id='.$_REQUEST['entry_id'].'&'.$msg);
				exit;
			}
		}
		else {
			//	No entry_id has been submitted; this is a new admin entry.
			if ($_POST['entry_isChecked'] == 'on') {
				$isChecked = '1';
				$checkedBy = $current_user->data->ID;
			}
			else {
				$isChecked = 0;
				$checkedBy = 0;
			}
			
			$save_result = mysql_query("
				INSERT
				INTO
					" . $wpdb->prefix . "gwolle_gb_entries e
				(
					e.entry_authorAdminId,
					e.entry_author_website,
					e.entry_author_origin,
					e.entry_author_ip,
					e.entry_author_host,
					e.entry_content,
					e.entry_date,
					e.entry_isChecked,
					e.entry_checkedBy
				) VALUES (
					".(int)$current_user->data->ID.",
					'".addslashes($_POST['entry_author_website'])."',
					'".addslashes($_POST['entry_author_origin'])."',
					'".$_SERVER['REMOTE_ADDR']."',
					'".gethostbyaddr($_SERVER['REMOTE_ADDR'])."',
					'".addslashes($_POST['entry_content'])."',
					'".mktime()."',
					".$isChecked.",
					".$checkedBy."
				)
			");
			if (mysql_affected_rows() > 0) {
				$msg = 'updated=true';
			}
			else {
				$msg = 'error=true';
			}
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/editor.php&entry_id=' . mysql_insert_id() . '&' . $msg);
		}
	}
?>