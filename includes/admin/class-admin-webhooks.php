<?php
use Braintree\WebhookNotification;
use Braintree\WebhookTesting;
class Braintree_Gateway_Admin_Webhooks
{

	public static function init()
	{
		add_action( 'wp_ajax_bfwc_admin_retrieve_payload', __CLASS__ . '::retreive_payload' );
		
		add_filter( 'bfwc_admin_subscription_hook_retrieve_payload', __CLASS__ . '::get_subscription_payload' );
		add_filter( 'bfwc_admin_check_hook_retrieve_payload', __CLASS__ . '::get_check_hook_payload' );
		add_filter( 'bfwc_admin_transaction_hook_retrieve_payload', __CLASS__ . '::get_transaction_payload' );
	}

	public static function output()
	{
		$nonce = isset( $_POST[ '_bfwc_webhook_test' ] ) ? $_POST[ '_bfwc_webhook_test' ] : '';
		if ( ! empty( $_POST ) && wp_verify_nonce( $nonce, 'webhook-test' ) ) {
			self::test_webhook();
		}
		bfwc_admin_get_template( 'views/webhooks.php' );
	}

	public static function test_webhook()
	{
		$webhook = $_POST[ 'braintree_admin_webhooks' ];
		
		do_action( "bfwc_admin_test_{$webhook}_webhook", $webhook );
	}

	public static function retreive_payload()
	{
		$nonce = isset( $_POST[ '_bfwc_webhook_test' ] ) ? $_POST[ '_bfwc_webhook_test' ] : '';
		if ( ! wp_verify_nonce( $nonce, 'webhook-test' ) ) {
			wp_send_json( array ( 
					'success' => false, 
					'message' => __( 'Unauthorized request.', 'braintree-payments' ) 
			) );
			exit();
		}
		$hook = isset( $_POST[ 'bfwc_admin_webhook' ] ) ? $_POST[ 'bfwc_admin_webhook' ] : '';
		switch ($hook) {
			case WebhookNotification::SUBSCRIPTION_CANCELED :
			case WebhookNotification::SUBSCRIPTION_CHARGED_SUCCESSFULLY :
			case WebhookNotification::SUBSCRIPTION_CHARGED_UNSUCCESSFULLY :
			case WebhookNotification::SUBSCRIPTION_EXPIRED :
			case WebhookNotification::SUBSCRIPTION_TRIAL_ENDED :
			case WebhookNotification::SUBSCRIPTION_WENT_ACTIVE :
			case WebhookNotification::SUBSCRIPTION_WENT_PAST_DUE :
				$action = 'subscription_hook';
				break;
			case WebhookNotification::TRANSACTION_SETTLED :
			case WebhookNotification::TRANSACTION_SETTLEMENT_DECLINED :
				$action = 'transaction_hook';
				break;
			case WebhookNotification::CHECK :
				$action = 'check_hook';
				break;
			default :
				$action = '';
				break;
		}
		
		$payload = apply_filters( "bfwc_admin_{$action}_retrieve_payload", $hook );
		
		if ( is_wp_error( $payload ) ) {
			wp_send_json( array ( 
					'success' => false, 
					'message' => $payload->get_error_message() 
			) );
		} else {
			wp_send_json( array ( 
					'success' => true, 
					'data' => http_build_query( $payload ) 
			) );
		}
		exit();
	}

	public static function get_subscription_payload( $hook )
	{
		
		$id = isset( $_POST[ 'subscription_id' ] ) ? absint( $_POST[ 'subscription_id' ] ) : '';
		
		$data = null;
		
		$data = self::do_url_request( '/subscriptions', '<subscription>', '</subscription>', $id );
		if ( is_wp_error( $data ) ) {
			return $data;
		}
		
		$payload = WebhookTesting::sampleNotification( $hook, $id, $data );
		
		return $payload;
	}

	/**
	 *
	 * @param strng $path 
	 * @param string $parse_start 
	 * @param string $parse_end 
	 * @param int $id 
	 * @return WP_Error
	 */
	public static function do_url_request( $path, $parse_start = '', $parse_end = '', $id = 0 )
	{
		try {
			$path = Braintree_Configuration::$global->merchantPath() . $path;
			
			$path = $id ? $path . '/' . $id : $url;
			
			$http = new Braintree_Http( Braintree_Configuration::$global );
			$response = $http->_doUrlRequest( 'GET', Braintree_Configuration::$global->baseUrl() . $path );
			if ( $response[ 'status' ] !== 200 ) {
				Braintree_Util::throwStatusCodeException( $response[ 'status' ] );
			}
			$xml = $response[ 'body' ];
			return bwc_admin_parse_contents( $xml, $parse_start, $parse_end );
		} catch ( \Braintree\Exception $e ) {
			if ( $e instanceof \Braintree\Exception\NotFound ) {
				$message = sprintf( __( 'Subscription %s was not found in your Braintree environment.', 'braintree-payments' ), $id );
			} else {
				$message = sprintf( __( 'There was an error processing Subscription %s. Exception: %s', 'braintree-payments' ), $id, get_class( $e ) );
			}
			return new WP_Error( 'webhook-error', $message );
		}
	}

	/**
	 *
	 * @param string $hook 
	 */
	public static function get_check_hook_payload( $hook )
	{
		return WebhookTesting::sampleNotification( WebhookNotification::CHECK, 0 );
	}

	/**
	 *
	 * @param string $hook 
	 */
	public static function get_transaction_payload( $hook )
	{
		$order_id = isset( $_POST[ 'transaction_order' ] ) ? $_POST[ 'transaction_order' ] : '';
		$order = wc_get_order( $order_id );
		if ( ! $transaction_id = $order->get_transaction_id() ) {
			return new WP_Error( 'webhook-error', sprintf( __( 'Order %s does not have a transaction associated with it.', 'braintree-payments' ), $order->get_order_number() ) );
		}
		$xml = self::do_url_request( '/transactions', '<transaction>', '</transaction>', $transaction_id );
		
		if ( is_wp_error( $xml ) ) {
			return $xml;
		}
		$payload = WebhookTesting::sampleNotification( $hook, $transaction_id, $xml );
		
		return $payload;
	}

}
Braintree_Gateway_Admin_Webhooks::init();