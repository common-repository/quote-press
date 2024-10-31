<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Admin' ) ) {

	class QPR_Admin {

		public function __construct() {
			
			add_action( 'init', array( $this, 'store_rewrite' ) );
			add_action( 'init', array( $this, 'register_post_types' ), 0 );
			add_action( 'manage_qpr_quote_posts_columns', array( $this, 'add_quote_columns' ), 0 );
			add_action( 'manage_qpr_quote_posts_custom_column' , array( $this, 'quote_columns_render'), 10, 2 );
			add_action( 'manage_qpr_product_posts_columns', array( $this, 'add_product_columns' ), 0 );
			add_action( 'manage_qpr_product_posts_custom_column' , array( $this, 'product_columns_render'), 10, 2 );
			add_action( 'init', array( $this, 'register_taxonomies' ), 0 );
			add_action( 'admin_menu', array( $this, 'menu_pages' ), 10 );
			add_action( 'admin_menu', array( $this, 'menu_indicators' ), 20 );
			add_action( 'init', array( $this, 'add_post_statuses' ) );
			add_filter( 'parent_file', array( $this, 'active_menu_parent' ) );
			add_filter( 'submenu_file', array( $this, 'active_menu_sub' ), 2, 10 );
			add_action( 'admin_init', array( $this, 'menu_pages_remove_add_new' ) );
			add_action( 'after_setup_theme', array( $this, 'remove_admin_bar_for_customers' ) );
			add_action( 'admin_notices', array( $this, 'first_time_notice' ) );
			add_action( 'admin_notices', array( $this, 'review_notice' ) );
			add_action( 'init', array( $this, 'review_notice_actions' ) );

		}

		public function store_rewrite() {
		
			add_rewrite_rule( '^store/?', 'index.php?post_type=qpr_product', 'top' );
		
		}

		public function menu_pages_remove_add_new() {
			
			global $submenu;
			unset( $submenu['edit.php?post_type=qpr_quote'][10] ); // Add New Link for CPT

		}

		public function menu_pages() {

			// Products

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Products', 'quote-press' ), // page title
				__( 'Products', 'quote-press' ), // menu title
				'manage_options', // capability
				'edit.php?post_type=qpr_product', // menu slug
				'' // callback
			);

			// Categories

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Categories', 'quote-press' ), // page title
				__( 'Categories', 'quote-press' ), // menu title
				'manage_options', // capability
				'edit-tags.php?taxonomy=qpr_product_cat',  // menu slug
				''
			);

			// Attributes

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Attributes', 'quote-press' ), // page title
				__( 'Attributes', 'quote-press' ), // menu title
				'manage_options', // capability
				'qpr-atts',  // menu slug
				array( $this, 'attributes' )
			);

			// Variations

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Variations', 'quote-press' ), // page title
				__( 'Variations', 'quote-press' ), // menu title
				'manage_options', // capability
				'qpr-variations',  // menu slug
				array( $this, 'variations' )
			);

			// Customers

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Customers', 'quote-press' ), // page title
				__( 'Customers', 'quote-press' ), // menu title
				'manage_options', // capability
				'users.php?role=qpr_customer',  // menu slug
				''
			);

			// Settings

			add_submenu_page(
				'edit.php?post_type=qpr_quote', // parent slug
				__( 'Settings', 'quote-press' ), // page title
				__( 'Settings', 'quote-press' ), // menu title
				'manage_options', // capability
				'qpr-settings',  // menu slug
				array( $this, 'settings' )
			);

		}

		public function menu_indicators() {
			
			global $submenu;
			$status = 'qpr-pending';
			$num_posts = wp_count_posts( 'qpr_quote', 'readable' );
			$pending_count = 0;
			
			if( !empty( $num_posts->$status ) ) {

				$pending_count = $num_posts->$status;
				$submenu['edit.php?post_type=qpr_quote'][5][0] = $submenu['edit.php?post_type=qpr_quote'][5][0] . ' <span class="update-plugins count-$pending_count"><span class="plugin-count">' . number_format_i18n( $pending_count ) . '</span></span>';

			}

		}

		public function dashboard() {

			QPR_Dashboard::page();

		}

		public function attributes() {

			QPR_Attributes::page();

		}

		public function variations() {

			QPR_Variations::page();

		}

		public function settings() {

			QPR_Settings::page();

		}

		public function register_taxonomies() {

			// Categories

			$labels = array(
				'name'                       => _x( 'Product Categories', 'Taxonomy General Name', 'quote-press' ),
				'singular_name'              => _x( 'Product Category', 'Taxonomy Singular Name', 'quote-press' ),
				'menu_name'                  => __( 'Product Categories', 'quote-press' ),
				'all_items'                  => __( 'All Product Categories', 'quote-press' ),
				'parent_item'                => __( 'Parent Product Category', 'quote-press' ),
				'parent_item_colon'          => __( 'Parent Product Category:', 'quote-press' ),
				'new_item_name'              => __( 'New Product Category Name', 'quote-press' ),
				'add_new_item'               => __( 'Add New Product Category', 'quote-press' ),
				'edit_item'                  => __( 'Edit Product Category', 'quote-press' ),
				'update_item'                => __( 'Update Product Category', 'quote-press' ),
				'view_item'                  => __( 'View Product Category', 'quote-press' ),
				'separate_items_with_commas' => __( 'Separate Product Categories with commas', 'quote-press' ),
				'add_or_remove_items'        => __( 'Add or remove Product Categories', 'quote-press' ),
				'choose_from_most_used'      => __( 'Choose from the most used Product Categories', 'quote-press' ),
				'popular_items'              => __( 'Popular Product Categories', 'quote-press' ),
				'search_items'               => __( 'Search Product Categories', 'quote-press' ),
				'not_found'                  => __( 'Not Found', 'quote-press' ),
				'no_terms'                   => __( 'No Product Categories', 'quote-press' ),
				'items_list'                 => __( 'Product Categories list', 'quote-press' ),
				'items_list_navigation'      => __( 'Product Categories list navigation', 'quote-press' ),
			);

			register_taxonomy(
				'qpr_product_cat',
				'qpr_product',
				array(
					'hierarchical' => true, // categories
					'show_in_menu'  => 'qpr',
					'show_ui' => true,
					'label' => __( 'Categories', 'quote-press' ),
					'labels' => $labels,
					'rewrite' => array(
						'slug' => 'product-cat',
						'hierarchical' => true
					),
				)
			);

			// Tax Profiles

			$labels = array(
				'name'                       => _x( 'Tax Profiles', 'Taxonomy General Name', 'quote-press' ),
				'singular_name'              => _x( 'Tax Profile', 'Taxonomy Singular Name', 'quote-press' ),
				'menu_name'                  => __( 'Tax Profiles', 'quote-press' ),
				'all_items'                  => __( 'All Tax Profiles', 'quote-press' ),
				'parent_item'                => __( 'Parent Tax Profile', 'quote-press' ),
				'parent_item_colon'          => __( 'Parent Tax Profile:', 'quote-press' ),
				'new_item_name'              => __( 'New Tax Profile Name', 'quote-press' ),
				'add_new_item'               => __( 'Add New Tax Profile', 'quote-press' ),
				'edit_item'                  => __( 'Edit Tax Profile', 'quote-press' ),
				'update_item'                => __( 'Update Tax Profile', 'quote-press' ),
				'view_item'                  => __( 'View Tax Profile', 'quote-press' ),
				'separate_items_with_commas' => __( 'Separate Tax Profiles with commas', 'quote-press' ),
				'add_or_remove_items'        => __( 'Add or remove Tax Profiles', 'quote-press' ),
				'choose_from_most_used'      => __( 'Choose from the most used Tax Profiles', 'quote-press' ),
				'popular_items'              => __( 'Popular Tax Profiles', 'quote-press' ),
				'search_items'               => __( 'Search Tax Profiles', 'quote-press' ),
				'not_found'                  => __( 'Not Found', 'quote-press' ),
				'no_terms'                   => __( 'No Tax Profiles', 'quote-press' ),
				'items_list'                 => __( 'Tax Profiles list', 'quote-press' ),
				'items_list_navigation'      => __( 'Tax Profiles list navigation', 'quote-press' ),
			);

			register_taxonomy(
				'qpr_tax_profile',
				'',
				array(
					'hierarchical' => false,
					'labels'	=> $labels,
				)
			);

			// Attributes

			$attributes = QPR_Attributes::get_attributes( false );

			if( !empty( $attributes ) ) {

				foreach( $attributes as $attribute_taxonomy => $attribute_name ) {

					$labels = array(
						'name'              			=> $attribute_name,
						'singular_name'     			=> $attribute_name,
						'menu_name'         			=> $attribute_name,
						'all_items'         			=> __( 'All', 'quote-press' ) . ' ' . $attribute_name,
						'parent_item'       			=> __( 'Parent', 'quote-press' ) . ' ' . $attribute_name,
						'parent_item_colon' 			=> __( 'Parent', 'quote-press' ) . ' ' . $attribute_name . __( ':', 'quote-press' ),
						'new_item_name'     			=> __( 'New', 'quote-press' ) . ' ' . $attribute_name . ' ' . __( 'Name', 'quote-press' ),
						'add_new_item'      			=> __( 'Add New', 'quote-press' ) . ' ' . $attribute_name,
						'edit_item'         			=> __( 'Edit', 'quote-press' ) . ' ' . $attribute_name,
						'update_item'       			=> __( 'Update', 'quote-press' ) . ' ' . $attribute_name,
						'view_item'       				=> __( 'View', 'quote-press' ) . ' ' . $attribute_name,
						'separate_items_with_commas' 	=> __( 'Seperate', 'quote-press' ) . ' ' . $attribute_name . ' ' . __( 'with commas', 'quote-press' ),
						'add_or_remove_items'        	=> __( 'Add or remove', 'quote-press' ) . ' ' . $attribute_name . ' ' . __( 'with commas', 'quote-press' ),
						'choose_from_most_used'      	=> __( 'Choose from the most used', 'quote-press' ) . ' ' . $attribute_name,
						'popular_items'              	=> __( 'Popular', 'quote-press' ) . ' ' . $attribute_name,
						'search_items'               	=> __( 'Search', 'quote-press' ) . ' ' . $attribute_name,
						'not_found'                  	=> __( 'Not Found', 'quote-press' ),
						'no_terms'                   	=> __( 'No', 'quote-press' ) . ' ' . $attribute_name,
						'items_list'                 	=> $attribute_name . ' ' . __( 'list', 'quote-press' ),
						'items_list_navigation'      	=> $attribute_name . ' ' . __( 'list navigation', 'quote-press' ),
					);

					register_taxonomy(
						$attribute_taxonomy,
						'qpr_product',
						array(
							'hierarchical' => true, // categories
							'label' => $attribute_name,
							'labels' => $labels,
							'rewrite' => array(
								'slug' => 'product-att/' . str_replace( 'qpr_', '', $attribute_taxonomy ),
								'hierarchical' => true
							),
						)
					);

				}

			}

			// Flush Rewrites

			if( delete_transient( 'qpr_flush_rewrites_next_load' ) ) {

				flush_rewrite_rules();

			}

		}

		public function register_post_types() {

			// Quotes

			$labels = array(
				'name'                  => _x( 'Quotes', 'Post Type General Name', 'quote-press' ),
				'singular_name'         => _x( 'Quote', 'Post Type Singular Name', 'quote-press' ),
				'menu_name'             => __( 'QuotePress', 'quote-press' ),
				'name_admin_bar'        => __( 'Quote', 'quote-press' ),
				'archives'              => __( 'Quote Archives', 'quote-press' ),
				'attributes'            => __( 'Quote Attributes', 'quote-press' ),
				'parent_item_colon'     => __( 'Parent Quote:', 'quote-press' ),
				'all_items'             => __( 'Quotes', 'quote-press' ),
				'add_new_item'          => __( 'Add New Quote', 'quote-press' ),
				'add_new'               => __( 'Add New', 'quote-press' ),
				'new_item'              => __( 'New Quote', 'quote-press' ),
				'edit_item'             => __( 'Edit Quote', 'quote-press' ),
				'update_item'           => __( 'Update Quote', 'quote-press' ),
				'view_item'             => __( 'View Quote', 'quote-press' ),
				'view_items'            => __( 'View Quotes', 'quote-press' ),
				'search_items'          => __( 'Search Quote', 'quote-press' ),
				'not_found'             => __( 'Not found', 'quote-press' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'quote-press' ),
				'featured_image'        => __( 'Featured Image', 'quote-press' ),
				'set_featured_image'    => __( 'Set featured image', 'quote-press' ),
				'remove_featured_image' => __( 'Remove featured image', 'quote-press' ),
				'use_featured_image'    => __( 'Use as featured image', 'quote-press' ),
				'insert_into_item'      => __( 'Insert into quote', 'quote-press' ),
				'uploaded_to_this_item' => __( 'Uploaded to this quote', 'quote-press' ),
				'items_list'            => __( 'Quotes list', 'quote-press' ),
				'items_list_navigation' => __( 'Quotes list navigation', 'quote-press' ),
				'filter_items_list'     => __( 'Filter quotes list', 'quote-press' ),
			);

			$args = array(
				'label'                 => __( 'Quote', 'quote-press' ),
				'description'           => __( 'Quotes', 'quote-press' ),
				'labels'                => $labels,
				'supports'              => array(),
				'taxonomies'            => array(),
				'hierarchical'          => false,
				'public'                => false,
				'show_ui'               => true,
				'show_in_menu'          => true,
				'menu_position'         => 50,
				'menu_icon'				=> 'dashicons-qpr-menu-icon',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => false,       
				'exclude_from_search'   => true,
				'publicly_queryable'    => false,
				'capability_type'       => 'page',
				'supports'              => array( '' ),
			);

			register_post_type( 'qpr_quote', $args );

			// Products

			$taxonomies = array( 'qpr_product_cat' );
			$attributes = QPR_Attributes::get_attributes( false );

			if( !empty( $attributes ) ) {

				foreach( $attributes as $attribute_taxonomy => $attribute_name ) {

					$taxonomies[] = $attribute_taxonomy;

				}

			}

			$labels = array(
				'name'                  => _x( 'Products', 'Post Type General Name', 'quote-press' ),
				'singular_name'         => _x( 'Product', 'Post Type Singular Name', 'quote-press' ),
				'menu_name'             => __( 'Products', 'quote-press' ),
				'name_admin_bar'        => __( 'Product', 'quote-press' ),
				'archives'              => __( 'Product Archives', 'quote-press' ),
				'attributes'            => __( 'Options', 'quote-press' ),
				'parent_item_colon'     => __( 'Parent Product:', 'quote-press' ),
				'all_items'             => __( 'Products', 'quote-press' ),
				'add_new_item'          => __( 'Add New Product', 'quote-press' ),
				'add_new'               => __( 'Add New', 'quote-press' ),
				'new_item'              => __( 'New Product', 'quote-press' ),
				'edit_item'             => __( 'Edit Product', 'quote-press' ),
				'update_item'           => __( 'Update Product', 'quote-press' ),
				'view_item'             => __( 'View Product', 'quote-press' ),
				'view_items'            => __( 'View Products', 'quote-press' ),
				'search_items'          => __( 'Search Product', 'quote-press' ),
				'not_found'             => __( 'Not found', 'quote-press' ),
				'not_found_in_trash'    => __( 'Not found in Trash', 'quote-press' ),
				'featured_image'        => __( 'Featured Image', 'quote-press' ),
				'set_featured_image'    => __( 'Set featured image', 'quote-press' ),
				'remove_featured_image' => __( 'Remove featured image', 'quote-press' ),
				'use_featured_image'    => __( 'Use as featured image', 'quote-press' ),
				'insert_into_item'      => __( 'Insert into product', 'quote-press' ),
				'uploaded_to_this_item' => __( 'Uploaded to this product', 'quote-press' ),
				'items_list'            => __( 'Products list', 'quote-press' ),
				'items_list_navigation' => __( 'Products list navigation', 'quote-press' ),
				'filter_items_list'     => __( 'Filter products list', 'quote-press' ),
			);

			$args = array(
				'label'                 => __( 'Product', 'quote-press' ),
				'description'           => __( 'Products', 'quote-press' ),
				'labels'                => $labels,
				'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'excerpt' ), // page attributes for display order
				'taxonomies'            => $taxonomies,
				'hierarchical'          => false,
				'public'                => true,
				'show_ui'               => true,
				'show_in_menu'          => false,
				'menu_position'         => 5,
				'menu_icon'             => 'dashicons-qpr-menu-icon',
				'show_in_admin_bar'     => true,
				'show_in_nav_menus'     => true,
				'can_export'            => true,
				'has_archive'           => true,
				'exclude_from_search'   => false,
				'publicly_queryable'    => true,
				'capability_type'       => 'post',
				'rewrite'				=> array(
					'slug' => 'product',
					'hierarchical' => true
				)
			);

			register_post_type( 'qpr_product', $args );

		}

		public function add_quote_columns( $columns ) {

			unset( $columns['date'] );
			$columns['title'] = __( 'ID', 'quote-press' );
			$columns['date_requested'] = __( 'Date Requested', 'quote-press' );
			$columns['valid_until'] = __( 'Valid Until', 'quote-press' );
			$columns['status'] = __( 'Status', 'quote-press' );
			$columns['total'] = __( 'Total', 'quote-press' );
			$columns['customer'] = __( 'Customer', 'quote-press' );
			$columns['date'] = __( 'Last Modified', 'quote-press' );
			return $columns;

		}

		public function quote_columns_render( $column, $post_id ) {

			switch ( $column ) {

				case 'status' :
					$quote_statuses = qpr_get_quote_statuses( true );
					echo $quote_statuses[ get_post_status( $post_id ) ];
					break;

				case 'date_requested' :
					echo qpr_date_format( get_post_time( 'U', true, $post_id ) );
					break;

				case 'valid_until' :

					$valid_until = get_post_meta( $post_id, '_qpr_valid_until', true );

					if( !empty( $valid_until ) ) {
						echo qpr_date_format( $valid_until );
					}
					
					break;

				case 'customer' :

					$user_id = get_post_meta( $post_id, '_qpr_user', true ) ;
					echo QPR_Customers::customer_link( $user_id, 'all', true );

					break;

				case 'total' :

					echo qpr_get_currency_symbol( get_post_meta( $post_id, '_qpr_currency', true ) ) . get_post_meta( $post_id, '_qpr_grand_totals_total', true );
					break;

			}

		}

		public function add_product_columns( $columns ) {

			unset( $columns['date'] );
			$columns['sku']	= __( 'SKU', 'quote-press' );
			$columns['price'] = __( 'Price', 'quote-press' );
			$columns['date'] = __( 'Last Modified', 'quote-press' );
			return $columns;

		}

		public function product_columns_render( $column, $post_id ) {

			switch ( $column ) {

				case 'sku' :
					echo get_post_meta( $post_id, '_qpr_sku', true );
					break;

				case 'price' :

					$variations = QPR_Variations::get_product_variations( $post_id );

					usort( $variations, function($a, $b) {
						return (float)$a['price'] - (float)$b['price'];
					});



					if( !empty( $variations ) ) {

						$price_from = reset($variations)['price'];
						$price_to = $variations[key(array_slice($variations, -1, 1, true) )]['price'];

						if( !empty( $price_from ) && !empty( $price_to ) ) {

							echo $price_from . ' ' . __( 'to', 'quote-press' ) . ' ' . $price_to;

						} else {

							echo '<a href="' . admin_url( 'edit.php?post_type=qpr_quote&page=qpr-variations' ) . '">' . __( 'Variation Prices Missing', 'quote-press' ) . '</a>';

						}

						

					} else {
					
						echo get_post_meta( $post_id, '_qpr_price', true );	
					
					}
					
					break;

			}

		}

		public function add_post_statuses() {

			$post_statuses = qpr_get_quote_statuses( true );

			foreach( $post_statuses as $post_status => $label ) {

				register_post_status( $post_status, array(
					'label'                     => $label,
					'public'                    => true,
					'exclude_from_search'       => false,
					'show_in_admin_all_list'    => true,
					'show_in_admin_status_list' => true,
					'post_type'                 => 'qpr_quote',
				) );

			}

		}

		public function add_notice( $type, $message ) {

			if( !empty( $type ) && !empty( $message ) ) {

				add_action( 'admin_notices', function() use ( $type, $message ) {
					$class = 'notice notice-' . $type;
					printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) ); 
				}, 10, 2 );

			}

		}

		public function active_menu_parent( $parent_file ) {

			$current_screen = get_current_screen();

			if( get_post_type() == 'qpr_quote' ) {

				$parent_file = 'edit.php?post_type=qpr_quote';

			}

			if( get_post_type() == 'qpr_product' ) {

				$parent_file = 'edit.php?post_type=qpr_quote';

			}

			if( qpr_check_for_prefix( $current_screen->taxonomy ) == true ) {

				$parent_file = 'edit.php?post_type=qpr_quote';

			}

			return $parent_file;

		}

		public function active_menu_sub( $submenu_file, $parent_file ) {

			$current_screen = get_current_screen();

			if( get_post_type() == 'qpr_quote' ) {

				$submenu_file = 'edit.php?post_type=qpr_quote';

			}

			if( get_post_type() == 'qpr_product' ) {

				$submenu_file = 'edit.php?post_type=qpr_product';

			}

			if( qpr_check_for_prefix( $current_screen->taxonomy ) == true ) {

				if( $current_screen->taxonomy == 'qpr_product_cat' ) {

					$submenu_file = 'edit-tags.php?taxonomy=qpr_product_cat';

				} else if( $current_screen->taxonomy == 'qpr_tax_profile' ) {

					$submenu_file = 'qpr-settings';

				} else {

					$submenu_file = 'qpr-atts';

				}

			}

			return $submenu_file;

		}

		public function remove_admin_bar_for_customers() {

			$user = wp_get_current_user();
			$roles = $user->roles;



			if( in_array( 'qpr_customer', $roles ) && !current_user_can( 'manage_options' ) ) {

				show_admin_bar(false);

			}

		}

		public function first_time_notice() {

			if( get_option( 'qpr_first_time' ) == 1 ) {

				echo '<div class="notice notice-success"><p>' . sprintf( __( 'To get started with QuotePress you will need to <a href="%s">create pages</a>.', 'quote-press' ), 
			get_admin_url( '', 'admin.php?page=qpr-settings&tab=setup' )
		) . '</p></div>';

			}

		}

		public function review_notice() {

			if( current_user_can( 'manage_options' ) ) {

				$current_user_id = get_current_user_id();
				$dismiss_status = get_user_meta( $current_user_id, 'qpr_review_notice_dismiss', true );
				$remind_after = get_user_meta( $current_user_id, 'qpr_review_notice_remind', true );
				$show_dismiss_after = get_option( 'qpr_review_notice_after', true ); // timestamp of install

				if( $dismiss_status !== 'yes' && time() > $show_dismiss_after ) {

					$show_notification = false;

					if( !empty( $remind_after ) ) {

						if( time() > $remind_after ) {

							$show_notification = true;

						}

					} else {

						$show_notification = true;

					}

					if( $show_notification == true ) { ?>

						<div class="notice notice-success">
							<p><?php _e( 'Thanks for using QuotePress, now you have been using QuotePress for a while we would love to hear your feedback on WordPress.org. It helps us develop the plugin further. If you have time please leave a review.', 'text-domain' ); ?></p>
							<p><form method="post"><a href="https://wordpress.org/support/plugin/quote-press/reviews/#new-post" target="_blank" class="button button-primary"><?php _e( 'Leave Review', 'quote-press' ); ?></a><button class="button" name="qpr_review_notice_remind"><?php _e( 'Remind Later', 'quote-press' ); ?></button><button class="button" name="qpr_review_notice_dismiss"><?php _e( 'Don\'t Show Again', 'quote-press' ); ?></button></form></p>
						</div>

					<?php }

				}

			}

		}

		public function review_notice_actions() {

			$current_user_id = get_current_user_id();

			if( isset( $_POST['qpr_review_notice_dismiss'] ) ) {

				update_user_meta( $current_user_id, 'qpr_review_notice_dismiss', 'yes' );
				delete_user_meta( $current_user_id, 'qpr_review_notice_remind' );

			} elseif( isset( $_POST['qpr_review_notice_remind'] ) ) {

				update_user_meta( $current_user_id, 'qpr_review_notice_remind', strtotime( '+1 day', time() ) );

			}

		}

	}

}