<?php get_header(); ?>
<div id="primary" class="content-area qpr-archive">
	<main id="main" class="site-main">
		<?php if( have_posts() ) { ?>
			<header class="page-header">
				<h1 class="page-title">
					<?php 
					if( qpr_is_store() == true ) {
						_e( 'Store', 'quote-press' );
					} else {
						echo ( get_queried_object()->taxonomy == 'qpr_product_cat' ? single_cat_title() : __( 'Filtered Results', 'quote-press' ) );
					} ?>
				</h1>
				<?php if( qpr_is_store() == false ) {
					the_archive_description( '<div class="archive-description">', '</div>' ); 
				} ?>
			</header>
			<div class="qpr-archive-products">
				<?php while( have_posts() ) { the_post(); ?>
					<article id="post-<?php the_ID(); ?>" <?php post_class( 'qpr-archive-product' ); ?>>
						<?php $thumbnail = get_the_post_thumbnail(); ?>
						<div class="qpr-featured-image">
							<a href="<?php echo get_permalink(); ?>">
								<?php if( !empty( $thumbnail ) ) {
									echo the_post_thumbnail( 'thumbnail' );
								} else {
									echo '<img src="' . apply_filters( 'qpr_no_image_src_thumbnail', plugins_url() . '/quote-press/assets/images/no-image.jpg' ) . '" width="' . get_option( 'thumbnail_size_w' ) . '" height="' . get_option( 'thumbnail_size_h' ) . '" alt="' . __( 'No image', 'quote-press' ) . '">';
								} ?>
							</a>
						</div>
						<div class="qpr-title"><a href="<?php echo get_permalink(); ?>"><?php echo the_title(); ?></a></div>
						<?php $excerpt = get_the_excerpt();
						if( has_excerpt() ) { ?>
							<div class="qpr-excerpt"><?php echo $excerpt; ?></div>
						<?php } ?>
						<a class="qpr-button button" href="<?php echo get_permalink(); ?>"><?php _e( 'View', 'quote-press' ); ?></a>
					</article>
				<?php } ?>
			</div>
			<?php the_posts_navigation(
				array(
					'prev_text' => __( 'Next', 'quote-press' ),
					'next_text' => __( 'Previous', 'quote-press' ),
				)
			);
		} else { ?>
			<p><?php _e( 'No products available in this category.', 'quote-press' ); ?></p>
		<?php } ?>
	</main>
</div>
<?php
get_sidebar();
get_footer();