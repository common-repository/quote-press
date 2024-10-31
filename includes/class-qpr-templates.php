<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Templates' ) ) {

	class QPR_Templates {

		public function __construct() {

			add_filter( 'template_include', array( $this, 'serve_templates' ) );

		}

		public function serve_templates( $template ) {

			$plugin_path = untrailingslashit( plugin_dir_path( __DIR__ ) );
			$theme_product_template_path = get_stylesheet_directory() . '/quote-press/templates/product.php';
			$theme_archive_template_path = get_stylesheet_directory() . '/quote-press/templates/archive.php';
			$post_type = get_post_type();

			if( !empty( $post_type ) ) {

				if( $post_type == 'qpr_product' ) {

					if( is_single() ) {

						if( file_exists( $theme_product_template_path ) ) {

							$template = $theme_product_template_path;

						} else {

							$template = $plugin_path . '/templates/product.php';

						}

					} else {

						if( file_exists( $theme_archive_template_path ) ) {

							$template = $theme_archive_template_path;

						} else {

							$template = $plugin_path . '/templates/archive.php';

						}

					}

				}

			} else {

				$taxonomy = get_query_var( 'taxonomy' );

				if( !empty( $taxonomy ) ) {

					if( qpr_check_for_prefix( $taxonomy ) == true ) {

						if( file_exists( $theme_archive_template_path ) ) {

							$template = $theme_archive_template_path;

						} else {

							$template = $plugin_path . '/templates/archive.php';

						}

					}

				}

			}

			return $template;

		}

		public function get_email_template( $type ) {

			$valid_types = array(
				'header',
				'footer',
				'pending',
				'pending-customer',
				'paid',
				'paid-customer',
				'paid-unconf',
				'paid-unconf-customer',
				'sent',
				'sent-customer',
			);

			if( in_array( $type, $valid_types ) ) {

				$plugin_path = untrailingslashit( plugin_dir_path( __DIR__ ) );
				$template_path = get_stylesheet_directory() . '/quote-press/templates/emails/' . $type . '.php';

				if( file_exists( $template_path ) ) {

					$email_template = $template_path;

				} else {

					$email_template = $plugin_path . '/templates/emails/' . $type . '.php';

				}

			} else {

				$email_template = false;

			}

			return $email_template;

		}

		public function get_email_body( $type ) {

			$email_template_header = QPR_Templates::get_email_template( 'header' );
			$email_template_footer = QPR_Templates::get_email_template( 'footer' );
			$email_template_content = QPR_Templates::get_email_template( $type );

			if( $email_template_header !== false && $email_template_footer !== false && $email_template_content !== false ) {

				ob_start();
				// Specifically not include_once as get_email_body can be called multiple times from same function, meaning the header/footer need including multiple times
				include( $email_template_header );
				include( $email_template_content );
				include( $email_template_footer );
				$body = ob_get_clean();

			} else {

				$body = '<p>' . __( 'There was a problem getting the contents of this email, please contact us for further information.', 'quote-press' ) . '</p>';

			}

			return $body;

		}

		public function replace_quick_tags( $body, $post_id, $payment_option ) {

			if( !empty( $post_id ) )  {

				$body = str_replace( '{quote_id}', $post_id, $body );

			}

			if( !empty( $payment_option ) )  {

				$payment_options = QPR_Payments::payment_options();

				$body = str_replace( '{quote_payment_option}', strtolower( $payment_options[$payment_option] ), $body );

			}

			return $body;

		}

	}

}