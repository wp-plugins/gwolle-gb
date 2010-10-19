<?php
  /**
   * gwolle_gb_get_dashboard_widget_row
   * Function to generate the HTML of an
   * entry row of the dashboard widget.
   *
   * Argument:
   * $entry   Array   as returned by gwolle_gb_get_entries
   */
  
  if (!function_exists('gwolle_gb_get_dashboard_widget_row')) {
    function gwolle_gb_get_dashboard_widget_row($args) {
      $entry = $args['entry'];
      $display = (isset($args['display']) && $args['display'] == 'none') ? 'none' : 'normal';
      echo '
      <div id="entry-'.$entry['entry_id'].'" style="display:'.$display.';" class="comment depth-1 comment-item '.$entry['entry_dashboard_class'].'">
        <img src="http://www.gravatar.com/avatar/'.$entry['entry_author_gravatar'].'?s=50" class="avatar avatar-50 photo avatar-default" height="50" width="50" />
        <div class="dashboard-comment-wrap">
          <h4 class="comment-meta">';
            $spam_display = ($entry['entry_isSpam']) ? 'normal' : 'none';
            echo '
            <img id="spam-icon-'.$entry['entry_id'].'" src="'.GWOLLE_GB_URL.'/admin/gfx/entry-spam.jpg" style="margin-right:2px;height:11px;display:'.$spam_display.'" />
            <cite class="comment-author">'.$entry['entry_author_name_html'].'</cite>
            <span class="approve">['.__('Unchecked', GWOLLE_GB_TEXTDOMAIN).']</span>
          </h4>
          <blockquote>
            <p>'.$entry['excerpt'].'</p>
          </blockquote>
          <p class="row-actions" style="display:none;" id="wait-entry-ajax-'.$entry['entry_id'].'">
            <img style="height:11px;margin-right:2px;" src="'.GWOLLE_GB_URL.'/admin/gfx/loading.gif" />
            '.__('Please wait...').'
          </p>
          <p class="row-actions" id="entry-actions-'.$entry['entry_id'].'">
            <span class="approve">
              <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&gwolle_gb_function=check_entry&entry_id='.$entry['entry_id'].'&amp;_wpnonce='.wp_create_nonce('check-entry-'.$entry['entry_id']).'&amp;return_to=dashboard" id="check_'.$entry['entry_id'].'" class="vim-a" title="'.__('Check entry', GWOLLE_GB_TEXTDOMAIN).'">'.__('Check', GWOLLE_GB_TEXTDOMAIN).'</a>
            </span>
            <span class="unapprove">
              <a href="'.GWOLLE_GB_URL.'/admin/gb-module.php?function=uncheck_entry&amp;entry_id='.$entry['entry_id'].'&amp;_wpnonce='.wp_create_nonce('uncheck_entry-'.$entry['entry_id']).'&amp;backto=dashboard" id="uncheck_'.$entry['entry_id'].'" class="vim-u" title="'.__('Uncheck entry', GWOLLE_GB_TEXTDOMAIN).'">'.__('Uncheck', GWOLLE_GB_TEXTDOMAIN).'</a>
            </span>
            <span class="edit">
              &nbsp;|&nbsp;
              <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/editor.php&entry_id='.$entry['entry_id'].'" title="'.__('Edit entry', GWOLLE_GB_TEXTDOMAIN).'">'.__('Edit', GWOLLE_GB_TEXTDOMAIN).'</a>
            </span>
            <span class="spam">
              &nbsp;|&nbsp;';
              $unmark_link_display  = ($entry['entry_isSpam']) ? 'normal' : 'none';
              $mark_link_display    = ($entry['entry_isSpam']) ? 'none' : 'normal';
              echo '
              <a id="unmark-spam-'.$entry['entry_id'].'" style="display:'.$unmark_link_display.';" href="admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&gwolle_gb_function=unmark_spam&entry_id='.$entry['entry_id'].'&amp;_wpnonce='.wp_create_nonce('mark-no-spam-entry-'.$entry['entry_id']).'&amp;return_to=dashboard" class="vim-a" title="'.__('Mark entry as not-spam.',GWOLLE_GB_TEXTDOMAIN).'">'.__('Not spam',GWOLLE_GB_TEXTDOMAIN).'</a>
              <a id="mark-spam-'.$entry['entry_id'].'" style="display:'.$mark_link_display.';" href="admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&gwolle_gb_function=mark_spam&entry_id='.$entry['entry_id'].'&amp;_wpnonce='.wp_create_nonce('mark-spam-entry-'.$entry['entry_id']).'&amp;return_to=dashboard" class="vim-s vim-destructive" title="'.__('Mark entry as spam.',GWOLLE_GB_TEXTDOMAIN).'">'.__('Spam',GWOLLE_GB_TEXTDOMAIN).'</a>
            </span>
            <span class="trash">
              &nbsp;|&nbsp;
              <a href="admin.php?page='.GWOLLE_GB_FOLDER.'/gwolle-gb.php&gwolle_gb_function=trash_entry&entry_id='.$entry['entry_id'].'&amp;_wpnonce='.wp_create_nonce('trash-entry-'.$entry['entry_id']).'&amp;return_to=dashboard" id="trash_'.$entry['entry_id'].'" class="delete vim-d vim-destructive" title="'.__('Move entry to trash.',GWOLLE_GB_TEXTDOMAIN).'">'.__('Trash').'</a>
            </span>
          </p>
        </div>
      </div>';
    }
  }