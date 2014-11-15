<?php
/**
 * gwolle_gb_mark_spam
 * Marks an entry as spam/no spam
 */
if (!function_exists('gwolle_gb_mark_spam')) {
	function gwolle_gb_mark_spam($args = array()) {
		global $wpdb;
		global $current_user;

		// Check permission
		if (!current_user_can('moderate_comments')) {
			// The current user's not allowed to do this.
			die(__('Cheatin&#8217; uh?'));
		}

		// Load settings, if not set
		global $gwolle_gb_settings;
		if (!isset($gwolle_gb_settings)) {
			gwolle_gb_get_settings();
		}

		//  Is Askismet activated for the Gwolle-GB plugin?
		if ($gwolle_gb_settings['akismet-active'] === FALSE) {
			$_SESSION['gwolle_gb']['msg'] = 'akismet-not-activated';
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php');
			exit ;
		}

		//  Is the WordPress API key present?
		$wordpress_api_key = get_option('wordpress_api_key');
		if (!$wordpress_api_key) {
			$_SESSION['gwolle_gb']['msg'] = 'check-akismet-configuration';
			header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=' . GWOLLE_GB_FOLDER . '/gwolle-gb.php');
			exit ;
		}

		if (!isset($args['entry_id']) || (int) $args['entry_id'] === 0) {
			return FALSE;
		}
		//  Check if the entry exists
		$entry = gwolle_gb_get_entries_old(array('entry_id' => $args['entry_id']));
		if ($entry === FALSE) {
			return FALSE;
		}

		/**
		 * Check for spam using Akismet, if this has been set in the
		 * settings dialog of Gwolle-GB and if there's a WordPress API key defined.
		 */
		if (get_option('gwolle_gb-akismet-active')) {
			if (class_exists('Akismet')) {
				$args['is_spam'] = 0;
				$wordpressApiKey = get_option('wordpress_api_key');
				if ($gwolle_gb_settings['akismet-active'] === TRUE && strlen($wordpressApiKey) > 0 && !isset($args['bypass_akismet'])) {
					$isspam = gwolle_gb_isspam_akismet_old( $args );
					if ($isspam === true) {
						//	Akismet detected spam.
						$args['is_spam'] = 1;
					}
				}
			}
		}

		// What shall we do with the entry?
		if (isset($args['no_spam']) && $args['no_spam'] === true) {
			//  User says it is no spam, so inform akismet of the false positive.
			if ($args['is_spam'] == 1) {
				// $akismet->submitHam(); // FIXME
			}
			$sql = "
				UPDATE
					" . $wpdb->gwolle_gb_entries . "
				SET
					entry_isSpam = 0
				WHERE
					entry_id = " . (int) $args['entry_id'] . "
				LIMIT 1";
			$result = $wpdb->query($sql);
			if ($result == 1) {
				gwolle_gb_add_log_entry(array('subject' => 'marked-as-not-spam', 'subject_id' => $args['entry_id']));
				return TRUE;
			}
		} else {
			//  User says it is spam, so inform akismet of the false negative.
			if ($args['is_spam'] == 0) {
				//$akismet -> submitSpam(); // FIXME
			}
			$sql = "
				UPDATE
					" . $wpdb->gwolle_gb_entries . "
				SET
					entry_isSpam = 1
				WHERE
					entry_id = " . (int) $args['entry_id'] . "
				LIMIT 1";
			$result = $wpdb->query($sql);
			if ($result == 1) {
				gwolle_gb_add_log_entry(array('subject' => 'marked-as-spam', 'subject_id' => $args['entry_id']));
				return TRUE;
			}
		}
		//  Everything else fails and returns FALSE.
		return FALSE;
	}

}
