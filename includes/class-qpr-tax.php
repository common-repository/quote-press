<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Tax' ) ) {

	class QPR_Tax {

		public function __construct() {

			add_action( 'qpr_tax_profile_add_form_fields', array( $this, 'add_tax_profile_fields' ), 10, 2 );
			add_action( 'qpr_tax_profile_edit_form_fields', array( $this, 'edit_tax_profile_fields' ), 10, 2 );
			add_action( 'edited_qpr_tax_profile', array( $this, 'save_taxonomy_custom_meta' ), 10, 2 );  
			add_action( 'create_qpr_tax_profile', array( $this, 'save_taxonomy_custom_meta' ), 10, 2 );
			add_action( 'delete_qpr_tax_profile', array( $this, 'delete_tax_profile' ), 10, 2 );

		}

		public function add_tax_profile_fields() { ?>

			<div class="form-field">
				<label><?php _e( 'Tax Profile %', 'quote-press' ); ?><input type="number" min="0" step="0.01" name="tax_profile_percent" value="0" required></label>
				<p class="description"><?php _e( 'Enter a value for this field', 'quote-press' ); ?></p>
			</div>
			<div class="form-field">
				<label><input type="checkbox" name="tax_profile_default" value="1"> <?php _e( 'Default Tax Profile', 'quote-press' ); ?></label>
				<p class="description"><?php _e( 'Make this Tax Profile the default', 'quote-press' ); ?></p>
			</div>


		<?php }

		public function edit_tax_profile_fields( $term ) {

			$tax_profiles = get_option( 'qpr_tax_profiles' );
			$tax_profile = $tax_profiles[$term->term_id];

			if( isset( $tax_profile['percent'] ) ) {

				$tax_profile_percent = $tax_profile['percent'];

			} else {

				$tax_profile_percent = '0';
			
			}
			
			$qpr_default_tax_profile = get_option('qpr_default_tax_profile'); ?>

			<tr class="form-field">
				<th scope="row" valign="top"><label for="qpr-tax-profile-percent"><?php _e( 'Tax Profile %', 'quote-press' ); ?></label></th>
				<td>
					<input id="qpr-tax-profile-percent" type="number" name="tax_profile_percent" min="0" step="0.01" value="<?php echo $tax_profile_percent; ?>" required>
					<p class="description"><?php _e( 'Enter a value for this field', 'quote-press' ); ?></p>
				</td>
			</tr>
			<tr class="form-field">
				<th scope="row" valign="top"><label for="qpr-tax-profile-default"><?php _e( 'Default Tax Profile', 'quote-press' ); ?></label></th>
				<td>
					<input type="checkbox" id="qpr-tax-profile-default" name="tax_profile_default" value="<?php echo $term->term_id; ?>"<?php echo ( $term->term_id == $qpr_default_tax_profile ? ' checked' : '' ); ?>>
					<p class="description"><?php _e( 'Make this Tax Profile the default', 'quote-press' ); ?></p>
				</td>
			</tr>

		<?php }

		public function save_taxonomy_custom_meta( $term_id ) {

			if( isset( $_POST['tax_profile_percent'] ) ) {

				$tax_profiles = get_option( 'qpr_tax_profiles' );
				$tax_profiles[ $term_id ]['percent'] = sanitize_text_field( $_POST['tax_profile_percent'] );
				update_option( 'qpr_tax_profiles', $tax_profiles );

			}

			if( isset( $_POST['tax_profile_default'] ) ) {

				update_option( 'qpr_default_tax_profile', sanitize_text_field( $_POST['tax_profile_default'] ) );

			} else {

				if( get_option( 'qpr_default_tax_profile' ) == $term_id ) {

					update_option( 'qpr_default_tax_profile', '' );

				}

			}

		}

		public function delete_tax_profile( $term_id ) {

			$tax_profiles = get_option( 'qpr_tax_profiles' );
			unset( $tax_profiles[ $term_id ] );
			update_option( 'qpr_tax_profiles', $tax_profiles );

			if( get_option( 'qpr_default_tax_profile' ) == $term_id ) {

				update_option( 'qpr_default_tax_profile', '' );

			}

		}

	}

}