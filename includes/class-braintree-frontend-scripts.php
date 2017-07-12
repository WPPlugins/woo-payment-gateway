<?php
class Braintree_Gateway_Frontend_Scripts
{
	public static $scripts = array ();
	public static $styles = array ();
	public static $localized_scripts = array ();
	public static $localized_client_token_scripts = array ();
	public static $prefix = 'braintree-';

	public static function init()
	{
		if ( is_admin() ) {
			return;
		}
		
		add_action( 'init', __CLASS__ . '::init_scripts' );
		
		add_action( 'wp_enqueue_scripts', __CLASS__ . '::load_scripts' );
		
		add_action( 'wp_print_scripts', __CLASS__ . '::localize_scripts', 5 );
		
		add_action( 'wp_print_footer_scripts', __CLASS__ . '::localize_scripts', 5 );
		
		add_action( 'braintree_before_localize_frontend_scripts', __CLASS__ . '::initialize_cart_totals' );
	}

	public static function init_scripts()
	{
		/**
		 *
		 * @since 2.6.7
		 */
		$version = bwc_dropin_v3_enabled() ? '3.11.1' : '3.17.0';
		
		define( 'BRAINTREE_JS_DROPIN', 'https://js.braintreegateway.com/js/braintree-2.30.0.min.js' );
		define( 'BRAINTREE_JS_V3_CLIENT', 'https://js.braintreegateway.com/web/' . $version . '/js/client.min.js' );
		define( 'BRAINTREE_JS_V3_HOSTED', 'https://js.braintreegateway.com/web/' . $version . '/js/hosted-fields.min.js' );
		define( 'BRAINTREE_PAYPAL_JS', 'https://js.braintreegateway.com/web/' . $version . '/js/paypal.min.js' );
		define( 'BRAINTREE_V3_DATA_COLLECTOR', 'https://js.braintreegateway.com/web/' . $version . '/js/data-collector.min.js' );
		define( 'BRAINTREE_V3_UNIONPAY', 'https://js.braintreegateway.com/web/' . $version . '/js/unionpay.min.js' );
		define( 'BRAINTREE_V3_APPLEPAY', 'https://js.braintreegateway.com/web/' . $version . '/js/apple-pay.min.js' );
		define( 'BRAINTREE_JS_V3_3DS', 'https://js.braintreegateway.com/web/' . $version . '/js/three-d-secure.min.js' );
		define( 'BRAINTREE_V3_DROPIN', 'https://js.braintreegateway.com/web/dropin/1.0.0-beta.6/js/dropin.min.js' );
	}

	public static function load_scripts()
	{
		// ensure WooCommerce has been activated.
		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		
		$js_path_frontend = bt_manager()->plugin_assets_path() . 'js/frontend/';
		$js_path = bt_manager()->plugin_assets_path() . 'js/';
		$css_path = bt_manager()->plugin_assets_path() . 'css/';
		$version = bt_manager()->version;
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || bt_manager()->is_active( 'enable_script_debug' ) ? '' : '.min';
		
		self::register_script( self::$prefix . 'dropin-v3-external', BRAINTREE_V3_DROPIN, array (
				self::$prefix . 'client' 
		), $version );
		
		self::register_script( self::$prefix . 'dropin-v2-external', BRAINTREE_JS_DROPIN, array (), $version );
		
		self::register_script( self::$prefix . 'client', BRAINTREE_JS_V3_CLIENT, array (), $version );
		
		self::register_script( self::$prefix . 'hosted', BRAINTREE_JS_V3_HOSTED, array (), $version );
		
		self::register_script( self::$prefix . '3ds-js', BRAINTREE_JS_V3_3DS, array (), $version );
		
		self::register_script( self::$prefix . 'paypal-external', BRAINTREE_PAYPAL_JS, array (), $version );
		
		self::register_script( self::$prefix . 'data-collector', BRAINTREE_V3_DATA_COLLECTOR, array (), $version );
		
		self::register_script( self::$prefix . 'union-pay', BRAINTREE_V3_UNIONPAY, array (), $version );
		
		self::register_script( self::$prefix . 'applepay-external', BRAINTREE_V3_APPLEPAY, array (), $version );
		
		self::register_script( self::$prefix . 'change-payment-method', $js_path_frontend . 'change-payment-method' . $suffix . '.js', array (
				'jquery' 
		), $version );
		
		self::register_script( self::$prefix . 'payment-methods', $js_path_frontend . 'braintree-payment-methods' . $suffix . '.js', array (
				'jquery' 
		), $version );
		
		self::register_script( self::$prefix . 'message-handler', $js_path_frontend . 'message-handler' . $suffix . '.js', array (
				'jquery' 
		), $version );
		
		self::register_script( self::$prefix . 'form-handler', $js_path_frontend . 'form-handler' . $suffix . '.js', array (
				'jquery' 
		), $version );
		
		self::register_script( self::$prefix . 'fees', $js_path_frontend . 'checkout-fees' . $suffix . '.js', array (
				'jquery' 
		), $version );
		
		$form = bwc_get_custom_form();
		self::register_script( self::$prefix . 'custom-js', $form [ 'javascript' ], array (
				self::$prefix . 'hosted-fields' 
		), $version );
		
		self::register_style( self::$prefix . 'custom-css', $form [ 'css' ], array (), $version, null );
		
		if ( ! empty( $form [ 'external_css' ] ) ) {
			self::register_style( self::$prefix . 'custom-external-css', $form [ 'external_css' ], array (), $version, null );
		}
		
		$credit_button = bwc_get_paypal_credit_button();
		$paypal_button = bwc_get_paypal_button();
		
		if ( ! empty( $credit_button [ 'css' ] ) ) {
			self::register_style( self::$prefix . 'paypal-credit-button-css', $credit_button [ 'css' ], array (), $version );
		}
		self::register_style( self::$prefix . 'paypal-css', $paypal_button [ 'css' ], array (), $version );
		
		self::register_style( self::$prefix . 'paypal-credit-css', $css_path . 'paypal/paypal-credit.css', array (), $version );
		
		self::register_style( self::$prefix . 'styles', $css_path . 'braintree.css', array (), $version );
		
		self::register_style( self::$prefix . 'payment-methods', $css_path . 'payment-methods.css', array (), $version );
		
		self::register_script( self::$prefix . 'hosted-fields', $js_path_frontend . 'hosted-fields' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'client', 
				self::$prefix . 'hosted', 
				self::$prefix . 'data-collector' 
		), $version );
		
		self::register_script( self::$prefix . 'dropin-v3', $js_path_frontend . 'dropin-v3' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'client', 
				self::$prefix . 'dropin-v3-external', 
				self::$prefix . 'data-collector' 
		), $version );
		
		self::register_script( self::$prefix . 'dropin', $js_path_frontend . 'dropin' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'dropin-v2-external' 
		), $version );
		
		self::register_script( self::$prefix . 'paypal', $js_path_frontend . 'paypal' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'client', 
				self::$prefix . 'paypal-external', 
				self::$prefix . 'data-collector' 
		), $version );
		
		self::register_script( self::$prefix . 'paypal-credit', $js_path_frontend . 'paypal-credit' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'client', 
				self::$prefix . 'paypal-external', 
				self::$prefix . 'data-collector' 
		), $version );
		
		self::register_script( self::$prefix . 'applepay', $js_path_frontend . 'applepay' . $suffix . '.js', array (
				'jquery', 
				self::$prefix . 'client', 
				self::$prefix . 'applepay-external' 
		), $version );
		
		self::enqueue_scripts_for_page();
	}

	public static function enqueue_scripts_for_page()
	{
		if ( bwc_is_checkout() || is_checkout_pay_page() || is_add_payment_method_page() || bfwcs_is_change_payment_method() || bfwcs_is_pay_for_subscription_request() ) {
			wp_enqueue_style( self::$prefix . 'styles' );
			wp_enqueue_script( self::$prefix . 'payment-methods' );
			wp_enqueue_script( self::$prefix . 'message-handler' );
			wp_enqueue_script( self::$prefix . 'form-handler' );
			
			if ( bwc_is_custom_form() && bwc_card_payments_enabled() ) {
				wp_enqueue_script( self::$prefix . 'hosted-fields' );
				if ( bwc_is_3ds_enabled() ) {
					wp_enqueue_script( self::$prefix . '3ds-js' );
				}
				wp_enqueue_script( self::$prefix . 'custom-js' );
				wp_enqueue_style( self::$prefix . 'custom-css' );
				wp_enqueue_style( self::$prefix . 'custom-external-css' );
			} elseif ( bwc_dropin_v3_enabled() && bwc_card_payments_enabled() ) {
				wp_enqueue_script( self::$prefix . 'dropin-v3' );
				if ( bwc_is_3ds_enabled() ) {
					wp_enqueue_script( self::$prefix . '3ds-js' );
				}
			} elseif ( bwc_dropin_v2_enabled() && bwc_card_payments_enabled() ) {
				wp_enqueue_script( self::$prefix . 'dropin' );
			}
			if ( bwc_is_paypal_enabled() ) {
				
				wp_enqueue_script( self::$prefix . 'paypal' );
				wp_enqueue_style( self::$prefix . 'paypal-css' );
			}
			if ( bwc_paypal_credit_enabled() ) {
				wp_enqueue_script( self::$prefix . 'paypal-credit' );
				if ( wp_style_is( self::$prefix . 'paypal-credit-css' ) ) {
					wp_enqueue_style( self::$prefix . 'paypal-credit-button-css' );
				}
				wp_enqueue_style( self::$prefix . 'paypal-credit-css' );
			}
			if ( bwc_is_applepay_enabled() ) {
				wp_enqueue_script( self::$prefix . 'applepay' );
			}
			if ( bwc_fees_enabled() ) {
				wp_enqueue_script( self::$prefix . 'fees' );
			}
		}
		if ( bfwcs_is_change_payment_method() || bfwcs_is_pay_for_subscription_request() ) {
			wp_enqueue_script( self::$prefix . 'change-payment-method' );
		}
		if ( is_account_page() ) {
			wp_enqueue_style( self::$prefix . 'payment-methods' );
		}
	}

	/**
	 * Register and enqueue a script.
	 *
	 * @param string $handle        	
	 * @param string $src        	
	 * @param array $deps        	
	 * @param string $version        	
	 * @param bool $in_footer        	
	 */
	public static function enqueue_script( $handle, $src = '', $deps = array(), $version = '', $in_footer = true )
	{
		if ( ! in_array( $handle, self::$scripts ) ) {
			self::register_script( $handle, $src, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle, $src, $deps, $version, $in_footer );
	}

	/**
	 * Register a script for enqueue later.
	 *
	 * @param string $handle        	
	 * @param string $src        	
	 * @param array $deps        	
	 * @param string $version        	
	 * @param bool $in_footer        	
	 */
	public static function register_script( $handle, $src, $deps = array(), $version, $in_footer = true )
	{
		self::$scripts [] = $handle;
		
		wp_register_script( $handle, $src, $deps, $version, $in_footer );
	}

	/**
	 * Register a style sheet for enquement later.
	 *
	 * @param string $handle        	
	 * @param string $src        	
	 * @param array $deps        	
	 * @param string $version        	
	 * @param string $media        	
	 */
	public static function register_style( $handle, $src, $deps = array(), $version, $media = null )
	{
		if ( ! in_array( $handle, self::$styles ) ) {
			self::$styles [] = $handle;
		}
		
		wp_register_style( $handle, $src, $deps, $version, $media );
	}

	/**
	 * Enqueue a style sheet.
	 *
	 * @param string $handle        	
	 * @param string $src        	
	 * @param array $deps        	
	 * @param string $version        	
	 * @param string $media        	
	 */
	public static function enqueue_style( $handle, $src, $deps = array(), $version = '', $media = null )
	{
		if ( ! in_array( $handle, self::$styles ) ) {
			self::register_style( $handle, $src, $deps, $version, $media = null );
		}
		
		wp_enqueue_style( $handle, $src, $deps, $version, $media );
	}

	public static function localize_scripts()
	{
		// ensure WooCommerce has been activated.
		if ( ! did_action( 'before_woocommerce_init' ) ) {
			return;
		}
		
		do_action( 'braintree_before_localize_frontend_scripts' );
		
		foreach ( self::$scripts as $handle ) {
			if ( ! in_array( $handle, self::$localized_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_data( $handle ) ) ) {
				
				self::$localized_scripts [] = $handle;
				
				$object_name = str_replace( '-', '_', $handle ) . '_vars';
				
				wp_localize_script( $handle, $object_name, $data );
			}
		}
		
		// generate client tokens;
		foreach ( self::$scripts as $handle ) {
			$data = null;
			if ( ! in_array( $handle, self::$localized_client_token_scripts ) && wp_script_is( $handle ) && ( $data = self::get_script_client_token( $handle ) ) ) {
				
				self::$localized_client_token_scripts [] = $handle;
				
				$object_name = str_replace( '-', '_', $handle ) . '_client_token';
				
				wp_localize_script( $handle, $object_name, $data );
			}
		}
	}

	public static function get_script_data( $handle )
	{
		switch( $handle ) {
			case self::$prefix . 'dropin' :
			case self::$prefix . 'dropin-v3' :
			case self::$prefix . 'hosted-fields' :
			case self::$prefix . 'paypal' :
			case self::$prefix . 'paypal-credit' :
			case self::$prefix . 'applepay' :
				
				$data = array (
						'order_total' => WC()->cart->total, 
						'_3ds' => array (
								'enabled' => bwc_is_3ds_enabled(), 
								'active' => bwc_is_3ds_active(), 
								'verify_vault' => bwc_3ds_verify_vaulted_methods() 
						), 
						'advanced_fraud' => array (
								'enabled' => bwc_is_advanced_fraud_tools() 
						), 
						'environment' => bt_manager()->get_environment(), 
						'wc_ajax_url' => Braintree_Gateway_WC_Ajax::get_endpoint( '%%endpoint%%' ), 
						'update_checkout_nonce' => wp_create_nonce( 'update-checkout-vars' ), 
						'payment_method_nonce' => wp_create_nonce( 'payment-method-nonce' ), 
						'page' => array (
								'checkout_page' => bwc_is_checkout(), 
								'change_payment_page' => bwcs_is_change_payment_method() 
						) 
				);
				
				if ( $handle === self::$prefix . 'hosted-fields' ) {
					$data [ 'gateway_id' ] = bwc_get_gateway_id( 'braintree' );
					$data [ 'custom_fields' ] = bwc_get_custom_form_fields();
					$data [ 'dynamic_card_display' ] = array (
							'enabled' => bwc_is_dynamic_card_display(), 
							'icon_style' => bwc_payment_icons_type() 
					);
					$data [ 'form_styles' ] = json_decode( bt_manager()->get_option( 'custom_form_styles' ), true );
					$data [ 'loader' ] = array (
							'enabled' => bt_manager()->is_active( 'enable_loader' ), 
							'css' => json_decode( bt_manager()->get_option( 'custom_form_loader_css' ), true ), 
							'message' => bt_manager()->get_option( 'custom_form_loader_message' ) 
					);
					if ( bwc_is_3ds_enabled() ) {
						$data [ '_3ds' ] [ 'modal_html' ] = bwc_get_3ds_modal_html();
					}
				}
				if ( $handle === self::$prefix . 'dropin-v3' || $handle === self::$prefix . 'dropin' ) {
					$data [ 'gateway_id' ] = bwc_get_gateway_id( 'braintree' );
					if ( bwc_is_3ds_enabled() ) {
						$data [ '_3ds' ] [ 'modal_html' ] = bwc_get_3ds_modal_html();
					}
					$data [ 'locale' ] = get_locale();
				}
				if ( $handle === self::$prefix . 'paypal' ) {
					$data [ 'order_button_text' ] = __( 'Checkout With PayPal', 'braintree-payments' );
					$data [ 'gateway_id' ] = bwc_get_gateway_id( 'paypal' );
					$data [ 'html' ] = bwc_get_paypal_html();
					$data [ 'form' ] [ 'submit' ] = bt_manager()->is_active( 'paypal_submit_form' );
					if ( bwc_paypal_checkout_flow() && is_checkout() ) {
						$data [ 'options' ] = array (
								'flow' => 'checkout', 
								'currency' => get_woocommerce_currency(), 
								'displayName' => bt_manager()->get_option( 'paypal_display_name' ), 
								'enableShippingAddress' => WC()->cart->needs_shipping() && bwc_paypal_send_shipping(), 
								'shippingAddressEditable' => WC()->cart->needs_shipping() && bwc_paypal_credit_send_shipping(), 
								'offerCredit' => false 
						);
					} else {
						$data [ 'options' ] = array (
								'flow' => 'vault', 
								'displayName' => bt_manager()->get_option( 'paypal_display_name' ) 
						);
						if ( $desc = bwc_get_billing_agreement_desc() ) {
							$data [ 'options' ] [ 'billingAgreementDescription' ] = $desc;
						}
					}
				}
				if ( $handle === self::$prefix . 'paypal-credit' ) {
					$data [ 'order_button_text' ] = __( 'Checkout With PayPal Credit', 'braintree-payments' );
					$data [ 'gateway_id' ] = bwc_get_gateway_id( 'paypal-credit' );
					$data [ 'html' ] = bwc_get_paypal_credit_html();
					$data [ 'form' ] [ 'submit' ] = bt_manager()->is_active( 'paypal_submit_form' );
					$data [ 'paypal_credit' ] [ 'enabled' ] = bwc_paypal_credit_enabled();
					$data [ 'options' ] = array (
							'flow' => 'checkout', 
							'currency' => get_woocommerce_currency(), 
							'displayName' => bt_manager()->get_option( 'paypal_display_name' ), 
							'enableShippingAddress' => WC()->cart->needs_shipping() && bwc_paypal_credit_send_shipping(), 
							'shippingAddressEditable' => WC()->cart->needs_shipping() && bwc_paypal_credit_send_shipping() 
					);
					
					if ( bwc_paypal_credit_active() ) {
						$data [ 'options' ] [ 'offerCredit' ] = true;
						$data [ 'paypal_credit' ] [ 'active' ] = true;
					} else {
						$data [ 'paypal_credit' ] [ 'active' ] = false;
					}
					$gateways = WC()->payment_gateways()->payment_gateways();
					$credit_gateway = isset( $gateways [ WC_PayPal_Credit_Payment_Gateway::ID ] ) ? $gateways [ WC_PayPal_Credit_Payment_Gateway::ID ] : null;
					if ( $credit_gateway ) {
						ob_start();
						wc_get_template( 'checkout/payment-method.php', array (
								'gateway' => $credit_gateway 
						) );
						$data [ 'gateway_html' ] = ob_get_clean();
					}
				}
				if ( $handle === self::$prefix . 'applepay' ) {
					$data [ 'gateway_id' ] = bwc_get_gateway_id( 'applepay' );
					$data [ 'store_name' ] = bt_manager()->get_option( 'applepay_store_name' );
					if ( ! $data [ 'order_total' ] ) {
						$data [ 'order_total' ] = 0.01; // apple pay requires at least one cent.
					}
				}
				break;
			case self::$prefix . 'change-payment-method' :
				$data [ 'loader' ] = array (
						'enabled' => bt_manager()->is_active( 'enable_loader' ), 
						'css' => json_decode( bt_manager()->get_option( 'custom_form_loader_css' ), true ), 
						'message' => bt_manager()->get_option( 'custom_form_loader_message' ) 
				);
				break;
			case self::$prefix . 'message-handler' :
				$data [ 'messages' ] = bfwc_get_combined_error_messages();
				break;
			case self::$prefix . 'payment-methods' :
				$data [ 'style' ] = bwc_saved_payment_method_style();
				$data [ 'icon_style' ] = bwc_payment_icons_type();
				$data [ 'wc' ] = array (
						'3.0.0' => bwc_is_wc_3_0_0_or_more() 
				);
				break;
			case self::$prefix . 'form-handler' :
				$data = array (
						'cart_fragments' => array (
								'refresh' => bwc_refresh_payment_fragments() 
						) 
				);
				break;
			case self::$prefix . 'fees' :
				$data = array (
						'fees' => array (
								'enabled' => bwc_fees_enabled() 
						) 
				);
				break;
			default :
				$data = false;
		}
		return $data;
	}

	public static function get_script_client_token( $handle )
	{
		$include_merchant_account = true;
		switch( $handle ) {
			case self::$prefix . 'hosted-fields' :
			case self::$prefix . 'dropin' :
			case self::$prefix . 'dropin-v3' :
			case self::$prefix . 'applepay' :
			case self::$prefix . 'paypal' :
			case self::$prefix . 'paypal-credit' :
				$env = bt_manager()->get_environment();
				$currency = get_woocommerce_currency();
				$token_key = "bfwc_{$env}_{$currency}_frontend_client_token_{$handle}";
				$bfwc_frontend_client_token = WC()->session->get( $token_key, array () );
				
				if ( empty( $bfwc_frontend_client_token ) || empty( $bfwc_frontend_client_token [ 'client_token' ] ) || $bfwc_frontend_client_token [ 'timeout' ] < time() || $bfwc_frontend_client_token [ 'count' ] >= 4 ) {
					
					$client_token = WC_Braintree_Payment_Gateway::generate_client_token( $include_merchant_account );
					
					WC()->session->set( $token_key, array (
							'client_token' => $client_token, 
							'timeout' => time() + MINUTE_IN_SECONDS * 10, 
							'count' => 1 
					) );
				} else {
					$client_token = $bfwc_frontend_client_token [ 'client_token' ];
					$bfwc_frontend_client_token [ 'count' ] = $bfwc_frontend_client_token [ 'count' ] + 1;
					WC()->session->set( $token_key, $bfwc_frontend_client_token );
				}
				return $client_token;
				break;
		}
		return null;
	}

	/**
	 *
	 * @deprecated 2.6.8
	 */
	public static function initialize_cart_totals()
	{
		if ( is_checkout() && WC()->cart->total === 0 ) {
			WC()->cart->calculate_totals();
		}
	}
}
Braintree_Gateway_Frontend_Scripts::init();