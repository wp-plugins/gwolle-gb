<?php
/**
 * msg.php
 * Displays a message (error/updated) according on the $_REQUEST['msg'] variable.
 */
$msg = (isset($_REQUEST['msg'])) ? $_REQUEST['msg'] : FALSE;

if (isset($_REQUEST['updated'])) {
	$msg = ($_REQUEST['updated'] == 'true') ? 'changes-saved' : 'no-changes-made';
} elseif (isset($_REQUEST['error'])) {
	$msg = 'error-saving';
} elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == GWOLLE_GB_FOLDER . '/editor.php' && isset($_SESSION['gwolle_gb']['error_messages'])) {
	$msg = 'error-adding-admin-entry';
} elseif (isset($_SESSION['gwolle_gb']['msg'])) {
	$msg = $_SESSION['gwolle_gb']['msg'];
	unset($_SESSION['gwolle_gb']['msg']);
} elseif (isset($_REQUEST['page']) && $_REQUEST['page'] == GWOLLE_GB_FOLDER . '/entries.php' && isset($_REQUEST['entry_id'])) {
	$msg = 'admin-entry-added';
}

$entry_id = 0;
if (isset($_SESSION['gwolle_gb']['entry_id'])) {
	$entry_id = $_SESSION['gwolle_gb']['entry_id'];
	unset($_SESSION['gwolle_gb']['entry_id']);
}

if ($msg !== FALSE) {
	$error_messages = array(
		'check-akismet-configuration' => __('Please check your Akismet configuration.', GWOLLE_GB_TEXTDOMAIN),
		'akismet-not-activated' => str_replace('%1', 'admin.php?page=' . GWOLLE_GB_FOLDER . '/settings.php', __('Please <a href="%1">enable Akismet</a> to use the spam feature of Gwolle-GB.', GWOLLE_GB_TEXTDOMAIN)),
		'no-massEditAction-selected' => __('No mass edit action selected.', GWOLLE_GB_TEXTDOMAIN),
		'error-trashing' => __('An error occured while trying to move the entry to trash.', GWOLLE_GB_TEXTDOMAIN),
		'error-deleting' => __('An error occured while trying to remove the entry from the database.', GWOLLE_GB_TEXTDOMAIN),
		'no-changes-made' => __('<strong>Notice:</strong> No changes were made.', GWOLLE_GB_TEXTDOMAIN),
		'error-saving' => __('Error(s) occurred while saving your changes.', GWOLLE_GB_TEXTDOMAIN),
		'error-adding-admin-entry' => __('Error(s) occurred adding your admin entry.', GWOLLE_GB_TEXTDOMAIN),
		'error-marking-spam' => __('Sorry, I was not able to mark this entry as spam.', GWOLLE_GB_TEXTDOMAIN),
		'error-unmarking-spam' => __('Error marking entry as not spam.', GWOLLE_GB_TEXTDOMAIN)
	);
	$updated_messages = array(
		'successfully-uninstalled' => __('<strong>Gwolle-GB has been successfully uninstalled.</strong> Thanks for using my plugin!', GWOLLE_GB_TEXTDOMAIN),
		'trashed' => str_replace('%1', 'admin.php?page=' . GWOLLE_GB_FOLDER . '/entries.php&gwolle_gb_function=untrash_entry&entry_id=' . $entry_id, __('Entry successfully moved to trash. <a href="%1">Undo</a>', GWOLLE_GB_TEXTDOMAIN)),
		'deleted' => __('Entry successfully removed from the database.', GWOLLE_GB_TEXTDOMAIN),
		'untrashed' => __('Entry successfully recovered from trash.', GWOLLE_GB_TEXTDOMAIN),
		'no-entries-edited' => __('No entries were edited.', GWOLLE_GB_TEXTDOMAIN),
		'changes-saved' => __('Changes saved.', GWOLLE_GB_TEXTDOMAIN),
		'successfully-unmarkedSpam' => __('Entry successfully classified as not-spam.', GWOLLE_GB_TEXTDOMAIN),
		'uninstall-not-confirmed' => __('Uninstall process stopped.', GWOLLE_GB_TEXTDOMAIN),
		'admin-entry-added' => __('Your new admin entry has been added.', GWOLLE_GB_TEXTDOMAIN)
	);

	//  Messages with numbers
	$updated_messages['successfully-edited'] = (isset($_REQUEST['count']) && (int)$_REQUEST['count'] > 1) ? $_REQUEST['count'] . ' ' . __('entries', GWOLLE_GB_TEXTDOMAIN) : __('One entry', GWOLLE_GB_TEXTDOMAIN);
	$updated_messages['successfully-edited'] .= ' ' . __('successfully edited.', GWOLLE_GB_TEXTDOMAIN);

	//  Output
	if (isset($error_messages[$msg])) {
		echo '
			<div id="message" class="error fade">
				<p>' . $error_messages[$msg] . '</p>';
		if (isset($_SESSION['gwolle_gb']['error_messages'])) {
			foreach ($_SESSION['gwolle_gb']['error_messages'] as $error_msg) {
				echo '<div style="display:block;">' . $error_msg . '</div>';
			}
		}
		echo '</div>';
	} elseif (isset($updated_messages[$msg])) {
		echo '
			<div id="message" class="updated fade">
				<p>' . $updated_messages[$msg] . '</p>
			</div>';
	}
}
//  Additional messages
if (isset($entry) && $entry['entry_isSpam'] === 1) {
	//	Notify that this entry is marked as spam.
	echo '
		<div id="message" class="error fade">
			<p>' . __('<strong>Attention:</strong> This entry is marked as spam!', GWOLLE_GB_TEXTDOMAIN) . '</p>
		</div>';
}

