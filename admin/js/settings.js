var gwolle_gb_ajax_url;

jQuery(document).ready(function($) {
  gwolle_gb_ajax_url = gwolle_gb_plugin_url + '/admin/ajax.php';
  
  //  Search for post ID via AJAX
  $('#search_gwolle_gb_post_ID').click(function(event) {
    $('img#post_id_status').attr('src', gwolle_gb_plugin_url+'/admin/gfx/loading.gif');
    
    //  AJAX request
    $.ajax({
      type: 'POST',
      url: gwolle_gb_ajax_url,
      data: ({
        func: 'search_gwolle_gb_post_ID'
      }),
      success: function(data) {
        if (data == 'failure') {
          $('#post_ID').val('');
          alert(stripslashes(gwolle_gb_strings.post_id_search_failed));
          $('img#post_id_status').attr('src', gwolle_gb_plugin_url+'/admin/gfx/entry-unchecked.jpg');
        }
        else if (data == '0') {
          $('#post_ID').val('');
          alert(stripslashes(gwolle_gb_strings.post_id_not_found));
          $('img#post_id_status').attr('src', gwolle_gb_plugin_url+'/admin/gfx/entry-unchecked.jpg');
        }
        else if (data > 0) {
          $('#post_ID').val(data);
          $('img#post_id_status').attr('src', gwolle_gb_plugin_url+'/admin/gfx/entry-checked.jpg').attr('title',gwolle_gb_strings.post_id_found);
        }
      }
    });
    
    event.preventDefault();
  });
});