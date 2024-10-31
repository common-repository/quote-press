<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Filters' ) ) {

	class QPR_Filters {

		public function __construct() {

			add_filter( 'pre_get_posts', array( $this, 'alter_query' ), 9999 );

		}

		public function alter_query( $query ) {

			// Get all applied filters

			foreach( $_GET as $gk => $gv ) {

				if( substr( $gk, 0, 7 ) === "filter_" ) {

					$applied_filters[$gk] = $gv;

				}

			}

			// Attributes

			$tax_query = array();

			if( !empty( $applied_filters ) ) {

				foreach( $applied_filters as $applied_filter_k => $applied_filter_v ) {

					if( isset( $_GET[ $applied_filter_k ] ) ) {

						if( isset( $_GET[ $applied_filter_k ] ) ) {

							$applied_filter_explode = explode( '_', $_GET[ $applied_filter_k ] );

							$tax_query[] = array(
								'taxonomy' => str_replace( 'filter_', '', $applied_filter_k ),
								'field' => 'term_id',
								'terms' => $applied_filter_explode,
								'operator'=> 'AND'
							);

						}	

					}

				}

				$query->set( 'tax_query', $tax_query );

			}

			return $query;

		}

	}

}