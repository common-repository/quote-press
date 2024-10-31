<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Attributes' ) ) {

	class QPR_Attributes {

		public function __construct() {

			add_action( 'init', array( $this, 'page_save' ) );
			add_action( 'deleted_term_taxonomy', array( $this, 'delete_variation_by_term_id' ) );

		}

		public function page_save() {

			global $wpdb;

			if( isset( $_POST['qpr_attributes'] ) ) {

				check_admin_referer( 'qpr-nonce-attributes' );

				if( isset( $_POST['add_attribute'] ) ) {

					$name = sanitize_text_field( $_POST['add_attribute'] );

					if( !empty( $name ) ) {

						$attributes = QPR_Attributes::get_attributes( false );
						$name_to_taxonomy_id = strtolower( str_replace( ' ', '_', preg_replace( "/[^A-Za-z0-9 ]/", '', $name ) ) );

						$wpdb->query( "
							INSERT INTO {$wpdb->prefix}qpr_attributes (attribute_taxonomy,attribute_taxonomy_label,attribute_variations) VALUES ('qpr_{$name_to_taxonomy_id}','{$name}','0') ON DUPLICATE KEY UPDATE attribute_taxonomy_label = '{$name}', attribute_variations = '0';
						" );

						set_transient( 'qpr_flush_rewrites_next_load', 1 );
						QPR_Admin::add_notice( 'success', __( 'Attribute added.', 'quote-press' ) );

					}

				}

				if( isset( $_POST['attribute_variations_enable'] ) ) {

					$attribute_variations_enable = sanitize_text_field( $_POST['attribute_variations_enable'] );

					$wpdb->query( "
						INSERT INTO {$wpdb->prefix}qpr_attributes (attribute_taxonomy,attribute_variations) VALUES ('{$attribute_variations_enable}','1') ON DUPLICATE KEY UPDATE attribute_variations = '1';
					" );

					QPR_Admin::add_notice( 'success', __( 'Attribute variations enabled.', 'quote-press' ) );

				}

				if( isset( $_POST['attribute_variations_disable'] ) ) {

					$attribute_variations_disable = sanitize_text_field( $_POST['attribute_variations_disable'] );

					$wpdb->query( "
						INSERT INTO {$wpdb->prefix}qpr_attributes (attribute_taxonomy,attribute_variations) VALUES ('{$attribute_variations_disable}','0') ON DUPLICATE KEY UPDATE attribute_variations = '0';
					" );

					$disabled_terms = get_terms( array(
						'taxonomy' => $attribute_variations_disable,
						'hide_empty' => false,
					) );

					foreach( $disabled_terms as $disabled_term ) {

						$disabled_term_id = $disabled_term->term_id;
						$this->delete_variation_by_term_id( $disabled_term_id );

					}

					// delete any variations where any terms from this taxonomy are

					QPR_Admin::add_notice( 'success', __( 'Attribute variations disabled.', 'quote-press' ) );

				}

				if( isset( $_POST['attribute_delete'] ) ) {

					$attribute_delete = sanitize_text_field( $_POST['attribute_delete'] );

					$wpdb->query( "
						DELETE FROM {$wpdb->prefix}qpr_attributes WHERE `attribute_taxonomy` = '{$attribute_delete}';
					" );

					$deleted_terms = get_terms( array(
						'taxonomy' => $attribute_delete,
						'hide_empty' => false,
					) );

					foreach( $deleted_terms as $deleted_term ) {

						$deleted_term_id = $deleted_term->term_id;
						$this->delete_variation_by_term_id( $deleted_term_id );
						wp_delete_term( $deleted_term_id, $attribute_delete );

					}

					set_transient( 'qpr_flush_rewrites_next_load', 1 );
					QPR_Admin::add_notice( 'success', __( 'Attribute deleted.', 'quote-press' ) );

				}

			}

		}

		static function page() {

			$attributes = QPR_Attributes::get_attributes( false );
			$attributes_variations_enabled = QPR_Attributes::get_attributes( true ); ?>

			<div class="wrap">
				<form method="post">
					<?php wp_nonce_field( 'qpr-nonce-attributes' ); ?>
					<input type="hidden" name="qpr_attributes">
					<h1><?php _e( 'Attributes', 'quote-press' ); ?></h1>
					<div>
						<h2><?php _e( 'Add Attribute', 'quote-press' ); ?></h2>
						<p><?php _e( 'Use singular name, not plural. e.g. Brand not Brands.', 'quote-press' ); ?></p>
						<input type="text" name="add_attribute">
						<button class="button button-primary"><?php _e( 'Create', 'quote-press' ); ?></button>
					</div>
					<div>
						<?php if( !empty( $attributes ) ) { ?>
							<h2><?php _e( 'Current Attributes', 'quote-press' ); ?></h2>
							<table class="wp-list-table widefat fixed striped posts">
								<thead>
									<tr>
										<td><?php _e( 'Name', 'quote-press' ); ?></td>
										<td><?php _e( 'Taxonomy', 'quote-press' ); ?></td>
										<td><?php _e( 'Variations', 'quote-press' ); ?></td>
										<td><?php _e( 'Action', 'quote-press' ); ?></td>
									</tr>
								</thead>
								<tbody>
									<?php foreach( $attributes as $attribute_taxonomy => $attribute_name ) { ?>
										<tr>
											<td><a href="<?php echo get_admin_url( null, 'edit-tags.php?taxonomy=' . $attribute_taxonomy ); ?>"><?php echo $attribute_name; ?></a></td>
											<td><?php echo $attribute_taxonomy; ?></td>
											<td>
												<?php if( array_key_exists( $attribute_taxonomy, $attributes_variations_enabled ) ) { ?>
													<button class="qpr-disable-variations-button button button-small" name="attribute_variations_disable" value="<?php echo $attribute_taxonomy; ?>"><?php _e( 'Disable', 'quote-press' ); ?></button>
												<?php } else { ?>
													<button class="button button-small" name="attribute_variations_enable" value="<?php echo $attribute_taxonomy; ?>"><?php _e( 'Enable', 'quote-press' ); ?></button>
												<?php } ?>
											</td>
											<td>
												<button class="qpr-attribute-delete-button button button-small" name="attribute_delete" value="<?php echo $attribute_taxonomy; ?>"><?php _e( 'Delete', 'quote-press' ); ?></button>
											</td>
										</tr>
									<?php } ?>
								</tbody>
							</table>
						<?php } ?>
					</div>
				</form>
			</div>

		<?php }

		public function delete_variation_by_term_id( $term_id ) {

			// Delete rows in variations table containig term id

			global $wpdb;
			$wpdb->query( "DELETE FROM `{$wpdb->prefix}qpr_variations` WHERE concat('_', variation_attributes, '_') LIKE '%@_" . $term_id . "@_%' escape '@'" );

		}

		public static function get_attributes( $variations_enabled ) {

			global $wpdb;

			if( $variations_enabled == true ) {

				$attributes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}qpr_attributes WHERE `attribute_variations` = '1'" );

			} else {

				$attributes = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}qpr_attributes;" );

			}

			$attributes_temp = array();

			foreach( $attributes as $attribute ) {

				$taxonomy = get_taxonomy( $attribute->attribute_taxonomy );
				$attributes_temp[$attribute->attribute_taxonomy] = $attribute->attribute_taxonomy_label;

			}

			return $attributes_temp;

		}

	}

}