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

			$default_value = array(
						"title"       => __('Guestbook', GWOLLE_GB_TEXTDOMAIN),
						"num_entries" => 5,
						"best"        => '',
						"name"        => 1,
						"date"        => 1,
						"num_words"   => 10,
						"link_text"   => __('Visit guestbook', GWOLLE_GB_TEXTDOMAIN),
						"postid"      => 0
				);
			$instance      = wp_parse_args( (array) $instance, $default_value );

			$widget_title  = esc_attr($instance['title']);
			$num_entries   = (int) esc_attr($instance['num_entries']);
			$best          = esc_attr($instance['best']);
			$best          = explode(",", $best);
			$name          = (int) esc_attr($instance['name']);
			$date          = (int) esc_attr($instance['date']);
			$num_words     = (int) esc_attr($instance['num_words']);
			$link_text     = esc_attr($instance['link_text']);
			$postid        = (int) esc_attr($instance['postid']);

			// Init
			$widget_html = '';

			$widget_html .= $before_widget;
			$widget_html .= '<div class="gwolle_gb_widget">';
			if ($widget_title !== FALSE) {
				$widget_html .= $before_title . apply_filters('widget_title', $widget_title) . $after_title;
			}

			$widget_html .= '<ul class="gwolle_gb_widget">';
			$counter = 0;

			// Get the best entries first
			if ( is_array( $best ) && !empty( $best ) ) {
				foreach ($best as $entry_id) {
					if ( $counter == $num_entries) { break; } // we have enough
					$entry = new gwolle_gb_entry();
					$entry_id = intval($entry_id);
					if ( isset($entry_id) && $entry_id > 0 ) {
						$result = $entry->load( $entry_id );
						if ( !$result ) {
							// No entry loaded
							continue;
						}
						// Main Content
						$widget_html .= '
										<li class="gwolle_gb_widget">
										';
						if ( $name ) {
							$widget_html .= '<span class="gb-author-name">' . $entry->get_author_name() . '</span>';
						}
						if ( $name && $date ) {
							$widget_html .= " / ";
						}
						if ( $date ) {
							$widget_html .= '<span class="gb-date">' . date_i18n( get_option('date_format'), $entry->get_date() ) . '</span>';
						}
						if ( $name || $date ) {
							$widget_html .= ":<br />";
						}

						$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), $num_words );
						if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
							$entry_content = convert_smilies($entry_content);
						}
						$widget_html .= '<span class="gb-entry-content">' . $entry_content . '</span';
						$widget_html .= '
										</li>
										';
						$counter++;
					}
				}
			}

			// Get the latest $num_entries guestbook entries
			if ( $counter != $num_entries) { // we have enough
				$entries = gwolle_gb_get_entries(
					array(
						'num_entries' => $num_entries,
						'checked'     => 'checked',
						'trash'       => 'notrash',
						'spam'        => 'nospam'
						)
					);
				if ( is_array( $entries ) && !empty( $entries ) ) {
					foreach( $entries as $entry ) {
						if ( $counter == $num_entries) { break; } // we have enough
						if ( is_array( $best) && in_array( $entry->get_id(), $best ) ) { continue; } // already listed
						// Main Content
						$widget_html .= '
										<li class="gwolle_gb_widget">
										';
						if ( $name ) {
							$widget_html .= '<span class="gb-author-name">' . $entry->get_author_name() . '</span>';
						}
						if ( $name && $date ) {
							$widget_html .= " / ";
						}
						if ( $date ) {
							$widget_html .= '<span class="gb-date">' . date_i18n( get_option('date_format'), $entry->get_date() ) . '</span>';
						}
						if ( $name || $date ) {
							$widget_html .= ":<br />";
						}

						$entry_content = gwolle_gb_get_excerpt( $entry->get_content(), $num_words );
						if ( get_option('gwolle_gb-showSmilies', 'true') === 'true' ) {
							$entry_content = convert_smilies($entry_content);
						}
						$widget_html .= '<span class="gb-entry-content">' . $entry_content . '</span';
						$widget_html .= '
										</li>
										';
						$counter++;
					}
				}
			}

			$widget_html .= '</ul>';

			// Post the link to the Guestbook.
			if ( (int) $postid > 0 ) {
				$widget_html .= '
				<p class="gwolle_gb_link">
					<a href="' . get_site_url() . "/?p=" . $postid . '" title="' . __('Click here to get to the guestbook.', GWOLLE_GB_TEXTDOMAIN) . '">' . $link_text . ' &raquo;</a>
				</p>';
			}
			$widget_html .= '</div>' . $after_widget;

			if ( $counter > 0 ) {
				// Only display widget if there are any entries
				echo $widget_html;

				// Load Frontend CSS in Footer, only when it's active
				wp_enqueue_style('gwolle_gb_frontend_css');
			}
		}

		/** @see WP_Widget::update */
		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title']       = strip_tags($new_instance['title']);
			$instance['num_entries'] = (int) strip_tags($new_instance['num_entries']);
			$instance['best']        = strip_tags($new_instance['best']);
			if ( isset($new_instance['name']) ) {
				$instance['name']    = (int) strip_tags($new_instance['name']);
			} else {
				$instance['name']    = 0;
			}
			if ( isset($new_instance['date']) ) {
				$instance['date']    = (int) strip_tags($new_instance['date']);
			} else {
				$instance['date']    = 0;
			}
			$instance['num_words']   = (int) strip_tags($new_instance['num_words']);
			$instance['link_text']   = strip_tags($new_instance['link_text']);
			$instance['postid']      = (int) strip_tags($new_instance['postid']);

			return $instance;
		}

		/** @see WP_Widget::form */
		function form($instance) {

			$default_value = array(
						"title"       => __('Guestbook', GWOLLE_GB_TEXTDOMAIN),
						"num_entries" => 5,
						"best"        => '',
						"name"        => 1,
						"date"        => 1,
						"num_words"   => 10,
						"link_text"   => __('Visit guestbook', GWOLLE_GB_TEXTDOMAIN),
						"postid"      => 0
				);
			$instance      = wp_parse_args( (array) $instance, $default_value );

			$title         = esc_attr($instance['title']);
			$num_entries   = (int) esc_attr($instance['num_entries']);
			$best          = esc_attr($instance['best']);
			$name          = (int) esc_attr($instance['name']);
			$date          = (int) esc_attr($instance['date']);
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
				<label for="<?php echo $this->get_field_id('best'); ?>" /><?php _e('Best entries to show:', GWOLLE_GB_TEXTDOMAIN); ?></label>
				<br />
				<input type="text" id="<?php echo $this->get_field_id('best'); ?>" value="<?php echo $best; ?>" name="<?php echo $this->get_field_name('best'); ?>" placeholder="<?php _e('List of entry_id\'s, comma-separated', GWOLLE_GB_TEXTDOMAIN); ?>" />
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('name'); ?>">
				<input type="checkbox" id="<?php echo $this->get_field_id('name'); ?>" <?php checked(1, $name ); ?> name="<?php echo $this->get_field_name('name'); ?>" value="1" />
				<?php _e('Show name of author.', GWOLLE_GB_TEXTDOMAIN); ?></label>
			</p>

			<p>
				<label for="<?php echo $this->get_field_id('date'); ?>">
				<input type="checkbox" id="<?php echo $this->get_field_id('date'); ?>" <?php checked(1, $date ); ?> name="<?php echo $this->get_field_name('date'); ?>" value="1" />
				<?php _e('Show date of entry.', GWOLLE_GB_TEXTDOMAIN); ?></label>
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
						'orderby'        => 'title',
						'order'          => 'ASC',
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

