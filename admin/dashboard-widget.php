<?php

/*
 * Adds a dashboard widget to show the latest entries.
 */


function gwolle_gb_dashboard() {

	// Only get new and unchecked entries
	$entries = gwolle_gb_get_entries(array(
			'num_entries' => 5,
			'checked' => 'unchecked',
			'trash'   => 'nottrash',
			'spam'    => 'nospam'
		));

	if ( is_array($entries) && count($entries) > 0 ) {
		// Dashboard JavaScript
		wp_enqueue_script( 'gwolle-gb-entries', WP_PLUGIN_URL . '/' . GWOLLE_GB_FOLDER .'/admin/js/dashboard.js', 'jquery', GWOLLE_GB_VER, true );

		// List of guestbook entries
		echo '<div class="gwolle-gb-dashboard gwolle-gb">';
		foreach ( $entries as $entry ) {
			$class = '';
			$rowOdd = false;
			// rows have a different color.
			if ($rowOdd) {
				$rowOdd = false;
				$class .= ' alternate';
			} else {
				$rowOdd = true;
			}

			// Attach 'spam' to class if the entry is spam
			if ( $entry->get_isspam() === 1 ) {
				$class .= ' spam';
			}

			// Attach 'trash' to class if the entry is in trash
			if ( $entry->get_istrash() === 1 ) {
				$class .= ' trash';
			}

			// Attach 'visible/invisible' to class
			if ( $entry->get_isspam() === 1 || $entry->get_istrash() === 1 || $entry->get_ischecked() === 0 ) {
				$class .= ' invisible';
			} else {
				$class .= ' visible';
			}

			// Add admin-entry class to an entry from an admin
			$author_id = $entry->get_author_id();
			$is_moderator = gwolle_gb_is_moderator( $author_id );
			if ( $is_moderator ) {
				$class .= ' admin-entry';
			} ?>


			<div id="entry-<?php echo $entry->get_id(); ?>" class="comment depth-1 comment-item <?php echo $class; ?>">
				<div class="dashboard-comment-wrap">
					<h4 class="comment-meta">
						<?php // Author info ?>
						<cite class="comment-author"><?php echo gwolle_gb_get_author_name_html($entry); ?></cite>
					</h4>

					<?php
					// Optional Icon column where CSS is being used to show them or not
					/*
					if ( get_option('gwolle_gb-showEntryIcons', 'true') === 'true' ) { ?>
						<div class="entry-icons">
							<span class="visible-icon"></span>
							<span class="invisible-icon"></span>
							<span class="spam-icon"></span>
							<span class="trash-icon"></span>
						</div><?php
					} */

					// Date column
					echo '
						<div class="date">' . date_i18n( get_option('date_format'), $entry->get_date() ) . ', ' .
							date_i18n( get_option('time_format'), $entry->get_date() ) .
						'</div>'; ?>

					<blockquote class="excerpt">
						<p>
						<?php
						// Content / Excerpt
						$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), 16 );
						if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
							$entry_content = convert_smilies($entry_content);
						}
						echo $entry_content; ?>
						</p>
					</blockquote><?php

					// Actions, to be made with AJAX
					?>
					<p class="row-actions" id="entry-actions-<?php echo $entry->get_id(); ?>">
						<span class="edit">
							<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/editor.php&entry_id=<?php echo $entry->get_id(); ?>" title="<?php _e('Edit entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Edit', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="approve">
							&nbsp;|&nbsp;
							<a href="#" id="check_<?php echo $entry->get_id(); ?>" class="vim-a" title="<?php _e('Check entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Check', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="unapprove">
							&nbsp;|&nbsp;
							<a href="#" id="uncheck_<?php echo $entry->get_id(); ?>" class="vim-u" title="<?php _e('Uncheck entry', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Uncheck', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="spam">
							&nbsp;|&nbsp;
							<a id="mark-spam-<?php echo $entry->get_id(); ?>" href="#" class="vim-s vim-destructive" title="<?php _e('Mark entry as spam.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Spam', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="nospam">
							&nbsp;|&nbsp;
							<a id="unmark-spam-<?php echo $entry->get_id(); ?>" href="#" class="vim-a" title="<?php _e('Mark entry as not-spam.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Not spam', GWOLLE_GB_TEXTDOMAIN); ?></a>
						</span>
						<span class="trash">
							&nbsp;|&nbsp;
							<a href="#" id="trash_<?php echo $entry->get_id(); ?>" class="delete vim-d vim-destructive" title="<?php _e('Move entry to trash.', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Trash'); ?></a>
						</span>
						<span class="ajax">
							&nbsp;|&nbsp;
							<a href="#" id="ajax_<?php echo $entry->get_id(); ?>" class="ajax vim-d vim-destructive" title="<?php _e('Please wait...', GWOLLE_GB_TEXTDOMAIN); ?>"><?php _e('Wait...'); ?></a>
						</span>
					</p>
				</div>
			</div>
			<?php

		} ?>

		</div>
		<p class="textright">
			<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=all" class="button button-primary"><?php _e('View all', GWOLLE_GB_TEXTDOMAIN); ?></a>
			<a href="admin.php?page=<?php echo GWOLLE_GB_FOLDER; ?>/entries.php&amp;show=unchecked" class="button button-primary"><?php _e('View new', GWOLLE_GB_TEXTDOMAIN); ?></a>
		</p><?php
	} else {
		echo '<p>' . __('No new and unchecked guestbook entries.', GWOLLE_GB_TEXTDOMAIN) . '</p>';
	}
}


// Add the widget
function gwolle_gb_dashboard_setup() {
	wp_add_dashboard_widget('gwolle_gb_dashboard', __('Guestbook (new entries)', GWOLLE_GB_TEXTDOMAIN), 'gwolle_gb_dashboard');
}
add_action('wp_dashboard_setup', 'gwolle_gb_dashboard_setup');


