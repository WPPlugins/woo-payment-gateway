<?php
use Braintree\PaymentInstrumentType;
class Braintree_Gateway_ApplePay_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->tab = 'checkout-settings';
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->title = array ( 
				'title' => __( 'Apple Pay Settings', 'braintree-payments' ), 
				'description' => __( 'These options allow you to customize the PayPal Gateway.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_default_settings', array ( 
				$this, 
				'get_default_settings' 
		) );
		add_action( "braintree_gateway_{$this->tab}_save_settings", array ( 
				$this, 
				'save' 
		) );
	}

	public function settings()
	{
		return array ( 
				'enable_applepay' => array ( 
						'type' => 'checkbox', 
						'title' => __( 'Enable Apple Pay', 'braintree-payments' ), 
						'default' => '', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, Apple Pay will be available as a payment optionsfor IOS devices and supported browesers such as Safari.', 'braintree-payments' ) 
				), 
				'applepay_gateway_title' => array ( 
						'type' => 'text', 
						'title' => __( 'Apple Pay Title', 'braintree-payments' ), 
						'default' => __( 'Apple Pay', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'This is the text that will appear on the checkout page for this gateway.', 'braintree-payments' ) 
				), 
				'applepay_store_name' => array ( 
						'type' => 'text', 
						'title' => __( 'Store Name', 'braintree-payments' ), 
						'default' => get_bloginfo( 'name' ), 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'The name of the store that will appear on the Apple Pay sheet.', 'braintree-payments' ) 
				), 
				'applepay_format' => array ( 
						'title' => __( 'ApplePay Method Display', 'braintree-payments' ), 
						'type' => 'select', 
						'options' => $this->get_applepay_options(), 
						'value' => '', 
						'default' => 'apple_type_last4', 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to customize how the Apple Pay method will displayed for your customers on orders, payment methods page, etc.' ) 
				), 
				'applepay_button' => array ( 
						'type' => 'select', 
						'title' => __( 'Apple Pay Button', 'braintree-payments' ), 
						'default' => 'black_logo', 
						'options' => $this->get_applepay_buttons(), 
						'tool_tip' => true, 
						'description' => __( 'This is the button that will appear on the checkout page for Apple Pay.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function get_applepay_options()
	{
		$patterns = braintree_get_payment_method_formats()[ PaymentInstrumentType::APPLE_PAY_CARD ];
		$formats = array ();
		foreach ( $patterns as $k => $v ) {
			$formats[ $k ] = $v[ 'example' ];
		}
		return $formats;
	}

	public function get_applepay_buttons()
	{
		$buttons = bwc_get_applepay_buttons();
		$apple_buttons = array ();
		foreach ( $buttons as $k => $button ) {
			$apple_buttons[ $k ] = $button[ 'label' ];
		}
		return $apple_buttons;
	}
}