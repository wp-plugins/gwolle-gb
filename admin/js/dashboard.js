var gwolle_gb_ajax_url;

jQuery(document).ready(function($) {
  gwolle_gb_ajax_url = gwolle_gb_plugin_url + '/admin/ajax.php';
  
  function refresh_gwolle_gb_ajax() {
    $('div[id^="entry-"]').each(function(intIndex) {
      if ($(this).hasClass('ajaxified') == false) {
        var entry_id = $(this).attr('id').replace(/entry-/,'');
        /**
         * Click at 'Check'
         */
        $('#check_'+entry_id).click(function(event) {
          $('#entry-actions-'+entry_id).hide();
          $('#wait-entry-ajax-'+entry_id).show();
          $.ajax({
            url: gwolle_gb_ajax_url,
            type: 'POST',
            data: ({
              func:       'set_entry_checked_state',
              new_state:  'checked',
              id:         entry_id
            }),
            dataType: 'html',
            success: function(msg){
              if (msg == 'success') {
                $('#entry-'+entry_id).removeClass('unapproved').addClass('approved');
              }
              $('#wait-entry-ajax-'+entry_id).hide();
              $('#entry-actions-'+entry_id).show();
            }
          });
          event.preventDefault();
        });
        
        /**
         * Click at 'Uncheck'
         */
        $('#uncheck_'+entry_id).click(function(event) {
          $('#entry-actions-'+entry_id).hide();
          $('#wait-entry-ajax-'+entry_id).show()
          $.ajax({
            url: gwolle_gb_ajax_url,
            type: 'POST',
            data: ({
              func:       'set_entry_checked_state',
              new_state:  'unchecked',
              id:         entry_id
            }),
            dataType: 'html',
            success: function(msg){
              if (msg == 'success') {
                $('#entry-'+entry_id).removeClass('approved').addClass('unapproved');
              }
              $('#wait-entry-ajax-'+entry_id).hide();
              $('#entry-actions-'+entry_id).show();
            }
          });
          event.preventDefault();
        });
        
        /**
         * Click at 'Trash entry'
         */
        $('.gwolle-gb-entry-list #trash_'+entry_id).click(function(event) {
          //  Hide the row actions
          $('#entry-actions-'+entry_id).hide();
          $('#wait-entry-ajax-'+entry_id).show();
          $.ajax({
            url: gwolle_gb_ajax_url,
            type: 'POST',
            data: ({
              func:     'trash_entry',
              entry_id: entry_id
            }),
            dataType: 'html',
            success: function(msg){
              //  Remove old undo items
              $('.gwolle-gb-entry-list .undo').remove();
              $('.gwolle-gb-entry-list').prepend(msg);
              //  Slide up the entry item
              $('#entry-'+entry_id).slideUp('fast', function() {
                //  Show the row action (for the 'undo' feature)
                $('#entry-actions-'+entry_id).show();
                $('#wait-entry-ajax-'+entry_id).hide();
              });
              //  Fadein the undo item
              $('#undo-'+entry_id).slideDown('fast');
              refresh_gwolle_gb_ajax();
              
              if ($('div[id^="entry-"]:visible').length < 5) {
                //  There are only 3 entries visible. Load one more via AJAX.
                $.ajax({
                  url: gwolle_gb_ajax_url,
                  type: 'POST',
                  data: ({
                    func:     'get_dashboard_widget_row'
                  }),
                  dataType: 'html',
                  success: function(msg){
                    if (msg == 'error') {
                      //  Just do nothing
                    }
                    else {
                      //  Yeah, we've got a new row. Append it...
                      $('.gwolle-gb-entry-list').append(msg);
                      //  ... and slide it down
                      $('.gwolle-gb-entry-list div[id^="entry-"]:last').slideDown('fast');
                      refresh_gwolle_gb_ajax();
                    }
                  }
                });
              }
            }
          });
          event.preventDefault();
        });
        
        /**
         * Click at 'Spam'
         */
        $('.gwolle-gb-entry-list #mark-spam-'+entry_id).click(function(event) {
          //  Hide the row actions
          $('#entry-actions-'+entry_id).hide();
          $('#wait-entry-ajax-'+entry_id).show();
          $.ajax({
            url: gwolle_gb_ajax_url,
            type: 'POST',
            data: ({
              func:     'mark_spam',
              entry_id: entry_id
            }),
            dataType: 'html',
            success: function(msg){
              if (msg == 'success') {
                //  Show the spam icon
                $('#spam-icon-'+entry_id).fadeIn();
                //  Hide the 'mark as spam' link
                $('#mark-spam-'+entry_id).hide();
                //  Show the 'Unmark spam' link
                $('#unmark-spam-'+entry_id).show();
              }
              else {
                alert('There was an error marking the entry as spam. Please try again and contact the plugin author.');
              }
              //  Show the row actions
              $('#wait-entry-ajax-'+entry_id).hide();
              $('#entry-actions-'+entry_id).show();
            }
          });
          event.preventDefault();
        });
        
        /**
         * Click at 'Not spam'
         */
        $('.gwolle-gb-entry-list #unmark-spam-'+entry_id).click(function(event) {
          //  Hide the row actions
          $('#entry-actions-'+entry_id).hide();
          $('#wait-entry-ajax-'+entry_id).show();
          $.ajax({
            url: gwolle_gb_ajax_url,
            type: 'POST',
            data: ({
              func:     'unmark_spam',
              entry_id: entry_id
            }),
            dataType: 'html',
            success: function(msg){
              if (msg == 'success') {
                //  Show the spam icon
                $('#spam-icon-'+entry_id).fadeOut();
                //  Hide the 'unmark spam' link
                $('#unmark-spam-'+entry_id).hide();
                //  Show the 'mark as spam' link
                $('#mark-spam-'+entry_id).show();
              }
              else {
                alert('There was an error marking the entry as not-spam. Please try again and contact the plugin author.');
              }
              //  Show the row actions
              $('#wait-entry-ajax-'+entry_id).hide();
              $('#entry-actions-'+entry_id).show();
            }
          });
          event.preventDefault();
        });
        
        $(this).addClass('ajaxified');
      }
    });
    
    /**
     * Click at 'Undo'
     */
    $('.gwolle-gb-entry-list a[id^="untrash_entry_"]').click(function(event) {
      var entry_id = $(this).attr('id').replace(/untrash_entry_/,'');
      $(this).after('<img src="'+gwolle_gb_plugin_url+'/admin/gfx/loading.gif" style="height:11px;" />').hide();
      $.ajax({
        url: gwolle_gb_ajax_url,
        type: 'POST',
        data: ({
          func:     'untrash_entry',
          entry_id: entry_id
        }),
        dataType: 'html',
        success: function(msg){
          if (msg == 'success') {
            //  Fadeout the undo item
            $('#undo-'+entry_id).slideUp('fast', function() {
              //  After fading out, remove this row from the DOM
              $(this).remove();
            });
            //  Fadein the entry item
            $('#entry-'+entry_id).slideDown('fast');
            refresh_gwolle_gb_ajax();
          }
        }
      });
      event.preventDefault();
    });
  }
  
  refresh_gwolle_gb_ajax();
});