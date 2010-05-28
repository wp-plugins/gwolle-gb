<?php
	/*
	**	Save new entries to the database, when valid.
	**	Obligatory fields:
	**	- name
	**	- entry
	**	... and a negative Akismet result (= no spam) and a correct captcha; both only when turned on in the settings panel.
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $wpdb;
	global $textdomain;
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
  
  include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_save_entry.func.php');
  $entry_id = gwolle_gb_save_entry();
  
  // Get links to guestbook page
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_link.func.php');
	$gb_links = gwolle_gb_get_link(array(
    'all' => TRUE
  ));
  
  if ($entry_id === FALSE) {
    header('Location: '.$gb_links['write']);
		exit;
	}
	else {
    $msg = __('Thanks for your entry.',$textdomain);
    if ($gwolle_gb_settings['moderate-entries'] === TRUE) {
      $msg .= __('<br>We will review it and unlock it in a short while.',$textdomain);
    }
    $_SESSION['gwolle_gb']['msg'] = $msg;
    header('Location: '.$gb_links['read']);
    exit;
  }
?>