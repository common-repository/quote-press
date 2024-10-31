<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Quotes' ) ) {

	class QPR_Quotes {

		public function __construct() {

			add_filter( 'bulk_actions-edit-qpr_quote', array( $this, 'remove_bulk_actions_edit_quote' ) );
			add_filter('post_row_actions', array( $this, 'remove_quick_edit' ),10,1);
			add_action( 'add_meta_boxes', array( $this, 'meta_boxes' ) );
			add_action( 'save_post_qpr_quote', array( $this, 'save' ) );
			add_action( 'save_post_qpr_quote', array( $this, 'send_to_customer' ), 20 ); // must be higher than save action above as we want changes there first, this number also relates to the add/remove actions in this function

		}

		public function remove_bulk_actions_edit_quote( $actions ){
		    unset( $actions[ 'edit' ] );
		    return $actions;
		}

		public function remove_quick_edit( $actions ) {

			if( get_post_type() == 'qpr_quote' ) {

				unset( $actions['inline hide-if-no-js'] );	
			}
		    
		    return $actions;

		}

		public function meta_boxes() {

			// Remove default meta boxes

			remove_meta_box( 'slugdiv', 'qpr_quote', 'normal' );

			// Add meta boxes

			global $post;

			if( get_post_type() == 'qpr_quote' ) {

				remove_meta_box( 'submitdiv', 'qpr_quote', 'side' );
				
				add_meta_box(
					'savebox',
					__( 'Actions', 'quote-press' ),
					array( $this, 'save_box' ),
					null,
					'side',
					'high'
				);

				add_meta_box(
					'qpr-summary',
					__( 'Quote', 'quote-press' ) . ' ' . $post->ID,
					array( $this, 'summary' ),
					null,
					'normal',
					'high'
				);

				add_meta_box(
					'qpr-contents',
					__( 'Contents', 'quote-press' ),
					array( $this, 'contents' ),
					null,
					'normal',
					'high'
				);

				add_meta_box(
					'qpr-notes-internal',
					__( 'Notes (Internal)', 'quote-press' ),
					array( $this, 'notes_internal' ),
					null,
					'side',
					'low'
				);

				add_meta_box(
					'qpr-notes-customer',
					__( 'Notes (To Customer)', 'quote-press' ),
					array( $this, 'notes_customer' ),
					null,
					'side',
					'low'
				);

			}

		}

		public function save_box() {

			// Class names within this function are as WordPress usually sets on standard save box

			global $post;

			$post_status = get_post_status( $post->ID );

			wp_nonce_field( 'qpr-nonce-quote', 'qpr-nonce-quote' );

			?>

			<button type="submit" class="button save_order button-primary" name="save" value="<?php echo 'auto-draft' === $post->post_status ? esc_attr__( 'Create', 'quote-press' ) : esc_attr__( 'Update', 'quote-press' ); ?>"><?php echo 'auto-draft' === $post->post_status ? esc_html__( 'Create', 'quote-press' ) : esc_html__( 'Update', 'quote-press' ); ?></button>

			<?php
			if( in_array( $post_status, qpr_contents_editable_quote_statuses() ) ) {
				echo '<button id="qpr-send-to-customer" name="send" class="button button-primary">Send to Customer</button>';
			} ?>

			<div>
				<?php if( current_user_can( 'delete_post', $post->ID ) ) {

					if( !EMPTY_TRASH_DAYS ) {
						$delete_text = __( 'Delete permanently', 'quote-press' );
					} else {
						$delete_text = __( 'Move to trash', 'quote-press' );
					} ?>

					<a class="submitdelete deletion button" href="<?php echo esc_url( get_delete_post_link( $post->ID ) ); ?>"><?php echo esc_html( $delete_text ); ?></a>
					
				<?php } ?>
			</div>

		<?php }

		public function notes_internal() {
			global $post;
			$notes_internal = get_post_meta( $post->ID, '_qpr_notes_internal', true );
			echo '<textarea name="notes_internal" class="qpr-notes">' . $notes_internal . '</textarea>';
		}

		public function notes_customer() {
			global $post;

			$quote_status = get_post_status( $post->ID );

			if( in_array( $quote_status, qpr_contents_editable_quote_statuses() ) ) {
				$contents_editable = true;
			} else {
				$contents_editable = false;
			}

			$notes_customer = get_post_meta( $post->ID, '_qpr_notes_customer', true );
			echo '<textarea name="notes_customer" class="qpr-notes"' . ( $contents_editable == false ? ' disabled' : '' ) . '>' . $notes_customer . '</textarea>';
			echo '<p class="description">' . __( 'Once quote has been sent to customer these notes are no longer editable.', 'quote-press' ) . '</p>';
		}

		public function summary() {

			global $post;

			$post_id = $post->ID;
			$quote_status = get_post_status( $post_id );

			if( in_array( $quote_status, qpr_contents_editable_quote_statuses() ) ) {
				$contents_editable = true;
			} else {
				$contents_editable = false;
			}

			$billing_first_name = get_post_meta( $post_id, '_qpr_billing_first_name', true );
			$billing_last_name = get_post_meta( $post_id, '_qpr_billing_last_name', true );
			$billing_company = get_post_meta( $post_id, '_qpr_billing_company', true );
			$billing_address_line_1 = get_post_meta( $post_id, '_qpr_billing_address_line_1', true );
			$billing_address_line_2 = get_post_meta( $post_id, '_qpr_billing_address_line_2', true );
			$billing_city = get_post_meta( $post_id, '_qpr_billing_city', true );
			$billing_state = get_post_meta( $post_id, '_qpr_billing_state', true );
			$billing_postcode = get_post_meta( $post_id, '_qpr_billing_postcode', true );
			$billing_country = get_post_meta( $post_id, '_qpr_billing_country', true );
			$billing_phone = get_post_meta( $post_id, '_qpr_billing_phone', true );
			$billing_email = get_post_meta( $post_id, '_qpr_billing_email', true );

			$shipping_first_name = get_post_meta( $post_id, '_qpr_shipping_first_name', true );
			$shipping_last_name = get_post_meta( $post_id, '_qpr_shipping_last_name', true );
			$shipping_company = get_post_meta( $post_id, '_qpr_shipping_company', true );
			$shipping_address_line_1 = get_post_meta( $post_id, '_qpr_shipping_address_line_1', true );
			$shipping_address_line_2 = get_post_meta( $post_id, '_qpr_shipping_address_line_2', true );
			$shipping_city = get_post_meta( $post_id, '_qpr_shipping_city', true );
			$shipping_state = get_post_meta( $post_id, '_qpr_shipping_state', true );
			$shipping_postcode = get_post_meta( $post_id, '_qpr_shipping_postcode', true );
			$shipping_country = get_post_meta( $post_id, '_qpr_shipping_country', true );

			$disabled = array();

			if( $quote_status == 'qpr-pending' ) {

				$disabled['qpr-pending'] = '';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = ' disabled';
				$disabled['qpr-paid-unconf'] = ' disabled';
				$disabled['qpr-cancelled'] = ' disabled';
				$disabled['qpr-completed'] = ' disabled';

			} elseif( $quote_status == 'qpr-sent' ) {

				$disabled['qpr-pending'] = '';
				$disabled['qpr-sent'] = '';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = ' disabled';
				$disabled['qpr-paid-unconf'] = ' disabled';
				$disabled['qpr-cancelled'] = ' disabled';
				$disabled['qpr-completed'] = ' disabled';

			} elseif( $quote_status == 'qpr-expired' ) {

				$disabled['qpr-pending'] = '';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = '';
				$disabled['qpr-paid'] = ' disabled';
				$disabled['qpr-paid-unconf'] = ' disabled';
				$disabled['qpr-cancelled'] = ' disabled';
				$disabled['qpr-completed'] = ' disabled';

			} elseif( $quote_status == 'qpr-paid' ) {

				$disabled['qpr-pending'] = ' disabled';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = '';
				$disabled['qpr-paid-unconf'] = '';
				$disabled['qpr-cancelled'] = '';
				$disabled['qpr-completed'] = '';

			} elseif( $quote_status == 'qpr-paid-unconf' ) {

				$disabled['qpr-pending'] = ' disabled';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = '';
				$disabled['qpr-paid-unconf'] = '';
				$disabled['qpr-cancelled'] = '';
				$disabled['qpr-completed'] = '';

			} elseif( $quote_status == 'qpr-cancelled' ) {

				$disabled['qpr-pending'] = ' disabled';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = '';
				$disabled['qpr-paid-unconf'] = '';
				$disabled['qpr-cancelled'] = '';
				$disabled['qpr-completed'] = '';

			} elseif( $quote_status == 'qpr-completed' ) {

				$disabled['qpr-pending'] = ' disabled';
				$disabled['qpr-sent'] = ' disabled';
				$disabled['qpr-expired'] = ' disabled';
				$disabled['qpr-paid'] = ' disabled';
				$disabled['qpr-paid-unconf'] = ' disabled';
				$disabled['qpr-cancelled'] = '';
				$disabled['qpr-completed'] = '';

			}

			$all_quote_statuses = qpr_get_quote_statuses( true );

			$customer = get_userdata( get_post_meta( $post_id, '_qpr_user', true ) );

			$countries = qpr_get_countries();

			$currencies = qpr_get_currencies();

			$quote_currency = get_post_meta( $post_id, '_qpr_currency', true ); ?>

			<div>
				<p>
					<strong><?php _e( 'Details', 'quote-press' ); ?></strong>
				</p>
				<p>
					<?php echo __( 'Date Requested:', 'quote-press' ) . '<br>' . get_the_date( 'Y-m-d H:i:s', $post_id ); ?>		
				</p>
				<p>
					<label>
						<?php _e( 'Valid Until:', 'quote-press' ); ?><br>
						<input type="text" name="valid_until" id="qpr-valid-until" class="datepicker" value="<?php echo ( get_post_meta( $post_id, '_qpr_valid_until', true ) ? date( 'Y-m-d', get_post_meta( $post_id, '_qpr_valid_until', true ) ) : '' ); ?>"<?php echo ( $contents_editable == false ? ' disabled' : '' ) ; ?> readonly required>
					</label>
				</p>
				<p>
					<label><?php _e( 'Status:', 'quote-press' ); ?><br>
					<select name="quote_status" id="qpr-quote-status">
						<?php foreach( $all_quote_statuses as $all_quote_status => $label ) { ?>
							<option value="<?php echo $all_quote_status; ?>"<?php echo ( $quote_status == $all_quote_status ? ' selected' : '' ); echo $disabled[$all_quote_status]; ?>><?php echo $label; ?></option>
						<?php } ?>
					</select>
					</label>
				</p>
				<p>
					<label><?php _e( 'Currency:', 'quote-press' ); ?><br>
					<select name="quote_currency" id="qpr-quote-currency"<?php echo ( $contents_editable == false ? ' disabled' : '' ) ; ?>>
						<?php foreach( $currencies as $currency ) { ?>
							<option value="<?php echo $currency['iso']['code']; ?>" data-currency-symbol="<?php echo $currency['units']['major']['symbol']; ?>"<?php echo ( $quote_currency == $currency['iso']['code'] ? ' selected' : '' ); ?>><?php echo $currency['iso']['code'] . ' (' . $currency['name'] . ')'; ?></option>
						<?php } ?>
					</select>
					</label>
				</p>

				<p>

					<?php if( !empty( $customer ) ) {

						echo __( 'Customer:', 'quote-press' ) . '<br>';
						echo QPR_Customers::customer_link( $customer->ID, 'all', false );

					} else { ?>

						<p>
							<label for="qpr-assign-customer-search"><?php _e( 'Assign Customer:', 'text-domain' ); ?></label><br>
							<input type="text" id="qpr-assign-customer-search" required><br>
							<div id="qpr-assign-customer-search-results" style="display: none;">
								<label for="qpr-assign-customer-search-results-select-customer"><?php _e( 'Select Customer:', 'text-domain' ); ?></label><br>
								<select id="qpr-assign-customer-search-results-select-customer" name="selected_customer" required>
									<option value=""><?php _e( 'No results', 'text-domain' ); ?></option>
								</select>
							</div>
						</p>

						<script>
							jQuery( document ).ready( function($) {

								$('#qpr-assign-customer-search').on( 'change keyup', function() {

									$('#qpr-assign-customer-search-results').show();

									// Set data to be sent via AJAX

									var data = {
										'action': 'qpr_assign_customer_search',
										'order_id': $( '#post_ID' ).val(),
										'search': $(this).val(),
										'nonce': $( '#qpr-nonce-quote' ).val(),
									};

									// Do the AJAX call and get response

									jQuery.post( ajaxurl, data, function( response ) {

										$('#qpr-assign-customer-search-results-select-customer').html( response );

									});

								});

							});
						</script>

					<?php } ?>
				</p>
			</div>
			<div>
				<p><strong><?php _e( 'Payment', 'quote-press' ); ?></strong></p>
				<?php $payment_details = get_post_meta( $post_id, '_qpr_payment_details', true );
				if( !empty( $payment_details ) ) {
					$payment_options = QPR_Payments::payment_options(); ?>
					<p><?php echo __( 'Option:', 'quote-press' ) . '<br>' . $payment_options[ $payment_details['option'] ]; ?></p>
				<?php } else {
					echo '<p>No payment</p>';
				} ?>
			</div>
			<div class="qpr-billing">
				<p><strong><?php _e( 'Billing Address', 'quote-press' ); ?></strong><button class="button button-small" id="qpr-billing-edit"><?php _e( 'Edit', 'quote-press' ); ?></button></p>
				<div class="qpr-initial">
					<p>
						<?php
						echo ( !empty( $billing_first_name ) || !empty( $billing_last_name ) ) ? $billing_first_name . ' ' . $billing_last_name . '<br>' : '';
						echo ( !empty( $billing_company ) ) ? $billing_company . '<br>' : '';
						echo ( !empty( $billing_address_line_1 ) ) ? $billing_address_line_1 . '<br>' : '';
						echo ( !empty( $billing_address_line_2 ) ) ? $billing_address_line_2 . '<br>' : '';
						echo ( !empty( $billing_city ) ) ? $billing_city . '<br>' : '';
						echo ( !empty( $billing_postcode ) ) ? $billing_postcode . '<br>' : '';
						echo ( !empty( $billing_country ) ) ? qpr_get_country_name( $billing_country ) . ' ' . __( '(', 'quote-press' ) . $billing_country . __( ')', 'quote-press') . '<br>' : '';
						?>
					</p>
					<p>
						<?php
						echo ( !empty( $billing_phone ) ) ? $billing_phone . '<br>' : '';
						echo ( !empty( $billing_email ) ) ? '<a href="mailto:' . $billing_email . '">' . $billing_email . '</a><br>' : '';
						?>
					</p>
				</div>
				<div class="qpr-edit">
					<p><label><?php _e( 'First Name:', 'quote-press' ); ?><br><input name="billing_first_name" type="text" value="<?php echo $billing_first_name; ?>" required></label></p>
					<p><label><?php _e( 'Last Name:', 'quote-press' ); ?><br><input name="billing_last_name" type="text" value="<?php echo $billing_last_name; ?>" required></label></p>
					<p><label><?php _e( 'Company:', 'quote-press' ); ?><br><input name="billing_company" type="text" value="<?php echo $billing_company; ?>"></label></p>
					<p><label><?php _e( 'Address Line 1:', 'quote-press' ); ?><br><input name="billing_address_line_1" type="text" value="<?php echo $billing_address_line_1; ?>" required></label></p>
					<p><label><?php _e( 'Address Line 2:', 'quote-press' ); ?><br><input name="billing_address_line_2" type="text" value="<?php echo $billing_address_line_2; ?>"></label></p>
					<p><label><?php _e( 'City:', 'quote-press' ); ?><br><input name="billing_city" type="text" value="<?php echo $billing_city; ?>" required></label></p>
					<p><label><?php _e( 'Postcode:', 'quote-press' ); ?><br><input name="billing_postcode" type="text" value="<?php echo $billing_postcode; ?>" required></label></p>
					<p>
						<label>
							<?php _e( 'Country:', 'quote-press' ); ?><br>
							<select name="billing_country" required>
								<?php foreach( $countries as $country ) { ?>
									<option value="<?php echo $country['abbreviation']; ?>"<?php echo ( $billing_country == $country['abbreviation'] ? ' selected' : '' ); ?>><?php echo $country['country']; ?> <?php echo __( '(', 'quote-press' ) . $country['abbreviation'] . __( ')', 'quote-press' ); ?></option>
								<?php } ?>
							</select>
						</label>
					</p>
					<p><label><?php _e( 'Phone:', 'quote-press' ); ?><br><input name="billing_phone" type="text" value="<?php echo $billing_phone; ?>" required></label></p>
					<p><label><?php _e( 'Email:', 'quote-press' ); ?><br><input name="billing_email" type="email" value="<?php echo $billing_email; ?>" required></label></p>
				</div>
			</div>
			<div class="qpr-shipping">
				<p><strong><?php _e( 'Shipping Address', 'quote-press' ); ?></strong><button class="button button-small" id="shipping-edit"><?php _e( 'Edit', 'quote-press' ); ?></button></p>
				<div class="qpr-initial">
					<p>
						<?php
						echo ( !empty( $shipping_first_name ) || !empty( $shipping_last_name ) ) ? $shipping_first_name . ' ' . $shipping_last_name . '<br>' : '';
						echo ( !empty( $shipping_company ) ) ? $shipping_company . '<br>' : '';
						echo ( !empty( $shipping_address_line_1 ) ) ? $shipping_address_line_1 . '<br>' : '';
						echo ( !empty( $shipping_address_line_2 ) ) ? $shipping_address_line_2 . '<br>' : '';
						echo ( !empty( $shipping_city ) ) ? $shipping_city . '<br>' : '';
						echo ( !empty( $shipping_postcode ) ) ? $shipping_postcode . '<br>' : '';
						echo ( !empty( $shipping_country ) ) ? qpr_get_country_name( $shipping_country ) . ' ' . __( '(', 'quote-press' ) . $shipping_country . __( ')', 'quote-press') . '<br>' : '';
						?>
					</p>
				</div>
				<div class="qpr-edit">
					<p><label><?php _e( 'First Name:', 'quote-press' ); ?><br><input name="shipping_first_name" type="text" value="<?php echo $shipping_first_name; ?>" required></label></p>
					<p><label><?php _e( 'Last Name:', 'quote-press' ); ?><br><input name="shipping_last_name" type="text" value="<?php echo $shipping_last_name; ?>" required></label></p>
					<p><label><?php _e( 'Company:', 'quote-press' ); ?><br><input name="shipping_company" type="text" value="<?php echo $shipping_company; ?>"></label></p>
					<p><label><?php _e( 'Address Line 1:', 'quote-press' ); ?><br><input name="shipping_address_line_1" type="text" value="<?php echo $shipping_address_line_1; ?>" required></label></p>
					<p><label><?php _e( 'Address Line 2:', 'quote-press' ); ?><br><input name="shipping_address_line_2" type="text" value="<?php echo $shipping_address_line_2; ?>"></label></p>
					<p><label><?php _e( 'City:', 'quote-press' ); ?><br><input name="shipping_city" type="text" value="<?php echo $shipping_city; ?>" required></label></p>
					<p><label><?php _e( 'Postcode:', 'quote-press' ); ?><br><input name="shipping_postcode" type="text" value="<?php echo $shipping_postcode; ?>" required></label></p>
					<p>
						<label>
							<?php _e( 'Country:', 'quote-press' ); ?><br>
							<select name="shipping_country" required>
								<?php foreach( $countries as $country ) { ?>
									<option value="<?php echo $country['abbreviation']; ?>"<?php echo ( $shipping_country == $country['abbreviation'] ? ' selected' : '' ); ?>><?php echo $country['country']; ?> <?php echo __( '(', 'quote-press' ) . $country['abbreviation'] . __( ')', 'quote-press' ); ?></option>
								<?php } ?>
							</select>
						</label>
					</p>
				</div>
			</div>

		<?php }

		public function contents() {

			global $post;

			$tax_profiles = get_option( 'qpr_tax_profiles' );
			$qpr_default_tax_profile = get_option( 'qpr_default_tax_profile' );

			$quote_status = get_post_status( $post->ID );

			if( in_array( $quote_status, qpr_contents_editable_quote_statuses() ) ) {
				$contents_editable = true;
			} else {
				$contents_editable = false;
			} ?>

			<table id="qpr-quote-contents" class="wp-list-table widefat fixed striped posts">
				<thead>
					<tr>
						<td><?php _e( 'Product', 'quote-press' ); ?></td>
						<td><?php _e( 'Variation', 'quote-press' ); ?></td>
						<td><?php _e( 'SKU', 'quote-press' ); ?></td>
						<td><?php _e( 'Qty', 'quote-press' ); ?></td>
						<td><?php _e( 'Price', 'quote-press' ); ?> <small><?php _e( '(each exc tax)', 'quote-press' ); ?></small></td>
						<td><?php _e( 'Tax', 'quote-press' ); ?> <small><?php _e( '(each)', 'quote-press' ); ?></small></td>
						<td><?php _e( 'Total', 'quote-press' ); ?></td>
						<td><?php _e( 'Remove', 'quote-press' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr><td colspan="8"><?php _e( 'Loading contents...', 'quote-press' ); ?></td></tr>
					<!-- ajax content -->
				</tbody>
				<tfoot>
					<tr><td colspan="8"><?php _e( 'Loading grand totals...', 'quote-press' ); ?></td></tr>
					<!-- ajax content -->
				</tfoot>
			</table>
			<button id="qpr-add-product" class="button"<?php echo ( $contents_editable == false ? ' disabled' : '' ); ?>><?php _e( 'Add Product', 'quote-press' ); ?></button>
			<button id="qpr-quote-estimate" class="button"<?php echo ( $contents_editable == false ? ' disabled' : '' ); ?>><?php _e( 'Estimate Prices & Taxes', 'quote-press' ); ?></button>
			<label>
				<?php _e( 'Estimated Tax Profile:', 'quote-press' ); ?>
				<select id="qpr-estimated-tax-profile"<?php echo ( $contents_editable == false ? ' disabled' : '' ); ?>>
					<option value=""><?php _e( 'None', 'quote-press' ); ?></option>
					<?php foreach( $tax_profiles as $tax_profile_id => $tax_profile_data ) {
						$selected = ( $qpr_default_tax_profile == $tax_profile_id ? ' selected' : '' );
						echo '<option value="' . $tax_profile_data['percent'] . '"' . $selected . '>' . get_term_by( 'id', $tax_profile_id, 'qpr_tax_profile' )->name . ' (' . $tax_profile_data['percent'] . '%)</option>';
					} ?>
				</select>
			</label>
			<button id="qpr-quote-contents-save" class="button button-primary"<?php echo ( $contents_editable == false ? ' disabled' : '' ); ?>><?php _e( 'Save Changes', 'quote-press' ); ?></button>

			<div id="qpr-add-product-expand">
				<div id="qpr-add-product-search"><label><?php _e( 'Search:', 'quote-press' ); ?> <input type="text"></label></div>
				<div id="qpr-add-product-search-results"><label><?php _e( 'Product:', 'quote-press' ); ?> <select></select></label></div>
				<div id="qpr-add-product-search-results-variation-select"></div>
				<div id="qpr-add-product-qty"><label><?php _e( 'Qty:', 'quote-press' ); ?> <input type="number" min="1" value="1" required></label></div>
				<button id="qpr-add-product-add" class="button"><?php _e( 'Add', 'quote-press' ); ?></button>
			</div>

		<?php }

		public function save( $post_id ) {

			// Data

			// Update post title to the post id (if a new quote being added by backend the post title needs setting to the post id or it will remain auto draft, unlike the ones from the frontend which get the post id added as a title un creation)

			remove_action('save_post_qpr_quote', array( $this, 'save' ) ); // this function must be removed and then re added otherwise the update post status causes an infinite loop

			wp_update_post(
				array(
					'ID' => $post_id,
					'post_title' => $post_id,
				)
			);

			add_action('save_post_qpr_quote', array( $this, 'save' ) );

			if( isset( $_POST[ 'quote_status' ] ) ) {

				$do_update = false;

				// Check if the post has paid status (used so if a customer pays for an order while being edited we don't end up changing a paid status to something else when it has been paid)

				if( get_post_status( $post_id ) !== 'qpr-paid' ) {

					$do_update = true;

				} else { // is paid

					// and being updated to paid, paid-unconf, cancelled or completed then allow it

					if( $_POST[ 'quote_status' ] == 'qpr-paid' || $_POST[ 'quote_status' ] == 'qpr-paid-unconf' || $_POST[ 'quote_status' ] == 'qpr-cancelled' || $_POST[ 'quote_status' ] == 'qpr-completed' ) {

						$do_update = true;

					}

				}

				if( $do_update == true ) {

					remove_action('save_post_qpr_quote', array( $this, 'save' ) ); // this function must be removed and then re added otherwise the update post status causes an infinite loop

					$quote_status = sanitize_text_field( $_POST['quote_status'] );

					wp_update_post(
						array(
							'ID' => $post_id,
							'post_status' => $quote_status,
						)
					);

					add_action('save_post_qpr_quote', array( $this, 'save' ) );

				}

			}

			if( isset( $_POST[ 'valid_until' ] ) ) {

				$valid_until = sanitize_text_field( $_POST[ 'valid_until' ] );

				// Data will be 00:00:00 we want this to be 23:59:59

				update_post_meta( $post_id, '_qpr_valid_until', strtotime( $valid_until . ' 23:59:59' ) );

			}

			if( isset( $_POST[ 'quote_currency' ] ) ) {

				$currency = sanitize_text_field( $_POST['quote_currency'] );

				update_post_meta( $post_id, '_qpr_currency', $currency );

			}

			if( isset( $_POST[ 'selected_customer' ] ) ) {

				$selected_customer = sanitize_text_field( $_POST['selected_customer'] );

				update_post_meta( $post_id, '_qpr_user', $selected_customer );

			}

			// Billing Address

			if( isset( $_POST[ 'billing_first_name' ] ) ) {

				$billing_first_name = sanitize_text_field( $_POST[ 'billing_first_name' ] );

				update_post_meta( $post_id, '_qpr_billing_first_name', $billing_first_name );

			}

			if( isset( $_POST[ 'billing_last_name' ] ) ) {

				$billing_last_name = sanitize_text_field( $_POST[ 'billing_last_name' ] );

				update_post_meta( $post_id, '_qpr_billing_last_name', $billing_last_name );

			}

			if( isset( $_POST[ 'billing_company' ] ) ) {

				$billing_company = sanitize_text_field( $_POST[ 'billing_company' ] );

				update_post_meta( $post_id, '_qpr_billing_company', $billing_company );

			}

			if( isset( $_POST[ 'billing_address_line_1' ] ) ) {

				$billing_address_line_1 = sanitize_text_field( $_POST[ 'billing_address_line_1' ] );

				update_post_meta( $post_id, '_qpr_billing_address_line_1', $billing_address_line_1 );

			}

			if( isset( $_POST[ 'billing_address_line_2' ] ) ) {

				$billing_address_line_2 = sanitize_text_field( $_POST[ 'billing_address_line_2' ] );

				update_post_meta( $post_id, '_qpr_billing_address_line_2', $billing_address_line_2 );

			}

			if( isset( $_POST[ 'billing_city' ] ) ) {

				$billing_city = sanitize_text_field( $_POST[ 'billing_city' ] );

				update_post_meta( $post_id, '_qpr_billing_city', $billing_city );

			}

			if( isset( $_POST[ 'billing_postcode' ] ) ) {

				$billing_postcode = sanitize_text_field( $_POST[ 'billing_postcode' ] );

				update_post_meta( $post_id, '_qpr_billing_postcode', $billing_postcode );

			}

			if( isset( $_POST[ 'billing_country' ] ) ) {

				$billing_country = sanitize_text_field( $_POST[ 'billing_country' ] );

				update_post_meta( $post_id, '_qpr_billing_country', $billing_country );

			}

			if( isset( $_POST[ 'billing_phone' ] ) ) {

				$billing_phone = sanitize_text_field( $_POST[ 'billing_phone' ] );

				update_post_meta( $post_id, '_qpr_billing_phone', $billing_phone );

			}

			if( isset( $_POST[ 'billing_email' ] ) ) {

				$billing_email = sanitize_text_field( $_POST[ 'billing_email' ] );

				update_post_meta( $post_id, '_qpr_billing_email', $billing_email );

			}

			// Shipping Address

			if( isset( $_POST[ 'shipping_first_name' ] ) ) {

				$shipping_first_name = sanitize_text_field( $_POST['shipping_first_name'] );

				update_post_meta( $post_id, '_qpr_shipping_first_name', $shipping_first_name );

			}

			if( isset( $_POST[ 'shipping_last_name' ] ) ) {

				$shipping_last_name = sanitize_text_field( $_POST['shipping_last_name'] );

				update_post_meta( $post_id, '_qpr_shipping_last_name', $shipping_last_name );

			}

			if( isset( $_POST[ 'shipping_company' ] ) ) {

				$shipping_company = sanitize_text_field( $_POST['shipping_company'] );

				update_post_meta( $post_id, '_qpr_shipping_company', $shipping_company );

			}

			if( isset( $_POST[ 'shipping_address_line_1' ] ) ) {

				$shipping_address_line_1 = sanitize_text_field( $_POST['shipping_address_line_1'] );

				update_post_meta( $post_id, '_qpr_shipping_address_line_1', $shipping_address_line_1 );

			}

			if( isset( $_POST[ 'shipping_address_line_2' ] ) ) {

				$shipping_address_line_2 = sanitize_text_field( $_POST['shipping_address_line_2'] );

				update_post_meta( $post_id, '_qpr_shipping_address_line_2', $shipping_address_line_2 );

			}

			if( isset( $_POST[ 'shipping_city' ] ) ) {

				$shipping_city = sanitize_text_field( $_POST['shipping_city'] );

				update_post_meta( $post_id, '_qpr_shipping_city', $shipping_city );

			}

			if( isset( $_POST[ 'shipping_postcode' ] ) ) {

				$shipping_postcode = sanitize_text_field( $_POST['shipping_postcode'] );

				update_post_meta( $post_id, '_qpr_shipping_postcode', $shipping_postcode );

			}

			if( isset( $_POST[ 'shipping_country' ] ) ) {

				$shipping_country = sanitize_text_field( $_POST['shipping_country'] );

				update_post_meta( $post_id, '_qpr_shipping_country', $shipping_country );

			}

			// Notes

			if( isset( $_POST['notes_internal'] ) ) {

				$notes_internal = sanitize_textarea_field( $_POST['notes_internal'] );

				update_post_meta( $post_id, '_qpr_notes_internal', $notes_internal );

			}

			if( isset( $_POST['notes_customer'] ) ) {

				$notes_customer = sanitize_textarea_field( $_POST['notes_customer'] );

				update_post_meta( $post_id, '_qpr_notes_customer', $notes_customer );

			}

		}

		public function send_to_customer( $post_id ) {

			if( isset( $_POST[ 'send' ] ) ) {

				// Email

				if( get_option( 'qpr_customer_notifications' ) == 'on' ) {

					$to = get_post_meta( $post_id, '_qpr_billing_email', true );
					$subject = __( 'Quote Ready to Review', 'quote-press' );
					$body = QPR_Templates::get_email_body( 'sent-customer' );
					$body = QPR_Templates::replace_quick_tags( $body, $post_id, '' );
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					wp_mail( $to, $subject, $body, $headers );

				}

				if( get_option( 'qpr_sent_notification' ) == 'on' ) {

					$to = get_option( 'qpr_notification_email_address' );
					$subject = __( 'Quote ID:', 'quote-press' ) . ' ' . $post_id . ' ' . __( 'Sent', 'quote-press' );
					$body = QPR_Templates::get_email_body( 'sent' );
					$body = QPR_Templates::replace_quick_tags( $body, $post_id, '' );
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					wp_mail( $to, $subject, $body, $headers );

				}

				// Update post status

				remove_action('save_post_qpr_quote', array( $this, 'send_to_customer' ), 20 ); // this function must be removed and then re added otherwise the update post status causes an infinite loop

				wp_update_post(
					array(
						'ID' => $post_id,
						'post_status' => 'qpr-sent'
					)
				);

				add_action('save_post_qpr_quote', array( $this, 'send_to_customer' ), 20 );

			}

		}

	}

}