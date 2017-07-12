<?php
use Braintree\WebhookNotification;
/**
 *
 * @author Payment Plugins
 * @copyright 2016
 */
class Braintree_Gateway_WCS_Controller
{

	public function __construct()
	{
		add_action( 'bfwc_validate_notification', array (
				$this, 
				'validate_webhook' 
		) );
		
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_CANCELED . '_subscription_controller', array (
				$this, 
				'subscription_cancelled' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY . '_subscription_controller', array (
				$this, 
				'charged_successfully' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY . '_subscription_controller', array (
				$this, 
				'charged_unsuccessfully' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_EXPIRED . '_subscription_controller', array (
				$this, 
				'subscription_expired' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_TRIAL_ENDED . '_subscription_controller', array (
				$this, 
				'subscription_trail_ended' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_WENT_ACTIVE . '_subscription_controller', array (
				$this, 
				'subscription_went_active' 
		), 10, 3 );
		add_action( 'bfwc_' . WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE . '_subscription_controller', array (
				$this, 
				'subscription_past_due' 
		), 10, 3 );
	
	}

	public static function init()
	{
		add_filter( 'bfwc_register_route_controllers', __CLASS__ . '::register_controller' );
	}

	public function get_notification_kinds()
	{
		return apply_filters( __CLASS__ . '_notification_kinds', array (
				WebhookNotification::SUBSCRIPTION_CANCELED => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_EXPIRED => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_TRIAL_ENDED => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_WENT_ACTIVE => array (
						$this, 
						'process_subscription_webhook' 
				), 
				WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE => array (
						$this, 
						'process_subscription_webhook' 
				) 
		) );
	}

	/**
	 * Validate the webhook notification.
	 *
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function validate_webhook( $notification )
	{
		switch( $notification->kind ) {
			case WebhookNotification::SUBSCRIPTION_CANCELED :
			case WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY :
			case WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY :
			case WebhookNotification::SUBSCRIPTION_EXPIRED :
			case WebhookNotification::SUBSCRIPTION_TRIAL_ENDED :
			case WebhookNotification::SUBSCRIPTION_WENT_ACTIVE :
			case WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE :
				global $bfwc_webhook_subscription_post;
				$id = absint( $notification->subscription->id );
				if ( ! $post = get_post( $id ) ) {
					throw new Exception( sprintf( __( 'Subscription %s was not found in your Wordpress database. 
							Subscription cancellation webhook could not be processed.', 'braintree-payments' ), $id ), 404 );
				}
				$bfwc_webhook_subscription_post = $post;
				break;
		}
	}

	/**
	 *
	 * @param array $controllers        	
	 */
	public static function register_controller( $controllers )
	{
		// only register this controller if WooCommerce Subscriptions is active.
		if ( bt_manager()->is_woocommerce_subscriptions_active() ) {
			$controllers [] = __CLASS__;
		}
		return $controllers;
	}

	/**
	 *
	 * @param WebhookNotification $notification        	
	 */
	public function process_subscription_webhook( $notification )
	{
		global $bfwc_api_message;
		
		if ( ! $wc_subscription = wcs_get_subscription( $notification->subscription->id ) ) {
			$bfwc_api_message = sprintf( __( 'Subscription %s not found in your database.', 'braintree-payments' ), $notification->subscription->id );
			throw new Exception( $bfwc_api_message, 200 );
		}
		
		do_action( "bfwc_{$notification->kind}_subscription_controller", $wc_subscription, $notification->subscription, $notification );
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function subscription_cancelled( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		
		if ( $wc_subscription->can_be_updated_to( 'cancelled' ) ) {
			$wc_subscription->update_status( 'cancelled' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status set to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'cancelled' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function charged_successfully( $wc_subscription, $subscription, $notification )
	{
		global $wpdb, $bfwc_api_message;
		$id = bwc_get_order_property( 'id', $wc_subscription );
		
		$order = is_callable( array (
				$wc_subscription, 
				'get_parent' 
		) ) ? $wc_subscription->get_parent() : $wc_subscription->order;
		$num_of_trans = count( $subscription->transactions );
		$transaction = $num_of_trans > 0 ? $subscription->transactions [ 0 ] : null;
		
		/* Subscription is currently processing via the process_order method of the gateway, so return rejection status. */
		if ( bwc_get_order_property( 'subscription_processing', $wc_subscription ) ) {
			$wc_subscription->add_order_note( __( 'Subscription still in processing state via customer checkout. 
					Webhook called before payment_complete() for order called. This can occur when Braintree\'s webhooks fire right after the Braintree subscription is created but the process_order() method on the frontend is still running. 409 status returned to Braintree. Webhook attempt from Braintree will be made again in one hour.' ) );
			throw new Exception( 'braintree subscription currently processing and payment_complete() not called on order yet.', 409 );
		}
		
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
		}
		
		if ( ! $transaction ) {
			$bfwc_api_message = sprintf( __( 'Subscription %s does not have any transactions associated with it yet. The 
					charged successfully webhook should only be called once a recurring payment has been processed by Braintree.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) );
			return;
		}
		
		/* If there is only one transaction, and the subscription does not have a trial and is not synced and doesn't have a transaction Id then don't create renewal order. */
		$create_order = $num_of_trans === 1 && ! bwcs_subscription_has_trial( $wc_subscription ) && ! bwcs_subscription_is_synched( $wc_subscription ) && $order && ! $order->get_transaction_id() ? false : true;
		
		if ( ! $create_order ) {
			update_post_meta( bwc_get_order_property( 'id', $wc_subscription ), '_transaction_id', $transaction->id );
			
			// Original order doesn't have a transaction so add it here.
			update_post_meta( bwc_get_order_property( 'id', $order ), '_transaction_id', $transaction->id );
			
			if ( ! $wc_subscription->has_status( 'active' ) ) {
				$wc_subscription->update_status( 'active' );
			}
			
			$wc_subscription->update_dates( array (
					'next_payment' => $wc_subscription->calculate_date( 'next_payment' ) 
			) );
			
			$bfwc_api_message = sprintf( __( 'Recurring payment for subscription %s charged by Braintree on %s.', 'braintree-payments' ), $subscription->id, $transaction->createdAt->format( 'Y-m-d H:i:s' ) );
		} else {
			
			/*
			 * Look for an existing order to make sure duplicates aren't created, resulting in incorrect financial reporting.
			 * Another cause for a duplicate could be during webhook testing.
			 */
			$results = $wpdb->get_row( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE post_id != %s AND meta_key = '_transaction_id' AND meta_value = %s", bwc_get_order_property( 'id', $wc_subscription ), $transaction->id ) );
			
			if ( $results ) {
				$renewal_order = wc_get_order( $results->post_id );
				if ( bwc_get_order_property( 'order', $wc_subscription ) && bwc_get_order_property( 'order', $wc_subscription )->id === $renewal_order->id ) {
					$bfwc_api_message = sprintf( __( 'Order %s already created for subscription %s. Renewal order will be created when recurring payment is processed on %s.', 'braintree-payments' ), $renewal_order->get_order_number(), $wc_subscription->get_order_number(), bwcs_get_subscription_date( 'next_payment', $wc_subscription ) );
				} else {
					$bfwc_api_message = sprintf( __( 'Renewal order %s already created for subscription %s.', 'braintree-payments' ), $renewal_order->get_order_number(), $wc_subscription->get_order_number() );
				}
				return;
			}
			// create a renewal order.
			$renewal_order = wcs_create_renewal_order( $wc_subscription );
			$renewal_order->add_order_note( sprintf( __( 'Recurring payment charged for subscription by Braintree on %s.', 'braintree-payments' ), $transaction->createdAt->format( 'Y-m-d H:i:s' ) ) );
			
			// save the payment method data for the renewal order.
			update_post_meta( bwc_get_order_property( 'id', $renewal_order ), '_payment_method', bwc_get_order_property( 'payment_method', $wc_subscription ) );
			update_post_meta( bwc_get_order_property( 'id', $renewal_order ), '_payment_method_title', bwc_get_order_property( 'payment_method_title', $wc_subscription ) );
			update_post_meta( bwc_get_order_property( 'id', $renewal_order ), '_payment_method_token', bwc_get_order_property( 'payment_method_token', $wc_subscription ) );
			
			$renewal_order->payment_complete( $transaction->id );
			
			// subscription started immediately and the order already has a transaction Id so subtract renewal order from original order.
			if ( $create_order && $num_of_trans === 1 ) {
				$order->set_total( $order->get_total() - $wc_subscription->get_total() );
			}
			
			$wc_subscription->update_dates( array (
					'next_payment' => $wc_subscription->calculate_date( 'next_payment' ) 
			) );
			
			$bfwc_api_message = sprintf( __( 'Renewal order %s created for subscription %s.', 'braintree-payments' ), $renewal_order->get_order_number(), $wc_subscription->get_order_number() );
		}
	}

	/**
	 * Update the subscription status for a failed renewal payment.
	 * The WC_Subscription status is changed to on-hold.
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function charged_unsuccessfully( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'on-hold' ) ) {
			$wc_subscription->update_status( 'on-hold' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status set to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'on-hold' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function subscription_past_due( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'on-hold' ) ) {
			$wc_subscription->update_status( 'on-hold' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status set to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'on-hold' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function subscription_expired( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'expired' ) ) {
			$wc_subscription->update_status( 'expired' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status set to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'expired' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function subscription_went_active( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status changed to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'active' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param Braintree_WebhookNotification $notification        	
	 */
	public function subscription_trail_ended( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status set to %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
		} else {
			$message = sprintf( __( 'Subscription %s status could not be changed to %s. Subscription\'s status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), wcs_get_subscription_status_name( 'active' ), wcs_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

}
Braintree_Gateway_WCS_Controller::init();