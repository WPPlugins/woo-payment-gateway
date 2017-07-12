<?php
use Braintree\WebhookNotification;
class Braintree_Gateway_Subscription_Controller
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
				'subscription_trial_ended' 
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

	public static function register_controller( $controllers )
	{
		// only register this controller if WooCommerce Subscriptions is not active.
		if ( ! bt_manager()->is_woocommerce_subscriptions_active() ) {
			$controllers [] = __CLASS__;
		}
		return $controllers;
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

	public function process_subscription_webhook( $notification )
	{
		$wc_subscription = bfwcs_get_subscription( $notification->subscription->id );
		
		do_action( "bfwc_{$notification->kind}_subscription_controller", $wc_subscription, $notification->subscription, $notification );
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function subscription_cancelled( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		
		// subscription has been cancalled in Braintree.
		if ( $wc_subscription->can_be_updated_to( 'cancelled' ) ) {
			
			$wc_subscription->update_status( 'cancelled' );
			$bfwc_api_message = sprintf( __( 'Subscription %s has been cancelled.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) );
		} else {
			$message = sprintf( __( 'Subscription %s could not be set to status %s. Current status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), bfwc_get_subscription_status_name( 'cancelled' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function charged_successfully( $wc_subscription, $subscription, $notification )
	{
		global $wpdb, $bfwc_api_message;
		$transactions = $subscription->transactions;
		$transaction = count( $transactions ) > 0 ? $transactions [ 0 ] : null;
		
		if ( ! $wc_subscription->can_be_updated_to( 'active' ) ) {
			$message = sprintf( __( 'Subscription %s recurring payment cannot be recorded. Subscription status is %s', 'braintree-payments' ), $wc_subscription->get_order_number(), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			return;
		}
		
		/* Subscription is currently processing via the process_order method of the gateway, so return rejection status. */
		if ( bwc_get_order_property( 'subscription_processing', $wc_subscription ) ) {
			$wc_subscription->add_order_note( __( 'Subscription still in processing state via customer checkout. 
					Webhook called before payment_complete() for order called. This can occur when Braintree\'s webhooks fire right after the Braintree subscription is created but the process_order() method on the frontend is still running. 409 status returned to Braintree. Webhook attempt from Braintree will be made again in one hour.' ) );
			throw new Exception( 'braintree subscription currently processing and payment_complete() not called on order yet.', 409 );
		}
		
		if ( ! $transaction ) {
			$bfwc_api_message = sprintf( __( 'Subscription %s does not have any transactions associated with it yet. The
					charged successfully webhook should only be called once a recurring payment has been processed by Braintree.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) );
			return;
		}
		
		$next_billing_date = clone $subscription->nextBillingDate;
		
		$start_date = $wc_subscription->get_date( 'start' );
		$start_date->setTimezone( new DateTimeZone( bwc_get_order_property( 'subscription_time_zone', $wc_subscription ) ? bwc_get_order_property( 'subscription_time_zone', $wc_subscription ) : 'UTC' ) );
		// Set the hour, minutes, and seconds to what they were in teh start date. Braintree always returns a 00:00:00 for the hours, minutes, and seconds.
		$next_billing_date->setTime( $start_date->format( 'H' ), $start_date->format( 'i' ), $start_date->format( 's' ) );
		
		// check if there is an order with this transaction.
		$has_transaction = $wpdb->get_row( $wpdb->prepare( "SELECT ID FROM $wpdb->posts as posts INNER JOIN $wpdb->postmeta as postmeta ON postmeta.post_id = posts.ID WHERE posts.post_type = 'shop_order' AND postmeta.meta_key = '_transaction_id' AND postmeta.meta_value = %s", $transaction->id ) );
		
		if ( $has_transaction ) {
			$wc_subscription->update_date( 'next_payment', $next_billing_date );
			$wc_subscription->update_date( 'last_payment', $transaction->createdAt );
			$wc_subscription->update_status( 'active' );
			$bfwc_api_message = sprintf( __( 'Order %s is already associated with transaction %s. No renewal order created.', 'braintree-payments' ), $has_transaction->ID, $transaction->id );
			return;
		}
		
		// first transaction for a subscription that started immediately.
		if ( count( $transactions ) === 1 && ! $wc_subscription->has_trial() && bwc_get_order_property( 'order', $wc_subscription ) ) {
			
			// get the number of subscriptions associated with the original order.
			$subscription_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_parent = %d AND post_type = 'bfwc_subscription'", bwc_get_order_property( 'id', bwc_get_order_property( 'order', $wc_subscription ) ) ) );
			
			// if there is no transaction and this subscription was the only one for the order.
			if ( ! $wc_subscription->order->get_transaction_id() && $subscription_count < 2 ) {
				update_post_meta( bwc_get_order_property( 'id', bwc_get_order_property( 'order', $wc_subscription ) ), '_transaction_id', $transaction->id );
				$wc_subscription->add_order_note( sprintf( __( 'Webhook received. Recurring payment charged.', 'braintree-payments' ) ) );
				$bfwc_api_message = sprintf( __( 'Recurring payment charged for subscription %s.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) );
			} else {
				/**
				 * There is already a transaction associated with the orginal order and there were multiple subscriptions possibly.
				 *
				 * @var WC_Order $renewal_order
				 */
				$renewal_order = bfwcs_create_renewal_order( bwc_get_order_property( 'id', $wc_subscription ) );
				$renewal_order->payment_complete( $transaction->id );
				$renewal_order->add_order_note( sprintf( __( 'Renewal payment for subscription %s charged.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) ) );
				$bfwc_api_message = sprintf( __( 'Recurring payment for subscription %s charged. Renewal order %s created.', 'braintree-payments' ), $wc_subscription->id, $renewal_order->get_order_number() );
				if ( $wc_subscription->order->get_total() > 0 ) {
					$wc_subscription->order->set_total( $wc_subscription->order->get_total() - $renewal_order->get_total() );
				}
			}
		} else {
			$renewal_order = bfwcs_create_renewal_order( bwc_get_order_property( 'id', $wc_subscription ) );
			$renewal_order->payment_complete( $transaction->id );
			$renewal_order->add_order_note( sprintf( __( 'Renewal payment for subscription %s charged.', 'braintree-payments' ), bwc_get_order_property( 'id', $wc_subscription ) ) );
			$bfwc_api_message = sprintf( __( 'Recurring payment for subscription %s charged. Renewal order %s created.', 'braintree-payments' ), $wc_subscription->id, $renewal_order->get_order_number() );
		}
		
		// update the next payment date since we got this far.
		$wc_subscription->update_date( 'next_payment', $next_billing_date );
		$wc_subscription->update_date( 'last_payment', $transaction->createdAt );
		
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
		}
	}

	/**
	 * Set the subscription status to on-hold since the recurring payment failed.
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function charged_unsuccessfully( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'past-due' ) ) {
			$wc_subscription->update_status( 'past-due' );
			$bfwc_api_message = sprintf( __( 'Subscription %s status changed to %s due to a failed recurring payment.', 'braintree-payments' ), $wc_subscription->get_order_number(), bfwc_get_subscription_status_name( 'past-due' ) );
		} else {
			$message = sprintf( __( 'Subscription %s could not be set to status %s. Current status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), bfwc_get_subscription_status_name( 'past-due' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function subscription_expired( $wc_subscription, $subscription, $notification )
	{
		global $wpdb, $bfwc_api_message;
		
		if ( $wc_subscription->can_be_updated_to( 'expired' ) ) {
			$wc_subscription->update_status( 'expired' );
			$bfwc_api_message = sprintf( __( 'Subscription %s has been expired.', 'braintree-payments' ), $wc_subscription->get_order_number() );
		} else {
			$message = sprintf( __( 'Subscription %s could not be set to status %s. Current status is %s.', 'braintree-payments' ), $wc_subscription->get_order_number(), bfwc_get_subscription_status_name( 'expired' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function subscription_went_active( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
			$bfwc_api_message = sprintf( __( 'Subscription %s is now active.', 'braintree-payments' ), $wc_subscription->get_order_number() );
			$wc_subscription->add_order_note( __( 'Braintree subscription has gone active.', 'braintree-payments' ) );
		} else {
			$message = sprintf( __( 'Braintree subscription cannot be updated to status %s. Current status is %s.', 'braintree-payments' ), bfwc_get_subscription_status_name( 'active' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function subscription_past_due( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'past-due' ) ) {
			$wc_subscription->update_status( 'past-due' );
			$bfwc_api_message = sprintf( __( 'Subscription %s is now Past Due.', 'braintree-payments' ), $wc_subscription->get_order_number() );
		} else {
			$message = sprintf( __( 'Braintree subscription cannot be updated to status %s. Current status is %s.', 'braintree-payments' ), bfwc_get_subscription_status_name( 'past-due' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	}

	/**
	 *
	 * @param Braintree_Gateway_WC_Subscription $wc_subscription        	
	 * @param Braintree_Subscription $subscription        	
	 * @param WebhookNotification $notification        	
	 */
	public function subscription_trial_ended( $wc_subscription, $subscription, $notification )
	{
		global $bfwc_api_message;
		if ( $wc_subscription->can_be_updated_to( 'active' ) ) {
			$wc_subscription->update_status( 'active' );
			$bfwc_api_message = sprintf( __( 'Subscription %s is now Active.', 'braintree-payments' ), $wc_subscription->get_order_number() );
		} else {
			$message = sprintf( __( 'Braintree subscription cannot be updated to status %s. Current status is %s.', 'braintree-payments' ), bfwc_get_subscription_status_name( 'active' ), bfwc_get_subscription_status_name( $wc_subscription->get_status() ) );
			$bfwc_api_message = $message;
			$wc_subscription->add_order_note( $message );
		}
	
	}

}
Braintree_Gateway_Subscription_Controller::init();