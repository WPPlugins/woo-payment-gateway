<?php
class Braintree_Gateway_ThreeDS_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->tab = 'checkout-settings';
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->title = array (
				'title' => __( '3D Secure Settings', 'braintree-payments' ), 
				'description' => __( 'These options allow you to customize 3D Secure.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_default_settings', array (
				$this, 
				'get_default_settings' 
		) );
		add_action( "braintree_gateway_{$this->tab}_save_settings", array (
				$this, 
				'save' 
		) );
		add_filter( 'braintree_gateway_validate_3ds_conditions', array (
				$this, 
				'validate_conditions' 
		), 10, 2 );
	}

	public function settings()
	{
		return array (
				'3ds_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( '3D Secure Enabled', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'default' => '', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'You must have Braintree enable 3D Secure in your Production and/or Sandbox account in order for this setting to take affect.', 'braintree-payments' ) 
				), 
				'3ds_conditions' => array (
						'title' => __( 'Conditional Statements', 'braintree-payments' ), 
						'type' => 'text', 
						'default' => '', 
						'value' => '', 
						'link' => array (
								'url' => 'https://support.paymentplugins.com/hc/en-us/articles/115002805388', 
								'text' => __( 'conditional examples', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'Conditional statements allow you to control when 3D Secure is available on the checkout page.', 'braintree-payments' ) 
				), 
				'3ds_enabled_payment_token' => array (
						'title' => __( '3DS For Vaulted Cards', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'For additional security you can request 3DS when a customer is using s saved credit card.', 'braintree-payments' ) 
				), 
				'3ds_liability_not_shifted' => array (
						'title' => __( 'Liability Not Shifted', 'braintree-payments' ), 
						'type' => 'select', 
						'default' => 'no_action', 
						'options' => array (
								'no_action' => __( 'No Action (Braintree will handle)', 'braintree-payments' ), 
								'authorize_only' => __( 'Authorize Amount', 'braintree-payments' ), 
								'reject' => __( 'Reject Transaction', 'braintree-payments' ), 
								'accept' => __( 'Accept Transaction', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'When 3DS is enabled, you can configure how to respond to a liability not shifted scenario. Liability not shifted means that you (the merchant) are still liable for fraud etc.', 'braintree-payments' ) 
				), 
				'3ds_card_ineligible' => array (
						'title' => __( 'Card Ineligible for 3DS', 'braintree-payments' ), 
						'type' => 'select', 
						'default' => 'no_action', 
						'options' => array (
								'no_action' => __( 'No Action (Braintree will handle)', 'braintree-payments' ), 
								'authorize_only' => __( 'Authorize Amount', 'braintree-payments' ), 
								'reject' => __( 'Reject Transaction', 'braintree-payments' ), 
								'accept' => __( 'Accept Transaction', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'When a card is ineligible for 3DS these are the actions you can take.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function validate_conditions( $value, $key )
	{
		$value = htmlspecialchars_decode( $value );
		
		if ( ! empty( $value ) && bt_manager()->is_woocommerce_active() ) {
			
			// execute conditional statement and capture output.
			ob_start();
			bwc_execute_conditional_statement( $value );
			$error = ob_get_clean();
			if ( ! empty( $error ) ) {
				$value = false;
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Your 3D Secure conditional statement has errors. Error Message: %s', 'braintree-payments' ), error_get_last() [ 'message' ] ) );
			}
		}
		
		return $value;
	}
}