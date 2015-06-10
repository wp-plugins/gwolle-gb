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
			'trash'   => 'notrash',
			'spam'    => 'nospam'
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
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Import', GWOLLE_GB_TEXTDOMAIN), __('Import', GWOLLE_GB_TEXTDOMAIN), 'manage_options', GWOLLE_GB_FOLDER . '/import.php', 'gwolle_gb_page_import' );

	// Admin page: admin/export.php
	add_submenu_page( GWOLLE_GB_FOLDER . '/gwolle-gb.php', __('Export', GWOLLE_GB_TEXTDOMAIN), __('Export', GWOLLE_GB_TEXTDOMAIN), 'manage_options', GWOLLE_GB_FOLDER . '/export.php', 'gwolle_gb_page_export' );

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
		$links[] = '<a href="' . admin_url( 'admin.php?page=gwolle-gb/settings.php' ) . '">'.__( 'Settings', GWOLLE_GB_TEXTDOMAIN ).'</a>';
	}
	return $links;
}
add_filter( 'plugin_action_links', 'gwolle_gb_links', 10, 2 );


/*
 * gwolle_gb_handle_post
 * Handle the $_POST for the Frontend.
 * Use this action, since we have a $post already and can use get_the_ID().
 */

add_action('wp', 'gwolle_gb_handle_post');
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
	register_setting( 'gwolle_gb_options', 'gwolle_gb-admin_style',       'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-adminMailContent',  'strval' ); // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-akismet-active',    'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-question', 'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-antispam-answer',   'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-authorMailContent', 'strval' ); // empty by default
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entries_per_page',  'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-entriesPerPage',    'intval' ); // 20
	register_setting( 'gwolle_gb_options', 'gwolle_gb-excerpt_length',    'intval' ); // 0
	register_setting( 'gwolle_gb_options', 'gwolle_gb-form',              'strval' ); // serialized Array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-header',            'strval' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-labels_float',      'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-linkAuthorWebsite', 'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail-from',         'strval' ); // empty string
	register_setting( 'gwolle_gb_options', 'gwolle_gb-mail_author',       'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-moderate-entries',  'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notifyByMail',      'strval' ); // array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-notice',            'strval' ); // string, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-read',              'strval' ); // serialized Array, but initially empty
	register_setting( 'gwolle_gb_options', 'gwolle_gb-require_login',     'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showEntryIcons',    'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showLineBreaks',    'strval' ); // 'false'
	register_setting( 'gwolle_gb_options', 'gwolle_gb-showSmilies',       'strval' ); // 'true'
	register_setting( 'gwolle_gb_options', 'gwolle_gb_version',           'strval' ); // string, mind the underscore
}


add_action('admin_init', 'gwolle_gb_init');
function gwolle_gb_init() {

	// Check if the plugin is out of date
	$current_version = get_option('gwolle_gb_version');
	if ($current_version && version_compare($current_version, GWOLLE_GB_VER, '<')) {
		// Upgrade, if this version differs from what the database says.
		upgrade_gwolle_gb();
	}
}


/* Register styles and scripts. */
function gwolle_gb_register() {

	// Always load jQuery, it's just easier this way.
	wp_enqueue_script('jquery');

	// Register style for frontend. Load it later.
	wp_register_style('gwolle_gb_frontend_css', plugins_url('frontend/style.css', __FILE__), false, GWOLLE_GB_VER,  'screen');
}
add_action('wp_enqueue_scripts', 'gwolle_gb_register');


/*
 * gwolle_gb_load_lang
 * Function called at initialisation.
 * - Loads language files for frontend and backend
 */
function gwolle_gb_load_lang() {
	load_plugin_textdomain( GWOLLE_GB_TEXTDOMAIN, false, GWOLLE_GB_FOLDER . '/lang' );
}
add_action('plugins_loaded', 'gwolle_gb_load_lang');


/*
 * Add the RSS link to the html head.
 * There is no post_content yet, but we do have a get_the_ID().
 */
function gwolle_gb_rss_head() {
	if ( is_singular() ) {
		$post = get_post( get_the_ID() );
		if ( has_shortcode( $post->post_content, 'gwolle_gb' ) || has_shortcode( $post->post_content, 'gwolle_gb_read' ) ) {

			// Remove standard RSS links.
			remove_action( 'wp_head', 'feed_links', 2 );
			remove_action( 'wp_head', 'feed_links_extra', 3 );

			// And add our own RSS link.
			global $wp_rewrite;
			$permalinks = $wp_rewrite->permalink_structure;
			if ( $permalinks ) {
				?>
				<link rel="alternate" type="application/rss+xml" title="<?php esc_attr_e("Guestbook Feed", GWOLLE_GB_TEXTDOMAIN); ?>" href="<?php bloginfo('url'); ?>/feed/gwolle_gb" />
				<?php
			} else {
				?>
				<link rel="alternate" type="application/rss+xml" title="<?php esc_attr_e("Guestbook Feed", GWOLLE_GB_TEXTDOMAIN); ?>" href="<?php bloginfo('url'); ?>/?feed=gwolle_gb" />
				<?php
			}

			// Also set a meta_key so we can find the post with the shortcode back.
			$meta_value = get_post_meta( get_the_ID(), 'gwolle_gb_read', true );
			if ( $meta_value != "true" ) {
				update_post_meta( get_the_ID(), 'gwolle_gb_read', "true", true );
			}
		} else {
			// Remove the meta_key in case it is set.
			delete_post_meta( get_the_ID(), 'gwolle_gb_read' );
		}
	}
}
add_action('wp_head', 'gwolle_gb_rss_head', 1);


