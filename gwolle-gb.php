<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://wolfgangtimme.de/blog/
Description: simple guestbook
Version: 0.9.6
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

	//	Textdomain for the translation
	$textdomain = 'GWGB';
	
	//	Load translation
	load_plugin_textdomain($textdomain, false, dirname( plugin_basename(__FILE__) ) . '/lang');

	//	plugin's version
	define('GWOLLE_GB_VER','0.9.6');

	//	Akismet PHP4 class folder's name
	define('AKISMET_PHP4_CLASS_DIR','Akismet_PHP4');

	//	Akismet PHP5 class folder's name
	define('AKISMET_PHP5_CLASS_DIR','PHP5Akismet.0.4');
	
	//	Access level
	define('GWOLLE_GB_ACCESS_LEVEL', get_option('gwolle_gb-access-level'));

	//	make sure this plugin is compatible to prior versions of Wordpress
	include(WP_PLUGIN_DIR.'/gwolle-gb/admin/_compatibility.php');
	
	//	Set the default mail text.
	$defaultMailText = __("Hello,\n\nthere is a new guestbook entry at '%blog_name%'.\nYou can check it at %entry_management_url%.\n\nHave a nice day!\nYour Gwolle-GB-Mailer",$textdomain);
	
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
		  	include(WP_PLUGIN_DIR.'/gwolle-gb/admin/upgrade.php');
		  	install_gwolle_gb();
		  }
		  elseif ($current_version != GWOLLE_GB_VER) {
		  	include(WP_PLUGIN_DIR.'/gwolle-gb/admin/upgrade.php');
		  	upgrade_gwolle_gb();
		  }
		}
	}
	register_activation_hook(__FILE__, 'gwolle_gb_activation');
	
	//	add a menu in the Wordpress backend.
	add_action('admin_menu', 'myAdminMenu');
	function myAdminMenu() {
		/*
		**	how to add new menu-entries:
		**	add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
		*/
		
		global $textdomain;
		
		//	main navigation entry
    add_menu_page(__('Guestbook',$textdomain), __('Guestbook',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/gwolle-gb.php', 'page_index', 'div');

    //	'entries'
    //	count: &nbsp;&nbsp;<span class="update-plugins count-1"><span class="plugin-count">1</span></span>
    add_submenu_page(__FILE__, __('Entries',$textdomain), __('Entries',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/entries.php', 'page_entries');

		//	'entry editor'
    add_submenu_page(__FILE__, __('Entry editor',$textdomain), __('New entry',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/editor.php', 'page_editor');
		
		//	'form fields-editor' (coming up ;)
    //add_submenu_page(__FILE__, __('Form fields',$textdomain), __('Form fields',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/formfields.php', 'page_formfields');
		
    //	'settings'
    add_submenu_page(__FILE__, __('Settings',$textdomain), __('Settings',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/settings.php', 'page_settings');
	}
	//	get the user's ID from the session
	add_action('init', 'gwolle_gb_init');
	function gwolle_gb_init() {
		global $current_user;
		@session_start();
		
		if ($_REQUEST['action'] == 'uninstall_gwolle_gb' && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			if ($_POST['uninstall_confirmed'] == 'on') {
				//	uninstall the plugin -> delete all tables and preferences of the plugin
				include(WP_PLUGIN_DIR.'/gwolle-gb/admin/upgrade.php');
				uninstall_gwolle_gb();
			}
			else {
				//	Uninstallation not confirmed.
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/settings.php&msg=uninstall-not-confirmed');
				exit;
			}
		}
		
		//	Check if the plugin's out of date
		$current_version = get_option('gwolle_gb_version');
		if ($current_version && version_compare($current_version,GWOLLE_GB_VER,'<')) {
			//	Upgrade, if this version differs from what the database says.
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/upgrade.php');
			upgrade_gwolle_gb();
		}
		
		if ($_REQUEST['do'] == 'massEdit' && current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
			//	Mass edit entries
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/do-massEdit.php');
		}
		elseif (is_numeric($_POST['entry_id']) || $_POST['action'] == 'newEntry') {
			include(WP_PLUGIN_DIR.'/gwolle-gb/frontend/gbLinkFormat.func.php');	//	Include function to format the guestbook link
			
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/do-saveEntry.php');
		}
		elseif (is_numeric($_REQUEST['entry_id']) && $_REQUEST['action'] == 'delete') {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/do-deleteEntry.php');
		}
		elseif (is_numeric($_REQUEST['entry_id']) && ($_REQUEST['action'] == 'markSpam' || $_REQUEST['action'] == 'unmarkSpam')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/do-spam.php');
		}
		elseif ($_REQUEST['gb_page'] == 'write' && $_POST) {
			global $defaultMailText;
			include(WP_PLUGIN_DIR.'/gwolle-gb/frontend/gbLinkFormat.func.php');	//	Include function to format the guestbook link
			
			include(WP_PLUGIN_DIR.'/gwolle-gb/frontend/do-saveNewEntry.php');
		}
		elseif ($_REQUEST['action'] == 'saveSettings') {
			global $defaultMailText;
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/do-saveSettings.php');
		}
		elseif ($_REQUEST['page'] == 'gwolle-gb/entries.php') {
			global $wpdb;
	
			if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
				//	The current user's not allowed to do this
				header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
				exit;
			}
		}
		elseif (isset($_POST['start_import'])) {  //  Import guestbook entries from another plugin.
		  //  Supported plugins to import guestbook entries from
		  $supported = array('dmsguestbook');
		  if (!in_array($_REQUEST['what'], $supported)) {
		    //  The requested plugin is not supported
		    header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&msg=plugin-not-supported');
		    exit;
		  }
		  else {
		    global $wpdb;
		    if ($_REQUEST['what'] == 'dmsguestbook') {
		      //  Import entries from DMSGuestbook
		      include(WP_PLUGIN_DIR.'/gwolle-gb/gwolle_gb_import_dmsgb_entry.func.php');
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
		          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-entries-to-import');
      		    exit;
      		  }
      		  else {
      		    while ($entry = mysql_fetch_array($result)) {
      		      gwolle_gb_import_dmsgb_entry($entry);
    		      }
    		      header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=import-successful&count='.mysql_num_rows($result));
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
		          header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-entries-to-import');
      		    exit;
      		  }
      		  else {
      		    while ($entry = mysql_fetch_array($result)) {
      		      gwolle_gb_import_dmsgb_entry($entry);
    		      }
    		      header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=import-successful&count='.mysql_num_rows($result));
      		    exit;
      		  }
      		}
      		else {
      		  //  There are more than one guestbook and the user didn't choose one to import from.
      		  header('Location: '.get_bloginfo('wpurl').'/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&do=import&what='.$_REQUEST['what'].'&msg=no-guestbook-chosen');
      		  exit;
      		}
        }
  		}
		}
	}
	
	//	load the frontend CSS definition
	add_action('wp_head', 'add_gwolle_gb_frontend_css');
	function add_gwolle_gb_frontend_css() {
		echo '<link rel="stylesheet" href="' . WP_PLUGIN_URL . '/gwolle-gb/frontend/style.css" type="text/css" />';
	}
	
	//	Load admin CSS/scripts
	add_action('admin_head', 'gwolle_gb_admin_head');
	function gwolle_gb_admin_head() {
    wp_enqueue_script('jquery');
    global $textdomain;
 
		echo "<link rel='stylesheet' href='" . get_option('siteurl') . "/wp-admin/css/dashboard.css?ver=20081210' type='text/css' media='all' />";
		echo "<link rel='stylesheet' href='" . WP_PLUGIN_URL . "/gwolle-gb/admin/style.css' type='text/css' media='all' />";
		
		if ($_REQUEST['page'] == 'gwolle-gb/entries.php') {
			//	Include JavaScript for the entries page
			$show = (isset($_REQUEST['show'])) ? $_REQUEST['show'] : 'all';
			echo '
			<script type="text/javascript">
        var entry_show = "'.$show.'";
        //  Localized JavaScript strings
        var gwolle_gb_strings = new Array();
        gwolle_gb_strings["warning_spam"] = "'.__("Warning: You're about to check an entry that is marked as spam. Continue?", $textdomain).'";
        //  URL to Gwolle-GB plugin folder
        var gwolle_gb_plugin_url = "'.WP_PLUGIN_URL.'/gwolle-gb";
      </script>
      <script language="JavaScript" src="'.WP_PLUGIN_URL.'/gwolle-gb/admin/entries.js"></script>';
		}
	}
	
	//	Function (use this to display the guestbook on a page)
	function get_gwolle_gb() {
    global $textdomain;
		include(WP_PLUGIN_DIR.'/gwolle-gb/frontend/gbLinkFormat.func.php');	//	Include function to format the guestbook link
		
		include(WP_PLUGIN_DIR.'/gwolle-gb/frontend/index.php');
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
		global $wpdb; global $textdomain; global $userLevelNames;
		if (!get_option('gwolle_gb_version')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/installSplash.php');
		}
		elseif ($_REQUEST['do'] == 'import') {
		  include(WP_PLUGIN_DIR.'/gwolle-gb/admin/import.php');
		}
		else {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/welcome.php');
		}
	}
	
	function page_entries() {
		global $wpdb; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/installSplash.php');
		}
		else {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/entries.php');
		}
	}
	
	function page_editor() {
		global $wpdb; global $current_user; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/installSplash.php');
		}
		else {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/editor.php');
		}
	}
	
	function page_formfields() {
		global $wpdb; global $current_user; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/installSplash.php');
		}
		else {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/formfields.php');
		}
	}
	
	function page_settings() {
		global $wpdb; global $textdomain; global $defaultMailText;
		if (!get_option('gwolle_gb_version')) {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/installSplash.php');
		}
		else {
			include(WP_PLUGIN_DIR.'/gwolle-gb/admin/settings.php');
		}
	}
?>