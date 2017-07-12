<?php
/**
 * 
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class WC_Product_Braintree_Subscription_Variation extends WC_Product_Variation
{

	public function __construct( $variation, $args = array() )
	{
		if ( property_exists( $this, 'extra_data' ) ) {
			$this->extra_data [ 'subscription_price' ] = 0;
			$this->extra_data [ 'subscription_length' ] = 0;
			$this->extra_data [ 'subscription_period_interval' ] = 1;
			$this->extra_data [ 'subscription_period' ] = 'month';
			$this->extra_data [ 'subscription_sign_up_fee' ] = 0;
			$this->extra_data [ 'subscription_trial_length' ] = 0;
			$this->extra_data [ 'subscription_trial_period' ] = '';
			$this->extra_data [ 'subscription_one_time_shipping' ] = '';
			$this->extra_data [ 'braintree_sandbox_plans' ] = array ();
			$this->extra_data [ 'braintree_production_plans' ] = array ();
			$this->data [ 'parent' ] = null;
		}
		
		if ( bwc_is_wc_3_0_0_or_more() ) {
			parent::__construct( $variation );
		}
		
		$this->variation_id = is_object( $variation ) ? $variation->ID : $variation;
		
		$this->id = bwc_is_wc_3_0_0_or_more() ? ( $variation ) : ( ! empty( $args [ 'parent_id' ] ) ? intval( $args [ 'parent_id' ] ) : wp_get_post_parent_id( $this->variation_id ) );
		$this->parent = bwc_is_wc_3_0_0_or_more() ? ( wc_get_product( wp_get_post_parent_id( $this->get_id() ) ) ) : ( ! empty( $args [ 'parent' ] ) ? $args [ 'parent' ] : wc_get_product( $this->id ) );
		
		$this->product_type = 'braintree-subscription-variation';
		$this->post = $this->parent ? $this->parent->post : get_post();
	}

	public static function init()
	{
		add_filter( 'woocommerce_product_class', __CLASS__ . '::get_classname', 10, 4 );
		add_filter( 'woocommerce_data_stores', __CLASS__ . '::add_data_store' );
	}

	public function __get( $key )
	{
		if ( method_exists( $this, 'get_prop' ) ) {
			return $this->get_prop( $key );
		} else {
			return parent::__get( $key );
		}
	}

	public function __set( $key, $value )
	{
		if ( method_exists( $this, 'set_prop' ) ) {
			$this->set_prop( $key, $value );
		}
	}

	public function get_type()
	{
		return 'braintree-subscription-variation';
	}

	public function is_type( $type )
	{
		return $type === 'variation' || $this->get_type() === $type || ( is_array( $type ) && in_array( $this->get_type(), $type ) );
	}

	public function get_price_html( $price = '' )
	{
		return bfwcs_get_product_price_html( $this, $price );
	}

	public static function get_classname( $classname, $product_type, $post_type, $product_id )
	{
		if ( $post_type === 'product_variation' ) {
			$post = get_post( $product_id );
			$terms = get_the_terms( $post->post_parent, 'product_type' );
			
			$parent_product_type = ! empty( $terms ) && isset( current( $terms )->slug ) ? current( $terms )->slug : '';
			
			if ( $parent_product_type === 'braintree-variable-subscription' ) {
				$classname = __CLASS__;
			}
		}
		return $classname;
	
	}

	/**
	 *
	 * @since 2.6.2
	 * @param array $stores        	
	 */
	public static function add_data_store( $stores )
	{
		$stores [ 'product-braintree-subscription-variation' ] = 'WC_Product_Variation_Data_Store_CPT';
		return $stores;
	}

	public function set_subscription_period( $period )
	{
		$this->subscription_period = $period;
	}

	public function set_subscription_price( $price )
	{
		$this->subscription_price = $price;
	}

	public function set_subscription_length( $length )
	{
		$this->subscription_length = $length;
	}

	public function set_subscription_period_interval( $period_interval )
	{
		$this->subscription_period_interval = $period_interval;
	}

	public function set_subscription_sign_up_fee( $fee )
	{
		$this->subscription_sign_up_fee = $fee;
	}

	public function set_subscription_trial_length( $trial_length )
	{
		$this->subscription_trial_length = $trial_length;
	}

	public function set_subscription_trial_period( $trial_period )
	{
		$this->subscription_trial_period = $trial_period;
	}

	public function set_subscription_one_time_shipping( $one_time_shipping )
	{
		$this->subscription_one_time_shipping = $one_time_shipping;
	}

	public function set_braintree_sandbox_plans( $plans )
	{
		$this->braintree_sandbox_plans = $plans;
	}

	public function set_braintree_production_plans( $plans )
	{
		$this->braintree_production_plans = $plans;
	}

	public function get_subscription_price()
	{
		return $this->subscription_price;
	}

	public function get_subscription_length()
	{
		return $this->subscription_length;
	}

	public function get_subscription_period_interval()
	{
		return $this->subscription_period_interval;
	}

	public function get_subscription_period()
	{
		return $this->subscription_period ? $this->subscription_period : 'month';
	}

	public function get_subscription_sign_up_fee()
	{
		return $this->subscription_sign_up_fee;
	}

	public function get_subscription_trial_length()
	{
		return $this->subscription_trial_length;
	}

	public function get_subscription_trial_period()
	{
		return $this->subscription_trial_period;
	}

	public function get_subscription_one_time_shipping()
	{
		return $this->subscription_one_time_shipping;
	}

	public function get_braintree_sandbox_plans()
	{
		return $this->braintree_sandbox_plans;
	}

	public function get_braintree_production_plans()
	{
		return $this->braintree_production_plans;
	}

	public function get_signup_fee()
	{
		return $this->subscription_sign_up_fee ? $this->subscription_sign_up_fee : 0;
	}

	public function is_one_time_shipping()
	{
		return $this->parent->subscription_one_time_shipping === 'yes' && ! $this->subscription_trial_length;
	}
}
WC_Product_Braintree_Subscription_Variation::init();