<?php
/**
 * Class that controls the display and saving of product subscription data.
 * @author Payment Plugins
 *
 */
class Braintree_Gateway_Admin_Subscription_Data
{

	public static function init()
	{
		add_action( 'admin_init', __CLASS__ . '::check_braintree_plans' );
		add_action( 'woocommerce_product_options_general_product_data', __CLASS__ . '::output_data', - 1000 );
		add_action( 'woocommerce_product_after_variable_attributes', __CLASS__ . '::output_variation_data', - 1000, 3 );
		add_action( 'save_post', __CLASS__ . '::save_subscription_data', 1000 );
		add_action( 'woocommerce_ajax_save_product_variations', __CLASS__ . '::save_variation_subscription_data' );
		add_action( 'add_meta_boxes', __CLASS__ . '::add_meta_boxes' );
	}

	public static function add_meta_boxes()
	{
		add_meta_box( 'bfwc-refresh-plans-box', __( 'Fetch Braintree Plans', 'woocommerce' ), __CLASS__ . '::output_plan_box', 'product', 'side', 'default' );
	}

	public static function output_data()
	{
		if ( ! bt_manager()->is_woocommerce_subscriptions_active() ) {
			return;
		}
		
		global $thepostid, $post;
		
		$sandbox_plans = get_option( 'braintree_wc_sandbox_plans', array () );
		include 'meta-box-html/product-data.php';
	}

	public static function output_variation_data( $loop, $variation_data, $variation )
	{
		
		if ( ! bt_manager()->is_woocommerce_subscriptions_active() ) {
			return;
		}
		
		global $thepostid, $post;
		
		$sandbox_plans = get_option( 'braintree_wc_sandbox_plans', array () );
		include 'meta-box-html/variation-product-data.php';
	}

	/**
	 * Save data specific to the product.
	 *
	 * @param unknown $post_id        	
	 */
	public static function save_subscription_data( $post_id )
	{
		if ( empty( $_POST [ '_wcsnonce' ] ) || ! wp_verify_nonce( $_POST [ '_wcsnonce' ], 'wcs_subscription_meta' ) || ! isset( $_POST [ 'product-type' ] ) ) {
			return;
		}
		$accepted_types = array (
				'subscription', 
				'variable-subscription' 
		);
		$product_type = empty( $_POST [ 'product-type' ] ) ? 'simple' : stripslashes( $_POST [ 'product-type' ] );
		
		$sandbox_plans = isset( $_POST [ '_braintree_sandbox_plans' ] ) ? $_POST [ '_braintree_sandbox_plans' ] : array ();
		
		// If not one of the subscription types, do not save as a subscription.
		if ( ! in_array( $product_type, $accepted_types ) ) {
			$is_subscription = 'no';
		} else {
			$is_subscription = ! empty( $_POST [ '_braintree_subscription' ] ) ? 'yes' : 'no';
			if ( $is_subscription === 'yes' ) {
				// Braintree only accepts monthly subscriptions.
				update_post_meta( $post_id, '_subscription_period', 'month' );
				
				// ensure the subscription_period_internval always matches the interval of the plans.
				$plan_id = ! empty( $sandbox_plans ) ? reset( $sandbox_plans ) : false;
				$sand_plans = get_option( 'braintree_wc_sandbox_plans', array () );
				if ( $sand_plans && $plan_id ) {
					$sand_plan = $sand_plans [ $plan_id ];
					if ( $sand_plan ) {
						update_post_meta( $post_id, '_subscription_period_interval', $sand_plan [ 'billingFrequency' ] );
					}
				}
			
			}
		}
		
		update_post_meta( $post_id, '_braintree_sandbox_plans', $sandbox_plans );
		update_post_meta( $post_id, '_braintree_subscription', $is_subscription );
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
		$variations = $_POST [ 'variable_post_id' ];
		
		foreach ( $variations as $i => $variation_id ) {
			
			$sandbox_plans = ! empty( $_POST [ 'variable_braintree_sandbox_plans' ] [ $i ] ) ? $_POST [ 'variable_braintree_sandbox_plans' ] [ $i ] : array ();
			
			$is_subscription = ! empty( $_POST [ 'variable_braintree_subscription' ] [ $i ] ) ? 'yes' : 'no';
			
			if ( $is_subscription === 'yes' ) {
				// Save subscription period as month.
				
				if ( ! empty( $_POST [ 'variable_subscription_period' ] [ $i ] ) ) {
					// $subscription_period = $_POST [ 'variable_subscription_period' ] [ $i ];
					update_post_meta( $variation_id, '_subscription_period', 'month' );
				}
				// ensure the subscription_period_interval always matches the interval of the plans.
				
				$plan_id = ! empty( $sandbox_plans ) ? reset( $sandbox_plans ) : false;
				$sand_plans = get_option( 'braintree_wc_sandbox_plans', array () );
				if ( $sand_plans && $plan_id ) {
					$sand_plan = $sand_plans [ $plan_id ];
					if ( $sand_plan ) {
						update_post_meta( $variation_id, '_subscription_period_interval', $sand_plan [ 'billingFrequency' ] );
					}
				}
			
			}
			
			update_post_meta( $variation_id, '_braintree_subscription', $is_subscription );
			
			update_post_meta( $variation_id, '_braintree_sandbox_plans', $sandbox_plans );
		}
	}

	public static function output_plan_box()
	{
		global $thepostid, $post;
		bfwc_admin_get_template( 'meta-box-html/refresh-plans.php' );
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
Braintree_Gateway_Admin_Subscription_Data::init();