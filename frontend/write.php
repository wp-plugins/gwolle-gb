<?php
	//	No direct calls to this script
	if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	echo '<div><a href="' . $gb_link . 'gb_page=read">&laquo; ' . __('Back to the entries.',$textdomain) . '</a></div>';	

	//	Has there been an error?
	if (!$resp->is_valid && $_POST && get_option('gwolle_gb-recaptcha-active') == 'true') {
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
		echo '<div id="error_msg">';
			for ($i=0; $i<count($error); $i++) {
				if ($i>0) { echo '<br>'; }
				echo $error[$i];
			}
		echo '</div>';
	}
	//	Form for submitting new entries
?>
	<form id="new_entry" style="text-align:left;" action="<?php echo $gb_link; ?>gb_page=write" accept-charset="UTF-8" method="POST">
		<input type="hidden" name="gb_link" id="gb_link" value="<?php echo $gb_link; ?>">
		<div class="label"><?php _e('Name',$textdomain); ?>:*</div>
		<div class="input"><input class="<?php if ($error_name) { echo ' error'; } ?>" value="<?php echo $_POST['entry_author_name']; ?>" type="text" name="entry_author_name"></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label"><?php _e('Origin',$textdomain); ?>:</div>
		<div class="input"><input value="<?php echo $_POST['entry_author_origin']; ?>" type="text" name="entry_author_origin"></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label"><?php _e('E-Mail',$textdomain); ?>:</div>
		<div class="input"><input value="<?php echo $_POST['entry_author_email']; ?>" type="text" name="entry_author_email"></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label"><?php _e('Homepage',$textdomain); ?>:</div>
		<div class="input"><input value="<?php echo $_POST['entry_author_website']; ?>" type="text" name="entry_author_website"></div>
		<div class="clearBoth">&nbsp;</div>
		
		<div class="label"><?php _e('Guestbook entry',$textdomain); ?>:*</div>
		<div class="input"><textarea name="entry_content" class="<?php if ($error_content) { echo ' error'; } ?>"><?php echo $_POST['entry_content']; ?></textarea></div>
		<div class="clearBoth">&nbsp;</div>
		
		<?php
			if (get_option('gwolle_gb-recaptcha-active') == 'true') {
		?>
				<div class="label">&nbsp;</div>
				<div class="input">
					<?php
						if (!function_exists('recaptcha_get_html')) {
							/*
							**	If function recaptcha_get_html already exists
							**	the reCAPTCHA library has been included by another
							**	plugin. Don't load it now 'til it would result in an error.
							*/
							require_once('recaptcha/recaptchalib.php');
						}
						$publickey = get_option('recaptcha-public-key');
						echo recaptcha_get_html($publickey);
					?>
				</div>
				<div class="clearBoth">&nbsp;</div>
		<?php
			}
		?>
		
		<div class="label">&nbsp;</div>
		<div class="input"><input type="submit" value="<?php _e('Submit',$textdomain); ?>"></div>
		<div class="clearBoth">&nbsp;</div>
	</form>
	
	<div id="notice">
		<?php
			_e('Fields marked with * are obligatory.',$textdomain);
			echo '<br>';
			echo str_replace('%1',$_SERVER['REMOTE_ADDR'],__('For security reasons we save you ip address <span id="ip">%1</span>.',$textdomain));
			echo '<br>';
			if (get_option('gwolle_gb-moderate-entries') == 'true') {
				_e('Your entry will be visible in the guestbook when we reviewed it and gave our permission.',$textdomain);
				echo '&nbsp;';
			}
			_e('We reserve our right to shorten, delete, or not publish entries.',$textdomain);
		?>
	</div>