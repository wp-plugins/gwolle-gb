<?php
  /**
   * gwolle_gb_get_gravatar_html
   * Gets the gravatar image for an email address.
   * Written to provide backwards compatibility.
   */
  if (!function_exists('gwolle_gb_get_gravtar_html')) {
    function gwolle_gb_get_gravtar_html($email='') {
      $size = '50';
      if (function_exists('get_avatar')) {
        //  Use the WP function get_avatar for this.
        return get_avatar($email, $size);
      }
      else {
        //  Manually generate html (see http://codex.wordpress.org/Using_Gravatars#Backwards_Compatibility)
        $grav_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5($email)."&size=".$size;
        return '<img src="'.$grav_url.'" alt="Gravtar"/>';
      }
    }
  }
?>