<?php
  /**
   * get_gwolle_gb_post_id
   * Searches for the post with the gwolle-gb tag.
   * Returns:
   * - FALSE, if search was not successful
   * - (int)$post_id, if found
   */
  if (!function_exists('get_gwolle_gb_post_id')) {
    function get_gwolle_gb_post_id() {
      global $wpdb;
      $sql = "
	    SELECT
	      p.ID
	    FROM
	      ".$wpdb->posts." p
	    WHERE
	      p.post_content LIKE '%[gwolle-gb]%'
	      AND
	      p.post_status = 'publish'
	    LIMIT 1";
	    $result = mysql_query($sql);
	    if (mysql_num_rows($result) == 0) {
        return 0;
      }
      else {
        $data = mysql_fetch_array($result, MYSQL_ASSOC);
        return $data['ID'];
      }
      return FALSE;
    }
  }
?>