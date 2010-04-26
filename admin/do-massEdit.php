<?php
	/*
	**	This script handles the mass edit feature.
	*/
	
	//	What's our mission?
	if ($_POST['massEditAction1'] == -1) { $massEditAction = $_POST['massEditAction2']; } else { $massEditAction = $_POST['massEditAction1']; }
	//	Include the function we're going to use
	if ($massEditAction == 'delete') {
		include('delete_entry.func.php');
	}
	elseif ($massEditAction == 'spam' || $massEditAction == 'no-spam') {
		include('spam.func.php');
	}
	elseif ($massEditAction == 'check' || $massEditAction == 'uncheck') {
		include('check_entry.func.php');
	}
	else {
		//	No mass edit action selected
		header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=no-massEditAction-selected&show=' . $_REQUEST['show']);
		exit;
	}
	
	//	Get the checkboxes that are checked.
	$entriesEdited = 0;
	foreach(array_keys($_POST) as $postElementName) {
		if (strpos($postElementName, 'check') > -1 && !strpos($postElementName, '-all-') && $_POST[$postElementName] == 'on') {
			$entry_id = str_replace('check-','',$postElementName);
			if ($massEditAction == 'delete' && delete_gwolle_gb_entry($entry_id)) {
				$entriesEdited++;
			}
			elseif ($massEditAction == 'spam' && spam_gwolle_gb_entry($entry_id)) {
				$entriesEdited++;
			}
			elseif ($massEditAction == 'no-spam' && spam_gwolle_gb_entry($entry_id, 'no-spam')) {
				$entriesEdited++;
			}
			elseif ($massEditAction == 'check' && check_gwolle_gb_entry($entry_id)) {
				$entriesEdited++;
			}
			elseif ($massEditAction == 'uncheck' && check_gwolle_gb_entry($entry_id, 'uncheck')) {
				$entriesEdited++;
			}
		}
	}
	
	//	Redirect
	if ($_REQUEST['show']) { $show = '&show=' . $_REQUEST['show']; }
	
	if ($entriesEdited > 0) {	$msg = 'successfully-edited'; $count = '&count=' . $entriesEdited; }
	else { $msg = 'no-entries-edited'; }
	header('Location: ' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=gwolle-gb/entries.php&msg=' . $msg . $show . $count);
	exit;
?>