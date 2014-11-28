<?php

/*
 * WordPress Actions and Filters.
 * See the Plugin API in the Codex:
 * http://codex.wordpress.org/Plugin_API
 */



/*
 * Add a menu in the WordPress backend.
 */

add_action('admin_menu', 'gwolle_gb_adminmenu');
function gwolle_gb_adminmenu() {
	/*
	 * How to add new menu-entries:
	 * add_menu_page( $page_title, $menu_title, $access_level, $file, $function = '', $icon_url = '' )
	 */


	// Counter
	$count_unchecked = gwolle_gb_get_entry_count(
		array(
			'checked' => 'unchecked',
			'deleted' => 'notdeleted',
			'spam' => 'nospam'
		)
	);

	// Main navigation entry
	// Admin page: admin/welcome.php
	add_menu_page(
		__('Guestbook', GWOLLE_GB_TEXTDOMAIN),
		__('Guestbook', GWOLLE_GB_TEXTDOMAIN) . "<span class='update-plugins count-" . $count_unchecked . "'><span class='theme-count'>" . $count_unchecked . "</span></span>",
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		'gwolle_gb_welcome',
		'dashicons-admin-comments'
	);

	// Admin page: admin/entries.php
	add_submenu_page(
		GWOLLE_GB_FOLDER . '/gwolle-gb.php',
		__('Entries', GWOLLE_GB_TEXTDOMAIN),
		__('Entries', GWOLLE_GB_TEXTDOMAIN) . "<span class='update-plugins count-" . $count_unchecked . "'><span class='theme-count'>" . $count_unchecked . "</span></span>",
		'moderate_comments',
		GWOLLE_GB_FOLDER . '/entries.php',
		'gwolle_gb_page_entries'
	);

	// Admin page: admin/editor.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Entry editor', GWOLLE_GB_TEXTDOMAIN), __('New entry', GWOLLE_GB_TEXTDOMAIN), 'moderate_comments', GWOLLE_GB_FOLDER . '/editor.php', 'gwolle_gb_page_editor' );

	// Admin page: admin/settings.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Settings', GWOLLE_GB_TEXTDOMAIN), __('Settings', GWOLLE_GB_TEXTDOMAIN), 'manage_options', GWOLLE_GB_FOLDER . '/settings.php', 'gwolle_gb_page_settings' );

	// Admin page: admin/import.php
	// FIXME, rename function to gwolle_gb_page_import
	//add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Import', GWOLLE_GB_TEXTDOMAIN), __('Import', GWOLLE_GB_TEXTDOMAIN), 'manage_options', GWOLLE_GB_FOLDER . '/import.php', 'gwolle_gb_import' );

	// Load Admin CSS
	wp_enqueue_style( 'gwolle-gb-css', WP_PLUGIN_URL . '/' . GWOLLE_GB_FOLDER .'/admin/style.css', false, GWOLLE_GB_VER, 'all' );

	// Load JavaScript for Admin
	wp_enqueue_script( 'gwolle-gb-entries', WP_PLUGIN_URL . '/' . GWOLLE_GB_FOLDER .'/admin/js/admin.js', 'jquery', GWOLLE_GB_VER, true );
}


/*
 * customtaxorder_links
 * Add Settings link to the main plugin page
 */

function gwolle_gb_links( $links, $file ) {
	if ( $file == plugin_basename( dirname(__FILE__).'/gwolle-gb.php' ) ) {
		$links[] = '<a href="' . admin_url( 'admin.php?page=gwolle-gb/settings.php' ) . '">'.__( 'Settings' ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gwolle_gb_links', 10, 2 );


/*
 * gwolle_gb_handle_post
 * Handle the $_POST for the Frontend.
 */

add_action('after_setup_theme', 'gwolle_gb_handle_post');
function gwolle_gb_handle_post() {
	if ( !is_admin() ) {
		// Frontend Handling of $_POST, only one form
		if ( isset($_POST['gwolle_gb_function']) && $_POST['gwolle_gb_function'] == 'add_entry' ) {
			gwolle_gb_frontend_posthandling();
		}
	}
}


/*
 * Register settings
 */

add_action( 'admin_init', 'gwolle_gb_register_settings' );
function gwolle_gb_register_settings() {
	register_setting( 'gwolle_gb_options', 'gwolle_gb-access-level',		'intval' ); // to delete
	register_setting( 'gwolle_gb_options', 'gwolle_gb-adminMailContent',	'strval' );
	register_setting( 'gwolle_gb_options', 'gwolle_gb-akismet-active',		'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-checkForImport',		'strval' ); // 'true', to delete?
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entriesPerPage',		'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-guestbookOnly',		'strval' ); // to delete
	register_setting( 'gwolle_gb_options', 'gwolle_gb-linkAuthorWebsite',	'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-moderate-entries',	'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notifyByMail',		'strval' ); // array, but empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-post_ID',				'intval' ); // to delete
	register_setting( 'gwolle_gb_options', 'gwolle_gb-recaptcha-active',	'strval' );
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showLineBreaks',		'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showSmilies',			'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showEntryIcons',		'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-defaultMailText',		'strval' );
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entries_per_page',	'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb_version',				'strval' ); // mind the underscore
}


add_action('init', 'gwolle_gb_init'); // FIXME: is this the right action, or admin_init?
function gwolle_gb_init() {

	// Check if the plugin is out of date
	$current_version = get_option('gwolle_gb_version');
	if ($current_version && version_compare($current_version, GWOLLE_GB_VER, '<')) {
		// Upgrade, if this version differs from what the database says.
		upgrade_gwolle_gb();
	}
}


/* Load jQuery */
function gwolle_gb_jquery() {
	// load always
	wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'gwolle_gb_jquery');


/*
 * gwolle_gb_load_lang
 * Function called at initialisation.
 * - Loads language files for frontend and backend
 */
function gwolle_gb_load_lang() {
	load_plugin_textdomain( GWOLLE_GB_TEXTDOMAIN, false, GWOLLE_GB_FOLDER . '/lang' );
}
add_action('plugins_loaded', 'gwolle_gb_load_lang');


