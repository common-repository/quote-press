<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Cart' ) ) {

	class QPR_Cart {

		public function __construct() {

			add_shortcode( 'qpr_cart', array( $this, 'render_cart' ) );
			add_shortcode( 'qpr_mini_cart', array( $this, 'render_mini_cart' ) );

		}

		public function render_cart( $attributes, $content = null ) {

			global $wpdb;
			$session_key = $_COOKIE['qpr_session'];
			$session_value = $wpdb->get_results( "SELECT session_value FROM `{$wpdb->prefix}qpr_sessions` WHERE `session_key` = '" . $session_key . "'" )[0]->session_value;
			$session_value = unserialize( $session_value );

			ob_start(); ?>

			<div class="qpr-main-cart">
				<?php wp_nonce_field( 'qpr-nonce-cart', 'qpr-nonce-cart' ); ?>
				<div id="qpr-main-cart-contents">
					<table>
						<thead>
							<tr>
								<td><?php _e( 'Product', 'quote-press' ); ?></td>
								<td><?php _e( 'Qty', 'quote-press' ); ?></td>
								<td><?php _e( 'Remove', 'quote-press' ); ?></td>
							</tr>
						</thead>
						<tbody>
							<!-- ajax content -->
						</tbody>
					</table>
				</div>
				<?php if( !empty( $session_value['cart'] ) ) { ?>
					<div class="cart-actions">
						<a href="<?php echo home_url( '/checkout/' ); ?>" class="button"><?php _e( 'Submit Quote Request', 'quote-press' ); ?></a>
					</div>
				<?php } ?>
			</div>

			<?php return ob_get_clean();

		}

		public function render_mini_cart( $attributes, $content = null ) {

			// Cart contents is a class not id as can have multiple mini carts on one page

			ob_start(); ?>

			<div class="qpr-mini-cart">
				<?php wp_nonce_field( 'qpr-nonce-mini-cart', 'qpr-nonce-mini-cart' ); ?>
				<ul class="qpr-mini-cart-contents">
					<!-- ajax content -->
				</ul>
				<p><a href="<?php echo home_url( '/cart/' ); ?>" class="button"><?php _e( 'View Cart', 'quote-press' ); ?></a></p>
			</div>

			<?php return ob_get_clean();

		}

	}

}