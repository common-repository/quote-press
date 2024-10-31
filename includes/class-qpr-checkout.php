<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Checkout' ) ) {

	class QPR_Checkout {

		public function __construct() {

			add_shortcode( 'qpr_checkout', array( $this, 'render_checkout' ) );
			add_action( 'wp_loaded', array( $this, 'add_quote' ) );
			add_shortcode( 'qpr_checkout_confirmation', array( $this, 'render_checkout_confirmation' ) );

		}

		public function render_checkout( $attributes, $content = null ) {

			global $wpdb;
			$session_key = $_COOKIE['qpr_session'];
			$session_value = $wpdb->get_results( "SELECT session_value FROM `{$wpdb->prefix}qpr_sessions` WHERE `session_key` = '" . $session_key . "'" )[0]->session_value;
			$session_value = unserialize( $session_value );

			ob_start();

			// Check if cart has been emptied before got here

			if( empty( $session_value['cart'] ) ) {

				_e( 'Cart empty', 'quote-press' );

			} else { ?>

				<div class="qpr-checkout">
					<?php if( is_user_logged_in() ) {

						$user = wp_get_current_user();

						if( current_user_can('manage_options') || in_array( 'qpr_customer', $user->roles ) ) {

							$first_name = get_user_meta( $user->ID, 'first_name', true ); // standard WP
							$last_name = get_user_meta( $user->ID, 'last_name', true ); // standard WP
							$phone = get_user_meta( $user->ID, 'qpr_phone', true );
							$company = get_user_meta( $user->ID, 'qpr_company', true );
							$address_line_1 = get_user_meta( $user->ID, 'qpr_address_line_1', true );
							$address_line_2 = get_user_meta( $user->ID, 'qpr_address_line_2', true );
							$city = get_user_meta( $user->ID, 'qpr_city', true );
							$state = get_user_meta( $user->ID, 'qpr_state', true );
							$postcode = get_user_meta( $user->ID, 'qpr_postcode', true );
							$customer_country = get_user_meta( $user->ID, 'qpr_country', true );
							$countries = qpr_get_countries(); ?>

							<form method="post">
								<?php wp_nonce_field( 'qpr-nonce-checkout' ); ?>
								<input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">

								<p><?php _e( 'To submit your quote request please fill in the following details.', 'quote-press' ); ?></p>
								
								<h2><?php _e( 'Billing Details', 'quote-press' ); ?></h2>

								<div id="qpr-checkout-billing-fields">
									<div>
										<label for="qpr-billing-email"><?php _e( 'Email', 'quote-press' ); ?></label>
										<input id="qpr-billing-email" type="text" name="billing_email" value="<?php echo $user->user_email; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-phone"><?php _e( 'Phone', 'quote-press' ); ?></label>
										<input id="qpr-billing-phone" type="text" name="billing_phone" value="<?php echo $phone; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-first-name"><?php _e( 'First Name', 'quote-press' ); ?></label>
										<input id="qpr-billing-first-name" type="text" name="billing_first_name" value="<?php echo $first_name; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-last-name"><?php _e( 'Last Name', 'quote-press' ); ?></label>
										<input id="qpr-billing-last-name" type="text" name="billing_last_name" value="<?php echo $last_name; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-company"><?php _e( 'Company', 'quote-press' ); ?></label>
										<input id="qpr-billing-company" type="text" name="billing_company" value="<?php echo $company; ?>">
									</div>
									<div>
										<label for="qpr-billing-address-line-1"><?php _e( 'Address Line 1', 'quote-press' ); ?></label>
										<input id="qpr-billing-address-line-1" type="text" name="billing_address_line_1" value="<?php echo $address_line_1; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-address-line-2"><?php _e( 'Address Line 2', 'quote-press' ); ?></label>
										<input id="qpr-billing-address-line-2" type="text" name="billing_address_line_2" value="<?php echo $address_line_2; ?>">
									</div>
									<div>
										<label for="qpr-billing-city"><?php _e( 'City', 'quote-press' ); ?></label>
										<input id="qpr-billing-city" type="text" name="billing_city" value="<?php echo $city; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-state"><?php _e( 'State', 'quote-press' ); ?></label>
										<input id="qpr-billing-state" type="text" name="billing_state" value="<?php echo $state; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-postcode"><?php _e( 'Postcode/Zip', 'quote-press' ); ?></label>
										<input id="qpr-billing-postcode" type="text" name="billing_postcode" value="<?php echo $postcode; ?>" required>
									</div>
									<div>
										<label for="qpr-billing-country"><?php _e( 'Country', 'quote-press' ); ?></label>
										<select id="qpr-billing-country" type="text" name="billing_country" required>
											<?php foreach( $countries as $country ) { ?>
												<option value="<?php echo $country['abbreviation']; ?>"<?php echo ( $customer_country == $country['abbreviation'] ? ' selected' : '' ); ?>><?php echo $country['country']; ?> <?php echo __( '(', 'quote-press' ) . $country['abbreviation'] . __( ')', 'quote-press' ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>

								<h2><?php _e( 'Shipping Details', 'quote-press' ); ?></h2>

								<label><input id="checkout-shipping-same-as-billing" type="checkbox" checked> <?php _e( 'Same as billing details', 'quote-press' ); ?></label>
								
								<div id="qpr-checkout-shipping-fields">
									<div>
										<label for="qpr-shipping-first-name"><?php _e( 'First Name', 'quote-press' ); ?></label>
										<input id="qpr-shipping-first-name" type="text" name="shipping_first_name" value="<?php echo $first_name; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-last-name"><?php _e( 'Last Name', 'quote-press' ); ?></label>
										<input id="qpr-shipping-last-name" type="text" name="shipping_last_name" value="<?php echo $last_name; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-company"><?php _e( 'Company', 'quote-press' ); ?></label>
										<input id="qpr-shipping-company" type="text" name="shipping_company" value="<?php echo $company; ?>">
									</div>
									<div>
										<label for="qpr-shipping-address-line-1"><?php _e( 'Address Line 1', 'quote-press' ); ?></label>
										<input id="qpr-shipping-address-line-1" type="text" name="shipping_address_line_1" value="<?php echo $address_line_1; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-address-line-2"><?php _e( 'Address Line 2', 'quote-press' ); ?></label>
										<input id="qpr-shipping-address-line-2" type="text" name="shipping_address_line_2" value="<?php echo $address_line_2; ?>">
									</div>
									<div>
										<label for="qpr-shipping-city"><?php _e( 'City', 'quote-press' ); ?></label>
										<input id="qpr-shipping-city" type="text" name="shipping_city" value="<?php echo $city; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-state"><?php _e( 'State', 'quote-press' ); ?></label>
										<input id="qpr-shipping-state" type="text" name="shipping_state" value="<?php echo $state; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-postcode"><?php _e( 'Postcode/Zip', 'quote-press' ); ?></label>
										<input id="qpr-shipping-postcode" type="text" name="shipping_postcode" value="<?php echo $postcode; ?>" required>
									</div>
									<div>
										<label for="qpr-shipping-country"><?php _e( 'Country', 'quote-press' ); ?></label>
										<select id="qpr-shipping-country" type="text" name="shipping_country" required>
											<?php foreach( $countries as $country ) { ?>
												<option value="<?php echo $country['abbreviation']; ?>"<?php echo ( $customer_country == $country['abbreviation'] ? ' selected' : '' ); ?>><?php echo $country['country']; ?> <?php echo __( '(', 'quote-press' ) . $country['abbreviation'] . __( ')', 'quote-press' ); ?></option>
											<?php } ?>
										</select>
									</div>
								</div>

								<div>
									<input type="hidden" name="session_key" value="<?php echo $_COOKIE['qpr_session']; ?>">
									<div><label><input id="checkout-update-address-on-account" name="update_address_on_account" type="checkbox" checked> <?php _e( 'Update address on account for next time', 'quote-press' ); ?></label></div>
									<button id="qpr-submit-quote-request" class="button" name="submit_quote_request"><?php _e( 'Submit Quote Request', 'quote-press' ); ?></button>
								</div>

							</form>

						<?php }

					} else { ?>

						<p><strong><?php _e( 'You are not signed in.', 'quote-press' ); ?></strong></p>
						<p><?php _e( 'To submit your quote request:', 'quote-press' ); ?></p>
						<p><a href="<?php echo home_url( '/login/' ); ?>"><?php _e( 'Login', 'quote-press' ); ?></a> <?php _e( 'or', 'quote-press' ); ?> <a href="<?php echo home_url( '/register/?return=checkout' ); ?>"><?php _e( 'Register an account', 'quote-press' ); ?></a><?php _e( '.', 'quote-press' ); ?></p>

					<?php } ?>
					
				</div>

			<?php }

			return ob_get_clean();

		}

		public function add_quote() {

			if( isset( $_POST['submit_quote_request'] ) ) {

				check_admin_referer( 'qpr-nonce-checkout' );

				// Get cart contents

				if( isset( $_POST['session_key'] ) ) {

					global $wpdb;

					// Add user role (so if someone was already a subscriber and have an account then they also get the customer role added)

					$current_user = wp_get_current_user();
					$current_user->add_role( 'qpr_customer' );
					$session_key = sanitize_text_field( $_POST['session_key'] );
					$session_value = $wpdb->get_results( "SELECT session_value FROM {$wpdb->prefix}qpr_sessions WHERE session_key = '{$session_key}'" )[0];
					$session_value = unserialize( $session_value->session_value );
					$update_address_on_account = sanitize_text_field( $_POST['update_address_on_account'] );

					// Quote data
					$new_post = array(
						'post_title'    => '',
						'post_status'   => 'qpr-pending',
						'post_author'   => 0,
						'post_type'   => 'qpr_quote',
						'meta_input' => array(
							'_qpr_user' => $current_user->ID,
							'_qpr_quote_date_requested' => time(),
							'_qpr_valid_until' => strtotime( '+30 days', strtotime( date( 'Y-m-d', time() ) . '23:59:59' ) ), // 30 days from now
							'_qpr_currency' => get_option( 'qpr_default_currency' ),
							'_qpr_billing_first_name' => sanitize_text_field( $_POST['billing_first_name'] ),
							'_qpr_billing_last_name' => sanitize_text_field( $_POST['billing_last_name'] ),
							'_qpr_billing_company' => sanitize_text_field( $_POST['billing_company'] ),
							'_qpr_billing_address_line_1' => sanitize_text_field( $_POST['billing_address_line_1'] ),
							'_qpr_billing_address_line_2' => sanitize_text_field( $_POST['billing_address_line_2'] ),
							'_qpr_billing_city' => sanitize_text_field( $_POST['billing_city'] ),
							'_qpr_billing_state' => sanitize_text_field( $_POST['billing_state'] ),
							'_qpr_billing_postcode' => sanitize_text_field( $_POST['billing_postcode'] ),
							'_qpr_billing_country' => sanitize_text_field( $_POST['billing_country'] ),
							'_qpr_billing_email' => sanitize_email( $_POST['billing_email'] ),
							'_qpr_billing_phone' => sanitize_text_field( $_POST['billing_phone'] ),
							'_qpr_shipping_first_name' => sanitize_text_field( $_POST['shipping_first_name'] ),
							'_qpr_shipping_last_name' => sanitize_text_field( $_POST['shipping_last_name'] ),
							'_qpr_shipping_company' => sanitize_text_field( $_POST['shipping_company'] ),
							'_qpr_shipping_address_line_1' => sanitize_text_field( $_POST['shipping_address_line_1'] ),
							'_qpr_shipping_address_line_2' => sanitize_text_field( $_POST['shipping_address_line_2'] ),
							'_qpr_shipping_city' => sanitize_text_field( $_POST['shipping_city'] ),
							'_qpr_shipping_state' => sanitize_text_field( $_POST['shipping_state'] ),
							'_qpr_shipping_postcode' => sanitize_text_field( $_POST['shipping_postcode'] ),
							'_qpr_shipping_country' => sanitize_text_field( $_POST['shipping_country'] ),
							'_qpr_quote_products' => $session_value['cart'],
							'_qpr_grand_totals_discount' => 0,
							'_qpr_grand_totals_shipping' => 0,
							'_qpr_grand_totals_shipping_tax' => 0,
							'_qpr_grand_totals_tax' => 0,
							'_qpr_grand_totals_total' => 0,
						)
					);

					$post_id = wp_insert_post( $new_post, $wp_error );

					// Update post title now got it

					$new_post = array(
						'ID'           => $post_id,
						'post_title'   => $post_id,
					);
					wp_update_post( $new_post );

					// Update customers account address

					if( $update_address_on_account == 'on' ) {

						update_user_meta( $current_user->ID, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
						wp_update_user( array( 'ID' => $current_user->ID, 'first_name' => sanitize_text_field( $_POST['billing_first_name'] ) ) );

						update_user_meta( $current_user->ID, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
						wp_update_user( array( 'ID' => $current_user->ID, 'last_name' => sanitize_text_field( $_POST['billing_last_name'] ) ) );

						update_user_meta( $current_user->ID, 'qpr_phone', sanitize_text_field( $_POST['billing_phone'] ) );
						update_user_meta( $current_user->ID, 'qpr_company', sanitize_text_field( $_POST['billing_company'] ) );
						update_user_meta( $current_user->ID, 'qpr_address_line_1', sanitize_text_field( $_POST['billing_address_line_1'] ) );
						update_user_meta( $current_user->ID, 'qpr_address_line_2', sanitize_text_field( $_POST['billing_address_line_2'] ) );
						update_user_meta( $current_user->ID, 'qpr_city', sanitize_text_field( $_POST['billing_city'] ) );
						update_user_meta( $current_user->ID, 'qpr_state', sanitize_text_field( $_POST['billing_state'] ) );
						update_user_meta( $current_user->ID, 'qpr_postcode', sanitize_text_field( $_POST['billing_postcode'] ) );
						update_user_meta( $current_user->ID, 'qpr_country', sanitize_text_field( $_POST['billing_country'] ) );

					}

					if( get_option( 'qpr_customer_notifications' ) == 'on' ) {

						$to = get_post_meta( $post_id, '_qpr_billing_email', true );
						$subject = __( 'Quote Requested', 'quote-press' );
						$body = QPR_Templates::get_email_body( 'pending-customer' );
						$body = QPR_Templates::replace_quick_tags( $body, $post_id, '' );
						$headers = array('Content-Type: text/html; charset=UTF-8');
						wp_mail( $to, $subject, $body, $headers );

					}

					if( get_option( 'qpr_pending_notification' ) == 'on' ) {

						$to = get_option( 'qpr_notification_email_address' );
						$subject = __( 'Quote ID:', 'quote-press' ) . ' ' . $post_id . ' ' . __( 'Pending', 'quote-press' );
						$body = QPR_Templates::get_email_body( 'pending' );
						$body = QPR_Templates::replace_quick_tags( $body, $post_id, '' );
						$headers = array('Content-Type: text/html; charset=UTF-8');
						wp_mail( $to, $subject, $body, $headers );

					}

					// Remove cart from session

					unset( $session_value['cart'] );

					$new_session_value = serialize($session_value);

					$wpdb->query( "UPDATE {$wpdb->prefix}qpr_sessions SET session_value = '{$new_session_value}' WHERE session_key = '{$session_key}'" );

					// Redirect to checkout confirmation page

					wp_redirect( home_url( '/checkout-confirmation/?quote=' . $post_id ) );

					// Exit

					exit;

				}

			}

		}

		public function render_checkout_confirmation( $attributes, $content = null ) {

			echo __( 'Thank you for your quote request, we will email you a confirmation and let you know when your quote is ready for review. You can also access your quotes via your account.', 'quote-press' ) . '<br>' . __( 'Your Quote ID is:', 'quote-press' ) . ' ' . $_GET['quote'] . __( '.', 'quote-press' );

		}

	}

}