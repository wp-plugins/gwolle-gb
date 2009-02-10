<?php
	/*
	**	Checks if the currently installed version differs from the 'official latest version'
	*/
	
	//	No direct calls to this script
	//if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('No direct calls allowed!'); }
	
	global $wpdb;
	
	if (function_exists('file') && get_cfg_var('allow_url_fopen')) {
		$versionFile = file('http://www.wolfgangtimme.de/gwolle_gb_version.php?my_version=' . GWOLLE_GB_VER);
		$version = $versionFile[0];
		if (version_compare(GWOLLE_GB_VER,$version,'<')) {
			$newVersion = $version;
		}
	}
	else {
		//	file() is not supported by this installation. Turn the versionCheck off.
		update_option('gwolle_gb-autoCheckVersion','false');
	}
	
?>