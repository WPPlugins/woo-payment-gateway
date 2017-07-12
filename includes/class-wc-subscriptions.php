<?php
if ( ! class_exists( 'WC_Braintree_Payment_Gateway' ) ) {
	return;
}

/**
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 */
class WC_Braintree_Gateway_Subscriptions extends WC_Braintree_Payment_Gateway
{
	/**
	 * Array of payment gateways used by Braintree.
	 *
	 * @var array
	 */
	public static $gateways = array (
			WC_Braintree_Payment_Gateway::ID, 
			WC_Paypal_Payment_Gateway::ID, 
			WC_Applepay_Payment_Gateway::ID 
	);
	
	/**
	 *
	 * @var WC_Braintree_Gateway_Subscriptions
	 */
	public static $_instance = null;
	
	private static $amount = 0;

	/**
	 * Add all necessary actions and filters.
	 */
	public static function add_subscription_actions()
	{
		foreach ( self::$gateways as $gateway ) {
			
			add_action( "woocommerce_scheduled_subscription_payment_{$gateway}", __CLASS__ . '::process_scheduled_payment', 10, 2 );
			
			add_action( "woocommerce_subscription_cancelled_{$gateway}", __CLASS__ . '::cancel_subscription' );
		}
	}

	public static function init()
	{
		add_filter( 'woocommerce_can_subscription_be_updated_to_new-payment-method', __CLASS__ . '::can_payment_method_be_changed', 99, 2 );
		
		add_filter( 'woocommerce_available_payment_gateways', __CLASS__ . '::availabe_payment_gateways' );
		
		add_filter( 'woocommerce_scheduled_subscription_payment', __CLASS__ . '::scheduled_subscription_payment', - 1000, 1 );
		
		add_filter( 'woocommerce_subscriptions_recurring_cart_key', __CLASS__ . '::subscriptions_recurring_cart_key', 10, 2 );
		
		// filter added to change the cart id in case like products should not be grouped.
		add_filter( 'woocommerce_cart_id', __CLASS__ . '::woocommerce_cart_id', 10, 5 );
		
		add_filter( 'woocommerce_add_cart_item', __CLASS__ . '::add_cart_item', 10, 2 );
		
		add_filter( 'woocommerce_update_cart_validation', __CLASS__ . '::update_cart_validation', 10, 4 );
		
		add_filter( 'woocommerce_add_to_cart_validation', __CLASS__ . '::add_to_cart_validation', 10, 5 );
		
		add_filter( 'braintree_wc_can_delete_payment_method', 'bwcs_can_delete_payment_method', 10, 2 );
		
		add_filter( 'bfwc_admin_can_delete_payment_method', 'bwcs_can_delete_payment_method', 10, 2 );
		
		add_action( 'woocommerce_checkout_subscription_created', __CLASS__ . '::checkout_subscription_created', 10, 3 );
		
		add_action( 'woocommerce_subscriptions_pre_update_payment_method', __CLASS__ . '::maybe_remove_cancel_subscription_on_payment_update', 10, 3 );
		
		add_filter( 'woocommerce_subscription_payment_method_to_display', __CLASS__ . '::payment_method_to_display', 10, 2 );
		
		add_action( 'wcs_after_renewal_setup_cart_subscriptions', __CLASS__ . '::maybe_set_cart_hash_for_renewal', 10, 2 );
		
		// this method adds actions related to a scheduled subscription payment.
		self::add_subscription_actions();
	}

	/**
	 * Process the recurring payment amount.
	 *
	 * @param float $amount        	
	 * @param WC_Order $order        	
	 */
	public static function process_scheduled_payment( $amount, $order )
	{
		$attribs = array (
				'amount' => $order->get_total(), 
				'taxAmount' => bwc_is_wc_3_0_0_or_more() ? wc_round_tax_total( $order->get_total_tax() ) : $order->get_total_tax(), 
				'paymentMethodToken' => bwc_get_order_property( 'payment_method_token', $order ), 
				'merchantAccountId' => bwc_get_order_property( 'merchant_account_id', $order ), 
				'recurring' => true 
		);
		self::add_order_id( $attribs, $order );
		self::add_customer( $attribs, $order );
		self::add_billing_address( $attribs, $order );
		self::add_shipping_address( $attribs, $order );
		self::add_options( $attribs, $order );
		self::add_descriptors( $attribs );
		self::add_partner_code( $attribs );
		
		$attribs = apply_filters( 'bwcs_scheduled_payment_attributes', $attribs, $order );
		
		try {
			$result = Braintree_Transaction::sale( $attribs );
			
			if ( $result->success ) {
				
				do_action( 'braintree_woocommerce_process_order_success', array (
						'order_id' => bwc_get_order_property( 'id', $order ), 
						'result' => $result 
				) );
				do_action( 'braintree_wc_' . bwc_get_order_property( 'payment_method', $order ) . '_save_wc_order_meta', bwc_get_order_property( 'id', $order ), $result->transaction );
				
				$order->payment_complete( $result->transaction->id );
				$order->add_order_note( sprintf( __( 'Renewal payment for subscription %s charged successfully.', 'braintree-payments' ), bwc_get_order_property( 'subscription_renewal', $order ) ) );
				
				do_action( 'braintree_gateway_wc_recurring_payment_success', bwc_get_order_property( 'subscription_renewal', $order ) );
			} else {
				
				$order->add_order_note( sprintf( __( 'Recurring payment for order %s failed. Reason: %s', 'braintree-payments' ), bwc_get_order_property( 'id', $order ), $result->message ) );
				
				do_action( 'braintree_woocommerce_process_order_error', array (
						'order_id' => bwc_get_order_property( 'id', $order ), 
						'result' => $result, 
						'method' => __METHOD__, 
						'line' => __LINE__ 
				) );
				
				do_action( 'braintree_gateway_wc_recurring_payment_failure', bwc_get_order_property( 'subscription_renewal', $order ) );
				
				$order->update_status( 'failed' ); // payment failed.
			}
		} catch( \Braintree\Exception $e ) {
			do_action( 'braintree_woocommerce_process_order_exception', array (
					'attribs' => $attribs, 
					'exception' => $e, 
					'order_id' => bwc_get_order_property( 'id', $order ), 
					'method' => __METHOD__, 
					'line' => __LINE__ 
			) );
			if ( bwcs_retry_after_exception() ) {
				/**
				 * Exceptions caused by authentication, authorization, network errors, timeout, etc can be encountered
				 * when trying to process a recurring payment.
				 * It is important that failed payments due to exceptions are
				 * handled correctly. The renewal order should be deleted and a new exception should be thrown which will
				 * be caught be the process_action() method of class ActionScheduler_QueueRunner. The process_action() method
				 * catches exceptions and marks the recurring_payment action as failed and a new recurring_payment is scheduled.
				 */
				$subscription = wcs_get_subscription( bwc_get_order_property( 'subscription_renewal', $order ) );
				
				$subscription->add_order_note( sprintf( __( 'An exception was thrown while processing payment for subscription. Exception: %s. Order %s will be deleted and a new renewal order will be created when the payment is tried again.', 'braintree-payments' ), get_class( $e ), $order->get_order_number() ) );
				
				// set status back to active since the subscription is always set to on-hold before a renewal order is processed.
				if ( $subscription->can_be_updated_to( 'active' ) ) {
					$subscription->update_status( 'active' );
				}
				
				wp_delete_post( bwc_get_order_property( 'id', $order ) ); // delete the renewal order so a new one is created the next time the action runs.
				
				throw new Exception( sprintf( __( 'Subscription %s recurring payment failed. An exception was thrown while making a connection to Braintree. Exception: %s', 'braintree-payments' ), $subscription->get_order_number(), get_class( $e ) ) ); // throw an exception so the action will be rescheduled.
			} else {
				$order->add_order_note( sprintf( __( 'Recurring payment failed Exception thrown while payment was being processed. Exception: %s', 'braintree-payments' ), get_class( $e ) ) );
				$order->update_status( 'failed' );
			}
		}
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 */
	public static function cancel_subscription( $subscription )
	{
		
		if ( ! bwcs_is_braintree_subscription( $subscription ) || bwc_is_admin_webhook_request() ) {
			return;
		}
		
		$id = bwc_get_order_property( 'id', $subscription );
		try {
			$result = Braintree_Subscription::cancel( $id );
			
			if ( $result->success ) {
				
				$message = sprintf( __( 'Subscription cancelled in Braintree environment.', 'braintree-payments' ) );
				$subscription->add_order_note( $message );
				
				do_action( 'braintree_wcs_subscription_cancelled_success', $result, $subscription );
			} else {
				
				do_action( 'braintree_wcs_subscription_cancelled_error', $result, $subscription );
				
				$subscription->add_order_note( sprintf( __( 'Subscription could not be cancelled. Reason: %s', 'braintree-payments' ), $result->message ) );
				
				// add notice for front end user.
				if ( ! is_admin() && function_exists( 'wc_add_notice' ) ) {
					wc_add_notice( sprintf( __( 'Your subscription could not be cancelled at this time. Reason: %s', $result->message ), 'braintree-payments' ), 'error' );
				}
				throw new Exception();
			}
		} catch( \Braintree\Exception $e ) {
			do_action( 'braintree_wcs_subscription_cancelled_exception', array (
					'subscription_id' => $id, 
					'method' => __METHOD__, 
					'line' => __LINE__, 
					'exception' => $e 
			) );
			if ( ! is_admin() && function_exists( 'wc_add_notice' ) ) {
				$message = $e instanceof \Braintree\Exception\NotFound ? sprintf( __( 'Subscription %s was not found in the recurring billing engine.', 'braintree-payments' ), $id ) : __( 'There was an error cancelling your subscription.', 'braintree-payments' );
				wc_add_notice( $message, 'error' );
			}
			throw new Exception( __( 'There was an error cancelling your subscription.', 'braintree-payments' ), 'subscription-cancellation' );
		}
	}

	/**
	 * If the cart_item contains a Braintree Subscription, modify the cart key so it is unique.
	 * This will
	 * result in a WC Subscription being generated for each item instead of combining them. This has the benefit
	 * of allowing a 1:1 association between WC Subscription and Braintree Subscriptions.
	 *
	 * @param string $cart_key        	
	 * @param array $cart_item        	
	 */
	public static function subscriptions_recurring_cart_key( $cart_key, $cart_item )
	{
		if ( bwcs_cart_item_contains_subscription( $cart_item ) && ! bwcs_can_combine_subscriptions() ) {
			$cart_key = sprintf( '%s_%s', $cart_key, md5( uniqid() ) );
		}
		return $cart_key;
	}

	/**
	 * If a product is being added to the cart and it's a Braintree Subscription, create a unique cart key
	 * to prevent cart items from having a quantity greater than one.
	 *
	 * @param string $cart_item_key        	
	 * @param int $product_id        	
	 * @param int $variation_id        	
	 * @param array $variation        	
	 * @param array $cart_item_data        	
	 */
	public static function woocommerce_cart_id( $cart_item_key, $product_id, $variation_id, $variation, $cart_item_data )
	{
		$id = $variation_id ? $variation_id : $product_id; // Use one product id.
		
		if ( bwcs_product_is_subscription( $id ) && ! bwcs_can_combine_subscriptions() ) {
			
			$cart_item_key = sprintf( '%s%s', $cart_item_key, md5( uniqid() ) );
		}
		
		return $cart_item_key;
	}

	/**
	 * If same products cannot be added, change quantity to one.
	 *
	 * @param array $cart_data        	
	 * @param string $cart_item_key        	
	 */
	public static function add_cart_item( $cart_data, $cart_item_key )
	{
		// product id is either for a variation or product.
		$product_id = $cart_data [ 'variation_id' ] ? $cart_data [ 'variation_id' ] : $cart_data [ 'product_id' ];
		
		$product = wc_get_product( $product_id );
		$quantity = $cart_data [ 'quantity' ];
		
		if ( bwcs_product_is_subscription( $product ) && ! bwcs_can_combine_subscriptions() && $quantity > 1 ) {
			$cart_data [ 'quantity' ] = 1;
			
			// change the message sent to the customer.
			add_filter( 'wc_add_to_cart_message', function ( $message, $product_id )
			{
				$message = preg_replace( '/[\d]+/', '1', $message );
				return sprintf( '%s %s', $message, esc_html( __( 'This product can only be added one at a time.', 'braintree-payments' ) ) );
			}, 10, 2 );
		
		}
		
		return $cart_data;
	}

	/**
	 * Ensure more than one item is not being added to the cart at once if combined subscriptions is not enabled.
	 *
	 * @param bool $valid        	
	 * @param string $cart_item_key        	
	 * @param array $values        	
	 * @param int $quantity        	
	 */
	public static function update_cart_validation( $valid, $cart_item_key, $values, $quantity )
	{
		$product_id = $values [ 'variation_id' ] ? $values [ 'variation_id' ] : $values [ 'product_id' ];
		
		if ( bwcs_product_is_subscription( $product_id ) && ! bwcs_can_combine_subscriptions() && $quantity > 1 ) {
			$product = wc_get_product( $product_id );
			wc_add_notice( sprintf( __( 'Cart was not updated. Product %s must be sold individually', 'braintree-payments' ), $product->get_title() ), 'error' );
			$valid = false;
		}
		return $valid;
	}

	/**
	 * Validate the items being added to the cart.
	 *
	 * @param bool $valid        	
	 * @param int $product_id        	
	 * @param unknown $quantity        	
	 */
	public static function add_to_cart_validation( $valid, $product_id, $quantity, $variation_id = 0, $variations = array() )
	{
		$product_id = $variation_id ? $variation_id : $product_id;
		if ( bwcs_product_is_subscription( $product_id ) ) {
			if ( ! $plan = bwcs_get_plan_from_product( $product_id ) ) {
				$product = wc_get_product( $product_id );
				wc_add_notice( sprintf( __( 'Error: product %s cannot be purchased using currency %s.', 'braintree-payments' ), $product->get_title(), get_woocommerce_currency() ), 'error' );
				$valid = false;
			}
		}
		return $valid;
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 * @param WC_Order $order        	
	 * @param WC_Cart $recurring_cart        	
	 */
	public static function checkout_subscription_created( $subscription, $order, $recurring_cart )
	{
		$product = bwcs_get_product_from_subscription( $subscription );
		
		// update the subscription type if the subscription is a Braintree Subscription.
		if ( bwcs_product_is_subscription( $product ) ) {
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_subscription_type', 'braintree' );
		}
	}

	/**
	 *
	 * @param bool $can_be_changed        	
	 * @param WC_Subscription $subscription        	
	 */
	public static function can_payment_method_be_changed( $can_be_changed, $subscription )
	{
		if ( bwcs_is_braintree_subscription( $subscription ) ) {
			$can_be_changed = true;
		}
		return $can_be_changed;
	}

	/**
	 * If the WC Subscription is a Braintree subscription, then remove all payment methods except those that
	 * support Braintree subscriptions.
	 *
	 * @param unknown $available_gateways        	
	 */
	public static function availabe_payment_gateways( $available_gateways )
	{
		if ( bwcs_is_change_payment_method() ) {
			$subscription_id = absint( $_REQUEST [ 'change_payment_method' ] );
			if ( $subscription_id ) {
				$subscription = wcs_get_subscription( $subscription_id );
				if ( bwcs_is_braintree_subscription( $subscription ) ) {
					foreach ( $available_gateways as $id => $gateway ) {
						if ( ! $gateway instanceof WC_Braintree_Payment_Gateway ) {
							unset( $available_gateways [ $id ] );
						}
					}
				}
			}
		}
		return $available_gateways;
	}

	/**
	 * This method removes all hooks associated with the 'woocommerce_scheduled_subscription_payment' hook.
	 * This is needed because Braintree
	 * subscriptions are processed by Braintree and then a webhook notification is sent updating the site when the transaction is processed.
	 * That is when the renewal order should be created.
	 *
	 * @param unknown $subscription_id        	
	 */
	public static function scheduled_subscription_payment( $subscription_id )
	{
		if ( bwcs_is_braintree_subscription( $subscription_id ) ) {
			// This is a Braintree subscription so don't allow subscriptions created by the old plugin version to process. This is because
			// all renewal orders will be created when the webhook is called.
			remove_action( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Manager::prepare_renewal', 1, 1 );
			remove_action( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Manager::maybe_process_failed_renewal_for_repair', 0, 1 );
			remove_action( 'woocommerce_scheduled_subscription_payment', 'WC_Subscriptions_Payment_Gateways::gateway_scheduled_subscription_payment', 10, 1 );
		}
	
	}

	public static function payment_gateway_supports( $supports_feature, $feature, $subscription )
	{
		if ( $feature === 'gateway_scheduled_payments' && bwcs_is_braintree_subscription( $subscription ) ) {
			$supports_feature = true;
		}
		return $supports_feature;
	}

	/**
	 *
	 * @param WC_Subscription $subscription        	
	 * @param string $new_payment_method
	 *        	id of the new payment gateway for the subscription.
	 * @param string $old_payment_method
	 *        	id of the old payment gateway for the subscription.
	 */
	public static function maybe_remove_cancel_subscription_on_payment_update( $subscription, $new_payment_method, $old_payment_method )
	{
		// if the subscription is a Braintree subscription or this is a request to update a payment method on a failed order.
		if ( bwcs_is_braintree_subscription( $subscription ) || bwcs_is_paid_for_failed_renewal_request() ) {
			remove_action( 'woocommerce_subscription_cancelled_' . bwc_get_order_property( 'payment_method', $subscription ), __CLASS__ . '::cancel_subscription' );
		}
	}

	public static function payment_method_to_display( $payment_method_to_display, $subscription )
	{
		if ( bwc_get_order_property( 'payment_method_title', $subscription ) ) {
			$payment_method_to_display = bwc_get_order_property( 'payment_method_title', $subscription );
		}
		return $payment_method_to_display;
	}

	/**
	 * There appears to be a bug in WC Subscriptions introduced in later versions of WooCommerce.
	 * In the create_order() method
	 * of class WC_Checkout, the order_awaiting_payment is fetched and the cart_hash between the order_data and the order_awaiting_payment is compared.
	 * These values are not matching up so a new order is created. That is not good so this code is put in place to prevent that from happening.
	 *
	 * @param array $subscriptions        	
	 * @param int $order_id        	
	 */
	public static function maybe_set_cart_hash_for_renewal( $subscriptions, $order )
	{
		// only update orders that have a failed status.
		if ( $order->has_status( 'failed' ) ) {
			$hash = md5( json_encode( wc_clean( WC()->cart->get_cart_for_session() ) ) . WC()->cart->total );
			update_post_meta( bwc_get_order_property( 'id', $order ), '_cart_hash', $hash );
		}
	}

	public static function add_options( &$attribs, $order )
	{
		$attribs [ 'options' ] = array (
				'submitForSettlement' => ! bt_manager()->is_active( 'authorize_transaction' ) 
		);
		return $attribs;
	}

}
WC_Braintree_Gateway_Subscriptions::init();