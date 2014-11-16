<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://zenoweb.nl
Description: Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle-GB and have a real guestbook.
Version: 0.9.9.2
Author: Marcel Pol
Author URI: http://zenoweb.nl
*/

/*  Copyright 2009  Wolfgang Timme  (email : gwolle@wolfgangtimme.de)
	Copyright 2014  Marcel Pol      (email : marcel@zenoweb.nl)

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


// Plugin Version
define('GWOLLE_GB_VER', '0.9.9.2');


/*
 * Definitions
 */
define('GWOLLE_GB_FOLDER', plugin_basename(dirname(__FILE__)));
define('GWOLLE_GB_URL', WP_PLUGIN_URL . '/' . GWOLLE_GB_FOLDER);
define('GWOLLE_GB_DIR', WP_PLUGIN_DIR . '/' . GWOLLE_GB_FOLDER);
// Textdomain for translation
define('GWOLLE_GB_TEXTDOMAIN', 'GWGB');


// Load settings
global $gwolle_gb_settings;
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_settings.func.php' );
gwolle_gb_get_settings();


// FIXME, this needs to be in init action or so?
// Declare database table names
$wpdb->gwolle_gb_entries = $wpdb->prefix . 'gwolle_gb_entries';
$wpdb->gwolle_gb_log = $wpdb->prefix . 'gwolle_gb_log';


// Classes
include_once( GWOLLE_GB_DIR . '/functions/class.gwolle_gb_entry.php' );

// Functions for the frontend
include_once( GWOLLE_GB_DIR . '/frontend/index.php' );
include_once( GWOLLE_GB_DIR . '/frontend/posthandling.php' );
include_once( GWOLLE_GB_DIR . '/frontend/read.php' );
include_once( GWOLLE_GB_DIR . '/frontend/write.php' );

// Functions and pages for the backend
include_once( GWOLLE_GB_DIR . '/admin/installSplash.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-editor.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-entries.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-gwolle-gb.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-import.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-settings.php' );
include_once( GWOLLE_GB_DIR . '/admin/upgrade.php' );

// General Functions
include_once( GWOLLE_GB_DIR . '/functions/function.gwolle_gb_akismet.php' );
include_once( GWOLLE_GB_DIR . '/functions/function.gwolle_gb_get_entries.php' );
include_once( GWOLLE_GB_DIR . '/functions/function.gwolle_gb_get_entry_count.php' );
include_once( GWOLLE_GB_DIR . '/functions/function.gwolle_gb_log.php' );
include_once( GWOLLE_GB_DIR . '/functions/function.gwolle_gb_misc.php' );


// Old Functions, to be replaced by new functions or class
include_once( GWOLLE_GB_DIR . '/admin/check_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/admin/spam.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/get_gwolle_gb_post_id.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_check_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_check_entry_data.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_delete_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_dashboard_widget_row.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_entries.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_entry_count.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_link.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_import_dmsgb_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_isspam_akismet.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_mark_spam.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_save_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_trash_entry.func.php' );
include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_update_entry.func.php' );

// Actions
include_once( GWOLLE_GB_DIR . '/actions.php' );

// Widget
// include_once( GWOLLE_GB_DIR . '/widget.php' );

// Dashboard widget (for the WP-Admin dashboard)
// include_once( GWOLLE_GB_DIR . '/admin/dashboard-widget.php' );



// FIXME; Nuke from Orbit
add_action('init', 'gwolle_gb_init');
function gwolle_gb_init() {
	if ( ! is_admin() ) {
		// only run this on wp-admin panel
		// frontend uses different action already
		return;
	}

	@session_start();

	global $current_user;

	$show = (isset($_REQUEST['show'])) ? '&show=' . $_REQUEST['show'] : '';


	// Process $_REQUEST variables
	$do = (isset($_REQUEST['do'])) ? $_REQUEST['do'] : '';
	$gb_page = (isset($_REQUEST['gb_page'])) ? $_REQUEST['gb_page'] : '';


	// FIXME: make it into a page
	/*
	if ($req_action == 'uninstall_gwolle_gb') {
		if ($_POST['uninstall_confirmed'] == 'on') {
			// uninstall the plugin -> delete all tables and preferences of the plugin
			uninstall_gwolle_gb();
		} else {
			// Uninstallation not confirmed.
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php&msg=uninstall-not-confirmed');
			exit ;
		}
	}*/


	// Check if the plugin is out of date
	$current_version = get_option('gwolle_gb_version');
	if ($current_version && version_compare($current_version, GWOLLE_GB_VER, '<')) {
		// Upgrade, if this version differs from what the database says.
		upgrade_gwolle_gb();
	}


	if ($do == 'massEdit') {
		// FIXME: Move to page-entries.php
		include (GWOLLE_GB_DIR . '/admin/do-massEdit.php');
	} elseif (isset($_POST['start_import'])) {
		// FIXME, make it into a separate page in /admin/import.php and move this posthandling there
		// Import guestbook entries from another plugin.
		// Supported plugins to import guestbook entries from
		$supported = array('dmsguestbook');
		if (!in_array($_REQUEST['what'], $supported)) {
			// The requested plugin is not supported
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&msg=plugin-not-supported');
			exit ;
		} else {
			global $wpdb;
			if ($_REQUEST['what'] == 'dmsguestbook') {
				// Import entries from DMSGuestbook
				if (isset($_POST['guestbook_number']) && is_numeric($_POST['guestbook_number'])) {
					// Get guestbook entries from the chosen guestbook
					// FIXME, cleanup first query
					$result_nr = $wpdb->query("
						SELECT
							*
						FROM
							" . $wpdb->prefix . "dmsguestbook
						WHERE
							guestbook = " . $_POST['guestbook_number'] . "
						ORDER BY
							date ASC
						");
					if ($result_nr === 0) {
						// The chosen guestbook does not contain any entries.
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-entries-to-import');
						exit ;
					} else {
						$result = $wpdb->get_results("
							SELECT
								*
							FROM
								" . $wpdb->prefix . "dmsguestbook
							WHERE
								guestbook = " . $_POST['guestbook_number'] . "
							ORDER BY
								date ASC
							", ARRAY_A);
						foreach ($result as $entry) {
							gwolle_gb_import_dmsgb_entry($entry);
						}
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=import-successful&count=' . $result_nr);
						exit ;
					}
				} elseif ($_POST['import-all'] == 'true') {
					//  Import all entries.
					// FIXME: cleanup first query
					$result_nr = $wpdb->query("
						SELECT
							*
						FROM
							" . $wpdb->prefix . "dmsguestbook
						ORDER BY
							date ASC
						");
					if ($result_nr === 0) {
						//  There are no entries to import.
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-entries-to-import');
						exit ;
					} else {
						$result = $wpdb->get_results("
							SELECT
								*
							FROM
								" . $wpdb->prefix . "dmsguestbook
							ORDER BY
								date ASC
							", ARRAY_A);
						foreach ($result as $entry) {
							gwolle_gb_import_dmsgb_entry($entry);
						}
						header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=import-successful&count=' . $result_nr);
						exit ;
					}
				} else {
					//  There are more than one guestbook and the user didn't choose one to import from.
					header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php&do=import&what=' . $_REQUEST['what'] . '&msg=no-guestbook-chosen');
					exit ;
				}
			}
		}
	}
}

// Load admin CSS/scripts
// FIXME, use enqueue
add_action('admin_head', 'gwolle_gb_admin_head');
function gwolle_gb_admin_head() {
	wp_enqueue_script('jquery');

	//  Process request variables
	$page = (isset($_REQUEST['page'])) ? $_REQUEST['page'] : '';

	echo '
    <link rel="stylesheet" href="' . get_bloginfo('wpurl') . '/wp-admin/css/dashboard.css" type="text/css" media="all" />
		<link rel="stylesheet" href="' . GWOLLE_GB_URL . '/admin/style.css" type="text/css" media="all" />
		<script type="text/javascript">
		  //  URL to Gwolle-GB plugin folder
      var gwolle_gb_plugin_url = "' . GWOLLE_GB_URL . '";
		</script>';
	if ($page == GWOLLE_GB_FOLDER . '/entries.php') {
		//	Include JavaScript for the entries page
		$show = (isset($_REQUEST['show'])) ? $_REQUEST['show'] : 'all';
		echo '
			<script type="text/javascript" src="' . GWOLLE_GB_URL . '/js/stripslashes.js"></script>
			<script type="text/javascript">
        var entry_show = "' . $show . '";
        //  Localized JavaScript strings
        var gwolle_gb_strings = new Array();
        gwolle_gb_strings["warning_spam"]             = "' . addslashes(__("Warning: You're about to check an entry that is marked as spam. Continue?", GWOLLE_GB_TEXTDOMAIN)) . '";
        gwolle_gb_strings["warning_marking_not_spam"] = "' . addslashes(__("A message will be sent to the Akismet team that this entry is not spam. Continue?", GWOLLE_GB_TEXTDOMAIN)) . '";
      </script>
      <script type="text/javascript" src="' . GWOLLE_GB_URL . '/admin/js/entries.js"></script>';
	} elseif ($page == GWOLLE_GB_FOLDER . '/settings.php') {
		echo '
		  <script type="text/javascript" src="' . GWOLLE_GB_URL . '/js/stripslashes.js"></script>
		  <script type="text/javascript">
		    //  Localized JavaScript strings
		    var gwolle_gb_strings = new Array();
		    gwolle_gb_strings["post_id_search_failed"]  = "' . addslashes(__("An error occured during the search.", GWOLLE_GB_TEXTDOMAIN)) . '";
		    gwolle_gb_strings["post_id_found"]          = "' . addslashes(__("I've found a post with the [gwolle-gb] tag. You can start using Gwolle-GB right away.", GWOLLE_GB_TEXTDOMAIN)) . '";
		    gwolle_gb_strings["post_id_not_found"]      = "' . addslashes(__("No post with [gwolle-gb] could be found. Please insert the tag and try again.", GWOLLE_GB_TEXTDOMAIN)) . '";
		    //  URL to Gwolle-GB plugin folder
		    var gwolle_gb_plugin_url = "' . GWOLLE_GB_URL . '";
		  </script>
		  <script type="text/javascript" src="' . GWOLLE_GB_URL . '/admin/js/settings.js"></script>';
	}
}


