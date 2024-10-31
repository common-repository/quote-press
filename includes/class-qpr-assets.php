<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Assets' ) ) {

	class QPR_Assets {

		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'public_enqueues' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueues' ) );

		}

		public function public_enqueues() {

			wp_enqueue_script( 'jquery' );
			wp_enqueue_style( 'qpr-public', plugin_dir_url( __DIR__ ) . 'assets/css/public.css', array(), filemtime( plugin_dir_path( __DIR__ ) . 'assets/css/public.css' ) );
			wp_enqueue_script( 'qpr-public', plugin_dir_url( __DIR__ ) . 'assets/js/public.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/public.js' ) );

			if( qpr_is_product() == true ) {

				wp_enqueue_script( 'qpr-public-product', plugin_dir_url( __DIR__ ) . 'assets/js/public-product.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/public-product.js' ) );
				wp_enqueue_style( 'qpr-lightcase', plugin_dir_url( __DIR__ ) . 'includes/libraries/lightcase/src/css/lightcase.css', array(), filemtime( plugin_dir_path( __DIR__ ) . 'includes/libraries/lightcase/src/css/lightcase.css' ) );
				wp_enqueue_script( 'qpr-lightcase', plugin_dir_url( __DIR__ ) . 'includes/libraries/lightcase/src/js/lightcase.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'includes/libraries/lightcase/src/js/lightcase.js' ) );

			}

		}

		public function admin_enqueues() {

			$current_screen = get_current_screen();

			wp_enqueue_style( 'qpr-admin', plugin_dir_url( __DIR__ ) . 'assets/css/admin.css', array(), filemtime( plugin_dir_path( __DIR__ ) . 'assets/css/admin.css' ) );
			wp_enqueue_script( 'qpr-admin', plugin_dir_url( __DIR__ ) . 'assets/js/admin.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/admin.js' ) );

			if( !is_null( $current_screen ) ) {

				if( $current_screen->id == 'qpr_quote_page_qpr-atts' ) {

					wp_enqueue_script( 'qpr-admin-attributes', plugin_dir_url( __DIR__ ) . 'assets/js/admin-attributes.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/admin-attributes.js' ) );

				} elseif( $current_screen->id == 'qpr_quote' ) {

					wp_enqueue_script( 'jquery-ui-datepicker' );
					wp_register_style( 'jquery-ui', plugin_dir_url( __DIR__ ) . 'includes/libraries/jquery-ui-themes/themes/smoothness/jquery-ui.css' );
					wp_enqueue_style( 'jquery-ui' );

					wp_enqueue_script( 'qpr-admin-quotes', plugin_dir_url( __DIR__ ) . 'assets/js/admin-quotes.js', array( 'jquery' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/admin-quotes.js' ) );

				} elseif( $current_screen->id == 'qpr_quote_page_qpr-settings' ) {

					wp_enqueue_style( 'wp-color-picker' );
					wp_enqueue_script( 'qpr-admin-settings', plugin_dir_url( __DIR__ ) . 'assets/js/admin-settings.js', array( 'jquery', 'wp-color-picker' ), filemtime( plugin_dir_path( __DIR__ ) . 'assets/js/admin-settings.js' ) );

				}

			}

		}

	}

}