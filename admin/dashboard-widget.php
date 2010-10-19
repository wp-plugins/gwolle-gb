<?php
	/*
	**	Adds a dashboard widget to show the latest entries.
	*/
	
	//	Content
	function gwolle_gb_dashboard() {
    $wpurl = get_bloginfo('wpurl');
    
    include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_entries.func.php');
    
    $entries = gwolle_gb_get_entries(array(
      'num_entries'     => 5,
      'excerpt_length'  => 250
    ));
    if ($entries !== FALSE) {
      //  Dashboard JavaScript
      echo '
      <script type="text/javascript" src="'.GWOLLE_GB_URL.'/admin/js/dashboard.js"></script>';
      //  List of guestbook entries
      echo '
      <div id="the-comment-list" class="gwolle-gb-entry-list">';
        include_once(GWOLLE_GB_DIR.'/functions/gwolle_gb_get_dashboard_widget_row.func.php');
        foreach($entries as $entry) {
          gwolle_gb_get_dashboard_widget_row(array(
            'entry' => $entry
          ));
        }
        echo '
      </div>
      <p class="textright">
        <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php" class="button">'.__('View all').'</a>
      </p>';
		}
		else {
			echo '
			<p>'.__('No guestbook entries yet.',GWOLLE_GB_TEXTDOMAIN).'</p>';
		}
	}
	
	//	Add the widget
	function gwolle_gb_dashboard_setup() {
		global $icon;
		wp_add_dashboard_widget( 'gwolle_gb_dashboard', __('Guestbook',GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_dashboard' );
	}
	 
	//	Setup the widget
	add_action('wp_dashboard_setup', 'gwolle_gb_dashboard_setup');
?>