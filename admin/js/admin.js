


/* Postbox on every admin page */
jQuery(document).ready(function($) {
	jQuery('.gwolle_gb .postbox h3').click( function() {
		jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
	});
});


/*
 * Entries Page
 */
jQuery(document).ready(function($) {

	jQuery("#gwolle_gb_entries input[name='check-all-top']").change(function() {
		gwolle_gb_toggleCheckboxes($("input[name='check-all-top']").is(":checked"));
	});

	jQuery("#gwolle_gb_entries input[name='check-all-bottom']").change(function() {
		gwolle_gb_toggleCheckboxes($("input[name='check-all-bottom']").is(":checked"));
	});

	// Function to check/uncheck all checkboxes.
	function gwolle_gb_toggleCheckboxes(checkAll_checked) {
		jQuery("input[name^='check-']").attr("checked", checkAll_checked);
	}

});


/*
 * Editor page
 */

/* Edit metadata */
jQuery(document).ready(function($) {
	jQuery('.gwolle_gb_edit_meta').click( function() {
		jQuery('.gwolle_gb_edit_meta_inputs').toggle();
		return false;
	});

	jQuery('.gwolle_gb_cancel_timestamp').click( function() {
		jQuery('.gwolle_gb_edit_meta_inputs').toggle();
		return false;
	});

	jQuery('.gwolle_gb_save_timestamp').click( function() {

		var dd = jQuery("#dd").val();
		var mm = jQuery("#mm").find(":selected").val();
		var yy = jQuery("#yy").val();
		var hh = jQuery("#hh").val();
		var mn = jQuery("#mn").val();

		var gwolle_date = new Date( yy, mm - 1, dd, hh, mn );
		// Calculate offset between UTC and local time, and adjust our time.
		date_offset = gwolle_date.getTimezoneOffset() * -60;
		var timestamp = Math.round( gwolle_date.getTime() / 1000 ) + date_offset;
		jQuery("#gwolle_gb_timestamp").val(timestamp);

		jQuery('.gwolle_gb_edit_meta_inputs').toggle();
		return false;
	});
});


/*
 * Settings Page
 */
jQuery(document).ready(function($) {

	/* Select the right tab on the options page */
	jQuery( '.gwolle-nav-tab-wrapper a' ).on('click', function() {
		jQuery( 'form.gwolle_gb_options' ).removeClass( 'active' );
		jQuery( '.gwolle-nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );

		var rel = jQuery( this ).attr('rel');
		jQuery( '.' + rel ).addClass( 'active' );
		jQuery( this ).addClass( 'nav-tab-active' );

		return false;
	});


	/* Checking checkbox will enable the uninstall button */
	jQuery("input#gwolle_gb_uninstall_confirmed").attr("checked", false); // init

	jQuery("input#gwolle_gb_uninstall_confirmed").change(function() {
		var checked = jQuery( "input#gwolle_gb_uninstall_confirmed" ).prop('checked');
		if ( checked == true ) {
			jQuery("#gwolle_gb_uninstall").addClass( 'button-primary' );
			jQuery("#gwolle_gb_uninstall").removeAttr('disabled');
		} else {
			jQuery("#gwolle_gb_uninstall").removeClass( 'button-primary' );
			jQuery("#gwolle_gb_uninstall").attr('disabled', true);
		}
	});

});


/*
 * Import Page
 */
jQuery(document).ready(function($) {

	/* Checking checkbox will enable the submit button for DMS import */
	jQuery("input#gwolle_gb_dmsguestbook").attr("checked", false); // init

	jQuery("input#gwolle_gb_dmsguestbook").change(function() {
		var checked = jQuery( "input#gwolle_gb_dmsguestbook" ).prop('checked');
		if ( checked == true ) {
			jQuery("#start_import_dms").addClass( 'button-primary' );
			jQuery("#start_import_dms").removeAttr('disabled');
		} else {
			jQuery("#start_import_dms").removeClass( 'button-primary' );
			jQuery("#start_import_dms").attr('disabled', true);
		}
	});


	/* Checking radio-buttons will enable the submit button for Gwolle import */
	jQuery("input#gwolle_gb_importfrom").attr("checked", false); // init

	jQuery("input#gwolle_gb_importfrom").change(function() {
		if ( jQuery(this).val() ) {
			jQuery("#start_import_wp").addClass( 'button-primary' );
			jQuery("#start_import_wp").removeAttr('disabled');
		} else {
			jQuery("#start_import_wp").removeClass( 'button-primary' );
			jQuery("#start_import_wp").attr('disabled', true);
		}
	});


	/* Checking checkbox will enable the submit button for CSV-file */
	jQuery("input#start_import_gwolle_file").change(function() {
		if ( jQuery(this).val() ) {
			jQuery("#start_import_gwolle").addClass( 'button-primary' );
			jQuery("#start_import_gwolle").removeAttr('disabled');
		} else {
			jQuery("#start_import_gwolle").removeClass( 'button-primary' );
			jQuery("#start_import_gwolle").attr('disabled', true);
		}
	});

});


/*
 * Export Page
 */
jQuery(document).ready(function($) {

	/* Checking checkbox will enable the submit button */
	jQuery("input#start_export_enable").attr("checked", false); // init

	jQuery("input#start_export_enable").change(function() {
		var checked = jQuery( "input#start_export_enable" ).prop('checked');
		if ( checked == true ) {
			jQuery("#gwolle_gb_start_export").addClass( 'button-primary' );
			jQuery("#gwolle_gb_start_export").removeAttr('disabled');
		} else {
			jQuery("#gwolle_gb_start_export").removeClass( 'button-primary' );
			jQuery("#gwolle_gb_start_export").attr('disabled', true);
		}
	});


	/* Click Event, submit the form through AJAX and receive a CSV-file */
	jQuery( 'input#gwolle_gb_start_export.button-primary' ).click(function(event) {

		form = jQuery('form#gwolle_gb_export');
		form.submit();

		event.preventDefault();
	});
});

