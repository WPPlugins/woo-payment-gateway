<?php

class Braintree_Gateway_Donation_Custom_Form_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'donation_settings';
		$this->tab = 'donation-settings';
		$this->title = array (
				'title' => __( 'Custom Form Settings', 'braintree-payments' ), 
				'description' => __( 'On this page you can configure your custom form settings. You can customize the text, placeholders, and processing functionality.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_default_settings', array (
				$this, 
				'get_default_settings' 
		) );
		add_action( "braintree_gateway_{$this->tab}_save_settings", array (
				$this, 
				'save' 
		) );
		$this->add_validate_filters();
	}

	public function add_validate_filters()
	{
		$validate = array (
				'donation_custom_form_styles' => 'validate_json' 
		);
		foreach ( $validate as $k => $method ) {
			add_filter( "braintree_gateway_validate_{$k}", array (
					$this, 
					$method 
			), 10, 2 );
		}
	}

	public function settings()
	{
		return array (
				'donation_custom_form_design' => array (
						'type' => 'select', 
						'title' => __( 'Custom Form', 'braintree-payments' ), 
						'default' => 'bootstrap_form', 
						'options' => $this->get_donation_forms(), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to select the payment form which will be displayed on the checkout page.', 'braintree-payments' ) 
				),
				'donation_postal_field_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Display Postal Field', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the custom form that you select will display a field for the customer to enter the postal code associated with their payment method.', 'braintree-payments' ) 
				), 
				'donation_cvv_field_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Display CVV Field', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the custom form that you select will display a field for the customer to enter their card security code (CVV).', 'braintree-payments' ) 
				), 
				'donation_card_number_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Number Placeholder', 'braintree-payments' ), 
						'default' => __( 'Card Number', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card number field.', 'braintree-payments' ) 
				), 
				'donation_card_cvv_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'CVV Placeholder', 'braintree-payments' ), 
						'default' => __( 'CVV', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card security code field.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_date_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Date Placeholder', 'braintree-payments' ), 
						'default' => __( 'MM/YY', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'For forms that contain a combined expiration date and year, this will be the placeholder.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_month_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Month Placeholder', 'braintree-payments' ), 
						'default' => __( 'MM', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the expiration month.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_year_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Year Placeholder', 'braintree-payments' ), 
						'default' => __( 'YY', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the expiration year.', 'braintree-payments' ) 
				), 
				'donation_card_postal_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Card Postal Placeholder', 'braintree-payments' ), 
						'default' => __( 'Postal Code', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card postal code field.', 'braintree-payments' ) 
				), 
				'donation_card_number_label' => array (
						'type' => 'text', 
						'title' => __( 'Card Number Label', 'braintree-payments' ), 
						'default' => __( 'Card Number', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card number input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_cvv_label' => array (
						'type' => 'text', 
						'title' => __( 'CVV Label', 'braintree-payments' ), 
						'default' => __( 'CVV', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card cvv input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_postal_label' => array (
						'type' => 'text', 
						'title' => __( 'Postal Label', 'braintree-payments' ), 
						'default' => __( 'Postal Code', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card postal input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_date_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Date Label', 'braintree-payments' ), 
						'default' => __( 'Exp Date', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration date input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_month_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Month Label', 'braintree-payments' ), 
						'default' => __( 'Month', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration month input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_expiration_year_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Year Label', 'braintree-payments' ), 
						'default' => __( 'Year', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration year input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'donation_card_save_label' => array (
						'type' => 'text', 
						'title' => __( 'Save Card Label', 'braintree-payments' ), 
						'default' => __( 'Save', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card save field for forms that contain a label.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function get_donation_forms()
	{
		$options = array ();
		foreach ( braintree_get_custom_donation_forms() as $k => $v ) {
			$options [ $k ] = $v [ 'description' ];
		}
		return $options;
	}
}