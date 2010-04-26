<?php
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	// Load settings, if not set
	global $gwolle_gb_settings;
	if (!isset($gwolle_gb_settings)) {
    include_once(WP_PLUGIN_DIR.'/gwolle-gb/functions/gwolle_gb_get_settings.func.php');
    gwolle_gb_get_settings();
  }
	
	$output .= '<div><a href="' . $gb_link . 'gb_page=read">&laquo; ' . __('Back to the entries.',$textdomain) . '</a></div>';	

	//	Has there been an error?
	if (!$resp->is_valid && $_POST && $gwolle_gb_settings['recaptcha-active'] === TRUE) {
		$error[] = __('The captcha has not been entered correctly.',$textdomain);
		$error_captcha = true;
	}
	
	if (strlen(str_replace(' ','',$_POST['entry_author_name'])) == 0 && $_POST) {
		$error[] = __('Please enter a name.',$textdomain);
		$error_name = true;
	}
	if (strlen(str_replace(' ','',$_POST['entry_content'])) == 0 && $_POST) {
		$error[] = __('Please write an entry.',$textdomain);
		$error_content = true;
	}
	if (count($error) > 0) {
		$output .= '<div id="error_msg">';
			for ($i=0; $i<count($error); $i++) {
				if ($i>0) { $output .= '<br />'; }
				$output .= $error[$i];
			}
		$output .= '</div>';
	}
	//	Form for submitting new entries
  $output .= '
  <form id="new_entry" style="text-align:left;" action="'.$gb_link.'gb_page=write" accept-charset="UTF-8" method="POST">
		<input type="hidden" name="gb_link" id="gb_link" value="'.$gb_link.'">
		<div class="label">'.__('Name',$textdomain).':*</div>
		<div class="input"><input class="'; if ($error_name) { $output .= ' error'; } $output .= '" value="'.$_POST['entry_author_name'].'" type="text" name="entry_author_name" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Origin',$textdomain).':</div>
		<div class="input"><input value="'.$_POST['entry_author_origin'].'" type="text" name="entry_author_origin" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('E-Mail',$textdomain).':</div>
		<div class="input"><input value="'.$_POST['entry_author_email'].'" type="text" name="entry_author_email" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Homepage',$textdomain).':</div>
		<div class="input"><input value="'.$_POST['entry_author_website'].'" type="text" name="entry_author_website" /></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label">'.__('Guestbook entry',$textdomain).':*</div>
		<div class="input"><textarea name="entry_content" class="';  if ($error_content) { $output .= ' error'; } $output .= '">'.$_POST['entry_content'].'</textarea></div>
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
	
	<div id="notice">'.
    __('Fields marked with * are obligatory.',$textdomain).
    '<br />'.
    str_replace('%1',$_SERVER['REMOTE_ADDR'],__('For security reasons we save you ip address <span id="ip">%1</span>.',$textdomain)).
		'<br />';
		if ($gwolle_gb_settings['moderate-entries'] === TRUE) {
		  $output .= __('Your entry will be visible in the guestbook when we reviewed it and gave our permission.',$textdomain).'&nbsp;';
		}
		$output .=
		__('We reserve our right to shorten, delete, or not publish entries.',$textdomain).
  '</div>';