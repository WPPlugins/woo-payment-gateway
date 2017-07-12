<?php
/**
 * 
 * @author Payment Plugins
 * @since 2.6.0
 *
 */
abstract class BFWC_Donation_Gateway
{
	
	public $title = '';
	
	public $id = '';
	
	public $icon = '';
	
	public $supports = array ();
	
	public $enabled;
	
	static $nonce_id = '';
	
	static $token_id = 'payment_method_token';
	
	static $device_data_id = '';

	public function __construct()
	{
	
	}

	/**
	 * Process the donation payment.
	 *
	 * @param int $donation_id        	
	 */
	public function process_donation( $donation_id )
	{
		$attribs = array ();
		
		// Add the billing address to the attribs array.
		self::add_billing_address( $attribs );
		self::add_partner_code( $attribs );
		self::add_merchant_account_id( $attribs );
		self::add_payment_method( $attribs );
		static::add_device_data( $attribs );
		self::add_customer( $attribs );
		self::add_options( $attribs );
		$attribs [ 'orderId' ] = $donation_id;
		$attribs [ 'amount' ] = self::get_request_parameter( 'donation_amount' );
		$attribs = apply_filters( 'bfwcd_transaction_attribs', $attribs, $this );
		try {
			bt_manager()->success( sprintf( __( 'Processing braintree donation. Attributes: %s', 'braintree-payments' ), print_r( $attribs, true ) ) );
			$result = Braintree_Transaction::sale( $attribs );
			if ( $result->success ) {
				$transaction = $result->transaction;
				$donation = bfwcd_get_donation( $donation_id );
				bt_manager()->success( sprintf( __( 'Donation %s has been processed in the amount of %s.', 'braintree-payments' ), $result->transaction->orderId, $result->transaction->amount ) );
				bfwcd_update_donation( $donation_id, array (
						'transaction_id' => $transaction->id, 
						'amount' => $transaction->amount, 
						'merchant_account_id' => $transaction->merchantAccountId, 
						'payment_method_title' => braintree_get_payment_method_title_from_transaction( $transaction ) 
				) );
				$donation->update_status( 'complete' );
			} else {
				bfwcd_add_notice( sprintf( __( 'There was an error processing your donation. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
			}
		} catch( \Braintree\Exception $e ) {
			bt_manager()->error( sprintf( __( 'Error processing donation %s.', 'braintree-payments' ), $donation_id ) );
			bfwcd_add_notice( sprintf( __( 'There was an error processing your donation. Reason: %s', 'braintree-payments' ), $e->getMessage() ), 'error' );
		}
	}

	/**
	 * Process the recurring donation.
	 *
	 * @param int $donation_id        	
	 */
	public function process_recurring_donation( $donation_id )
	{
		$donation = bfwcd_get_donation( $donation_id );
		$subscription_attribs = array ();
		if ( ! is_user_logged_in() ) {
			// If the donor is not logged in then we need to create a Braintree
			// Customer so we can save the payment method and use it for the
			// subscription creation.
			try {
				$result = Braintree_Customer::create( array (
						'firstName' => self::get_request_parameter( 'billing_first_name' ), 
						'lastName' => self::get_request_parameter( 'billing_last_name' ), 
						'company' => self::get_request_parameter( 'billing_company' ), 
						'email' => self::get_request_parameter( 'email_address' ), 
						'paymentMethodNonce' => self::get_request_parameter( static::$nonce_id ) 
				) );
				if ( $result->success ) {
					$payment_method = $result->customer->paymentMethods [ 0 ];
					$subscription_attribs [ 'paymentMethodToken' ] = $payment_method->token;
					$payment_method_title = braintree_get_payment_method_title_from_method( $payment_method );
				} else {
					bfwcd_add_notice( sprintf( __( 'There was an error creating a user in our system. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
				}
			} catch( Exception $e ) {
				bfwcd_add_notice( sprintf( __( 'There was an error creating a user in our system.', 'braintree-payments' ) ), 'error' );
			}
		} else {
			if ( self::use_saved_method() ) {
				$user_id = wp_get_current_user()->ID;
				$token = self::get_request_parameter( static::$token_id );
				$subscription_attribs [ 'paymentMethodToken' ] = $token;
				$payment_method_title = braintree_get_payment_method_title_from_array( braintree_get_payment_method_from_token( $user_id, $token ) );
			} else {
				try {
					$result = Braintree_PaymentMethod::create( array (
							'customerId' => bt_manager()->get_customer_id( wp_get_current_user()->ID ), 
							'paymentMethodNonce' => self::get_request_parameter( static::$nonce_id ) 
					) );
					if ( $result->success ) {
						braintree_save_user_payment_method( wp_get_current_user()->ID, $result->paymentMethod );
						$subscription_attribs [ 'paymentMethodToken' ] = $result->paymentMethod->token;
						$payment_method_title = braintree_get_payment_title_from_token( wp_get_current_user()->ID, $result->paymentMethod->token );
					} else {
						bfwcd_add_notice( sprintf( __( 'There was an error saving your payment method. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
					}
				} catch( Exception $e ) {
					bfwcd_add_notice( sprintf( __( 'An exception was an encountered while saving your payment method.', 'braintree-payments' ) ), 'error' );
				}
			}
		}
		
		$subscription_attribs [ 'planId' ] = self::get_request_parameter( 'recurring_donation_plan' );
		$this->add_merchant_account_id( $subscription_attribs );
		$subscription_attribs [ 'id' ] = $donation_id;
		
		if ( isset( $_REQUEST [ 'bfwcd_amount' ] ) ) {
			$subscription_attribs [ 'price' ] = self::get_request_parameter( 'bfwcd_amount' );
		}
		if ( isset( $_REQUEST [ 'bfwcd_recurring_start_date' ] ) ) {
			$billingDate = DateTime::createFromFormat( 'm-d-Y', self::get_request_parameter( 'bfwcd_recurring_start_date' ) );
			$subscription_attribs [ 'firstBillingDate' ] = $billingDate;
		}
		$subscription_attribs = apply_filters( 'bfwcd_recurring_donation_attribs', $subscription_attribs, $this );
		try {
			$result = Braintree_Subscription::create( $subscription_attribs );
			
			if ( $result->success ) {
				$subscription = $result->subscription;
				bfwcd_update_donation( $donation_id, array (
						'merchant_account_id' => $subscription->merchantAccountId, 
						'first_billing_date' => $subscription->firstBillingDate, 
						'plan_id' => $subscription->planId, 
						'amount' => $subscription->price, 
						'recurring' => true, 
						'payment_method_title' => $payment_method_title 
				) );
				$donation->update_status( 'complete' );
			} else {
				bfwcd_add_notice( sprintf( __( 'There was an error creating your recurring donation. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
			}
		} catch( Exception $e ) {
			bfwcd_add_notice( __( 'There was an error creating your recurring donation.', 'braintree-payments' ), 'error' );
		}
	}

	/**
	 * Render the payment fields for the donation gateway.
	 */
	public abstract function payment_fields();

	public abstract function get_icon();

	public function available()
	{
		return $this->enabled;
	}

	public function supports( $feature )
	{
		return apply_filters( 'bfwcd_gateway_supports', in_array( $feature, $this->supports ), $this );
	}

	public function add_billing_address( &$attribs = array() )
	{
		$attribs [ 'billing' ] = array (
				'firstName' => self::get_request_parameter( 'billing_first_name' ), 
				'lastName' => self::get_request_parameter( 'billing_last_name' ), 
				'locality' => self::get_request_parameter( 'billing_city' ), 
				'countryCodeAlpha2' => self::get_request_parameter( 'billing_country' ), 
				'postalCode' => self::get_request_parameter( 'billing_postalcode' ), 
				'region' => self::get_request_parameter( 'billing_state' ), 
				'streetAddress' => self::get_request_parameter( 'billing_address_1' ), 
				'extendedAddress' => self::get_request_parameter( 'billing_address_2' ) 
		);
	}

	public static function add_partner_code( &$attribs = array() )
	{
		$attribs [ 'channel' ] = bt_manager()->get_partner_code();
	}

	public static function add_merchant_account_id( &$attribs = array() )
	{
		$merchant_account = bt_manager()->get_option( 'donation_' . bt_manager()->get_environment() . '_merchant_account_id' );
		if ( ! empty( $merchant_account ) ) {
			$attribs [ 'merchantAccountId' ] = $merchant_account;
		}
	}

	public static function add_payment_method( &$attribs )
	{
		if ( self::use_saved_method() ) {
			$attribs [ 'paymentMethodToken' ] = self::get_request_parameter( static::$token_id );
		} else {
			$attribs [ 'paymentMethodNonce' ] = self::get_request_parameter( static::$nonce_id );
		}
	}

	public static function add_customer( &$attribs )
	{
		$attribs [ 'customer' ] = array (
				'firstName' => self::get_request_parameter( 'billing_first_name' ), 
				'lastName' => self::get_request_parameter( 'billing_last_name' ) 
		);
		$email = self::get_request_parameter( 'email_address' );
		if ( ! empty( $email ) ) {
			$attribs [ 'customer' ] [ 'email' ] = self::get_request_parameter( 'email_address' );
		}
	}

	public static function add_options( &$attribs )
	{
		$attribs [ 'options' ] = array (
				'submitForSettlement' => ! bt_manager()->is_active( 'authorize_donation' ) 
		);
		
		$attribs [ 'taxExempt' ] = bt_manager()->is_active( 'donation_tax_exempt' );
	}

	public static function add_device_data( &$attribs )
	{
		if ( bfwcd_advanced_fraud_enabled() ) {
			$attribs [ 'deviceData' ] = stripslashes( self::get_request_parameter( static::$device_data_id ) );
		}
	}

	public static function use_saved_method()
	{
		$saved_token = self::get_request_parameter( static::$token_id );
		return ! empty( $saved_token );
	}

	public static function get_request_parameter( $name )
	{
		return isset( $_POST [ $name ] ) ? $_POST [ $name ] : '';
	}

}