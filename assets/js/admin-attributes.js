jQuery( document ).ready( function($) {

	$('.qpr-attribute-delete-button').on( 'click', function() {

		if( !confirm( 'All associated terms will be deleted, are you sure you want to delete this attribute?' ) ) {
			event.preventDefault();
		}

	});

	$('.qpr-disable-variations-button').on( 'click', function() {

		if( !confirm( 'This will delete all product variation data which use the terms of this attribute? Are you sure?' ) ) {
			event.preventDefault();
		}

	});

});