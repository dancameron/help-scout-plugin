jQuery.noConflict();

jQuery(function($) {

	/**
	 * License Activation
	 */
	$('#hsd_activate_license').on('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $button = $( this ),
			$license_key = $('#hsd_license_key').val(),
			$license_message = $('#license_message');

		$button.hide();
		$button.after('<span class="spinner si_inline_spinner" style="visibility:visible;display:inline-block;"></span>');
		$.post( ajaxurl, { action: 'hsd_activate_license', license: $license_key, security: hsd_js_object.sec },
			function( data ) {
				if ( data.error ) {
					$button.show();
					$license_message.html('<span class="inline_error_message">' + data.response + '</span>');
				}
				else {
					$license_message.html('<span class="inline_success_message">' + data.response + '</span>');
				}
				$('.spinner').hide();
			}
		);
	});

	/**
	 * License Deactivation
	 */
	$('#hsd_deactivate_license').on('click', function(e) {
		e.stopPropagation();
		e.preventDefault();
		var $button = $( this ),
			$activate_button = $('#hsd_activate_license');
			$license_key = $('#hsd_license_key').val(),
			$license_message = $('#license_message');

		$button.hide();
		$button.after('<span class="spinner si_inline_spinner" style="visibility:visible;display:inline-block;"></span>');
		$.post( ajaxurl, { action: 'hsd_deactivate_license', license: $license_key, security: hsd_js_object.sec },
			function( data ) {
				if ( data.error ) {
					$button.show();
					$license_message.html('<span class="inline_error_message">' + data.response + '</span>');
				}
				else {
					$activate_button.hide();
					$activate_button.removeAttr('disabled').addClass('button-primary').fadeIn();
					$license_message.html('<span class="inline_success_message">' + data.response + '</span>');
				}
				$('.spinner').hide();
			}
		);
	});

	/**
	 * Reset Customer IDs
	 */
	$("#reset_customer_ids").on('click', function(event) {
		event.stopPropagation();
		event.preventDefault();
		var $button = jQuery( this );

		$button.after('<span class="spinner si_inline_spinner" style="visibility:visible;display:inline-block;"></span>');

		if( confirm( 'Are you sure? This will delete stored customer ids for your users.' ) ) {
			$.post(
				ajaxurl, { action: 'hsd_reset_customer_ids', nonce: $button.data('nonce') },
				function( data ) {
					$('.si_inline_spinner').remove();
					$("#reset_customer_ids").removeClass('button');
					$("#reset_customer_ids").html('All done');
				}
			);
		}
	});


});
