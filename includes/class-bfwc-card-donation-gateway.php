<?php
class BFWC_Card_Donation_Gateway extends BFWC_Donation_Gateway
{
	
	const ID = 'bfwc_card_donation_gateway';
	
	static $nonce_id = 'bfwc_card_donation_nonce';
	
	static $device_data_id = 'bfwc_device_data';

	public function __construct()
	{
		$this->id = static::ID;
		$this->title = bt_manager()->get_option( 'donation_gateway_title' );
		$this->enabled = bt_manager()->is_active( 'card_donation_enabled' );
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
		if ( bfwcd_dropin_enabled() ) {
			bfwc_get_template( 'donations/dropin.php', array (
					'gateway' => $this 
			) );
		} else {
			bfwc_get_template( 'donations/custom-form.php', array (
					'gateway' => $this 
			) );
		}
	}

	public function get_icon()
	{
		bfwc_get_template( 'donations/payment-method-icons.php' );
	}

}
BFWC_Card_Donation_Gateway::init();