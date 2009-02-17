jQuery(document).ready(function() {
	
	//	Display the "check all"-checkboxes
	jQuery("input[name^='check-all-']").css('display','inline');
	
	jQuery("input[name='check-all-top']").change(function() {
		toogleCheckboxes(jQuery("input[name='check-all-top']").is(":checked"));
	});
	
	jQuery("input[name='check-all-bottom']").change(function() {
		toogleCheckboxes(jQuery("input[name='check-all-bottom']").is(":checked"));
	});
	
	//	Function to check/uncheck all checkboxes.
	function toogleCheckboxes(checkAll_checked) {
		jQuery("input[name^='check-']").attr("checked", checkAll_checked);
	}


});