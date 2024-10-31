<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Payments' ) ) {

	class QPR_Payments {

		static function payment_options() {

			$payment_options = array(
				'bank_transfer' => __( 'Bank Transfer', 'quote-press' ),
				'check' => __( 'Check', 'quote-press' ),
			);

			return apply_filters( 'qpr_payment_options', $payment_options );

		}

		static function payment_option_settings() {

			$payment_option_settings = array();

			$payment_option_settings['bank_transfer'][] = array(
				'id'			=> 'bank-transfer-instructions',
				'label'			=> __( 'Bank Transfer Instructions', 'quote-press' ),
				'description'	=> __( 'Enter the instructions to be shown to customer when making a bank transfer payment.', 'quote-press' ),
				'field'			=> 'textarea',
				'public'		=> true,
			);

			$payment_option_settings['check'][] = array(
				'id'			=> 'check-instructions',
				'label'			=> __( 'Check Instructions', 'quote-press' ),
				'description'	=> __( 'Enter the instructions to be shown to customer when making a check payment.', 'quote-press' ),
				'field'			=> 'textarea',
				'public'		=> true,
			);

			return apply_filters( 'qpr_payment_option_settings', $payment_option_settings );

		}

	}

}