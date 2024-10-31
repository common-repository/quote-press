<?php get_header(); ?>
<div id="primary" class="content-area qpr-product">
	<main id="main" class="site-main">
		<?php while ( have_posts() ) { the_post(); ?>
			<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
				<div class="entry-container">
					<div class="entry-content">
						<h1 class="entry-title"><?php the_title(); ?></h1>
						<div class="qpr-product-top">
							<div class="qpr-images">
								<div class="qpr-featured-image">
									<?php $featured_image = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0];
									if( !empty( $featured_image ) ) { ?>
										<a href="<?php echo wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' )[0]; ?>" target="_blank" data-rel="lightcase:qprProductImages"><?php echo the_post_thumbnail(); ?></a>
									<?php } else {
										echo '<img src="' . apply_filters( 'qpr_no_image_src_full', plugins_url() . '/quote-press/assets/images/no-image.jpg' ) . '" width="' . get_option( 'large_size_w' ) . '" height="' . get_option( 'large_size_h' ) . '" alt="' . __( 'No image', 'quote-press' ) . '">';	
									} ?>
								</div>
								<?php $gallery_images = get_post_meta( $post->ID, '_qpr_gallery_ids', true );
								if( !empty( $gallery_images ) ) { ?>
									<div class="qpr-gallery">
										<?php
										$gallery_images = explode( ',', $gallery_images );
										foreach( $gallery_images as $gallery_image ) {
											echo '<div><a href="' . wp_get_attachment_image_src( $gallery_image, 'full' )[0] . '" target="_blank" data-rel="lightcase:qprProductImages">' . wp_get_attachment_image( $gallery_image ) . '</a></div>';
										} ?>
									</div>
								<?php } ?>
							</div>
							<div class="qpr-buy">
								<form method="post">
									<?php wp_nonce_field( 'qpr-nonce-add-to-quote', 'qpr-nonce-add-to-quote' ); ?>
									<h3><?php _e( 'Add to Quote', 'quote-press' ); ?></h3>
									<div><label><?php _e( 'Qty:', 'quote-press' ); ?><br><input id="add-to-cart-qty" type="number" min="1" value="1" required></label></div>
									<?php
									$attributes = QPR_Variations::get_product_variations( get_the_id() );
									$attributes_variations = QPR_Attributes::get_attributes( true ); // Enabled variations
									$attribute_options = array();
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
										echo '<div><label>' . get_taxonomy( $attribute_taxonomy )->label . '<br>';
										echo '<select id="qpr-attribute-select" class="qpr-attribute-select" required>';
										echo '<option value="">' . __( 'Select', 'quote-press' ) . '</option>';
										foreach( $attribute_terms as $attribute_term ) {
											echo '<option value="' . $attribute_term . '">' . get_term( $attribute_term )->name . '</option>';
										}
										echo '</select></label></div>';
									} ?>
									<div><button id="add_to_cart" class="button" data-product-id="<?php echo get_the_id(); ?>" data-product-qty="1" data-product-variation=""><?php _e( 'Add', 'quote-press' ); ?></button></div>
								</form>
							</div>
						</div>
						<div class="qpr-description">
							<h3><?php _e( 'Description', 'quote-press' ); ?></h3>
							<?php echo the_content(); ?>
						</div>
						<?php
						$attributes = QPR_Attributes::get_attributes( false );
						if( !empty( $attributes ) ) { ?>
							<div class="qpr-attributes">
								<h3><?php _e( 'Attributes', 'quote-press' ); ?></h3>
								<table>
									<tbody>
										<?php foreach( $attributes as $attribute_taxonomy => $attribute_name ) { ?>
											<tr>
												<td><?php echo $attribute_name; ?></td>
												<td>
													<?php
													$terms = wp_get_post_terms( get_the_id(), $attribute_taxonomy );
													$terms_string = '';
													foreach( $terms as $term ) {
														$term_name = get_term_by( 'id', $term->term_id, $attribute_taxonomy );
														$term_name = $term_name->name;
														$terms_string .= $term_name . ', ';
													}
													$terms_string = rtrim( $terms_string, ', ' );
													echo $terms_string;
													?>
												</td>
											</tr>
										<?php } ?>
									</tbody>
								</table>
							</div>
						<?php } ?>
					</div>
				</div>
			</article>
		<?php } ?>
	</main>
</div>
<?php
get_sidebar();
get_footer();