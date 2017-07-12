<?php
use Braintree\PaymentMethod;
if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
	return;
}

/**
 * Gateway class that processess WooCommrce orders.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class WC_Braintree_Payment_Gateway extends WC_Payment_Gateway
{
	const ID = 'braintree_payment_gateway';
	
	/**
	 * Nonce id contained in the $_POST.
	 *
	 * @var string
	 */
	public static $nonce_id = 'braintree_payment_gateway_nonce';
	
	/**
	 * Device data field key located in $_POST.
	 *
	 * @var string
	 */
	public static $device_data_id = 'braintree_device_data';
	
	/**
	 * The key used in $_POST to identify the selected payment method token.
	 *
	 * @var string
	 */
	public static $token_id = 'braintree_payment_gateway_method_token';
	
	/**
	 * Nonce that represents a payment method.
	 *
	 * @var String
	 */
	public $nonce = '';
	
	/**
	 * Token that represents a vaulted payment method.
	 *
	 * @var string
	 */
	public static $payment_method_token = '';
	
	/**
	 * Name of the save payment method checkbox.
	 *
	 * @var string
	 */
	public static $save_method_name = 'bfwc_save_credit_card';
	
	/**
	 *
	 * @var WC_Order
	 */
	public $order;

	public function __construct()
	{
		$this->enabled = $this->settings [ 'enabled' ] = bwc_card_payments_enabled() ? 'yes' : 'no';
		
		$this->id = static::ID;
		
		$this->title = bt_manager()->get_option( 'title_text' );
		
		$this->method_title = __( 'Braintree Payment Gateway', 'braintree-payments' );
		
		$this->has_fields = true;
		
		$this->actions();
		
		$this->set_supports();
		
		$this->init_settings();
	}

	public function actions()
	{
		add_action( "woocommerce_update_options_payment_gateways_{$this->id}", array (
				$this, 
				'process_admin_options' 
		) );
		
		// Only add actions and filters if the gateway is available.
		if ( $this->is_available() ) {
			add_filter( "braintree_wc_{$this->id}_process_order", array (
					$this, 
					'process_order' 
			) );
			add_filter( "braintree_wc_{$this->id}_process_subscription", array (
					$this, 
					'process_subscription' 
			) );
			add_filter( "bfwc_process_{$this->id}_braintree_subscription", array (
					$this, 
					'process_braintree_subscription' 
			) );
			add_action( "braintree_wc_{$this->id}_save_wc_order_meta", array (
					$this, 
					'save_order_meta' 
			), 10, 2 );
			add_filter( 'woocommerce_gateway_icon', array (
					$this, 
					'woocommerce_gateway_icon' 
			), 10, 2 );
			add_filter( 'woocommerce_gateway_title', array (
					$this, 
					'woocommerce_gateway_title' 
			), 10, 2 );
			add_action( "woocommerce_subscription_payment_method_updated_to_{$this->id}", array (
					$this, 
					'payment_method_updated_to' 
			), 10, 2 );
			add_filter( "braintree_woocommerce_{$this->id}_order_attributes", __CLASS__ . '::maybe_remove_postal_code', 10, 2 );
		}
	}

	public static function init()
	{
		add_filter( 'bwc_add_payment_gateways', __CLASS__ . '::add_braintree_gateway' );
		
		add_filter( 'woocommerce_payment_gateways', __CLASS__ . '::add_gateway' );
		
		add_action( 'wp_loaded', __CLASS__ . '::maybe_delete_payment_method' );
		
		add_filter( 'woocommerce_saved_payment_methods_list', __CLASS__ . '::saved_payment_method_list', 10, 2 );
		
		add_filter( 'woocommerce_payment_complete_order_status', __CLASS__ . '::maybe_update_order_status', 99, 2 );
		
		add_action( 'woocommerce_subscription_failing_payment_method_updated_' . static::ID, __CLASS__ . '::update_failing_payment_method', 10, 2 );
		
		// called when a new customer is being inserted into the database.
		add_filter( 'insert_user_meta', __CLASS__ . '::woocommerce_new_customer_data', 10, 3 );
		
		add_action( 'bfwc_before_process_order', __CLASS__ . '::before_order_process', 99 );
		
		add_action( 'bfwc_before_process_order_' . static::ID, __CLASS__ . '::maybe_save_payment_method' );
		
		add_action( 'woocommerce_payment_complete', __CLASS__ . '::woocommerce_payment_complete' );
		
		add_action( 'bfwc_ajax_bfwc_updated_checkout', __CLASS__ . '::updated_checkout' );
		
		add_filter( 'woocommerce_update_order_review_fragments', __CLASS__ . '::update_order_review_fragments' );
		
		add_action( 'woocommerce_order_refunded', __CLASS__ . '::woocommerce_order_refunded', 10, 2 );
		
		add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::after_checkout_validation' );
		
		add_filter( 'bfwc_maybe_save_payment_method_attribs', __CLASS__ . '::maybe_remove_postal_code', 10, 2 );
		
		add_action( 'woocommerce_save_account_details', __CLASS__ . '::update_vaulted_customer_details' );
		
		add_action( 'woocommerce_customer_save_address', __CLASS__ . '::update_vaulted_customer_details' );
		
		add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::checkout_update_customer', 10, 1 );
		
		if ( bwc_display_icons_on_payment_methods_page() ) {
			add_action( 'woocommerce_account_payment_methods_column_method', __CLASS__ . '::output_payment_method' );
		}
		
		add_action( 'bfwc_ajax_generate_payment_nonce', __CLASS__ . '::generate_payment_nonce_for_token' );
		
		add_action( 'woocommerce_before_checkout_process', __CLASS__ . '::initialize_threeds_validation' );
		
		add_action( 'woocommerce_review_order_after_order_total', __CLASS__ . '::print_cart_total' );
		
		add_action( 'before_woocommerce_pay', __CLASS__ . '::add_output_order_total' );
	}

	public static function add_braintree_gateway( $gateways )
	{
		$gateways [] = __CLASS__;
		return $gateways;
	}

	public static function add_gateway( $gateways )
	{
		$gateways [] = __CLASS__;
		return $gateways;
	}

	/**
	 * Process the payment for the WC_Order.
	 *
	 * {@inheritDoc}
	 *
	 * @see WC_Payment_Gateway::process_payment()
	 */
	public function process_payment( $order_id )
	{
		$order = wc_get_order( $order_id );
		/**
		 * Perform any functionality needed before processing the order.
		 * If errors, return;
		 */
		do_action( 'bfwc_before_process_order', $order );
		
		if ( wc_notice_count( 'error' ) > 0 ) {
			return $this->order_error();
		}
		
		global $bfwc_order_total;
		
		$bfwc_order_total = $order->get_total();
		
		if ( $this->order_has_subscription( $order_id ) ) {
			
			return apply_filters( "braintree_wc_{$this->id}_process_subscription", $order );
		
		} elseif ( bfwcs_order_contains_subscription( $order_id ) ) {
			
			return apply_filters( "bfwc_process_{$this->id}_braintree_subscription", $order );
		
		} else {
			
			if ( bwcs_is_woocommerce_change_payment() ) {
				return array (
						'result' => 'success', 
						'redirect' => wc_get_page_permalink( 'myaccount' ) 
				);
			}
			
			return apply_filters( "braintree_wc_{$this->id}_process_order", $order );
		}
	}

	/**
	 * Process the WooCommerce order.
	 *
	 * @param WC_Order $order        	
	 * @return array
	 */
	public function process_order( $order )
	{
		global $bfwc_order_total;
		$attribs = array (
				'amount' => $order->get_total(), 
				'taxAmount' => bwc_is_wc_3_0_0_or_more() ? wc_round_tax_total( $order->get_total_tax() ) : $order->get_total_tax() 
		);
		self::add_customer( $attribs, $order );
		static::add_order_payment_method( $attribs );
		self::add_order_id( $attribs, $order );
		self::add_billing_address( $attribs, $order );
		self::add_shipping_address( $attribs, $order );
		self::add_merchant_account( $attribs, $order );
		self::add_partner_code( $attribs );
		self::add_options( $attribs, $order );
		self::add_descriptors( $attribs );
		static::add_device_data( $attribs );
		
		// allow plugins to add additional attributes.
		$attribs = apply_filters( "braintree_woocommerce_{$this->id}_order_attributes", $attribs, $order, $this );
		
		bt_manager()->success( sprintf( __( 'Processing order %s. Attribs: %s', 'braintree-payments' ), $order->get_order_number(), print_r( $attribs, true ) ) );
		
		try {
			$result = Braintree_Transaction::sale( $attribs );
			
			if ( $result->success ) {
				do_action( "braintree_wc_{$this->id}_save_wc_order_meta", bwc_get_order_property( 'id', $order ), $result->transaction );
				do_action( 'braintree_woocommerce_process_order_success', array (
						'order_id' => bwc_get_order_property( 'id', $order ), 
						'result' => $result 
				) );
				
				// ensure the original order total is set as it could be manipulated.
				$order->set_total( $bfwc_order_total );
				
				if ( isset( $attribs [ 'options' ] [ 'storeInVaultOnSuccess' ] ) && $attribs [ 'options' ] [ 'storeInVaultOnSuccess' ] ) {
					braintree_save_payment_method_from_transaction( bwc_get_order_property( 'customer_user', $order ), $result->transaction );
				}
				
				$order->payment_complete( $result->transaction->id );
				
				WC()->cart->empty_cart();
				return $this->order_success( $order );
			} else {
				wc_add_notice( sprintf( __( 'There was an error processing your payment. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
				do_action( 'braintree_woocommerce_process_order_error', array (
						'order_id' => bwc_get_order_property( 'id', $order ), 
						'result' => $result, 
						'method' => __METHOD__, 
						'line' => __LINE__ 
				) );
				
				$order->update_status( 'failed' ); // Payment failed,
				                                   // set status to
				                                   // failed.
				$order->add_order_note( sprintf( __( 'Payment for order failed. Reason: %s', 'braintree-payments' ), $result->message ) );
				
				return $this->order_error();
			}
		} catch( Braintree\Exception $e ) {
			wc_add_notice( sprintf( __( 'There was an error processing your payment. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ), 'error' );
			do_action( 'braintree_woocommerce_process_order_exception', array (
					'attribs' => $attribs, 
					'exception' => $e, 
					'order_id' => bwc_get_order_property( 'id', $order ), 
					'method' => __METHOD__, 
					'line' => __LINE__ 
			) );
			$order->update_status( 'failed' );
			
			return $this->order_error();
		}
	}

	/**
	 * Process the WooCommerce Subscription.
	 *
	 * @param WC_Order $order_id        	
	 * @return string[]
	 */
	public function process_subscription( $order )
	{
		global $bfwc_order_total;
		
		$subscriptions = wcs_get_subscriptions_for_order( $order );
		
		// There may be Braintree Subscriptions in the order. If so, process them.
		foreach ( $subscriptions as $subscription ) {
			if ( bwcs_is_braintree_subscription( $subscription ) ) {
				$result = $this->process_wcs_braintree_subscription( $subscription, $order );
				
				if ( is_wp_error( $result ) ) {
					wc_add_notice( $result->get_error_message(), 'error' );
					return $this->order_error();
				}
			}
		}
		if ( $order->get_total() > 0 ) {
			
			// Add subscription meta method so it can be saved.
			add_action( "braintree_wc_{$this->id}_save_wc_order_meta", array (
					$this, 
					'save_wc_subscription_meta' 
			), 10, 2 );
			// add methods that are needed for processing a WC subscription
			add_filter( "braintree_woocommerce_{$this->id}_order_attributes", __CLASS__ . '::add_subscription_id', 10, 2 );
			
			return $this->process_order( $order );
		} else {
			// reset the order's original value since it might have been manipulated.
			$order->set_total( $bfwc_order_total );
			
			// ensure the payment method token is set.
			self::$payment_method_token = self::$payment_method_token ? self::$payment_method_token : self::get_request_param( static::$token_id );
			$this->save_synchronized_subscription_meta( $order ); // Save
			                                                      // metadata
			                                                      // for
			                                                      // synchronized
			                                                      // subscription.
			$order->payment_complete();
			WC()->cart->empty_cart();
			
			return $this->order_success( $order ); // There is no order amount to
				                                       // process.
		}
	}

	/**
	 *
	 * @param WC_Order $order        	
	 */
	public function process_braintree_subscription( $order )
	{
		global $bfwc_order_total;
		$user_id = wp_get_current_user()->ID;
		
		$subscriptions = bfwcs_get_subscriptions_for_order( $order );
		
		foreach ( $subscriptions as $subscription ) {
			
			// subscription already created in Braintree so continue.
			if ( $subscription->is_created() ) {
				// subscription was already created, so remove the subscription price from order.
				bfwcs_calculate_order_total( $order, $subscription );
				continue;
			}
			
			$attribs = array (
					'id' => bwc_get_order_property( 'id', $subscription ), 
					'planId' => bwc_get_order_property( 'braintree_plan', $subscription ), 
					'price' => $subscription->get_total(), 
					'merchantAccountId' => bwc_get_order_property( 'merchant_account_id', $subscription ) 
			);
			self::add_order_payment_method( $attribs );
			
			if ( $subscription->never_expires() ) {
				$attribs [ 'neverExpires' ] = true;
			} else {
				$attribs [ 'numberOfBillingCycles' ] = $subscription->get_num_of_billing_cycles();
			}
			
			/* calculate the start date etc. */
			if ( $subscription->has_trial() ) {
				$attribs [ 'trialDuration' ] = $subscription->get_trial_length();
				$attribs [ 'trialDurationUnit' ] = $subscription->get_trial_period();
				$attribs [ 'trialPeriod' ] = true;
			} else {
				if ( $subscription->last_day_of_month() ) {
					$attribs [ 'billingDayOfMonth' ] = 31;
				} else {
					// $attribs [ 'firstBillingDate' ] = $subscription->get_date( 'start' );
					$attribs [ 'options' ] [ 'startImmediately' ] = true;
				}
			}
			
			if ( $subscription->has_descriptors() ) {
				self::set_subscription_descriptors( $attribs, $subscription );
			}
			
			try {
				/**
				 * Set the order to processing.
				 * This is done because webhooks are called before $order->payment_complete() can be reached.
				 * so a mechanism is needed to detect what state the subscription is currently in.
				 */
				$subscription->update_meta( 'subscription_processing', true );
				$result = Braintree_Subscription::create( apply_filters( "bfwc_{$this->id}_subscription_attributes", $attribs ) );
				
				if ( $result->success ) {
					
					$subscription->set_created( true );
					
					$subscription->update_payment_method_title( braintree_get_payment_title_from_token( $user_id, $result->subscription->paymentMethodToken ) );
					$subscription->update_payment_method_token( $result->subscription->paymentMethodToken );
					$subscription->add_order_note( __( 'Subscription created in Braintree.', 'braintree-payments' ) );
					$subscription->update_status( 'active' );
					
					// subscription was created in Braintree, so remove the total from the order to prevent double charges.
					bfwcs_calculate_order_total( $order, $subscription );
				
				} else {
					$subscription->set_created( false );
					$subscription->update_status( 'failed' );
					$order->update_status( 'failed' );
					$subscription->add_order_note( sprintf( __( 'Subscription creation in Braintree failed. Reason: %s.', 'braintree-payments' ), $result->message ) );
					wc_add_notice( sprintf( __( 'Error processing checkout. Reason: %s', 'braintree-payments' ), $result->message ), 'error' );
					
					// set the original order amount.
					$order->set_total( $bfwc_order_total );
					return $this->order_error();
				}
			} catch( \Braintree\Exception $e ) {
				$subscription->set_created( false );
				$subscription->update_status( 'failed' );
				$order->update_status( 'failed' );
				$subscription->add_order_note( sprintf( __( 'Subscription creation in Braintree failed. Exception: %s.', 'braintree-payments' ), get_class( $e ) ) );
				
				// set the original order amount.
				$order->set_total( $bfwc_order_total );
				return $this->order_error();
			}
		}
		
		// loop has ended. Process order.
		if ( $order->get_total() > 0 ) {
			// there are additional fees that need to be charged, like a signup fee, etc.
			return $this->process_order( $order );
		} else {
			// set the order's total since it may have been manipulated.
			$order->set_total( $bfwc_order_total );
			update_post_meta( bwc_get_order_property( 'id', $order ), '_payment_method_title', braintree_get_payment_title_from_token( bwc_get_order_property( 'customer_user', $order ), $attribs [ 'paymentMethodToken' ] ) );
			update_post_meta( bwc_get_order_property( 'id', $order ), '_payment_method_token', $attribs [ 'paymentMethodToken' ] );
			$order->payment_complete();
			WC()->cart->empty_cart();
			return $this->order_success( $order );
		}
	}

	/**
	 * Process the WC Braintree Subscription.
	 * If any errors are encountered, WP_Error object is returned.
	 *
	 * @param WC_Subscription $subscription        	
	 * @param WC_Order $order        	
	 * @return mixed bool|WP_Error
	 */
	public function process_wcs_braintree_subscription( $subscription, $order )
	{
		// If not a Braintree Subscription or already created in Braintree, continue.
		if ( ! bwcs_is_braintree_subscription( $subscription ) || bwc_get_order_property( 'created_in_braintree', $subscription ) ) {
			return;
		}
		
		$product = bwcs_get_product_from_subscription( $subscription ); // There should only be one item in each subscription. There can be multiple quantities of the same product.
		
		if ( ! $plan_id = bwcs_get_plan_from_product( $product ) ) {
			return new WP_Error( 'subscription-error', sprintf( __( 'Product %s does not have a subscription plan configured for currency %s.', 'braintree-payments' ), $product->get_title(), get_woocommerce_currency() ) );
		}
		
		$attribs = array (
				'id' => bwc_get_order_property( 'id', $subscription ), 
				'planId' => $plan_id, 
				'price' => $subscription->get_total() 
		);
		self::add_order_payment_method( $attribs );
		self::add_merchant_account( $attribs, $order );
		self::add_billing_date( $attribs, $subscription, $order );
		self::add_billing_cycles( $attribs, $subscription, $order );
		
		// let plugins add additional attributes.
		$attribs = apply_filters( "braintree_wcs_{$this->id}_braintree_subscription_attribs", $attribs, $subscription, $this );
		
		try {
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_subscription_processing', true );
			$result = Braintree_Subscription::create( $attribs );
			if ( $result->success ) {
				
				$this->save_wc_braintree_subscription_meta( $subscription, $result->subscription );
				
				do_action( 'braintree_wc_braintree_subscription_success', $result );
				
				// Made it this far so now remove this subscription's total from the order. This will prevent double charges.
				if ( ! bwcs_order_has_coupon( $order ) ) {
					bwcs_calculate_order_total( $order, $subscription );
				}
			} else {
				
				$order->add_order_note( sprintf( __( 'Error processing subscription %s. Reason: %s', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ), bfwc_get_error_message( $result ) ) );
				
				do_action( 'braintree_wc_braintree_subscription_error', $result );
				
				// Exit method so errors can be displayed on checkout page.
				return new WP_Error( 'subscription-error', sprintf( __( 'There was an error processing your subscription. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ) );
			}
		} catch( \Braintree\Exception $e ) {
			
			do_action( 'braintree_wc_braintree_subscription_exception', array (
					'method' => __METHOD__, 
					'line' => __LINE__, 
					'exception' => $e, 
					'attribs' => $attribs 
			) );
			
			// exit method.
			return new WP_Error( 'subscription-error', __( 'There was an error processing your subscription. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) );
		}
		
		return true;
	}

	public function process_refund( $order_id, $amount = null, $reason = '' )
	{
		$order = wc_get_order( $order_id );
		
		if ( ! bwc_can_refund_order( $order ) ) {
			return new WP_Error( 'refund-error', sprintf( __( 'Order %s does not contain a transaction Id.', 'braintree-payments' ), $order_id ) );
		}
		
		$id = $order->get_transaction_id();
		global $bfwc_refund_transaction;
		try {
			$result = Braintree_Transaction::refund( $id, $amount );
			if ( $result->success ) {
				$order->add_order_note( sprintf( __( 'Order was successfully refunded in the amount of %s%s.', 'braintree-payments' ), get_woocommerce_currency_symbol( bwc_get_order_property( 'order_currency', $order ) ), $amount ) );
				$bfwc_refund_transaction = $result->transaction;
				return true;
			} else {
				return new WP_Error( 'refund-error', apply_filters( 'braintree_woocommerce_refund_error', sprintf( __( 'There was an error refunding order %s. Reason: %s', 'braintree-payments' ), $order_id, $result->message ), $result ) );
			}
		} catch( Braintree\Exception\NotFound $e ) {
			return new WP_Error( 'refund-error', sprintf( __( 'Transaction %s was not found in your %s Braintree environment.', 'braintree-payments' ), $id, bt_manager()->get_environment() ) );
		} catch( Braintree\Exception $e ) {
			return new WP_Error( 'refund-error', sprintf( __( 'There was an exception thrown while refunding transaction %s.', 'braintree-payments' ), $id ) );
		}
	}

	public function order_success( $order )
	{
		return array (
				'result' => 'success', 
				'redirect' => $order->get_checkout_order_received_url() 
		);
	}

	public function order_error()
	{
		return array (
				'result' => 'failure', 
				'redirect' => '' 
		);
	}

	/**
	 * Does the WC order contain WC subscriptions? If yes, return true.
	 * This method is plugin safe meaning
	 * it will not execute the wcs_order_contains_subscription method if it
	 * doesn't exist.
	 *
	 * @param int $order_id        	
	 * @return boolean
	 */
	public function order_has_subscription( $order_id )
	{
		if ( ! function_exists( 'wcs_order_contains_subscription' ) ) {
			return false;
		}
		return wcs_order_contains_subscription( $order_id );
	}

	/**
	 * Return true if the user has chosen to use a saved payment method or the user selected to
	 * save the payment method during checkout.
	 *
	 * @return bool
	 */
	public static function use_saved_method()
	{
		$method = self::get_request_param( static::$token_id );
		return ! empty( $method ) || ! empty( self::$payment_method_token );
	}

	/**
	 * Return the given param from the $_POST.
	 * If the param does not exists, return an empty string.
	 *
	 * @param int $param        	
	 * @return string|mixed
	 */
	public static function get_request_param( $param )
	{
		return isset( $_POST [ $param ] ) ? $_POST [ $param ] : '';
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_customer( &$attribs, $order )
	{
		$customer_id = braintree_get_customer_id( bwc_get_order_property( 'customer_user', $order ) );
		if ( $customer_id ) {
			$attribs [ 'customerId' ] = $customer_id;
		}
		$attribs [ 'customer' ] = array (
				'firstName' => bwc_get_order_property( 'billing_first_name', $order ), 
				'lastName' => bwc_get_order_property( 'billing_last_name', $order ), 
				'phone' => bwc_get_order_property( 'billing_phone', $order ), 
				'email' => bwc_get_order_property( 'billing_email', $order ), 
				'company' => bwc_get_order_property( 'billing_company', $order ) 
		);
		return $attribs;
	}

	/**
	 * Add a payment method nonce or token to the order attributes array.
	 *
	 * @param array $attribs        	
	 */
	public static function add_order_payment_method( &$attribs )
	{
		if ( self::use_saved_method() ) {
			$attribs [ 'paymentMethodToken' ] = self::$payment_method_token ? self::$payment_method_token : self::get_request_param( static::$token_id );
		} else {
			$attribs [ 'paymentMethodNonce' ] = self::get_request_param( static::$nonce_id );
		}
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_order_id( &$attribs, $order )
	{
		$order_prefix = bt_manager()->get_option( 'order_prefix' );
		$attribs [ 'orderId' ] = $order_prefix . $order->get_order_number();
		
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_subscription_id( $attribs, $order )
	{
		$order_prefix = bt_manager()->get_option( 'woocommerce_subscriptions_prefix' );
		$attribs [ 'orderId' ] = $order_prefix . $order->get_order_number();
		
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_merchant_account( &$attribs, $order )
	{
		$merchant_account = bwc_get_merchant_account( bwc_get_order_property( 'order_currency', $order ) );
		
		if ( ! empty( $merchant_account ) ) {
			
			$attribs [ 'merchantAccountId' ] = $merchant_account;
		}
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_billing_address( &$attribs, $order )
	{
		$attribs [ 'billing' ] = array (
				'firstName' => bwc_get_order_property( 'billing_first_name', $order ), 
				'lastName' => bwc_get_order_property( 'billing_last_name', $order ), 
				'locality' => bwc_get_order_property( 'billing_city', $order ), 
				'postalCode' => bwc_get_order_property( 'billing_postcode', $order ), 
				'region' => bwc_get_order_property( 'billing_state', $order ), 
				'streetAddress' => bwc_get_order_property( 'billing_address_1', $order ), 
				'extendedAddress' => bwc_get_order_property( 'billing_address_2', $order ), 
				'countryCodeAlpha2' => bwc_get_order_property( 'billing_country', $order ) 
		);
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_shipping_address( &$attribs, $order )
	{
		$attribs [ 'shipping' ] = array (
				'firstName' => bwc_get_order_property( 'shipping_first_name', $order ), 
				'lastName' => bwc_get_order_property( 'shipping_last_name', $order ), 
				'locality' => bwc_get_order_property( 'shipping_city', $order ), 
				'postalCode' => bwc_get_order_property( 'shipping_postcode', $order ), 
				'region' => bwc_get_order_property( 'shipping_state', $order ), 
				'streetAddress' => bwc_get_order_property( 'shipping_address_1', $order ), 
				'extendedAddress' => bwc_get_order_property( 'shipping_address_2', $order ), 
				'countryCodeAlpha2' => bwc_get_order_property( 'shipping_country', $order ) 
		);
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function add_options( &$attribs, $order )
	{
		$attribs [ 'options' ] = array (
				'submitForSettlement' => ! bt_manager()->is_active( 'authorize_transaction' ) 
		);
		if ( bwc_is_3ds_active() ) {
			if ( ! self::use_saved_method() || bwc_3ds_verify_vaulted_methods() ) { // threeDSecure options attribute
			                                                                        // can only be added if the
			                                                                        // payment method nonce is
			                                                                        // present or vaulted method verification is enabled.
				$attribs [ 'options' ] [ 'threeDSecure' ] = array (
						'required' => true 
				);
			}
		}
		if ( self::should_save_payment_method() ) {
			$attribs [ 'options' ] [ 'storeInVaultOnSuccess' ] = true;
		}
		// Braintree only allows credit card attributes to be sent if a payment nonce is being used.
		if ( self::ID === bwc_get_order_property( 'payment_method', $order ) && ! self::use_saved_method() ) {
			$attribs [ 'creditCard' ] = array (
					'cardholderName' => sprintf( '%s %s', bwc_get_order_property( 'billing_first_name', $order ), bwc_get_order_property( 'billing_last_name', $order ) ) 
			);
		}
		return $attribs;
	}

	public static function add_partner_code( &$attribs )
	{
		$attribs [ 'channel' ] = bt_manager()->get_partner_code();
		return $attribs;
	}

	public static function add_descriptors( &$attribs )
	{
		if ( bwc_is_descriptors_enabled() ) {
			$attribs [ 'descriptor' ] = array (
					'name' => bt_manager()->get_option( 'dynamic_descriptor_name' ), 
					'phone' => bt_manager()->get_option( 'dynamic_descriptor_phone' ), 
					'url' => bt_manager()->get_option( 'dynamic_descriptor_url' ) 
			);
		}
		return $attribs;
	}

	public static function add_device_data( &$attribs )
	{
		if ( bwc_is_advanced_fraud_tools() ) {
			$device_data = self::get_request_param( static::$device_data_id );
			$device_data = stripslashes( $device_data );
			$attribs [ 'deviceData' ] = $device_data;
		}
		return $attribs;
	}

	/**
	 * Adds the Subscription billing date.
	 *
	 * @param array $attribs        	
	 * @param WC_Subscription $subscription        	
	 */
	public static function add_billing_date( &$attribs, $subscription, $order )
	{
		if ( bwcs_order_has_coupon( $order ) ) {
			$start_time_in_utc = new DateTime();
			$start_time_in_utc->setTimestamp( $subscription->get_time( 'next_payment' ) );
			$attribs [ 'firstBillingDate' ] = $start_time_in_utc;
		} else {
			$start_time_in_utc = bwcs_get_start_date_in_utc( $subscription );
			$current_time_in_utc = DateTime::createFromFormat( 'Y-m-d H:i:s', current_time( 'mysql' ) );
			$current_time_in_utc = new DateTime( $current_time_in_utc->format( 'Y-m-d H:i:s' ), new DateTimeZone( 'UTC' ) );
			
			// set the hours, minutes, seconds equal to the values of start_time so that any difference calulcations will be in whole days.
			$current_time_in_utc->setTime( $start_time_in_utc->format( 'H' ), $start_time_in_utc->format( 'i' ), $start_time_in_utc->format( 's' ) );
			
			$has_trial = ( bool ) $subscription->get_time( 'trial_end' );
			$attribs [ 'trialPeriod' ] = $has_trial;
			
			if ( $has_trial ) {
				$difference = $start_time_in_utc->diff( $current_time_in_utc );
				$attribs [ 'trialDurationUnit' ] = 'day';
				$attribs [ 'trialDuration' ] = ( int ) $difference->days;
			} else {
				if ( bwcs_subscription_is_synched( $subscription ) ) {
					$attribs [ 'firstBillingDate' ] = $start_time_in_utc;
				} else {
					$attribs [ 'options' ] [ 'startImmediately' ] = true;
				}
			}
		}
		return $attribs;
	}

	/**
	 * Add the billing cycles to the Braintree Subscription.
	 *
	 * @param array $attribs        	
	 * @param WC_Subscription $subscription        	
	 */
	public static function add_billing_cycles( &$attribs, $subscription, $order )
	{
		// If there is no end date then the subscription never expires.
		if ( bwcs_get_subscription_date( 'end', $subscription ) === 0 ) {
			$attribs [ 'neverExpires' ] = true;
		} else {
			$product = bwcs_get_product_from_subscription( $subscription );
			$cycles = bwcs_get_num_of_billing_cycles( $product );
			$cycles = bwcs_order_has_coupon( $order ) ? $cycles - 1 : $cycles;
			$attribs [ 'numberOfBillingCycles' ] = $cycles; // WC_Subscriptions_Product::get_length( $product );
		}
		return $attribs;
	}

	/**
	 *
	 * @param array $attribs        	
	 * @param Braintree_Gateway_WC_Subscription $subscription        	
	 */
	public static function set_subscription_descriptors( &$attribs, $subscription )
	{
		if ( $name = $subscription->get_descriptor( 'name' ) ) {
			$attribs [ 'descriptor' ] [ 'name' ] = $name;
		}
		if ( $phone = $subscription->get_descriptor( 'phone' ) ) {
			$attribs [ 'descriptor' ] [ 'phone' ] = $phone;
		}
		if ( $url = $subscription->get_descriptor( 'url' ) ) {
			$attribs [ 'descriptor' ] [ 'url' ] = $url;
		}
		return $attribs;
	}

	public function payment_fields()
	{
		if ( bwc_is_checkout() || bwcs_is_change_payment_method() || bfwcs_is_change_payment_method() || bfwcs_is_pay_for_subscription_request() ) {
			
			$methods = bwc_get_user_payment_methods( wp_get_current_user()->ID );
			bwc_get_template( 'checkout/braintree-checkout.php', array (
					'gateway' => $this, 
					'custom_form' => bwc_get_custom_form(), 
					'loader' => bwc_get_loader_file(), 
					'methods' => $methods, 
					'has_methods' => ( bool ) $methods, 
					'default_method' => $methods ? bwc_get_default_method( $methods ) : false 
			) );
		} elseif ( is_add_payment_method_page() ) {
			if ( bwc_is_custom_form() ) {
				bwc_get_template( 'custom-form.php', array (
						'has_methods' => false, 
						'gateway' => $this, 
						'custom_form' => bwc_get_custom_form(), 
						'loader' => bwc_get_loader_file(), 
						'show_payment_icons' => ! bwc_payment_icons_outside(), 
						'icons_inside' => ! bwc_payment_icons_outside() 
				) );
			} else {
				bwc_get_template( 'dropin-form.php', array (
						'has_methods' => false, 
						'gateway' => $this, 
						'show_payment_icons' => ! bwc_payment_icons_outside(), 
						'icons_inside' => ! bwc_payment_icons_outside() 
				) );
			}
		}
	}

	public function set_supports()
	{
		$this->supports = array (
				'subscriptions', 
				'products', 
				'add_payment_method', 
				'subscription_cancellation', 
				'multiple_subscriptions', 
				'subscription_amount_changes', 
				'subscription_date_changes', 
				'default_credit_card_form', 
				'refunds', 
				'pre-orders', 
				'subscription_payment_method_change_admin', 
				'subscription_reactivation', 
				'subscription_suspension', 
				'subscription_payment_method_change_customer', 
				'bfwcs_change_payment_method', 
				'bfwc_subscriptions', 
				'bfwc_credit_card_form', 
				'bfwc_fees' 
		);
	}

	/**
	 * Save the order meta for the transaction.
	 *
	 * @param int $order_id        	
	 * @param Braintree_Transaction $transaction        	
	 */
	public function save_order_meta( $order_id, $transaction )
	{
		update_post_meta( $order_id, '_braintree_version', bt_manager()->version );
		update_post_meta( $order_id, '_merchant_account_id', $transaction->merchantAccountId );
		update_post_meta( $order_id, '_payment_method_token', braintree_get_payment_token_from_transaction( $transaction ) );
		update_post_meta( $order_id, '_payment_method_title', braintree_get_payment_method_title_from_transaction( $transaction ) );
		update_post_meta( $order_id, '_braintree_environment', bt_manager()->get_environment() );
	}

	/**
	 * Save the subscription order meta for the transaction.
	 *
	 * @param int $order_id        	
	 * @param Braintree_Transaction $transaction        	
	 */
	public function save_wc_subscription_meta( $order_id, $transaction )
	{
		if ( ! function_exists( 'wcs_get_subscriptions_for_order' ) ) {
			return;
		}
		$subscriptions = wcs_get_subscriptions_for_order( $order_id );
		foreach ( $subscriptions as $subscription ) { // save the subscription
		                                              // metadata.
			if ( ! bwcs_is_braintree_subscription( $subscription ) ) { // Only save non Braintree Subscriptions meta.
				$this->save_order_meta( bwc_get_order_property( 'id', $subscription ), $transaction );
				
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_subscription_type', 'woocommerce' );
			}
		}
	}

	/**
	 * Save the subscription metadata for the synchronized subscription.
	 * This method saves the metadata to the WC_Order and then to each
	 * WC_Subscription
	 * that is contained within the order.
	 *
	 * @param WC_Order $order        	
	 */
	public function save_synchronized_subscription_meta( $order )
	{
		$payment_method_title = braintree_get_payment_method_title_from_array( braintree_get_payment_method_from_token( wp_get_current_user()->ID, self::$payment_method_token ) );
		
		// Save the metadata for the order.
		update_post_meta( bwc_get_order_property( 'id', $order ), '_braintree_version', bt_manager()->version );
		update_post_meta( bwc_get_order_property( 'id', $order ), '_merchant_account_id', bwc_get_merchant_account() );
		update_post_meta( bwc_get_order_property( 'id', $order ), '_payment_method_token', self::$payment_method_token );
		update_post_meta( bwc_get_order_property( 'id', $order ), '_payment_method_title', $payment_method_title );
		update_post_meta( bwc_get_order_property( 'id', $order ), '_braintree_environment', bt_manager()->get_environment() );
		if ( $order instanceof WC_Subscription ) {
			update_post_meta( bwc_get_order_property( 'id', $order ), '_subscription_type', 'woocommerce' );
		}
		
		$subscriptions = wcs_get_subscriptions_for_order( $order );
		
		// Save the metadata for the subscriptions.
		foreach ( $subscriptions as $subscription ) {
			if ( ! bwcs_is_braintree_subscription( $subscription ) ) { // only save non braintree subscriptions.
				$this->save_synchronized_subscription_meta( $subscription );
			}
		}
	}

	/**
	 * Save the subscription metadata for the Braintree Subscription.
	 *
	 * @param WC_Subscription $subscription        	
	 * @param Braintree_Subscription $bt_subscription        	
	 */
	public function save_wc_braintree_subscription_meta( $subscription, $bt_subscription )
	{
		$user_id = wp_get_current_user()->ID;
		$token = $bt_subscription->paymentMethodToken;
		$payment_method = braintree_get_payment_method_from_token( $user_id, $token );
		$payment_method_title = braintree_get_payment_method_title_from_array( $payment_method );
		$post_id = bwc_get_order_property( 'id', $subscription );
		
		update_post_meta( $post_id, '_payment_method', $this->id );
		update_post_meta( $post_id, '_braintree_version', bt_manager()->version );
		update_post_meta( $post_id, '_created_in_braintree', true );
		update_post_meta( $post_id, '_merchant_account_id', $bt_subscription->merchantAccountId );
		update_post_meta( $post_id, '_payment_method_token', $token );
		update_post_meta( $post_id, '_payment_method_title', $payment_method_title );
		update_post_meta( $post_id, '_braintree_plan', $bt_subscription->planId );
		update_post_meta( $post_id, '_subscription_type', 'braintree' );
	}

	/**
	 * If the setting for updating the order status is enabled, allow the plugin
	 * order status to be set.
	 * This will override the status set by WooCommerce.
	 *
	 * @param string $status        	
	 * @param int $id        	
	 * @return string
	 */
	public static function maybe_update_order_status( $status, $id )
	{
		$order = wc_get_order( $id );
		if ( $order && bwc_get_order_property( 'payment_method', $order ) === static::ID ) {
			$order_status = bt_manager()->get_option( 'order_status' );
			
			if ( $order_status !== 'default' ) {
				$status = $order_status;
			}
		}
		return $status;
	}

	public function woocommerce_gateway_icon( $icon, $id )
	{
		if ( $id === $this->id ) {
			if ( bwc_payment_icons_outside() ) {
				ob_start();
				bwc_get_template( 'checkout/payment-method-icons.php', array (
						'icons_inside' => false 
				) );
				$icon = ob_get_clean();
			}
		}
		return $icon;
	}

	public function woocommerce_gateway_title( $title, $id )
	{
		if ( $id === $this->id ) {
			if ( bt_manager()->get_environment() === 'sandbox' ) {
				$title = sprintf( '%s ( %s )', $title, __( 'Sandbox', 'braintree-payments' ) );
			}
		}
		return $title;
	}

	/**
	 * Populate an array of saved payment methods for the user.
	 * This array of payment methods is displayed
	 * on the payment-methods page.
	 *
	 * @param array $saved_methods        	
	 * @param int $user_id        	
	 */
	public static function saved_payment_method_list( $saved_methods, $user_id )
	{
		$methods = bwc_get_user_payment_methods( $user_id );
		
		return bwc_saved_payment_methods_list( $saved_methods, $methods, static::ID );
	}

	/**
	 * Check to see if this is a request to delete a payment method.
	 * If true, then delete the payment method.
	 * Do not allow a user to delete a payment method if there is a subscription
	 * associated with it.
	 */
	public static function maybe_delete_payment_method()
	{
		$nonce = isset( $_GET [ '_wpnonce' ] ) ? $_GET [ '_wpnonce' ] : '';
		
		if ( isset( $_GET [ static::ID . '_delete_method' ] ) && wp_verify_nonce( $nonce, 'delete-payment-method' ) ) {
			
			$user_id = wp_get_current_user()->ID;
			$token = $_GET [ static::ID . '_delete_method' ];
			
			// Allow other functionality to prevent the delete from occuring.
			$can_delete = apply_filters( 'braintree_wc_can_delete_payment_method', new WP_Error(), $token );
			
			if ( $can_delete->get_error_message() ) {
				wc_add_notice( sprintf( __( 'Your payment method cannot be deleted. Reason: %s', 'braintree-payments' ), $can_delete->get_error_message() ), 'error' );
				return;
			}
			
			try {
				$result = PaymentMethod::delete( $token );
				
				if ( $result->success ) {
					
					braintree_delete_user_payment_method( $user_id, $token );
					
					wc_add_notice( __( 'Your payment method has been deleted.', 'braintree-payments' ), 'success' );
					do_action( 'braintree_wc_delete_payment_method_success', $result, $token );
				} else {
					wc_add_notice( sprintf( __( 'Your payment method could not be deleted. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
					
					do_action( 'braintree_wc_delete_payment_method_error', $result );
				}
			} catch( \Braintree\Exception $e ) {
				do_action( 'braintree_wc_delete_payment_method_exception', array (
						'user_id' => $user_id, 
						'exception' => $e, 
						'token' => $token, 
						'method' => __METHOD__, 
						'line' => __LINE__ 
				) );
				braintree_delete_user_payment_method( $user_id, $token );
				wc_add_notice( sprintf( __( 'Your payment method could not be deleted at this time. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ), 'error' );
			}
		}
	}

	/**
	 * Add a payment method to the user's account after vaulting it in Braintree.
	 */
	public function add_payment_method( $attribs = array() )
	{
		$nonce = self::get_request_param( static::$nonce_id );
		$user = wp_get_current_user();
		
		try {
			$default_attribs = array (
					'customerId' => braintree_get_customer_id( $user->ID ), 
					'paymentMethodNonce' => $nonce, 
					'options' => array (
							'failOnDuplicatePaymentMethod' => bwc_fail_on_duplicate(), 
							'makeDefault' => true, 
							'verifyCard' => true 
					) 
			);
			$attribs = wp_parse_args( $attribs, $default_attribs );
			if ( is_add_payment_method_page() ) {
				$attribs [ 'cardholderName' ] = sprintf( '%s %s', $user->first_name, $user->last_name );
			}
			if ( bwc_is_advanced_fraud_tools() ) {
				$attribs [ 'deviceData' ] = stripslashes( self::get_request_param( static::$device_data_id ) );
			}
			$result = Braintree_PaymentMethod::create( $attribs );
			
			if ( $result->success ) {
				
				braintree_save_user_payment_method( $user->ID, $result->paymentMethod );
				self::$payment_method_token = $result->paymentMethod->token;
				do_action( 'braintree_wc_add_payment_method_success', $result );
				return array (
						'result' => 'success', 
						'redirect' => wc_get_endpoint_url( 'payment-methods' ) 
				);
			} else {
				do_action( 'braintree_wc_add_payment_method_error', $result );
				wc_add_notice( sprintf( __( 'There was an error saving your payment method. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
				return array (
						'result' => 'failure' 
				);
			}
		} catch( \Braintree\Exception $e ) {
			do_action( 'braintree_wc_add_payment_method_exception', array (
					'exception' => $e, 
					'method' => __METHOD__, 
					'line' => __LINE__, 
					'nonce' => $nonce 
			) );
			wc_add_notice( sprintf( __( 'There was an error saving your payment method. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ), 'error' );
			return array (
					'result' => 'failure' 
			);
		}
	}

	/**
	 * Update the payment method for the subscription.
	 * If the subscription is from another gateway,
	 * then simply update the payment method token. Even if the product is configured as a Braintree Subscription,
	 * it is better to let WC Subscriptions continue to handle this subscription, rather then create a new Braintree Subscription.
	 *
	 * @param WC_Subscription $subscription        	
	 * @param string $old_payment_method        	
	 */
	public function payment_method_updated_to( $subscription, $old_payment_method )
	{
		if ( ! self::use_saved_method() ) {
			// save the payment method
			$result = $this->add_payment_method();
			if ( $result [ 'result' ] !== 'success' ) {
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_new_payment_method', $old_payment_method );
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method', $old_payment_method );
				return;
			}
			$token = self::$payment_method_token;
		} else {
			$token = self::get_request_param( static::$token_id );
		}
		
		$payment_method_title = braintree_get_payment_method_title_from_array( braintree_get_payment_method_from_token( wp_get_current_user()->ID, $token ) );
		
		// subscription must be a Braintree subscription and it must simply be a payment method change and not a payment gateway change.
		if ( bwcs_is_braintree_subscription( $subscription ) && bwc_get_order_property( 'payment_method', $subscription ) === $old_payment_method ) {
			try {
				$result = Braintree_Subscription::update( bwc_get_order_property( 'id', $subscription ), array (
						'paymentMethodToken' => $token 
				) );
				if ( $result->success ) {
					update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_token', $token );
					update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_title', $payment_method_title );
				} else {
					wc_add_notice( sprintf( __( 'Error changing payment methods for subscription. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
					update_post_meta( bwc_get_order_property( 'id', $subscription ), '_new_payment_method', $old_payment_method );
					update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method', $old_payment_method );
				}
			} catch( \Braintree\Exception $e ) {
				wc_add_notice( __( 'Error changing payment method for subscription.', 'braintree-payments' ), 'error' );
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_new_payment_method', $old_payment_method );
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method', $old_payment_method );
			}
		} else {
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_token', $token );
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_title', $payment_method_title );
		}
	}

	/**
	 * Update the Braintree subscription's payment method.
	 *
	 * @param Braintree_Gateway_WC_Subscription $subscription        	
	 * @return mixed bool|WP_Error
	 */
	public function change_subscription_payment_method( $subscription )
	{
		$user_id = wp_get_current_user()->ID;
		
		if ( ! self::use_saved_method() ) {
			
			// save the payment method
			$result = $this->add_payment_method();
			if ( $result [ 'result' ] === 'failure' ) {
				return new WP_Error();
			}
			$token = self::$payment_method_token;
		} else {
			$token = self::get_request_param( static::$token_id );
		}
		$result = true;
		try {
			$result = Braintree_Subscription::update( bwc_get_order_property( 'id', $subscription ), array (
					'paymentMethodToken' => $token 
			) );
			
			if ( $result->success ) {
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_title', braintree_get_payment_title_from_token( $user_id, $token ) );
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_token', $result->subscription->paymentMethodToken );
				bt_manager()->success( sprintf( __( 'Payment method for Braintree subscription %s updated to %s.', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ), $subscription->get_payment_method_to_display() ) );
			} else {
				bt_manager()->error( sprintf( __( 'Error changing payment method for Braintree subscription %s. Reason: %s', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ), $result->message ) );
				$result = new WP_Error( 'subscription-error', sprintf( __( 'Error changing payment method for subscription. Reason: %s', 'braintree-payments' ), $result->message ) );
			}
		} catch( \Braintree\Exception $e ) {
			$message = sprintf( __( 'Error changing payment method for Braintree subscription %s. Exception: %s.', 'braintree-payments' ), bwc_get_order_property( 'id', $subscription ), get_class( $e ) );
			$subscription->add_order_note( $message );
			bt_manager()->error( $message );
			$result = new WP_Error( 'subscription-error', __( 'There was an error changing your payment method. If the issue continues please contact us.', 'braintree-payments' ) );
		}
		return $result;
	}

	/**
	 * Save the payment method data from the paid for order to the subscription metadata so future renewal orders won't fail.
	 *
	 * @param WC_Subscription $subscription        	
	 * @param WC_Order $order        	
	 */
	public static function update_failing_payment_method( $subscription, $order )
	{
		// only update the subscription's payment method if the order was paid for using a saved payment method.
		if ( bwc_get_order_property( 'payment_method_token', $order ) ) {
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_token', bwc_get_order_property( 'payment_method_token', $order ) );
			update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method_title', bwc_get_order_property( 'payment_method_title', $order ) );
		}
	}

	/**
	 *
	 * @param int $subscription_id        	
	 *
	 * @return mixed bool|WP_Error
	 */
	public function pay_for_braintree_subscription( $subscription_id )
	{
		$subscription = bfwcs_get_subscription( $subscription_id );
		$user_id = wp_get_current_user()->ID;
		
		if ( ! self::use_saved_method() ) {
			$result = $this->add_payment_method();
			if ( $result [ 'result' ] === 'failure' ) {
				return new WP_Error();
			}
		}
		
		$attribs = array (
				'id' => bwc_get_order_property( 'id', $subscription ), 
				'price' => $subscription->get_total(), 
				'planId' => bwc_get_order_property( 'braintree_plan', $subscription ), 
				'merchantAccountId' => bwc_get_order_property( 'merchant_account_id', $subscription ) 
		);
		self::add_order_payment_method( $attribs );
		if ( $subscription->never_expires() ) {
			$attribs [ 'neverExpires' ] = true;
		} else {
			$attribs [ 'numberOfBillingCycles' ] = $subscription->get_num_of_billing_cycles();
		}
		
		/* calculate the start date etc. */
		if ( $subscription->has_trial() ) {
			$attribs [ 'trialDuration' ] = $subscription->get_trial_length();
			$attribs [ 'trialDurationUnit' ] = $subscription->get_trial_period();
			$attribs [ 'trialPeriod' ] = true;
		} else {
			if ( $subscription->last_day_of_month() ) {
				$attribs [ 'billingDayOfMonth' ] = 31; // set billing day to last day of month in Braintree
			} else {
				// $attribs [ 'firstBillingDate' ] = $subscription->get_date( 'start' );
				$attribs [ 'options' ] [ 'startImmediately' ] = true;
			}
		}
		
		if ( $subscription->has_descriptors() ) {
			self::set_subscription_descriptors( $attribs, $subscription );
		}
		
		try {
			$subscription->sync_dates();
			$subscription->update_meta( 'subscription_processing', true );
			$result = Braintree_Subscription::create( $attribs );
			if ( $result->success ) {
				$subscription->update_status( 'active' );
				$subscription->add_order_note( __( 'Subscription created successfully in Braintree.', 'braintree-payments' ) );
				$subscription->update_payment_method_title( braintree_get_payment_title_from_token( $user_id, $result->subscription->paymentMethodToken ) );
				$subscription->update_payment_method_token( $result->subscription->paymentMethodToken );
				update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method', $this->id );
				$subscription->set_created( true );
				$subscription->update_meta( 'subscription_processing', false );
				wc_add_notice( __( 'Your payment method has been added to the subscription.', 'braintree-payments' ), 'success' );
				return true;
			} else {
				$message = sprintf( __( 'Error creating subscription. Reason: %s', 'braintree-payments' ), $result->message );
				return new WP_Error( 'subscription-error', $message );
			}
		} catch( \Braintree\Exception $e ) {
			return new WP_Error( 'subscription-error', sprintf( __( 'There was an error creating your subscription. Please contact us if the issue continues.', 'braintree-payments' ) ) );
		}
	}

	/**
	 * Add customer metadata before a new user is inserted into the database.
	 *
	 * @param array $customer_data        	
	 * @param WP_User $user        	
	 * @param bool $update        	
	 */
	public static function woocommerce_new_customer_data( $customer_data, $user, $update )
	{
		// if called during checkout, then proceed with adding customer data. This will ensure
		// that when a customer is created within Braintree, the firstName, lastName, etc is populated.
		if ( defined( 'WOOCOMMERCE_CHECKOUT' ) && ! $update ) {
			$checkout = WC()->checkout();
			$customer_data [ 'first_name' ] = $checkout->posted [ 'billing_first_name' ];
			$customer_data [ 'last_name' ] = $checkout->posted [ 'billing_last_name' ];
			$customer_data [ 'billing_phone' ] = $checkout->posted [ 'billing_phone' ];
			$customer_data [ 'billing_email' ] = $checkout->posted [ 'billing_email' ];
			$customer_data [ 'billing_company' ] = $checkout->posted [ 'billing_company' ];
		}
		return $customer_data;
	}

	/**
	 * Perform gateway specific validation.
	 *
	 * @param WC_Order $order        	
	 */
	public static function before_order_process( $order )
	{
		do_action( 'bfwc_before_process_order_' . bwc_get_order_property( 'payment_method', $order ), $order );
	}

	/**
	 * Hooked into the 'bfwc_before_process_order_' + gateway_id action.
	 * If a customer is requesting to save a payment method, then save it here.
	 *
	 * @param WC_Order $order        	
	 */
	public static function maybe_save_payment_method( $order )
	{
		// customer has chosen a saved payment method so exit;
		if ( self::use_saved_method() ) {
			return;
		}
		$save_method = false;
		if ( class_exists( 'WC_Subscriptions_Cart' ) && WC_Subscriptions_Cart::cart_contains_subscription() ) {
			$save_method = true;
		} elseif ( bfwcs_cart_contains_subscriptions() ) {
			$save_method = true;
		}
		
		// commented in version 2.6.7
		
		if ( $save_method ) {
			$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$gateway = $available_gateways [ bwc_get_order_property( 'payment_method', $order ) ];
			
			// add the payment method.
			$result = $gateway->add_payment_method( apply_filters( 'bfwc_maybe_save_payment_method_attribs', array (
					'billingAddress' => array (
							'firstName' => bwc_get_order_property( 'billing_first_name', $order ), 
							'lastName' => bwc_get_order_property( 'billing_last_name', $order ), 
							'postalCode' => bwc_get_order_property( 'billing_postcode', $order ), 
							'streetAddress' => bwc_get_order_property( 'billing_address_1', $order ), 
							'region' => bwc_get_order_property( 'billing_state', $order ), 
							'countryCodeAlpha2' => bwc_get_order_property( 'billing_country', $order ) 
					), 
					'cardholderName' => sprintf( '%s %s', bwc_get_order_property( 'billing_first_name', $order ), bwc_get_order_property( 'billing_last_name', $order ) ) 
			), $order ) );
			global $bfwc_error_message;
			if ( $result [ 'result' ] === 'failure' && $bfwc_error_message ) {
				$order->add_order_note( sprintf( __( 'Payment method could no be saved while processing order. Reason: %s', 'braintree-payments' ), $bfwc_error_message ) );
				$order->update_status( 'failed' );
			}
		}
	}

	/**
	 *
	 * @since 2.6.7
	 */
	public static function should_save_payment_method()
	{
		return ! self::use_saved_method() && ! empty( $_POST [ static::$save_method_name ] );
	}

	public static function woocommerce_payment_complete( $order_id )
	{
		if ( function_exists( 'bfwcs_order_contains_subscription' ) && bfwcs_order_contains_subscription( $order_id ) ) {
			$subscriptions = bfwcs_get_subscriptions_for_order( $order_id );
			foreach ( $subscriptions as $subscription ) {
				delete_post_meta( bwc_get_order_property( 'id', $subscription ), '_subscription_processing' );
			}
		} elseif ( function_exists( 'wcs_get_subscriptions_for_order' ) && wcs_order_contains_subscription( $order_id ) ) {
			$subscriptions = wcs_get_subscriptions_for_order( $order_id );
			foreach ( $subscriptions as $subscription ) {
				delete_post_meta( bwc_get_order_property( 'id', $subscription ), '_subscription_processing' );
			}
		}
		// delete any session data.
		unset( WC()->session->bfwc_frontend_client_token );
	}

	/**
	 * Provides updated checkout vars when the checkout page has been updated.
	 */
	public static function updated_checkout()
	{
		if ( ! check_ajax_referer( 'update-checkout-vars', 'security' ) ) {
			wp_send_json( array (
					'success' => false 
			) );
			die();
		}
		if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
			define( 'WOOCOMMERCE_CHECKOUT', true );
		}
		$handle = isset( $_POST [ 'bfwc_handle' ] ) ? Braintree_Gateway_Frontend_Scripts::$prefix . $_POST [ 'bfwc_handle' ] : '';
		do_action( 'braintree_before_localize_frontend_scripts' );
		$vars = Braintree_Gateway_Frontend_Scripts::get_script_data( $handle );
		wp_send_json_success( $vars, 200 );
	}

	public static function update_order_review_fragments( $fragments )
	{
		if ( ! bwc_refresh_payment_fragments() ) {
			unset( $fragments [ '.woocommerce-checkout-payment' ] );
		}
		return $fragments;
	}

	/**
	 * Generate a client token.
	 *
	 * @param array $attribs        	
	 */
	public static function generate_client_token( $include_merchant = false )
	{
		$attribs = $include_merchant ? array (
				'merchantAccountId' => bwc_get_merchant_account() 
		) : array ();
		return bt_manager()->get_client_token( $attribs );
	}

	/**
	 * Add the Braintree transaction id to the refund.
	 *
	 * @param int $order_id        	
	 * @param int $refund_id        	
	 */
	public static function woocommerce_order_refunded( $order_id, $refund_id )
	{
		global $bfwc_refund_transaction;
		$order = wc_get_order( $order_id );
		if ( in_array( bwc_get_order_property( 'payment_method', $order ), bwc_get_payment_gateways() ) && $bfwc_refund_transaction ) {
			update_post_meta( $refund_id, '_transaction_id', $bfwc_refund_transaction->id );
		}
	}

	/**
	 * Perform validations on the posted data before checkout takes place.
	 *
	 * @param array $posted        	
	 */
	public static function after_checkout_validation( $posted )
	{
		if ( bwc_enable_signup_from_checkout() && ! is_user_logged_in() && ! empty( $_POST [ static::$save_method_name ] ) ) {
			if ( empty( $posted [ 'createaccount' ] ) && ! WC()->checkout()->must_create_account ) {
				wc_add_notice( __( 'In order to save a payment method, you must create an account.', 'braintree-payments' ), 'error' );
			}
		}
	}

	/**
	 * Remove the postalCode attribute if the credit card form has postal code enabled and this is a new payment method.
	 * Braintree's API will use the postal code from the nonce if none is provided in the billing array's attributes.
	 *
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 */
	public static function maybe_remove_postal_code( $attribs, $order = null )
	{
		if ( isset( $attribs [ 'billing' ] ) || isset( $attribs [ 'postalCode' ] ) ) {
			$gateways = WC()->payment_gateways()->get_available_payment_gateways();
			$gateway = $order ? $gateways [ bwc_get_order_property( 'payment_method', $order ) ] : ( isset( $_POST [ 'payment_method' ] ) ? $gateways [ $_POST [ 'payment_method' ] ] : null );
			if ( $gateway && $gateway->supports( 'bfwc_credit_card_form' ) && bwc_postal_code_enabled() && ! self::use_saved_method() ) {
				if ( isset( $attribs [ 'postalCode' ] ) ) {
					unset( $attribs [ 'postalCode' ] );
				} else {
					unset( $attribs [ 'billing' ] [ 'postalCode' ] );
				}
			}
		}
		return $attribs;
	}

	public function get_option_key()
	{
		if ( version_compare( WC()->version, '2.6.0', '<' ) ) {
			return $this->plugin_id . $this->id . '_settings';
		} else {
			return parent::get_option_key();
		}
	}

	public function init_settings()
	{
		$options = get_option( $this->get_option_key(), array () );
		$this->settings = array_merge( $options, $this->settings );
	}

	public function get_option( $key, $empty_value = null )
	{
		if ( ! isset( $this->settings [ $key ] ) ) {
			$form_fields = ( ! $fields = $this->get_form_fields() ) ? array () : $fields;
			$this->settings [ $key ] = $empty_value ? $empty_value : ( isset( $form_fields [ $key ] ) ? $this->get_field_default( $form_fields [ $key ] ) : '' );
		}
		return $this->settings [ $key ];
	}

	/**
	 * Update the customer info stored in the Braintree vault.
	 *
	 * @deprecated 2.6.7
	 *            
	 * @param int $user_id        	
	 */
	public static function update_vaulted_customer_details( $user_id )
	{
		$user = get_user_by( 'id', $user_id );
		$customer_id = braintree_get_customer_id( $user_id );
		try {
			$result = Braintree_Customer::update( $customer_id, apply_filters( 'bfwc_update_vaulted_customer_details', array (
					'firstName' => $user->first_name, 
					'lastName' => $user->last_name, 
					'email' => $user->user_email, 
					'phone' => get_user_meta( $user_id, 'billing_phone', true ), 
					'company' => get_user_meta( $user_id, 'billing_company', true ), 
					'website' => $user->user_url 
			) ) );
			if ( ! $result->success ) {
				wc_add_notice( sprintf( __( 'There was an error updating your account information. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
			}
		} catch( \Braintree\Exception $e ) {
			wc_add_notice( sprintf( __( 'There was an error updating your account information. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ), 'error' );
		}
	}

	/**
	 * Update the Braintree customer's info if a change is detected.
	 *
	 * @since 2.6.7
	 * @param array $posted        	
	 */
	public static function checkout_update_customer( $posted )
	{
		$user = wp_get_current_user();
		$user_id = $user->ID;
		$new_data = array (
				'billing_phone' => $posted [ 'billing_phone' ], 
				'billing_company' => $posted [ 'billing_company' ] 
		);
		self::update_customer_during_checkout( $user_id, $new_data );
	}

	/**
	 *
	 * @since 2.6.7
	 * @param int $user_id        	
	 * @param array $new_data        	
	 */
	public static function update_customer_during_checkout( $user_id, $new_data )
	{
		$user = get_user_by( 'id', $user_id );
		$customer_id = braintree_get_customer_id( $user_id );
		
		if ( $customer_id ) {
			$old_meta = array (
					'first_name' => $user->first_name, 
					'last_name' => $user->last_name, 
					'email' => $user->user_email, 
					'billing_phone' => get_user_meta( $user_id, 'billing_phone', true ), 
					'billing_company' => get_user_meta( $user_id, 'billing_company', true ), 
					'website' => $user->user_url 
			);
			$new_meta [ 'first_name' ] = isset( $new_data [ 'first_name' ] ) ? $new_data [ 'first_name' ] : $old_meta [ 'first_name' ];
			$new_meta [ 'last_name' ] = isset( $new_data [ 'last_name' ] ) ? $new_data [ 'last_name' ] : $old_meta [ 'last_name' ];
			$new_meta [ 'email' ] = isset( $new_data [ 'email' ] ) ? $new_data [ 'email' ] : $old_meta [ 'email' ];
			$new_meta [ 'billing_phone' ] = isset( $new_data [ 'billing_phone' ] ) ? $new_data [ 'billing_phone' ] : $old_meta [ 'billing_phone' ];
			$new_meta [ 'billing_company' ] = isset( $new_data [ 'billing_company' ] ) ? $new_data [ 'billing_company' ] : $old_meta [ 'billing_company' ];
			$new_meta [ 'website' ] = isset( $new_data [ 'website' ] ) ? $new_data [ 'website' ] : $old_meta [ 'website' ];
			if ( $old_meta != $new_meta ) {
				try {
					$customer_id = braintree_get_customer_id( $user_id );
					$result = Braintree_Customer::update( $customer_id, array (
							'firstName' => $new_meta [ 'first_name' ], 
							'lastName' => $new_meta [ 'last_name' ], 
							'email' => $new_meta [ 'email' ], 
							'phone' => $new_meta [ 'billing_phone' ], 
							'company' => $new_meta [ 'billing_company' ], 
							'website' => $new_meta [ 'website' ] 
					) );
					if ( ! $result->success ) {
						bt_manager()->error( sprintf( __( 'Error updating customer information during checkout. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $result ) ) );
					}
				} catch( \Braintree\Exception $e ) {
					bt_manager()->error( sprintf( __( 'Error updating customer information during checkout. Reason: %s', 'braintree-payments' ), bfwc_get_error_message( $e ) ) );
				}
			}
		}
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $method        	
	 */
	public static function output_payment_method( $method )
	{
		if ( isset( $method [ 'method' ] [ 'bfwc_token' ] ) ) {
			if ( bwc_payment_icons_enclosed_type() ) {
				printf( '<span class="bfwc-payment-method-method"><img src="%s"/></span>', braintree_get_method_url( $method [ 'method' ] [ 'method_type' ] ) );
			} else {
				printf( '<span class="bfwc-payment-method-open %s"></span>', $method [ 'method' ] [ 'method_type' ] );
			}
			if ( ! empty( $method [ 'method' ] [ 'last4' ] ) ) {
				printf( __( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method [ 'method' ] [ 'brand' ] ) ), esc_html( $method [ 'method' ] [ 'last4' ] ) );
			} else {
				echo esc_html( wc_get_credit_card_type_label( $method [ 'method' ] [ 'brand' ] ) );
			}
		} else {
			if ( ! empty( $method [ 'method' ] [ 'last4' ] ) ) {
				/* translators: 1: credit card type 2: last 4 digits */
				echo sprintf( __( '%1$s ending in %2$s', 'woocommerce' ), esc_html( wc_get_credit_card_type_label( $method [ 'method' ] [ 'brand' ] ) ), esc_html( $method [ 'method' ] [ 'last4' ] ) );
			} else {
				echo esc_html( wc_get_credit_card_type_label( $method [ 'method' ] [ 'brand' ] ) );
			}
		}
	}

	public static function generate_payment_nonce_for_token()
	{
		if ( ! check_ajax_referer( 'payment-method-nonce', 'security' ) ) {
			wp_send_json( array (
					'success' => false 
			) );
			die();
		}
		$token = isset( $_POST [ 'bfwc_payment_token' ] ) ? $_POST [ 'bfwc_payment_token' ] : '';
		try {
			$result = Braintree_PaymentMethodNonce::create( $token );
			wp_send_json_success( $result->paymentMethodNonce->nonce, 200 );
		} catch( \Braintree\Exception $e ) {
			wp_send_json_error( array (
					'code' => '91903' 
			), 200 );
		}
	}

	/**
	 *
	 * @since 2.6.7
	 */
	public static function initialize_threeds_validation()
	{
		if ( bwc_is_3ds_active() && ! bwc_3ds_no_action_needed() ) {
			$nonce = self::get_request_param( static::$nonce_id );
			try {
				$payment_nonce = Braintree_PaymentMethodNonce::find( $nonce );
				$threeds_validation = new WC_Braintree_Gateway_3DS_Validation( $payment_nonce );
				$threeds_validation->init();
			} catch( \Braintree\Exception $e ) {
				bt_manager()->error( __( 'Invalid nonce provided during 3d secure validation.', 'braintree-payments' ) );
			}
		}
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 * @param WC_Payment_Gateway $gateway        	
	 */
	public static function threeds_authorize( $attribs, $order, $gateway )
	{
		$attribs [ 'options' ] = array (
				'submitForSettlement' => false 
		);
		return $attribs;
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $attribs        	
	 * @param WC_Order $order        	
	 * @param WC_Payment_Gateway $gateway        	
	 */
	public static function threeds_accept( $attribs, $order, $gateway )
	{
		$attribs [ 'options' ] [ 'threeDSecure' ] = array (
				'required' => false 
		);
		return $attribs;
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $data        	
	 */
	public static function threeds_reject( $data )
	{
		wc_add_notice( __( 'Your payment method could not be processed at this time. Reason: 3D Secure not accepted or validation failed.', 'braintree-payments' ), 'error' );
	}

	public function admin_options()
	{
		bfwc_admin_get_template( 'views/admin-gateway-options.php', array (
				'gateway' => $this 
		) );
	}

	/**
	 * Print the WC_Cart total to the order review section of the checkout page.
	 *
	 * @since 2.6.8
	 */
	public static function print_cart_total()
	{
		printf( '<input type="hidden" id="bfwc_cart_total" value="%s"/>', WC()->cart->total );
	}

	/**
	 * Output the order's total so it can used for 3DS, PayPal, etc.
	 *
	 * @since 2.6.8
	 */
	public static function add_output_order_total()
	{
		add_action( 'woocommerce_pay_order_before_submit', function ()
		{
			global $wp;
			$order = wc_get_order( $wp->query_vars [ 'order-pay' ] );
			printf( '<input type="hidden" id="bfwc_cart_total" value="%s"/>', $order->get_total() );
		} );
	}

	public function admin_options_extended()
	{
		echo '<h2>' . __( 'Additional Options', 'braintree-payments' ) . '</h2>';
		echo '<p>' . __( 'These are options available through other plugins that utilize the payment gateway.', 'braintree-payments' ) . '</p>';
		echo '<table class="form-table">' . $this->generate_settings_html( $this->get_form_fields(), false ) . '</table>';
	}
}
WC_Braintree_Payment_Gateway::init();