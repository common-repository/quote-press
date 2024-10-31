<?php

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
if ( !class_exists( 'QPR_Accounts' ) ) {
    class QPR_Accounts
    {
        public function __construct()
        {
            add_shortcode( 'qpr_login', array( $this, 'render_login_form' ) );
            add_shortcode( 'qpr_register', array( $this, 'render_register_form' ) );
            add_shortcode( 'qpr_lost_password', array( $this, 'render_lost_password_form' ) );
            add_shortcode( 'qpr_password_reset', array( $this, 'render_password_reset_form' ) );
            add_shortcode( 'qpr_account', array( $this, 'render_account' ) );
            // Redirects
            add_action( 'login_form_login', array( $this, 'redirect_to_frontend_login' ) );
            add_filter(
                'authenticate',
                array( $this, 'redirect_to_frontend_login_at_authenticate' ),
                101,
                3
            );
            add_filter(
                'login_redirect',
                array( $this, 'redirect_after_login' ),
                10,
                3
            );
            add_action( 'wp_logout', array( $this, 'redirect_after_logout' ) );
            add_action( 'login_form_register', array( $this, 'redirect_to_frontend_register' ) );
            add_action( 'login_form_lostpassword', array( $this, 'redirect_to_frontend_lost_password' ) );
            add_action( 'login_form_rp', array( $this, 'redirect_to_frontend_password_reset' ) );
            add_action( 'login_form_resetpass', array( $this, 'redirect_to_frontend_password_reset' ) );
            // Handlers
            add_action( 'login_form_register', array( $this, 'register_user' ) );
            add_action( 'login_form_lostpassword', array( $this, 'lost_password' ) );
            add_action( 'login_form_rp', array( $this, 'password_reset' ) );
            add_action( 'login_form_resetpass', array( $this, 'password_reset' ) );
            add_action( 'show_user_profile', array( $this, 'customer_fields' ) );
            add_action( 'edit_user_profile', array( $this, 'customer_fields' ) );
            add_action( 'personal_options_update', array( $this, 'customer_fields_save' ) );
            add_action( 'edit_user_profile_update', array( $this, 'customer_fields_save' ) );
        }
        
        public function change_password_logout()
        {
            
            if ( $_REQUEST['account_change_password'] == '1' ) {
                wp_logout();
                // has to be done before headers sent
            }
        
        }
        
        public function render_login_form( $attributes, $content = null )
        {
            $default_attributes = array(
                'show_title' => false,
            );
            $attributes = shortcode_atts( $default_attributes, $attributes );
            if ( is_user_logged_in() ) {
                return __( 'You are already signed in.', 'quote-press' );
            }
            // Pass the redirect parameter to the WordPress login functionality: by default,
            // don't specify a redirect, but if a valid redirect URL has been passed as
            // request parameter, use it.
            $attributes['redirect'] = '';
            if ( isset( $_REQUEST['redirect_to'] ) ) {
                $attributes['redirect'] = wp_validate_redirect( $_REQUEST['redirect_to'], $attributes['redirect'] );
            }
            // Error messages
            $errors = array();
            
            if ( isset( $_REQUEST['login'] ) ) {
                $request_login = sanitize_text_field( $_REQUEST['login'] );
                $error_codes = explode( ',', $request_login );
                foreach ( $error_codes as $code ) {
                    $errors[] = $this->get_error_message( $code );
                }
            }
            
            $attributes['errors'] = $errors;
            // Check if user just logged out
            $attributes['logged_out'] = isset( $_REQUEST['logged_out'] ) && $_REQUEST['logged_out'] == true;
            // Check if the user just requested a new password
            $attributes['lost_password_sent'] = isset( $_REQUEST['check_email'] ) && $_REQUEST['check_email'] == 'confirm';
            // Check if user just updated password
            $attributes['password_updated'] = isset( $_REQUEST['password'] ) && $_REQUEST['password'] == 'changed';
            // Render the login form using an external template
            ob_start();
            ?>

			<?php 
            
            if ( $attributes['show_title'] ) {
                ?>
				<h2><?php 
                _e( 'Sign In', 'quote-press' );
                ?></h2>
			<?php 
            }
            
            ?>

			<?php 
            
            if ( count( $attributes['errors'] ) > 0 ) {
                ?>
				<?php 
                foreach ( $attributes['errors'] as $error ) {
                    ?>
					<div class="qpr-notice qpr-notice-error">
						<?php 
                    echo  $error ;
                    ?>
					</div>
				<?php 
                }
                ?>
			<?php 
            }
            
            ?>

			<?php 
            
            if ( $attributes['logged_out'] ) {
                ?>
				<div class="qpr-notice qpr-notice-info">
					<?php 
                _e( 'You have been logged out.', 'quote-press' );
                ?>
				</div>
			<?php 
            }
            
            ?>

			<?php 
            
            if ( $attributes['lost_password_sent'] ) {
                ?>
				<div class="qpr-notice qpr-notice-info">
					<?php 
                _e( 'Check your email for a link to reset your password.', 'quote-press' );
                ?>
				</div>
			<?php 
            }
            
            ?>

			<?php 
            
            if ( $attributes['password_updated'] ) {
                ?>
				<div class="qpr-notice qpr-notice-success">
					<?php 
                _e( 'Your password has been changed. You can sign in now.', 'quote-press' );
                ?>
				</div>
			<?php 
            }
            
            ?>

			<p><a href="<?php 
            echo  home_url( 'register' ) ;
            ?>"><?php 
            _e( 'No account? Register here', 'quote-press' );
            ?></a></p>
			
			<?php 
            wp_login_form( array(
                'label_username' => __( 'Email', 'quote-press' ),
                'label_log_in'   => __( 'Log In', 'quote-press' ),
                'redirect'       => $attributes['redirect'],
            ) );
            ?>

			<p><a href="<?php 
            echo  wp_lostpassword_url() ;
            ?>"><?php 
            _e( 'Forgot your password?', 'quote-press' );
            ?></a></p>

			<?php 
            return ob_get_clean();
        }
        
        public function render_register_form( $attributes, $content = null )
        {
            $default_attributes = array(
                'show_title' => false,
            );
            $attributes = shortcode_atts( $default_attributes, $attributes );
            
            if ( is_user_logged_in() ) {
                return '<div class="qpr-notice qpr-notice-info">' . __( 'You are already signed in.', 'quote-press' ) . '</div>';
            } elseif ( !get_option( 'users_can_register' ) ) {
                return '<div class="qpr-notice qpr-notice-error">' . __( 'Registering new users is currently not allowed.', 'quote-press' ) . '</div>';
            } else {
                // Retrieve possible errors from request parameters
                $attributes['errors'] = array();
                
                if ( isset( $_REQUEST['register-errors'] ) ) {
                    $register_errors = sanitize_text_field( $_REQUEST['register-errors'] );
                    $error_codes = explode( ',', $register_errors );
                    foreach ( $error_codes as $error_code ) {
                        $attributes['errors'][] = $this->get_error_message( $error_code );
                    }
                }
                
                ob_start();
                ?>

				<?php 
                
                if ( $attributes['show_title'] ) {
                    ?>
					<h3><?php 
                    _e( 'Register', 'quote-press' );
                    ?></h3>
				<?php 
                }
                
                ?>

				<?php 
                
                if ( count( $attributes['errors'] ) > 0 ) {
                    ?>
					<?php 
                    foreach ( $attributes['errors'] as $error ) {
                        ?>
						<div class="qpr-notice qpr-notice-error"><?php 
                        echo  $error ;
                        ?></div>
					<?php 
                    }
                    ?>
				<?php 
                }
                
                $countries = qpr_get_countries();
                $default_country = get_option( 'qpr_default_country' );
                ?>

				<form class="qpr-register-form" action="<?php 
                echo  wp_registration_url() ;
                ?>" method="post">
					<?php 
                wp_nonce_field( 'qpr-register-form' );
                ?>
					<input type="hidden" name="qpr_register">
					<div>
						<label for="qpr-email"><?php 
                _e( 'Email', 'quote-press' );
                ?></label>
						<input id="qpr-email" type="email" name="email" required>
					</div>
					<div>
						<label for="qpr-phone"><?php 
                _e( 'Phone', 'quote-press' );
                ?></label>
						<input id="qpr-phone" type="text" name="phone" required>
					</div>
					<div>
						<label for="qpr-first-name"><?php 
                _e( 'First name', 'quote-press' );
                ?></label>
						<input id="qpr-first-name" type="text" name="first_name" required>
					</div>
					<div>
						<label for="qpr-last-name"><?php 
                _e( 'Last name', 'quote-press' );
                ?></label>
						<input id="qpr-last-name" type="text" name="last_name" required>
					</div>
					<div>
						<label for="qpr-company"><?php 
                _e( 'Company', 'quote-press' );
                ?></label>
						<input id="qpr-company" type="text" name="company">
					</div>
					<div>
						<label for="qpr-address-line-1"><?php 
                _e( 'Address Line 1', 'quote-press' );
                ?></label>
						<input id="qpr-address-line-1" type="text" name="address_line_1" required>
					</div>
					<div>
						<label for="qpr-address-line-2"><?php 
                _e( 'Address Line 2', 'quote-press' );
                ?></label>
						<input id="qpr-address-line-2" type="text" name="address_line_2">
					</div>
					<div>
						<label for="qpr-city"><?php 
                _e( 'City', 'quote-press' );
                ?></label>
						<input id="qpr-city" type="text" name="city" required>
					</div>
					<div>
						<label for="qpr-state"><?php 
                _e( 'State', 'quote-press' );
                ?></label>
						<input id="qpr-state" type="text" name="state" required>
					</div>
					<div>
						<label for="qpr-postcode"><?php 
                _e( 'Postcode/Zip', 'quote-press' );
                ?></label>
						<input id="qpr-postcode" type="text" name="postcode" required>
					</div>
					<div>
						<label for="qpr-country"><?php 
                _e( 'Country:', 'quote-press' );
                ?></label>
						<select id="qpr-country" name="country" required>
							<?php 
                foreach ( $countries as $country ) {
                    ?>
								<option value="<?php 
                    echo  $country['abbreviation'] ;
                    ?>"<?php 
                    echo  ( $default_country == $country['abbreviation'] ? ' selected' : '' ) ;
                    ?>><?php 
                    echo  $country['country'] ;
                    ?> <?php 
                    echo  __( '(', 'quote-press' ) . $country['abbreviation'] . __( ')', 'quote-press' ) ;
                    ?></option>
							<?php 
                }
                ?>
						</select>
					</div>
					<div>
						<?php 
                _e( 'Note: You choose a password after verifying your email.', 'quote-press' );
                ?>
					</div>
					<p><input type="submit" name="submit" class="register-button" value="<?php 
                _e( 'Register', 'quote-press' );
                ?>"></p>
				</form>

				<?php 
                return ob_get_clean();
            }
        
        }
        
        public function render_lost_password_form( $attributes, $content = null )
        {
            // Parse shortcode attributes
            $default_attributes = array(
                'show_title' => false,
            );
            $attributes = shortcode_atts( $default_attributes, $attributes );
            
            if ( is_user_logged_in() ) {
                return __( 'You are already signed in.', 'quote-press' );
            } else {
                // Retrieve possible errors from request parameters
                $attributes['errors'] = array();
                
                if ( isset( $_REQUEST['errors'] ) ) {
                    $errors = sanitize_text_field( $_REQUEST['errors'] );
                    $error_codes = explode( ',', $errors );
                    foreach ( $error_codes as $error_code ) {
                        $attributes['errors'][] = $this->get_error_message( $error_code );
                    }
                }
                
                ob_start();
                ?>

				<?php 
                
                if ( $attributes['show_title'] ) {
                    ?>
					<h3><?php 
                    _e( 'Forgot Your Password?', 'quote-press' );
                    ?></h3>
				<?php 
                }
                
                ?>

				<?php 
                
                if ( count( $attributes['errors'] ) > 0 ) {
                    ?>
					<?php 
                    foreach ( $attributes['errors'] as $error ) {
                        ?>
						<div class="qpr-notice qpr-notice-error"><?php 
                        echo  $error ;
                        ?></p>
					<?php 
                    }
                    ?>
				<?php 
                }
                
                ?>

				<p><?php 
                _e( 'Enter your email address and we\'ll send you a link you can use to pick a new password.', 'quote-press' );
                ?></p>

				<form class="qpr-lost-password-form" action="<?php 
                echo  wp_lostpassword_url() ;
                ?>" method="post">
					<?php 
                wp_nonce_field( 'qpr-nonce-lost-password-form' );
                ?>
					<input type="hidden" name="qpr_lost_password">
					<div>
						<label for="qpr-email"><?php 
                _e( 'Email', 'quote-press' );
                ?></label>
						<input id="qpr-email" type="email" name="user_login">
					</div>
					<input type="submit" name="submit" class="button" value="<?php 
                _e( 'Reset Password', 'quote-press' );
                ?>">
				</form>

				<?php 
                return ob_get_clean();
            }
        
        }
        
        public function render_password_reset_form( $attributes, $content = null )
        {
            // Parse shortcode attributes
            $default_attributes = array(
                'show_title' => false,
            );
            $attributes = shortcode_atts( $default_attributes, $attributes );
            
            if ( is_user_logged_in() ) {
                return __( 'You are already signed in.', 'quote-press' );
            } else {
                
                if ( isset( $_REQUEST['login'] ) && isset( $_REQUEST['key'] ) ) {
                    $request_login = sanitize_text_field( $_REQUEST['login'] );
                    $request_key = sanitize_text_field( $_REQUEST['key'] );
                    $attributes['login'] = $request_login;
                    $attributes['key'] = $request_key;
                    // Error messages
                    $errors = array();
                    
                    if ( isset( $_REQUEST['error'] ) ) {
                        $request_error = sanitize_text_field( $_REQUEST['error'] );
                        $error_codes = explode( ',', $request_error );
                        foreach ( $error_codes as $code ) {
                            $errors[] = $this->get_error_message( $code );
                        }
                    }
                    
                    $attributes['errors'] = $errors;
                    ob_start();
                    ?>

					<?php 
                    
                    if ( $attributes['show_title'] ) {
                        ?>
						<h3><?php 
                        _e( 'Pick a New Password', 'quote-press' );
                        ?></h3>
					<?php 
                    }
                    
                    ?>

					<form class="qpr-password-reset-form" name="resetpassform" action="<?php 
                    echo  site_url( 'wp-login.php?action=resetpass' ) ;
                    ?>" method="post" autocomplete="off">
						<?php 
                    wp_nonce_field( 'qpr-nonce-password-reset-form' );
                    ?>
						<input type="hidden" name="qpr_password_reset">
						<input type="hidden" name="password_reset_login" value="<?php 
                    echo  esc_attr( $attributes['login'] ) ;
                    ?>" autocomplete="off">
						<input type="hidden" name="password_reset_key" value="<?php 
                    echo  esc_attr( $attributes['key'] ) ;
                    ?>">
						<?php 
                    
                    if ( count( $attributes['errors'] ) > 0 ) {
                        ?>
							<?php 
                        foreach ( $attributes['errors'] as $error ) {
                            ?>
								<div class="qpr-notice qpr-notice-error"><?php 
                            echo  $error ;
                            ?></div>
							<?php 
                        }
                        ?>
						<?php 
                    }
                    
                    ?>
						<p>
							<label for="qpr-pass-1"><?php 
                    _e( 'New password', 'quote-press' );
                    ?></label>
							<input id="qpr-pass-1" type="password" name="pass1" class="input" size="20" value="" autocomplete="off">
						</p>
						<p>
							<label for="qpr-pass-2"><?php 
                    _e( 'Repeat new password', 'quote-press' );
                    ?></label>
							<input id="qpr-pass-2" type="password" name="pass2" class="input" size="20" value="" autocomplete="off">
						</p>
						<p><?php 
                    echo  wp_get_password_hint() ;
                    ?></p>
						<p><input type="submit" name="submit" class="button" value="<?php 
                    _e( 'Reset Password', 'quote-press' );
                    ?>"></p>
					</form>

					<?php 
                    return ob_get_clean();
                } else {
                    return '<div class="qpr-notice qpr-notice-error">' . __( 'Invalid password reset link.', 'quote-press' ) . '</div>';
                }
            
            }
        
        }
        
        public function render_account( $attributes, $content = null )
        {
            global  $wp ;
            if ( !is_user_logged_in() ) {
                return '<p>' . __( 'You are not logged in.', 'quote-press' ) . '</p><p>' . '<a href="' . home_url( 'login' ) . '">' . __( 'Login', 'quote-press' ) . '</a>' . ' ' . __( 'or', 'quote-press' ) . ' ' . '<a href="' . home_url( 'register' ) . '">' . __( 'Register', 'quote-press' ) . '</a>' . '</p>';
            }
            $current_user = wp_get_current_user();
            $current_user_id = $current_user->ID;
            ob_start();
            
            if ( isset( $_REQUEST['registered'] ) ) {
                ?>

				<div class="qpr-notice qpr-notice-success">
					<?php 
                printf( __( 'You have successfully registered to <strong>%s</strong>. You are now logged in. Follow the instructions within the email we have just sent to change your password or use the link below. Not received? Check your spam folders.', 'quote-press' ), get_bloginfo( 'name' ) );
                ?>
				</div>

			<?php 
            }
            
            // Place Order
            
            if ( isset( $_REQUEST['place_order'] ) ) {
                $place_order = sanitize_text_field( $_REQUEST['place_order'] );
                // stop people changing the var and ordering someone elses quote
                $quote_customer = (int) get_post_meta( $place_order, '_qpr_user', true );
                if ( $quote_customer !== $current_user_id ) {
                    return;
                }
                // Check post status is sent as there is a small possibility an admin has changed the status inbetween the user viewing the quote and paying
                
                if ( get_post_status( $place_order ) == 'qpr-sent' ) {
                    
                    if ( !isset( $_REQUEST['_wpnonce'] ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'qpr-nonce-place-order' ) ) {
                        echo  '<div class="qpr-notice qpr-notice-error">' . __( 'Sorry, security issue occurred, return to previous page refresh and try again.', 'quote-press' ) . '</div>' ;
                        return;
                    } else {
                        $place_order_payment_option = sanitize_text_field( $_REQUEST['place_order_payment_option'] );
                        // If Bank Transfer or Check
                        
                        if ( $place_order_payment_option == 'bank_transfer' || $place_order_payment_option == 'check' ) {
                            wp_update_post( array(
                                'ID'          => $place_order,
                                'post_status' => 'qpr-paid-unconf',
                            ) );
                            $payment_details = array(
                                'option' => $place_order_payment_option,
                            );
                            update_post_meta( $place_order, '_qpr_payment_details', $payment_details );
                            
                            if ( get_option( 'qpr_customer_notifications' ) == 'on' ) {
                                $to = get_post_meta( $place_order, '_qpr_billing_email', true );
                                $subject = __( 'Quote Payment', 'quote-press' );
                                $body = QPR_Templates::get_email_body( 'paid-unconf-customer' );
                                $body = QPR_Templates::replace_quick_tags( $body, $place_order, $place_order_payment_option );
                                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                                wp_mail(
                                    $to,
                                    $subject,
                                    $body,
                                    $headers
                                );
                            }
                            
                            
                            if ( get_option( 'qpr_paid_unconf_notification' ) == 'on' ) {
                                $to = get_option( 'qpr_notification_email_address' );
                                $subject = __( 'Quote ID:', 'quote-press' ) . ' ' . $place_order . ' ' . __( 'Paid (Unconfirmed)', 'quote-press' );
                                $body = QPR_Templates::get_email_body( 'paid-unconf' );
                                $body = QPR_Templates::replace_quick_tags( $body, $place_order, $place_order_payment_option );
                                $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                                wp_mail(
                                    $to,
                                    $subject,
                                    $body,
                                    $headers
                                );
                            }
                            
                            ?>

							<h2><?php 
                            echo  __( 'Quote', 'quote-press' ) . ' ' . $place_order ;
                            ?></h2>
							<p><?php 
                            _e( 'Thank you for your order, check your email for further information.', 'quote-press' );
                            ?></p>

						<?php 
                        } else {
                        }
                    
                    }
                
                } else {
                    echo  '<div class="qpr-notice qpr-notice-info">' . __( 'The quote you were attempting to pay has just been changed or it has expired and cannot be paid. Wait for the quote to be resent to you or contact us for further information.', 'quote-press' ) . '</div>' ;
                    return;
                }
            
            } elseif ( isset( $_GET['place_order_payment'] ) ) {
                // Place Order Payment
                $place_order_payment = sanitize_text_field( $_GET['place_order_payment'] );
                // order id
                $place_order_payment_option = sanitize_text_field( $_GET['place_order_payment_option'] );
                $place_order_payment_status = sanitize_text_field( $_GET['place_order_payment_status'] );
                $place_order_payment_nonce = sanitize_text_field( $_GET['place_order_payment_nonce'] );
                
                if ( !isset( $_GET['place_order_payment_nonce'] ) || !wp_verify_nonce( $place_order_payment_nonce, 'qpr-nonce-payment' ) ) {
                    echo  '<div class="qpr-notice qpr-notice-error">' . __( 'Sorry, security issue occurred, return to previous page refresh and try again.', 'quote-press' ) . '</div>' ;
                    return;
                } else {
                    
                    if ( $place_order_payment_status == 1 ) {
                        wp_update_post( array(
                            'ID'          => $place_order_payment,
                            'post_status' => 'qpr-paid',
                        ) );
                        $payment_details = array(
                            'option' => $place_order_payment_option,
                        );
                        update_post_meta( $place_order_payment, '_qpr_payment_details', $payment_details );
                        
                        if ( get_option( 'qpr_customer_notifications' ) == 'on' ) {
                            $to = get_post_meta( $place_order_payment, '_qpr_billing_email', true );
                            $subject = __( 'Quote Payment', 'quote-press' );
                            $body = QPR_Templates::get_email_body( 'paid-customer' );
                            $body = QPR_Templates::replace_quick_tags( $body, $place_order_payment, $place_order_payment_option );
                            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                            wp_mail(
                                $to,
                                $subject,
                                $body,
                                $headers
                            );
                        }
                        
                        
                        if ( get_option( 'qpr_paid_unconf_notification' ) == 'on' ) {
                            $to = get_option( 'qpr_notification_email_address' );
                            $subject = __( 'Quote ID:', 'quote-press' ) . ' ' . $place_order_payment . ' ' . __( 'Paid', 'quote-press' );
                            $body = QPR_Templates::get_email_body( 'paid' );
                            $body = QPR_Templates::replace_quick_tags( $body, $place_order_payment, $place_order_payment_option );
                            $headers = array( 'Content-Type: text/html; charset=UTF-8' );
                            wp_mail(
                                $to,
                                $subject,
                                $body,
                                $headers
                            );
                        }
                        
                        ?>

						<h2><?php 
                        echo  __( 'Quote', 'quote-press' ) . ' ' . $place_order_payment ;
                        ?></h2>
						<p><?php 
                        _e( 'Thank you for your order.', 'quote-press' );
                        ?></p>

					<?php 
                    } else {
                        echo  '<div class="qpr-notice qpr-notice-error">' . __( 'Order payment unsuccessful, try again via your account.', 'quote-press' ) . '</div>' ;
                        return;
                    }
                
                }
            
            } elseif ( isset( $_REQUEST['view_quote'] ) ) {
                // View Quote
                $view_quote = sanitize_text_field( $_REQUEST['view_quote'] );
                // Stops changing the query var and viewing other users quote
                $quote_customer = (int) get_post_meta( $view_quote, '_qpr_user', true );
                if ( $quote_customer !== $current_user_id ) {
                    return;
                }
                $quote_status = get_post_status( $view_quote );
                $quote_products = get_post_meta( $view_quote, '_qpr_quote_products', true );
                $quote_notes_customer = get_post_meta( $view_quote, '_qpr_notes_customer', true );
                $quote_valid_until = get_post_meta( $view_quote, '_qpr_valid_until', true );
                $quote_currency = get_post_meta( $view_quote, '_qpr_currency', true );
                
                if ( !empty($quote_valid_until) ) {
                    $quote_valid_until = qpr_date_format( $quote_valid_until );
                } else {
                    $quote_valid_until = __( 'Unconfirmed', 'quote-press' );
                }
                
                ?>

				<h2><?php 
                _e( 'Quote', 'quote-press' );
                ?> <?php 
                echo  $view_quote ;
                ?></h2>
				<?php 
                _e( 'Status:', 'quote-press' );
                ?> <?php 
                echo  qpr_get_quote_statuses( true )[$quote_status] ;
                ?><br>
				<?php 
                echo  __( 'Expiry:', 'quote-press' ) . ' ' . $quote_valid_until ;
                ?><br>
				<?php 
                echo  __( 'Currency:', 'quote-press' ) . ' ' . $quote_currency ;
                ?><br>
				<h2><?php 
                _e( 'Notes', 'quote-press' );
                ?></h2>
				<?php 
                
                if ( !empty($quote_notes_customer) ) {
                    ?>
					<p><?php 
                    echo  $quote_notes_customer ;
                    ?></p>
				<?php 
                } else {
                    ?>
					<p><?php 
                    _e( 'No notes.', 'quote-press' );
                    ?></p>
				<?php 
                }
                
                ?>
				<h2><?php 
                _e( 'Contents', 'quote-press' );
                ?></h2>
				<form class="qpr-place-order-form" method="post">
					<?php 
                wp_nonce_field( 'qpr-nonce-place-order' );
                ?>
					<table method="post">
						<thead>
							<tr>
								<td><?php 
                _e( 'Product', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Variation', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'SKU', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Qty', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Price', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Tax', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Total', 'quote-press' );
                ?></td>
							</tr>
						</thead>
						<tbody>
							<?php 
                foreach ( $quote_products as $quote_product_product_id => $quote_product_variations ) {
                    foreach ( $quote_product_variations as $quote_product_variation_id => $quote_product_variation ) {
                        $variation = '';
                        foreach ( $quote_product_variation['variation'] as $v ) {
                            $variation .= $v['taxonomy'] . ': ' . $v['name'] . '<br>';
                        }
                        ?>
									<tr>
										<td><?php 
                        echo  $quote_product_variation['product'] ;
                        ?></td>
										<td><?php 
                        echo  $variation ;
                        ?></td>
										<td><?php 
                        echo  $quote_product_variation['sku'] ;
                        ?></td>
										<td><?php 
                        echo  $quote_product_variation['qty'] ;
                        ?></td>
										<td><?php 
                        echo  $quote_product_variation['price'] ;
                        ?></td>
										<td><?php 
                        echo  $quote_product_variation['tax'] ;
                        ?></td>
										<td><?php 
                        echo  $quote_product_variation['total'] ;
                        ?></td>
									</tr>
								<?php 
                    }
                }
                ?>
						</tbody>
						<tfoot>
							<tr>
								<td colspan="6"><?php 
                _e( 'Discount (-)', 'quote-press' );
                ?></td>
								<td colspan="1"><?php 
                echo  get_post_meta( $view_quote, '_qpr_grand_totals_discount', true ) ;
                ?></td>
							</tr>
							<tr>
								<td colspan="6"><?php 
                _e( 'Shipping', 'quote-press' );
                ?></td>
								<td colspan="1"><?php 
                echo  get_post_meta( $view_quote, '_qpr_grand_totals_shipping', true ) ;
                ?></td>
							</tr>
							<tr>
								<td colspan="6"><?php 
                _e( 'Tax (Shipping)', 'quote-press' );
                ?></td>
								<td colspan="1"><?php 
                echo  get_post_meta( $view_quote, '_qpr_grand_totals_shipping_tax', true ) ;
                ?></td>
							</tr>
							<tr>
								<td colspan="6"><?php 
                _e( 'Tax (Total)', 'quote-press' );
                ?></td>
								<td colspan="1"><?php 
                echo  get_post_meta( $view_quote, '_qpr_grand_totals_tax', true ) ;
                ?></td>
							</tr>
							<tr>
								<td colspan="6"><?php 
                echo  __( 'Total', 'quote-press' ) . ' ' . __( '(', 'quote-press' ) . qpr_get_currency_symbol( $quote_currency ) . __( ')', 'quote-press' ) ;
                ?></td>
								<td colspan="1"><?php 
                echo  get_post_meta( $view_quote, '_qpr_grand_totals_total', true ) ;
                ?></td>
							</tr>
						</tfoot>
					</table>
					<?php 
                
                if ( $quote_status == 'qpr-sent' && (double) get_post_meta( $view_quote, '_qpr_grand_totals_total', true ) > 0 ) {
                    ?>
						<div id="qpr-payment-methods">
							<h2><?php 
                    _e( 'Payment Method', 'quote-press' );
                    ?></h2>
							<?php 
                    foreach ( QPR_Payments::payment_options() as $payment_option_key => $payment_option_label ) {
                        $payment_option_id = str_replace( '_', '-', $payment_option_key );
                        
                        if ( get_option( 'qpr_payments_' . $payment_option_key ) == 'on' ) {
                            ?>
									<button id="qpr-payment-<?php 
                            echo  $payment_option_id ;
                            ?>" class="qpr-payment" data-opens="qpr-payment-details-<?php 
                            echo  $payment_option_id ;
                            ?>" data-payment-option="<?php 
                            echo  $payment_option_key ;
                            ?>"><?php 
                            echo  $payment_option_label ;
                            ?></button>
								<?php 
                        }
                        
                        ?>
							<?php 
                    }
                    ?>
						</div>
						<?php 
                    foreach ( QPR_Payments::payment_option_settings() as $payment_option_settings_key => $payment_option_settings ) {
                        $payment_option_settings_id = str_replace( '_', '-', $payment_option_settings_key );
                        ?>
							<div id="qpr-payment-details-<?php 
                        echo  $payment_option_settings_id ;
                        ?>" class="qpr-payment-details">
								<?php 
                        foreach ( $payment_option_settings as $payment_option_setting ) {
                            
                            if ( $payment_option_setting['public'] == true ) {
                                $payment_option_setting_key = str_replace( '-', '_', $payment_option_setting['id'] );
                                echo  $payment_option_setting['label'] . ':<br>' . get_option( 'qpr_payments_' . $payment_option_setting_key ) ;
                            }
                        
                        }
                        ?>
							</div>
						<?php 
                    }
                    ?>
						<p><button id="qpr-place-order" name="place_order" value="<?php 
                    echo  $view_quote ;
                    ?>"><?php 
                    _e( 'Place Order', 'quote-press' );
                    ?></button></p>
						<input type="hidden" name="place_order_payment_option">
					<?php 
                }
                
                ?>
				</form>
				<div class="qpr-addresses">
					<h2><?php 
                _e( 'Addresses', 'quote-press' );
                ?></h2>
					<div>
						<strong><?php 
                _e( 'Billing Address', 'quote-press' );
                ?></strong><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_first_name', true ) . ' ' . get_post_meta( $view_quote, '_qpr_billing_last_name', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_company', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_address_line_1', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_address_line_2', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_city', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_state', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_postcode', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_country', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_email', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_billing_phone', true ) ;
                ?>
					</div>
					<div>
						<strong><?php 
                _e( 'Shipping Address', 'quote-press' );
                ?></strong><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_first_name', true ) . ' ' . get_post_meta( $view_quote, '_qpr_shipping_last_name', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_company', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_address_line_1', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_address_line_2', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_city', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_state', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_postcode', true ) ;
                ?><br>
						<?php 
                echo  get_post_meta( $view_quote, '_qpr_shipping_country', true ) ;
                ?><br>
					</div>
				</div>
				<p><a href="<?php 
                echo  home_url( '/account/' ) ;
                ?>" class="button"><?php 
                _e( 'Back', 'quote-press' );
                ?></a></p>

			<?php 
            } else {
                // My Account
                $paged = ( get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1 );
                $quote_statuses = qpr_get_quote_statuses( false );
                $quote_statuses_with_labels = qpr_get_quote_statuses( true );
                $args = array(
                    'meta_query'     => array( array(
                    'key'   => '_qpr_user',
                    'value' => $current_user_id,
                ) ),
                    'post_type'      => 'qpr_quote',
                    'posts_per_page' => 5,
                    'post_status'    => $quote_statuses,
                    'paged'          => $paged,
                );
                $quotes = new WP_Query( $args );
                $args = array(
                    'meta_query'     => array( array(
                    'key'   => '_qpr_user',
                    'value' => $current_user_id,
                ) ),
                    'post_type'      => 'qpr_quote',
                    'posts_per_page' => -1,
                    'post_status'    => 'qpr-sent',
                    'paged'          => $paged,
                );
                $sent_quotes = new WP_Query( $args );
                // Get sent quotes
                $sent_quote_ids = array();
                
                if ( $sent_quotes->have_posts() ) {
                    while ( $sent_quotes->have_posts() ) {
                        $quote = $sent_quotes->the_post();
                        if ( get_post_status() == 'qpr-sent' ) {
                            $sent_quote_ids[] = get_the_id();
                        }
                    }
                    wp_reset_postdata();
                }
                
                ?>
				
				<div class="qpr-account-actions">
					<a href="<?php 
                echo  wp_logout_url() ;
                ?>"><?php 
                _e( 'Logout', 'quote-press' );
                ?></a>
				</div>

				<div class="qpr-account-quotes">
					<h2><?php 
                _e( 'Quotes', 'quote-press' );
                ?></h2>
					<?php 
                if ( !empty($sent_quotes) ) {
                    foreach ( $sent_quote_ids as $sent_quote_id ) {
                        ?>
							<div class="qpr-notice qpr-notice-info"><?php 
                        echo  sprintf( __( 'Quote %s is now available for review/payment. <a href="%s">View Quote</a>.', 'quote-press' ), $sent_quote_id, '?view_quote=' . $sent_quote_id ) ;
                        ?></div>
						<?php 
                    }
                }
                ?>
					<table>
						<thead>
							<tr>
								<td><?php 
                _e( 'ID', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Date Requested', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Valid Until', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Status', 'quote-press' );
                ?></td>
								<td><?php 
                _e( 'Details', 'quote-press' );
                ?></td>
							</tr>
						</thead>
						<tbody>
							<?php 
                
                if ( $quotes->have_posts() ) {
                    while ( $quotes->have_posts() ) {
                        $quotes->the_post();
                        ?>
									<tr>
										<td><?php 
                        echo  get_the_id() ;
                        ?></td>
										<td><?php 
                        echo  get_the_date() ;
                        ?></td>
										<td>
											<?php 
                        $valid_until = get_post_meta( get_the_id(), '_qpr_valid_until', true );
                        if ( !empty($valid_until) ) {
                            echo  qpr_date_format( $valid_until ) ;
                        }
                        ?>		
										</td>
										<td><?php 
                        echo  $quote_statuses_with_labels[get_post_status()] ;
                        ?></td>
										<td><a href="?view_quote=<?php 
                        echo  get_the_id() ;
                        ?>" class="button"><?php 
                        _e( 'View', 'quote-press' );
                        ?></a></td>
									</tr>
								<?php 
                    }
                    wp_reset_postdata();
                } else {
                    ?>
								<tr>
									<td colspan="5"><?php 
                    _e( 'No quotes yet.', 'quote-press' );
                    ?></td>
								</tr>
							<?php 
                }
                
                ?>
						</tbody>
					</table>

					<div><?php 
                next_posts_link( __( 'Older Quotes', 'quote-press' ), $quotes->max_num_pages );
                ?></div>
					<div><?php 
                previous_posts_link( __( 'Next Quotes', 'quote-press' ) );
                ?></div>

				</div>

				<div class="qpr-account-details">

					<h2><?php 
                _e( 'Account Details', 'quote-press' );
                ?></h2>

					<p><?php 
                _e( 'These details are used as the defaults when requesting a new quote. You can still change billing/shipping details to other details when requesting a quote.', 'quote-press' );
                ?></p>
					<p><a href="<?php 
                echo  home_url( '/lost-password/?account_change_password=1' ) ;
                ?>"><?php 
                _e( 'Change Password', 'quote-press' );
                ?></a></p>

					<?php 
                if ( isset( $_POST['account_save_changes'] ) ) {
                    
                    if ( wp_verify_nonce( $_REQUEST['_wpnonce'], 'qpr-nonce-account-details-form' ) ) {
                        // Add user role (so if someone was already a subscriber and have an account then they also get the customer role added)
                        $current_user->add_role( 'qpr_customer' );
                        
                        if ( !empty($_POST['email']) ) {
                            update_user_meta( $current_user_id, 'email', sanitize_email( $_POST['email'] ) );
                            $update_email = wp_update_user( array(
                                'ID'         => $current_user_id,
                                'user_email' => sanitize_email( $_POST['email'] ),
                            ) );
                            if ( is_wp_error( $update_email ) ) {
                                _e( 'Email could not be updated, maybe you already have an account registered with that email address.', 'quote-press' );
                            }
                        }
                        
                        if ( !empty($_POST['phone']) ) {
                            update_user_meta( $current_user_id, 'qpr_phone', sanitize_text_field( $_POST['phone'] ) );
                        }
                        
                        if ( !empty($_POST['first_name']) ) {
                            update_user_meta( $current_user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
                            wp_update_user( array(
                                'ID'         => $current_user_id,
                                'first_name' => sanitize_text_field( $_POST['first_name'] ),
                            ) );
                        }
                        
                        
                        if ( !empty($_POST['last_name']) ) {
                            update_user_meta( $current_user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
                            wp_update_user( array(
                                'ID'        => $current_user_id,
                                'last_name' => sanitize_text_field( $_POST['last_name'] ),
                            ) );
                        }
                        
                        if ( !empty($_POST['company']) ) {
                            update_user_meta( $current_user_id, 'qpr_company', sanitize_text_field( $_POST['company'] ) );
                        }
                        if ( !empty($_POST['address_line_1']) ) {
                            update_user_meta( $current_user_id, 'qpr_address_line_1', sanitize_text_field( $_POST['address_line_1'] ) );
                        }
                        if ( !empty($_POST['address_line_2']) ) {
                            update_user_meta( $current_user_id, 'qpr_address_line_2', sanitize_text_field( $_POST['address_line_2'] ) );
                        }
                        if ( !empty($_POST['city']) ) {
                            update_user_meta( $current_user_id, 'qpr_city', sanitize_text_field( $_POST['city'] ) );
                        }
                        if ( !empty($_POST['state']) ) {
                            update_user_meta( $current_user_id, 'qpr_state', sanitize_text_field( $_POST['state'] ) );
                        }
                        if ( !empty($_POST['postcode']) ) {
                            update_user_meta( $current_user_id, 'qpr_postcode', sanitize_text_field( $_POST['postcode'] ) );
                        }
                        if ( !empty($_POST['country']) ) {
                            update_user_meta( $current_user_id, 'qpr_country', sanitize_text_field( $_POST['country'] ) );
                        }
                        echo  '<div class="qpr-notice qpr-notice-success">' . __( 'Updated', 'quote-press' ) . '</div>' ;
                    }
                
                }
                $countries = qpr_get_countries();
                ?>

					<form class="qpr-account-details-form" method="post">
						<?php 
                wp_nonce_field( 'qpr-nonce-account-details-form' );
                ?>
						<div>
							<label for="qpr-email"><?php 
                _e( 'Email', 'quote-press' );
                ?></label><input id="qpr-email" type="email" name="email" value="<?php 
                echo  $current_user->user_email ;
                ?>" required>
							<p class="description"><?php 
                _e( 'You will need to confirm this new email address before it becomes active.', 'quote-press' );
                ?></p>
						</div>
						<div>
							<label for="qpr-phone"><?php 
                _e( 'Phone', 'quote-press' );
                ?></label><input id="qpr-phone" type="text" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_phone', true ) ;
                ?>" name="phone" required>
						</div>
						<div>
							<label for="qpr-first-name"><?php 
                _e( 'First name', 'quote-press' );
                ?></label><input id="qpr-first-name" type="text" name="first_name" value="<?php 
                echo  get_user_meta( $current_user_id, 'first_name', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-last-name"><?php 
                _e( 'Last name', 'quote-press' );
                ?></label><input id="qpr-last-name" type="text" name="last_name" value="<?php 
                echo  get_user_meta( $current_user_id, 'last_name', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-company"><?php 
                _e( 'Company', 'quote-press' );
                ?></label><input id="qpr-company" type="text" name="company" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_company', true ) ;
                ?>" >
						</div>
						<div>
							<label for="qpr-address-line-1"><?php 
                _e( 'Address Line 1', 'quote-press' );
                ?></label><input id="qpr-address-line-1" type="text" name="address_line_1" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_address_line_1', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-address-line-2"><?php 
                _e( 'Address Line 2', 'quote-press' );
                ?></label><input id="qpr-address-line-2" type="text" name="address_line_2" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_address_line_2', true ) ;
                ?>" >
						</div>
						<div>
							<label for="qpr-city"><?php 
                _e( 'City', 'quote-press' );
                ?></label><input id="qpr-city" type="text" name="city" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_city', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-state"><?php 
                _e( 'State', 'quote-press' );
                ?></label><input id="qpr-state" type="text" name="state" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_state', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-postcode"><?php 
                _e( 'Postcode/Zip', 'quote-press' );
                ?></label><input id="qpr-postcode" type="text" name="postcode" value="<?php 
                echo  get_user_meta( $current_user_id, 'qpr_postcode', true ) ;
                ?>" required>
						</div>
						<div>
							<label for="qpr-country"><?php 
                _e( 'Country:', 'quote-press' );
                ?></label>
							<select id="qpr-country" name="country" required>
								<?php 
                foreach ( $countries as $country ) {
                    ?>
									<option value="<?php 
                    echo  $country['abbreviation'] ;
                    ?>"<?php 
                    echo  ( get_user_meta( $current_user_id, 'qpr_country', true ) == $country['abbreviation'] ? ' selected' : '' ) ;
                    ?>><?php 
                    echo  $country['country'] ;
                    ?> (<?php 
                    echo  $country['abbreviation'] ;
                    ?>)</option>
								<?php 
                }
                ?>
							</select>
						</div>
						<p><input type="submit" name="account_save_changes" class="button" value="<?php 
                _e( 'Save Changes', 'quote-press' );
                ?>"></p>
					</form>

				</div>

			<?php 
            }
            
            return ob_get_clean();
        }
        
        private function get_error_message( $error_code )
        {
            switch ( $error_code ) {
                // Login errors
                case 'empty_username':
                    return __( 'You do have an email address, right?', 'quote-press' );
                case 'empty_password':
                    return __( 'You need to enter a password to login.', 'quote-press' );
                case 'invalid_username':
                    return __( "We don't have any users with that email address. Maybe you used a different one when signing up?", 'quote-press' );
                case 'incorrect_password':
                    $err = __( 'The password you entered wasn\'t quite right. <a href="%s">Did you forget your password</a>?', 'quote-press' );
                    return sprintf( $err, wp_lostpassword_url() );
                    // Registration errors
                // Registration errors
                case 'email':
                    return __( 'The email address you entered is not valid.', 'quote-press' );
                case 'email_exists':
                    return __( 'An account exists with this email address.', 'quote-press' );
                case 'closed':
                    return __( 'Registering new users is currently not allowed or the link you followed has expired.', 'quote-press' );
                    // Lost password
                // Lost password
                case 'empty_username':
                    return __( 'You need to enter your email address to continue.', 'quote-press' );
                case 'invalid_email':
                case 'invalidcombo':
                    return __( 'There are no users registered with this email address.', 'quote-press' );
                    // Reset password
                // Reset password
                case 'expiredkey':
                case 'invalidkey':
                    return __( 'The password reset link you used is not valid anymore.', 'quote-press' );
                case 'password_reset_mismatch':
                    return __( "The two passwords you entered don't match.", 'quote-press' );
                case 'password_reset_empty':
                    return __( "Sorry, we don't accept empty passwords.", 'quote-press' );
                default:
                    break;
            }
            return __( 'An unknown error occurred. Please try again later.', 'quote-press' );
        }
        
        public function redirect_to_frontend_login()
        {
            
            if ( $GLOBALS['pagenow'] === 'wp-login.php' ) {
                return;
                // if accessing /wp-admin or wordpress's direct login URL use the normal login page
            }
            
            
            if ( $_SERVER['REQUEST_METHOD'] == 'GET' ) {
                
                if ( is_user_logged_in() ) {
                    $this->redirect_logged_in_user();
                    exit;
                }
                
                $login_url = home_url( 'login' );
                
                if ( !empty($_REQUEST['redirect_to']) ) {
                    $redirect_to = sanitize_text_field( $_REQUEST['redirect_to'] );
                    $login_url = add_query_arg( 'redirect_to', $redirect_to, $login_url );
                }
                
                
                if ( !empty($_REQUEST['check_email']) ) {
                    $check_email = sanitize_text_field( $_REQUEST['check_email'] );
                    $login_url = add_query_arg( 'check_email', $check_email, $login_url );
                }
                
                wp_redirect( $login_url );
                exit;
            }
        
        }
        
        public function redirect_to_frontend_login_at_authenticate( $user, $username, $password )
        {
            if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
                
                if ( is_wp_error( $user ) ) {
                    $error_codes = join( ',', $user->get_error_codes() );
                    $login_url = home_url( 'login' );
                    $login_url = add_query_arg( 'login', $error_codes, $login_url );
                    wp_redirect( $login_url );
                    exit;
                }
            
            }
            return $user;
        }
        
        public function redirect_after_login( $redirect_to, $requested_redirect_to, $user )
        {
            $redirect_url = home_url();
            if ( !isset( $user->ID ) ) {
                return $redirect_url;
            }
            
            if ( user_can( $user, 'manage_options' ) ) {
                
                if ( $requested_redirect_to == '' ) {
                    $redirect_url = admin_url();
                } else {
                    $redirect_url = $redirect_to;
                }
            
            } else {
                $redirect_url = home_url( 'account' );
            }
            
            return wp_validate_redirect( $redirect_url, home_url() );
        }
        
        public function redirect_after_logout()
        {
            
            if ( $_GET['account_change_password'] !== '1' ) {
                wp_redirect( home_url( 'login?logged_out=true' ) );
            } else {
                wp_redirect( home_url( 'lost-password?logged_out=true' ) );
            }
            
            exit;
        }
        
        public function redirect_to_frontend_register()
        {
            
            if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
                
                if ( is_user_logged_in() ) {
                    $this->redirect_logged_in_user();
                } else {
                    wp_redirect( home_url( 'register' ) );
                }
                
                exit;
            }
        
        }
        
        public function redirect_to_frontend_lost_password()
        {
            
            if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
                
                if ( is_user_logged_in() ) {
                    $this->redirect_logged_in_user();
                    exit;
                }
                
                wp_redirect( home_url( 'lost-password' ) );
                exit;
            }
        
        }
        
        public function redirect_to_frontend_password_reset()
        {
            
            if ( 'GET' == $_SERVER['REQUEST_METHOD'] ) {
                $key = sanitize_text_field( $_REQUEST['key'] );
                $login = sanitize_text_field( $_REQUEST['login'] );
                $user = check_password_reset_key( $key, $login );
                
                if ( !$user || is_wp_error( $user ) ) {
                    
                    if ( $user && $user->get_error_code() === 'expired_key' ) {
                        wp_redirect( home_url( 'login?login=expiredkey' ) );
                    } else {
                        wp_redirect( home_url( 'login?login=invalidkey' ) );
                    }
                    
                    exit;
                }
                
                $redirect_url = home_url( 'password-reset' );
                $redirect_url = add_query_arg( 'login', esc_attr( $login ), $redirect_url );
                $redirect_url = add_query_arg( 'key', esc_attr( $key ), $redirect_url );
                wp_redirect( $redirect_url );
                exit;
            }
        
        }
        
        public function register_user()
        {
            if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                
                if ( isset( $_POST['qpr_register'] ) ) {
                    $redirect_url = home_url( 'register' );
                    
                    if ( !get_option( 'users_can_register' ) || !wp_verify_nonce( $_REQUEST['_wpnonce'], 'qpr-register-form' ) ) {
                        $redirect_url = add_query_arg( 'register-errors', 'closed', $redirect_url );
                    } else {
                        $email = sanitize_email( $_POST['email'] );
                        $phone = sanitize_text_field( $_POST['phone'] );
                        $first_name = sanitize_text_field( $_POST['first_name'] );
                        $last_name = sanitize_text_field( $_POST['last_name'] );
                        $company = sanitize_text_field( $_POST['company'] );
                        $address_line_1 = sanitize_text_field( $_POST['address_line_1'] );
                        $address_line_2 = sanitize_text_field( $_POST['address_line_2'] );
                        $city = sanitize_text_field( $_POST['city'] );
                        $state = sanitize_text_field( $_POST['state'] );
                        $postcode = sanitize_text_field( $_POST['postcode'] );
                        $country = sanitize_text_field( $_POST['country'] );
                        $errors = new WP_Error();
                        // Email address used as both username and email
                        
                        if ( !is_email( $email ) ) {
                            $errors->add( 'email', $this->get_error_message( 'email' ) );
                            $result = $errors;
                        }
                        
                        
                        if ( username_exists( $email ) || email_exists( $email ) ) {
                            $errors->add( 'email_exists', $this->get_error_message( 'email_exists' ) );
                            $result = $errors;
                        }
                        
                        // Generate the password so that the subscriber will have to check email...
                        $password = wp_generate_password( 12, false );
                        $user_data = array(
                            'user_login' => $email,
                            'user_email' => $email,
                            'user_pass'  => $password,
                            'first_name' => $first_name,
                            'last_name'  => $last_name,
                            'nickname'   => $first_name,
                            'role'       => 'qpr_customer',
                        );
                        $user_id = wp_insert_user( $user_data );
                        add_user_meta( $user_id, 'qpr_phone', $phone );
                        add_user_meta( $user_id, 'qpr_company', $company );
                        add_user_meta( $user_id, 'qpr_address_line_1', $address_line_1 );
                        add_user_meta( $user_id, 'qpr_address_line_2', $address_line_2 );
                        add_user_meta( $user_id, 'qpr_city', $city );
                        add_user_meta( $user_id, 'qpr_state', $state );
                        add_user_meta( $user_id, 'qpr_postcode', $postcode );
                        add_user_meta( $user_id, 'qpr_country', $country );
                        wp_new_user_notification( $user_id, $password );
                        
                        if ( is_wp_error( $result ) ) {
                            $errors = join( ',', $result->get_error_codes() );
                            $redirect_url = add_query_arg( 'register-errors', $errors, $redirect_url );
                        } else {
                            $redirect_url = home_url( 'account' );
                            $redirect_url = add_query_arg( 'registered', $email, $redirect_url );
                            wp_set_auth_cookie( $user_id );
                            // Login the user
                        }
                    
                    }
                    
                    wp_redirect( $redirect_url );
                    exit;
                }
            
            }
        }
        
        public function lost_password()
        {
            if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                if ( isset( $_POST['qpr_lost_password'] ) ) {
                    
                    if ( isset( $_REQUEST['_wpnonce'] ) || wp_verify_nonce( $_REQUEST['_wpnonce'], 'qpr-nonce-lost-password-form' ) ) {
                        $errors = retrieve_password();
                        
                        if ( is_wp_error( $errors ) ) {
                            // Errors found
                            $redirect_url = home_url( 'lost-password' );
                            $redirect_url = add_query_arg( 'errors', join( ',', $errors->get_error_codes() ), $redirect_url );
                        } else {
                            // Email sent
                            $redirect_url = home_url( 'login' );
                            $redirect_url = add_query_arg( 'check_email', 'confirm', $redirect_url );
                            if ( !empty($_REQUEST['redirect_to']) ) {
                                $redirect_url = sanitize_text_field( $_REQUEST['redirect_to'] );
                            }
                        }
                        
                        wp_safe_redirect( $redirect_url );
                        exit;
                    }
                
                }
            }
        }
        
        public function password_reset()
        {
            if ( 'POST' == $_SERVER['REQUEST_METHOD'] ) {
                if ( isset( $_POST['qpr_password_reset'] ) ) {
                    
                    if ( isset( $_REQUEST['_wpnonce'] ) || wp_verify_nonce( $_REQUEST['_wpnonce'], 'qpr-nonce-password-reset-form' ) ) {
                        $password_reset_key = sanitize_text_field( $_REQUEST['password_reset_key'] );
                        $password_reset_login = sanitize_text_field( $_REQUEST['password_reset_login'] );
                        $user = check_password_reset_key( $password_reset_key, $password_reset_login );
                        
                        if ( !$user || is_wp_error( $user ) ) {
                            
                            if ( $user && $user->get_error_code() === 'expired_key' ) {
                                wp_redirect( home_url( 'login?login=expiredkey' ) );
                            } else {
                                wp_redirect( home_url( 'login?login=invalidkey' ) );
                            }
                            
                            exit;
                        }
                        
                        
                        if ( isset( $_POST['pass1'] ) ) {
                            
                            if ( $_POST['pass1'] != $_POST['pass2'] ) {
                                $redirect_url = home_url( 'password-reset' );
                                $redirect_url = add_query_arg( 'key', $password_reset_key, $redirect_url );
                                $redirect_url = add_query_arg( 'login', $password_reset_login, $redirect_url );
                                $redirect_url = add_query_arg( 'error', 'password_reset_mismatch', $redirect_url );
                                wp_redirect( $redirect_url );
                                exit;
                            }
                            
                            
                            if ( empty($_POST['pass1']) ) {
                                // Password is empty
                                $redirect_url = home_url( 'password-reset' );
                                $redirect_url = add_query_arg( 'key', $password_reset_key, $redirect_url );
                                $redirect_url = add_query_arg( 'login', $password_reset_login, $redirect_url );
                                $redirect_url = add_query_arg( 'error', 'password_reset_empty', $redirect_url );
                                wp_redirect( $redirect_url );
                                exit;
                            }
                            
                            // Parameter checks OK, reset password
                            reset_password( $user, $_POST['pass1'] );
                            wp_redirect( home_url( 'login?password=changed' ) );
                        } else {
                            _e( 'Invalid request.', 'quote-press' );
                        }
                        
                        exit;
                    }
                
                }
            }
        }
        
        private function redirect_logged_in_user( $redirect_to = null )
        {
            $user = wp_get_current_user();
            
            if ( user_can( $user, 'manage_options' ) ) {
                
                if ( $redirect_to ) {
                    wp_safe_redirect( $redirect_to );
                } else {
                    wp_redirect( admin_url() );
                }
            
            } else {
                wp_redirect( home_url( 'account' ) );
            }
        
        }
        
        public function customer_fields( $user )
        {
            
            if ( in_array( 'qpr_customer', $user->roles ) ) {
                $countries = qpr_get_countries();
                ?>

				<h3><?php 
                _e( 'Customer Data', 'quote-press' );
                ?></h3>
				<table class="form-table">
					<tr>
						<th>
							<label for="qpr-phone"><?php 
                _e( 'Phone' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_phone" id="qpr-phone" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_phone', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-company"><?php 
                _e( 'Company' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_company" id="qpr-company" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_company', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-address_line_1"><?php 
                _e( 'Address Line 1' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_address_line_1" id="qpr-address_line_1" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_address_line_1', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-address_line_2"><?php 
                _e( 'Address Line 2' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_address_line_2" id="qpr-address_line_2" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_address_line_2', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-city"><?php 
                _e( 'City' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_city" id="qpr-city" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_city', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-state"><?php 
                _e( 'State' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_state" id="qpr-state" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_state', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-postcode"><?php 
                _e( 'Postcode/Zip' );
                ?></label>
						</th>
						<td>
							<input type="text" name="qpr_postcode" id="qpr-postcode" value="<?php 
                echo  esc_attr( get_the_author_meta( 'qpr_postcode', $user->ID ) ) ;
                ?>" class="regular-text" /><br />
						</td>
					</tr>
					<tr>
						<th>
							<label for="qpr-country"><?php 
                _e( 'Country' );
                ?></label>
						</th>
						<td>
							<select id="qpr-country" name="qpr_country" required>
								<?php 
                foreach ( $countries as $country ) {
                    ?>
									<option value="<?php 
                    echo  $country['abbreviation'] ;
                    ?>"<?php 
                    echo  ( esc_attr( get_the_author_meta( 'qpr_country', $user->ID ) ) == $country['abbreviation'] ? ' selected' : '' ) ;
                    ?>><?php 
                    echo  $country['country'] ;
                    ?> (<?php 
                    echo  $country['abbreviation'] ;
                    ?>)</option>
								<?php 
                }
                ?>
							</select>
						</td>
					</tr>
				</table>

			<?php 
            }
        
        }
        
        public function customer_fields_save( $user_id )
        {
            if ( !current_user_can( 'edit_user', $user_id ) ) {
                return false;
            }
            update_user_meta( $user_id, 'qpr_phone', sanitize_text_field( $_POST['qpr_phone'] ) );
            update_user_meta( $user_id, 'qpr_company', sanitize_text_field( $_POST['qpr_company'] ) );
            update_user_meta( $user_id, 'qpr_address_line_1', sanitize_text_field( $_POST['qpr_address_line_1'] ) );
            update_user_meta( $user_id, 'qpr_address_line_2', sanitize_text_field( $_POST['qpr_address_line_2'] ) );
            update_user_meta( $user_id, 'qpr_city', sanitize_text_field( $_POST['qpr_city'] ) );
            update_user_meta( $user_id, 'qpr_state', sanitize_text_field( $_POST['qpr_state'] ) );
            update_user_meta( $user_id, 'qpr_postcode', sanitize_text_field( $_POST['qpr_postcode'] ) );
            update_user_meta( $user_id, 'qpr_country', sanitize_text_field( $_POST['qpr_country'] ) );
        }
    
    }
}