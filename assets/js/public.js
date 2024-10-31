jQuery( document ).ready( function($) {

	// Access the session key - rename this function

	function getSessionKey(name) {
		var value = "; " + document.cookie;
		var parts = value.split("; " + name + "=");
		if (parts.length == 2) return parts.pop().split(";").shift();
	}

	// Key gen

	function uuidv4() {
		return ([1e7]+-1e3+-4e3+-8e3+-1e11).replace(/[018]/g, c =>
			(c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
		)
	}

	// Get current session key

	var sessionKey = getSessionKey( 'qpr_session' );

	// Get new session expiry date to be used if updated

	var d = new Date();
	d.setTime(d.getTime() + (31*24*60*60*1000));
	var sessionExpiry = d.getTime();
	var sessionExpiryString = d.toUTCString();

	// Main Cart

	function updateMainCart() {

		if( sessionKey ) {

			var data = {
				'action': 'qpr_get_cart_rows',
				'session_key': sessionKey,
				'type': 'main',
				'nonce': $('#qpr-nonce-cart').val(),
			};

			jQuery.post( ajaxurl, data, function( response ) {

				$( '#qpr-main-cart-contents tbody' ).html( response );

			});

		} else {

			$( '#qpr-main-cart-contents' ).html( '<td colspan="4">Cart empty.</td>' );

		}

	}

	// Mini Cart

	function updateMiniCart() {

		if( sessionKey ) {

			var data = {
				'action': 'qpr_get_cart_rows',
				'session_key': sessionKey,
				'type': 'mini',
				'nonce': $('#qpr-nonce-mini-cart').val(),
			};

			jQuery.post( ajaxurl, data, function( response ) {

				$( '.qpr-mini-cart-contents' ).html( response );

			});

		} else {

			$( '.qpr-mini-cart-contents' ).html( 'Cart empty.' );

		}

	}

	updateMainCart();
	updateMiniCart();

	// Remove from Cart

	$( '.qpr-main-cart, .qpr-mini-cart' ).on( 'click', '.cart-remove', function(e) {

		e.preventDefault();

		var data = {
			'action': 'qpr_remove_from_cart',
			'session_key': sessionKey,
			'session_expiry': sessionExpiry,
			'product_id': $(this).attr( 'data-product-id' ),
			'variation_id': $(this).attr( 'data-product-variation' ),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			updateMainCart();
			updateMiniCart();
			alert( 'Removed' );

		});

	});

	// Qty Change

	$('#add-to-cart-qty').on( 'change', function(e) {

		$( '#add_to_cart' ).attr( 'data-product-qty', $(this).val() );

	});

	// Add To Cart

	$('#add_to_cart').on( 'click', function(e) {

		e.preventDefault();

		// check all variations are selected

		isValid = true;

		$( '.qpr-buy' ).find('input, select').each(function(){
		    
		    if( $(this).prop('required') ){

				if( $(this).val() === '' ) {
				
					isValid = false;
				
				}

		    }

		});

		if( isValid == true ) {

			// As we are updating the session we generate a new expiry date

			if( !sessionKey ) {

				sessionKey = uuidv4();
				document.cookie = "qpr_session=" + sessionKey + "; expires=" + sessionExpiryString + ";path=/";

			}

			var data = {
				'action': 'qpr_add_to_cart',
				'session_key': sessionKey,
				'session_expiry': sessionExpiry,
				'product_id': $(this).attr( 'data-product-id' ),
				'product_qty': $(this).attr( 'data-product-qty' ),
				'product_variation': $(this).attr( 'data-product-variation' ),
				'nonce': $('#qpr-nonce-add-to-quote').val(),
			};

			jQuery.post( ajaxurl, data, function( response ) {

				response = JSON.parse( response );
				alert( response.message );

				if( response.redirect == true ) {

					window.location.replace( 'cart' );

				} else {

					updateMainCart();
					updateMiniCart();

				}

			});

		} else {

			alert( 'Select all options' );

		}

	});

	// Set attribute hidden field from selection

	$('.qpr-attribute-select').on( 'change', function() {

		$( '#add_to_cart' ).attr( 'data-product-variation', '' );

		$( '.qpr-attribute-select option:selected' ).each(function( index ) {

			if( $(this).val() != '' ) {


				$( '#add_to_cart' ).attr( 'data-product-variation', $( '#add_to_cart' ).attr( 'data-product-variation' ) + $(this).val() + '_' );

			}


		});

		$( '#add_to_cart' ).attr( 'data-product-variation', $( '#add_to_cart' ).attr('data-product-variation').slice(0,-1) );

	});

	// Checkout if same as billing unchecked

	$('#checkout-shipping-same-as-billing').on( 'change', function() {

		if( !$(this).is(':checked') ) {

			$(this).closest('label').remove();

			$('#qpr-checkout-shipping-fields').slideDown();

		} 

	});

	// Clone billing fields to shipping if checked

	$('#qpr-billing-first-name').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-first-name').val( $(this).val() );

		} 

	});

	$('#qpr-billing-last-name').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-last-name').val( $(this).val() );

		} 

	});

	$('#qpr-billing-company').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-company').val( $(this).val() );

		} 

	});

	$('#qpr-billing-address-line-1').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-address-line-1').val( $(this).val() );

		} 

	});

	$('#qpr-billing-address-line-2').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-address-line-2').val( $(this).val() );

		} 

	});

	$('#qpr-billing-city').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-city').val( $(this).val() );

		} 

	});

	$('#qpr-billing-state').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-state').val( $(this).val() );

		} 

	});

	$('#qpr-billing-postcode').on( 'keyup', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-postcode').val( $(this).val() );

		} 

	});

	$('#qpr-billing-country').on( 'change', function() {

		if( $('#checkout-shipping-same-as-billing').is(':checked') ) {

			$('#qpr-shipping-country').val( $(this).val() );

		} 

	});

	// Pay for quote

	$('#qpr-payment-methods .qpr-payment').on( 'click', function(e) {

		e.preventDefault();
		$('.qpr-payment-details').slideUp();
		$( '#' + $( $(this) ).attr('data-opens') ).slideDown();
		$( '#qpr-place-order' ).show();
		$( 'input[name="place_order_payment_option"]' ).val( $(this).attr( 'data-payment-option' ) );

	});	

});