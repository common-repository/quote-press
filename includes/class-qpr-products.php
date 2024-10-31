<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Products' ) ) {

	class QPR_Products {

		public function __construct() {

			add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
			add_action( 'save_post', array( $this, 'meta_boxes_save' ) );

		}

		public function add_meta_boxes() {

			add_meta_box(
				'qpr-product-data',
				__( 'Product Data', 'quote-press' ),
				array( $this, 'meta_box_product_data' ),
				'qpr_product',
				'normal',
				'high'
			);

			add_meta_box(
				'multiple-product-images',
				__( 'Gallery', 'quote-press' ),
				array( $this, 'meta_box_gallery' ),
				'qpr_product',
				'side',
				'default'
			);

		}

		public function meta_box_product_data() {

			global $post;
			$sku = get_post_meta( $post->ID, '_qpr_sku', true );
			$price = get_post_meta( $post->ID, '_qpr_price', true ); ?>

			<div>
				<label for="qpr-sku"><?php _e( 'SKU:', 'quote-press' ); ?></label>
				<input type="text" name="sku" id="qpr-sku" value="<?php echo $sku; ?>">
			</div>
			<div>
				<label for="qpr-price"><?php _e( 'Price:', 'quote-press' ); ?></label>
				<input type="number" name="price" id="qpr-price" value="<?php echo $price; ?>" min="0" step="0.01">
				<p class="description"><?php _e( 'If this product uses variations then this price is overridden by the variation price.' ); ?></p>
			</div>

		<?php }

		public function meta_box_gallery() {

			wp_enqueue_media();

			global $post;
			
			$attachments_value = get_post_meta( $post->ID, '_qpr_gallery_ids', true );

			echo '<div id="qpr-gallery-thumbs">'; // must be here even if no contents, so stuff can get appended to it

			if( !empty( $attachments_value ) ) {

				$attachments_explode = explode( '_', $attachments_value );
				
				if( !empty( $attachments_explode ) ) {

					foreach( $attachments_explode as $attachments_explode_val ) {

						$attachment_url = wp_get_attachment_url( $attachments_explode_val );
						$attachment_info = pathinfo( $attachment_url );
						$attachment_attributes = wp_get_attachment_image_src( $attachments_explode_val , array( 128, 128 ) );

						echo '<div>';
						echo '<a href="' . wp_get_attachment_url( $attachments_explode_val ) . '" target="_blank"><img src="' . $attachment_attributes[0] . '" width="' . $attachment_attributes[1] . '" height="' . $attachment_attributes[2] . '" title="View ' . $attachment_info['basename'] . '"></a>';
						echo '<a href="#" class="qpr-remove-gallery-item dashicons dashicons-trash" data-id="' . $attachments_explode_val . '" title="Remove ' . $attachment_info['basename'] . '">&nbsp;</a>';
						echo '</div>';

					}

				}

			}

			echo '</div>';
			echo '<div><a href="#" id="qpr-gallery-add" style="cursor: pointer;">' . __( 'Add image(s)', 'quote-press' ) . '</a></div>';
			echo '<input type="hidden" name="gallery_ids" id="qpr-gallery-ids" value="' . $attachments_value . '">'; ?>

			<script type="text/javascript">

				jQuery( document ).ready( function( $ ) {
		
					var customMediaLibrary = window.wp.media({
					    frame: 'select',
					    title: 'Select images',
					    multiple: true,
					    library: {
					        order: 'DESC',
					        orderby: 'date',
					        type: ['image' ],
					        search: null,
					        uploadedTo: null
					    },
					    button: {
					        text: 'Add images'
					    }
					});

					$( '#qpr-gallery-add' ).on( 'click', function( e ) {
						e.preventDefault();
						customMediaLibrary.open();
					});

					customMediaLibrary.on( 'select', function() {

					    var imagesSelected = customMediaLibrary.state().get( 'selection' ).toJSON();

						jQuery.each( imagesSelected, function( i, attachment ) {

							if( $( '#qpr-gallery-ids' ).val() == '' ) {

								$( '#qpr-gallery-ids' ).val( attachment.id );

							} else {

								oldVal = $( '#qpr-gallery-ids' ).val();
								$( '#qpr-gallery-ids' ).val( oldVal + ',' + attachment.id );

							}

							$( '#qpr-gallery-thumbs' ).append( '<div><a href="' + attachment.url + '" target="_blank"><img src="' + attachment.url + '" width="128" height="128" title="View ' + attachment.title + '" data-id="' + attachment.id + '" /></a><a class="qpr-remove-gallery-item dashicons dashicons-trash" data-id="' + attachment.id + '" title="' + attachment.title + '">&nbsp;</a></div>' );

						});

					});

					$( '.qpr-remove-gallery-item' ).live( 'click',function( e ) {

						event.preventDefault();

						valArr = $( '#qpr-gallery-ids' ).val().split( ',' );
						var index = valArr.indexOf( $(this).attr( 'data-id' ) );

						if( index > -1 ) {

							valArr.splice( index, 1 );
							$(this).parent().remove();

						}

						$( '#qpr-gallery-ids' ).val( valArr.toString() );

					});

				});

			</script>

		<?php }

		public function meta_boxes_save( $post_id ) {

			$post_type = get_post_type( $post_id );

			if( $post_type == 'qpr_product' ) {

				if( isset( $_POST[ 'sku' ] ) ) {

					update_post_meta( $post_id, '_qpr_sku', sanitize_text_field( $_POST[ 'sku' ] ) );

				}

				if( isset( $_POST[ 'price' ] ) ) {

					update_post_meta( $post_id, '_qpr_price', sanitize_text_field( $_POST[ 'price' ] ) );

				}

				if( isset( $_POST['gallery_ids'] ) ) {

					update_post_meta( $post_id, '_qpr_gallery_ids', sanitize_text_field( $_POST['gallery_ids'] ) );

				}

			}

		}

	}

}