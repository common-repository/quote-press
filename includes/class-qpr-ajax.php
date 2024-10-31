<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Ajax' ) ) {

	class QPR_Ajax {

		public function __construct() {

			// Public

			add_action( 'wp_head', array( $this, 'set_ajax_url' ) );
			add_action( 'wp_ajax_qpr_add_to_cart', array( $this, 'qpr_add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_qpr_add_to_cart', array( $this, 'qpr_add_to_cart' ) );
			add_action( 'wp_ajax_qpr_get_cart_rows', array( $this, 'qpr_get_cart_rows' ) );
			add_action( 'wp_ajax_nopriv_qpr_get_cart_rows', array( $this, 'qpr_get_cart_rows' ) );
			add_action( 'wp_ajax_qpr_remove_from_cart', array( $this, 'qpr_remove_from_cart' ) );
			add_action( 'wp_ajax_nopriv_qpr_remove_from_cart', array( $this, 'qpr_remove_from_cart' ) );

			// Admin

			add_action( 'wp_ajax_qpr_get_quote_contents', array($this, 'qpr_get_quote_contents') );
			add_action( 'wp_ajax_qpr_add_product_search', array( $this, 'qpr_add_product_search' ) );
			add_action( 'wp_ajax_qpr_add_product_search_select_variations', array( $this, 'qpr_add_product_search_select_variations' ) );
			add_action( 'wp_ajax_qpr_add_product_to_quote', array( $this, 'qpr_add_product_to_quote' ) );
			add_action( 'wp_ajax_qpr_save_quote_contents', array( $this, 'qpr_save_quote_contents' ) );
			add_action( 'wp_ajax_qpr_get_grand_totals', array( $this, 'qpr_get_grand_totals' ) );
			add_action( 'wp_ajax_qpr_quote_estimate', array( $this, 'qpr_quote_estimate' ) );
			add_action( 'wp_ajax_qpr_assign_customer_search', array( $this, 'qpr_assign_customer_search' ) );

		}

		public function set_ajax_url() {

			echo '<script type="text/javascript">var ajaxurl = "' . admin_url( 'admin-ajax.php' ) . '";</script>';

		}

		public function qpr_add_to_cart() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-add-to-quote' ) ) {

				global $wpdb;

				$product_id = sanitize_text_field( $_POST['product_id'] );
				$product_qty = sanitize_text_field( $_POST['product_qty'] );
				$session_key = sanitize_text_field( $_POST['session_key'] );
				$session_expiry = sanitize_text_field( $_POST['session_expiry'] );
				$session_value = $wpdb->get_results( "SELECT session_value FROM `{$wpdb->prefix}qpr_sessions` WHERE `session_key` = '" . $session_key . "'" )[0];

				if( !empty( $session_value ) ) {

					$session_value = unserialize( $session_value->session_value );

				} else {

					$session_value = array();

				}
				
				$variation_names = array();

				if( empty( $_POST['product_variation'] ) ) {

					$product_variation = '0';

				} else {

					$product_variation = sanitize_text_field( $_POST['product_variation'] );
					$vars = explode( '_', $product_variation );
					foreach( $vars as $var ) {
						$var_term = get_term( $var );
						$variation_names[] = array(
							'name' => $var_term->name,
							'taxonomy' => get_taxonomy( $var_term->taxonomy )->label,
						);
					}
				}


				if( $product_variation !== '0' ) {

					$sku = $wpdb->get_results( "SELECT variation_sku FROM `{$wpdb->prefix}qpr_variations` WHERE `variation_attributes` = '{$product_variation}' AND `variation_product_id` = '{$product_id}'" );
					$sku = $sku[0]->variation_sku;

				} else {

					$sku = get_post_meta( $product_id, '_qpr_sku', true );

				}

				$session_value['cart'][$product_id][$product_variation] = array(
					'qty' => ( !empty( $session_value['cart'][$product_id][$product_variation]['qty'] ) ? (int)$session_value['cart'][$product_id][$product_variation]['qty'] + (int)$product_qty : $product_qty ),
					'price' => 0,
					'tax' => 0,
					'total' => 0,
					'product' => get_the_title( $product_id ),
					'variation' => $variation_names,
					'sku' => $sku,
				);

				$session_value = serialize( $session_value );

				$insert = $wpdb->query( "
					INSERT INTO {$wpdb->prefix}qpr_sessions (session_id, session_key, session_value, session_expiry)
					VALUES ( '', '" . $session_key . "', '" . $session_value . "', '" . $session_expiry . "' )
					ON DUPLICATE KEY UPDATE session_value = '$session_value', session_expiry = '" . $session_expiry . "'"
				);

				if( get_option( 'qpr_add_to_cart_redirect' ) == 'on' ) {

					$redirect = true;

				} else {

					$redirect = false;

				}

				$return = array();

				if( !empty( $insert ) ) {

					$return = array(
						'redirect'	=> $redirect,
						'message'	=> __( 'Added', 'quote-press' ),
					);

				} else {

					$return = array(
						'redirect'	=> $redirect,
						'message'	=> __( 'Product could not be added, please try again or contact us for further details.', 'quote-press' ),
					);

				}

				echo json_encode( $return );

			}

			exit;

		}

		public function qpr_get_cart_rows() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-cart' ) || wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-mini-cart' ) ) {

				global $wpdb;
				$session_key = sanitize_text_field( $_POST['session_key'] );
				$session_value = $wpdb->get_results( "SELECT session_value FROM `{$wpdb->prefix}qpr_sessions` WHERE `session_key` = '" . $session_key . "'" );
				$string = '';
				$cart_empty = false;
				$type = sanitize_text_field( $_POST['type'] );

				if( $session_value !== FALSE ) {

					$session_value = $session_value[0];
					$session_value = unserialize( $session_value->session_value );

					if( !empty( $session_value['cart'] ) ) {

						foreach( $session_value['cart'] as $product_id => $variations ) {

							foreach( $variations as $variation_id => $data ) {

								$variation = '';

								foreach( $data['variation'] as $v ) {

									$variation .= $v['taxonomy'] . ': ' . $v['name'] . '<br>';

								}

								$sku = $data['sku'];

								if( $type == 'main' ) {

									$string .= '<tr><td>' . $data['product'];

									if( !empty( $sku ) || !empty( $variation ) ) {

										$string .= '<br><small>';

										if( !empty( $sku ) && get_option( 'qpr_display_skus_in_cart' ) == 'on' ) {

											$string .= __( 'SKU:', 'quote-press' ) . ' ' . $sku . '<br>';

										}

										if( !empty( $variation ) ) {

											$string .= $variation;

										}

										$string .= '</small>';

									}

									$string .= '</td><td>' . $data['qty'] . '</td><td><a href="" class="cart-remove" data-product-id="' . $product_id . '" data-product-variation="' . $variation_id . '">' . __( 'Remove', 'quote-press' ) . '</a></td></tr>';

								} else {

									$variation = str_replace( '<br>', ', ', $variation );
									$variation = rtrim( $variation, ', ' );

									$string .= '<li>';
									$string .= $data['qty'] . ' x ' . $data['product'] . '<br>';

									if( $variation !== '' || !empty( $sku ) ) {
										$string .= '<small>';
									}
									
									if( $variation !== '' ) {
										$string .= $variation . '<br>';
									}

									if( !empty( $sku ) && get_option( 'qpr_display_skus_in_cart' ) == 'on' ) {
										$string .= __( 'SKU:', 'quote-press' ) . ' ' . $sku . '<br>';
									}

									$string = rtrim( $string, '<br>' );

									if( $variation !== '' || !empty( $sku ) ) {
										$string .= '</small>';
									}
									
									$string .= '<div><a href="" class="cart-remove" data-product-id="' . $product_id . '" data-product-variation="' . $variation_id . '">' . __( 'Remove', 'quote-press' ) . '</a></div>';
									$string .= '</li>';

								}

							}

						}

					} else {

						$cart_empty = true;

					}

				} else {

					$cart_empty = true;

				}

				if( $cart_empty == true ) {

					if( $type == 'main' ) {

						$string = '<tr><td colspan="6">' . __( 'Cart empty', 'quote-press' ) . '</td></tr>';

					} else {

						$string = '<p>' . __( 'Cart empty', 'quote-press' ) . '</p>';

					}

				}

				echo $string;

			}		

			exit;

		}

		public function qpr_remove_from_cart() {

			global $wpdb;

			$product_id = sanitize_text_field( $_POST['product_id'] );
			$session_key = sanitize_text_field( $_POST['session_key'] );
			$session_value = $wpdb->get_results( "SELECT session_value FROM `{$wpdb->prefix}qpr_sessions` WHERE `session_key` = '" . $session_key . "'" )[0];
			$session_expiry = sanitize_text_field( $_POST['session_expiry'] );

			if( !empty( $session_value ) ) {

				$session_value = unserialize( $session_value->session_value );

			} else {

				$session_value = array();

			}

			unset( $session_value['cart'][$product_id][$_POST['variation_id']] );

			if( isset( $session_value['cart'][$product_id] ) ) {

				if( empty( $session_value['cart'][$product_id] ) ) {

					unset( $session_value['cart'][$product_id] );

				}

			}

			// If the cart is now empty remove the cart array key

			if( empty( $session_value['cart'] ) ) {

				unset( $session_value['cart'] );

			}

			$session_value = serialize( $session_value );

			$insert = $wpdb->query( "
				INSERT INTO {$wpdb->prefix}qpr_sessions (session_id, session_key, session_value, session_expiry)
				VALUES ( '', '" . $session_key . "', '" . $session_value . "', '" . $session_expiry . "' )
				ON DUPLICATE KEY UPDATE session_value = '$session_value', session_expiry = '" . $session_expiry . "'"
			);

			if( !empty( $insert ) ) {

				_e( 'Removed', 'quote-press' );

			} else {

				_e( 'Not removed', 'quote-press' );

			}

			exit;

		}

		public function qpr_get_quote_contents() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$quote_id = sanitize_text_field( $_POST['quote_id'] );
				$quote_status = get_post_status( $quote_id );

				if( in_array( $quote_status, qpr_contents_editable_quote_statuses() ) ) {
					$contents_editable = true;
				} else {
					$contents_editable = false;
				}

				$products = get_post_meta( $quote_id, '_qpr_quote_products', true );

				$contents = '';

				if( !empty( $products ) ) {

					foreach( $products as $product_id => $variations ) {

						foreach( $variations as $variation_id => $data ) {

							$variation = '';

							foreach( $data['variation'] as $v ) {

								$variation .= $v['taxonomy'] . ': ' . $v['name'] . '<br>';

							}

							$product_deleted = false;
							if( get_post_status( $product_id ) === false ) {
								$product_deleted = true;
							}

							$sku = $data['sku'];

							$contents .= '<tr data-product-id="' . $product_id . '" data-variation-id="' . $variation_id . '"><td>' . $data['product'] . ( $product_deleted == true ? '<br><small>No longer available</small>' : '' ) . '</td><td>' . $variation . '</td><td>' . $sku . '</td><td><input class="qpr-qty" type="number" min="1" step="1" name="quote_products[' . $product_id . '][' . $variation_id . '][qty]" value="' . $data['qty'] . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td><td><input class="qpr-price" type="number" min="0" step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][price]" value="' . $data['price'] . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td><td><input class="qpr-tax" type="number" min="0"  step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][tax]" value="' . $data['tax'] . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td><td><input class="qpr-total" type="number" min="0" step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][total]" value="' . $data['total'] . '"' . ( $contents_editable == false ? ' disabled' : '' ) . ' readonly></td><td><button class="qpr-product-remove button button-small" data-product-id="' . $product_id . '" data-product-variation="' . $variation_id . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '>Remove</a></td></tr>';	

						}					

					}

				}

				echo $contents;

			}

			exit;

		}

		public function qpr_get_grand_totals() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$quote_id = sanitize_text_field( $_POST['quote_id'] );
				$quote_status = get_post_status( $quote_id );

				if( in_array( $quote_status, qpr_contents_editable_quote_statuses() ) ) {
					$contents_editable = true;
				} else {
					$contents_editable = false;
				}

				$discount = get_post_meta( $quote_id, '_qpr_grand_totals_discount', true );
				$discount = ( !empty( $discount ) ? $discount : '0' );

				$shipping = get_post_meta( $quote_id, '_qpr_grand_totals_shipping', true );
				$shipping = ( !empty( $shipping ) ? $shipping : '0' );

				$shipping_tax = get_post_meta( $quote_id, '_qpr_grand_totals_shipping_tax', true );
				$shipping_tax = ( !empty( $shipping_tax ) ? $shipping_tax : '0' );

				$tax = get_post_meta( $quote_id, '_qpr_grand_totals_tax', true );
				$tax = ( !empty( $tax ) ? $tax : '0' );

				$total = get_post_meta( $quote_id, '_qpr_grand_totals_total', true );
				$total = ( !empty( $total ) ? $total : '0' );

				$currency = get_post_meta( $quote_id, '_qpr_currency', true );

				$grand_totals = '
					<tr>
						<td colspan="6" style="text-align: right;">' . __( 'Discount (-)', 'quote-press' ) . '</td>
						<td colspan="2"><input id="qpr-quote-grand-totals-discount" type="number" min="0" value="' . $discount . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td>
					</tr>
					<tr>
						<td colspan="6" style="text-align: right;">' . __( 'Shipping', 'quote-press' ) . '</td>
						<td colspan="2"><input id="qpr-quote-grand-totals-shipping" type="number" min="0" value="' . $shipping . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td>
					</tr>
					<tr>
						<td colspan="6" style="text-align: right;">' . __( 'Tax (Shipping)', 'quote-press' ) . '</td>
						<td colspan="2"><input id="qpr-quote-grand-totals-shipping-tax" type="number" min="0" value="' . $shipping_tax . '"' . ( $contents_editable == false ? ' disabled' : '' ) . '></td>
					</tr>
					<tr>
						<td colspan="6" style="text-align: right;">' . __( 'Tax (Total)', 'quote-press' ) . '</td>
						<td colspan="2"><input id="qpr-quote-grand-totals-tax"  type="number" min="0" value="' . $tax . '"' . ( $contents_editable == false ? ' disabled' : '' ) . ' readonly></td>
					</tr>
					<tr>
						<td colspan="6" style="text-align: right;">' . __( 'Total', 'quote-press' )  . ' ' . __( '(', 'quote-press' ) . '<span id="qpr-total-currency-symbol">' . qpr_get_currency_symbol( $currency ) . '</span>' . __( ')', 'quote-press' ) . '</td>
						<td colspan="2"><input id="qpr-quote-grand-totals-total"  type="number" min="0" value="' . $total . '"' . ( $contents_editable == false ? ' disabled' : '' ) . ' readonly></td>
					</tr>
				';

				echo $grand_totals;

			}

			exit;

		}

		public function qpr_add_product_search() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				global $wpdb;

				$search = sanitize_text_field( $_POST['search'] );

				if( !empty( $search ) ) {

					$query_results = $wpdb->get_results( "SELECT ID, post_title FROM {$wpdb->prefix}posts WHERE post_title LIKE '%{$search}%' AND post_type = 'qpr_product'" );

					if( !empty( $query_results ) ) {

						$results = '<option value="">' . __( 'Select', 'quote-press' ) . '</option>'; // value blank so jquery picks up as blank and not 'Select'

						foreach( $query_results as $query_result ) {

							$results .= '<option value="' . $query_result->ID . '">' . $query_result->post_title . '</option>';

						}

					} else {

						$results = 0;

					}

				} else {

					$results = 0;

				}

				echo $results;

			}
			
			exit;

		}

		public function qpr_add_product_search_select_variations() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$attributes = QPR_Variations::get_product_variations($_POST['product_id']);
				$attributes_variations = QPR_Attributes::get_attributes( true ); // THE ENABLED VARIATIONS
				$attribute_options = array();

				$return = '';

				foreach( $attributes as $attribute_term_ids => $attribute_price ) {

					$attribute_term_ids = explode( '_', $attribute_term_ids );

					foreach( $attribute_term_ids as $attribute_term_id ) {

						$attribute_term_taxonomy = get_term( $attribute_term_id )->taxonomy;

						if( array_key_exists( $attribute_term_taxonomy, $attributes_variations ) ) {

							$attribute_options[$attribute_term_taxonomy][$attribute_term_id] = $attribute_term_id;

						}

					}

				}
				
				foreach( $attribute_options as $attribute_taxonomy => $attribute_terms ) {

					$return .= '<div><label>' . get_taxonomy( $attribute_taxonomy )->label;
					$return .= '<select id="qpr-attribute-select" class="qpr-attribute-select" required>';
					$return .= '<option value="">Select</option>';
					foreach( $attribute_terms as $attribute_term ) {
						$return .= '<option value="' . $attribute_term . '">' . get_term( $attribute_term )->name . '</option>';
					}
					$return .= '</select></label></div>';

				}

				echo $return;

			}

			exit;

		}

		public function qpr_add_product_to_quote() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$product_id = sanitize_text_field( $_POST['product_id'] );

				if( empty( $_POST['variation_id'] ) ) {

					$variation_id = '0';
					$sku = get_post_meta( $product_id, '_qpr_sku', true );

				} else {

					$variation_id = sanitize_text_field( $_POST['variation_id'] );
					$sku = QPR_Variations::get_product_variations( $product_id )[$variation_id]['sku'];

				}

				$qty = sanitize_text_field( $_POST['product_qty'] );

				$variation_explode = explode( '_', $variation_id );
				$variation_name = '';

				foreach( $variation_explode as $var ) {

					$var_term = get_term( $var );
					$variation_names[] = array(
						'name' => $var_term->name,
						'taxonomy' => get_taxonomy( $var_term->taxonomy )->label,
					);

				}

				foreach( $variation_names as $v ) {
					if( !empty( $v['taxonomy'] ) && !empty( $v['name'] ) ) {
						$variation_name .= $v['taxonomy'] . ': ' . $v['name'] . '<br>'; 	
					}
				}

				$new_row = '<tr data-product-id="' . $product_id . '" data-variation-id="' . $variation_id . '"><td>' . get_the_title( $product_id ) . '</td><td>' . $variation_name . '</td><td>' . $sku . '</td><td><input class="qpr-qty" type="number" min="1" step="1" name="quote_products[' . $product_id . '][' . $variation_id . '][qty]" value="' . $qty . '"></td><td><input class="qpr-price" type="number" min="0" step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][price]" value="0"></td><td><input class="qpr-tax" type="number" min="0"  step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][tax]" value="0"></td><td><input class="qpr-total" type="number" min="0" step="0.01" name="quote_products[' . $product_id . '][' . $variation_id . '][total]" value="0" readonly></td><td><a href="" class="qpr-product-remove button button-small" data-product-id="' . $product_id . '" data-product-variation="' . $variation_id . '">Remove</a></td></tr>';	

				echo $new_row; // return an array of the product id and var

			}

			exit;

		}

		public function qpr_save_quote_contents() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$quote_id = sanitize_text_field( $_POST['quote_id'] );
				$contents = $_POST['contents'];
				$products = get_post_meta( $quote_id, '_qpr_quote_products', true );

				$grand_totals_discount = sanitize_text_field( $_POST['grand_totals_discount'] );
				$grand_totals_shipping = sanitize_text_field( $_POST['grand_totals_shipping'] );
				$grand_totals_shipping_tax = sanitize_text_field( $_POST['grand_totals_shipping_tax'] );
				$grand_totals_tax = sanitize_text_field( $_POST['grand_totals_tax'] );
				$grand_totals_total = sanitize_text_field( $_POST['grand_totals_total'] );

				foreach( $contents as $product_data ) {

					$products[ $product_data[0] ][ $product_data[1] ]['qty'] = $product_data[2];
					$products[ $product_data[0] ][ $product_data[1] ]['price'] = $product_data[3];
					$products[ $product_data[0] ][ $product_data[1] ]['tax'] = $product_data[4];
					$products[ $product_data[0] ][ $product_data[1] ]['total'] = $product_data[5];

				}

				update_post_meta( $quote_id, '_qpr_grand_totals_discount', $grand_totals_discount );
				update_post_meta( $quote_id, '_qpr_grand_totals_shipping', $grand_totals_shipping );
				update_post_meta( $quote_id, '_qpr_grand_totals_shipping_tax', $grand_totals_shipping_tax );
				update_post_meta( $quote_id, '_qpr_grand_totals_tax', $grand_totals_tax );
				update_post_meta( $quote_id, '_qpr_grand_totals_total', $grand_totals_total );

				if( !empty( $_POST['products_to_remove'] ) ) {

					foreach( $_POST['products_to_remove'] as $key => $value ) {

						if( !empty( $value ) ) {

							$explode = explode( '_', $value );
							unset( $products[ $explode[0] ][ $explode[1] ] );

						}

					}

				}

				update_post_meta( $quote_id, '_qpr_quote_products', $products );

			}

			exit;

		}

		public function qpr_quote_estimate() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				global $wpdb;
				$quote_id = sanitize_text_field( $_POST['quote_id'] );
				$contents = $_POST['contents'];
				$estimated_tax_profile = sanitize_text_field( $_POST['estimated_tax_profile'] );
				$return = array();

				foreach( $contents as $product_data ) {

					// if it's not a variation

					if( $product_data[1] == 0 ) {

						$price = get_post_meta( $product_data[0], '_qpr_price', true );

					} else {

						// if it's a variation

						$price = $wpdb->get_results("SELECT variation_price FROM {$wpdb->prefix}qpr_variations WHERE variation_product_id = '{$product_data[0]}' AND variation_attributes = '{$product_data[1]}'")[0];
						$price = $price->variation_price;

						// if product prices entered inc/vat

						if( get_option( 'qpr_product_price_tax_status' ) == 'inc' ) {

							$tax_temp = '1.' . $estimated_tax_profile;

							$price = $price / (float)$tax_temp;

						}

					}

					if( empty( $price ) ) {

						$price = 0;

					}

					$tax = 0; // for if nothing or a zero rate selected

					if( $estimated_tax_profile > 0 ) {

						$tax = $price * ( $estimated_tax_profile / 100 ); // 20 needs to be 0.20

					}

					$return[] = $product_data[0] . ',' . $product_data[1] . ',' . qpr_round_number( $price, 2 ) . ',' . qpr_round_number( $tax, 2 );

				}

				echo json_encode( $return );

			}
			
			exit;

		}

		public function qpr_assign_customer_search() {

			if( wp_verify_nonce( $_POST['nonce'], 'qpr-nonce-quote' ) ) {

				$search_term = sanitize_text_field( $_POST['search'] );

				if( empty( $search_term ) ) {

					$no_results = true;

				} else {

					$args = array(
						'role__in'     => array( 'qpr_customer' ),
						'orderby'      => 'display_name',
						'order'        => 'ASC',
						'search'       => '*' . $search_term . '*',
						'fields'		=> array( 'ID', 'user_email', 'display_name' ),
						'number'		=> 20
					 ); 
					$customers = get_users( $args );

					if( !empty( $customers ) ) {

						$results = '';

						foreach( $customers as $customer ) {

							$results .= '<option value="' . $customer->ID . '">' . $customer->display_name . ' (#' . $customer->ID . ' - ' . $customer->user_email . ')</option>';

						}

						echo $results;

					} else {

						$no_results = true;

					}

				}

				if( $no_results == true ) {

					echo '<option value="">' . __( 'No results', 'quote-press' ) . '</option>';

				}

			}
			
			exit;

		}

	}

}