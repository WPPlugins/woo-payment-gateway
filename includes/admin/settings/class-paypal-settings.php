<?php

use Braintree\PaymentInstrumentType;
class Braintree_Gateway_PayPal_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->tab = 'checkout-settings';
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->title = array (
				'title' => __( 'PayPal Settings', 'braintree-payments' ), 
				'description' => __( 'These options allow you to customize the PayPal & PayPal Credit Gateway.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_default_settings', array (
				$this, 
				'get_default_settings' 
		) );
		add_action( "braintree_gateway_{$this->tab}_save_settings", array (
				$this, 
				'save' 
		) );
		add_filter( 'braintree_gateway_validate_paypal_credit_conditions', array (
				$this, 
				'validate_conditions' 
		), 10, 2 );
		
		add_filter( 'bfwc_admin_option_paypal_credit_conditions', 'htmlspecialchars', 10, 1 );
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function output_paypal_buttons( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'views/paypal-buttons.php';
	}

	public function settings()
	{
		$settings = array (
				'enable_paypal' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => 'no', 
						'title' => __( 'PayPal Enabled', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If you have enabled custom forms and want to use PayPal as a payment option you must select this option. PayPal will be treated as a separate gateway. You must ensure that you have linked your PayPal and Braintree accounts.', 'braintree-payments' ) 
				), 
				'paypal_gateway_title' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => __( 'PayPal', 'braintree-payments' ), 
						'title' => __( 'PayPal Title', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value here determines the text that appears on the checkout page next to the PayPal gateway.', 'braintree-payments' ) 
				), 
				'paypal_checkout_flow' => array (
						'type' => 'select', 
						'value' => '', 
						'default' => 'checkout', 
						'title' => __( 'Checkout With PayPal', 'braintree-payments' ), 
						'options' => array (
								'checkout' => __( 'Checkout Flow', 'braintree-payments' ), 
								'vault' => __( 'Vault Flow', 'braintree-payments' ) 
						), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This options allows you to set the checkout flow for PayPal. Vault flow is a shorter version of the checkout flow and will require your customers to grant your merchant site authorization.
						The checkout flow is the standard checkout options for PayPal. If you are using subscriptions, the vault flow will be used regardless of this setting as subscriptions require a billing agreement which
						the vault flow grants.', 'braintree-payments' ) 
				), 
				'paypal_billing_agreement_desc' => array (
						'type' => 'textarea', 
						'title' => __( 'Billing Agreement Description', 'braintree-payments' ), 
						'default' => sprintf( __( 'Purchase agreement from %s.', 'braintree-payments' ), get_bloginfo( 'name' ) ), 
						'value' => '', 
						'description' => __( 'The billing agreement description appears on your customer\'s PayPal account and gives information about the company they have granted authorization to. This is a good way to prevent
								customers from cancelling recurring billing authorizations because they are unsure who they granted access to.', 'braintree-payments' ), 
						'tool_tip' => true 
				), 
				'paypal_display_name' => array (
						'type' => 'text', 
						'default' => get_option( 'blogname' ), 
						'title' => __( 'PayPal Display Name', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'This is the name that will appear on the PayPal checkout screen if the customer chooses to pay with PayPal.', 'braintree-payments' ) 
				), 
				'paypal_submit_form' => array (
						'type' => 'checkbox', 
						'title' => __( 'Submit Form Automatically', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the order will be placed once the PayPal payment method is selected by the customer. If not enabled, the customer will still need to click the Place Order button to process the order.', 'braintree-payments' ) 
				), 
				'paypal_format' => array (
						'title' => __( 'PayPal Method Display', 'braintree-payments' ), 
						'type' => 'select', 
						'options' => $this->get_paypal_options(), 
						'value' => '', 
						'default' => 'paypal_and_email', 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to customize how the PayPal method will display for your customers on orders, payment methods page, etc.' ) 
				), 
				'paypal_button_design' => array (
						'type' => 'select', 
						'title' => __( 'PayPal Button Design', 'braintree-payments' ), 
						'default' => 0, 
						'options' => $this->get_paypal_button_options(), 
						'class' => '', 
						'value' => '', 
						'default' => 2, 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to select the PayPal button design that you want to be displayed on the checkout page. If you want to
						create your own PayPal button, follow the tutorials.', 'braintree-payments' ) 
				), 
				'paypal_send_shipping' => array (
						'title' => __( 'Send Shipping Address', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the customer\'s shipping address information from the checkout page will be sent to PayPal during tokenization. The customer will not have to re-enter their shipping information on the PayPal popup screen. Keep in mind that PayPal performs validations on the address.', 'braintree-payments' ) 
				), 
				'paypal_credit' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => '', 
						'title' => __( 'PayPal Credit', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'PayPal credit allows your customers to pay for their order over time. You receive all the funds up front and the customer makes payments on their end.', 'braintree-payments' ) 
				), 
				'paypal_credit_conditions' => array (
						'title' => __( 'PayPal Credit Conditions', 'braintree-payments' ), 
						'type' => 'text', 
						'value' => '', 
						'default' => '', 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('paypal_credit')}", 
								'data-show-if' => 'checked' 
						), 
						'link' => array (
								'url' => 'https://support.paymentplugins.com/hc/en-us/articles/115002805388', 
								'text' => __( 'condition examples', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'Enter a condition that must be met before PayPal Credit is offered as an option. E.g. {amount} > 400 && {amount} < 1000 if the cart amount must be greater than 400 but less than 1,000.', 'braintree-payments' ) 
				), 
				'paypal_credit_button' => array (
						'type' => 'select', 
						'title' => __( 'PayPal Credit Button', 'braintree-payments' ), 
						'default' => 0, 
						'options' => $this->get_paypal_credit_button_options(), 
						'class' => '', 
						'value' => '', 
						'default' => 1, 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('paypal_credit')}", 
								'data-show-if' => 'checked' 
						), 
						'tool_tip' => true, 
						'description' => __( 'When PayPal credit is offered as a payment option, this is the button that will appear on the checkout page.', 'braintree-payments' ) 
				), 
				'paypal_credit_title' => array (
						'type' => 'text', 
						'title' => __( 'PayPal Credit Title', 'braintree-payments' ), 
						'default' => __( 'PayPal Credit', 'braintree-payments' ), 
						'value' => '', 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('paypal_credit')}", 
								'data-show-if' => 'checked' 
						), 
						'tool_tip' => true, 
						'description' => __( 'The title of the PayPal payment option when PayPal credit is available.', 'braintree-payments' ) 
				), 
				'paypal_credit_send_shipping' => array (
						'title' => __( 'Send Shipping Address', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => 'no', 
						'value' => 'yes', 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('paypal_credit')}", 
								'data-show-if' => 'checked' 
						), 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the customer\'s shipping address information from the checkout page will be sent to PayPal during tokenization. The customer will not have to re-enter their shipping information on the PayPal popup screen. Keep in mind that PayPal performs validations on the address.', 'braintree-payments' ) 
				) 
		);
		return $settings;
	}

	public function get_paypal_options()
	{
		$patterns = braintree_get_payment_method_formats() [ PaymentInstrumentType::PAYPAL_ACCOUNT ];
		$formats = array ();
		foreach ( $patterns as $k => $v ) {
			$formats [ $k ] = $v [ 'example' ];
		}
		return $formats;
	}

	/**
	 * Return an array of PayPal button options.
	 *
	 * @return array
	 */
	public function get_paypal_button_options()
	{
		$buttons = braintree_get_paypal_buttons();
		$options = array ();
		foreach ( $buttons as $index => $button ) {
			$options [ $index ] = $button [ 'name' ];
		}
		return $options;
	}

	public function get_paypal_credit_button_options()
	{
		$buttons = braintree_get_paypal_credit_buttons();
		$options = array ();
		foreach ( $buttons as $index => $button ) {
			$options [ $index ] = $button [ 'name' ];
		}
		return $options;
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
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Your PayPal Credit conditional statement has errors. Error Message: %s', 'braintree-payments' ), error_get_last() [ 'message' ] ) );
			}
		}
		
		return $value;
	}
}