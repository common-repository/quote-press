<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Activation' ) ) {

	class QPR_Activation {

		public function __construct() {

			register_activation_hook( plugin_dir_path( __DIR__ ) . 'quote-press.php', array( $this, 'qpr_flush_rewrites_next_load' ) );
			register_activation_hook( plugin_dir_path( __DIR__ ) . 'quote-press.php', array( $this, 'create_database_tables' ) );
			register_activation_hook( plugin_dir_path( __DIR__ ) . 'quote-press.php', array( $this, 'add_roles' ) );
			register_activation_hook( plugin_dir_path( __DIR__ ) . 'quote-press.php', array( $this, 'add_options' ) );

		}

		public function qpr_flush_rewrites_next_load() { // Has prefix as its the scheduled task

			set_transient( 'qpr_flush_rewrites_next_load', 1 ); // so product post type works straight away without flushing rewrites

		}

		public function create_database_tables() {

			// Global $wpdb

			global $wpdb;
			$wpdb->hide_errors();

			// Require upgrade

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			// Set charset
			
			$charset_collate = $wpdb->get_charset_collate();

			// Queries

			$queries = [
			    "
				CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "qpr_sessions (
					session_id bigint(20) NOT NULL AUTO_INCREMENT,
					session_key char(64) NOT NULL,
					session_value longtext,
					session_expiry text,
					PRIMARY KEY (session_id),
					UNIQUE KEY (session_key)
				) $charset_collate;
			    ",
			    "
				CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "qpr_variations (
					variation_id bigint(20) NOT NULL AUTO_INCREMENT,
					variation_attributes longtext NOT NULL,
					variation_product_id bigint(20) NOT NULL,
					variation_price text,
					variation_sku text,
					PRIMARY KEY (variation_id)
				) $charset_collate;
			    ",
			    "
				CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . "qpr_attributes (
					attribute_taxonomy varchar(191) NOT NULL,
					attribute_taxonomy_label text,
					attribute_variations int(11),
					PRIMARY KEY (attribute_taxonomy)
				) $charset_collate;
			    ",
			];

			// Do SQL

			foreach( $queries as $sql ) {
				dbDelta( $sql );
			}

			// Options

			add_option( 'qpr_table_version_sessions', '0.0.1' );
			add_option( 'qpr_table_version_variations', '0.0.1' );
			add_option( 'qpr_table_version_attributes', '0.0.1' );

		}

		public function add_roles() {

			add_role(
				'qpr_customer',
				'Customer',
				array(
					'read' => true,
				)
			);

		}

		public function add_options() {

			// Checks for '' are for checkboxes and mean if it already exists and is blank then the option doesn't get updated (as that would mean where the user had specifically stated something to be unchecked and so that is respected and not changed to on when going through reactivation)

			update_option( 'users_can_register', 1 ); // WP setting must be on

			if( get_option( 'qpr_first_time', 'not-exists' ) == 'not-exists' ) {  // check if first time option doesn't exist (not-exists is specified to return if the option doesn't exist as it would be false without, which is the same as existing but set to 0) this stops the value getting updated to 1 on reactivation/plugin update

				update_option( 'qpr_first_time', 1 );
				
			}

			if( empty( get_option( 'qpr_review_notice_after' ) ) ) {

				update_option( 'qpr_review_notice_after', strtotime( '+1 day', time() ) );
				
			}

			// Settings > General

			if( empty( get_option( 'qpr_default_country' ) ) ) {

				update_option( 'qpr_default_country', 'US' );

			}

			if( empty( get_option( 'qpr_default_currency' ) ) ) {

				update_option( 'qpr_default_currency', 'USD' );

			}

			if( empty( get_option( 'qpr_session_expiry' ) ) ) {

				update_option( 'qpr_session_expiry', 30 );

			}

			// Settings > Cart

			if( get_option( 'qpr_add_to_cart_redirect' ) !== '' ) {

				update_option( 'qpr_add_to_cart_redirect', 'on' );

			}

			if( get_option( 'qpr_display_skus_in_cart' ) !== '' ) {

				update_option( 'qpr_display_skus_in_cart', 'on' );

			}

			// Settings > Tax

			if( empty( get_option( 'qpr_product_price_tax_status' ) ) ) {

				update_option( 'qpr_product_price_tax_status', 'inc' );

			}

			if( empty( get_option( 'qpr_default_tax_profile' ) ) ) {

				update_option( 'qpr_default_tax_profile', '' );

			}

			// Settings > Payments

			if( empty( get_option( 'qpr_payments_bank_transfer' ) ) ) {

				update_option( 'qpr_payments_bank_transfer', 'on' );

			}

			if( empty( get_option( 'qpr_payments_bank_transfer_instructions' ) ) ) {

				update_option( 'qpr_payments_bank_transfer_instructions', __( 'Please make payment as soon as possible and contact us once paid.' ) );

			}

			if( empty( get_option( 'qpr_payments_check' ) ) ) {

				update_option( 'qpr_payments_check', 'on' );

			}

			if( empty( get_option( 'qpr_payments_check_instructions' ) ) ) {

				update_option( 'qpr_payments_check_instructions', __( 'Please make payment as soon as possible and contact us once paid.' ) );

			}

			// Settings > Notifications

			if( empty( get_option( 'qpr_notification_email_address' ) ) ) {

				update_option( 'qpr_notification_email_address', get_bloginfo( 'admin_email' ) );

			}

			if( empty( get_option( 'qpr_notification_email_color_background' ) ) ) {

				update_option( 'qpr_notification_email_color_background', '#f6f6f6' );

			}

			if( empty( get_option( 'qpr_notification_email_color_primary' ) ) ) {

				update_option( 'qpr_notification_email_color_primary', '#34495e' );

			}

			if( get_option( 'qpr_pending_notification' ) !== '' ) {

				update_option( 'qpr_pending_notification', 'on' );

			}

			if( get_option( 'qpr_paid_notification' ) !== '' ) {

				update_option( 'qpr_paid_notification', 'on' );

			}

			if( get_option( 'qpr_paid_unconf_notification' ) !== '' ) {

				update_option( 'qpr_paid_unconf_notification', 'on' );

			}

			if( get_option( 'qpr_sent_notification' ) !== '' ) {

				update_option( 'qpr_sent_notification', 'on' );

			}

			if( get_option( 'qpr_customer_notifications' ) !== '' ) {

				update_option( 'qpr_customer_notifications', 'on' );

			}

		}

	}

}