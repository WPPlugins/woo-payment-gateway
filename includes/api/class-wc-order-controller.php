<?php
use Braintree\WebhookNotification;
class Braintree_Gateway_WC_Order_Controller
{

	public function __construct()
	{
		add_action( 'bfwc_validate_notification', array ( 
				$this, 
				'validate_webhook' 
		) );
		
		/* add_action( 'bfwc_' . WebhookNotification::TRANSACTION_SETTLED . '_order_controller', array ( 
				$this, 
				'transaction_settled' 
		), 10, 2 ); */
		/* add_action( 'bfwc_' . WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED . '_order_controller', array ( 
				$this, 
				'transaction_settlement_declined' 
		), 10, 2 ); */
		add_action( 'bfwc_' . WebhookNotification::DISBURSEMENT . '_order_controller', array ( 
				$this, 
				'disbursement' 
		), 10, 2 );
	}

	public static function init()
	{
		add_filter( 'bfwc_register_route_controllers', __CLASS__ . '::register_controller' );
	}

	public static function register_controller( $controllers )
	{
		$controllers[] = __CLASS__;
		return $controllers;
	}

	public function get_notification_kinds()
	{
		return apply_filters( __CLASS__ . '_notification_kinds', array ( 
				WebhookNotification::TRANSACTION_SETTLED => array ( 
						$this, 
						'process_transaction_webhook' 
				), 
				WebhookNotification::TRANSACTION_DISBURSED => array ( 
						$this, 
						'process_transaction_webhook' 
				), 
				WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED => array ( 
						$this, 
						'process_transaction_webhook' 
				) 
		) );
	}

	/**
	 *
	 * @param WebhookNotification $notification 
	 */
	public function validate_webhook( $notification )
	{
		switch ($notification->kind) {
			case WebhookNotification::TRANSACTION_SETTLED :
			case WebhookNotification::TRANSACTION_DISBURSED :
			case WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED :
				global $wpdb, $bfwc_api_message;
				$transaction = $notification->transaction;
				
				// Fetch the order_id or subscription_id from the postmeta table using the transction id.
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_transaction_id' AND meta_value = %s", $transaction->id ) );
				
				if ( ! $row ) {
					throw new Exception( sprintf( __( 'Transaction %s does not have a WooCommerce order associated with it in your database.', 'braintree-payments' ), $transaction->id ), 404 );
				}
				break;
		}
	}

	public function process_transaction_webhook( $notification )
	{
		global $wpdb;
		$transaction = $notification->transaction;
		
		// Fetch the order_id from the postmeta table using the transction id.
		$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->posts AS posts INNER JOIN $wpdb->postmeta AS postmeta ON posts.ID = postmeta.post_id WHERE meta_key = '_transaction_id' AND meta_value = %s", $transaction->id ) );
		
		$order_id = $row->post_type === 'shop_subscription' ? $row->post_parent : $row->post_id;
		$order = wc_get_order( $order_id );
		
		do_action( "bfwc_{$notification->kind}_order_controller", $order, $notification );
	}

	/**
	 *
	 * @param WC_Order $order 
	 * @param WebhookNotification $notification 
	 * @deprecated Braintree no longer supports transaction settled webhooks.
	 */
	public function transaction_settled( $order, $notification )
	{
		global $bfwc_api_message;
		
		$transaction = $notification->transaction;
		$status = bt_manager()->get_option( 'transaction_settled_order_status' );
		if ( $status === 'ignore' ) {
			$bfwc_api_message = sprintf( __( 'Transaction settlement received. Current order status option is set to ignore. 
					No WooCommerce order status update has been performed.', 'braintree-payments' ) );
		} else {
			$order->update_status( $status );
			$message = sprintf( __( 'Transaction settled webhook received. Order status updated to %s.', 'braintree-payments' ), wc_get_order_status_name( $status ) );
			$order->add_order_note( $message );
			$bfwc_api_message = $message;
		}
	}

	/**
	 *
	 * @param WC_Order $order 
	 * @param WebhookNotification $notification 
	 * @deprecated Braintree no longer supports settlement declined webhooks.
	 */
	public function transaction_settlement_declined( $order, $notification )
	{
		global $bfwc_api_message;
		
		$transaction = $notification->transaction;
		$order->update_status( 'wc-failed' );
		$message = sprintf( __( 'Settlement of funds was declined. Reason: %s', 'braintree-payments' ), $transaction->processorResponseText );
		$bfwc_api_message = $message;
		$order->add_order_note( $message );
	}
}
Braintree_Gateway_WC_Order_Controller::init();