<?php
if ( ! class_exists( 'WC_Braintree_Payment_Gateway' ) ) {
	return;
}

/**
 * Subscription cart class used for logic related to adding a Braintree Subscription to the WC cart.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class WC_Braintree_Subscriptions_Cart
{
	
	private static $recurring_total_calculation = false;
	
	private static $current_recurring_cart_key = '';
	
	private static $current_recurring_cart = null;

	public static function init()
	{
		add_action( 'woocommerce_before_calculate_totals', __CLASS__ . '::set_subscription_calculations' );
		
		add_action( 'woocommerce_after_calculate_totals', __CLASS__ . '::remove_subscription_calculations', 50 );
		
		add_action( 'woocommerce_calculate_totals', __CLASS__ . '::save_shipping_data', 10, 1 );
		
		add_action( 'woocommerce_after_calculate_totals', __CLASS__ . '::add_subscription_data', 10, 1 );
		
		add_action( 'bfwc_after_recurring_cart_calculations', __CLASS__ . '::set_shipping_data' );
		
		add_action( 'woocommerce_cart_id', __CLASS__ . '::generate_cart_key', 10, 5 );
		
		add_filter( 'bfwcs_get_recurring_cart_key', __CLASS__ . '::generate_recurring_cart_key', 10, 3 );
		
		add_filter( 'woocommerce_cart_product_price', __CLASS__ . '::cart_product_price', 10, 2 );
		
		add_filter( 'woocommerce_cart_product_subtotal', __CLASS__ . '::cart_product_subtotal', 10, 4 );
		
		add_action( 'woocommerce_cart_totals_after_order_total', __CLASS__ . '::cart_totals_after_order_total' );
		
		add_action( 'woocommerce_braintree-subscription_add_to_cart', __CLASS__ . '::add_to_cart_simple_template' );
		
		add_action( 'woocommerce_braintree-variable-subscription_add_to_cart', __CLASS__ . '::add_to_cart_variable_template' );
		
		add_filter( 'woocommerce_add_to_cart_validation', __CLASS__ . '::add_to_cart_validation', 10, 5 );
		
		add_filter( 'woocommerce_cart_needs_payment', __CLASS__ . '::cart_needs_payment', 10, 2 );
	}

	/**
	 *
	 * @param WC_Cart $cart        	
	 */
	public static function set_subscription_calculations( $cart )
	{
		if ( bwc_is_wc_3_0_0_or_more() ) {
			add_action( 'woocommerce_product_get_price', __CLASS__ . '::calculate_subscription_price', 10, 2 );
			add_filter( 'woocommerce_product_needs_shipping', __CLASS__ . '::subscription_needs_shipping', 10, 2 );
		} else {
			add_action( 'woocommerce_get_price', __CLASS__ . '::calculate_subscription_price', 10, 2 );
			add_filter( 'woocommerce_product_needs_shipping', __CLASS__ . '::subscription_needs_shipping', 10, 2 );
		}
	}

	public static function remove_subscription_calculations( $cart )
	{
		if ( bwc_is_wc_3_0_0_or_more() ) {
			remove_action( 'woocommerce_product_get_price', __CLASS__ . '::calculate_subscription_price', 10, 2 );
			remove_filter( 'woocommerce_product_needs_shipping', __CLASS__ . '::subscription_needs_shipping', 10, 2 );
		} else {
			remove_action( 'woocommerce_get_price', __CLASS__ . '::calculate_subscription_price', 10, 2 );
			remove_filter( 'woocommerce_product_needs_shipping', __CLASS__ . '::subscription_needs_shipping', 10, 2 );
		}
	}

	/**
	 *
	 * @param bool $bool        	
	 * @param WC_Product $product        	
	 */
	public static function subscription_needs_shipping( $needs_shipping, $product )
	{
		if ( ! bfwcs_product_is_subscription( $product ) ) {
			return $needs_shipping;
		}
		
		// if currently in the middle of performing a recurring calculation.
		if ( self::$recurring_total_calculation ) {
			if ( $product->is_one_time_shipping() ) {
				$needs_shipping = false; // don't want to keep charging shipping on the recurring fee.
			}
			
			return $needs_shipping;
		} else {
			if ( ! $product->is_virtual() ) {
				// subscription has a trial period, no shipping needed on first order.
				if ( $trial_length = $product->subscription_trial_length ) {
					$needs_shipping = false;
				}
			}
		}
		return $needs_shipping;
	}

	/**
	 * Save the shipping data in the session so it can be retrieved later.
	 */
	public static function save_shipping_data( $cart )
	{
		$shipping_data = array (
				'shipping_methods' => WC()->session->chosen_shipping_methods, 
				'shipping_total' => WC()->shipping()->shipping_total, 
				'shipping_taxes' => WC()->shipping()->shipping_taxes, 
				'packages' => WC()->shipping()->packages 
		);
		if ( ! self::$recurring_total_calculation ) {
			WC()->session->set( 'bfwcs_shipping_data', $shipping_data );
		} else {
			WC()->session->set( 'bfwcs_shipping_data_' . $cart->cart_key, $shipping_data );
		}
	
	}

	/**
	 * If there are shipping methods which need to be set then they will be reset on the WC session.
	 * This method is needed because when WC()->shipping()->reset_shipping() is called, it resets all the shipping methods.
	 */
	public static function set_shipping_data()
	{
		
		$shipping_data = WC()->session->get( 'bfwcs_shipping_data', array () );
		if ( ! empty( $shipping_data ) && $shipping_data [ 'shipping_total' ] ) {
			WC()->session->chosen_shipping_methods = $shipping_data [ 'shipping_methods' ];
			WC()->shipping()->shipping_total = $shipping_data [ 'shipping_total' ];
			WC()->shipping()->shipping_taxes = $shipping_data [ 'shipping_taxes' ];
			WC()->shipping()->packages = $shipping_data [ 'packages' ];
			
			WC()->session->set( 'bfwcs_shipping_data', array () );
		}
	
	}

	/**
	 *
	 * @param string $price        	
	 * @param WC_Product $product        	
	 * @return string $price
	 */
	public static function calculate_subscription_price( $price, $product )
	{
		if ( bfwcs_product_is_subscription( $product ) ) {
			if ( ! self::$recurring_total_calculation ) {
				$trial_length = $product->subscription_trial_length;
				if ( $trial_length > 0 ) {
					$price = $product->get_signup_fee();
				} else {
					$price = $price + $product->get_signup_fee();
				}
			}
		}
		return $price;
	}

	/**
	 * Caculate subscription dates, totals, etc for the subscriptions located in the cart.
	 *
	 * @param WC_Cart $cart        	
	 */
	public static function add_subscription_data( $cart )
	{
		// If currently calculting data or cart doesn't contain subscriptions, return;
		if ( self::$recurring_total_calculation || ! bfwcs_cart_contains_subscriptions() ) {
			return;
		}
		$subscription_groups = array ();
		
		WC()->cart->recurring_carts = array ();
		$index = 0;
		foreach ( WC()->cart->get_cart() as $cart_key => $cart_item ) {
			
			// product in cart_item is a subscription so perform logic.
			if ( bfwcs_product_is_subscription( $cart_item [ 'data' ] ) ) {
				$subscription_groups [ self::get_recurring_cart_key( $cart_item [ 'data' ], $index ) ] [] = $cart_key;
				$index ++;
			}
		}
		
		foreach ( $subscription_groups as $recurring_cart_key => $subscription_group ) {
			
			$recurring_cart = clone WC()->cart;
			$recurring_cart->is_recurring_cart = true;
			$recurring_cart->cart_key = $recurring_cart_key;
			
			foreach ( $recurring_cart->get_cart() as $cart_item_key => $recurring_cart_item ) {
				// unset any keys that don't match this key. This is necessary to calculate totals for each subscription group only.
				if ( ! in_array( $cart_item_key, $subscription_group ) ) {
					unset( $recurring_cart->cart_contents [ $cart_item_key ] );
				} else {
					// only the same products can be grouped together because of the recurring_cart_key.
					$product = $recurring_cart_item [ 'data' ];
				}
			}
			self::$recurring_total_calculation = true;
			self::$current_recurring_cart_key = $recurring_cart_key;
			self::$current_recurring_cart = $recurring_cart;
			
			/* recalculate the totals for this cart so the recurring fee can be shown. */
			$recurring_cart->calculate_totals();
			
			$trial_period = $product->subscription_trial_period;
			$trial_length = $product->subscription_trial_length;
			$length = $product->get_subscription_length();
			$period = $product->subscription_period;
			
			$recurring_cart->merchant_account_id = bwc_get_merchant_account();
			$recurring_cart->start_date = bfwcs_calculate_start_date();
			$recurring_cart->next_payment_date = bfwcs_calculate_first_payment_date( $trial_period, $trial_length );
			$recurring_cart->trial_end_date = bfwcs_calculate_first_payment_date( $trial_period, $trial_length );
			$recurring_cart->end_date = bfwcs_calculate_end_date( $length, $period, $trial_period, $trial_length, bfwc_get_gateway_timezone() );
			$recurring_cart->subscription_time_zone = bfwc_get_gateway_timezone();
			$recurring_cart->subscription_trial_length = $trial_length;
			$recurring_cart->subscription_trial_period = $trial_period;
			$recurring_cart->first_payment_date = $recurring_cart->next_payment_date;
			$recurring_cart->braintree_plan = bfwcs_get_plan_from_product( $product );
			$recurring_cart->subscription_period = $period;
			$recurring_cart->subscription_period_interval = $product->subscription_period_interval;
			$recurring_cart->subscription_length = $length;
			$recurring_cart->descriptors = bfwc_get_product_descriptors( $product );
			
			WC()->cart->recurring_carts [ $recurring_cart_key ] = $recurring_cart;
		}
		self::$recurring_total_calculation = false;
		
		do_action( 'bfwc_after_recurring_cart_calculations' );
	}

	/**
	 * Generate a recurring cart key using the product.
	 *
	 * @param WC_Product_Braintree_Subscription $product        	
	 */
	public static function get_recurring_cart_key( $product, $index )
	{
		$key = '';
		$plan = bfwcs_get_plan_from_product( $product );
		$interval = $product->subscription_period_interval;
		
		// product_id_1.62_every_3rd_month_for_12months_with_a_12day_free_trial_and_a_0.45_signup_fee_plan_testEUR.
		$key = sprintf( '%s_%s', $product->get_id(), $product->price );
		
		$key = sprintf( '%s %s', $key, bfwcs_billing_interval_string( $product->subscription_period_interval ) );
		
		if ( $trial_length = $product->subscription_trial_length ) {
			$key = sprintf( '%s %s', $key, sprintf( __( 'with a %s %s free trial', 'braintree-payments' ), $product->subscription_trial_length, $product->subscription_trial_period ) );
		}
		if ( $signup_fee = $product->get_signup_fee() ) {
			$key = sprintf( '%s %s', $key, sprintf( __( 'and a %s signup fee plan %s', 'braintree-payments' ), $signup_fee, $plan ) );
		}
		return apply_filters( 'bfwcs_get_recurring_cart_key', md5( str_replace( ' ', '_', $key ) ), $product, $index );
	}

	/**
	 *
	 * @param string $cart_item_key        	
	 * @param WC_Product $product_id        	
	 * @param int $variation_id        	
	 * @param array $variation        	
	 * @param array $cart_item_data        	
	 */
	public static function generate_cart_key( $cart_item_key, $product_id, $variation_id, $variation, $cart_item_data )
	{
		// subscriptions can't be combined.
		if ( bfwcs_product_is_subscription( $product_id ) && ! bfwcs_can_combine_subscriptions() ) {
			$cart_item_key = $cart_item_key . md5( uniqid() );
		}
		return $cart_item_key;
	}

	/**
	 * Generate a random cart key that makes it unique.
	 *
	 * @param string $cart_item_key        	
	 * @param WC_Product $product        	
	 * @return string
	 */
	public static function generate_recurring_cart_key( $cart_item_key, $product, $index )
	{
		if ( bfwcs_product_is_subscription( $product ) && ! bfwcs_can_combine_subscriptions() ) {
			$cart_item_key = $cart_item_key . md5( $index );
		}
		return $cart_item_key;
	}

	public static function cart_product_price( $price, $product )
	{
		if ( bfwcs_product_is_subscription( $product ) ) {
			$price = bfwcs_get_product_price_html( $product );
		}
		return $price;
	}

	/**
	 *
	 * @param string $product_subtotal        	
	 * @param WC_Product $_product        	
	 * @param int $quantity        	
	 * @param WC_Cart $cart        	
	 */
	public static function cart_product_subtotal( $product_subtotal, $_product, $quantity, $cart )
	{
		if ( bfwcs_product_is_subscription( $_product ) ) {
			$product_subtotal = bfwcs_get_product_price_html( $_product, '', $quantity );
		}
		return $product_subtotal;
	}

	/**
	 * Display html for the recurring cart totals portion of the cart.
	 */
	public static function cart_totals_after_order_total()
	{
		self::$recurring_total_calculation = true;
		
		if ( WC()->cart->recurring_carts ) {
			bwc_get_template( 'cart/cart-totals.php', array (
					'recurring_carts' => WC()->cart->recurring_carts 
			) );
		}
	}

	public static function add_to_cart_simple_template()
	{
		wc_get_template( 'single-product/add-to-cart/simple.php' );
	}

	public static function add_to_cart_variable_template()
	{
		global $product;
		
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		
		$get_variations = sizeof( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );
		
		wc_get_template( 'single-product/add-to-cart/variable.php', array (
				'available_variations' => $get_variations ? $product->get_available_variations() : false, 
				'attributes' => $product->get_variation_attributes(), 
				'selected_attributes' => bwc_is_wc_3_0_0_or_more() ? $product->get_default_attributes() : $product->get_variation_default_attributes() 
		) );
	}

	/**
	 * If the item being added is a Braintree subscription, validate that there is a plan configured for the currency.
	 *
	 * @param bool $valid        	
	 * @param int $product_id        	
	 * @param int $quantity        	
	 * @param number $variation_id        	
	 * @param array $variations        	
	 */
	public static function add_to_cart_validation( $valid, $product_id, $quantity, $variation_id = 0, $variations = array() )
	{
		$product_id = $variation_id ? $variation_id : $product_id;
		if ( bfwcs_product_is_subscription( $product_id ) ) {
			if ( ! $plan = bfwcs_get_plan_from_product( $product_id ) ) {
				$product = wc_get_product( $product_id );
				wc_add_notice( sprintf( __( 'Error: product %s cannot be purchased using currency %s.', 'braintree-payments' ), $product->get_title(), get_woocommerce_currency() ), 'error' );
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 *
	 * @param bool $needs_payment        	
	 * @param WC_Cart $cart        	
	 */
	public static function cart_needs_payment( $needs_payment, $cart )
	{
		if ( bfwcs_cart_contains_subscriptions() ) {
			$needs_payment = true;
		}
		return $needs_payment;
	}
}
WC_Braintree_Subscriptions_Cart::init();