<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://www.wolfgangtimme.de/blog/category/gwolle-gb/
Description: Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle-GB and have a real guestbook.
Version: 0.9.8
Author: Wolfgang Timme
Author URI: http://www.wolfgangtimme.de/blog/
*/

/*  Copyright 2009  Wolfgang Timme  (email : gwolle@wolfgangtimme.de)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
  
	//	plugin's version
	define('GWOLLE_GB_VER','0.9.8');
	
	/**
	 * Definitions
	 */
	define('GWOLLE_GB_FOLDER',plugin_basename(dirname(__FILE__)));
	define('GWOLLE_GB_URL',WP_PLUGIN_URL.'/'.GWOLLE_GB_FOLDER);
	define('GWOLLE_GB_DIR',WP_PLUGIN_DIR.'/'.GWOLLE_GB_FOLDER);
	// Textdomain for translation
	define('GWOLLE_GB_TEXTDOMAIN','GWGB');
	
	//	Load translation
	load_plugin_textdomain(GWOLLE_GB_TEXTDOMAIN, false, GWOLLE_GB_FOLDER.'/lang');

	//	Akismet PHP4 class folder's name
	define('AKISMET_PHP4_CLASS_DIR',GWOLLE_GB_DIR.'/Akismet_PHP4');

	//	Akismet PHP5 class folder's name
	define('AKISMET_PHP5_CLASS_DIR',GWOLLE_GB_DIR.'/PHP5Akismet.0.4');
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  //  If the access level for Gwolle-GB is still not defined use 10 as the default
  if (defined('GWOLLE_GB_ACCESS_LEVEL') === FALSE) {
    define('GWOLLE_GB_ACCESS_LEVEL',10);
  }

	//	make sure this plugin is compatible to prior versions of Wordpress
	include(GWOLLE_GB_DIR.'/admin/_compatibility.php');
	
	//	Set the user level names
	$userLevelNames = array(
    0   => 'Subscriber',
    1   => 'Contributor',
    2   => 'Author',
    3   => 'Author',
    4   => 'Author',
    5   => 'Editor', 
    6   => 'Editor',
    7   => 'Editor',
    8   => 'Administrator',
    9   => 'Administrator',
    10  => 'Administrator'
  );
	
	//	Trigger an upgrade function when the plugin is activated.
	if (!function_exists('gwolle_gb_activation')) {
		function gwolle_gb_activation() {
		  $current_version = get_option('gwolle_gb_version');
		
		  if (!$current_version) {
		  	include(GWOLLE_GB_DIR.'/admin/upgrade.php');
		  	install_gwolle_gb();
		  }
		  elseif ($current_version != GWOLLE_GB_VER) {
		  	include(GWOLLE_GB_DIR.'/admin/upgrade.php');
		  	upgrade_gwolle_gb();
		  }
		}
	}
	register_activation_hook(__FILE__, 'gwolle_gb_activation');
	
	// Widget
	include_once(GWOLLE_GB_DIR.'/widget.php');
	
	// Dashboard widget (for the WP-Admin dashboard)
	include_once(GWOLLE_GB_DIR.'/admin/dashboard-widget.php');
	
	//	add a menu in the Wordpress backend.
	add_action('admin_menu', 'myAdminMenu');
	function myAdminMenu() {
		/*
		**	how to add new menu-entries:
		**	add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
		*/
		
		//	main navigation entry
    add_menu_page(__('Guestbook',GWOLLE_GB_TEXTDOMAIN), __('Guestbook',GWOLLE_GB_TEXTDOMAIN), GWOLLE_GB_ACCESS_LEVEL, GWOLLE_GB_FOLDER.'/gwolle-gb.php', 'page_index', 'div');

    //	'entries'
    //	count: &nbsp;&nbsp;<span class="update-plugins count-1"><span class="plugin-count">1</span></span>
    add_submenu_page(__FILE__, __('Entries',GWOLLE_GB_TEXTDOMAIN), __('Entries',GWOLLE_GB_TEXTDOMAIN), GWOLLE_GB_ACCESS_LEVEL, GWOLLE_GB_FOLDER.'/entries.php', 'page_entries');

		//	'entry editor'
    add_submenu_page(__FILE__, __('Entry editor',GWOLLE_GB_TEXTDOMAIN), __('New entry',GWOLLE_GB_TEXTDOMAIN), GWOLLE_GB_ACCESS_LEVEL, GWOLLE_GB_FOLDER.'/editor.php', 'page_editor');
		
		//	'settings'
    add_submenu_page(__FILE__, __('Settings',GWOLLE_GB_TEXTDOMAIN), __('Settings',GWOLLE_GB_TEXTDOMAIN), GWOLLE_GB_ACCESS_LEVEL, GWOLLE_GB_FOLDER.'/settings.php', 'page_settings');
	}
	//	get the user's ID from the session
	add_action('init', 'gwolle_gb_init');
	function gwolle_gb_init() {
    @session_start();
    
		global $current_user;
		
		// Load settings, if not set
    global $gwolle_gb_settings;
    if (!isset($gwolle_gb_settings)) {
      include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_settings.func.php');
      gwolle_gb_get_settings();
    }
		
		/**
		 * Proccess all the $_POST requests of Gwolle-GB.
		 */
		$gwolle_gb_function = '';
		if (isset($_POST['gwolle_gb_function'])) {
		  $gwolle_gb_function = $_POST['gwolle_gb_function'];
		}
		elseif (isset($_GET['gwolle_gb_function'])) {
		  $gwolle_gb_function = $_GET['gwolle_gb_function'];
		}
		
		if (isset($_POST['entry_id']) && (int)$_POST['entry_id'] > 0) {
		  $entry_id = (int)$_POST['entry_id'];
		}
		elseif (isset($_GET['entry_id']) && (int)$_GET['entry_id'] > 0) {
		  $entry_id = (int)$_GET['entry_id'];
		}
		
		$show               = (isset($_REQUEST['show'])) ? '&show='.$_REQUEST['show'] : '';
		$return_to          = (isset($_REQUEST['return_to']) && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) ? $_REQUEST['return_to'] : FALSE;
		switch($gwolle_gb_function) {
		  case 'add_entry':
        if ($return_to === FALSE) { //  This is an entry by a visitor. Redirect to Gwolle-GB page.
          include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_save_entry.func.php');
          $entry_id = gwolle_gb_save_entry();
          // Get links to guestbook page
        	include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_link.func.php');
        	$gb_links = gwolle_gb_get_link(array(
            'all' => TRUE
          ));
          
          if ($entry_id === FALSE) {
            //  There were errors processing the data. Redirect to writing page.
            header('Location: '.$gb_links['write']);
        		exit;
        	}
        	else {
            $msg = __('Thanks for your entry.',GWOLLE_GB_TEXTDOMAIN);
            if ($gwolle_gb_settings['moderate-entries'] === TRUE) {
              $msg .= __('<br>We will review it and unlock it in a short while.',GWOLLE_GB_TEXTDOMAIN);
            }
            $_SESSION['gwolle_gb']['msg'] = $msg;
            header('Location: '.$gb_links['read']);
            exit;
          }
        }
        else {  //  This is an entry by an admin.
          include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_save_entry.func.php');
          $entry_id = gwolle_gb_save_entry(array(
            'action'            => 'admin_entry'
          ));
          if ($entry_id === FALSE) {
            //  Error while saving. Redirect to editor.
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php');
          }
          else {
            //  Entry added successfully. Redirect to entries.
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&entry_id='.$entry_id);
          }
          exit;
        }
      break;
      case 'edit_entry':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_update_entry.func.php');
        if (gwolle_gb_update_entry(array(
          'old_entry' => $entry
        )) === FALSE) {
          //  Update failed. Return to editor.
          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry['entry_id']);
          exit;
        }
        else {
          //  Updated entry successfully. Return to entries.
          $_SESSION['gwolle_gb']['msg'] = 'changes-saved';
          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php');
          exit;
        }
      break;
      case 'trash_entry':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_trash_entry.func.php');
        if (gwolle_gb_trash_entry(array(
          'entry_id'  => $entry_id
        )) === FALSE) {
          //  Entry could not be trashed.
          $_SESSION['gwolle_gb']['msg'] = 'error-trashing';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
            exit;
          }
        }
        else {
          //  Entry has been trashed
          $_SESSION['gwolle_gb']['entry_id']  = $entry_id;
          $_SESSION['gwolle_gb']['msg']       = 'trashed';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
            exit;
          }
        }
      break;
      case 'untrash_entry':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_trash_entry.func.php');
        if (gwolle_gb_trash_entry(array(
          'entry_id'  => $entry_id,
          'untrash'   => TRUE
        )) === FALSE) {
          //  Entry could not be untrashed.
          $_SESSION['gwolle_gb']['msg'] = 'error-untrashing';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
            exit;
          }
        }
        else {
          //  Entry has been untrashed
          $_SESSION['gwolle_gb']['entry_id']  = $entry_id;
          $_SESSION['gwolle_gb']['msg']       = 'untrashed';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
            exit;
          }
        }
      break;
      case 'delete_entry':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_delete_entry.func.php');
        if (gwolle_gb_delete_entry(array(
          'entry_id'  => $entry_id
        )) === FALSE) {
          //  Entry could not be deleted.
          $_SESSION['gwolle_gb']['msg'] = 'error-deleting';
          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
          exit;
        }
        else {
          //  Entry has been deleted
          $_SESSION['gwolle_gb']['msg'] = 'deleted';
          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php'.$show);
          exit;
        }
      break;
      case 'mark_spam':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_mark_spam.func.php');
        if (gwolle_gb_mark_spam(array(
          'entry_id'  => $entry_id
        )) === FALSE) {
          //  Entry could not be marked as spam.
          $_SESSION['gwolle_gb']['msg'] = 'error-marking-spam';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry_id);
            exit;
          }
        }
        else {
          //  Entry has been marked as spam
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry_id);
            exit;
          }
        }
      break;
      case 'unmark_spam':
        if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
          //  This user does not have the right to edit a guestbook entry.
          exit;
        }
        if (!isset($entry_id)) {
          //  No entry id provided; can't update.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
        $entry = gwolle_gb_get_entries(array(
          'entry_id'  => $entry_id
        ));
        if ($entry === FALSE) {
          //  An entry with this id could not be found.
          exit;
        }
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_mark_spam.func.php');
        if (gwolle_gb_mark_spam(array(
          'entry_id'  => $entry_id,
          'no_spam'   => TRUE
        )) === FALSE) {
          //  Entry could not be marked as no-spam.
          $_SESSION['gwolle_gb']['msg'] = 'error-unmarking-spam';
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry_id);
            exit;
          }
        }
        else {
          //  Entry has been marked as no-spam
          if ($return_to == 'dashboard') {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/index.php');
            exit;
          }
          else {
            header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry_id);
            exit;
          }
        }
      break;
		}
		
		
		//  Process $_REQUEST variables
		$req_action   = (isset($_REQUEST['action'])) ? $_REQUEST['action'] : FALSE;
		$post_action  = (isset($_POST['action'])) ? $_POST['action'] : '';
		$do           = (isset($_REQUEST['do'])) ? $_REQUEST['do'] : '';
		$entry_id     = (isset($_REQUEST['entry_id']) && (int)$_REQUEST['entry_id'] > 0) ? (int)$_REQUEST['entry_id'] : FALSE;
		$gb_page      = (isset($_REQUEST['gb_page'])) ? $_REQUEST['gb_page'] : '';
		$page         = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';
		
		if ($req_action == 'uninstall_gwolle_gb' && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			if ($_POST['uninstall_confirmed'] == 'on') {
				//	uninstall the plugin -> delete all tables and preferences of the plugin
				include_once(GWOLLE_GB_DIR.'/admin/upgrade.php');
				uninstall_gwolle_gb();
			}
			else {
				//	Uninstallation not confirmed.
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/settings.php&msg=uninstall-not-confirmed');
				exit;
			}
		}
		
		//	Check if the plugin's out of date
		$current_version = get_option('gwolle_gb_version');
		if ($current_version && version_compare($current_version,GWOLLE_GB_VER,'<')) {
			//	Upgrade, if this version differs from what the database says.
			include(GWOLLE_GB_DIR.'/admin/upgrade.php');
			upgrade_gwolle_gb();
		}
		
		if ($do == 'massEdit' && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			//	Mass edit entries
			include(GWOLLE_GB_DIR.'/admin/do-massEdit.php');
		}
		elseif ($req_action == 'saveSettings') {
			include(GWOLLE_GB_DIR.'/admin/do-saveSettings.php');
		}
		elseif ($page == GWOLLE_GB_FOLDER.'/entries.php') {
			global $wpdb;
	
			if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
				//	The current user's not allowed to do this
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&msg=no-permission');
				exit;
			}
		}
		elseif (isset($_POST['start_import'])) {  //  Import guestbook entries from another plugin.
		  //  Supported plugins to import guestbook entries from
		  $supported = array('dmsguestbook');
		  if (!in_array($_REQUEST['what'], $supported)) {
		    //  The requested plugin is not supported
		    header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&msg=plugin-not-supported');
		    exit;
		  }
		  else {
		    global $wpdb;
		    if ($_REQUEST['what'] == 'dmsguestbook') {
		      //  Import entries from DMSGuestbook
		      include(GWOLLE_GB_DIR.'/functions/gwolle_gb_import_dmsgb_entry.func.php');
		      if (isset($_POST['guestbook_number']) && is_numeric($_POST['guestbook_number'])) {
		        //  Get guestbook entries from the chosen guestbook
		        $result = mysql_query("
		        SELECT
		          *
		        FROM
		          ".$wpdb->prefix."dmsguestbook
		        WHERE
		          guestbook = ".$_POST['guestbook_number']."
		        ORDER BY
		          date ASC
		        ");
		        if (mysql_num_rows($result) === 0) {
		          //  The chosen guestbook does not contain any entries.
		          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-entries-to-import');
      		    exit;
      		  }
      		  else {
      		    while ($entry = mysql_fetch_array($result)) {
      		      gwolle_gb_import_dmsgb_entry($entry);
    		      }
    		      header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=import-successful&count='.mysql_num_rows($result));
      		    exit;
      		  }
      		}
      		elseif ($_POST['import-all'] == 'true') {
      		  //  Import all entries.
      		  $result = mysql_query("
		        SELECT
		          *
		        FROM
		          ".$wpdb->prefix."dmsguestbook
		        ORDER BY
		          date ASC
		        ");
		        if (mysql_num_rows($result) === 0) {
		          //  There are no entries to import.
		          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-entries-to-import');
      		    exit;
      		  }
      		  else {
      		    while ($entry = mysql_fetch_array($result)) {
      		      gwolle_gb_import_dmsgb_entry($entry);
    		      }
    		      header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=import-successful&count='.mysql_num_rows($result));
      		    exit;
      		  }
      		}
      		else {
      		  //  There are more than one guestbook and the user didn't choose one to import from.
      		  header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-guestbook-chosen');
      		  exit;
      		}
        }
  		}
		}
	}
	
	//	Load admin CSS/scripts
	add_action('admin_head', 'gwolle_gb_admin_head');
	function gwolle_gb_admin_head() {
    wp_enqueue_script('jquery');
    
    //  Process request variables
    $page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';
 
		echo '
		<style type="text/css">
    <!--
      /**
       * CSS for the menu item is defined here
       * so that the folder name can be customized.
       */
      #adminmenu #toplevel_page_'.GWOLLE_GB_FOLDER.'-gwolle-gb div.wp-menu-image {
      	background-image: url('.GWOLLE_GB_URL.'/admin/gfx/icon.png);
      	background-repeat: no-repeat;
      	background-position: 5px 7px;
      }
      
      #adminmenu #toplevel_page_'.GWOLLE_GB_FOLDER.'-gwolle-gb:hover div.wp-menu-image,
      #adminmenu #toplevel_page_'.GWOLLE_GB_FOLDER.'-gwolle-gb.wp-has-current-submenu div.wp-menu-image {
      	background-image: url('.GWOLLE_GB_URL.'/admin/gfx/icon-hover.png);
      }
    -->
    </style>
    <link rel="stylesheet" href="'.get_bloginfo('wpurl').'/wp-admin/css/dashboard.css" type="text/css" media="all" />
		<link rel="stylesheet" href="'.GWOLLE_GB_URL.'/admin/style.css" type="text/css" media="all" />
		<script type="text/javascript">
		  //  URL to Gwolle-GB plugin folder
      var gwolle_gb_plugin_url = "'.GWOLLE_GB_URL.'";
		</script>';
		if ($page == GWOLLE_GB_FOLDER.'/entries.php') {
			//	Include JavaScript for the entries page
			$show = (isset($_REQUEST['show'])) ? $_REQUEST['show'] : 'all';
			echo '
			<script type="text/javascript" src="'.GWOLLE_GB_URL.'/js/stripslashes.js"></script>
			<script type="text/javascript">
        var entry_show = "'.$show.'";
        //  Localized JavaScript strings
        var gwolle_gb_strings = new Array();
        gwolle_gb_strings["warning_spam"]             = "'.addslashes(__("Warning: You're about to check an entry that is marked as spam. Continue?", GWOLLE_GB_TEXTDOMAIN)).'";
        gwolle_gb_strings["warning_marking_not_spam"] = "'.addslashes(__("A message will be sent to the Akismet team that this entry is not spam. Continue?",GWOLLE_GB_TEXTDOMAIN)).'";
      </script>
      <script type="text/javascript" src="'.GWOLLE_GB_URL.'/admin/js/entries.js"></script>';
		}
		elseif ($page == GWOLLE_GB_FOLDER.'/settings.php') {
		  echo '
		  <script type="text/javascript" src="'.GWOLLE_GB_URL.'/js/stripslashes.js"></script>
		  <script type="text/javascript">
		    //  Localized JavaScript strings
		    var gwolle_gb_strings = new Array();
		    gwolle_gb_strings["post_id_search_failed"]  = "'.addslashes(__("An error occured during the search.", GWOLLE_GB_TEXTDOMAIN)).'";
		    gwolle_gb_strings["post_id_found"]          = "'.addslashes(__("I've found a post with the [gwolle-gb] tag. You can start using Gwolle-GB right away.", GWOLLE_GB_TEXTDOMAIN)).'";
		    gwolle_gb_strings["post_id_not_found"]      = "'.addslashes(__("No post with [gwolle-gb] could be found. Please insert the tag and try again.", GWOLLE_GB_TEXTDOMAIN)).'";
		    //  URL to Gwolle-GB plugin folder
		    var gwolle_gb_plugin_url = "'.GWOLLE_GB_URL.'";
		  </script>
		  <script type="text/javascript" src="'.GWOLLE_GB_URL.'/admin/js/settings.js"></script>';
		}
	}
	
	//	Function (use this to display the guestbook on a page)
	function get_gwolle_gb() {
		include(GWOLLE_GB_DIR.'/frontend/index.php');
		return $output;
	}
	
	//	Replace the [gwolle-gb] tag with the guestbook
	add_filter('the_content', 'output_guestbook');
	function output_guestbook($content) {
    $gwolle_gb_tagPosition = strpos($content,'[gwolle-gb]');
		if ($gwolle_gb_tagPosition > -1) {
		  if (get_option('gwolle_gb-guestbookOnly')=='true') {
  			//	Display frontend only; don't display other post content.
  			return get_gwolle_gb();
  		}
  		else {
  		  $output = '';
  		  //  Display content BEFORE the guestbook
  		  $output .= substr($content, 0, $gwolle_gb_tagPosition);
  		  //  Display the frontend
  		  $output .= get_gwolle_gb();
  		  //  Display content AFTER the guestbook
  		  $output .= substr($content, ($gwolle_gb_tagPosition+strlen('[gwolle-gb]')), (strlen($content)-$gwolle_gb_tagPosition));
  		  return $output;
  		}
		}
		else {
			return $content;
		}
	}

	
	function page_index() {
		global $wpdb;
		global $userLevelNames;
		
		//  Process request variables
		$do = (isset($_REQUEST['do'])) ? $_REQUEST['do'] : '';
		
		if (get_option('gwolle_gb_version') === FALSE) {
			include(GWOLLE_GB_DIR.'/admin/installSplash.php');
		}
		elseif ($do == 'import') {
		  include(GWOLLE_GB_DIR.'/admin/import.php');
		}
		else {
			include(GWOLLE_GB_DIR.'/admin/welcome.php');
		}
	}
	
	function page_entries() {
		global $wpdb;
		if (!get_option('gwolle_gb_version')) {
			include(GWOLLE_GB_DIR.'/admin/installSplash.php');
		}
		else {
			include(GWOLLE_GB_DIR.'/admin/entries.php');
		}
	}
	
	function page_editor() {
		global $wpdb; global $current_user;
		if (!get_option('gwolle_gb_version')) {
			include(GWOLLE_GB_DIR.'/admin/installSplash.php');
		}
		else {
			include(GWOLLE_GB_DIR.'/admin/editor.php');
		}
	}
	
	function page_formfields() {
		global $wpdb; global $current_user;
		if (!get_option('gwolle_gb_version')) {
			include(GWOLLE_GB_DIR.'/admin/installSplash.php');
		}
		else {
			include(GWOLLE_GB_DIR.'/admin/formfields.php');
		}
	}
	
	function page_settings() {
		global $wpdb;
		global $defaultMailText;
		if (!get_option('gwolle_gb_version')) {
			include(GWOLLE_GB_DIR.'/admin/installSplash.php');
		}
		else {
			include(GWOLLE_GB_DIR.'/admin/settings.php');
		}
	}
?>