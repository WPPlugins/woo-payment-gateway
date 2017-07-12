<?php
use Braintree\WebhookNotification;
class Braintree_Gateway_Test_API_Controller
{

	public function __construct()
	{

	}

	public static function init()
	{
		add_filter( 'bfwc_register_route_controllers', __CLASS__ . '::register_controller' );
	}

	/**
	 *
	 * @param array $controllers 
	 */
	public static function register_controller( $controllers )
	{
		// only register this controller if WooCommerce Subscriptions is active.
		$controllers[] = __CLASS__;
		
		return $controllers;
	}

	public function get_notification_kinds()
	{
		return apply_filters( __CLASS__ . '_notification_kinds', array ( 
				WebhookNotification::CHECK => array ( 
						$this, 
						'check_webhook_connection' 
				) 
		) );
	}

	/**
	 *
	 * @param WebhookNotification $notification 
	 */
	public function check_webhook_connection( $notification )
	{
		global $bfwc_api_message;
		
		$bfwc_api_message = sprintf( __( 'Braintree webhook connection test to url %s was successful. Received: %s', 'braintree-payments' ), get_rest_url(null, bt_manager()->api->get_path()), $notification->timestamp->format( 'Y-m-d H:i:s' ) );
		bt_manager()->success( $bfwc_api_message );
	}
}
Braintree_Gateway_Test_API_Controller::init();