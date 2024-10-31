jQuery( document ).ready( function($) {

	$('#qpr-billing-edit').on( 'click', function(e) {
		e.preventDefault();
		$(this).hide();
		$('.qpr-billing .qpr-initial').hide();
		$('.qpr-billing .qpr-edit').show();
	});

	$('#shipping-edit').on( 'click', function(e) {
		e.preventDefault();
		$(this).hide();
		$('.qpr-shipping .qpr-initial').hide();
		$('.qpr-shipping .qpr-edit').show();
	});	

	$(".datepicker").datepicker({
		dateFormat: 'yy-mm-dd',
		numberOfMonths: 1,
		showButtonPanel: true,
		minDate: 0,
	})

	// any inputs blank change to the minimum allowed (zero or if qty 1) - important, don't want any empty values for calculations

	$( '#qpr-quote-contents' ).on( 'keyup change', 'input[type="number"]', function(e) {

		if( $(this).val() == '' ) {

			if( $(this).hasClass('qpr-qty') ) {

				$(this).val(1);

			} else {

				$(this).val(0);

			}

		}

	});

	$( 'body' ).on( 'change', '#qpr-quote-currency', function(e) {

		newCurrencySymbol = $(this).find('option:selected').attr( 'data-currency-symbol' );

		// Fallback to currency code if no symbol

		if( newCurrencySymbol.length == '' ) {

			newCurrencySymbol = $(this).find('option:selected').val();

		}

		$('#qpr-total-currency-symbol').html( newCurrencySymbol );

	});

	$( '#qpr-quote-contents tbody' ).on( 'keyup change', '.qpr-qty,.qpr-price,.qpr-tax', function(e) {

		row = $(this).closest('tr');
		qty = ( !isNaN( parseFloat( row.find('.qpr-qty').val() ) ) ? parseFloat( row.find('.qpr-qty').val() ) : 0 );
		price = ( !isNaN( parseFloat( row.find('.qpr-price').val() ) ) ? parseFloat( row.find('.qpr-price').val() ) : 0 );

		if( $('#qpr-estimated-tax-profile').val() == '' ) {

			// no automatic calculation

			tax = ( !isNaN( parseFloat( row.find('.qpr-tax').val() ) ) ? parseFloat( row.find('.qpr-tax').val() ) : 0 );

		} else {

			// auto calculation from estimate

			tax = 0;

			estimatedTaxPrice = parseFloat( $('#qpr-estimated-tax-profile').val() );

			if( estimatedTaxPrice > 0 ) {

				tax = price * ( estimatedTaxPrice / 100 );

			}

		}

		total = ( qty * ( price + tax ) );
		row.find('.qpr-tax').val( tax.toFixed(2) );
		row.find('.qpr-total').val( total.toFixed(2) );
		calculateGrandTotals();

	});

	$( '#qpr-quote-contents tfoot' ).on( 'keyup change', '#qpr-quote-grand-totals-discount,#qpr-quote-grand-totals-shipping-tax,#qpr-quote-grand-totals-shipping', function(e) {

		calculateGrandTotals();

	});

	function calculateGrandTotals() {

		rowTotalsTax = 0.00;
		rowTotalsTotal = 0.00;

		$( '#qpr-quote-contents tbody tr' ).each(function( index ) {

			row = $(this);
			rowShippingTax = parseFloat( row.find('.shipping-tax').val() );
			rowTax = parseFloat( row.find('.qpr-tax').val() );
			rowTotal = parseFloat( row.find('.qpr-total').val() );
			rowTotalsTax = parseFloat( rowTotalsTax + rowTax );
			rowTotalsTotal = parseFloat( rowTotalsTotal + rowTotal );

		});

		discounts = parseFloat( !isNaN( $('#qpr-quote-grand-totals-discount').val() ) ? $('#qpr-quote-grand-totals-discount').val() : 0 );
		shipping =  parseFloat( !isNaN( $('#qpr-quote-grand-totals-shipping').val() ) ? $('#qpr-quote-grand-totals-shipping').val() : 0 );

		discountsTaxTotal = 0.00;
		shippingTaxTotal = 0.00;
		rowTotalsTotal = parseFloat( rowTotalsTotal - discounts );

		if( $( '#qpr-estimated-tax-profile' ).val() !== '' ) {

			estimatedTax = parseFloat( $( '#qpr-estimated-tax-profile' ).val() );
			estimatedTax1Point = parseFloat( '1.' + estimatedTax );

			if( estimatedTax > 0 ) {

				rowTotalsTax = parseFloat( ( rowTotalsTotal - ( rowTotalsTotal / estimatedTax1Point ) ).toFixed(2) );
				shippingTaxTotal = parseFloat( ( shipping * ( estimatedTax / 100 ) ).toFixed(2) );

			}

			$( '#qpr-quote-grand-totals-shipping-tax' ).val( shippingTaxTotal );

		}

		grandTax = parseFloat( ( rowTotalsTax + shippingTaxTotal ).toFixed(2) );
		grandTotal = parseFloat( ( rowTotalsTotal + shipping + shippingTaxTotal ).toFixed(2) );

		$( '#qpr-quote-grand-totals-tax' ).val( grandTax );
		$( '#qpr-quote-grand-totals-total' ).val( grandTotal );

	}

	function qpr_get_quote_contents() {

		var data = {
			'action': 'qpr_get_quote_contents',
			'quote_id' : $('#post_ID').val(),
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			$( '#qpr-quote-contents tbody' ).html( response );
			calculateGrandTotals();

		});

	}

	qpr_get_quote_contents();

	function getGrandTotals() {

		var data = {
			'action': 'qpr_get_grand_totals',
			'quote_id' : $('#post_ID').val(),
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			$( '#qpr-quote-contents tfoot' ).html( response );

		});

	}

	getGrandTotals();

	productsToRemove = [];

	$( '#qpr-quote-contents' ).on( 'click', '.qpr-product-remove', function(e) {

		e.preventDefault();

		productsToRemove[ productsToRemove.length + 1 ] = $(this).attr( 'data-product-id' ) + ',' + $(this).attr( 'data-product-variation' );

		$(this).closest('tr').remove();

		calculateGrandTotals();

	});

	$('#qpr-add-product').on( 'click', function(e) {

		e.preventDefault();
		$('#qpr-add-product-expand').slideDown();

	});

	$('#qpr-add-product-search input').on( 'keyup', function(e) {

		var data = {
			'action': 'qpr_add_product_search',
			'search': $('#qpr-add-product-search input').val(),
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			jQuery( '#qpr-add-product-search-results' ).hide();
			jQuery( '#qpr-add-product-search-results select' ).html('');
			jQuery( '#qpr-add-product-qty' ).val('1').hide();
			jQuery( '#qpr-add-product-add' ).hide();

			if( response != 0 ) {

				jQuery( '#qpr-add-product-search-results select' ).append( response );
				jQuery( '#qpr-add-product-search-results' ).show();

			}

		});

	});

	$('#qpr-add-product-search-results select').on( 'change', function(e) {

		$( '#qpr-add-product-add' ).attr( 'data-product-id', $(this).val() );

		// get/show variations for selection

		var data = {
			'action': 'qpr_add_product_search_select_variations',
			'product_id': $(this).val(),
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			$('#qpr-add-product-search-results-variation-select').html(response).show();

		});

		jQuery( '#qpr-add-product-qty input' ).val('1');

		if( $(this).val() > 0 ) {

			jQuery( '#qpr-add-product-qty' ).show();
			jQuery( '#qpr-add-product-add' ).show();

		} else {

			jQuery( '#qpr-add-product-qty' ).hide();
			jQuery( '#qpr-add-product-add' ).hide();

		}

	});

	$('#qpr-add-product-add').on( 'click', function(e) {
		
		e.preventDefault();

		isValid = true;

		$( '#qpr-add-product-expand' ).find('input, select').each(function(){
		    
		    if( $(this).prop('required') ){

				if( $(this).val() === '' ) {
				
					isValid = false;
				
				}

		    }

		});

		if( isValid == true ) {

			$( '#qpr-add-product-add' ).attr( 'data-product-variation', '' ); // Has to be blank first

			$( '.qpr-attribute-select option:selected' ).each(function( index ) {

				// Stuff gets added, if nothing found it gets zero instead see later condition

				if( $(this).val() != '' ) {

					$( '#qpr-add-product-add' ).attr( 'data-product-variation', $( '#qpr-add-product-add' ).attr( 'data-product-variation' ) + $(this).val() + '_' );

				}

			});

			// Zero used instead of blank

			if( $( '#qpr-add-product-add' ).attr( 'data-product-variation' ) == '' ) {

				$( '#qpr-add-product-add' ).attr( 'data-product-variation', '0' );

			}

			// If ends with _

			if( $( '#qpr-add-product-add' ).attr('data-product-variation').match("_$") ) {

				$( '#qpr-add-product-add' ).attr( 'data-product-variation', $( '#qpr-add-product-add' ).attr( 'data-product-variation' ).slice( 0,-1 ) );

			}

			addProductId = $( '#qpr-add-product-add' ).attr( 'data-product-id' );
			addVariationId = $( '#qpr-add-product-add' ).attr( 'data-product-variation' );
			addProductQty = $('#qpr-add-product-qty input').val();

			// Check if already exists and just update qty else do the ajax

			alreadyExists = false;

			$( '#qpr-quote-contents tbody tr' ).each(function( index ) {

				if( $(this).attr('data-product-id') == addProductId && $(this).attr('data-variation-id') == addVariationId ) {

					currentQty = $(this).find('.qpr-qty').val();
					$(this).find('.qpr-qty').val( parseInt( currentQty ) + parseInt( addProductQty ) );
					alreadyExists = true;
					return false; // Breaks loop

				}

			});

			if( alreadyExists == false ) {

				var data = {
					'action': 'qpr_add_product_to_quote',
					'product_id': addProductId,
					'variation_id': addVariationId,
					'product_qty': addProductQty,
					'nonce': $( '#qpr-nonce-quote' ).val(),
				};

				jQuery.post( ajaxurl, data, function( response ) {

					$('#qpr-quote-contents tbody').append( response );

				});

			}

			$(this).removeAttr( 'data-product-id' );
			$(this).removeAttr( 'data-product-variation' );

			$('#qpr-add-product-search-results-variation-select').html('');

			jQuery( '#qpr-add-product-expand' ).hide();
			jQuery( '#qpr-add-product-search input' ).val('');
			jQuery( '#qpr-add-product-search-results' ).hide();
			jQuery( '#qpr-add-product-search-results select' ).html('');
			jQuery( '#qpr-add-product-qty' ).val('1').hide();
			jQuery( '#qpr-add-product-add' ).hide();

		} else {

			alert( 'Select all options' );

		}

	});

	$('#qpr-quote-contents-save').on( 'click', function(e) {

		e.preventDefault();

		var contents = [];
		$("#qpr-quote-contents").find("tbody tr").each(function(index) {
			contents[index] = [];
		    contents[index].push( $(this).attr( 'data-product-id' ), $(this).attr( 'data-variation-id' ), $(this).find('.qpr-qty').val(), $(this).find('.qpr-price').val(), $(this).find('.qpr-tax').val(), $(this).find('.qpr-total').val() );

		});

		var data = {
			'action': 'qpr_save_quote_contents',
			'quote_id': $('#post_ID').val(),
			'contents': contents,
			'grand_totals_discount': $('#qpr-quote-grand-totals-discount').val(),
			'grand_totals_shipping': $('#qpr-quote-grand-totals-shipping').val(),
			'grand_totals_shipping_tax': $('#qpr-quote-grand-totals-shipping-tax').val(),
			'grand_totals_tax': $('#qpr-quote-grand-totals-tax').val(),
			'grand_totals_total': $('#qpr-quote-grand-totals-total').val(),
			'products_to_remove': productsToRemove,
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			alert('Saved');

		});

	});

	$('#qpr-quote-estimate').on( 'click', function(e) {

		e.preventDefault();

		var contents = [];
		$("#qpr-quote-contents").find("tbody tr").each(function(index) {
			contents[index] = [];
		    contents[index].push( $(this).attr( 'data-product-id' ), $(this).attr( 'data-variation-id' ) );
		});

		var data = {
			'action': 'qpr_quote_estimate',
			'quote_id': $('#post_ID').val(),
			'contents': contents,
			'estimated_tax_profile': $('#qpr-estimated-tax-profile').val(),
			'nonce': $( '#qpr-nonce-quote' ).val(),
		};

		jQuery.post( ajaxurl, data, function( response ) {

			response = JSON.parse( response );

			$.each( response, function( index, value ) {

				updates = value.split(',');

				$("#qpr-quote-contents").find("tbody tr").each( function(index) {

					if( $(this).attr( 'data-product-id' ) == updates[0] && $(this).attr('data-variation-id') == updates[1] ) {

						$(this).find('.qpr-price').val( updates[2] ).trigger('change'); // triggers change so the row total gets updated
						$(this).find('.qpr-tax').val( updates[3] ).trigger('change');

					}

				});

			});

			calculateGrandTotals();

		});

	});

	$('#qpr-send-to-customer').on( 'click', function(e) {

		if( !confirm( 'Are you sure you have saved the contents of this quote before sending?' ) ){
			e.preventDefault();
		}

	});

});