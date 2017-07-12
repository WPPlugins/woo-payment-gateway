<?php
class Braintree_Gateway_WC_Subscriptions_Checkout
{

	public function __construct()
	{
	
	}

	public static function init()
	{
		add_action( 'woocommerce_checkout_order_processed', __CLASS__ . '::process_checkout', 1000, 2 );
		
		add_action( 'woocommerce_review_order_after_order_total', __CLASS__ . '::after_order_total' );
		
		add_action( 'woocommerce_before_pay_action', __CLASS__ . '::pay_order_action' );
	}

	/**
	 *
	 * @param int $order_id        	
	 * @param array $posted_data        	
	 */
	public static function process_checkout( $order_id, $posted_data )
	{
		$order = wc_get_order( $order_id );
		
		if ( ! bfwcs_cart_contains_subscriptions() ) {
			return;
		}
		
		$subscriptions = bfwcs_get_subscriptions_for_order( $order_id );
		
		$recurring_carts = WC()->cart->recurring_carts;
		
		foreach ( $subscriptions as $subscription ) {
			if ( $subscription->is_created() ) {
				
				// unset the recurring cart associated with this subscription to prevent duplicate processing.
				unset( $recurring_carts [ bwc_get_order_property( 'recurring_cart_key', $subscription ) ] );
			} else {
				// delete any existing subscriptions associated with the order that haven't been created in Braintree yet.
				wp_delete_post( bwc_get_order_property( 'id', $subscription ) );
			}
		}
		
		foreach ( $recurring_carts as $key => $recurring_cart ) {
			self::create_subscription( $order, $recurring_cart, $key );
		}
	}

	/**
	 * Create the subscription object for the order.
	 *
	 * @param WC_Order $order        	
	 * @param WC_Cart $recurring_cart        	
	 * @param string $cart_key        	
	 */
	public static function create_subscription( $order, $recurring_cart, $recurring_cart_key )
	{
		$format = 'Y-m-d H:i:s';
		$subscription = bfwcs_create_subscription( array (
				'order_id' => bwc_get_order_property( 'id', $order ), 
				'start_date' => $recurring_cart->start_date->format( $format ), 
				'subscription_trial_length' => $recurring_cart->subscription_trial_length, 
				'subscription_trial_period' => $recurring_cart->subscription_trial_period, 
				'trial_end_date' => $recurring_cart->trial_end_date->format( $format ), 
				'next_payment_date' => $recurring_cart->next_payment_date->format( $format ), 
				'first_payment_date' => $recurring_cart->first_payment_date->format( $format ), 
				'end_date' => $recurring_cart->end_date ? $recurring_cart->end_date->format( $format ) : 0, 
				'braintree_plan' => $recurring_cart->braintree_plan, 
				'subscription_period' => $recurring_cart->subscription_period, 
				'subscription_period_interval' => $recurring_cart->subscription_period_interval, 
				'subscription_length' => $recurring_cart->subscription_length, 
				'subscription_time_zone' => $recurring_cart->subscription_time_zone, 
				'merchant_account_id' => $recurring_cart->merchant_account_id, 
				'descriptors' => $recurring_cart->descriptors, 
				'customer_user' => $order->get_user_id(), 
				'order_currency' => get_woocommerce_currency(), 
				'order_note' => bwc_get_order_property( 'customer_note', $order ) 
		) );
		
		if ( is_wp_error( $subscription ) ) {
			throw new Exception( $subscription->get_error_message() );
		}
		
		// save the recurring cart key to the subscription.
		update_post_meta( bwc_get_order_property( 'id', $subscription ), '_recurring_cart_key', $recurring_cart_key );
		
		// update the billing and shipping addresses.
		$subscription = bfwc_copy_address_from_order( $order, $subscription );
		
		// add the line items
		
		if ( bwc_is_wc_3_0_0_or_more() ) {
			WC()->checkout()->create_order_line_items( $subscription, $recurring_cart );
			WC()->checkout()->create_order_fee_lines( $subscription, $recurring_cart );
			WC()->checkout()->create_order_shipping_lines( $subscription, self::get_chosen_shipping_methods( $recurring_cart_key ), self::get_shipping_packages( $recurring_cart_key ) );
			WC()->checkout()->create_order_tax_lines( $subscription, $recurring_cart );
			WC()->checkout()->create_order_coupon_lines( $subscription, $recurring_cart );
		} else {
			foreach ( $recurring_cart->get_cart() as $cart_key => $cart_item ) {
				$subscription->add_product( $cart_item [ 'data' ], $cart_item [ 'quantity' ], array (
						'variation' => $cart_item [ 'variation' ], 
						'total' => array (
								'subtotal' => $cart_item [ 'line_subtotal' ], 
								'subtotal_tax' => $cart_item [ 'line_subtotal_tax' ], 
								'total' => $cart_item [ 'line_total' ], 
								'tax' => $cart_item [ 'line_tax' ], 
								'tax_data' => $cart_item [ 'line_tax_data' ] 
						) 
				) );
			}
			// add fees
			foreach ( $recurring_cart->get_fees() as $fee_key => $fee ) {
				if ( ! $fee_id = $subscription->add_fee( $fee ) ) {
					throw new Exception( sprintf( __( 'Error %d: Subscription cannot be created.', 'braintree-payments' ), 403 ) );
				}
			}
			
			// add coupons
			foreach ( $recurring_cart->get_coupons() as $code => $coupon ) {
				if ( ! $coupon_id = $subscription->add_coupon( $code, $recurring_cart->get_coupon_discount_amount( $code ), $recurring_cart->get_coupon_discount_tax_amount( $code ) ) ) {
					throw new Exception( sprintf( __( 'Error adding coupons: Subscription cannot be created. Please try again.', 'braintree-payments' ) ) );
				}
			}
			
			// add taxes
			foreach ( $recurring_cart->get_taxes() as $tax_rate_id => $amount ) {
				if ( $tax_rate_id && ! $subscription->add_tax( $tax_rate_id, $recurring_cart->get_tax_amount( $tax_rate_id ), $recurring_cart->get_shipping_tax_amount( $tax_rate_id ) ) && apply_filters( 'woocommerce_cart_remove_taxes_zero_rate_id', 'zero-rated' ) !== $tax_rate_id ) {
					throw new Exception( sprintf( __( 'Error adding taxes: Unable to create subscription. Please try again.', 'braintree-payments' ) ) );
				}
			}
		}
		
		$gateways = WC()->payment_gateways()->get_available_payment_gateways();
		
		if ( isset( $gateways [ bwc_get_order_property( 'payment_method', $order ) ] ) ) {
			$subscription->set_payment_method( $gateways [ bwc_get_order_property( 'payment_method', $order ) ] );
		}
		
		if ( bwc_is_wc_3_0_0_or_more() ) {
			$subscription->set_shipping_total( $recurring_cart->shipping_total );
			$subscription->set_discount_total( $recurring_cart->get_cart_discount_total() );
			$subscription->set_discount_tax( $recurring_cart->get_cart_discount_tax_total() );
			$subscription->set_cart_tax( $recurring_cart->tax_total );
			$subscription->set_shipping_tax( $recurring_cart->shipping_tax_total );
			$subscription->set_total( $recurring_cart->total );
			$subscription->save();
		} else {
			$subscription->set_total( $recurring_cart->shipping_total, 'shipping' );
			$subscription->set_total( $recurring_cart->get_cart_discount_total(), 'cart_discount' );
			$subscription->set_total( $recurring_cart->get_cart_discount_tax_total(), 'cart_discount_tax' );
			$subscription->set_total( $recurring_cart->tax_total, 'tax' );
			$subscription->set_total( $recurring_cart->shipping_tax_total, 'shipping_tax' );
			$subscription->set_total( $recurring_cart->total );
		}
		
		do_action( 'bfwc_subscription_created', $subscription, $order );
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public static function pay_order_action( $order )
	{
		self::process_checkout( $order->id, array () );
	}

	public static function after_order_total()
	{
		$recurring_carts = WC()->cart->recurring_carts;
		if ( $recurring_carts ) {
			bwc_get_template( 'checkout/review-order.php', array (
					'recurring_carts' => $recurring_carts 
			) );
		}
	}

	/**
	 *
	 * @since 2.6.2
	 * @param string $cart_key        	
	 */
	public static function get_chosen_shipping_methods( $cart_key )
	{
		$shipping_data = WC()->session->get( 'bfwcs_shipping_data_' . $cart_key, array () );
		unset( $shipping_data [ 'packages' ] );
		return $shipping_data;
	}

	public static function get_shipping_packages( $cart_key )
	{
		$shipping_data = WC()->session->get( 'bfwcs_shipping_data_' . $cart_key, array () );
		unset( $shipping_data [ 'packages' ] );
		return isset( $shipping_data [ 'packages' ] ) ? $shipping_data [ 'packages' ] : array ();
	}

}
Braintree_Gateway_WC_Subscriptions_Checkout::init();