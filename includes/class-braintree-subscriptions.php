<?php
use Braintree\Subscription;
class Braintree_Gateway_Subscriptions
{

	public static function init()
	{
		add_action( 'bfwc_subscription_status_cancelled', __CLASS__ . '::cancel_subscription', 10, 2 );
		
		add_action( 'woocommerce_before_my_account', __CLASS__ . '::my_subscriptions_template' );
		
		add_action( 'woocommerce_account_menu_items', __CLASS__ . '::add_subscriptions_endpoint' );
		
		add_action( 'woocommerce_account_subscriptions_endpoint', __CLASS__ . '::view_subscriptions' );
		
		add_action( 'woocommerce_account_view-subscription_endpoint', __CLASS__ . '::view_subscription', 10, 1 );
		
		add_action( 'woocommerce_account_change-payment-method_endpoint', __CLASS__ . '::change_payment_method_view' );
		
		add_action( 'wp_loaded', __CLASS__ . '::maybe_cancel_subscription' );
		
		add_action( 'wp_loaded', __CLASS__ . '::maybe_change_payment_method' );
		
		add_action( 'wp_loaded', __CLASS__ . '::pay_for_subscription' );
		
		add_filter( 'braintree_wc_can_delete_payment_method', 'bfwc_can_delete_payment_method', 10, 2 );
		
		add_filter( 'bfwc_admin_can_delete_payment_method', 'bfwc_can_delete_payment_method', 10, 2 );
		
		add_action( 'woocommerce_account_pay-subscription_endpoint', __CLASS__ . '::pay_for_subscription_template' );
		
		add_filter( 'woocommerce_available_payment_gateways', __CLASS__ . '::available_payment_gateways' );
		
		add_action( 'woocommerce_after_checkout_validation', __CLASS__ . '::force_must_create_account' );
		
		add_action( 'woocommerce_checkout_init', __CLASS__ . '::maybe_disable_guest_checkout' );
		
		// WC 3.0.0
		add_filter( 'woocommerce_checkout_registration_required', __CLASS__ . '::checkout_registration_required' );
	}

	/**
	 * Cancel the Braintree subscription.
	 *
	 * @param string $old_status        	
	 * @param Braintree_Gateway_WC_Subscription $subscription        	
	 */
	public static function cancel_subscription( $old_status, $subscription )
	{
		try {
			$result = Subscription::cancel( bwc_get_order_property( 'id', $subscription ) );
			
			if ( $result->success ) {
				$subscription->add_order_note( sprintf( __( 'Subscription in Braintree has been cancelled. Billing will no longer be triggered for this subscription.', 'braintree-payments' ) ) );
				
				if ( ! is_admin() ) {
					wc_add_notice( sprintf( __( 'Subscription has been cancelled successfully.', 'braintree-payments' ) ), 'success' );
				}
				return;
			} else {
				if ( ! is_admin() ) {
					wc_add_notice( sprintf( __( 'Braintree subscription could not be cancelled. Reason: %s', 'braintree-payments' ), $result->message ), 'error' );
				}
				throw new Exception( sprintf( __( 'Braintree subscription could not be cancelled. Reason: %s', 'braintree-payments' ), $result->message ) );
			}
		} catch( \Braintree\Exception $e ) {
			if ( ! is_admin() ) {
				wc_add_notice( sprintf( __( 'Braintree subscription could not be cancelled. Exception: %s', 'braintree-payments' ), get_class( $e ) ), 'error' );
			}
			throw new Exception( sprintf( __( 'Braintree subscription could not be cancelled. Exception: %s', 'braintree-payments' ), get_class( $e ) ) );
		}
	}

	public static function my_subscriptions_template()
	{
		// only show template if version is less than 2.6
		if ( bfwcs_subscription_link_active() && version_compare( 2.6, WC()->version, '>' ) ) {
			$subscriptions = bfwcs_get_subscriptions_for_user( wp_get_current_user()->ID );
			bwc_get_template( 'myaccount/my-subscriptions.php', array (
					'subscriptions' => $subscriptions 
			) );
		}
	}

	public static function add_subscriptions_endpoint( $menu_items )
	{
		if ( bfwcs_subscription_link_active() ) {
			$menu_items [ 'subscriptions' ] = __( 'Subscriptions', 'braintree-payments' );
		}
		return $menu_items;
	}

	public static function view_subscriptions()
	{
		$subscriptions = bfwcs_get_subscriptions_for_user( wp_get_current_user()->ID );
		bwc_get_template( 'myaccount/my-subscriptions.php', array (
				'subscriptions' => $subscriptions 
		) );
	}

	public static function view_subscription( $id )
	{
		$subscription = bfwcs_get_subscription( $id );
		if ( ! $subscription ) {
			wc_add_notice( sprintf( __( 'Subscription %s is not a valid subscription.', 'braintree-payments' ), $id ), 'error' );
			wc_print_notices();
		} else {
			$subscription->sync_dates();
			bwc_get_template( 'myaccount/view-subscription.php', array (
					'subscription' => $subscription 
			) );
		}
	}

	/**
	 * Check if this is a cancel subscription request.
	 */
	public static function maybe_cancel_subscription()
	{
		$nonce = isset( $_REQUEST [ '_wpnonce' ] ) ? $_REQUEST [ '_wpnonce' ] : '';
		
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'cancel-subscription' ) || ! isset( $_REQUEST [ 'cancel-subscription' ] ) ) {
			return;
		}
		
		$subscription = bfwcs_get_subscription( absint( $_REQUEST [ 'cancel-subscription' ] ) );
		
		/**
		 *
		 * @param Braintree_Gateway_WC_Subscription $subscription        	
		 */
		$subscription->update_status( 'cancelled' );
	
	}

	/**
	 * Output the template for the change-payment-method page.
	 */
	public static function change_payment_method_view( $id )
	{
		$subscription = bfwcs_get_subscription( $id );
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		bwc_get_template( 'myaccount/change-payment-method.php', array (
				'subscription' => $subscription, 
				'available_gateways' => $available_gateways 
		) );
	}

	public static function is_change_payment_method()
	{
		if ( bfwcs_is_change_payment_method() ) {
			return true;
		}
	}

	/**
	 * Check if this is a payment method change request.
	 * If so, then process the change.
	 */
	public static function maybe_change_payment_method()
	{
		$nonce = isset( $_POST [ '_change_method_nonce' ] ) ? $_POST [ '_change_method_nonce' ] : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'change-payment-method' ) ) {
			return;
		}
		
		$payment_method = $_POST [ 'payment_method' ];
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		if ( ! $gateway = isset( $available_gateways [ $payment_method ] ) ? $available_gateways [ $payment_method ] : false ) {
			wc_add_notice( __( 'Invalid payment gateway. Please try again.', 'braintree-payments' ), 'error' );
			return;
		}
		$subscription = bfwcs_get_subscription( absint( $_POST [ 'bfwc_subscription' ] ) );
		
		$result = $gateway->change_subscription_payment_method( $subscription );
		
		if ( is_wp_error( $result ) ) {
			if ( $result->get_error_message() ) {
				wc_add_notice( $result->get_error_message(), 'error' );
			}
			return;
		}
		update_post_meta( bwc_get_order_property( 'id', $subscription ), '_payment_method', $gateway->id );
		
		$subscription->add_order_note( sprintf( __( 'Payment method for braintree subscription has been updated.', 'braintree-payments' ) ) );
		
		wc_add_notice( __( 'Payment method updated.', 'braintree-payments' ), 'success' );
	}

	/**
	 * dislay the pay for subscription page.
	 *
	 * @param int $id        	
	 */
	public static function pay_for_subscription_template( $id )
	{
		$subscription = bfwcs_get_subscription( $id );
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		
		bwc_get_template( 'myaccount/pay-for-subscription.php', array (
				'subscription' => $subscription, 
				'available_gateways' => $available_gateways 
		) );
	}

	/**
	 * Filter the payment gateways based on their support for bfwc_subscriptions.
	 *
	 * @param array $available_gateways        	
	 */
	public static function available_payment_gateways( $available_gateways )
	{
		if ( bfwcs_is_pay_for_subscription_request() ) {
			foreach ( $available_gateways as $id => $gateway ) {
				if ( ! $gateway->supports( 'bfwc_subscriptions' ) ) {
					unset( $available_gateways [ $id ] );
				}
			}
		}
		return $available_gateways;
	}

	public static function pay_for_subscription()
	{
		$nonce = isset( $_POST [ '_wpnonce' ] ) ? $_POST [ '_wpnonce' ] : '';
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'pay-for-subscription' ) ) {
			return;
		}
		global $wp;
		$subscription_id = absint( $_POST [ 'bfwc_subscription' ] );
		
		$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
		
		$gateway = $available_gateways [ wc_clean( $_POST [ 'payment_method' ] ) ];
		
		if ( ! $gateway->supports( 'bfwc_subscriptions' ) ) {
			wc_add_notice( sprintf( __( 'Payment gateway %s does not support this functionality.', 'braintree-payments' ), $gateway->get_title() ) );
			return;
		}
		
		// use the gateway to create the subscription.
		$result = $gateway->pay_for_braintree_subscription( $subscription_id );
		
		if ( ! is_wp_error( $result ) ) {
			wp_redirect( wc_get_page_permalink( 'myaccount' ) );
			exit();
		} else {
			if ( $result->get_error_message() ) {
				wc_add_notice( $result->get_error_message(), 'error' );
			}
		}
	}

	/**
	 * Check to see if a user is not logged in during order processing.
	 * If not logged in, set WC_Checkout()->must_create_account to true to ensure
	 * a customer Id is generated in Braintree before processing payment methods.
	 *
	 * @param array $posted        	
	 */
	public static function force_must_create_account( $posted )
	{
		if ( bfwcs_cart_contains_subscriptions() && ! is_user_logged_in() ) {
			WC()->checkout()->must_create_account = true;
		}
	}

	/**
	 *
	 * @param WC_Checkout $checkout        	
	 */
	public static function maybe_disable_guest_checkout( $checkout )
	{
		if ( bfwcs_cart_contains_subscriptions() ) {
			if ( bwc_is_wc_3_0_0_or_more() ) {
			
			} else {
				$checkout->enable_guest_checkout = false;
				$checkout->must_create_account = true;
			}
		}
	}

	/**
	 * If the cart contains a subscription then set registration required to true.
	 *
	 * @since 2.6.2
	 * @param bool $bool        	
	 */
	public static function checkout_registration_required( $bool )
	{
		if ( bfwcs_cart_contains_subscriptions() ) {
			$bool = true;
		}
		return $bool;
	}
}
Braintree_Gateway_Subscriptions::init();