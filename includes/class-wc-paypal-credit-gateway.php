<?php
/**
 * 
 * @author Payment Plugins
 *
 */
class WC_PayPal_Credit_Payment_Gateway extends WC_Braintree_Payment_Gateway
{
	
	const ID = 'braintree_paypal_credit_payments';
	
	/**
	 * Nonce id contained in the $_POST.
	 *
	 * @var string
	 */
	public static $nonce_id = 'braintree_paypal_credit_payments_nonce';
	
	/**
	 * Device data field key located in $_POST.
	 *
	 * @var string
	 */
	public static $device_data_id = 'braintree_paypal_credit_device_data';

	public function __construct()
	{
		$this->enabled = $this->settings [ 'enabled' ] = is_admin() ? ( bwc_paypal_credit_enabled() ? 'yes' : 'no' ) : ( bwc_paypal_credit_active() ? 'yes' : 'no' );
		$this->id = static::ID;
		$this->title = bt_manager()->get_option( 'paypal_credit_title' );
		$this->method_title = __( 'PayPal Credit Payment Gateway', 'braintree-payments' );
		$this->has_fields = true;
		$this->actions();
		$this->set_supports();
		$this->init_settings();
	}

	public static function init()
	{
		add_filter( 'bwc_add_payment_gateways', __CLASS__ . '::add_braintree_gateway' );
		
		add_filter( 'woocommerce_payment_gateways', __CLASS__ . '::add_gateway' );
		
		add_filter( 'woocommerce_payment_complete_order_status', __CLASS__ . '::maybe_update_order_status', 99, 2 );
	}

	public static function add_braintree_gateway( $gateways )
	{
		$gateways [] = __CLASS__;
		return $gateways;
	}

	public static function add_gateway( $gateways )
	{
		$gateways [] = __CLASS__;
		return $gateways;
	}

	public function payment_fields()
	{
		bwc_get_template( 'checkout/paypal-credit-checkout.php', array (
				'gateway' => $this 
		) );
	}

	public function woocommerce_gateway_icon( $icon, $id )
	{
		if ( $id === $this->id ) {
			$icon = sprintf( '<span class="paypal-credit-icon paypal"><img src="%s"/></span>', bt_manager()->plugin_assets_path() . 'img/paypal/paypal-credit.png' );
		}
		return $icon;
	}

	/**
	 * Add a payment method nonce order attributes array.
	 *
	 * @param array $attribs        	
	 */
	public static function add_order_payment_method( &$attribs )
	{
		$attribs [ 'paymentMethodNonce' ] = self::get_request_param( static::$nonce_id );
		
		return $attribs;
	}

	/**
	 * Always add device data for PayPal Credit.
	 * With PayPal, deviceData is expected regardless of the merchant's control panel settings.
	 *
	 * @param array $attribs        	
	 */
	public static function add_device_data( &$attribs )
	{
		$attribs [ 'deviceData' ] = stripslashes( self::get_request_param( static::$device_data_id ) );
		
		return $attribs;
	}

	public function set_supports()
	{
		$this->supports = array (
				'products', 
				'default_credit_card_form', 
				'refunds', 
				'pre-orders', 
				'bfwc_fees' 
		);
	}
}
WC_PayPal_Credit_Payment_Gateway::init();