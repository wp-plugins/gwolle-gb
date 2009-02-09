<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://wolfgangtimme.de/blog/
Description: simple guestbook
Version: 0.9.4.1
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
	define('GWOLLE_GB_VER','0.9.4.1');

	//	Akismet PHP4 class folder's name
	define('AKISMET_PHP4_CLASS_DIR','Akismet_PHP4');

	//	Akismet PHP5 class folder's name
	define('AKISMET_PHP5_CLASS_DIR','PHP5Akismet.0.4');
	
	//	Access level
	define('GWOLLE_GB_ACCESS_LEVEL', get_option('gwolle_gb-access-level'));

	//	make sure this plugin is compatible to prior versions of Wordpress
	include('admin/_compatibility.php');
		
	
	//	Trigger an upgrade function when the plugin is activated.
	if (!function_exists('gwolle_gb_activation')) {
		function gwolle_gb_activation() {
		  $current_version = get_option('gwolle_gb_version');
		
		  if (!$current_version) {
		  	include('admin/upgrade.php');
		  	install_gwolle_gb();
		  }
		  elseif ($current_version != GWOLLE_GB_VER) {
		  	include('admin/upgrade.php');
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
		
    //	'settings'
    add_submenu_page(__FILE__, __('Settings',$textdomain), __('Settings',$textdomain), GWOLLE_GB_ACCESS_LEVEL, 'gwolle-gb/settings.php', 'page_settings');
	}
	//	get the user's ID from the session
	add_action('init', 'gb_init');
	function gb_init() {
		global $current_user;
		session_start();
		
		if ($_REQUEST['action'] == 'uninstall_gwolle_gb' && current_user_can('level_10')) {
			//	uninstall the plugin -> delete all tables and preferences of the plugin
			include('admin/upgrade.php');
			uninstall_gwolle_gb();
		}
		
		//	Check if the plugin's out of date
		$current_version = get_option('gwolle_gb_version');
		if ($current_version && version_compare($current_version,GWOLLE_GB_VER,'<')) {
			//	Upgrade, if this version differs from what the database says.
			include('admin/upgrade.php');
			upgrade_gwolle_gb();
		}
		
		if (is_numeric($_POST['entry_id']) || $_POST['action'] == 'newEntry') {
			include('admin/do-saveEntry.php');
		}
		elseif (is_numeric($_REQUEST['entry_id']) && $_REQUEST['action'] == 'delete') {
			include('admin/do-deleteEntry.php');
		}
		elseif (is_numeric($_REQUEST['entry_id']) && ($_REQUEST['action'] == 'markSpam' || $_REQUEST['action'] == 'unmarkSpam')) {
			include('admin/do-spam.php');
		}
		elseif ($_REQUEST['gb_page'] == 'write' && $_POST) {
			include('frontend/do-saveNewEntry.php');
		}
		elseif ($_REQUEST['action'] == 'saveSettings') {
			include('admin/do-saveSettings.php');
		}
		elseif ($_REQUEST['page'] == 'gwolle-gb/entries.php') {
			global $wpdb;
	
			if (!current_user_can('level_' . GWOLLE_GB_ACCESS_LEVEL)) {
				//	The current user's not allowed to do this
				header('Location: ' . get_bloginfo('url') . '/wp-admin/admin.php?page=gwolle-gb/gwolle-gb.php&msg=no-permission');
				exit;
			}
		}
	}
	
	//	load the frontend CSS definition
	add_action('wp_head', 'add_gwolle_gb_frontend_css');
	function add_gwolle_gb_frontend_css() {
		echo '<link rel="stylesheet" href="' . WP_PLUGIN_URL . '/gwolle-gb/frontend/style.css" type="text/css">';
	}
	
	//	load the admin panel CSS
	add_action('admin_head', 'add_gwolle_gb_admin_css');
	function add_gwolle_gb_admin_css() {
		echo "<link rel='stylesheet' href='" . get_option('siteurl') . "/wp-admin/css/dashboard.css?ver=20081210' type='text/css' media='all' />";
		echo "<link rel='stylesheet' href='" . WP_PLUGIN_URL . "/gwolle-gb/admin/style.css' type='text/css' media='all' />";
	}	
	
	//	Replace the [gwolle-gb] tag with the guestbook
	add_filter('the_content', 'output_guestbook');
	function output_guestbook($content) {
		if (strpos($content,'[gwolle-gb]') > -1) {
			//	Display the frontend.
			include('frontend/index.php');
		}
		else {
			return $content;
		}
	}

	
	function page_index() {
		global $wpdb; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include('admin/installSplash.php');
		}
		else {
			include('admin/welcome.php');
		}
	}
	
	function page_entries() {
		global $wpdb; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include('admin/installSplash.php');
		}
		else {
			include('admin/entries.php');
		}
	}
	
	function page_editor() {
		global $wpdb; global $current_user; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include('admin/installSplash.php');
		}
		else {
			include('admin/editor.php');
		}
	}
	
	function page_settings() {
		global $wpdb; global $textdomain;
		if (!get_option('gwolle_gb_version')) {
			include('admin/installSplash.php');
		}
		else {
			include('admin/settings.php');
		}
	}
?>