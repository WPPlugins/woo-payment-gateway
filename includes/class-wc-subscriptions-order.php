<?php
/**
 * 
 * @author Payment Plugins
 * @copyright 2016
 *
 */
class Braintree_Gateway_WC_Subscriptions_Order
{

	public static function init()
	{
		add_action( 'woocommerce_order_status_changed', __CLASS__ . '::maybe_payment_complete', 10, 3 );
		
		add_action( 'woocommerce_order_details_after_order_table', __CLASS__ . '::order_details_template' );
	}

	public static function maybe_payment_complete( $order_id, $old_status, $new_status )
	{
		if ( ! bfwcs_order_contains_subscription( $order_id ) ) {
			return;
		}
		
		$complete_statuses = apply_filters( 'bfwcs_payment_complete_statuses', array ( 
				'completed', 
				'processing' 
		) );
		
		$payment_complete = in_array( $new_status, $complete_statuses );
		
		if ( $payment_complete ) {
			$subscriptions = bfwcs_get_subscriptions_for_order( $order_id );
			
			foreach ( $subscriptions as $subscription ) {
				$subscription->payment_complete();
			}
		}
	}

	/**
	 *
	 * @param WC_Order $order 
	 */
	public static function order_details_template( $order )
	{
		if ( bfwcs_order_contains_subscription( $order ) ) {
			$subscriptions = bfwcs_get_subscriptions_for_order( $order );
			bwc_get_template( 'order/order-details.php', array ( 
					'subscriptions' => $subscriptions 
			) );
		}
	}
}
Braintree_Gateway_WC_Subscriptions_Order::init();