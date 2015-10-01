<?php
/*
 * Page that is shown to the user when the plugin was not correctly installed.
 * This is just a fallback in case something went wrong somewhere.
 * Maybe it just needs to be removed alltogether.
 */


function gwolle_gb_installSplash() {
	?>
	<div class="wrap">
		<div id="icon-gwolle-gb"><br /></div>
		<h1>Gwolle-GB &#8212;
			<?php _e('Installation','gwolle-gb'); ?>
		</h1>

		<div>
			<?php
			if ( !isset($_REQUEST['install_gwolle_gb']) || $_REQUEST['install_gwolle_gb'] != 'install_gwolle_gb') {
				_e('Welcome!<br>It seems that either you\'re using this plugin for the first time or you\'ve deleted all settings.<br>However, to use this plugin we have to setup the database tables. Good for you, we\'ve made this as easy as possible.<br>All you\'ve got to do is click on that button below, and that\'s it.','gwolle-gb');
				?>
				<br /><br />
				<div>
					<form action="<?php echo $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page']; ?>" method="POST">
						<input type="hidden" id="install_gwolle_gb" name="install_gwolle_gb" value="install_gwolle_gb" />
						<input type="submit" class="button button-primary" value="<?php esc_attr_e('Sure, let\'s do this!', 'gwolle-gb'); ?>">
					</form>
				</div>
				<?php
			} elseif ( isset($_REQUEST['install_gwolle_gb']) && $_REQUEST['install_gwolle_gb'] == 'install_gwolle_gb' && !get_option('gwolle_gb_version') ) {
				// perform installation
				gwolle_gb_install();
				echo sprintf( __('Allright, we\'re done. <a href="%s">Click here to continue...</a>', 'gwolle-gb'), $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'] );
			} else {
				echo sprintf( __('It looks like there has been an error. <a href="%s">Click here to continue...</a>', 'gwolle-gb'), $_SERVER['PHP_SELF'] . '?page=' . $_REQUEST['page'] );
			}
			?>
		</div>
	</div>
	<?php
}
