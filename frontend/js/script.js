/*
 * JavaScript for Gwolle Guestbook Frontend.
 */


/*
 * Event for clicking the button, and getting the form visible.
 */
jQuery(document).ready(function($) {
	jQuery( "#gwolle_gb_write_button input" ).click(function() {
		document.getElementById("gwolle_gb_write_button").style.display = "none";
		jQuery("#gwolle_gb_new_entry").slideDown(1000);
		return false;
	});
});

