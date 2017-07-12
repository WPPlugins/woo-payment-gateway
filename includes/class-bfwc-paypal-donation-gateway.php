<?php
class BFWC_PayPal_Donation_Gateway extends BFWC_Donation_Gateway
{
	
	const ID = 'bfwc_paypal_donation_gateway';
	
	static $nonce_id = 'bfwc_paypal_donation_nonce';
	
	static $device_data_id = 'bfwc_paypal_device_data';

	public function __construct()
	{
		$this->id = static::ID;
		$this->enabled = bfwcd_paypal_enabled();
		$this->title = bt_manager()->get_option( 'paypal_donation_gateway_title' );
		$this->supports = array (
				'donations', 
				'recurring_donations' 
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
		bfwc_get_template( 'donations/paypal.php', array (
				'gateway' => $this 
		) );
	}

	public function get_icon()
	{
		echo '<span class="paypal-icon paypal"></span>';
	}

	public static function add_device_data( &$attribs )
	{
		$attribs [ 'deviceData' ] = stripslashes( self::get_request_parameter( static::$device_data_id ) );
	}

}
BFWC_PayPal_Donation_Gateway::init();