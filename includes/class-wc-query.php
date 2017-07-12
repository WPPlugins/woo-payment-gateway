<?php
/**
 * Class that handles queries related to WC functionality.
 * @author Clayton
 *
 */
class Braintree_Gateway_WC_Query
{
	
	private $endpoints = array ();

	public function __construct()
	{
		add_action( 'init', array ( 
				$this, 
				'add_endpoints' 
		) );
		add_action( 'parse_request', array ( 
				$this, 
				'parse_request' 
		) );
		
		$this->initialize_endpoints();
	}

	private function initialize_endpoints()
	{
		$this->endpoints = array ( 
				'subscriptions' => 'subscriptions', 
				'view-subscription' => 'view-subscription', 
				'edit-subscription' => 'edit-subscription', 
				'change-payment-method' => 'change-payment-method', 
				'pay-subscription' => 'pay-subscription' 
		);
	}

	/**
	 * Add endpoints associated with plugin.
	 * These endpoints can be used to trigger page rendering functionality, etc.
	 */
	public function add_endpoints()
	{
		global $wp_rewrite;
		foreach ( $this->get_endpoints() as $key => $value ) {
			add_rewrite_endpoint( $value, EP_PAGES );
		}
	}

	public function parse_request()
	{
		global $wp;
	}

	public function get_endpoints()
	{
		return apply_filters( 'bfwc_get_endpoints', $this->endpoints );
	}
}