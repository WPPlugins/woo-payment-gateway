<?php
class Braintree_Gateway_Admin_Subscription_Metabox
{

	public static function init()
	{
		add_action( 'current_screen', __CLASS__ . '::maybe_update_subscription_statuses' );
		add_action( 'add_meta_boxes', __CLASS__ . '::add_meta_boxes' );
		
		// use when saving existing subscription data.
		add_action( 'woocommerce_process_shop_order_meta', __CLASS__ . '::save_subscription_data', 999, 2 );
		
		add_filter( 'woocommerce_subscription_payment_meta', __CLASS__ . '::add_payment_method_meta', 10, 2 );
		
		add_action( 'woocommerce_process_shop_subscription_meta', __CLASS__ . '::process_shop_subscription_meta', 10, 2 );
	}

	public static function maybe_update_subscription_statuses( $screen )
	{
		$screen_id = $screen ? $screen->id : '';
		
		if ( $screen_id === 'bfwc_subscription' ) {
			add_filter( 'wc_order_statuses', function ( $order_statuses )
			{
				$subscription_statuses = bfwc_get_subscription_statuses();
				$subscription_statuses [ 'wc-pending' ] = $order_statuses [ 'wc-pending' ];
				return wp_parse_args( $order_statuses, $subscription_statuses );
			} );
		}
	}

	public static function add_meta_boxes()
	{
		add_meta_box( 'bfwc-subscriptions-metabox', __( 'Related Orders', 'braintree-payments' ), __CLASS__ . '::output_related_orders', 'bfwc_subscription', 'normal', 'low' );
		add_meta_box( 'bfwc-subscription-options-metabox', __( 'Subscription Options', 'braintree-payments' ), __CLASS__ . '::output_subscription_options', 'bfwc_subscription', 'side', 'default' );
	}

	/**
	 *
	 * @param WP_Post $post        	
	 */
	public static function output_related_orders( $post )
	{
		$subscription = bfwcs_get_subscription( $post->ID );
		$orders = bfwcs_get_related_orders( $subscription );
		if ( bwc_get_order_property( 'order', $subscription ) ) {
			$orders [] = bwc_get_order_property( 'order', $subscription );
		}
		if ( $orders ) {
			bfwc_admin_get_template( 'meta-box-html/related-orders.php', array (
					'orders' => $orders, 
					'subscription' => $subscription 
			) );
		}
	}

	/**
	 *
	 * @param WP_Post $post        	
	 */
	public static function output_subscription_options( $post )
	{
		$plans = bfwc_admin_get_subscription_plans();
		$subscription = bfwcs_get_subscription( $post->ID );
		bfwc_admin_get_template( 'meta-box-html/subscription-metabox-options.php', array (
				'subscription_plans' => $plans, 
				'subscription' => $subscription 
		) );
	}

	/**
	 * Save the subscription meta data.
	 *
	 * @param int $post_id        	
	 * @param WP_Post $post        	
	 */
	public static function save_subscription_data( $post_id, $post )
	{
		if ( ! isset( $_POST [ '_bfwc_subscription' ] ) || ! wp_verify_nonce( $_POST [ '_bfwc_subscription' ], 'bfwc-subscription' ) ) {
			return;
		}
		global $wpdb;
		
		$subscription = bfwcs_get_subscription( $post_id );
		
		// only update the subscription dates if the subscription hasn't been created in Braintree.
		if ( ! $subscription->is_created() ) {
			
			$trial_period = isset( $_POST [ '_subscription_trial_period' ] ) ? $_POST [ '_subscription_trial_period' ] : '';
			$trial_length = isset( $_POST [ '_subscription_trial_length' ] ) ? $_POST [ '_subscription_trial_length' ] : 0;
			
			$subscription_length = isset( $_POST [ '_subscription_length' ] ) ? $_POST [ '_subscription_length' ] : 0;
			
			$braintree_plan = wc_clean( $_POST [ '_subscription_plan' ] );
			$plans = bfwc_admin_get_subscription_plans();
			$plan = $plans [ $braintree_plan ];
			
			$subscription->update_meta( 'subscription_length', $subscription_length );
			$subscription->update_meta( 'subscription_trial_period', $trial_period );
			$subscription->update_meta( 'subscription_trial_length', $trial_length );
			$subscription->update_meta( 'subscription_period', 'month' );
			
			$subscription->update_meta( 'braintree_plan', $braintree_plan );
			$subscription->update_meta( 'merchant_account_id', bwc_get_merchant_account( $plan [ 'currencyIsoCode' ] ) );
			$subscription->update_meta( 'subscription_time_zone', bfwc_get_gateway_timezone() );
			$subscription->update_meta( 'subscription_period_interval', $plan [ 'billingFrequency' ] );
			$subscription->update_meta( 'order_currency', $plan [ 'currencyIsoCode' ] );
			
			if ( ! $subscription->has_status( 'pending', 'cancelled' ) ) {
				// update the status of the subscription to pending payment.
				$wpdb->update( $wpdb->posts, array (
						'post_status' => 'wc-pending' 
				), array (
						'ID' => $post_id 
				) );
			}
			
			$subscription->sync_dates();
		}
	}

	public static function localize_data()
	{
		// create localized text for frequency. every month, every 2 months etc
		
		return apply_filters( 'bfwc_subscription_localized_data', array (
				'plans' => bfwc_admin_get_subscription_plans(), 
				'plan_placeholder' => __( 'Select a Braintree Plan', 'braintree-payments' ), 
				'billing_frequency_text' => array (
						'intervals' => bfwcs_billing_interval_string() 
				), 
				'trial_text' => array (
						'singular' => bfwc_billing_periods_string( 'singular' ), 
						'plural' => bfwc_billing_periods_string( 'plural' ) 
				) 
		) );
	}

	/**
	 *
	 * @since 2.6.4
	 * @param array $meta        	
	 * @param WC_Subscription $subscription        	
	 */
	public static function add_payment_method_meta( $payment_method_table, $subscription )
	{
		// hacky way to prevent these parameters from being added during a subscription save.
		if ( did_action( 'woocommerce_admin_order_data_after_order_details' ) ) {
			foreach ( bwc_get_payment_gateways() as $id ) {
				$payment_method_table [ $id ] = array (
						'post_meta' => array (
								"_payment_method_token_$id" => array (
										'label' => __( 'Payment Token', 'braintree-payments' ), 
										'value' => bwc_get_order_property( 'payment_method', $subscription ) === $id ? bwc_get_order_property( 'payment_method_token', $subscription ) : '' 
								) 
						) 
				);
			}
		}
		return $payment_method_table;
	}

	public static function process_shop_subscription_meta( $post_id, $post )
	{
		$payment_method = isset( $_POST [ '_payment_method' ] ) ? wc_clean( $_POST [ '_payment_method' ] ) : '';
		if ( in_array( $payment_method, bwc_get_payment_gateways() ) ) {
			$token = isset( $_POST [ '_payment_method_meta' ] [ 'post_meta' ] [ "_payment_method_token_$payment_method" ] ) ? $_POST [ '_payment_method_meta' ] [ 'post_meta' ] [ "_payment_method_token_$payment_method" ] : '';
			update_post_meta( $post_id, '_payment_method_token', $token );
			update_post_meta( $post_id, '_payment_method_title', braintree_get_payment_title_from_token( get_post_meta( $post_id, '_customer_user', true ), $token ) );
		}
	}
}
Braintree_Gateway_Admin_Subscription_Metabox::init();