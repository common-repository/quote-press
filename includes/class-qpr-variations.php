<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Variations' ) ) {

	class QPR_Variations {

		public function __construct() {

			add_action( 'init', array( $this, 'page_save' ) );
			add_action( 'save_post_qpr_product', array( $this, 'product_save_add_variations_to_db' ) );
			add_action( 'deleted_term_relationships', array( $this, 'product_terms_deleted_delete_variations_from_db' ), 10, 3 );

		}

		static function get_term_combinations( $array ) {

			$taxonomy_terms = array();

			foreach( $array as $a ) {

				$term = get_term( $a );
				$taxonomy = $term->taxonomy;
				$taxonomy_terms[$taxonomy][] = $a;

			}

			$term_combinations = array();

			$data = $taxonomy_terms;

			$combinations = [[]];
			$comKeys = array_keys($data);

			for( $count = 0; $count < count( $comKeys ); $count++ ) {
				$tmp = [];
				foreach ($combinations as $v1) {
					foreach ($data[$comKeys[$count]] as $v2)
						$tmp[] = $v1 + [$comKeys[$count] => $v2];

				}
				$combinations = $tmp;
			}

			foreach( $combinations as $combination ) {

				$combination_ordered_by_id = array();

				foreach( $combination as $c_key => $c_val ) {

					$combination_ordered_by_id[] = $c_val;

				}

				sort( $combination_ordered_by_id );

				$string = '';

				foreach( $combination_ordered_by_id as $cobi ) {

					$string .= $cobi . '_';

				}

				$term_combinations[] = rtrim( $string, '_' );

			}

			return $term_combinations;

		}

		static function page() {

			global $wpdb;

			add_thickbox();
			$per_page = 20;

			if( isset( $_REQUEST['paged'] ) && empty( $_REQEST['search'] ) ) {

				$paged = sanitize_text_field( $_REQUEST['paged'] );

			} else {

				$paged = '';

			}

			if( empty( $paged ) ) {

				$paged = '1';

			}

			$args = array(
				'post_type'			=> 'qpr_product',
				'posts_per_page'	=> $per_page,
				'paged'				=> $paged,
				'order'				=> 'ASC',
				'orderby'			=> 'title',
			);

			if( isset( $_POST['search'] ) ) {

				$search_term = sanitize_text_field( $_POST['search'] );
				$args['s'] = $search_term;

			} else {

				$search_term = '';

			}

			$products = new WP_Query( $args ); ?>

			<div class="wrap">
				<form method="post">
					<?php wp_nonce_field( 'qpr-nonce-variations' ); ?>
					<input type="hidden" name="qpr_variations">
					<h1><?php _e( 'Variations', 'quote-press' ); ?><?php echo ( !empty( $_REQUEST['search'] ) ? ' ' . __( 'matching: ' . $_REQUEST['search'] ) : '' ); ?></h1>
					<div class="qpr-variations-top">
						<button class="button button-primary qpr-save"><?php _e( 'Save', 'quote-press' ); ?></button>
						<div class="qpr-search">
							<input type="text" name="search" value="<?php echo esc_html( $search_term ); ?>">
							<button class="button button-primary"><?php _e( 'Search', 'quote-press' ); ?></button>
							<p class="description"><?php _e( 'Unsaved changes will be saved upon searching', 'quote-press' ); ?></p>
						</div>
					</div>
					<table class="wp-list-table widefat fixed striped posts">
						<thead>
							<tr>
								<td><?php _e( 'ID', 'quote-press' ); ?></td>
								<td><?php _e( 'SKU', 'quote-press' ); ?></td>
								<td><?php _e( 'Product', 'quote-press' ); ?></td>
								<td><?php _e( 'Variations', 'quote-press' ); ?></td>
							</tr>
						</thead>
						<tbody>
							<?php if( !empty( $products->posts ) ) {
								$attributes_variations = QPR_Attributes::get_attributes( true ); // gets only attributes with variations enabled
								foreach( $products->posts as $product ) { ?>
									<tr>
										<td><?php echo $product->ID; ?></td>
										<td><?php echo get_post_meta( $product->ID, '_qpr_sku', true ); ?></td>
										<td><a href="<?php echo get_edit_post_link( $product->ID ); ?>"><?php echo $product->post_title; ?></a></td>
										<td>
											<div id="qpr-attribute-variation-<?php echo $product->ID; ?>" style="display: none;">

													<?php
													$product_variations = QPR_Variations::get_product_variations( $product->ID );
													$product_taxonomies = get_post_taxonomies( $product->ID );
													$product_attributes = QPR_Attributes::get_attributes( true );
													$no_results = false;

													// If it's the product category taxonomy ignore as it's not an attribute

													if( ( $key = array_search( 'qpr_product_cat', $product_taxonomies ) ) !== false ) {
														
														unset( $product_taxonomies[$key] );
													
													}

													if( ( $key = array_search( 'qpr_tax_profile', $product_taxonomies ) ) !== false ) {
														
														unset( $product_taxonomies[$key] );
													
													}

													$product_terms = get_the_terms( $product->ID, $product_taxonomies );

													if( !empty( $product_terms ) ) {

														$product_term_ids = array();

														foreach( $product_terms as $product_term ) {

															if( array_key_exists( $product_term->taxonomy, $product_attributes ) )

															$product_term_ids[] = $product_term->term_id;

														}

														if( !empty( $product_term_ids ) ) {

															$term_combos = QPR_Variations::get_term_combinations( $product_term_ids );

															foreach( $term_combos as $term_combo_ids ) {

																$term_combo_ids_string = $term_combo_ids;
																$term_combo_ids = explode( '_', $term_combo_ids );
																$term_combo_ids_last = end( $term_combo_ids );

																echo '<div class="qpr-variation-edit">';

																echo '<div class="qpr-variation-title">';

																foreach( $term_combo_ids as $term_combo_id ) {

																	$term = get_term( $term_combo_id );

																	echo $term->name;

																	if( $term_combo_id !== $term_combo_ids_last ) {

																		echo ' / ';

																	}

																}

																echo '</div>';

																echo '<label>Price:<br><input type="number" name="price_attribute[' . $product->ID . '][' . $term_combo_ids_string . ']" value="' . $product_variations[$term_combo_ids_string]['price'] . '" step="0.01" min="0"></label>';
																echo '<label>SKU:<br><input type="text" name="sku_attribute[' . $product->ID . '][' . $term_combo_ids_string . ']" value="' . $product_variations[$term_combo_ids_string]['sku'] . '"></label>';

																echo '</div>';

															}

														} else {

															$no_results = true;

														}

													} else {

														$no_results = true;

													}

													if( $no_results == true ) {

														echo '<div class="qpr-no-variations">' . __( 'No variation enabled attributes have been assigned to this product, ensure the attribute has variations enabled in the Attributes section and this product has attribute assigned.', 'quote-press' ) . '</div>';

													} ?>
											</div>
											<a href="#TB_inline?&width=600&height=550&inlineId=qpr-attribute-variation-<?php echo $product->ID; ?>" class="thickbox button button-small"><?php _e( 'Variations', 'quote-press' ); ?></a>
										</td>
									</tr>
								<?php }
							} else { ?>
								<tr>
									<td colspan="4"><?php _e( 'No variations found', 'quote-press' ); ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
					<button class="button button-primary qpr-save"><?php _e( 'Save', 'quote-press' ); ?></button>
					
					<?php

					$total_pages = ceil( $products->found_posts / $per_page );

					if( $total_pages > 1 ) { ?>

						<div class="pagination">

							<label>
								Page:
								<select name="paged">
									<?php for ( $x = 1; $x <= $total_pages; $x++ ) {
										echo '<option value="' . $x . '"' . ( $paged == $x ? ' selected' : '' ) . '>' . $x . '</option>';
									} 
									?>
								</select>
								<button class="button"><?php _e( 'Go', 'quote-press' ); ?></button>
							</label>
							<p class="description"><?php _e( 'Unsaved changes will be saved upon navigating', 'quote-press' ); ?></p>
						</div>

					<?php } ?>

				</form>
			</div>

		<?php }

		public function page_save() {

			global $wpdb;

			if( isset( $_POST['qpr_variations'] ) ) {

				check_admin_referer( 'qpr-nonce-variations' );

				if( isset( $_POST['price_attribute'] ) ) {

					$price_attributes = $_POST['price_attribute'];

					foreach( $price_attributes as $product_id => $price_attribute ) {

						foreach( $price_attribute as $attribute_combination => $attribute_combination_price ) {

							$existing_attribute = $wpdb->get_results( "SELECT variation_attributes FROM {$wpdb->prefix}qpr_variations WHERE variation_attributes = '{$attribute_combination}' AND variation_product_id = '{$product_id}' LIMIT 1;" );

							if( !empty( $existing_attribute ) ) {

								$wpdb->query( "
									UPDATE {$wpdb->prefix}qpr_variations SET variation_price = '{$attribute_combination_price}' WHERE variation_attributes = '{$attribute_combination}' AND variation_product_id = '{$product_id}';
								" );

							} else {

								$wpdb->query( "
									INSERT INTO {$wpdb->prefix}qpr_variations (variation_id,variation_attributes,variation_product_id,variation_price) VALUES ('','{$attribute_combination}','{$product_id}','{$attribute_combination_price}');
								" );

							}

						}

					}

				}

				if( isset( $_POST['sku_attribute'] ) ) {

					$sku_attributes = $_POST['sku_attribute'];

					foreach( $sku_attributes as $product_id => $sku_attribute ) {

						foreach( $sku_attribute as $attribute_combination => $attribute_combination_sku ) {

							$existing_attribute = $wpdb->get_results( "SELECT variation_attributes FROM {$wpdb->prefix}qpr_variations WHERE variation_attributes = '{$attribute_combination}' AND variation_product_id = '{$product_id}' LIMIT 1;" );

							if( !empty( $existing_attribute ) ) {

								$wpdb->query( "
									UPDATE {$wpdb->prefix}qpr_variations SET variation_sku = '{$attribute_combination_sku}' WHERE variation_attributes = '{$attribute_combination}' AND variation_product_id = '{$product_id}';
								" );

							} else {

								$wpdb->query( "
									INSERT INTO {$wpdb->prefix}qpr_variations (variation_id,variation_attributes,variation_product_id,variation_sku) VALUES ('','{$attribute_combination}','{$product_id}','{$attribute_combination_sku}');
								" );

							}

						}

					}

				}

			}

		}

		static function get_product_variations( $product_id ) {

			// if you unselect a variation term it will still be in this database unless the term is deleted

			global $wpdb;

			$product_variations = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}qpr_variations WHERE variation_product_id = '{$product_id}'" );
			$product_variations_temp = array();
			foreach( $product_variations as $product_variation ) {
				$product_variations_temp[$product_variation->variation_attributes] = array(
					'price' => $product_variation->variation_price,
					'sku' => $product_variation->variation_sku,
				);
			}
			$product_variations = $product_variations_temp;

			return $product_variations;

		}

		public function product_save_add_variations_to_db( $post_id ) {

			global $wpdb;
			$product_taxonomies = get_post_taxonomies( $post_id );
			$product_attributes = QPR_Attributes::get_attributes( true );

			// If it's the product category taxonomy ignore as it's not an attribute

			if( ( $key = array_search( 'qpr_product_cat', $product_taxonomies ) ) !== false ) {
				
				unset( $product_taxonomies[$key] );
			
			}

			if( ( $key = array_search( 'qpr_tax_profile', $product_taxonomies ) ) !== false ) {
				
				unset( $product_taxonomies[$key] );
			
			}

			$product_terms = get_the_terms( $post_id, $product_taxonomies );

			if( !empty( $product_terms ) ) {

				$product_term_ids = array();

				foreach( $product_terms as $product_term ) {

					if( array_key_exists( $product_term->taxonomy, $product_attributes ) )

					$product_term_ids[] = $product_term->term_id;

				}

				if( !empty( $product_term_ids ) ) {

					$term_combos = QPR_Variations::get_term_combinations( $product_term_ids );

					foreach( $term_combos as $term_combo_ids ) {

						$term_combo_ids_string = $term_combo_ids;

						// need something to check for existing conditions

						$existing_results = $wpdb->get_results( "
							SELECT * FROM {$wpdb->prefix}qpr_variations WHERE variation_attributes = '{$term_combo_ids_string}' AND variation_product_id = '{$post_id}';
						" );

						if( empty( $existing_results ) ) {

							$wpdb->query( "
								INSERT INTO {$wpdb->prefix}qpr_variations (variation_id,variation_attributes,variation_product_id,variation_price,variation_sku) VALUES ('','{$term_combo_ids_string}','{$post_id}','','');
							" );

						}

					}

				}

			}

		}

		public function product_terms_deleted_delete_variations_from_db( $object_id, $tt_ids, $taxonomy ) {		

			if( get_post_type( $object_id ) == 'qpr_product' ) {

				global $wpdb;

				if( !empty( $tt_ids ) ) {	

					foreach( $tt_ids as $tt_id ) {
						
						$wpdb->query( "DELETE FROM `{$wpdb->prefix}qpr_variations` WHERE concat('_', variation_attributes, '_') LIKE '%@_" . $tt_id . "@_%' escape '@' AND variation_product_id = '{$object_id}'" );

					}

				}

			}

		}

	}

}