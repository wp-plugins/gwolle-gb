<?php
	/*
	**	Page that is shown to the user when the plugin's not installed.
	*/
	global $textdomain;
?>

<div class="wrap">
	<div id="icon-gwolle-gb"><br /></div>
	<h2>Gwolle-GB &#8212; <?php if ($_REQUEST['msg'] == 'successfully-uninstalled') { _e('Successfully uninstalled',$textdomain); } else { _e('Installation',$textdomain); } ?></h2>
	
	<div>
		<?php
			if ($_REQUEST['do'] != 'install_gwolle_gb' && $_REQUEST['msg'] != 'successfully-uninstalled') {
				_e('Welcome!<br>It seems that either you\'re using this plugin for the first time or you\'ve deleted all settings.<br>However, to use this plugin we have to setup the database tables. Good for you, we\'ve made this as easy as possible.<br>All you\'ve got to do is click on that button below, and that\'s it.',$textdomain);
		?>
				<br><br>
				<div style="text-align:center;">
					<form action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'] . '&amp;do=install_gwolle_gb'; ?>" method="POST">
						<input type="submit" class="button" value="<?php _e('Sure, let\'s do this!',$textdomain); ?>">
					</form>
				</div>
		<?php
			}
			elseif ($_REQUEST['msg'] == 'successfully-uninstalled') {
				_e('You successfully uninstalled Gwolle-GB.',$textdomain);
				echo '<br><br>';
				echo str_replace('%1','plugins.php',__('You now may deactivate the plugin using the <a href="%1" title="Go to the plugin manager...">plugin manager</a> or/and delete the plugin from your webserver.',$textdomain));
				echo '<br>';
				echo str_replace('%1','http://wolfgangtimme.de/blog/gwolle-gb/',__('If you like feel free to drop a message at the <a href="%1" title="Go to the Gwolle-GB homepage..." target="_blank">Gwolle-GB homepage</a> regarding why you choosed not to use this plugin anymore; I\'d really appreciate that.',$textdomain));
			}
			elseif ($_REQUEST['do'] == 'install_gwolle_gb' && !get_option('gwolle_gb_version')) {
				//	perform installation
				include(WP_PLUGIN_DIR.'/gwolle-gb/admin/upgrade.php');
				install_gwolle_gb();
				echo str_replace('%1',$_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'], __('Allright, we\'re done. <a href="%1">Click here to continue...</a>',$textdomain));
			}
			else {
				echo str_replace('%1',$_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'], __('It looks like there has been an error. <a href="%1">Click here to continue...</a>',$textdomain));
			}
		?>
	</div>
</div>