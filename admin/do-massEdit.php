<?php
	/*
	 * This script handles the mass edit feature.
	 */

	// What's our mission?
	if ($_POST['massEditAction1'] == -1) {
		$massEditAction = $_POST['massEditAction2'];
	} else {
		$massEditAction = $_POST['massEditAction1'];
	}
	// Include the function we're going to use
	if ($massEditAction == 'trash' || $massEditAction == 'untrash' ||
		$massEditAction == 'spam' || $massEditAction == 'no-spam' ||
		$massEditAction == 'check' || $massEditAction == 'uncheck') {
		// echo, do nothing...  FIXME later

	} else {
		// No mass edit action selected
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&msg=no-massEditAction-selected&show=' . $_REQUEST['show']);
		exit;
	}

	// Get the checkboxes that are checked.
	$entriesEdited = 0;
	foreach(array_keys($_POST) as $postElementName) {
		if (strpos($postElementName, 'check') > -1 && !strpos($postElementName, '-all-') && $_POST[$postElementName] == 'on') {
			$entry_id = str_replace('check-','',$postElementName);
			if ($massEditAction == 'trash' && gwolle_gb_trash_entry(array('entry_id' => $entry_id)) === TRUE) {
				$entriesEdited++;
			} elseif ($massEditAction == 'untrash' && gwolle_gb_trash_entry(array('entry_id' => $entry_id, 'untrash' => TRUE)) === TRUE) {
				$entriesEdited++;
			} elseif ($massEditAction == 'spam' && gwolle_gb_mark_spam(array('entry_id' => $entry_id)) === TRUE) {
				$entriesEdited++;
			} elseif ($massEditAction == 'no-spam' && gwolle_gb_mark_spam(array('entry_id' => $entry_id, 'no_spam' => TRUE)) === TRUE) {
				$entriesEdited++;
			} elseif ($massEditAction == 'check' && gwolle_gb_check_entry(array('entry_id' => $entry_id)) === TRUE) {
				$entriesEdited++;
			} elseif ($massEditAction == 'uncheck' && gwolle_gb_check_entry(array('entry_id' => $entry_id, 'uncheck' => TRUE)) === TRUE) {
				$entriesEdited++;
			}
		}
	}

	// Redirect
	if ($_REQUEST['show']) { $show = '&show=' . $_REQUEST['show']; }

	if ($entriesEdited > 0) {
		$msg = 'successfully-edited';
		$count = '&count=' . $entriesEdited;
	} else {
		$msg = 'no-entries-edited';
	}
	header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page='.GWOLLE_GB_FOLDER.'/entries.php&msg=' . $msg . $show . $count);
	exit;
?>