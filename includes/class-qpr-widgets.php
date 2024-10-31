<?php

if( !defined( 'ABSPATH' ) ) {
	exit;
}

if( !class_exists( 'QPR_Widgets' ) ) {

	class QPR_Widgets {

		public function __construct() {

			add_action( 'widgets_init', array( $this, 'widgets' ) );

		}

		public function widgets() {

			register_widget( 'QPR_Widget_Sorting' );
			register_widget( 'QPR_Widget_Filters' );
			register_widget( 'QPR_Widget_Filters_Applied' );
			register_widget( 'QPR_Widget_Categories' );
			register_widget( 'QPR_Widget_Mini_Cart' );

		}

	}

	class QPR_Widget_Sorting extends WP_Widget {

		static $widget_once; // This widget only allowed once on page

		public function __construct() {
			$widget_ops = array( 
				'classname' => 'qpr-widget-sorting',
				'description' => 'Displays sorting for products',
			);
			parent::__construct( 'QPR_Widget_Sorting', 'QPR Sorting', $widget_ops );
		}

		public function widget( $args, $instance ) {

			// Stop widget after once display

			if( !$this->widget_once ) {
				
				$this->widget_once = true;
			
			} else {
			
				return;
			
			}

			// Start widget

			if( qpr_is_taxonomy() == true || qpr_is_store() == true ) {

				$title = apply_filters( 'widget_title', $instance['title'] );

				echo $args['before_widget'];

				if( !empty( $title ) ) {

					echo $args['before_title'] . $title . $args['after_title'];

				}

				$current_orderby = sanitize_text_field( $_GET['orderby'] );
				$current_order = sanitize_text_field( $_GET['order'] );

				$url_no_query_var = untrailingslashit( strtok($_SERVER["REQUEST_URI"],'?') );
				
				if( empty( $_GET ) ) {

					$url_start = $url_no_query_var . '?';

				} else {

					$count = 0;
					$old_vars = '';

					foreach( $_GET as $gk => $gv ) {

						// removes all filters from old vars string (as added later)

						if( substr( $gk, 0, 5 ) !== "order" ) { // picks up both order and orderby

							if( $count == 0 ) {

								$old_vars = $old_vars . '?' . $gk . '=' . $gv;

							} else {

								$old_vars = $old_vars . '&' . $gk . '=' . $gv;

							}
							
							$count = $count + 1;

						}					

					}

					if( $count > 0 ) {
						
						$url_start = $url_no_query_var  . $old_vars . '&';

					} else {

						$url_start = $url_no_query_var  . $old_vars . '?';

					}

				}

				echo '<select id="qpr-sorting">';
				echo '<option value="featured_newest"' . ( !isset( $current_orderby ) || ( $current_orderby == 'featured_newest' && $current_order == 'ASC' ) ? ' selected' : '' ) . '>' . __( 'Featured/Newest', 'quote-press' ) . '</option>';
				echo '<option value="date_asc"' . ( $current_orderby == 'date' && $current_order == 'ASC' ? ' selected' : '' ) . '>' . __( 'Newest', 'quote-press' ) . '</option>';
				echo '<option value="date_desc"' . ( $current_orderby == 'date' && $current_order == 'DESC' ? ' selected' : '' ) . '>' . __( 'Oldest', 'quote-press' ) . '</option>';
				echo '<option value="name_asc"' . ( $current_orderby == 'name' && $current_order == 'ASC' ? ' selected' : '' ) . '>' . __( 'Name A-Z', 'quote-press' ) . '</option>';
				echo '<option value="name_desc"' . ( $current_orderby == 'name' && $current_order == 'DESC' ? ' selected' : '' ) . '>' . __( 'Name Z-A', 'quote-press' ) . '</option>';
				echo '</select>'; ?>

				<script>
					jQuery( document ).ready(function($) {

						// Specificially INLINE because of php vars
						
						$( '#qpr-sorting' ).change( function() {

							generateFilterStringRedirectSorting();

						});

						function generateFilterStringRedirectSorting() {

							// Sorting

							sorting = $( '#qpr-sorting' ).val();

							if( sorting == 'name_asc' ) {
								orderBy = 'name';
								order = 'ASC';
							} else if( sorting == 'name_desc' ) {
								orderBy = 'name';
								order = 'DESC';
							} else if( sorting == 'date_asc' ) {
								orderBy = 'date';
								order = 'ASC';
							} else if( sorting == 'date_desc' ) {
								orderBy = 'date';
								order = 'DESC';
							} else {
								orderBy = 'menu_order';
								order = 'ASC'; // menu order higher is higher on list					
							}

							filterString = 'orderby=' + orderBy + '&order=' + order;

							window.location.replace( '<?php echo $url_start; ?>' + filterString );

						}

					});

				</script>

				<?php echo $args['after_widget'];

			}

		}

		public function form( $instance ) { ?>

			<p>
				<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
			</p>

		<?php }

		public function update( $new_instance, $old_instance ) {

			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			return $instance;

		}

	}

	class QPR_Widget_Filters extends WP_Widget {

		static $widget_once; // This widget only allowed once on page

		public function __construct() {
			$widget_ops = array( 
				'classname' => 'qpr-widget-filters',
				'description' => 'Displays filters for all available variations in current category',
			);
			parent::__construct( 'QPR_Widget_Filters', 'QPR Filters', $widget_ops );
		}

		public function widget( $args, $instance ) {

			// Stop widget after once display

			if( !$this->widget_once ) {
				
				$this->widget_once = true;
			
			} else {
			
				return;
			
			}

			// Start widget

			if( qpr_is_taxonomy() == true || qpr_is_store() == true ) {

				global $wpdb;
				global $wp_query;
				global $wp;

				$title = apply_filters( 'widget_title', $instance['title'] );

				echo $args['before_widget'];

				if( !empty( $title ) ) {

					echo $args['before_title'] . $title . $args['after_title'];

				}

				$url_no_query_var = untrailingslashit( strtok($_SERVER["REQUEST_URI"],'?') );

				if( empty( $_GET ) ) {

					$url_start = $url_no_query_var . '?';

				} else {

					$count = 0;
					$old_vars = '';

					foreach( $_GET as $gk => $gv ) {

						// removes all filters from old vars string (as added later)

						if( substr( $gk, 0, 10 ) !== "filter_qpr" ) {

							if( $count == 0 ) {

								$old_vars = $old_vars . '?' . $gk . '=' . $gv;

							} else {

								$old_vars = $old_vars . '&' . $gk . '=' . $gv;

							}
							
							$count = $count + 1;

						}					

					}

					if( $count > 0 ) {
						
						$url_start = $url_no_query_var  . $old_vars . '&';

					} else {

						$url_start = $url_no_query_var  . $old_vars . '?';

					}

				}

				$wp_query_request = $wp_query->request;
				$wp_query_request_no_limit = explode( 'LIMIT', $wp_query_request );
				$wp_query_request_no_limit = $wp_query_request_no_limit[0];
				$wp_query_no_limit_post_ids = $wpdb->get_results( $wp_query_request_no_limit );

				$available_filterable_options = array();

				if( !empty( $wp_query_no_limit_post_ids ) ) {

					foreach( $wp_query_no_limit_post_ids as $no_limit_post_id ) {

						$no_limit_post_id = $no_limit_post_id->ID;

						// Attributes

						$all_attributes = QPR_Attributes::get_attributes( false );

						$taxonomies = array();

						foreach( $all_attributes as $k => $v ) {
							$taxonomies[] = $k;
						}

						$no_limit_post_id_terms = get_the_terms( $no_limit_post_id, $taxonomies );

						if( $no_limit_post_id_terms !== false && !is_wp_error( $no_limit_post_id_terms ) ) {

							foreach( $no_limit_post_id_terms as $no_limit_post_id_term ) {

								$available_filterable_options[$no_limit_post_id_term->taxonomy][$no_limit_post_id_term->term_id] = $no_limit_post_id_term->term_id;

							}

						}

					}

				}

				if( !empty( $available_filterable_options ) ) { ?>

					<div id="qpr-filters">
					
						<?php foreach( $available_filterable_options as $available_filterable_option_key => $available_filterable_option_value ) {

							$current_taxonomy = get_taxonomy( $available_filterable_option_key );
							
							echo '<div class="qpr-filter qpr-filter-' . $available_filterable_option_key . '">';
							echo $current_taxonomy->label . '<hr>';

							foreach( $available_filterable_option_value as $a ) {

								$checked = '';
								$a_term = get_term( $a );

								if( isset( $_GET['filter_' . $available_filterable_option_key ] ) ) {

									$applied_filter_explode = explode( '_', $_GET['filter_' . $available_filterable_option_key ] );

									foreach( $applied_filter_explode as $afe ) {

										if( $afe == $a ) {

											$checked = ' checked';

										}

									}

								}

								echo '<div><label><input type="checkbox" data-taxonomy="' . $available_filterable_option_key . '" data-attribute="' . $a . '" value=""' . $checked . '> ' . $a_term->name . '</label></div>';

							}

							echo '</div>';

						} ?>

						<script>
							jQuery( document ).ready(function($) {

								$( '#qpr-filters input[type="checkbox"]' ).change( function() {

									generateFilterStringRedirectFilters();

								});

								
								function generateFilterStringRedirectFilters() {

									var filterArray = [];

									// Attributes

									$( '#qpr-filters input[type="checkbox"]:checked' ).each( function() {

										filterArray.push([ 'filter_' + $(this).attr( 'data-taxonomy' ), $(this).attr( 'data-attribute' ) ] );

									});

									let arrayTest = filterArray,
										result = Object.entries(arrayTest.reduce((a, [key, value]) => {
										  if (a[key]) a[key] += `_${String(value)}`;
										  else a[key] = value;
										  return a;
										}, Object.create(null))).reduce((a, [key, value]) => a + `${key}=${value}&`, "").slice(0, -1);

									filterString = result;
									window.location.replace( '<?php echo $url_start; ?>' + filterString );

								}					

							});
						</script>

					</div>

				<?php } else {

					_e( 'No filters available', 'quote-press' );

				}

	 			echo $args['after_widget'];

			}

		}

		public function form( $instance ) { ?>

			<p>
				<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
			</p>

		<?php }

		public function update( $new_instance, $old_instance ) {

			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			return $instance;

		}


	}

	class QPR_Widget_Filters_Applied extends WP_Widget {

		static $widget_once; // This widget only allowed once on page

		public function __construct() {
			$widget_ops = array( 
				'classname' => 'qpr-widget-filters-applied',
				'description' => 'Displays applied filters for all available variations in current category',
			);
			parent::__construct( 'QPR_Widget_Filters_Applied', 'QPR Filters Applied', $widget_ops );
		}

		public function widget( $args, $instance ) {

			// Stop widget after once display

			if( !$this->widget_once ) {
				
				$this->widget_once = true;
			
			} else {
			
				return;
			
			}

			// Start widget

			if( qpr_is_taxonomy() == true || qpr_is_store() == true ) {

				$title = apply_filters( 'widget_title', $instance['title'] );

				echo $args['before_widget'];

				if( !empty( $title ) ) {

					echo $args['before_title'] . $title . $args['after_title'];

				}

				$url_no_query_var = untrailingslashit( strtok( $_SERVER["REQUEST_URI"], '?' ) );

				// Applied Filters

				$applied_filters = array();

				foreach( $_GET as $gk => $gv ) {

					if( substr( $gk, 0, 7 ) === "filter_" ) {

						$applied_filters[$gk] = $gv;

					}

				}

				if( !empty( $applied_filters ) ) {

					echo '<ul>';

					foreach( $applied_filters as $applied_filter_key => $applied_filter_value ) {

						echo '<li>' . qpr_get_taxonomy_label( str_replace( 'filter_', '', $applied_filter_key ) ) . ': ' . qpr_get_term_name( $applied_filter_value ) . '</li>';

					}

					echo '</ul>';
					echo '<p><a href="' . $url_no_query_var . '" class="button">' . __( 'Remove All Filters', 'quote-press' ) . '</a></p>';

				} else {

					_e( 'No filters applied', 'quote-press' );

				}

				echo $args['after_widget'];

			}

		}

		public function form( $instance ) { ?>

			<p>
				<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
			</p>

		<?php }

		public function update( $new_instance, $old_instance ) {

			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			return $instance;

		}

	}

	class QPR_Widget_Categories extends WP_Widget {

		public function __construct() {
			$widget_ops = array( 
				'classname' => 'qpr-widget-categories',
				'description' => __( 'Displays product categories', 'quote-press' ),
			);
			parent::__construct( 'QPR_Widget_Categories', 'QPR Categories', $widget_ops );
		}

		public function widget( $args, $instance ) {

			if( qpr_is_taxonomy() == true || qpr_is_store() == true ) {

				$title = apply_filters( 'widget_title', $instance['title'] );

				echo $args['before_widget'];

				if( !empty( $title ) ) {

					echo $args['before_title'] . $title . $args['after_title'];

				}

				$hide_empty = ( $instance['hide_empty'] == 'on' ? true : false );
				$show_counts = ( $instance['show_counts'] == 'on' ? true : false );

				echo '<ul>';

				wp_list_categories(
					array(
						'taxonomy' => 'qpr_product_cat',
						'title_li' => '',
						'hide_empty' => $hide_empty,
						'show_count' => $show_counts,
					)
				);

				echo '</ul>';

				echo $args['after_widget'];

			}

		}

		public function form( $instance ) { ?>

			<p>
				<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
			</p>
			<p>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_name( 'hide_empty' ); ?>" name="<?php echo $this->get_field_name( 'hide_empty' ); ?>"<?php echo ( $instance[ 'hide_empty' ] == 'on' ? ' checked' : '' ); ?>>
				<label for="<?php echo $this->get_field_name( 'hide_empty' ); ?>"><?php _e( 'Hide Empty' ); ?></label><br>
				<input type="checkbox" class="checkbox" id="<?php echo $this->get_field_name( 'show_counts' ); ?>" name="<?php echo $this->get_field_name( 'show_counts' ); ?>"<?php echo ( $instance[ 'show_counts' ] == 'on' ? ' checked' : '' ); ?>>
				<label for="<?php echo $this->get_field_name( 'show_counts' ); ?>"><?php _e( 'Show Counts' ); ?></label><br>
			</p>

		<?php }
	 
		public function update( $new_instance, $old_instance ) {

			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			$instance['hide_empty'] = ( !empty( $new_instance['hide_empty'] ) ) ? strip_tags( $new_instance['hide_empty'] ) : '';
			$instance['show_counts'] = ( !empty( $new_instance['show_counts'] ) ) ? strip_tags( $new_instance['show_counts'] ) : '';
			return $instance;

		}

	}

	class QPR_Widget_Mini_Cart extends WP_Widget {

		public function __construct() {
			$widget_ops = array( 
				'classname' => 'qpr-widget-mini-cart',
				'description' => 'Displays cart contents',
			);
			parent::__construct( 'QPR_Widget_Mini_Cart', 'QPR Mini Cart', $widget_ops );
		}

		public function widget( $args, $instance ) {

			if( qpr_is_cart() == false || qpr_is_checkout() == false ) {

				$title = apply_filters( 'widget_title', $instance['title'] );

				echo $args['before_widget'];

				if( !empty( $title ) ) {

					echo $args['before_title'] . $title . $args['after_title'];

				}

				echo do_shortcode( '[qpr_mini_cart]' );

				echo $args['after_widget'];

			}

		}

		public function form( $instance ) { ?>

			<p>
				<label for="<?php echo $this->get_field_name( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $instance[ 'title' ]; ?>" />
			</p>

		<?php }

		public function update( $new_instance, $old_instance ) {

			$instance = array();
			$instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
			return $instance;

		}

	}

}