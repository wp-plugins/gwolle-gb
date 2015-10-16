<?php
/*
Plugin Name: Gwolle Guestbook
Plugin URI: http://zenoweb.nl
Description: Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and have a real guestbook.
Version: 1.5.4
Author: Marcel Pol
Author URI: http://zenoweb.nl
License: GPLv2 or later
Text Domain: gwolle-gb
Domain Path: /lang/
*/

/*  Copyright 2009       Wolfgang Timme  (email: gwolle@wolfgangtimme.de)
	Copyright 2014-2015  Marcel Pol      (email: marcel@zenoweb.nl)

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
define('GWOLLE_GB_VER', '1.5.4');


/*
 * Todo List:
 *
 * - Entries Admin page, make columns sortable, add order parameters to get* functions.
 * - Fix Emoji for Admin_reply.
 * - When setting a max words for reading an entry, add a Readmore link with a JS event.
 * - Do AJAX the proper way for CAPTCHA check.
 * - Meta Key also saves the book_id, so we can use that in the links to the right guestbook.
 * - Fix leftover pagination issues.
 *
 */


/*
 * Definitions
 */
define('GWOLLE_GB_FOLDER', plugin_basename(dirname(__FILE__)));
define('GWOLLE_GB_DIR', WP_PLUGIN_DIR . '/' . GWOLLE_GB_FOLDER);


global $wpdb;

// Declare database table names
$wpdb->gwolle_gb_entries = $wpdb->prefix . 'gwolle_gb_entries';
$wpdb->gwolle_gb_log = $wpdb->prefix . 'gwolle_gb_log';


// Classes
include_once( GWOLLE_GB_DIR . '/functions/class-entry.php' );

// Functions for the frontend
include_once( GWOLLE_GB_DIR . '/frontend/captcha-ajax.php' );
include_once( GWOLLE_GB_DIR . '/frontend/index.php' );
include_once( GWOLLE_GB_DIR . '/frontend/pagination.php' );
include_once( GWOLLE_GB_DIR . '/frontend/posthandling.php' );
include_once( GWOLLE_GB_DIR . '/frontend/read.php' );
include_once( GWOLLE_GB_DIR . '/frontend/rss.php' );
include_once( GWOLLE_GB_DIR . '/frontend/write.php' );

// Functions and pages for the backend
include_once( GWOLLE_GB_DIR . '/admin/ajax.php' );
include_once( GWOLLE_GB_DIR . '/admin/installSplash.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-editor.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-entries.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-export.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-gwolle-gb.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-import.php' );
include_once( GWOLLE_GB_DIR . '/admin/page-settings.php' );
include_once( GWOLLE_GB_DIR . '/admin/pagination.php' );
include_once( GWOLLE_GB_DIR . '/admin/upgrade.php' );

// Tabs for page-settings.php
include_once( GWOLLE_GB_DIR . '/admin/tabs/formtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/readingtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/admintab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/antispamtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/emailtab.php' );
include_once( GWOLLE_GB_DIR . '/admin/tabs/uninstalltab.php' );

// General Functions
include_once( GWOLLE_GB_DIR . '/functions/akismet.php' );
include_once( GWOLLE_GB_DIR . '/functions/bbcode_emoji.php' );
include_once( GWOLLE_GB_DIR . '/functions/get_entries.php' );
include_once( GWOLLE_GB_DIR . '/functions/get_entry_count.php' );
include_once( GWOLLE_GB_DIR . '/functions/log.php' );
include_once( GWOLLE_GB_DIR . '/functions/mail.php' );
include_once( GWOLLE_GB_DIR . '/functions/misc.php' );

// WordPress Hooks
include_once( GWOLLE_GB_DIR . '/hooks.php' );

// Frontend Widget
include_once( GWOLLE_GB_DIR . '/frontend/widget.php' );

// Dashboard Widget (for the WP-Admin dashboard)
include_once( GWOLLE_GB_DIR . '/admin/dashboard-widget.php' );


/*
 * Trigger an install/upgrade function when the plugin is activated.
 */
function gwolle_gb_activation( $networkwide ) {
	global $wpdb;

	$current_version = get_option( 'gwolle_gb_version' );

	if ( function_exists('is_multisite') && is_multisite() ) {
		$blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
		foreach ($blogids as $blog_id) {
			switch_to_blog($blog_id);
			if ( $current_version == false ) {
				gwolle_gb_install();
			} elseif ($current_version != GWOLLE_GB_VER) {
				gwolle_gb_upgrade();
			}
			restore_current_blog();
		}
	} else {
		if ( $current_version == false ) {
			gwolle_gb_install();
		} elseif ($current_version != GWOLLE_GB_VER) {
			gwolle_gb_upgrade();
		}
	}
}
register_activation_hook(__FILE__, 'gwolle_gb_activation');


/* Translate Description */
function gwolle_gb_description() {
	$var = __( "Gwolle Guestbook is not just another guestbook for WordPress. The goal is to provide an easy and slim way to integrate a guestbook into your WordPress powered site. Don't use your 'comment' section the wrong way - install Gwolle Guestbook and have a real guestbook.", 'gwolle-gb' );
	$var = __( "Gwolle Guestbook is the WordPress guestbook you've just been looking for. Beautiful and easy.", 'gwolle-gb' );
}
