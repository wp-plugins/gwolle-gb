<?php
/*
 * widget.php
 * Gwolle-GB widget
 */

if (function_exists('register_sidebar') && class_exists('WP_Widget')) {
	class GwolleGB_Widget extends WP_Widget {

		/* Constructor */
		function __construct() {
			$widget_ops = array( 'classname' => 'gwolle_gb', 'description' => __('Displays the recent guestbook entries.',GWOLLE_GB_TEXTDOMAIN) );
			parent::__construct('gwolle_gb', 'Gwolle-GB Widget', $widget_ops);
			$this->alt_option_name = 'gwolle_gb';
		}

		/** @see WP_Widget::widget */
		function widget($args, $instance) {
			extract($args);

			$widget_title = (isset($instance['title']) && strlen(trim($instance['title'])) > 0) ? $instance['title'] : __('Guestbook', GWOLLE_GB_TEXTDOMAIN);

			$num_entries = (isset($instance['num_entries']) && (int)$instance['num_entries'] > 0) ? (int)$instance['num_entries'] : 5;

			$num_words = (isset($instance['num_words']) && (int)$instance['num_words'] > 0) ? (int)$instance['num_words'] : 10;

			$link_text = (isset($instance['link_text']) && strlen(trim($instance['link_text'])) > 0) ? $instance['link_text'] : __('Visit guestbook', GWOLLE_GB_TEXTDOMAIN);

			$postid = (isset($instance['postid']) && (int)$instance['postid'] > 0) ? (int)$instance['postid'] : 0;

			// Init
			$widget_html = '';

			$widget_html .= $before_widget;
			$widget_html .= '<div class="gwolle_gb_widget">';
			if ($widget_title !== FALSE) {
				$widget_html .= $before_title . apply_filters('widget_title', $widget_title) . $after_title;
			}

			// Get the latest $num_entries guestbook entries
			$entries = gwolle_gb_get_entries(
				array(
					'num_entries' => $num_entries,
					'checked' => 'checked',
					'deleted' => 'notdeleted',
					'spam' => 'nospam'
					)
				);
			if ( is_array( $entries ) && count( $entries ) > 0 ) {
				$widget_html .= '<ul class="gwolle_gb_widget">';
				foreach( $entries as $entry ) {
					// Main Content
					$widget_html .= '
									<li class="gwolle_gb_widget">
									';
					$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), $num_words );
					if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
						$entry_content = convert_smilies($entry_content);
					}
					$widget_html .= $entry_content;
					$widget_html .= '
									</li>
									';
				}
				$widget_html .= '</ul>';
			}

			// Post the link to the Guestbook.
			if ( (int) $postid > 0 ) {
				$widget_html .= '
				<p class="gwolle_gb_link">
					<a href="' . get_site_url() . "/?p=" . $postid . '" title="' . __('Click here to get to the guestbook.', GWOLLE_GB_TEXTDOMAIN) . '">' . $link_text . ' &raquo;</a>
				</p>';
			}
			$widget_html .= '</div>' . $after_widget;

			if ( is_array( $entries ) && count( $entries ) > 0 ) {
				// Only display widget if there are any entries
				echo $widget_html;
			}
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title']       = strip_tags($new_instance['title']);
			$instance['num_entries'] = (int) strip_tags($new_instance['num_entries']);
			$instance['num_words']   = (int) strip_tags($new_instance['num_words']);
			$instance['link_text']   = strip_tags($new_instance['link_text']);
			$instance['postid']      = (int) strip_tags($new_instance['postid']);

			return $instance;
		}

		/** @see WP_Widget::form */
		function form($instance) {

			$default_value = array(
						"title" => __('Guestbook', GWOLLE_GB_TEXTDOMAIN),
						"num_entries" => 5,
						"num_words" => 10,
						"link_text" => __('Visit guestbook', GWOLLE_GB_TEXTDOMAIN),
						"postid" => 0
				);
			$instance      = wp_parse_args( (array) $instance, $default_value );

			$title         = esc_attr($instance['title']);
			$num_entries   = (int) esc_attr($instance['num_entries']);
			$num_words     = (int) esc_attr($instance['num_words']);
			$link_text     = esc_attr($instance['link_text']);
			$postid        = (int) esc_attr($instance['postid']);
			?>

			<p>
				<label for="<?php echo $this->get_field_id('title'); ?>" /><?php _e('Title:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<br />
				<input type="text" id="<?php echo $this->get_field_id('title'); ?>" value="<?php echo $title; ?>" name="<?php echo $this->get_field_name('title'); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('num_entries'); ?>" /><?php _e('Number of entries:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<br />
				<select id="<?php echo $this->get_field_id('num_entries'); ?>" name="<?php echo $this->get_field_name('num_entries'); ?>">
					<?php
					for ($i = 1; $i <= 15; $i++) {
						echo '<option value="' . $i . '"';
						if ( $i === $num_entries ) {
							echo ' selected="selected"';
						}
						echo '>' . $i . '</option>';
					}
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('num_words'); ?>" /><?php _e('Number of words for each entry:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<br />
				<select id="<?php echo $this->get_field_id('num_words'); ?>" name="<?php echo $this->get_field_name('num_words'); ?>">
					<?php
					for ($i = 1; $i <= 25; $i++) {
						echo '<option value="' . $i . '"';
						if ( $i === $num_words ) {
							echo ' selected="selected"';
						}
						echo '>' . $i . '</option>';
					}
					?>
				</select>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('link_text'); ?>" /><?php _e('Link text:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<br />
				<input type="text" id="<?php echo $this->get_field_id('link_text'); ?>" value="<?php echo $link_text; ?>" name="<?php echo $this->get_field_name('link_text'); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('postid'); ?>"><?php _e('Select the page of the guestbook:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<select id="<?php echo $this->get_field_id('postid'); ?>" name="<?php echo $this->get_field_name('postid'); ?>">
					<option value="0"><?php _e('Select page', GWOLLE_GB_TEXTDOMAIN); ?></option>
					<?php
					$args = array(
						'post_type'      => 'page',
						'nopaging'       => true,
						'posts_per_page' => -1
					);

					$sel_query = new WP_Query( $args );
					if ( $sel_query->have_posts() ) {
						while ( $sel_query->have_posts() ) : $sel_query->the_post();
							$selected = false;
							if ( get_the_ID() == $postid ) {
								$selected = true;
							}
							echo '<option value="' . get_the_ID() . '"'
							. selected( $selected )
							. '>'. get_the_title() . '</option>';
						endwhile;
					}
					wp_reset_postdata(); ?>
				</select>
			</p>
			<?php
		}

	}

	function gwolle_gb_widget() {
		register_widget('GwolleGB_Widget');
	}
	add_action('widgets_init', 'gwolle_gb_widget' );
}

