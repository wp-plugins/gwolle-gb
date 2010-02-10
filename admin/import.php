<?php
  /*
   *
	 *	import.php
	 *	Lets the user import guestbook entries from other plugins.
	 *  Currently supported:
	 *  - DMSGuestbook (http://wordpress.org/extend/plugins/dmsguestbook/)
	*/
	
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	
?>

<div class="wrap">
	<div id="icon-gwolle-gb"><br /></div>
	<h2><?php
    if ($_REQUEST['what'] == 'dmsguestbook') {
      _e('Import guestbook entries from DMSGuestbook',$textdomain);
    }
    else {
      _e('Import guestbook entries from other plugins',$textdomain);
    }
  ?></h2>
	
	<?php
		if ($_REQUEST['msg'] || $showMsg) {
			if ($_REQUEST['msg'] == 'no-guestbook-chosen') {
				$msgClass = 'error';
			}
			else {
				$msgClass = 'updated';
			}
			echo '<div id="message" class="' . $msgClass . ' fade"><p>';
        $msg['no-guestbook-chosen']   = __("You haven't chosen a guestbook. Please select one and try again.",$textdomain);
        $msg['no-entries-to-import']  = __("<strong>Nothing to import.</strong> The guestbook you've chosen does not contain any entries.",$textdomain);
        if ($_REQUEST['count'] == 1) {
          $msg['import-successful']   = __('One entry imported successfully.',$textdomain);
        }
        else {
          $msg['import-successful']   = str_replace('%1',$_REQUEST['count'],__('%1 entries imported successfully.',$textdomain));
        }
				echo $msg[$_REQUEST['msg']];
				echo $msg[$showMsg];
			echo '</p></div>';
		}
		
		
		if ($_REQUEST['what'] == 'dmsguestbook') {
		  //  Does the table of DMSGuestbook exist?
		  $result = mysql_query("
      SHOW
      TABLES
      LIKE '".$wpdb->prefix."dmsguestbook'");
      $foundTables = mysql_fetch_array($result);
      if ($foundTables[0] === $wpdb->prefix.'dmsguestbook') {
		  
      ?>
    	<form action="admin.php?<?php echo $_REQUEST['page']; ?>&amp;do=import&amp;what=dmsguestbook" method="POST">
      	<?php
          //  Get the DMSGuestbook options from database
          $page_id_starttag = '<page_id>';
          $page_id_endtag = '</page_id>';
          $dmsguestbook_options = str_replace('\r\n','',get_option('DMSGuestbook_options'));
          //  Get the start position of the '<page_id>' tag
          $page_id_startposition = strpos($dmsguestbook_options, $page_id_starttag);
          //  Get the start position of the closing tag
          $page_id_endposition = strpos($dmsguestbook_options, $page_id_endtag);
          //  Try to get the page ids
          $page_id_string = substr($dmsguestbook_options, strlen($page_id_starttag), ($page_id_endposition-$page_id_startposition));
          
          $page_ids = explode(',',$page_id_string);
          
          if (count($page_ids) === 0 || $page_id_string == 0) {
            //  No guestbooks detected.
            echo '<div style="margin-bottom:20px;">'.__("Sorry, but I wasn't able to determine the pages at which your guestbook was displayed. You cannot choose the guestbook to import from.",$textdomain).'</div>';
            echo '<input type="hidden" name="import-all" value="true">';
            //  Get entry count
            $result = mysql_query("
            SELECT
              id
            FROM
              ".$wpdb->prefix."dmsguestbook
            ");
            echo '<div style="margin-bottom:10px;font-weight:bold;">'.str_replace('%1',mysql_num_rows($result),__("%1 entries were found and will be imported.",$textdomain)).'</div>';
          }
          else {
            echo '<div>'.str_replace('%1', count($page_ids), __('I was able to find %1 configured DMSGuestbooks. Please choose the guestbook you want to import entries from.',$textdomain)).'</div>';
          ?>
            <table style="margin-top:15px;margin-bottom:15px;" class="widefat">
          		<thead>
          			<tr>
                  <th scope="col" >&nbsp;</th>
          				<th scope="col" ><?php _e('Page title',$textdomain); ?></th>
          				<th scope="col" ><?php _e('Number of guestbook entries',$textdomain); ?></th>
          			</tr>
          		</thead>
          		
          		<tbody>
          		  <?php
          		    for ($i=0; $i<count($page_ids); $i++) {
          		      $guestbook_post = get_post($page_ids[$i]);
          		      
          		      //  Get entry count for this guestbook
          		      $result = mysql_query("
          		      SELECT
          		        COUNT(id) AS entry_count
          		      FROM
          		        ".$wpdb->prefix."dmsguestbook
          		      WHERE
          		        guestbook = ".$i."
          		      GROUP BY
          		        guestbook
          		      ");
          		      $data = mysql_fetch_array($result);
          		      $entry_count = ($data !== FALSE) ? $data['entry_count'] : 0;
          		      
          		      echo '<tr>';
          		        echo '<td><input type="radio" name="guestbook_number" value="'.$i.'"></td>';
          		        echo '<td>'.$guestbook_post->post_title.'</td>';
          		        echo '<td>'.$entry_count.' (<a href="admin.php?page=Entries&guestbook='.$i.'" title="'.__('Click here to view the entries of this guestbook...',$textdomain).'">'.__('Review entries',$textdomain).' &raquo;</a>)</td>';
          		      echo '</tr>';
          		    }
          		  ?>
          		</tbody>
            </table>
          <?php
          }
        ?>
        
        <div>
          <?php _e('The importer will preserve the following data per entry:',$textdomain); ?>
          <ul style="list-style-type:disc;padding-left:15px;">
            <li><?php _e('Name',$textdomain); ?></li>
            <li><?php _e('E-Mail address',$textdomain); ?></li>
            <li><?php _e('URL/Website',$textdomain); ?></li>
            <li><?php _e('Date of the entry',$textdomain); ?></li>
            <li><?php _e('IP address',$textdomain); ?></li>
            <li><?php _e('Message',$textdomain); ?></li>
            <li><?php _e('"is spam" flag',$textdomain); ?></li>
            <li><?php _e('"is checked" flag',$textdomain); ?></li>
          </ul>
          <?php _e('However, data such as HTML formating and gravatars are not supported by Gwolle-GB and <strong>will not</strong> be imported.',$textdomain); ?>
          <br>
          <?php _e('The importer does not delete any data, so you can go back whenever you want.<br>Please start the import by pressing "Start import".',$textdomain); ?>
        </div>
        
        <p style="text-align:center;margin-top:10px;">
          <input name="start_import" type="submit" value="<?php _e('Start import',$textdomain); ?>">
        </p>
      
      </form>
    
    <?php
      }
      else {
        //  Table of DMSGuestbook does not exist.
        _e("I'm sorry, but I wasn't able to find the table of DMSGuestbook. Please check your MySQL database and try again.",$textdomain);
      }
    }
    else {
      //  User did not choose a plugin to import entries from.
    ?>
      <div style="margin-top:10px;margin-bottom:10px;">
        <?php _e("You may want to import entries from another plugin. Click on the plugin's name to get more details on the import.",$textdomain); ?>
      </div>
      
      <strong><?php _e('Supported plugins:',$textdomain); ?></strong>
      <ul style="list-style-type:disc;padding-left:25px;margin-top:5px;">
    <?php
      //  Check if the 'dmsguestbook' table exists
      $result = mysql_query("
      SHOW
      TABLES
      LIKE '".$wpdb->prefix."dmsguestbook'");
      $foundTables = mysql_fetch_array($result);
      if ($foundTables[0] === $wpdb->prefix.'dmsguestbook') {
        echo '<li><a href="admin.php?page=gwolle-gb/gwolle-gb.php&amp;do=import&amp;what=dmsguestbook">DMSGuestbook</a></li>';
      }
      else {
        //  DMSGuestbook table could not be found, so we can't import from it.
        echo '<li>DMSGuestbook ('.str_replace('%1',$wpdb->prefix.'dmsguestbook',__('Table %1 not found.',$textdomain)).')</li>';
      }
    ?>
      </ul>
    <?php  
    }
    ?>
	
</div>