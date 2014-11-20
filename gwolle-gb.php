<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://zenoweb.nl
Description: Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle-GB and have a real guestbook.
Version: 0.9.9.2
Author: Marcel Pol
Author URI: http://zenoweb.nl
*/

/*  Copyright 2009  Wolfgang Timme  (email: gwolle@wolfgangtimme.de)
	Copyright 2014  Marcel Pol      (email: marcel@zenoweb.nl)

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

// Actions
include_once( GWOLLE_GB_DIR . '/actions.php' );

// Widget
// include_once( GWOLLE_GB_DIR . '/widget.php' );

// Dashboard widget (for the WP-Admin dashboard)
// include_once( GWOLLE_GB_DIR . '/admin/dashboard-widget.php' );
// include_once( GWOLLE_GB_DIR . '/functions/gwolle_gb_get_dashboard_widget_row.func.php' );


/*
 * Trigger an install/upgrade function when the plugin is activated.
 */

function gwolle_gb_activation() {
	$current_version = get_option( 'gwolle_gb_version' );

	if ( $current_version == false ) {
		install_gwolle_gb();
	} elseif ($current_version != GWOLLE_GB_VER) {
		upgrade_gwolle_gb();
	}
}
register_activation_hook(__FILE__, 'gwolle_gb_activation');




