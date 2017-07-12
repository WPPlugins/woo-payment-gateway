<?php

class Braintree_Gateway_Custom_Form_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->tab = 'checkout-settings';
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->title = array (
				'title' => __( 'Custom Form Settings', 'braintree-payments' ), 
				'description' => __( 'Custom forms allow you to have more control over the look and feel of your payment form.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_default_settings', array (
				$this, 
				'get_default_settings' 
		) );
		add_action( "braintree_gateway_{$this->tab}_save_settings", array (
				$this, 
				'save' 
		) );
		add_filter( 'braintree_settings_localized_variables', array (
				$this, 
				'localize_vars' 
		) );
		$this->add_validate_filters();
	}

	public function localize_vars( $vars )
	{
		$vars [ 'custom_forms' ] = array ();
		$forms = bwc_get_custom_forms();
		foreach ( $this->settings() [ 'custom_form_design' ] [ 'options' ] as $form => $value ) {
			$vars [ 'custom_forms' ] [ $form ] = array (
					'styles' => $forms [ $form ] [ 'default_styles' ] 
			);
		}
		return $vars;
	}

	public function add_validate_filters()
	{
		$validate = array (
				'custom_form_styles' => 'validate_json', 
				'custom_form_loader_css' => 'validate_json' 
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
				'custom_form_design' => array (
						'type' => 'select', 
						'title' => __( 'Custom Form', 'braintree-payments' ), 
						'default' => 'bootstrap_form', 
						'options' => $this->get_form_options(), 
						'value' => '', 
						'class' => '', 
						'link' => array (
								'url' => 'https://support.paymentplugins.com/hc/en-us/articles/115001527088-Custom-Forms', 
								'text' => __( 'View live examples', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to select the payment form which will be displayed on the checkout page.', 'braintree-payments' ) 
				), 
				'custom_form_styles' => array (
						'type' => 'textarea', 
						'title' => __( 'Styles', 'braintree-payments' ), 
						'default' => '{"input":{"font-size":"14px","font-family":"helvetica, tahoma, calibri, sans-serif","color":"#3a3a3a"}}', 
						'placeholder' => '{"input":{  "font-size":"14px","font-family":"helvetica, tahoma, calibri, sans-serif","color":"#3a3a3a"}}', 
						'class' => array (), 
						'tool_tip' => true, 
						'description' => __( 'You can customize the css of the hosted payment fields using this setting. All css must be in json format or it will cause errors. If the styles are left blank, the default styles will be applied automatically. Please reference https://developers.braintreepayments.com/guides/hosted-fields/styling/javascript/v3 if you have questions on how to format the custom css.', 'braintree-payments' ) 
				), 
				'dynamic_card_display' => array (
						'type' => 'checkbox', 
						'title' => __( 'Dynamic Card display' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the payment form will display the card type dynamically as the customer enters their payment information.' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Dynamic Card Display', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'If enabled, an icon of the card type being entered by the customer will appear. This provides dynamic feedback to the customer as they enter their information.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/dynamic_card_display.png' 
						) 
				), 
				'postal_field_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Display Postal Field', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the custom form that you select will display a field for the customer to enter the postal code associated with their payment method.', 'braintree-payments' ) 
				), 
				'cvv_field_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Display CVV Field', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the custom form that you select will display a field for the customer to enter their card security code (CVV).', 'braintree-payments' ) 
				), 
				'admin_text_for_form' => array (
						'type' => 'checkbox', 
						'title' => __( 'Use Custom Texts', 'braintree-payments' ), 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the texts you enter for card numbers, placeholders, etc will be displayed on the custom form. If not selected, the standard texts will be used.', 'braintree-payments' ) 
				), 
				'card_number_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Number Placeholder', 'braintree-payments' ), 
						'default' => __( 'Card Number', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card number field.', 'braintree-payments' ) 
				), 
				'card_cvv_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'CVV Placeholder', 'braintree-payments' ), 
						'default' => __( 'CVV', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card security code field.', 'braintree-payments' ) 
				), 
				'card_expiration_date_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Date Placeholder', 'braintree-payments' ), 
						'default' => __( 'MM/YY', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'For forms that contain a combined expiration date and year, this will be the placeholder.', 'braintree-payments' ) 
				), 
				'card_expiration_month_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Month Placeholder', 'braintree-payments' ), 
						'default' => __( 'MM', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the expiration month.', 'braintree-payments' ) 
				), 
				'card_expiration_year_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Expiration Year Placeholder', 'braintree-payments' ), 
						'default' => __( 'YY', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the expiration year.', 'braintree-payments' ) 
				), 
				'card_postal_placeholder' => array (
						'type' => 'text', 
						'title' => __( 'Card Postal Placeholder', 'braintree-payments' ), 
						'default' => __( 'Postal Code', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value entered here will display as a placeholder on the credit card postal code field.', 'braintree-payments' ) 
				), 
				'card_number_label' => array (
						'type' => 'text', 
						'title' => __( 'Card Number Label', 'braintree-payments' ), 
						'default' => __( 'Card Number', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card number input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_cvv_label' => array (
						'type' => 'text', 
						'title' => __( 'CVV Label', 'braintree-payments' ), 
						'default' => __( 'CVV', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card cvv input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_postal_label' => array (
						'type' => 'text', 
						'title' => __( 'Postal Label', 'braintree-payments' ), 
						'default' => __( 'Postal Code', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card postal input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_expiration_date_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Date Label', 'braintree-payments' ), 
						'default' => __( 'Exp Date', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration date input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_expiration_month_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Month Label', 'braintree-payments' ), 
						'default' => __( 'Month', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration month input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_expiration_year_label' => array (
						'type' => 'text', 
						'title' => __( 'Exp Year Label', 'braintree-payments' ), 
						'default' => __( 'Year', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card expiration year input field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'card_save_label' => array (
						'type' => 'text', 
						'title' => __( 'Save Card Label', 'braintree-payments' ), 
						'default' => __( 'Save', 'braintree-payments' ), 
						'value' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'This text will display above or next to the card save field for forms that contain a label.', 'braintree-payments' ) 
				), 
				'enable_loader' => array (
						'type' => 'checkbox', 
						'title' => __( 'Checkout Loader', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'class' => 'filled-in', 
						'description' => __( 'If enabled, a loader will appear around the payment form to let the customer know that the payment is processing.', 'braintree-payments' ) 
				), 
				'custom_form_loader_file' => array (
						'type' => 'select', 
						'title' => __( 'Loader File', 'braintree-payments' ), 
						'default' => 'circular-loader.php', 
						'value' => '', 
						'options' => $this->get_loader_options(), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('enable_loader')}", 
								'data-show-if' => 'checked' 
						), 
						'link' => array (
								'url' => 'https://support.paymentplugins.com/hc/en-us/articles/115003651188', 
								'text' => __( 'create your own loader', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'This is the file of the loader that will display across your custom payment form.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function get_form_options()
	{
		$forms = bwc_get_custom_forms();
		$form_options = array ();
		foreach ( $forms as $k => $data ) {
			$form_options [ $k ] = $data [ 'label' ];
		}
		return $form_options;
	}

	public function get_loader_options()
	{
		$dirs = array (
				bt_manager()->plugin_path() . 'templates/loader/', 
				trailingslashit( get_stylesheet_directory() ) . bt_manager()->template_path() . 'loader/', 
				trailingslashit( get_template_directory() ) . bt_manager()->template_path() . 'loader/' 
		);
		$files = array (
				'circular-loader.php' => 'circular-loader', 
				'hour-glass.php' => 'hour-glass', 
				'loading.php' => 'loading', 
				'pacman.php' => 'pacman', 
				'ping-pong.php' => 'ping-pong', 
				'processing-plain.php' => 'processing-plain', 
				'rotating-gears.php' => 'rotating-gears', 
				'simple-dots.php' => 'simple-dots', 
				'stairs-loader.php' => 'stairs-loader' 
		);
		foreach ( $dirs as $dir ) {
			$pattern = $dir . "*.php";
			foreach ( glob( $pattern ) as $file_name ) {
				$name = preg_match( '/(([^\/]*).php)/', $file_name, $matches ) ? $matches [ 1 ] : $file_name;
				$files [ $name ] = isset( $matches [ 2 ] ) ? $matches [ 2 ] : $name;
			}
		}
		return $files;
	}
}