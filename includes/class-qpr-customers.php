<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Customers' ) ) {

	class QPR_Customers {

		static function customer_link( $user_id, $include_info, $link_breaks ) {

			$user = get_user_by( 'id', $user_id );

			if( $include_info == 'all' ) {

				return '<a href="' . qpr_get_customer_edit_link( $user->ID ) . '">' . qpr_get_customer_name( $user->ID ) . '</a>' . ( $link_breaks == true ? '<br>' : ' ' ) . '<small>(#' . $user->ID . ' <a href="mailto:' . $user->user_email . '">' . $user->user_email . '</a>)</small>';

			} else {

				return '<a href="' . qpr_get_customer_edit_link( $user->ID ) . '">' . qpr_get_customer_name( $user->ID ) . '</a>';

			}
		}

	}

}