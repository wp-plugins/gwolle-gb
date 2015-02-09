

/*
 * Entries Page
 */

jQuery(document).ready(function($) {

	// Display the "check all"-checkboxes
	$("#gwolle_gb_entries input[name^='check-all-']").css('display','inline');

	$("#gwolle_gb_entries input[name='check-all-top']").change(function() {
		gwolle_gb_toggleCheckboxes($("input[name='check-all-top']").is(":checked"));
	});

	$("#gwolle_gb_entries input[name='check-all-bottom']").change(function() {
		gwolle_gb_toggleCheckboxes($("input[name='check-all-bottom']").is(":checked"));
	});

	// Function to check/uncheck all checkboxes.
	function gwolle_gb_toggleCheckboxes(checkAll_checked) {
		$("input[name^='check-']").attr("checked", checkAll_checked);
	}

});


/* Postbox on every admin page */
jQuery(document).ready(function($) {
	jQuery('.postbox h3').click( function() {
		jQuery(jQuery(this).parent().get(0)).toggleClass('closed');
	} );
} );



/* Export Click Event, submit the form through Ajax and receive a CSV */
jQuery(document).ready(function($) {
	jQuery( 'input#gwolle_gb_start_export' ).click(function(event) {

		// Set up data to send
		var checked = jQuery( "input#gwolle_gb" ).prop('checked');

		if ( checked == true ) {
			form = jQuery('form#gwolle_gb_export');
			form.submit();
		} else {
			alert('Please select the checkbox if you really want to do an export.');
		}

		event.preventDefault();
	});
});


/*
 * Select the right tab on the options page
 *
 */
jQuery(document).ready(function($) {
	jQuery( '.gwolle-nav-tab-wrapper a' ).on('click', function() {

		jQuery( 'form.gwolle_gb_options' ).removeClass( 'active' );
		jQuery( '.gwolle-nav-tab-wrapper a' ).removeClass( 'nav-tab-active' );

		var rel = jQuery( this ).attr('rel');
		jQuery( '.' + rel ).addClass( 'active' );
		jQuery( this ).addClass( 'nav-tab-active' );

		return false;
	});
});
