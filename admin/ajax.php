<?php
/*
 * ajax.php
 * Processes AJAX requests.
 */


/*
 * Add JavaScript to the admin Footer so we can do Ajax.
 */

add_action( 'admin_footer', 'gwolle_gb_ajax_javascript' );
function gwolle_gb_ajax_javascript() {
	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		return;
	}

	// Set Your Nonce
	$ajax_nonce = wp_create_nonce( "gwolle_gb_ajax" ); ?>

	<script>
	jQuery( document ).ready( function( $ ) {

		// Page-entries.php Admin page Click events
		jQuery( '#gwolle_gb_entries .gwolle_gb_actions a' ).click(function(event) {


			// Do not do anything here...
			var parent_class = jQuery(this).parent().hasClass('gwolle_gb_edit');
			if (parent_class) {
				//console.log('gwolle_gb_edit');
				return;
			}
			var parent_class = jQuery(this).parent().hasClass('gwolle_gb_ajax');
			if (parent_class) {
				//console.log('gwolle_gb_ajax');
				return;
			}


			// Set up data to send
			var a_el_id = jQuery(this).attr('id');
			var entry_id = a_el_id;
			entry_id = entry_id.replace(/uncheck_/,'');
			entry_id = entry_id.replace(/check_/,'');
			entry_id = entry_id.replace(/unspam_/,'');
			entry_id = entry_id.replace(/spam_/,'');
			entry_id = entry_id.replace(/untrash_/,'');
			entry_id = entry_id.replace(/trash_/,'');
			var setter = a_el_id.replace(new RegExp( "_" + entry_id, "g"), '');

			var data = {
				action: 'gwolle_gb_ajax',
				security: '<?php echo $ajax_nonce; ?>',
				id: entry_id,
				setter: setter
			};


			// Set Ajax icon on visible
			jQuery( "tr#entry_" + entry_id + " .gwolle_gb_ajax" ).css('display', 'inline-block');


			// Do the actual request
			$.post( ajaxurl, data, function( response ) {
				response = jQuery.trim( response );

				// Set classes accordingly
				if ( response == setter ) { // We got what we wanted

					switch ( response ) {
						// possible classes: nospam notrash checked visible spam trash unchecked invisible
						case 'uncheck':
							jQuery( "tr#entry_" + entry_id ).addClass('unchecked').removeClass('checked');
							break;
						case 'check':
							jQuery( "tr#entry_" + entry_id ).addClass('checked').removeClass('unchecked');
							break;
						case 'unspam':
							jQuery( "tr#entry_" + entry_id ).addClass('nospam').removeClass('spam');
							break;
						case 'spam':
							jQuery( "tr#entry_" + entry_id ).addClass('spam').removeClass('nospam');
							break;
						case 'untrash':
							jQuery( "tr#entry_" + entry_id ).addClass('notrash').removeClass('trash');
							break;
						case 'trash':
							jQuery( "tr#entry_" + entry_id ).addClass('trash').removeClass('notrash');
							break;
					}

					// Set to visible or invisible
					if ( jQuery( "tr#entry_" + entry_id ).hasClass('checked') && jQuery( "tr#entry_" + entry_id ).hasClass('nospam') && jQuery( "tr#entry_" + entry_id ).hasClass('notrash') ) {
						jQuery( "tr#entry_" + entry_id ).addClass('visible').removeClass('invisible');
					} else {
						jQuery( "tr#entry_" + entry_id ).addClass('invisible').removeClass('visible');
					}

					//alert( ' response:' + response + ' setter:' + setter ); // debugging
				}

				// Hide Ajax icon again
				jQuery( "tr#entry_" + entry_id + " .gwolle_gb_ajax" ).css('display', 'none');
			});

			event.preventDefault();
		});


		// Dashboard Widget Click events
		jQuery( '#gwolle_gb_dashboard .row-actions a' ).click(function(event) {

			// Do not do anything here...
			var parent_class = jQuery(this).parent().hasClass('gwolle_gb_edit');
			if (parent_class) {
				//console.log('gwolle_gb_edit');
				return;
			}
			var parent_class = jQuery(this).parent().hasClass('gwolle_gb_ajax');
			if (parent_class) {
				//console.log('gwolle_gb_ajax');
				return;
			}


			// Set up data to send
			var a_el_id = jQuery(this).attr('id');
			var entry_id = a_el_id;
			entry_id = entry_id.replace(/check_/,'');
			entry_id = entry_id.replace(/spam_/,'');
			entry_id = entry_id.replace(/trash_/,'');
			var setter = a_el_id.replace(new RegExp( "_" + entry_id, "g"), '');

			var data = {
				action: 'gwolle_gb_ajax',
				security: '<?php echo $ajax_nonce; ?>',
				id: entry_id,
				setter: setter
			};


			// Set Ajax icon on visible
			jQuery( ".gwolle-gb-dashboard div#entry_" + entry_id + " .gwolle_gb_ajax" ).css('display', 'inline-block');


			// Do the actual request
			$.post( ajaxurl, data, function( response ) {
				response = jQuery.trim( response );

				if ( response == setter ) { // We got what we wanted

					// Remove entry from widget
					jQuery( ".gwolle-gb-dashboard div#entry_" + entry_id ).remove();

					// alert( ' response:' + response + ' setter:' + setter ); // debugging
				}

			});

			event.preventDefault();
		});
	});
	</script>
	<?php
}


/*
 * Callback function for handling the Ajax requests that are generated from the JavaScript above in gwolle_gb_ajax_javascript
 */

add_action( 'wp_ajax_gwolle_gb_ajax', 'gwolle_gb_ajax_callback' );
function gwolle_gb_ajax_callback() {

	if ( function_exists('current_user_can') && !current_user_can('moderate_comments') ) {
		echo "error";
		die();
	}

	check_ajax_referer( 'gwolle_gb_ajax', 'security' );

	if (isset($_POST['id'])) {
		$id = intval( $_POST['id'] );
	}
	if (isset($_POST['setter'])) {
		$setter = strval( $_POST['setter'] );
	}


	if ( isset($id) && $id > 0 && isset($setter) && strlen($setter) > 0) {
		$entry = new gwolle_gb_entry();
		$result = $entry->load( $id );
		if ( !$result ) {
			$response = "error";
		}


		switch ($setter) {
			case 'uncheck':
				if ( $entry->get_ischecked() == 1 ) {
					$entry->set_ischecked( false );
					$result = $entry->save();
					if ($result ) {
						$response = "uncheck";
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-unchecked' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
			case 'check':
				if ( $entry->get_ischecked() == 0 ) {
					$entry->set_ischecked( true );
					$user_id = get_current_user_id(); // returns 0 if no current user
					$entry->set_checkedby( $user_id );
					$result = $entry->save();
					if ($result ) {
						$response = "check";
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-checked' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
			case 'unspam':
				if ( $entry->get_isspam() == 1 ) {
					$entry->set_isspam( false );
					$result = $entry->save();
					gwolle_gb_akismet( $entry, 'submit-ham' );
					if ($result ) {
						$response = "unspam";
						gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-not-spam' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
			case 'spam':
				if ( $entry->get_isspam() == 0 ) {
					$entry->set_isspam( true );
					$result = $entry->save();
					gwolle_gb_akismet( $entry, 'submit-spam' );
					if ($result ) {
						$response = "spam";
						gwolle_gb_add_log_entry( $entry->get_id(), 'marked-as-spam' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
			case 'untrash':
				if ( $entry->get_istrash() == 1 ) {
					$entry->set_istrash( false );
					$result = $entry->save();
					if ($result ) {
						$response = "untrash";
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-untrashed' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
			case 'trash':
				if ( $entry->get_istrash() == 0 ) {
					$entry->set_istrash( true );
					$result = $entry->save();
					if ($result ) {
						$response = "trash";
						gwolle_gb_add_log_entry( $entry->get_id(), 'entry-trashed' );
					} else {
						$response = "error";
					}
				} else {
					$response = "nochange";
				}
				break;
		}
	} else {
		$response = "error";
	}

	echo $response;
	die(); // this is required to return a proper result

}



