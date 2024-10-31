jQuery( document ).ready( function($) {

	// WordPress Amends

	$( '.wp-admin.options-general-php #users_can_register' ).attr('checked', true).attr('disabled',true); // must be enabled for QuotePress, setting changed on activation
	$('<p class="description">As you are using QuotePress registration is required.</p>').insertAfter( '.wp-admin.options-general-php label[for="users_can_register"]' );

});