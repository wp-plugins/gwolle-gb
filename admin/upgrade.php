<?php
	/*
	**	This file is used during activation or upgrading from a prior version.
	**	
	**	It uses the PHP function version_compare(). Usage:
	**	version_compare(OLD_VERSION_NUMBER,NEW_VERSION_NUMBER,'<')
	**	=> TRUE, if the old version number is smaller than the new one.
	*/
	
	
	function install_gwolle_gb() {
		global $wpdb;
		
		//	Install the table for the entries
		$wpdb->query("
			CREATE TABLE " . $wpdb->prefix . "gwolle_gb_entries (
			  entry_id int(10) NOT NULL auto_increment,
			  entry_author_name text NOT NULL,
			  entry_authorAdminId int(5) NOT NULL default '0',
			  entry_author_email text NOT NULL,
			  entry_author_origin text NOT NULL,
			  entry_author_website text NOT NULL,
			  entry_author_ip text NOT NULL,
			  entry_author_host text NOT NULL,
			  entry_content longtext NOT NULL,
			  entry_date varchar(10) NOT NULL,
			  entry_isChecked tinyint(1) NOT NULL,
			  entry_checkedBy int(5) NOT NULL,
			  entry_isDeleted varchar(1) NOT NULL default '0',
  			entry_isSpam varchar(1) NOT NULL default '0',
			  PRIMARY KEY  (entry_id)
			) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
		");
		
		//	Install the table for the log
		$wpdb->query("
			CREATE TABLE " . $wpdb->prefix . "gwolle_gb_log (
			  log_id int(8) NOT NULL auto_increment,
			  log_subject text NOT NULL,
			  log_subjectId int(5) NOT NULL,
			  log_authorId int(5) NOT NULL,
			  log_date varchar(12) NOT NULL,
			  PRIMARY KEY  (log_id)
			) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;
		");
		
		//	Add reCAPTCHA option
		add_option('gwolle_gb-recaptcha-active','false');
		add_option('recaptcha-public-key','');
		add_option('recaptcha-private-key','');
		
		//	Add Akismet option
		add_option('gwolle_gb-akismet-active','false');
		
		//	Add access level option
		add_option('gwolle_gb-access-level','10');
		
		//	Add moderation option
		add_option('gwolle_gb-moderate-entries','true');
		
		//	Add icon option
		add_option('gwolle_gb-showEntryIcons','true');
		
		//	Add option for the admin mail content (can then be defined by the user)
		add_option('gwolle_gb-adminMailContent','');
		
		//	Add entries per page option
		add_option('gwolle_gb-entriesPerPage','20');
		
		//	Add option to toggle the visibility of line breaks
		add_option('gwolle_gb-showLineBreaks');
		
		//  Add option to show/hide text before/after [gwolle-gb] tag.
		add_option('gwolle_gb-guestbookOnly','true');
		
		//  Add option to toggle check if there is data to import.
		add_option('gwolle_gb-checkForImport','true');
		
		//  Add option to toggle replacing of smilies with graphics.
		add_option('gwolle_gb-showSmilies','true');
		
		//  Add option to toogle linking of author's website
		add_option('gwolle_gb-linkAuthorWebsite','true');
		
		//  Add option for the post ID of Gwolle-GB
		add_option('gwolle_gb-post_ID', 'false');
		
		//	Save plugin version to database
		add_option('gwolle_gb_version', GWOLLE_GB_VER);
	}
	
	function uninstall_gwolle_gb() {
		//	delete the plugin's tables
		global $wpdb;
		mysql_query("DROP TABLE " . $wpdb->prefix . "gwolle_gb_log");
		mysql_query("DROP TABLE " . $wpdb->prefix . "gwolle_gb_entries");
		
		//	delete the plugin's preferences and version-no in the wp-options table
		mysql_query("
			DELETE
				FROM " . $wpdb->prefix . "options
			WHERE
				option_name LIKE 'gwolle_gb%'
		");
		
		if ($_POST['delete_recaptchaKeys'] == 'on' || (get_option('recaptcha-public-key') == '' && get_option('recaptcha-private-key') == '')) {
			//	also delete the reCAPTCHA-keys
			mysql_query("
				DELETE
					FROM " . $wpdb->prefix . "options
				WHERE
					option_name = 'recaptcha-public-key'
					OR
					option_name = 'recaptcha-private-key'
			");
		}
		
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=successfully-uninstalled');
		exit;
	}
	
	
	function upgrade_gwolle_gb() {
		global $wpdb;
		$installed_ver = get_option('gwolle_gb_version');
		
		if (version_compare($installed_ver,'0.9','<')) {
			/*
			**	0.8 -> 0.9
			**	No changes to the database; just added a few options.
			*/
			add_option('recaptcha-active','false');
			add_option('recaptcha-public-key','');
			add_option('recaptcha-private-key','');
		}
		
		if (version_compare($installed_ver,'0.9.1','<')) {
			/*
			**	0.9 -> 0.9.1
			**	Moved the email notification options to the WP options table.
			*/
			$notifyUser_result = mysql_query("
				SELECT *
				FROM
					" . $wpdb->prefix . "gwolle_gb_settings
				WHERE
					setting_name = 'notify_by_mail'
					AND
					setting_value = '1'
			");
			while ($notifySetting = mysql_fetch_array($notifyUser_result)) {
				//	Add an option for each notification subscriber.
				add_option('gwolle_gb-notifyByMail-' . $notifySetting['user_id'], 'true');
			}
			
			//	Delete the old settings table.
			mysql_query("
				DROP TABLE
					" . $wpdb->prefix . "gwolle_gb_settings
			");
		}
		
		if (version_compare($installed_ver,'0.9.2','<')) {
			/*
			**	0.9.1->0.9.2
			**	Renamed the option for toggling reCAPTCHA so that we can
			**	have different plugins using reCAPTCHA.
			*/
			add_option('gwolle_gb-recaptcha-active',get_option('recaptcha-active'));
			delete_option('recaptcha-active');
		}
		
		if (version_compare($installed_ver,'0.9.3','<')) {
			/*
			**	0.9.2->0.9.3
			**	Added Akismet integration
			**	Add an option row and a new column to the entry table
			**	to be able to mark entries as spam automatically.
			*/
			add_option('gwolle_gb-akismet-active','false');
			mysql_query("
				ALTER
				TABLE " . $wpdb->prefix . "gwolle_gb_entries
				ADD
					entry_isSpam
						VARCHAR( 1 )
						NOT NULL
						DEFAULT '0'
						AFTER entry_isDeleted
			");
		}
		
		if (version_compare($installed_ver,'0.9.4','<')) {
			/*
			**	0.9.3->0.9.4
			**	added access-level, no-mail-on-spam, moderate on/off.
			*/
			add_option('gwolle_gb-access-level','10');
			add_option('gwolle_gb-moderate-entries','true');
			
			$emailNotification_result = mysql_query("
				SELECT *
				FROM
					" . $wpdb->prefix . "options
				WHERE
					option_name LIKE 'gwolle_gb-notifyByMail-%'
			");
			while($notification = mysql_fetch_array($emailNotification_result)) {
				add_option('gwolle_gb-notifyAll-' . str_replace('gwolle_gb-notifyByMail-','',$notification['option_name']), 'true');
			}
		}
		
		if (version_compare($installed_ver,'0.9.4.1','<')) {
			/*
			**	0.9.4->0.9.4.1
			**	Caching the Wordpress API key so that we don't need to
			**	validate it each time the user opens the settings panel.
			**	Also, add an option to show icons in the entry list.
			*/
			add_option('gwolle_gb-wordpress-api-key',get_option('wordpress_api_key'));
			add_option('gwolle_gb-showEntryIcons','true');
		}
		
		if (version_compare($installed_ver,'0.9.4.2','<')) {
			/*
			**	0.9.4.1->0.9.4.2
			**	Added the possibility to specify the content of the mail
			**	a subscriber of the mail notification gets.
			**	Also, added an option to turn the version-check on/off
			**	and the possibility to set the numbers of entries per page.
			*/
			add_option('gwolle_gb-adminMailContent','');
			if (function_exists('file') && get_cfg_var('allow_url_fopen')) { $default = 'true'; } else { $default = 'false'; }
			add_option('gwolle_gb-autoCheckVersion',$default);
			add_option('gwolle_gb-entriesPerPage','20');
		}
		
		if (version_compare($installed_ver,'0.9.4.2.1','<')) {
			/*
			**	0.9.4.2->0.9.4.2.1
			**	Removed the version check because of some problems.
			*/
			delete_option('gwolle_gb-autoCheckVersion');
		}
		
		if (version_compare($installed_ver,'0.9.4.3','<')) {
			/*
			**	0.9.4.2.1->0.9.4.3
			**	Added option to manually set link to the guestbook.
			*/
			add_option('gwolle_gb-guestbookLink');
		}
		
		if (version_compare($installed_ver,'0.9.4.5','<')) {
			/*
			**	0.9.4.2.3->0.9.4.5
			**	Support for Croation chars.
			**	Added option to toggle line breaks-visibility.
			*/
			mysql_query("
				ALTER
				TABLE " . $wpdb->prefix . "gwolle_gb_entries
					DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci
			");
			mysql_query("
				ALTER
				TABLE " . $wpdb->prefix . "gwolle_gb_entries
					CHANGE `entry_id` `entry_id` INT(10) NOT NULL AUTO_INCREMENT,
					CHANGE `entry_author_name` `entry_author_name` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_authorAdminId` `entry_authorAdminId` INT(5) NOT NULL DEFAULT '0',
					CHANGE `entry_author_email` `entry_author_email` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_origin` `entry_author_origin` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_website` `entry_author_website` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_ip` `entry_author_ip` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_author_host` `entry_author_host` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_content` `entry_content` LONGTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_date` `entry_date` VARCHAR(10) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
					CHANGE `entry_isChecked` `entry_isChecked` TINYINT(1) NOT NULL,
					CHANGE `entry_checkedBy` `entry_checkedBy` INT(5) NOT NULL,
					CHANGE `entry_isDeleted` `entry_isDeleted` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
					CHANGE `entry_isSpam` `entry_isSpam` VARCHAR(1) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0'
			");
			add_option('gwolle_gb-showLineBreaks','false');
		}
		
		if (version_compare($installed_ver,'0.9.4.6','<')) {
		  /*
		  **  0.9.4.5->0.9.4.6
		  **  Added option to show/hide text before/after [gwolle-gb] tag.
		  */
		  add_option('gwolle_gb-guestbookOnly','true');
		}
		
		if (version_compare($installed_ver,'0.9.5','<')) {
		  /*
		  **  0.9.4.6->0.9.5
		  **  Added option to toggle check for import data.
		  */
		  add_option('gwolle_gb-checkForImport','true');
		}
		
		if (version_compare($installed_ver,'0.9.6','<')) {
		  /**
		   * 0.9.5->0.9.6
		   * Added the following options:
		   * - toggle replacing of smilies
		   * - toogle link to author's website
		   */
		  add_option('gwolle_gb-showSmilies','true');
		  add_option('gwolle_gb-linkAuthorWebsite','true');
		}
		
		if (version_compare($installed_ver, '0.9.7', '<')) {
		  /**
		   * 0.9.6->0.9.7
		   * Replace the guestbook link with an ID
		   */
		  //  Get post where the [gwolle-gb] tag is set
	    $sql = "
	    SELECT
	      p.ID
	    FROM
	      ".$wpdb->posts." p
	    WHERE
	      p.post_content LIKE '%[gwolle-gb]%'
	      AND
	      p.post_status = 'publish'
	    LIMIT 1";
	    $result = mysql_query($sql);
	    if (mysql_num_rows($result) == 0) {
	      //  No post found.
	      add_option('gwolle_gb-post_ID', 'false');
	    }
	    else {
	      //  Guestbook post found
	      $data = mysql_fetch_array($result, MYSQL_ASSOC);
	      add_option('gwolle_gb-post_ID', $data['ID']);
	    }
	    delete_option('gwolle_gb-guestbookLink');
    }
		
		//	Update the plugin version option
		update_option('gwolle_gb_version', GWOLLE_GB_VER);
	}
?>