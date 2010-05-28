<?php
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	// Get links to guestbook page
	include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_link.func.php');
	$gb_links = gwolle_gb_get_link(array(
    'all' => TRUE
  ));
  
  //  Link to the 'read' page
  $output .= '
	<div>
    <a target="_self" href="'.$gb_links['read'].'">&laquo; ' . __('Back to the entries.',$textdomain) . '</a>
  </div>';
  
  //  Parse data that has been stored in the $_SESSION
  $name     = '';
  $origin   = '';
  $email    = '';
  $website  = '';
  $content  = '';
  if (isset($_SESSION['gwolle_gb']['entry'])) {
    foreach($_SESSION['gwolle_gb']['entry'] as $key => $value) {
      if ($key !== 'is_spam') {
        $$key = $value;
      }
    }
  }
  
  if (isset($_SESSION['gwolle_gb']['error_messages'])) {
    $error_messages = $_SESSION['gwolle_gb']['error_messages'];
    $output .= '
    <div id="error_msg">
      <strong>'.__('There were errors submitting your guestbook entry.', $textdomain).'</strong>
      <ul>';
        foreach($error_messages as $error) {
          $output .= '
          <li>'.$error.'</li>';
        }
    $output .= '
      </ul>
    </div>';
  }
  
  $error_fields = array();
  if (isset($_SESSION['gwolle_gb']['error_fields'])) {
    $error_fields = $_SESSION['gwolle_gb']['error_fields'];
  }
  
	//	Form for submitting new entries
  $output .= '
  <form id="new_entry" style="text-align:left;" action="'.$gb_links['write'].'" accept-charset="UTF-8" method="POST">
		<input type="hidden" name="gb_link" id="gb_link" value="'.$gb_links['plain'].'">
		<input type="hidden" name="gwolle_gb_function" value="add_entry" />
		<div class="label">'.__('Name',$textdomain).':*</div>
		<div class="input"><input class="'; if (in_array('name', $error_fields)) { $output .= ' error'; } $output .= '" value="'.$name.'" type="text" name="entry_author_name" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Origin',$textdomain).':</div>
		<div class="input"><input value="'.$origin.'" type="text" name="entry_author_origin" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('E-Mail',$textdomain).':</div>
		<div class="input"><input value="'.$email.'" type="text" name="entry_author_email" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Homepage',$textdomain).':</div>
		<div class="input"><input value="'.$website.'" type="text" name="entry_author_website" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Guestbook entry',$textdomain).':*</div>
		<div class="input"><textarea name="entry_content" class="'; if (in_array('content', $error_fields)) { $output .= ' error'; } $output .= '">'.$content.'</textarea></div>
		<div class="clearBoth">&nbsp;</div>';
	
	  if ($gwolle_gb_settings['recaptcha-active'] === TRUE) {
	    $output .= '
		  <div class="label">&nbsp;</div>
		  <div class="input">';
		  
  		  if (!function_exists('recaptcha_get_html')) {
  				/*
  				**	If function recaptcha_get_html already exists
  				**	the reCAPTCHA library has been included by another
  				**	plugin. Don't load it now since it would result in an error.
  				*/
  				require_once('recaptcha/recaptchalib.php');
  			}
  			$publickey = get_option('recaptcha-public-key');
  			$output .=
  			recaptcha_get_html($publickey).'
		  </div>
		  <div class="clearBoth">&nbsp;</div>';
		}
		
		$output .= '
		<div class="label">&nbsp;</div>
		<div class="input"><input type="submit" value="'.__('Submit',$textdomain).'" /></div>
		<div class="clearBoth">&nbsp;</div>
	</form>
	
	<div id="notice">
    '.__('Fields marked with * are obligatory.',$textdomain).'
    <br />
    '.str_replace('%1',$_SERVER['REMOTE_ADDR'],__('For security reasons we save you ip address <span id="ip">%1</span>.',$textdomain)).'
		<br />';
		if ($gwolle_gb_settings['moderate-entries'] === TRUE) {
		  $output .= '
		  '.__('Your entry will be visible in the guestbook when we reviewed it and gave our permission.',$textdomain).'&nbsp;';
		}
		$output .= '
		'.__('We reserve our right to shorten, delete, or not publish entries.',$textdomain).'
  </div>';