<?php
  /**
   * gwolle_gb_get_link
   * Gets the link for the post Gwolle-GB is displayed on.
   */
  if (!function_exists('gwolle_gb_get_link')) {
  	function gwolle_gb_get_link($args=array()) {
      // Load settings, if not set
      global $gwolle_gb_settings;
      if (!isset($gwolle_gb_settings)) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
        gwolle_gb_get_settings();
      }
      
      /**
       * At first, check if the ID of the guestbook post has been set.
       * If not, try to get it.
       */
      $post_id = (isset($gwolle_gb_settings['post_ID']) && (int)$gwolle_gb_settings['post_ID'] > 0) ? (int)$gwolle_gb_settings['post_ID'] : FALSE;
      if ($post_id === FALSE) {
        include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/get_gwolle_gb_post_id.func.php');
        $post_id = get_gwolle_gb_post_id();
      }
      
      if ($post_id === FALSE) {
        global $wp_query;
        global $post;
        // Still no $post_id. Try to get it via the $_REQUEST vars.
        if (isset($_REQUEST['p'])) {
          $post_id = $_REQUEST['p'];
        }
        elseif (isset($wp_query) && is_object($wp_query)) {
          //  Try the $wp_query object
          $post_id = $wp_query->post->ID;
        }
        elseif (isset($post) && is_object($post)) {
          $post_id = $post->ID;
        }
      }
      
      if ((int)$post_id === 0) {
        //  Well, we've done everything we can. Time to tell the truth.
        return FALSE;
      }
      
      //  Get the permalink to the post
      $gwolle_gb_url = get_permalink($post_id);
      
      if (count($args) == 0) {
        //  no arguments given. Just return the plain URL.
        return $gwolle_gb_url;
      }
      
      /**
       * If the $argument 'gb_page' is set we have to append
       * something to the $permalink to let the Gwolle-GB index.php file
       * know which page to display when this link is clicked.
       */
      if ((isset($args['all']) && $args['all'] === TRUE) || (isset($args['gb_page']) && in_array($args['gb_page'], array('read', 'write')))) {
        $last_char_pos = strlen($gwolle_gb_url)-1;
        $last_char = substr($gwolle_gb_url, $last_char_pos, 1);
        
        if ($last_char == '?') {
          if (strpos($gwolle_gb_url, '?') < $last_char_pos) {
            //  The URL ends with a questionmark, but there is a question mark before the last one.
            $gwolle_gb_url .= '&';
          }
        }
        elseif ($last_char == '&') {
          //  do nothing. $gb_page will be appended.
        }
        elseif ($last_char == '/') {
          //  URL ends with a slash. Append questionmark.
          $gwolle_gb_url .= '?';
        }
        elseif (strpos($gwolle_gb_url, '?') !== FALSE) {
          //  There has been a questionmark, so there is a list of $_GET vars. Append '&'.
          $gwolle_gb_url .= '&';
        }
        else {
          //  Don't know. Append slash with questionmark.
          $gwolle_gb_url .= '/?';
        }
        if (isset($args['gb_page'])) {
          //  Just return a single link
          return $gwolle_gb_url.'gb_page='.$args['gb_page'];
        }
        elseif (isset($args['all'])) {
          return array(
            'read'  => $gwolle_gb_url.'gb_page=read',
            'write' => $gwolle_gb_url.'gb_page=write',
            'plain' => $gwolle_gb_url
          );
        }
      }
      //  Don't know what to do more. Just end this.
      return FALSE;
  	}
  }
?>