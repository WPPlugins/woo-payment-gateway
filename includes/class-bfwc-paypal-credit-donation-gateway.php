<?php
class BFWC_PayPal_Credit_Donation_Gateway extends BFWC_Donation_Gateway
{
	
	const ID = 'bfwc_paypal_credit_donation_gateway';
	
	static $nonce_id = 'bfwc_paypal_credit_donation_nonce';
	
	static $device_data_id = 'bfwc_paypal_credit_device_data';

	public function __construct()
	{
		$this->id = static::ID;
		$this->enabled = bfwcd_paypal_credit_enabled();
		$this->title = bt_manager()->get_option( 'paypal_credit_donation_title' );
		$this->supports = array (
				'donations' 
		);
	}

	public static function init()
	{
		add_action( 'bfwc_load_donation_gateways', __CLASS__ . '::load_gateway' );
	}

	public static function load_gateway( $gateways )
	{
		$gateways [] = __CLASS__;
		return $gateways;
	}

	public function payment_fields()
	{
		braintree_nonce_field( static::$nonce_id );
		braintree_device_data_field( static::$device_data_id );
		bfwc_get_template( 'donations/paypal-credit.php', array (
				'gateway' => $this 
		) );
	}

	public function get_icon()
	{
		printf( '<span class="paypal-credit-icon paypal"><img src="%s"/></span>', bt_manager()->plugin_assets_path() . 'img/paypal/paypal-credit.png' );
	}

	public static function add_device_data( &$attribs )
	{
		$attribs [ 'deviceData' ] = stripslashes( self::get_request_parameter( static::$device_data_id ) );
	}

}
BFWC_PayPal_Credit_Donation_Gateway::init();