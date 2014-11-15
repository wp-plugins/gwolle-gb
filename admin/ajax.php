<?php
/**
 * ajax.php
 * Processes AJAX requests.
 */

// FIXME: Drop AJAX?

//  Set charset to UTF-8
header("Content-Type: text/html; charset=utf-8");

include ('../../../../wp-load.php');

// Load settings, if not set
global $gwolle_gb_settings;
if (!isset($gwolle_gb_settings)) {
	//  In this case, path must be relative because ajax.php is called without the main gwolle-gb.php
	include_once ('../functions/gwolle_gb_get_settings.func.php');
	gwolle_gb_get_settings();
}

if (!function_exists('set_entry_checked_state')) {
	function set_entry_checked_state() {
		global $wpdb;
		global $current_user;
		if (!isset($_POST['id']) || !isset($_POST['new_state'])) {
			return FALSE;
		}
		$entry_id = (int) $_POST['id'];
		if ($entry_id === 0) {
			return FALSE;
		}
		$entry_is_checked = ($_POST['new_state'] == 'checked') ? 1 : 0;
		$sql = "
			  UPDATE
			    " . $wpdb -> gwolle_gb_entries . "
			  SET
			    entry_isChecked = " . $entry_is_checked . "
			  WHERE
			    entry_id = " . $entry_id . "
			  LIMIT 1";
		$result = $wpdb->query($sql);
		if ($result == 1) {
			//	Write a log entry on this.
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_add_log_entry.func.php');
			$log_subject = ($entry_is_checked === 0) ? 'entry-unchecked' : 'entry-checked';
			return gwolle_gb_add_log_entry(array('subject' => $log_subject, 'subject_id' => $entry_id));
			return TRUE;
		}
		return FALSE;
	}

}

include_once (GWOLLE_GB_DIR . '/functions/get_gwolle_gb_post_id.func.php');

global $current_user;
if (is_user_logged_in() && current_user_can('moderate_comments')) {
	if (!isset($_POST['func'])) {
		exit ;
	}
	$function = $_POST['func'];
	switch($function) {
		case 'trash_entry' :
			/**
			 * Echo the HTML of an 'entry has been moved to trash' row
			 * for the dashboard widget of Gwolle-GB.
			 */
			//  Get entry data
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_entries.func.php');
			$entry = gwolle_gb_get_entries(array('entry_id' => $_POST['entry_id']));
			if ($entry === FALSE) {
				echo 'entry-not-found: ' . $_POST['entry_id'];
			} else {
				include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_trash_entry.func.php');
				if (gwolle_gb_trash_entry(array('entry_id' => $_POST['entry_id'])) === FALSE) {
					echo 'entry-not-trashed';
				} else {
					echo '
					<div class="undo untrash" style="display:none;" id="undo-' . $entry['entry_id'] . '">
						<div class="trash-undo-inside">
					    <img width="50" height="50" class="avatar avatar-50 photo avatar-default" src="http://www.gravatar.com/avatar/' . $entry['entry_author_gravatar'] . '?s=50" />
					    ' . str_replace('%1', '<strong>' . $entry['entry_author_name_html'] . '</strong>', __('Entry by %1 has been moved to trash.', GWOLLE_GB_TEXTDOMAIN)) . '
					    <span class="undo untrash">
					      <a id="untrash_entry_' . $_POST['entry_id'] . '" href="javascript:void(0);" class="vim-z vim-destructive">' . __('Undo') . '</a>
					    </span>
					  </div>
					</div>';
				}
			}
			break;
		case 'untrash_entry' :
			/**
			 * Untrash the entry and return the result. (success/error)
			 */
			//  Get entry data
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_entries.func.php');
			$entry = gwolle_gb_get_entries(array('entry_id' => $_POST['entry_id'], 'trash' => TRUE));
			if ($entry === FALSE) {
				echo 'error';
			} else {
				include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_trash_entry.func.php');
				if (gwolle_gb_trash_entry(array('entry_id' => $_POST['entry_id'], 'untrash' => TRUE)) === FALSE) {
					echo 'error';
				} else {
					echo 'success';
				}
			}
			break;
		case 'set_entry_checked_state' :
			if (set_entry_checked_state() === TRUE) {
				echo 'success';
			} else {
				echo 'failure';
			}
			break;
		case 'search_gwolle_gb_post_ID' :
			$gwolle_gb_post_id = get_gwolle_gb_post_id();
			if ($gwolle_gb_post_id === FALSE) {
				echo 'failure';
			} else {
				echo $gwolle_gb_post_id;
			}
			break;
		case 'mark_spam' :
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_mark_spam.func.php');
			if (gwolle_gb_mark_spam(array('entry_id' => $_POST['entry_id'])) === TRUE) {
				echo 'success';
			} else {
				echo 'error';
			}
			break;
		case 'unmark_spam' :
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_mark_spam.func.php');
			if (gwolle_gb_mark_spam(array('entry_id' => $_POST['entry_id'], 'no_spam' => TRUE)) === TRUE) {
				echo 'success';
			} else {
				echo 'error';
			}
			break;
		case 'get_dashboard_widget_row' :
			//  There are only 3 dashboard widget rows left. Load another one.
			include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_entries.func.php');
			$entry = gwolle_gb_get_entries(array('num_entries' => 1, 'offset' => 3));
			if ($entry === FALSE) {
				echo 'error';
			} else {
				include_once (GWOLLE_GB_DIR . '/functions/gwolle_gb_get_dashboard_widget_row.func.php');
				gwolle_gb_get_dashboard_widget_row(array('entry' => $entry, 'display' => 'none'));
			}
			break;
	}
}
?>