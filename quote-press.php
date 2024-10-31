<?php

/*
Plugin Name: QuotePress
Plugin URI: https://wordpress.org/plugins/quote-press/
Description: The Ultimate WordPress Quote Plugin - Setup a store for your products & services and get quotes.
Version: 1.1.3
Author: QuotePress
Author URI: https://quotepress.org
Text Domain: quote-press
Domain Path: /languages
*/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
// Constants
define( 'QPR_NAME', 'QuotePress' );
define( 'QPR_URL', 'https://quotepress.org' );
define( 'QPR_VERSION', '1.1.3' );
// Freemius

if ( !function_exists( 'qpr_freemius' ) ) {
    function qpr_freemius()
    {
        global  $qpr_freemius ;
        
        if ( !isset( $qpr_freemius ) ) {
            require_once dirname( __FILE__ ) . '/includes/libraries/freemius/start.php';
            $qpr_freemius = fs_dynamic_init( array(
                'id'             => '4768',
                'slug'           => 'quote-press',
                'premium_slug'   => 'quote-press-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_0abe1e58fc8ee63c64833c68c2537',
                'is_premium'     => false,
                'premium_suffix' => 'Pro',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                'slug' => 'edit.php?post_type=qpr_quote',
            ),
                'is_live'        => true,
            ) );
        }
        
        return $qpr_freemius;
    }
    
    qpr_freemius();
    do_action( 'qpr_freemius_loaded' );
}

$active_plugins = get_option( 'active_plugins' );
// Check for WooCommerce

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', $active_plugins ) ) ) {
    
    if ( !class_exists( 'QPR' ) ) {
        class QPR
        {
            public function __construct()
            {
                // Functions
                require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';
                // Classes
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-admin.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-attributes.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-settings.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-products.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-variations.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-tax.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-payments.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-widgets.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-activation.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-quotes.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-background-process.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-templates.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-assets.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-filters.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-ajax.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-accounts.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-cart.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-checkout.php';
                require_once plugin_dir_path( __FILE__ ) . 'includes/class-qpr-customers.php';
                new QPR_Admin();
                new QPR_Attributes();
                new QPR_Settings();
                new QPR_Products();
                new QPR_Variations();
                new QPR_Tax();
                new QPR_Payments();
                new QPR_Widgets();
                new QPR_Activation();
                new QPR_Quotes();
                new QPR_Background_Process();
                new QPR_Templates();
                new QPR_Assets();
                new QPR_Filters();
                new QPR_Ajax();
                new QPR_Accounts();
                new QPR_Cart();
                new QPR_Checkout();
            }
        
        }
        new QPR();
    }

} else {
    $notice = __( 'QuotePress is a standalone system and should not be used with WooCommerce, to use deactivate WooCommerce.', 'quote-press' );
    // WooCommerce is activated and QuotePress is not already active
    
    if ( !in_array( 'quote-press/quote-press.php', apply_filters( 'active_plugins', $active_plugins ) ) ) {
        die( $notice );
    } else {
        // WooCommerce is activated and QuotePress is already active
        add_action( 'admin_notices', function () use( $notice ) {
            ?>

			<div class="notice notice-error">
				<p><?php 
            echo  $notice ;
            ?></p>
			</div>

		<?php 
        } );
    }

}
