var gwolle_gb_ajax_url;

jQuery(document).ready(function($) {
  gwolle_gb_ajax_url = gwolle_gb_plugin_url + '/admin/ajax.php';
	var current_entry_id = false;
	
	//	Display the "check all"-checkboxes
	$("input[name^='check-all-']").css('display','inline');
	
	$("input[name='check-all-top']").change(function() {
		toogleCheckboxes($("input[name='check-all-top']").is(":checked"));
	});
	
	$("input[name='check-all-bottom']").change(function() {
		toogleCheckboxes($("input[name='check-all-bottom']").is(":checked"));
	});
	
	//	Function to check/uncheck all checkboxes.
	function toogleCheckboxes(checkAll_checked) {
		$("input[name^='check-']").attr("checked", checkAll_checked);
	}
  
  /*
  //  Change mouse cursor over entry rows
  $('tr[id^="entry_"]').css('cursor', 'help');
  //  Function to toggle quick edit rows
  $('tr[id^="entry_"]').click(function() {
    //  TODO: Check if there's already an editor open
    
    //  Get the plain entry id
    current_entry_id = $(this).attr('id').replace(/entry_/,'');
    
    //  Display quick edit row
    $('#quickedit_'+current_entry_id).css('display', 'table-row');
    
    //  Hide entry row
    $('#entry_'+current_entry_id).css('display', 'none');
  });
  */
  
  //  Approve entries by clicking on the icon
  $('td[class$="checked"]').click(function() {
    //  Only one entry per time
    if (current_entry_id !== false) {
      return false;
    }
    
    var current_state;
    var new_state;
    if ($(this).hasClass('entry-checked')) {
      current_state = 'checked';
      new_state = 'unchecked';
    } else {
      current_state = 'unchecked';
      new_state = 'checked';
    }
    
    //  Warn if this is spam
    if (new_state == 'checked' && $(this).parent().hasClass('spam') && !confirm(stripslashes(gwolle_gb_strings.warning_spam))) {
      return false;
    }
    
    //  ID of the entry
    current_entry_id = $(this).parent().attr('id').replace(/entry_/,'');
    
    //  Loading graphic
    $(this).removeClass('entry-'+current_state).addClass('entry-loading');
    
    var icon_column = $(this);
    
    $.ajax({
      type: 'POST',
      url: gwolle_gb_ajax_url,
      data: ({
        func:       'set_entry_checked_state',
        id:         current_entry_id,
        new_state:  new_state
      }),
      success: function(data) {
        if (data == 'success') {
          //  Change the loading graphic to the entry state icon
          icon_column.removeClass('entry-loading').addClass('entry-'+new_state);
          
          //  Set the current_entry_id to false
          current_entry_id = false;
        }
        else {
          alert('Error: '+data);
        }
      }
    });
  });
  
  /*
  //  Mark entries as 'not spam' by clicking on the icon
  $('img.spam').click(function(event) {
    if (confirm(gwolle_gb_strings.warning_marking_not_spam)) {
      $(this).attr('src',gwolle_gb_plugin_url+'/admin/gfx/loading.gif');
    }
    event.preventDefault();
  });
  */

});