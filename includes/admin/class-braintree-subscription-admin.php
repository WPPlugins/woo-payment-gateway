<?php
class Braintree_Gateway_Subscriptions_Admin
{

	public static function init()
	{
		add_action( 'admin_init', __CLASS__ . '::check_braintree_plans' );
		add_filter( 'product_type_selector', __CLASS__ . '::add_product_types' );
		add_action( 'woocommerce_product_options_general_product_data', __CLASS__ . '::output_data' );
		add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::output_variation_data', - 1000, 3 );
		add_action( 'woocommerce_product_options_shipping', __CLASS__ . '::shipping_fields' );
		add_action( 'save_post', __CLASS__ . '::save_subscription_meta' );
		add_action( 'woocommerce_ajax_save_product_variations', __CLASS__ . '::save_variation_subscription_data' );
		add_action( 'add_meta_boxes', __CLASS__ . '::add_meta_boxes' );
		add_filter( 'product_type_options', __CLASS__ . '::product_type_options' );
	}

	public static function add_meta_boxes()
	{
		add_meta_box( 'bfwc-refresh-plans-box', __( 'Fetch Braintree Plans', 'woocommerce' ), __CLASS__ . '::output_plan_box', 'product', 'side', 'default' );
	}

	public static function add_product_types( $product_types )
	{
		// Only allow plugin subscriptions if WC Subscriptions is not active.
		if ( ! bt_manager()->is_woocommerce_subscriptions_active() ) {
			$product_types [ 'braintree-subscription' ] = __( 'Braintree Subscription', 'braintree-payments' );
			$product_types [ 'braintree-variable-subscription' ] = __( 'Braintree Variable Subscription', 'braintree-payments' );
		}
		return $product_types;
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $product_options        	
	 */
	public static function product_type_options( $product_options )
	{
		if ( isset( $product_options [ 'virtual' ] [ 'wrapper_class' ] ) ) {
			$product_options [ 'virtual' ] [ 'wrapper_class' ] = sprintf( '%s %s', $product_options [ 'virtual' ] [ 'wrapper_class' ], 'show_if_braintree-subscription' );
		}
		if ( isset( $product_options [ 'downloadable' ] [ 'wrapper_class' ] ) ) {
			$product_options [ 'downloadable' ] [ 'wrapper_class' ] = sprintf( '%s %s', $product_options [ 'downloadable' ] [ 'wrapper_class' ], 'show_if_braintree-subscription' );
		}
		return $product_options;
	}

	public static function output_data()
	{
		global $thepostid, $post;
		
		$sandbox_plans = get_option( 'braintree_wc_sandbox_plans', array () );
		wp_nonce_field( 'bfwc-save-product-subscription', '_bfwc_admin_subscription' );
		include bt_manager()->plugin_admin_path() . 'meta-box-html/braintree-simple-subscription.php';
	}

	public static function output_plan_box()
	{
		global $thepostid, $post;
		bfwc_admin_get_template( 'meta-box-html/refresh-plans.php' );
	}

	public static function shipping_fields()
	{
		global $thepostid, $post;
		
		woocommerce_wp_checkbox( array (
				'wrapper_class' => 'show_if_subscription show_if_braintree-subscription show_if_braintree-variable-subscription', 
				'label' => __( 'One Time Shipping', 'braintree-payments' ), 
				'id' => '_subscription_one_time_shipping', 
				'cbvalue' => 'yes', 
				'desc_tip' => true, 
				'description' => __( 'Select if you only want shipping to be charged during checkout. Note: one time shipping does not apply to subscriptions with trial periods.', 'braintree-payments' ) 
		) );
	}

	public static function save_subscription_meta( $post_id )
	{
		$post = get_post( $post_id );
		if ( ! $post || ! in_array( $post->post_type, array (
				'product' 
		) ) || ! is_admin() ) {
			return;
		}
		
		if ( ! isset( $_POST [ 'product-type' ] ) || ! isset( $_POST [ '_bfwc_admin_subscription' ] ) || ! wp_verify_nonce( $_POST [ '_bfwc_admin_subscription' ], 'bfwc-save-product-subscription' ) ) {
			return;
		}
		
		$product_types = array (
				'braintree-subscription', 
				'braintree-variable-subscription' 
		);
		
		// don't save unless this is a braintree subscription product.
		if ( ! in_array( $_POST [ 'product-type' ], $product_types ) ) {
			return;
		}
		
		$price = isset( $_POST [ '_subscription_price' ] ) ? stripslashes( sanitize_text_field( $_POST [ '_subscription_price' ] ) ) : '';
		$price = wc_format_decimal( $price );
		
		update_post_meta( $post_id, '_regular_price', $price );
		update_post_meta( $post_id, '_subscription_price', $price );
		
		// Braintree only accepts monthly subscriptions.
		update_post_meta( $post_id, '_subscription_period', 'month' );
		
		$fields = array (
				'_subscription_period_interval', 
				'_subscription_length', 
				'_subscription_sign_up_fee', 
				'_subscription_trial_length', 
				'_subscription_trial_period', 
				'_subscription_one_time_shipping' 
		);
		
		foreach ( $fields as $field ) {
			$value = isset( $_POST [ $field ] ) ? stripslashes( sanitize_text_field( $_POST [ $field ] ) ) : '';
			
			switch( $field ) {
				case '_subscription_sign_up_fee' :
					if ( empty( $value ) ) {
						$value = 0;
					} else {
						$value = wc_format_decimal( $value );
					}
					break;
			}
			
			update_post_meta( $post_id, $field, $value );
		}
		
		$sandbox_plans = isset( $_POST [ '_braintree_sandbox_plans' ] ) ? $_POST [ '_braintree_sandbox_plans' ] : array ();
		
		update_post_meta( $post_id, '_braintree_sandbox_plans', $sandbox_plans );
		
		$plan_id = ! empty( $sandbox_plans ) ? reset( $sandbox_plans ) : false;
		$sand_plans = get_option( 'braintree_wc_sandbox_plans', array () );
		if ( $sand_plans && $plan_id ) {
			$sand_plan = $sand_plans [ $plan_id ];
			if ( $sand_plan ) {
				update_post_meta( $post_id, '_subscription_period_interval', $sand_plan [ 'billingFrequency' ] );
			}
		}
		
		// maybe update _price to _subscription_price;
		$sale_from = isset( $_POST [ '_sale_price_dates_from' ] ) ? strtotime( $_POST [ '_sale_price_dates_from' ] ) : false;
		$sale_to = isset( $_POST [ '_sale_price_dates_to' ] ) ? strtotime( $_POST [ '_sale_price_dates_to' ] ) : false;
		$now = strtotime( 'NOW', current_time( 'timestamp' ) );
		
		if ( ! $sale_from || ! $sale_to || ( $sale_to < $now ) ) {
			// no sale so update _price;
			update_post_meta( $post_id, '_price', get_post_meta( $post_id, '_subscription_price', true ) );
		}
	}

	/**
	 *
	 * @param int $product_id        	
	 */
	public static function save_variation_subscription_data( $product_id )
	{
		if ( ! isset( $_POST [ 'variable_post_id' ] ) ) {
			return;
		}
		if ( isset( $_POST [ 'product-type' ] ) && $_POST [ 'product-type' ] === 'braintree-variable-subscription' ) {
			$variations = $_POST [ 'variable_post_id' ];
			
			$sale_dates_to = isset( $_POST [ 'variable_sale_price_dates_to' ] ) ? $_POST [ 'variable_sale_price_dates_to' ] : array ();
			$sale_dates_from = isset( $_POST [ 'variable_sale_price_dates_from' ] ) ? $_POST [ 'variable_sale_price_dates_from' ] : array ();
			
			$sale_prices = isset( $_POST [ 'variable_sale_price' ] ) ? $_POST [ 'variable_sale_price' ] : array ();
			
			foreach ( $variations as $i => $variation_id ) {
				
				$sandbox_plans = ! empty( $_POST [ 'variable_braintree_sandbox_plans' ] [ $i ] ) ? $_POST [ 'variable_braintree_sandbox_plans' ] [ $i ] : array ();
				update_post_meta( $variation_id, '_braintree_sandbox_plans', $sandbox_plans );
				
				// Save subscription period as month.
				update_post_meta( $variation_id, '_subscription_period', 'month' );
				
				$fields = array (
						'_subscription_price', 
						'_subscription_period_interval', 
						'_subscription_length', 
						'_subscription_sign_up_fee', 
						'_subscription_trial_length', 
						'_subscription_trial_period', 
						'_subscription_one_time_shipping' 
				);
				
				foreach ( $fields as $field ) {
					$key = 'variable' . $field;
					$value = isset( $_POST [ $key ] [ $i ] ) ? wc_clean( $_POST [ $key ] [ $i ] ) : '';
					update_post_meta( $variation_id, $field, $value );
				}
				
				// update the regular price and re-sync.
				update_post_meta( $variation_id, '_regular_price', get_post_meta( $variation_id, '_subscription_price', true ) );
				WC_Product_Variable::sync( $product_id );
				
				WC_Product_Braintree_Variable_Subscription::sync_product( $product_id );
				
				$sale_to = isset( $sale_dates_to [ $i ] ) ? strtotime( $sale_dates_to [ $i ] ) : false;
				$sale_from = isset( $sale_dates_from [ $i ] ) ? strtotime( $sale_dates_from [ $i ] ) : false;
				$now = strtotime( 'NOW', current_time( 'timestamp' ) );
				
				if ( ! $sale_to || ! $sale_from || ( $sale_to < $now ) ) {
					// no sale so update price with subscription price;
					update_post_meta( $variation_id, '_price', get_post_meta( $variation_id, '_subscription_price', true ) );
				
				}
			}
		
		}
	}

	/**
	 *
	 * @param unknown $loop        	
	 * @param unknown $variation_data        	
	 * @param WP_Post $variation        	
	 */
	public static function output_variation_data( $loop, $variation_data, $variation )
	{
		
		$sandbox_plans = get_option( 'braintree_wc_sandbox_plans', array () );
		bfwc_admin_get_template( 'meta-box-html/braintree-variable-subscription.php', array (
				'sandbox_plans' => $sandbox_plans, 
				'loop' => $loop, 
				'variation_data' => $variation_data, 
				'variation' => $variation 
		) );
	}

	/**
	 * Update the database with the subscription plan data.
	 */
	public static function check_braintree_plans()
	{
		$load_plans = isset( $_REQUEST [ 'bfwc_refresh_plans' ] );
		
		$environments = array (
				'sandbox' 
		);
		
		// fetch plans for both sandbox and production.
		foreach ( $environments as $environment ) {
			
			$merchant_id = bt_manager()->get_option( "sandbox_merchant_id" );
			$private_key = bt_manager()->get_option( "sandbox_private_key" );
			$public_key = bt_manager()->get_option( "sandbox_public_key" );
			
			if ( $merchant_id && $private_key && $public_key ) {
				$plans = array ();
				try {
					Braintree_Configuration::environment( "sandbox" );
					Braintree_Configuration::merchantId( $merchant_id );
					Braintree_Configuration::privateKey( $private_key );
					Braintree_Configuration::publicKey( $public_key );
					
					$plans = Braintree_Plan::all();
					if ( $plans ) {
						$subscription_plans = array ();
						
						foreach ( $plans as $plan ) {
							$subscription_plans [ $plan->id ] = array (
									'id' => $plan->id, 
									'name' => $plan->name, 
									'numberOfBillingCycles' => $plan->numberOfBillingCycles, 
									'billingDayOfMonth' => $plan->billingDayOfMonth, 
									'billingFrequency' => $plan->billingFrequency, 
									'createdAt' => $plan->createdAt, 
									'currencyIsoCode' => $plan->currencyIsoCode, 
									'description' => $plan->description, 
									'price' => $plan->price, 
									'trialDuration' => $plan->price, 
									'trialDurationUnit' => $plan->trialDurationUnit, 
									'trialPeriod' => $plan->trialPeriod, 
									'updatedAt' => $plan->updatedAt 
							);
						}
						update_option( "braintree_wc_{$environment}_plans", $subscription_plans ); // update
							                                                                           // every
							                                                                           // 24hrs
					}
				} catch( \Braintree\Exception $e ) {
					bt_manager()->error( sprintf( __( 'Error loading braintree plans for subscriptions. Exception: %s', 'braintree-payments' ), get_class( $e ) ) );
				}
			}
		}
		bt_manager()->initialize_braintree(); // Refresh the configuration.
	}
}
Braintree_Gateway_Subscriptions_Admin::init();