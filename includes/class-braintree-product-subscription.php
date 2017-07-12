<?php
/**
 * Simple subscription product that is used to represent a Braintree Subscription.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 *@property string $subscription_price price of the subscription.
 *@property string $subscription_period billing period.
 *@property string $subscription_period_interval billing internval. ex monthly, every 2nd month.
 *@property string $subscription_length length of the subscription.
 *@property string $subscription_trial_length length of the trial period.
 *@property string $subscription_trial_period trial period. ex day, month.
 *@property string $subscription_sign_up_fee signup fee associated with the subscription.
 */
class WC_Product_Braintree_Subscription extends WC_Product_Simple
{

	public function __construct( $product )
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
		}
		
		parent::__construct( $product );
		
		$this->subscription_period = 'month';
		$this->product_type = 'braintree-subscription';
	}

	/**
	 *
	 * @since 2.6.2
	 *        Magic method for fetching properties.
	 *        This method was overridden in version 2.6.2 because WC 3.0.0+ issues warnings
	 *        when calling properties directly.
	 *       
	 * {@inheritDoc}
	 *
	 * @see WC_Product::__get()
	 */
	public function __get( $key )
	{
		if ( bwc_is_wc_3_0_0_or_more() ) {
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

	public function get_price_html( $price = '' )
	{
		return bfwcs_get_product_price_html( $this, $price );
	}

	public function get_plans()
	{
		$var = 'braintree_' . bt_manager()->get_environment() . '_plans';
		return $this->$var;
	}

	/**
	 * Return the subscription signup fee.
	 *
	 * @return string $fee;
	 */
	public function get_signup_fee()
	{
		return $this->subscription_sign_up_fee ? $this->subscription_sign_up_fee : 0;
	}

	/**
	 * Return true if shipping is only charged once for the product.
	 * If there is a trial period then shipping must be included
	 * in all charges if there is any shipping.
	 */
	public function is_one_time_shipping()
	{
		return $this->subscription_one_time_shipping === 'yes' && ! $this->subscription_trial_length;
	}

	/**
	 *
	 * @since 2.6.2
	 * {@inheritDoc}
	 *
	 * @see WC_Product::get_type()
	 */
	public function get_type()
	{
		return 'braintree-subscription';
	}

	public function get_subscription_length()
	{
		return $this->subscription_length;
	}

	public function get_subscription_price()
	{
		return $this->subscription_price;
	}

	public function get_subscription_period_interval()
	{
		return $this->subscription_period_interval;
	}

	public function get_subscription_period()
	{
		return $this->subscription_period;
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

	public function set_subscription_price( $price )
	{
		$this->subscription_price = $price;
	}

	public function set_subscription_length( $length )
	{
		$this->subscription_length = $length;
	}

	public function set_subscription_period_interval( $interval )
	{
		$this->subscription_period_interval = $interval;
	}

	public function set_subscription_period( $period )
	{
		$this->subscription_period = $period;
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
}