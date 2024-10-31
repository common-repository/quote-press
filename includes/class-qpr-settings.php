<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Settings' ) ) {

	class QPR_Settings {

		static function page() {

			global $wpdb;

			if( isset( $_POST['qpr_settings'] ) && isset( $_POST['submit'] ) ) {

				check_admin_referer( 'qpr-nonce-settings' );

				if( $_GET['tab'] == 'general' || !isset( $_GET['tab'] ) ) {

					$default_country = sanitize_text_field( $_POST['default_country'] );
					( isset( $_POST['default_country'] ) ? update_option( 'qpr_default_country', $default_country ) : update_option( 'qpr_default_country', '' ) );

					$default_currency = sanitize_text_field( $_POST['default_currency'] );
					( isset( $_POST['default_currency'] ) ? update_option( 'qpr_default_currency', $default_currency ) : update_option( 'qpr_default_currency', '' ) );

				} elseif( $_GET['tab'] == 'cart' ) {

					$add_to_cart_redirect = sanitize_text_field( $_POST['add_to_cart_redirect'] );
					( isset( $_POST['add_to_cart_redirect'] ) ? update_option( 'qpr_add_to_cart_redirect', $add_to_cart_redirect ) : update_option( 'qpr_add_to_cart_redirect', '' ) );

					$display_skus_in_cart = sanitize_text_field( $_POST['display_skus_in_cart'] );
					( isset( $_POST['display_skus_in_cart'] ) ? update_option( 'qpr_display_skus_in_cart', $display_skus_in_cart ) : update_option( 'qpr_display_skus_in_cart', '' ) );

				} elseif( $_GET['tab'] == 'tax' ) {

					$product_price_tax_status = sanitize_text_field( $_POST['product_price_tax_status'] );
					( isset( $_POST['product_price_tax_status'] ) ? update_option( 'qpr_product_price_tax_status', $product_price_tax_status ) : update_option( 'qpr_product_price_tax_status', '' ) );

				} elseif( $_GET['tab'] == 'payments' ) {

					foreach( QPR_Payments::payment_options() as $payment_option_key => $payment_option_label ) {
						( $_POST['payments_' . $payment_option_key] == 'on' ? update_option( 'qpr_payments_' . $payment_option_key, sanitize_text_field( $_POST['payments_' . $payment_option_key] ) ) : delete_option( 'qpr_payments_' . $payment_option_key ) );
					}

					foreach( QPR_Payments::payment_option_settings() as $payment_option_key => $payment_option_settings ) {

						foreach( $payment_option_settings as $payment_option_setting ) {

							$payment_option_setting_key = str_replace( '-', '_', $payment_option_setting['id'] );

							( isset( $_POST['payments_' . $payment_option_setting_key] ) ? update_option( 'qpr_payments_' . $payment_option_setting_key, sanitize_textarea_field( $_POST['payments_' . $payment_option_setting_key] ) ) : '' );

						}

					}

				} elseif( $_GET['tab'] == 'notifications' ) {

					$notification_email_address = sanitize_email( $_POST['notification_email_address'] );
					( isset( $_POST['notification_email_address'] ) ? update_option( 'qpr_notification_email_address', $notification_email_address ) : update_option( 'qpr_notification_email_address', '' ) );

					$notification_email_color_background = sanitize_text_field( $_POST['notification_email_color_background'] );
					( isset( $_POST['notification_email_color_background'] ) ? update_option( 'qpr_notification_email_color_background', $notification_email_color_background ) : update_option( 'qpr_notification_email_color_background', '' ) );

					$notification_email_color_primary = sanitize_text_field( $_POST['notification_email_color_primary'] );
					( isset( $_POST['notification_email_color_primary'] ) ? update_option( 'qpr_notification_email_color_primary', $notification_email_color_primary ) : update_option( 'qpr_notification_email_color_primary', '' ) );

					$pending_notification = sanitize_text_field( $_POST['pending_notification'] );
					( $_POST['pending_notification'] == 'on' ? update_option( 'qpr_pending_notification', $pending_notification ) : update_option( 'qpr_pending_notification', '' ) );
					
					$paid_notification = sanitize_text_field( $_POST['paid_notification'] );
					( $_POST['paid_notification'] == 'on' ? update_option( 'qpr_paid_notification', $paid_notification ) : update_option( 'qpr_paid_notification', '' ) );

					$paid_unconf_notification = sanitize_text_field( $_POST['paid_unconf_notification'] );
					( $_POST['paid_unconf_notification'] == 'on' ? update_option( 'qpr_paid_unconf_notification', $paid_unconf_notification ) : update_option( 'qpr_paid_unconf_notification', '' ) );
					
					$sent_notification = sanitize_text_field( $_POST['sent_notification'] );
					( $_POST['sent_notification'] == 'on' ? update_option( 'qpr_sent_notification', $sent_notification ) : update_option( 'qpr_sent_notification', '' ) );
					
					$customer_notifications = sanitize_text_field( $_POST['customer_notifications'] );
					( $_POST['customer_notifications'] == 'on' ? update_option( 'qpr_customer_notifications', $customer_notifications ) : update_option( 'qpr_customer_notifications', '' ) );

				} elseif( $_GET['tab'] == 'setup' ) {

					if( $_POST['create_pages'] == 'on' ) {

						// Delete old pages

						$posts_to_delete = $wpdb->get_results( "SELECT ID FROM `{$wpdb->prefix}posts` WHERE `post_name` IN( 'login', 'account', 'register', 'lost-password', 'password-reset', 'checkout-confirmation', 'cart', 'checkout' )" );

						foreach( $posts_to_delete as $post_to_delete ) {

							$post_to_delete = $post_to_delete->ID;
							wp_delete_post( $post_to_delete, true );

						}

						// Add new pages

						$page_definitions = array(
							'account' => array(
								'title' => __( 'Account', 'quote-press' ),
								'content' => '[qpr_account]'
							),
							'cart' => array(
								'title' => __( 'Cart', 'quote-press' ),
								'content' => '[qpr_cart]'
							),
							'checkout' => array(
								'title' => __( 'Checkout', 'quote-press' ),
								'content' => '[qpr_checkout]'
							),
							'checkout-confirmation' => array(
								'title' => __( 'Checkout Confirmation', 'quote-press' ),
								'content' => '[qpr_checkout_confirmation]'
							),
							'login' => array(
								'title' => __( 'Login', 'quote-press' ),
								'content' => '[qpr_login]'
							),
							'lost-password' => array(
								'title' => __( 'Lost Password', 'quote-press' ),
								'content' => '[qpr_lost_password]'
							),
							'password-reset' => array(
								'title' => __( 'Password Reset', 'quote-press' ),
								'content' => '[qpr_password_reset]'
							),
							'register' => array(
								'title' => __( 'Register', 'quote-press' ),
								'content' => '[qpr_register]'
							),
						);

						foreach( $page_definitions as $slug => $page ) {

							// Check that the page doesn't exist already

							$query = new WP_Query( 'pagename=' . $slug );

							if( !$query->have_posts() ) {

								wp_insert_post(
									array(
										'post_content'   => $page['content'],
										'post_name'      => $slug,
										'post_title'     => $page['title'],
										'post_status'    => 'publish',
										'post_type'      => 'page',
										'ping_status'    => 'closed',
										'comment_status' => 'closed',
									)
								);

							}

						}

					}

				}

				echo '<div class="notice notice-success"><p>' . __( 'Saved.', 'quote-press' ) . ( $_POST['create_pages'] == 'on' ? ' ' . __( 'Pages created successfully.', 'quote-press' ) : '' ) . '</p></div>';

			} ?>

			<div class="wrap" id="qpr-settings">
				<h1 class="wp-heading-inline qpr-settings-heading"><?php _e( 'Settings', 'quote-press' ); ?><a href="<?php echo QPR_URL; ?>" target="_blank"><img id="qpr-logo" src="<?php echo plugin_dir_url( __DIR__ ) . 'assets/images/logo.svg'; ?>"></a></h1>
				<h2 class="nav-tab-wrapper">
					<a href="<?php echo remove_query_arg( 'tab' ); ?>" class="nav-tab<?php if( empty( $_GET['tab'] ) ) { echo ' nav-tab-active'; } ?>"><?php _e( 'General', 'quote-press' ); ?></a>
					<a href="<?php echo add_query_arg( 'tab', 'cart' ); ?>" class="nav-tab<?php if( isset( $_GET['tab'] ) && $_GET['tab'] == 'cart' ) { echo ' nav-tab-active'; } ?>"><?php _e( 'Cart', 'quote-press' ); ?></a>
					<a href="<?php echo add_query_arg( 'tab', 'tax' ); ?>" class="nav-tab<?php if( isset( $_GET['tab'] ) && $_GET['tab'] == 'tax' ) { echo ' nav-tab-active'; } ?>"><?php _e( 'Tax', 'quote-press' ); ?></a>
					<a href="<?php echo add_query_arg( 'tab', 'payments' ); ?>" class="nav-tab<?php if( isset( $_GET['tab'] ) && $_GET['tab'] == 'payments' ) { echo ' nav-tab-active'; } ?>"><?php _e( 'Payments', 'quote-press' ); ?></a>
					<a href="<?php echo add_query_arg( 'tab', 'notifications' ); ?>" class="nav-tab<?php if( isset( $_GET['tab'] ) && $_GET['tab'] == 'notifications' ) { echo ' nav-tab-active'; } ?>"><?php _e( 'Notifications', 'quote-press' ); ?></a>
					<a href="<?php echo add_query_arg( 'tab', 'setup' ); ?>" class="nav-tab<?php if( isset( $_GET['tab'] ) && $_GET['tab'] == 'setup' ) { echo ' nav-tab-active'; } ?>"><?php _e( 'Setup', 'quote-press' ); ?></a>
				</h2>
				<form name="post" action="" method="post" id="post">
					<?php wp_nonce_field( 'qpr-nonce-settings' ); ?>
					<input type="hidden" name="qpr_settings">
					<?php if( !isset( $_GET['tab'] ) ) {
						$countries = qpr_get_countries();
						$currencies = qpr_get_currencies(); ?>
						<h2><?php _e( 'General', 'quote-press' ); ?></h2>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="qpr-default-country"><?php _e( 'Default Country', 'quote-press' ); ?></label>
									</th>
									<td>
										<select id="qpr-default-country" name="default_country" required>
											<?php foreach( $countries as $country ) { ?>
												<option value="<?php echo $country['abbreviation']; ?>"<?php echo ( get_option( 'qpr_default_country' ) == $country['abbreviation'] ? ' selected' : '' ); ?>><?php echo $country['country']; ?> (<?php echo $country['abbreviation']; ?>)</option>
											<?php } ?>
										</select>
										<p class="description"><?php _e( 'Set the default country, used for default country option when users create an account.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-default-currency"><?php _e( 'Default Currency', 'quote-press' ); ?></label>
									</th>
									<td>
										<select id="qpr-default-currency" name="default_currency" required>
											<?php foreach( $currencies as $currency ) { ?>
												<option value="<?php echo $currency['iso']['code']; ?>"<?php echo ( get_option( 'qpr_default_currency' ) == $currency['iso']['code'] ? ' selected' : '' ); ?>><?php echo $currency['iso']['code'] . ' (' . $currency['name'] . ')'; ?></option>
											<?php } ?>
										</select>
										<p class="description"><?php _e( 'The default currency for quotes.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-session-expiry"><?php _e( 'Session Expiry (days)', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-session-expiry" name="session_expiry" type="number" value="<?php echo get_option('qpr_session_expiry'); ?>" class="regular-text" min="1" max="365" required>
										<p class="description"><?php _e( 'Expiration limit for a users session including their cart contents.', 'quote-press' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					<?php } elseif( $_GET['tab'] == 'cart' ) { ?>
						<h2><?php _e( 'Cart', 'quote-press' ); ?></h2>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="qpr-add-to-cart-redirect"><?php _e( 'Add to Cart Redirect' ); ?></label>
									</th>
									<td>
										<input id="qpr-add-to-cart-redirect" type="checkbox" name="add_to_cart_redirect"<?php echo ( get_option( 'qpr_add_to_cart_redirect' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Redirects user to cart page upon adding product to cart.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-display-skus-in-cart"><?php _e( 'Display SKUs in Cart' ); ?></label>
									</th>
									<td>
										<input id="qpr-display-skus-in-cart" type="checkbox" name="display_skus_in_cart"<?php echo ( get_option( 'qpr_display_skus_in_cart' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Displays a product\'s SKU within cart if available.', 'quote-press' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					<?php } elseif( $_GET['tab'] == 'tax' ) { ?>
						<h2><?php _e( 'Tax', 'quote-press' ); ?></h2>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="qpr-product-price-tax-status"><?php _e( 'Product Price Tax Status', 'quote-press' ); ?></label>
									</th>
									<td>
										<select id="qpr-product-price-tax-status" name="product_price_tax_status" required>
											<option value="inc"<?php echo ( get_option( 'qpr_product_price_tax_status' ) == 'inc' ? ' selected' : '' ); ?>><?php _e( 'Product prices include Tax', 'quote-press' ); ?></option>
											<option value="exc"<?php echo ( get_option( 'qpr_product_price_tax_status' ) == 'exc' ? ' selected' : '' ); ?>><?php _e( 'Product prices exclude Tax', 'quote-press' ); ?></option>
										</select>
										<p class="description"><?php _e( 'Select whether you will be entering product prices including or excluding tax.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label><?php _e( 'Tax Profiles', 'quote-press' ); ?></label>
									</th>
									<td>
										<a href="<?php echo get_admin_url( '', 'edit-tags.php?taxonomy=qpr_tax_profile' ) ?>"><?php _e( 'Manage Tax Profiles', 'quote-press' ); ?></a>
									</td>
								</tr>
							</tbody>
						</table>
					<?php } elseif( $_GET['tab'] == 'payments' ) { ?>
						<h2><?php _e( 'Payments', 'quote-press' ); ?></h2>
						<div class="qpr-settings-left">
							<table class="form-table">
								<tbody>
									<?php foreach( QPR_Payments::payment_options() as $payment_option_key => $payment_option_label ) {
										$payment_option_id = str_replace( '_', '-', $payment_option_key ); ?>
										<tr>
											<th scope="row">
												<label for="qpr-payments-<?php echo $payment_option_id; ?>"><?php echo $payment_option_label; ?></label>
											</th>
											<td>
												<input id="qpr-payments-<?php echo $payment_option_id; ?>" type="checkbox" name="payments_<?php echo $payment_option_key; ?>"<?php echo ( get_option( 'qpr_payments_' . $payment_option_key ) == 'on' ? ' checked' : '' ); ?>>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
							<p><?php echo sprintf( __( 'Accept more payment options with <a href="%s">QuotePress Pro</a>.', 'quote-press' ), get_admin_url( '', 'edit.php?post_type=qpr_quote&page=quote-press-pricing' ) ); ?></p>
						</div>
						<div class="qpr-settings-right">
							<?php foreach( QPR_Payments::payment_option_settings() as $payment_option_key => $payment_option_settings ) {
								$payment_option_id = str_replace( '_', '-', $payment_option_key );
								if( get_option( 'qpr_payments_' . $payment_option_key ) == 'on' ) { ?>
									<table class="form-table">
										<tbody>
											<?php foreach( $payment_option_settings as $payment_option_setting ) {
												$payment_option_setting_key = str_replace( '-', '_', $payment_option_setting['id'] ); ?>
												<tr>
													<th scope="row">
														<label for="qpr-payments-<?php echo $payment_option_setting['id']; ?>"><?php echo $payment_option_setting['label']; ?></label>
													</th>
													<?php if( $payment_option_setting['field'] == 'text' ) { ?>
														<td>
															<input type="text" id="qpr-payments-<?php echo $payment_option_setting['id']; ?>" name="payments_<?php echo $payment_option_setting_key; ?>" value="<?php echo get_option( 'qpr_payments_' . $payment_option_setting_key ); ?>">
															<p class="description"><?php echo $payment_option_setting['description']; ?></p>
														</td>
													<?php } elseif( $payment_option_setting['field'] == 'textarea' ) { ?>
														<td>
															<textarea id="qpr-payments-<?php echo $payment_option_setting['id']; ?>" name="payments_<?php echo $payment_option_setting_key; ?>"><?php echo get_option( 'qpr_payments_' . $payment_option_setting_key ); ?></textarea>
															<p class="description"><?php echo $payment_option_setting['description']; ?></p>
														</td>
													<?php } elseif( $payment_option_setting['field'] == 'select' ) { ?>
														<td>
															<select id="qpr-payments-<?php echo $payment_option_setting['id']; ?>" name="payments_<?php echo $payment_option_setting_key; ?>">
																<?php foreach( $payment_option_setting['options'] as $option_id => $option_label ) { ?>
																	<option value="<?php echo $option_id; ?>"<?php echo ( get_option( 'qpr_payments_' . $payment_option_setting_key ) == $option_id ? ' selected' : '' )  ?>><?php echo $option_label; ?></option>
																<?php } ?>
															</select>
															<p class="description"><?php echo $payment_option_setting['description']; ?></p>
														</td>
													<?php } ?>
												</tr>
											<?php } ?>
										</tbody>
									</table>
									<hr>
								<?php }
							} ?>
						</div>
					<?php } elseif( $_GET['tab'] == 'notifications' ) { ?>
						<h2><?php _e( 'Notifications', 'quote-press' ); ?></h2>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="qpr-notification-email-address"><?php _e( 'Notification Email Address', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-notification-email-address" type="email" name="notification_email_address" value="<?php echo get_option('qpr_notification_email_address'); ?>" required>
										<p class="description"><?php _e( 'Comma seperate for multiple emails', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-notification-email-color-background"><?php _e( 'Notification Email Color Background', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-notification-email-color-background" class="qpr-color-picker" type="text" name="notification_email_color_background" value="<?php echo get_option('qpr_notification_email_color_background'); ?>" required>
										<p class="description"><?php _e( 'Background color used within email notifications. If you are overriding email templates this setting may not have any effect.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-notification-email-color-primary"><?php _e( 'Notification Email Color Primary', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-notification-email-color-primary" class="qpr-color-picker" type="text" name="notification_email_color_primary" value="<?php echo get_option('qpr_notification_email_color_primary'); ?>" required>
										<p class="description"><?php _e( 'Primary color used within email notifications. If you are overriding email templates this setting may not have any effect.', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-pending-notification"><?php _e( 'Pending Notification', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-pending-notification" type="checkbox" name="pending_notification"<?php echo ( get_option( 'qpr_pending_notification' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Sent to notification email address', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-paid-notification"><?php _e( 'Paid Notification', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-paid-notification" type="checkbox" name="paid_notification"<?php echo ( get_option( 'qpr_paid_notification' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Sent to notification email address', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-paid-unconf-notification"><?php _e( 'Paid (Unconfirmed) Notification', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-paid-unconf-notification" type="checkbox" name="paid_unconf_notification"<?php echo ( get_option( 'qpr_paid_unconf_notification' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Sent to notification email address', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-sent-notification"><?php _e( 'Sent Notification', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-sent-notification" type="checkbox" name="sent_notification"<?php echo ( get_option( 'qpr_sent_notification' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Sent to notification email address', 'quote-press' ); ?></p>
									</td>
								</tr>
								<tr>
									<th scope="row">
										<label for="qpr-customer-notifications"><?php _e( 'Customer Notifications', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-customer-notifications" type="checkbox" name="customer_notifications"<?php echo ( get_option( 'qpr_customer_notifications' ) == 'on' ? ' checked' : '' ); ?>>
										<p class="description"><?php _e( 'Sends notifications to customer email address, it is strongly recommended this option is enabled. Only disable if you wish to manually contact customers when a quote has been updated.', 'quote-press' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					<?php } elseif( $_GET['tab'] == 'setup' ) { update_option( 'qpr_first_time', 0 ); // after page visited first time set to false so removes notice ?>
						<h2><?php _e( 'Setup', 'quote-press' ); ?></h2>
						<p><?php _e( 'Now you have installed and activate QuotePress, before you begin we recommend following these optional steps to get up and running:', 'quote-press' ); ?></p>
						<hr>
						<h2><?php _e( 'Initial Setup', 'quote-press' ); ?></h2>
						<ol>
							<li><?php _e( 'Automatically create the pages QuotePress requires for users to navigate your website using the checkbox below and saving.', 'quote-press' ); ?></li>
							<li><?php _e( 'Edit the settings using the tabs in this section as required. We recommend reviewing the enabled payment methods in the Payments tab, customers can then use these payment methods after viewing their quote. Add Tax Profiles if required via the Tax tab, these can then be used when estimated a quote\'s product prices and totals.', 'quote-press' ); ?></li>
							<li><?php _e( 'Create product categories via QuotePress > Categories', 'quote-press' ); ?></li>
							<li><?php _e( 'Create products via QuotePress > Products and assign to categories', 'quote-press' ); ?></li>
							<li><?php _e( 'Create required attributes and variations via QuotePress > Attributes/Variations. For attributes if you use the enable variations option you can then assign data such as a price to a specific variation of a product which is used for estimating prices and more.', 'quote-press' ); ?></li>
							<li><?php _e( 'Setup links on your website to the newly created pages for cart, account, login/register, etc so users can navigate around your website.', 'quote-press' ); ?></li>
							<li><?php _e( 'Link to your product categories in your website\'s menus via Appearance > Menus, click screen options from the top right and enable Product Categories to allow you to select these.', 'quote-press' ); ?></li>
							<li><?php _e( 'Add any required widgets to your theme\'s sidebar such as filters, applied filters, sorting, etc.', 'quote-press' ); ?></li>
						</ol>
						<hr>
						<h2><?php _e( 'Theme Integration', 'quote-press' ); ?></h2>
						<p><?php _e( 'QuotePress is designed to work with all WordPress themes and has 2 simple page templates - archive.php (for product categories) and product.php (for single product pages), all other pages use your standard WordPress page template from your theme.', 'quote-press' ); ?></p>
						<p><?php _e( 'The only issue you may find when integrating QuotePress into your is with the product category and single product pages not displaying optimally e.g. sidebars in incorrect position. If this occurs it is usually because your theme has additional HTML elements such as wrapper containers which our 2 templates do not include. Because including these non-standard elements are decided by theme developers we can\'t ensure QuotePress legislates for this. If you notice this issue we recommend overriding the 2 templates (see below) and adding in the equivalent elements required by your theme to ensure these 2 templates display correctly.', 'quote-press' ); ?>
						</p>
						<p><?php echo sprintf( __( 'Alternatively, if you don\'t want to override templates we recommend using one of the following free WordPress themes with QuotePress: <a href="%s" target="_blank">Zakra</a> and <a href="%s" target="_blank">Storefront</a> (Storefront is a WooCommerce template but works great with QuotePress).', 'quote-press' ), 'https://wordpress.org/themes/zakra/', 'https://wordpress.org/themes/storefront/' ); ?></p>
						<hr>
						<h2><?php _e( 'Template Overrides', 'quote-press' ); ?></h2>
						<p><?php _e( 'Navigate to the following directory: <code>wp-content/plugins/quote-press/templates/</code> and copy the templates in the same file/folder structure to <code>wp-content/themes/your-theme-name/quote-press/templates/</code>. These files will then be used instead of the default templates.' ); ?></p>
						<hr>
						<h2><?php _e( 'Page Creation', 'quote-press' ); ?></h2>
						<p><?php _e( 'Use the setting below to create pages required for QuotePress.', 'quote-press' ); ?></p>
						<table class="form-table">
							<tbody>
								<tr>
									<th scope="row">
										<label for="qpr-create-pages"><?php _e( 'Create Pages', 'quote-press' ); ?></label>
									</th>
									<td>
										<input id="qpr-create-pages" type="checkbox" name="create_pages">
										<p class="description"><?php _e( 'Warning: Any existing pages with the slug account, cart, checkout, checkout-confirmation, login, lost-password, password-reset or register will be removed and recreated.', 'quote-press' ); ?></p>
									</td>
								</tr>
							</tbody>
						</table>
					<?php } ?>
					<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>
				</form>
			</div>

		<?php }

	}

}