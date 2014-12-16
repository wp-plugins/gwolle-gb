

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
		save_postboxes_state(page);
	} );
} );


