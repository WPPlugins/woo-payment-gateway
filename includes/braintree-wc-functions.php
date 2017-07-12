<?php
use Braintree\PaymentInstrumentType;

/**
 * Return the merchant account id configured for the WooCommerce currency.
 * If there is no merchant account, return an empty string.
 */
function bwc_get_merchant_account( $currency = '' )
{
	$currency = empty( $currency ) ? get_woocommerce_currency() : $currency;
	$key = sprintf( 'woocommerce_braintree_%s_merchant_account_id', bt_manager()->get_environment() );
	$merchant_accounts = bt_manager()->get_option( $key );
	
	if ( ! empty( $merchant_accounts ) ) {
		
		return isset( $merchant_accounts [ $currency ] ) ? $merchant_accounts [ $currency ] : '';
	} else {
		return '';
	}
}

/**
 * Return true if dynamic descriptors have been enabled.
 *
 * @return boolean
 */
function bwc_is_descriptors_enabled()
{
	return bt_manager()->is_active( 'dynamic_descriptors' );
}

/**
 * Return true if custom forms are enabled.
 *
 * @return boolean
 */
function bwc_is_custom_form()
{
	return bt_manager()->get_option( 'checkout_form' ) === 'custom_form';
}

function bwc_is_3ds_enabled()
{
	if ( ! bwc_is_checkout() ) {
		return false; // only allow 3DS check on checkout page since 3DS requires an amount.
	}
	return bt_manager()->is_active( '3ds_enabled' );
}

/**
 * Return true if 3DS is active.
 * If WC subscriptions is active and
 * the request is for a payment method change, this function will return false
 * regardless of
 * the 3DS setting.
 *
 * @return boolean
 */
function bwc_is_3ds_active()
{
	$enabled = bwc_is_3ds_enabled();
	if ( $enabled ) {
		$cond = bt_manager()->get_option( '3ds_conditions' );
		return bwc_execute_conditional_statement( $cond );
	} else {
		return false;
	}
}

/**
 * Return true if credit card payments are enabled.
 *
 * @return boolean
 */
function bwc_card_payments_enabled()
{
	return bt_manager()->is_active( 'enabled' );
}

/**
 * Return true if Apple Pay is enabled.
 *
 * @return boolean
 */
function bwc_is_applepay_enabled()
{
	return bt_manager()->is_active( 'enable_applepay' );
}

/**
 * Return true of the order contains a transaction Id.
 *
 * @param WC_Order $order        	
 */
function bwc_can_refund_order( $order )
{
	$id = $order->get_transaction_id();
	return ! empty( $id );
}

/**
 * Return an array of custom form fields used in the custom payment form.
 *
 * @return mixed
 */
function bwc_get_custom_form_fields()
{
	return apply_filters( 'braintree_woocommerce_custom_form_fields', array (
			'number' => array (
					'selector' => '#bfwc-card-number' 
			), 
			'cvv' => array (
					'selector' => '#bfwc-cvv' 
			), 
			'postalCode' => array (
					'selector' => '#bfwc-postal-code' 
			), 
			'expirationDate' => array (
					'selector' => '#bfwc-expiration-date' 
			), 
			'expirationMonth' => array (
					'selector' => '#bfwc-expiration-month' 
			), 
			'expirationYear' => array (
					'selector' => '#bfwc-expiration-year' 
			) 
	) );
}

/**
 * Return the configured custom form.
 * <strong>Example</strong>
 * array('html' => 'custom-forms/bootstrap-form.php',
 * 'css'=>'https:'//example.com/styles/mycss.css')
 *
 * @return array
 */
function bwc_get_custom_form()
{
	$form = bt_manager()->get_option( 'custom_form_design' );
	$forms = bwc_get_custom_forms();
	return $forms [ $form ];
}

/**
 * Return an array of custom form.
 *
 * @return array
 */
function bwc_get_custom_forms()
{
	return include bt_manager()->plugin_include_path() . 'braintree-custom-forms.php';
}

/**
 * Return the html for the 3DS modal html.
 */
function bwc_get_3ds_modal_html()
{
	ob_start();
	bwc_get_template( 'custom-forms/3ds-modal.php' );
	return ob_get_clean();
}

/**
 * Return true if the payment method icons should be displayed on the outside of
 * the gateway html.
 *
 * @return boolean
 */
function bwc_payment_icons_outside()
{
	return bt_manager()->get_option( 'payment_method_location' ) === 'outside';
}

/**
 * Return true if PayPal has been enabled as a payment gateway.
 * Custom forms
 * must be enabled as well in order for this function to return true.
 *
 * @return boolean
 */
function bwc_is_paypal_enabled()
{
	return bt_manager()->is_active( 'enable_paypal' );
}

/**
 * Return the button that has been selected for use on the frontend.
 *
 * @return array
 */
function bwc_get_paypal_button()
{
	$button = bt_manager()->get_option( 'paypal_button_design' );
	$buttons = braintree_get_paypal_buttons();
	return $buttons [ $button ];
}

function bwc_get_paypal_credit_button()
{
	$button = bt_manager()->get_option( 'paypal_credit_button' );
	$buttons = braintree_get_paypal_credit_buttons();
	return $buttons [ $button ];
}

/**
 * Return the gateway id for the given gateway.
 *
 * @param string $gateway
 *        	braintree | paypal
 * @return string
 */
function bwc_get_gateway_id( $gateway )
{
	switch( $gateway ) {
		case 'braintree' :
			return WC_Braintree_Payment_Gateway::ID;
			break;
		case 'paypal' :
			return WC_PayPal_Payment_Gateway::ID;
			break;
		case 'paypal-credit' :
			return WC_PayPal_Credit_Payment_Gateway::ID;
			break;
		case 'applepay' :
			return WC_Applepay_Payment_Gateway::ID;
			break;
	}
}

/**
 * Return the html used for paypal methods.
 *
 * @return string
 */
function bwc_get_paypal_html()
{
	ob_start();
	bwc_get_template( 'paypal/paypal-vaulted.php' );
	return ob_get_clean();
}

function bwc_get_paypal_credit_html()
{
	ob_start();
	bwc_get_template( 'paypal/paypal-credit-vaulted.php' );
	return ob_get_clean();
}

/**
 * Return true if the PayPal checkout flow is 'checkout.'
 * If WooCommerce Subscriptions is active, then check if the cart contains
 * subscriptions.
 * If there are subscriptions in the cart, then 'vault' must be active to ensure
 * PayPal payment methods are saved. If the page is the add payment page, then
 * false is returned as 'vault' flow
 * is needed for adding PayPal payment methods.
 *
 * @return string 'checkout' | 'vault'
 */
function bwc_paypal_checkout_flow()
{
	$flow = bt_manager()->get_option( 'paypal_checkout_flow' );
	
	if ( class_exists( 'WC_Subscriptions_Cart' ) ) {
		if ( WC_Subscriptions_Cart::cart_contains_subscription() ) {
			return false;
		}
	}
	if ( bfwcs_cart_contains_subscriptions() ) {
		return false;
	}
	if ( is_add_payment_method_page() ) {
		return false;
	}
	
	return $flow === 'checkout';
}

/**
 * Return true of PayPal Credit is enabled.
 * PayPal Credit is only available when it is the checkout page
 * and the cart contains no subscriptions.
 *
 * @return boolean
 */
function bwc_paypal_credit_enabled()
{
	$is_enabled = bt_manager()->is_active( 'paypal_credit' ) && ( bwc_is_checkout() || is_admin() );
	if ( $is_enabled ) {
		if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
			$is_enabled = false;
		} elseif ( function_exists( 'bfwcs_cart_contains_subscriptions' ) && bfwcs_cart_contains_subscriptions() ) {
			$is_enabled = false;
		}
	}
	return $is_enabled;
}

function bwc_paypal_credit_active()
{
	$cond = bt_manager()->get_option( 'paypal_credit_conditions' );
	
	$is_enabled = bwc_paypal_credit_enabled();
	
	if ( $is_enabled ) {
		return bwc_execute_conditional_statement( $cond );
	} else {
		return false;
	}
}

function bwc_execute_conditional_statement( $cond )
{
	if ( ! class_exists( 'Braintree_Gateway_Condition_Evaluator' ) ) {
		require_once bt_manager()->plugin_include_path() . 'libraries/class-condition-evaluator.php';
	}
	
	$eval = new Braintree_Gateway_Condition_Evaluator();
	return $eval->evaluate( $cond, bwc_get_conditional_values() );
}

/**
 * Return an array of conditional value keys and their values.
 *
 * @return mixed
 */
function bwc_get_conditional_values()
{
	return apply_filters( 'bwc_get_conditional_values', is_admin() ? array (
			'{amount}' => 2.00, 
			'{qty}' => 1, 
			'{currency}' => 'USD', 
			'{b_country}' => 'US', 
			'{s_country}' => 'US', 
			'{products}' => bwc_get_comma_separated_product_names() 
	) : array (
			'{amount}' => WC()->cart->total, 
			'{qty}' => WC()->cart->get_cart_contents_count(), 
			'{currency}' => get_woocommerce_currency(), 
			'{b_country}' => isset( $_POST [ 'billing_country' ] ) ? $_POST [ 'billing_country' ] : ( bwc_is_wc_3_0_0_or_more() ? WC()->customer->get_billing_country() : WC()->customer->get_country() ), 
			'{s_country}' => isset( $_POST [ 'shipping_country' ] ) ? $_POST [ 'shipping_country' ] : WC()->customer->get_shipping_country(), 
			'{products}' => bwc_get_comma_separated_product_names() 
	) );
}

function bwc_get_conditional_statements()
{
	return apply_filters( 'bwc_get_conditional_statements', array (
			'/AND/' => '&&', 
			'/OR/' => '||', 
			'/([\w]+)(?<!NOT)\s+?EQ\s+?([\w]+)/' => '"$1" == "$2"', 
			'/([\w]+)\s?+NOT\s+EQ\s?+([\w]+)/' => '"$1" != "$2"', 
			'/([\w]+)(?<!NOT)\s+?IN\s+?(\([\w,\s]*\))/' => str_replace( array_keys( bwc_get_conditional_values() ), bwc_get_conditional_values(), 'strpos("$2", "$1") !== false' ), 
			'/([\w]+)\s+?NOT\s+IN\s+?(\([\w,\s]*\))/' => str_replace( array_keys( bwc_get_conditional_values() ), bwc_get_conditional_values(), 'strpos("$2", "$1") == false' ) 
	) );
}

/**
 * Return a comma separated list of products.
 */
function bwc_get_comma_separated_product_names()
{
	global $wpdb, $bwc_comma_separated_product_names_result;
	
	if ( ! $bwc_comma_separated_product_names_result ) {
		$query = $wpdb->prepare( "SELECT post_name FROM $wpdb->posts WHERE post_type = %s AND post_status = %s", 'product', 'publish' );
		$results = $wpdb->get_results( $query );
		foreach ( $results as $result ) {
			$bwc_comma_separated_product_names_result [] = $result->post_name;
		}
	}
	return $bwc_comma_separated_product_names_result ? implode( ',', $bwc_comma_separated_product_names_result ) : '';
}

/**
 * Return true if the gateway is configured to reject duplicate payment methods.
 *
 * @return boolean
 */
function bwc_fail_on_duplicate()
{
	return bt_manager()->is_active( 'fail_on_duplicate' );
}

/**
 * Return true if advanced fraud tools has been enabled.
 *
 * @return boolean
 */
function bwc_is_advanced_fraud_tools()
{
	return bt_manager()->is_active( 'advanced_fraud_enabled' );
}

/**
 * Include the given template.
 * The template is first checked in the theme's path.
 *
 * @param string $template        	
 * @param array $args        	
 */
function bwc_get_template( $template, $args = array() )
{
	extract( $args );
	
	$located = bwc_locate_template( $template );
	
	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( 'File %s does not exist.', $template ), bt_manager()->version );
		return;
	}
	
	// Allow other plugins to replace the file if desired.
	$located = apply_filters( "braintree_woocommerce_template_{$template}", $located, $args );
	
	include $located;
}

/**
 * Loate the template file in the theme and if not in the theme then in the
 * plugin.
 *
 * @param unknown $template_names        	
 */
function bwc_locate_template( $template_name )
{
	$default_path = bt_manager()->plugin_path() . 'templates/';
	
	$located = locate_template( trailingslashit( bt_manager()->template_path() ) . $template_name );
	
	if ( ! $located ) {
		$located = $default_path . $template_name;
	}
	return $located;
}

function bwc_is_checkout()
{
	global $wp;
	return is_checkout() && ! bwcs_is_change_payment_method() && ! isset( $wp->query_vars [ 'order-received' ] ) || defined( 'WOOCOMMERCE_CHECKOUT' );
}

function bwc_get_user_payment_methods( $user_id )
{
	$methods = braintree_get_user_payment_methods( $user_id );
	// If PayPal is enabled, only return non PayPal payment methods.
	if ( bwc_is_paypal_enabled() ) {
		foreach ( $methods as $key => $method )
			if ( $method [ 'type' ] === PaymentInstrumentType::PAYPAL_ACCOUNT ) {
				unset( $methods [ $key ] );
			}
	}
	if ( bwc_is_applepay_enabled() ) {
		foreach ( $methods as $key => $method )
			if ( $method [ 'type' ] === PaymentInstrumentType::APPLE_PAY_CARD ) {
				unset( $methods [ $key ] );
			}
	}
	return $methods;
}

function bwc_get_user_paypal_payment_methods( $user_id )
{
	$methods = braintree_get_user_payment_methods( $user_id );
	// If PayPal is enabled, only return non PayPal payment methods.
	if ( bwc_is_paypal_enabled() ) {
		foreach ( $methods as $key => $method )
			if ( $method [ 'type' ] !== PaymentInstrumentType::PAYPAL_ACCOUNT ) {
				unset( $methods [ $key ] );
			}
	}
	return $methods;
}

function bwc_get_user_applepay_payment_methods( $user_id )
{
	$methods = braintree_get_user_payment_methods( $user_id );
	// If PayPal is enabled, only return non PayPal payment methods.
	if ( bwc_is_applepay_enabled() ) {
		foreach ( $methods as $key => $method )
			if ( $method [ 'type' ] !== PaymentInstrumentType::APPLE_PAY_CARD ) {
				unset( $methods [ $key ] );
			}
	}
	return $methods;
}

/**
 * Return an array of saved payment methods.
 * Most useful if called
 * when the payment-methods page is being rendered.
 *
 * @param unknown $methods        	
 * @return mixed
 */
function bwc_saved_payment_methods_list( $saved_methods, $methods, $gateway_id )
{
	foreach ( $methods as $token => $method ) {
		$data = array (
				'method' => array (), 
				'expires' => $method [ 'type' ] === PaymentInstrumentType::PAYPAL_ACCOUNT ? __( 'N/A', 'braintree-payments' ) : sprintf( '%s / %s', $method [ 'exp_month' ], $method [ 'exp_year' ] ), 
				
				'actions' => array (
						'delete' => array (
								'name' => __( 'Delete', 'braintree-payments' ), 
								'url' => wp_nonce_url( add_query_arg( "{$gateway_id}_delete_method", $token, wc_get_endpoint_url( 'payment-methods', '', get_permalink( wc_get_page_id( 'myaccount' ) ) ) ), 'delete-payment-method' ) 
						) 
				) 
		);
		$data [ 'method' ] [ 'bfwc_token' ] = $token;
		$data [ 'method' ] [ 'method_type' ] = strtolower( str_replace( ' ', '_', $method [ 'method_type' ] ) );
		switch( $method [ 'type' ] ) {
			case PaymentInstrumentType::CREDIT_CARD :
				$data [ 'method' ] [ 'last4' ] = $method [ 'last4' ];
				$data [ 'method' ] [ 'brand' ] = $method [ 'card_type' ];
				break;
			case PaymentInstrumentType::PAYPAL_ACCOUNT :
				$data [ 'method' ] [ 'brand' ] = sprintf( __( 'PayPal - %s', 'braintree-payments' ), $method [ 'email' ] );
				break;
			case PaymentInstrumentType::APPLE_PAY_CARD :
				$last4 = preg_match( '/[\d]+/', $method [ 'payment_instrument_name' ], $matches ) ? $matches [ 0 ] : '';
				$data [ 'method' ] [ 'brand' ] = preg_match( '/[a-z]+/i', $method [ 'payment_instrument_name' ], $matches ) ? sprintf( __( 'Apple Pay %s', 'braintree-payments' ), $matches [ 0 ] ) : $method [ 'card_type' ];
				$data [ 'method' ] [ 'last4' ] = $last4;
				break;
		}
		
		if ( $method [ 'default' ] ) {
			$data [ 'is_default' ] = 'true';
		}
		$saved_methods [ $gateway_id ] [] = $data;
	}
	
	return apply_filters( 'braintree_saved_payment_methods_list', $saved_methods );
}

/**
 * Return true if dynamic card display is active.
 *
 * @return boolean
 */
function bwc_is_dynamic_card_display()
{
	return bt_manager()->is_active( 'dynamic_card_display' );
}

/**
 * Echo an input field for the payment method token.
 *
 * @param string $token        	
 */
function bwc_payment_method_token_field( $id, $token = '' )
{
	$field = '<input type="hidden" class="bfwc-payment-method-token" id="' . $id . '" name="' . $id . '" value="' . $token . '"/>';
	echo $field;
}

/**
 * Return an array of Apple Pay buttons.
 *
 * @return array
 */
function bwc_get_applepay_buttons()
{
	$buttons = require bt_manager()->plugin_include_path() . 'braintree-applepay-buttons.php';
	return $buttons;
}

/**
 * Return the button that has been configured for Apple Pay.
 *
 * @return array
 */
function bwc_get_applepay_button()
{
	$button_id = bt_manager()->get_option( 'applepay_button' );
	$buttons = bwc_get_applepay_buttons();
	return $buttons [ $button_id ];
}

/**
 * Return an array of braintree WC gateways.
 *
 * @return array
 */
function bwc_get_payment_gateways()
{
	$gateways = apply_filters( 'bwc_add_payment_gateways', array () );
	$wc_gateways = array ();
	foreach ( $gateways as $class ) {
		$wc_gateways [] = $class::ID;
	}
	return $wc_gateways;
}

function bwc_get_default_method( $methods = array() )
{
	$default_method = null;
	
	foreach ( $methods as $method ) {
		if ( $method [ 'default' ] ) {
			return $method;
		} else {
			if ( is_null( $default_method ) || ( isset( $method [ 'created_at' ] ) && $method [ 'created_at' ] > $default_method [ 'created_at' ] ) ) {
				$default_method = $method;
			}
		}
	}
	return $default_method;
}

function bwc_get_save_method_template()
{
	bwc_get_template( 'checkout/save-method.php' );
}

/**
 * Return true if the save payment method checkbox should be displayed.
 */
function bwc_display_save_payment_method()
{
	if ( bt_manager()->is_active( 'save_payment_methods' ) && bwc_enable_signup_from_checkout() ) {
		
		// must be checkout page to display save payment method checkbox.
		if ( bwc_is_checkout() ) {
			if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
				return false;
			}
			if ( bfwcs_cart_contains_subscriptions() ) {
				return false;
			} else {
				return true;
			}
		} else {
			return false;
		}
	} else {
		return false;
	}
}

function bwc_enable_signup_from_checkout()
{
	return get_option( 'woocommerce_enable_signup_and_login_from_checkout' ) === 'yes';
}

function bwc_saved_payment_method_style()
{
	return bt_manager()->get_option( 'saved_payment_methods_style' );
}

function bwc_refresh_payment_fragments()
{
	return bt_manager()->is_active( 'refresh_payment_fragments' );
}

/**
 *
 * @deprecated
 *
 * @param string $id        	
 * @param string $token        	
 */
function bwc_payment_token_field( $id, $token = '' )
{
	return braintree_payment_token_field( $id );
}

/**
 * Return true of V3 of the dropin is enabled.
 *
 * @return boolean
 */
function bwc_dropin_v3_enabled()
{
	return ! bwc_is_custom_form() && bt_manager()->get_option( 'dropin_form_version' ) === 'v3';
}

/**
 * Return true if V2 of the dropin is enabled.
 *
 * @return boolean
 */
function bwc_dropin_v2_enabled()
{
	return ! bwc_is_custom_form() && bt_manager()->get_option( 'dropin_form_version' ) === 'v2';
}

function bwc_get_loader_file()
{
	$name = bt_manager()->get_option( 'custom_form_loader_file' );
	$file = 'loader/' . $name;
	return $file;
}

function bwc_payment_loader_enabled()
{
	return bt_manager()->is_active( 'enable_loader' );
}

/**
 * Return true if the postal code field has been enabled for custom forms.
 *
 * @return boolean
 */
function bwc_postal_code_enabled()
{
	if ( bwc_is_custom_form() ) {
		return bt_manager()->is_active( 'postal_field_enabled' );
	} else {
		return bt_manager()->is_active( 'dropin_postal_enabled' );
	}
}

/**
 * Return true if the cvv field has been enabled for custom forms.
 *
 * @return boolean
 */
function bwc_cvv_field_enabled()
{
	return bt_manager()->is_active( 'cvv_field_enabled' );
}

function bwc_paypal_send_shipping()
{
	return bt_manager()->is_active( 'paypal_send_shipping' );
}

function bwc_paypal_credit_send_shipping()
{
	return bt_manager()->is_active( 'paypal_send_shipping' );
}

/**
 * Return true if the WC version is 3.0.0 or greater.
 *
 * @return boolean
 */
function bwc_is_wc_3_0_0_or_more()
{
	return function_exists( 'WC' ) ? version_compare( WC()->version, '3.0.0', '>=' ) : false;
}

/**
 * Wrapper for returning the provided order property.
 * Based on the WC version, the property is fetched differently. Backwards compatability for versions
 * less than WC 3.0.0 is needed, thus the implementation of a wrapper.
 *
 * @param string $prop        	
 * @param $order WC_Order        	
 * @param string $context        	
 */
function bwc_get_order_property( $prop, $order )
{
	$value = '';
	
	if ( bwc_is_wc_3_0_0_or_more() ) {
		if ( array_key_exists( $prop, bwc_get_3_0_0_updated_props() ) ) {
			$prop = bwc_get_3_0_0_updated_props() [ $prop ];
		}
		if ( is_callable( array (
				$order, 
				"get_$prop" 
		) ) ) {
			$value = $order->{"get_$prop"}();
		} else {
			if ( ! $value = bwc_get_3_0_0_deprecated_order_prop( $prop, $order ) ) {
				/**
				 * If the getter method does not exist (for custom properties for example) then
				 * fetch the data directly from the post_meta of the order.
				 */
				$value = get_post_meta( bwc_get_order_property( 'id', $order ), "_{$prop}", true );
			}
		}
	} else {
		$value = $order->{$prop};
	}
	return $value;
}

function bwc_get_3_0_0_updated_props()
{
	return array (
			'customer_user' => 'customer_id', 
			'order_currency' => 'currency' 
	);
}

function bwc_get_3_0_0_deprecated_order_prop( $prop, $order )
{
	$value = null;
	switch( $prop ) {
		case 'post_status' :
			$value = get_post_status( $order->get_id() );
			break;
		case 'id' :
			$value = $order->get_id();
			break;
		case 'order_currency' :
			$value = $order->get_currency();
			break;
		case 'post' :
			$value = get_post( $order->get_id() );
			break;
	}
	return $value;
}

/**
 * Return true if the current request is an admin webhook test.
 *
 * @since 2.6.2
 */
function bwc_is_admin_webhook_request()
{
	return defined( 'BFWC_ADMIN_WEBHOOK_TEST' );
}

/**
 *
 * @param string $prop        	
 * @param WC_Product $product        	
 */
function bwc_get_product_property( $prop, $product )
{
	$value = '';
	if ( bwc_is_wc_3_0_0_or_more() ) {
		if ( is_callable( array (
				$product, 
				"get_$prop" 
		) ) ) {
			$value = $product->{"get_$prop"}();
		} else {
			$value = get_post_meta( bwc_get_product_property( 'id', $product ), "_$prop", true );
		}
	} else {
		$value = $product->{$prop};
	}
	return $value;
}

/**
 * Return the billing agreement description.
 *
 * @return string
 */
function bwc_get_billing_agreement_desc()
{
	return bt_manager()->get_option( 'paypal_billing_agreement_desc' );
}

function bwc_payment_icons_enclosed_type()
{
	return bwc_payment_icons_type() === 'enclosed';
}

/**
 * Return the icon type that is set for payment methods
 *
 * @return string
 */
function bwc_payment_icons_type()
{
	return bt_manager()->get_option( 'method_icon_style' );
}

function bwc_get_enclosed_icon_url( $method )
{
	return bt_manager()->plugin_assets_path() . 'img/payment-methods/' . $method . '.png';
}

/**
 * Return true if icons should be displayed on the payment methods page.
 *
 * @since 2.6.7
 * @return boolean
 */
function bwc_display_icons_on_payment_methods_page()
{
	return bt_manager()->is_active( 'display_icons_on_payment_methods' );
}

/**
 * Return true if vaulted payment methods should be verified with 3DS.
 */
function bwc_3ds_verify_vaulted_methods()
{
	return bwc_is_3ds_active() && bt_manager()->is_active( '3ds_enabled_payment_token' );
}

function bwc_3ds_no_action_needed()
{
	$no_action = bt_manager()->get_option( '3ds_liability_not_shifted' );
	$no_action2 = bt_manager()->get_option( '3ds_card_ineligible' );
	return $no_action === 'no_action' && $no_action2 === 'no_action';
}

/**
 * Return true if fees are enabled.
 *
 * @since 2.6.7
 * @return boolean
 */
function bwc_fees_enabled()
{
	$fees = bwc_get_gateway_fees();
	return bt_manager()->is_active( 'checkout_fee_enabled' ) && ! empty( $fees );
}

/**
 * Return true if there is a fee configured for the gateway.
 *
 * @since 2.6.7
 * @param string $gateway        	
 */
function bwc_fee_enabled_for_gateway( $gateway )
{
	$fees = bwc_get_gateway_fees();
	if ( $fees ) {
		foreach ( $fees as $fee ) {
			if ( ! empty( $fee [ 'gateways' ] ) ) {
				$fee_gateways = $fee [ 'gateways' ];
				if ( in_array( $gateway, $fee_gateways ) ) {
					return true;
				}
			}
		}
		return false;
	} else {
		return false;
	}
}

/**
 * Evaluate the fee.
 *
 * @since 2.6.7
 * @param string $fee        	
 */
function bwc_calculate_fee( $fee, $args = array() )
{
	$fee [ 'calculation' ] = str_replace( array (
			'[qty]', 
			'[cost]' 
	), array (
			$args [ 'qty' ], 
			$args [ 'cost' ] 
	), $fee [ 'calculation' ] );
	if ( ! class_exists( 'WC_Eval_Math' ) ) {
		include_once ( WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php' );
	}
	return $fee ? WC_Eval_Math::evaluate( $fee [ 'calculation' ] ) : 0;
}

/**
 * Return the gateway fee(s).
 *
 * @since 2.6.7
 * @return string
 */
function bwc_get_gateway_fees()
{
	return bt_manager()->get_option( 'checkout_fees' );
}

function bwc_get_fees_for_gateway( $gateway )
{
	$fees = bwc_get_gateway_fees();
	$fees = $fees ? $fees : array ();
	$gateway_fees = array ();
	foreach ( $fees as $fee ) {
		if ( in_array( $gateway, $fee [ 'gateways' ] ) ) {
			$gateway_fees [] = $fee;
		}
	}
	return $gateway_fees;
}

/**
 *
 * @since 2.6.9
 * @param string $option        	
 * @return string
 */
function bwc_get_option_text( $option )
{
	return bt_manager()->get_option( $option );
}

/**
 *
 * @param string $placeholder        	
 * @param string $text        	
 * @param string $echo        	
 * @return string
 */
function bwc_custom_form_text( $key, $text, $echo = true )
{
	if ( bwc_use_admin_text_for_custom_form() ) {
		$val = bt_manager()->get_option( $key );
		return $echo ? printf( $val ) : $val;
	}
	return $echo ? printf( $text ) : $text;
}

function bwc_use_admin_text_for_custom_form()
{
	return bt_manager()->is_active( 'admin_text_for_form' );
}