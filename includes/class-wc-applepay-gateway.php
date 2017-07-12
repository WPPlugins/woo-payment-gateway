<?php
if ( ! class_exists( 'WC_Braintree_Payment_Gateway' ) ) {
	return;
}
/**
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *           
 */
class WC_Applepay_Payment_Gateway extends WC_Braintree_Payment_Gateway
{
	const ID = 'braintree_applepay_payments';
	
	/**
	 * Nonce id contained in the $_POST.
	 *
	 * @var string
	 */
	public static $nonce_id = 'braintree_applepay_payments_nonce';
	
	/**
	 * Device data field key located in $_POST.
	 *
	 * @var string
	 */
	public static $device_data_id = 'braintree_applepay_device_data';
	
	/**
	 * The key used in $_POST to identify the selected payment method token.
	 *
	 * @var string
	 */
	public static $token_id = 'braintree_applepay_payments_method_token';
	
	/**
	 * Name of the save payment method checkbox.
	 *
	 * @var string
	 */
	public static $save_method_name = 'bfwc_save_applepay_card';

	public function __construct()
	{
		$this->enabled = $this->settings [ 'enabled' ] = bt_manager()->is_active( 'enable_applepay' ) ? 'yes' : 'no';
		$this->title = bt_manager()->get_option( 'applepay_gateway_title' );
		$this->id = static::ID;
		$this->method_title = __( 'ApplePay Braintree Gateway', 'braintree-payments' );
		$this->has_fields = true;
		$this->actions();
		$this->set_supports();
		$this->init_settings();
	}

	public static function init()
	{
		add_filter( 'bwc_add_payment_gateways', __CLASS__ . '::add_braintree_gateway' );
		
		add_filter( 'woocommerce_payment_gateways', __CLASS__ . '::add_gateway' );
		
		add_action( 'wp_loaded', __CLASS__ . '::maybe_delete_payment_method' );
		
		add_filter( 'woocommerce_saved_payment_methods_list', __CLASS__ . '::saved_payment_method_list', 10, 2 );
		
		add_filter( 'woocommerce_payment_complete_order_status', __CLASS__ . '::maybe_update_order_status', 99, 2 );
		
		add_action( 'woocommerce_subscription_failing_payment_method_updated_' . static::ID, __CLASS__ . '::update_failing_payment_method', 10, 2 );
		
		add_action( 'bfwc_before_process_order_' . static::ID, __CLASS__ . '::maybe_save_payment_method' );
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
		if ( bwc_is_checkout() || bwcs_is_change_payment_method() || bfwcs_is_change_payment_method() || bfwcs_is_pay_for_subscription_request() ) {
			bwc_get_template( 'checkout/applepay-checkout.php', array (
					'gateway' => $this 
			) );
		} else {
			bwc_get_template( 'applepay.php', array (
					'has_methods' => false, 
					'gateway' => $this 
			) );
		}
	}

	public function woocommerce_gateway_icon( $icon, $id )
	{
		if ( $id === $this->id ) {
			$icon = '<span class="braintree-applepay mark"></span>';
		}
		return $icon;
	}

	public static function saved_payment_method_list( $saved_methods, $user_id )
	{
		if ( bwc_is_applepay_enabled() ) {
			$methods = bwc_get_user_applepay_payment_methods( $user_id );
			$saved_methods = bwc_saved_payment_methods_list( $saved_methods, $methods, static::ID );
		}
		return $saved_methods;
	}

	public function set_supports()
	{
		parent::set_supports();
		if ( ( $key = array_search( 'bfwc_credit_card_form', $this->supports ) ) !== false ) {
			unset( $this->supports [ $key ] );
		}
	}
}
WC_Applepay_Payment_Gateway::init();