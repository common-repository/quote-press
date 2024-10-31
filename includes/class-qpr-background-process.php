<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Background_Process' ) ) {

	class QPR_Background_Process {

		public function __construct() {

			register_activation_hook( plugin_dir_path( __DIR__ ) . 'quote-press.php', array( $this, 'schedule_events' ) );
			add_action( 'qpr_clear_expired_sessions', array( $this, 'clear_expired_sessions' ) );
			add_action( 'qpr_expire_quotes', array( $this, 'expire_quotes' ) );

		}

		public function schedule_events() {

			if( !wp_next_scheduled( 'qpr_clear_expired_sessions' ) ) {

				// 2am

				wp_schedule_event( strtotime( '02:00:00' ), 'daily', 'qpr_clear_expired_sessions' );

			}

			if( !wp_next_scheduled( 'qpr_expire_quotes' ) ) {

				// 15 seconds after midnight (expirys are set at 23:59:59 so this just waits 15 seconds after)

				wp_schedule_event( strtotime( '00:00:15' ), 'daily', 'qpr_expire_quotes' );

			}

		}

		public function clear_expired_sessions() {

			$session_expiry = get_option( 'qpr_session_expiry' );

			if( $session_expiry >= 1 ) {

				global $wpdb;
				$cut_off = strtotime( '-' . $session_expiry . ' days', time() );
				$wpdb->query( "DELETE FROM {$wpdb->prefix}qpr_sessions WHERE session_expiry < '{$cut_off}'" );

			}

		}

		public function expire_quotes() {

			global $wpdb;
			
			$current_date = time();

			// Gets all sent quotes and expiries

			$quotes = $wpdb->get_results( "
				SELECT ID, meta_value AS expiry FROM `{$wpdb->prefix}posts` as posts
				JOIN `{$wpdb->prefix}postmeta` AS postmeta ON posts.ID = postmeta.post_id
				WHERE `post_type` = 'qpr_quote'
				AND `post_status` = 'qpr-sent'
				AND	`meta_key` = '_qpr_valid_until'
			");

			foreach( $quotes as $quote ) {

				if( $quote->expiry <= $current_date ) {

					wp_update_post(
						array(
							'ID' => $quote->ID,
							'post_status' => 'qpr-expired'
						)
					);

				}

			}

		}

	}

}