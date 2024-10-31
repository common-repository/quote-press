<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

function qpr_check_for_prefix( $string ) {

	$result = false;

	if( substr( $string, 0, 4 ) === 'qpr_' ) {

		$result = true;

	}

	return $result;

}

function qpr_get_quote_statuses( $labels ) {

	if( $labels == true ) {

		return array(
			'qpr-pending' => __( 'Pending', 'quote-press' ),
			'qpr-sent' => __( 'Sent', 'quote-press' ),
			'qpr-expired' => __( 'Expired', 'quote-press' ),
			'qpr-paid' => __( 'Paid', 'quote-press' ),
			'qpr-paid-unconf' => __( 'Paid Unconfirmed', 'quote-press' ),
			'qpr-cancelled' => __( 'Cancelled', 'quote-press' ),
			'qpr-completed' => __( 'Completed', 'quote-press' ),
		);

	} else {

		return array(
			'qpr-pending',
			'qpr-sent',
			'qpr-expired',
			'qpr-paid',
			'qpr-paid-unconf',
			'qpr-cancelled',
			'qpr-completed',
		);

	}

}

function qpr_contents_editable_quote_statuses() {

	return array(
		'auto-draft', // for new posts
		'qpr-pending',
	);

}

function qpr_round_number( $number, $decimal_places ) {

	if( isset( $number, $decimal_places ) ) {

		return (float)number_format( (float)$number, $decimal_places, '.', '' );

	}

}

function qpr_date_format( $timestamp ) {

	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );

	if( !empty( $date_format ) && !empty( $time_format ) ) {

		$format = $date_format . ' ' . $time_format;

	} else {

		$format = 'd-m-Y H:i:s'; // Fallback

	}

	return date( $format, $timestamp );

}

function qpr_get_customer_name( $user_id ) {
	$user = get_userdata( $user_id ); 

	$customer_name = '';

	if( !empty( $user->user_firstname ) ) {

		$customer_name .= $user->user_firstname . ' ';

	}

	if( !empty( $user->user_lastname ) ) {

		$customer_name .= $user->user_lastname . ' ';

	}

	$customer_name = rtrim( $customer_name, ' ' );

	if( empty( $customer_name ) ) {

		$customer_name = __( 'No Name', 'quote-press' );

	}

	return $customer_name;
}

function qpr_get_customer_edit_link( $user_id ) {
	return get_edit_user_link( $user_id ); 
}

function qpr_is_taxonomy() {

	$queried_object = get_queried_object();

	$return = false;

	if( !empty( $queried_object ) ) {

		if( qpr_check_for_prefix( $queried_object->taxonomy ) == true ) {

			$return = true;

		}

	}

	return $return;
	
}

function qpr_is_cart() {

	global $wp;

	if( $wp->request == 'cart' ) {

		return true;

	} else {

		return false;

	}

}


function qpr_is_store() {

	global $wp;

	if( $wp->request == 'store' ) {

		return true;

	} else {

		return false;

	}

}

function qpr_is_account() {

	global $wp;

	if( $wp->request == 'account' ) {

		return true;

	} else {

		return false;

	}

}

function qpr_is_product() {

	if( get_post_type() == 'qpr_product' && is_single() ) {

		return true;

	} else {

		return false;

	}

}

function qpr_is_checkout() {

	global $wp;

	if( $wp->request == 'checkout' ) {

		return true;

	} else {

		return false;

	}

}

function qpr_get_taxonomy_label( $taxonomy ) {

	if( !empty( $taxonomy ) ) {

		$taxonomy = get_taxonomy( $taxonomy );

		return $taxonomy->label;

	} else {

		return false;

	}

}

function qpr_get_term_name( $term_id ) {

	if( !empty( $term_id ) ) {

		return get_term( $term_id )->name;

	} else {

		return false;

	}

}

function qpr_get_currencies() {

	$currencies = file_get_contents( plugin_dir_path( __DIR__ ) . 'includes/libraries/world-currencies/dist/json/currencies.json' );
	$currencies = json_decode( $currencies, true );
	return $currencies;

}

function qpr_get_currency_symbol( $currency_code ) {

	$symbol = false;

	$currencies = qpr_get_currencies();

	foreach( $currencies as $currency ) {
		if( $currency['iso']['code'] == $currency_code ) {
			$symbol = $currency['units']['major']['symbol'];
			break;
		}
	}

	if( $symbol == false ) {

		$symbol = $currency_code;

	}

	return $symbol;

}

function qpr_get_countries() {

	$countries = file_get_contents( plugin_dir_path( __DIR__ ) . 'includes/libraries/country-json/src/country-by-abbreviation.json' );
	$countries = json_decode( $countries, true );

	// Countries has 2 countries with same abbreviation, United Kingdom and Northern Ireland, as Northern Ireland is UK this is removed, otherwise United Kingdom selected countries appear as Northern Ireland as Northern Ireland first on list

	foreach( $countries as $k => $v ) {
		if( $v['country'] == 'Northern Ireland' ) {
			unset( $countries[$k] );
			break;
		}
	}

	return $countries;

}

function qpr_get_country_name( $abbreviation ) {

	$countries = qpr_get_countries();
	$country_name = false;

	foreach( $countries as $country ) {
		if( $abbreviation == $country['abbreviation'] ) {
			$country_name = $country['country'];
			break;
		}
	}

	return $country_name;

}