<?php
use Braintree\WebhookNotification;
/**
 * Abstract controller class.
 *
 * @author Payment Plugins
 *        
 */
class Braintree_Gateway_API_Controller
{
	public $namespace = 'braintree-gateway/v1/';
	
	public $route = 'webhooks';

	public function __construct()
	{
		add_action( 'rest_api_init', array (
				$this, 
				'register_route' 
		) );
	}

	public function get_url()
	{
		return trailingslashit( get_site_url() ) . 'wp-json/' . $this->namespace . $this->route;
	}

	public function get_path()
	{
		return $this->namespace . $this->route;
	}

	/**
	 * Register controllers.
	 */
	public function register_route()
	{
		register_rest_route( $this->namespace, $this->route, array (
				'methods' => WP_REST_Server::EDITABLE, 
				'callback' => array (
						$this, 
						'process_webhook' 
				) 
		) );
	}

	/**
	 * perform authentication of the webhook.
	 *
	 * @param WP_REST_Request $request        	
	 */
	public function process_webhook( $request )
	{
		global $bfwc_api_message;
		
		if ( isset( $_REQUEST [ 'bfwc-admin-webhook-test' ] ) ) {
			define( 'BFWC_ADMIN_WEBHOOK_TEST', true );
		}
		
		$post = $request->get_body_params();
		
		$signature = isset( $post [ 'bt_signature' ] ) ? $post [ 'bt_signature' ] : '';
		$payload = isset( $post [ 'bt_payload' ] ) ? str_replace( '\r\n', '', $post [ 'bt_payload' ] ) : '';
		
		try {
			$notification = WebhookNotification::parse( $signature, $payload );
			
			try {
				
				// Allow functionality to validate the notification.
				do_action( 'bfwc_validate_notification', $notification );
				
				// allow plugins to hook into the notification. Exceptions can be caught and returned to Braintree.
				do_action( "bfwc_webhook_{$notification->kind}_notification", $notification );
				
				$response = new WP_REST_Response();
				$response->set_status( 200 );
				$response->set_data( array (
						'success' => true, 
						'message' => sprintf( __( 'Webhook notification received. Message: %s', 'braintree-payments' ), $bfwc_api_message ) 
				) );
				return $response;
			
			} catch( Exception $e ) {
				return new WP_Error( 'webhook-exception', $e->getMessage(), array (
						'status' => $e->getCode() 
				) );
			}
		
		} catch( Braintree\Exception $e ) {
			
			do_action( 'bfwc_webhook_parse_exception', $e, array (
					'signature' => $signature, 
					'payload' => $payload 
			) );
			
			return new WP_Error( 'webhook-exception', __( 'Exception thrown while parsing the webhook notification.', 'braintree-payments' ), array (
					'status' => 400 
			) );
		}
	}
}