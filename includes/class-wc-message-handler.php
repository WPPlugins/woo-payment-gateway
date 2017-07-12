<?php

/**
 * Message handler class that hooks into actions called throughout WC_Order processing. The messages are recorded
 * in the log.
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_WC_Message_Handler
{

	public function __construct()
	{
		add_action( 'braintree_woocommerce_process_order_exception', array (
				$this, 
				'process_order_exception' 
		) );
		add_action( 'braintree_woocommerce_process_order_error', array (
				$this, 
				'process_order_error' 
		) );
		add_action( 'braintree_woocommerce_process_order_success', array (
				$this, 
				'process_order_success' 
		) );
		add_filter( 'braintree_woocommerce_refund_error', array (
				$this, 
				'refund_error' 
		), 10, 2 );
		add_action( 'braintree_customer_creation_error', array (
				$this, 
				'customer_creation_error' 
		) );
		add_action( 'braintree_customer_creation_success', array (
				$this, 
				'customer_creation_success' 
		) );
		add_action( 'braintree_wc_delete_payment_method_success', array (
				$this, 
				'delete_payment_method_success' 
		), 10, 2 );
		add_action( 'braintree_wc_delete_payment_method_error', array (
				$this, 
				'delete_payment_method_error' 
		) );
		add_action( 'braintree_wc_delete_payment_method_exception', array (
				$this, 
				'delete_payment_method_exception' 
		) );
		add_action( 'braintree_wc_add_payment_method_error', array (
				$this, 
				'add_payment_method_error' 
		) );
		add_action( 'braintree_wc_add_payment_method_success', array (
				$this, 
				'add_payment_method_success' 
		) );
		add_action( 'braintree_wc_braintree_subscription_success', array (
				$this, 
				'braintree_subscription_success' 
		) );
		add_action( 'braintree_wc_braintree_subscription_error', array (
				$this, 
				'braintree_subscription_error' 
		) );
		add_action( 'braintree_wc_braintree_subscription_exception', array (
				$this, 
				'braintree_subscription_exception' 
		) );
		add_action( 'braintree_wcs_subscription_cancelled_success', array (
				$this, 
				'wcs_subscription_cancelled_success' 
		), 10, 2 );
		add_action( 'braintree_wcs_subscription_cancelled_error', array (
				$this, 
				'wcs_subscription_cancelled_error' 
		), 10, 2 );
		add_action( 'braintree_wcs_subscription_cancelled_exception', array (
				$this, 
				'wcs_subscription_cancelled_exception' 
		) );
		add_action( 'bfwc_webhook_parse_exception', array (
				$this, 
				'webhook_parse_exception' 
		), 10, 2 );
	}

	public function process_order_exception( $data )
	{
		$attribs = wp_parse_args( $data, array (
				'order_id' => '', 
				'exception' => '', 
				'method' => '', 
				'line' => '' 
		) );
		$message = sprintf( __( 'Exception thrown while processing order %s. Exception Type: %s. Method: %s. Line %s', 'braintree-payments' ), $data [ 'order_id' ], get_class( $data [ 'exception' ] ), $data [ 'method' ], $data [ 'line' ] );
		bt_manager()->error( $message );
	}

	public function process_order_error( $data )
	{
		$data = wp_parse_args( $data, array (
				'order_id' => '', 
				'result' => '', 
				'method' => '', 
				'line' => '' 
		) );
		$error_message = $data [ 'result' ]->message;
		
		braintree_global_errors( $data [ 'result' ] );
		
		$message = sprintf( __( 'Error processing payment for order %s. Details: %s. Method %s. Line: %s', 'braintree-payments' ), $data [ 'order_id' ], $error_message, $data [ 'method' ], $data [ 'line' ] );
		bt_manager()->error( $message );
	}

	public function process_order_success( $data )
	{
		$data = wp_parse_args( $data, array (
				'order_id' => '', 
				'result' => '' 
		) );
		$message = sprintf( __( 'Payment for order %s received. Response: %s', 'braintree-payments' ), $data [ 'order_id' ], print_r( $data [ 'result' ]->transaction, true ) );
		bt_manager()->success( $message );
	}

	/**
	 * Return a modified message that contains detail about the error.
	 *
	 * @param string $message        	
	 * @param Braintree_Result_Error $result        	
	 */
	public function refund_error( $message, $result )
	{
		$error = $result->errors->deepAll() [ 0 ];
		$new_message = braintree_get_error_code_message( $error->code );
		braintree_global_errors( $result );
		bt_manager()->error( $new_message );
		
		return $new_message;
	}

	/**
	 *
	 * @param Braintree_Result_Error $result        	
	 */
	public function customer_creation_error( $result )
	{
		braintree_global_errors( $result );
		bt_manager()->error( sprintf( __( 'Error creating braintree customer in %s environment. Reason: %s', 'braintree-payments' ), bt_manager()->get_environment(), $result->message ) );
	}

	/**
	 *
	 * @param Braintree_Result_Successful $result        	
	 */
	public function customer_creation_success( $result )
	{
		bt_manager()->success( sprintf( __( 'Customer %s created in %s environment', 'braintree-payments' ), $result->customer->id, bt_manager()->get_environment() ) );
	}

	/**
	 *
	 * @param Braintree_Result_Successful $result        	
	 */
	public function delete_payment_method_success( $result, $token )
	{
		bt_manager()->success( sprintf( __( 'Payment token %s deleted. Response: %s.', 'braintree-payments' ), $token, print_r( $result, true ) ) );
	}

	/**
	 *
	 * @param Braintree_Result_Error $result        	
	 */
	public function delete_payment_method_error( $result )
	{
		braintree_global_errors( $result );
		
		bt_manager()->error( sprintf( __( 'Error deleting payment method for user. Reason: %s', 'braintree-payments' ), $result->message ) );
	}

	public function delete_payment_method_exception( $data )
	{
		bt_manager()->error( sprintf( __( 'Exception thrown while deleting payment token %s. Exception Type: %s. Method: %s. Line %s', 'braintree-payments' ), $data [ 'token' ], get_class( $data [ 'exception' ] ), $data [ 'method' ], $data [ 'line' ] ) );
	}

	/**
	 *
	 * @param Braintree_Result_Error $result        	
	 */
	public function add_payment_method_error( $result )
	{
		braintree_global_errors( $result );
		bt_manager()->error( sprintf( __( 'Error while saving payment method. Reason: %s', 'braintree-payments' ), $result->message ) );
	}

	/**
	 *
	 * @param Braintree_Result_Successful $result        	
	 */
	public function add_payment_method_success( $result )
	{
		bt_manager()->success( sprintf( __( 'Payment method vaulted for user. Response: %s', 'braintree-payments' ), print_r( $result, true ) ) );
	}

	/**
	 *
	 * @param Braintree_Result_Successful $result        	
	 */
	public function braintree_subscription_success( $result )
	{
		$subscription = $result->subscription;
		bt_manager()->success( sprintf( __( 'Braintree subscription %s created.', 'braintree-payments' ), $subscription->id ) );
	}

	/**
	 *
	 * @param Braintree_Result_Error $result        	
	 */
	public function braintree_subscription_error( $result )
	{
		braintree_global_errors( $result );
		bt_manager()->error( sprintf( __( 'Error creating Braintree subscription. Reason: %s', 'braintree-payments' ), $result->message ) );
	}

	/**
	 *
	 * @param array $data        	
	 */
	public function braintree_subscription_exception( $data )
	{
		bt_manager()->error( sprintf( __( 'Exception thrown while creating Braintree subscription. Attributes: %s. Exception Type: %s. 
				Method: %s. Line %s', 'braintree-payments' ), print_r( $data [ 'attribs' ], true ), get_class( $data [ 'exception' ] ), $data [ 'method' ], $data [ 'line' ] ) );
	}

	/**
	 *
	 * @param Braintree_Result_Successful $result        	
	 * @param WC_Subscription $subscription        	
	 */
	public function wcs_subscription_cancelled_success( $result, $subscription )
	{
		if ( bwcs_is_braintree_subscription( $subscription ) ) {
			bt_manager()->success( sprintf( __( 'Braintree subscription %s cancelled successfuly.', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ) ) );
		} else {
		
		}
	}

	/**
	 *
	 * @param Braintree_Result_Error $result        	
	 * @param WC_Subscription $subscription        	
	 */
	public function wcs_subscription_cancelled_error( $result, $subscription )
	{
		if ( bwcs_is_braintree_subscription( $subscription ) ) {
			bt_manager()->error( sprintf( __( 'Error cancelling Braintree subscription %s. Reason: %s', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ), $result->message ) );
		} else {
		
		}
	}

	public function wcs_subscription_cancelled_exception( $data )
	{
		bt_manager()->error( sprintf( __( 'Exception thrown while cancelling Braintree subscription %s. Exception Type: %s.
				Method: %s. Line %s', 'braintree-payments' ), $data [ 'subscription_id' ], get_class( $data [ 'exception' ] ), $data [ 'method' ], $data [ 'line' ] ) );
	}

	/**
	 *
	 * @param Braintree_Exception $e        	
	 * @param array $data        	
	 */
	public function webhook_parse_exception( $e, $data )
	{
		$message = sprintf( __( 'Error parsing the Braintree webhook. Signature: %s. Payload: %s', 'braintree-payments' ), $data [ 'signature' ], $data [ 'payload' ] );
		bt_manager()->error( $message );
	}
}
new Braintree_Gateway_WC_Message_Handler();