<?php
class Braintree_Gateway_Webhook_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'webhook_settings';
		$this->tab = 'webhook-settings';
		$this->label = __( 'Webhook Settings', 'braintree-payments' );
		$this->title = array ( 
				'title' => __( 'Webhook Settings', 'braintree-payments' ), 
				'description' => __( 'On this page you can configure your webhook settings. Webhooks are notifications' ) 
		);
		parent::__construct();
	}

	public function settings()
	{
		return array ( 
				'failed_payments_allowed_before_cancel' => array ( 
						'type' => 'text', 
						'title' => __( 'Failed Payments Allowed Before Cancel', 'braintree-payments' ), 
						'default' => 1, 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'When a recurring payment fails for a Braintree Subscription a notification is sent to your site. You can control how many failed payments are allowed before the subscription is cancelled.', 'braintree-payments' ) 
				), 
				'failed_payments_before_onhold' => array ( 
						'type' => 'text', 
						'title' => __( 'Failed Payments Before On Hold', 'braintree-payments' ), 
						'default' => 1, 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'This is the number of times a payment can fail for a subscription before it is place on an On Hold status within WC Subscriptions.' ) 
				), 
				'transaction_settled_order_status' => array ( 
						'type' => 'select', 
						'title' => __( 'Transaction Settled Order Status' ), 
						'default' => 'wc-completed', 
						'options' => array_merge( array ( 
								'ignore' => __( 'Ignore', 'braintree-payments' ) 
						), $this->get_wc_order_statuses() ), 
						'tool_tip' => true, 
						'description' => __( 'You can automate the order status process by configuring the assigned order status when a transaction is settled. If set to ignore, then the webhoook will not update the WC Order.' ) 
				) 
		);
	}

	public function get_wc_order_statuses()
	{
		if ( function_exists( 'wc_get_order_statuses' ) ) {
			return wc_get_order_statuses();
		} else {
			return bwc_admin_wc_get_order_statuses();
		
		}
	}

}
//new Braintree_Gateway_Webhook_Settings();