<?php

class Braintree_Gateway_Donation_Settings extends Braintree_Gateway_Settings_API
{
	
	/**
	 *
	 * @var Braintree_Gateway_Donation_Modal_Settings
	 */
	public $modal_settings;
	
	public $custom_form_settings;
	
	public $merchant_account_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_PayPal_Donation_Settings
	 */
	public $paypal_options;

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'donation_settings';
		$this->tab = 'donation-settings';
		$this->label = __( 'Donations', 'braintree-payments' );
		$this->title = array (
				'title' => __( 'Donation Settings', 'braintree-payments' ), 
				'description' => __( 'On this page you can configure your donation settings. This plugin allows you to select the donation 
						form and the types of fields that will appear on your donation form.
						<p>To add a donation section on a page use shortcode <strong>[braintree_donations]</strong>. You can add a dropdown for the donation amount if desired. <strong>[braintree_donations 1="2" 2="5" 3="10"]</strong></p>
						<p>To add a recurring donation section on a page, use shortcode <strong>[braintree_recurring_donation 1="my_subscription_plan"]</strong>. Each plan you add will be added to a dropdown of selectable recurring donations.</p> 
				<p>For examples of how to use the shortcodes, visit our <a target="_blank" href="https://support.paymentplugins.com/hc/en-us/sections/115000960827-Donations">Help Center</a></p>', 'braintree-payments' ) 
		);
		add_filter( 'braintree_settings_localized_variables', array (
				$this, 
				'localize_vars' 
		) );
		
		include_once 'class-donation-modal-settings.php';
		include_once 'class-donation-custom-form-settings.php';
		$this->modal_settings = new Braintree_Gateway_Donation_Modal_Settings();
		$this->custom_form_settings = new Braintree_Gateway_Donation_Custom_Form_Settings();
		$this->paypal_options = new Braintree_Gateway_PayPal_Donation_Settings();
		
		parent::__construct();
	}

	public function localize_vars( $vars )
	{
		$keys = array (
				'donation_payment_methods', 
				'donation_fields' 
		);
		foreach ( $keys as $key ) {
			$field_key = $this->get_field_key_name( $key );
			$vars [ 'keys' ] [ $field_key ] = array (
					'options' => $this->settings() [ $key ] [ 'options' ], 
					'html' => '<div class="chip">%title%<input type="hidden" name="' . $field_key . '[%name%]" value=""><i class="remove-settings-chip close material-icons">close</i></div>', 
					'toast' => __( 'Field %s has already been added.', 'braintree-payments' ) 
			);
		}
		return $vars;
	}

	public function settings()
	{
		return array (
				'card_donation_enabled' => array (
						'title' => __( 'Enable Card Donation', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, credit card donations will be an option on your donation form.', 'braintree-payments' ) 
				), 
				'donation_gateway_title' => array (
						'title' => __( 'Credit Card Title', 'braintree-payments' ), 
						'type' => 'text', 
						'default' => __( 'Credit Card', 'braintree-payments' ), 
						'value' => '', 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('card_donation_enabled')}", 
								'data-show-if' => 'checked' 
						), 
						'tool_tip' => true, 
						'description' => __( 'The title that appears next to the payment option on the donation page.', 'braintree-payments' ) 
				), 
				'donation_tax_exempt' => array (
						'title' => __( 'Tax Exempt', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => '', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If set to true, then the transaction will be passed to Braintree as tax exempt.', 'braintree-payments' ) 
				), 
				'authorize_donation' => array (
						'type' => 'checkbox', 
						'title' => __( 'Authorize Transaction', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'default' => '', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, donation transactions will be authorized but not submitted for settlement. You can capture authorized charges via the Donations admin page.', 'braintree-payments' ) 
				), 
				'donation_fraud_tools' => array (
						'type' => 'checkbox', 
						'title' => __( 'Enable Advanced Fraud', 'braintree-payments' ), 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If you have enabled Advanced Fraud Tools from within your Braintree Control Panel then ensure this setting is enabled.', 'braintree-payments' ) 
				), 
				'donation_form_type' => array (
						'type' => 'select', 
						'title' => __( 'Payment Form Type', 'braintree-payments' ), 
						'default' => 'dropin', 
						'value' => '', 
						'options' => array (
								'dropin' => __( 'Dropin Form', 'braintree-payments' ), 
								'custom' => __( 'Custom Form', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'You can select if you want your donation payment form to be the Braintree dropin form or a custom form.', 'braintree-payments' ) 
				), 
				'donation_custom_form_settings' => array (
						'type' => 'custom', 
						'title' => __( 'Custom Form Settings', 'braintree-payments' ), 
						'function' => array (
								$this->custom_form_settings, 
								'output_settings' 
						), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('donation_form_type')}", 
								'data-show-if' => 'custom' 
						), 
						'tool_tip' => true, 
						'description' => __( 'If you have selected to use a custom form, this setting allows you to customize your custom form.', 'braintree-payments' ) 
				), 
				'paypal_donation_options' => array (
						'title' => __( 'PayPal Options', 'braintree-payments' ), 
						'type' => 'custom', 
						'function' => array (
								$this->paypal_options, 
								'output_options' 
						), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('donation_form_type')}", 
								'data-show-if' => 'custom' 
						), 
						'tool_tip' => true, 
						'description' => __( 'These are settings pertaining to the PayPal and PayPal Credit donation gateways.', 'braintree-payments' ) 
				), 
				'donation_form_layout' => array (
						'title' => __( 'Form Layout', 'braintree-payments' ), 
						'type' => 'select', 
						'options' => array (
								'modal' => __( 'Modal', 'braintree-payments' ), 
								'inline' => __( 'Inline', 'braintree-payments' ) 
						), 
						'default' => 'modal', 
						'title' => __( 'Form Layout', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'The form layout has two options. If modal is selected, the form will appear as a popup when the donator
						clicks the donation button. If inline is selected, the donation form will appear inside the html of the page.', 'braintree-payments' ) 
				), 
				'donation_modal_options' => array (
						'type' => 'custom', 
						'title' => __( 'Donation Modal Options', 'braintree-payments' ), 
						'default' => '', 
						'value' => '', 
						'function' => array (
								$this->modal_settings, 
								'output_settings' 
						), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('donation_form_layout')}", 
								'data-show-if' => 'modal' 
						), 
						'tool_tip' => true, 
						'description' => __( 'These settings pertain to all of the modal options.', 'braintree-payments' ) 
				), 
				'donation_custom_form_loader_file' => array (
						'type' => 'select', 
						'title' => __( 'Loader File', 'braintree-payments' ), 
						'default' => 'circular-loader.php', 
						'value' => '', 
						'options' => $this->get_loader_options(), 
						'tool_tip' => true, 
						'description' => __( 'This is the file of the loader that will display across your custom payment form.', 'braintree-payments' ) 
				), 
				'donation_button_text' => array (
						'type' => 'text', 
						'value' => 'Donate', 
						'default' => __( 'Donate', 'braintree-payments' ), 
						'title' => __( 'Button Text', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the text that appears on the donation button by entering the text here.', 'braintree-payments' ) 
				), 
				'donation_button_background' => array (
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#61D395', 
						'title' => __( 'Background Color', 'braintree-payments' ), 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the background color of the donation button by selecting a color from the color picker.', 'braintree-payments' ) 
				), 
				'donation_button_border' => array (
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#61D395', 
						'title' => __( 'Border Color', 'braintree-payments' ), 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the border color of the donation button by selecting a color from the color picker.', 'braintree-payments' ) 
				), 
				'donation_button_text_color' => array (
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#ffffff', 
						'title' => __( 'Text Color', 'braintree-payments' ), 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the text color of the button.', 'braintree-payments' ) 
				), 
				'donation_fields' => array (
						'title' => __( 'Donation Fields', 'braintree-payments' ), 
						'type' => 'multiselect', 
						'default' => array (), 
						'options' => array (
								'billing_first_name' => __( 'First Name', 'braintree-payments' ), 
								'billing_last_name' => __( 'Last Name', 'braintree-payments' ), 
								'billing_company' => __( 'Billing Company', 'braintree-payments' ), 
								'billing_address_1' => __( 'Billing Address', 'braintree-payments' ), 
								'billing_address_2' => __( 'Billing Address2', 'braintree-payments' ), 
								'billing_country' => __( 'Billing Country', 'braintree-payments' ), 
								'billing_city' => __( 'Billing City', 'braintree-payments' ), 
								'billing_state' => __( 'Billing State', 'braintree-payments' ), 
								'billing_postalcode' => __( 'Postal Code', 'braintree-payments' ), 
								'email_address' => __( 'Email Address', 'braintree-payments' ), 
								'donation_message' => __( 'Donation Message', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'This settings allows you to set each of the donation fields that will appear on your donation form.', 'braintree-payments' ) 
				), 
				'donation_production_merchant_account_id' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => '', 
						'title' => __( 'Production Merchant Account Id', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The merchant account ID determines the settlement currency of your donations. If left blank, the transcation will be settled using your default Braintree merchant account.', 'braintree-payments' ) 
				), 
				'donation_sandbox_merchant_account_id' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => '', 
						'title' => __( 'Sandbox Merchant Account Id', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The merchant account ID determines the settlement currency of your donations. If left blank, the transcation will be settled using your default Braintree merchant account.', 'braintree-payments' ) 
				), 
				'donation_currency' => array (
						'type' => 'select', 
						'title' => __( 'Donation Currency', 'braintree-payments' ), 
						'options' => braintree_get_currencies(), 
						'default' => 'USD', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'You can set the currency that will display on the amount field. Ensure this currency matches the currency for your Braintree Account. If you have set a merchant account for donations, the donation currency will automatically use that currency.', 'braintree-payments' ) 
				), 
				'donation_success_url' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => '', 
						'title' => __( 'Success URL', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'Enter the url of the page/site you would like the customer to be redirected to after the donation. This page typically has a message thanking the donor and informing them about the donation.', 'braintree-payments' ) 
				), 
				'donation_payment_methods' => array (
						'title' => __( 'Accepted Payment Methods', 'braintree-payments' ), 
						'type' => 'multiselect', 
						'default' => array (
								'amex', 
								'discover', 
								'visa', 
								'master_card' 
						), 
						'options' => array (
								'amex' => __( 'Amex', 'braintree-payments' ), 
								'china_union_pay' => __( 'China Union Pay', 'braintree-payments' ), 
								'diners_club_international' => __( 'Diners', 'braintree-payments' ), 
								'discover' => __( 'Discover', 'braintree-payments' ), 
								'jcb' => __( 'JCB', 'braintree-payments' ), 
								'maestro' => __( 'Maestro', 'braintree-payments' ), 
								'master_card' => __( 'MasterCard', 'braintree-payments' ), 
								'visa' => __( 'Visa', 'braintree-payments' ), 
								'paypal' => __( 'PayPal', 'braintree-payments' ) 
						), 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'These payment methods will appear as icons aboove the payment form.', 'braintree-payments' ) 
				) 
		);
	}

	public function get_loader_options()
	{
		$dirs = array (
				bt_manager()->plugin_path() . 'templates/donations/loader/', 
				trailingslashit( get_stylesheet_directory() ) . bt_manager()->template_path() . 'donations/loader/', 
				trailingslashit( get_template_directory() ) . bt_manager()->template_path() . 'donations/loader/' 
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

use Braintree\PaymentInstrumentType;
class Braintree_Gateway_PayPal_Donation_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->tab = 'donation-settings';
		$this->page = 'braintree-gateway-settings';
		$this->id = 'donation_settings';
		$this->title = array (
				'title' => __( 'PayPal Settings', 'braintree-payments' ), 
				'description' => __( 'These options allow you to customize the PayPal & PayPal Credit donation gateway.', 'braintree-payments' ) 
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

	public function output_paypal_buttons( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'views/paypal-buttons.php';
	}

	public function settings()
	{
		return array (
				'paypal_donation_enabled' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => 'yes', 
						'title' => __( 'PayPal Enabled', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If you have enabled custom forms and want to use PayPal as a payment option you must select this option. PayPal will be treated as a separate gateway. You must ensure that you have linked your PayPal and Braintree accounts.', 'braintree-payments' ) 
				), 
				'paypal_donation_gateway_title' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => __( 'PayPal', 'braintree-payments' ), 
						'title' => __( 'PayPal Title', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value here determines the text that appears on the checkout page next to the PayPal gateway.', 'braintree-payments' ) 
				), 
				'paypal_donation_display_name' => array (
						'type' => 'text', 
						'default' => get_option( 'blogname' ), 
						'title' => __( 'PayPal Display Name', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'This is the name that will appear on the PayPal checkout screen if the customer chooses to pay with PayPal.', 'braintree-payments' ) 
				), 
				'paypal_donation_button_design' => array (
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
				'paypal_credit_donation_enabled' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => '', 
						'title' => __( 'PayPal Credit', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'PayPal credit allows your customers to pay for their order over time. You receive all the funds up front and the customer makes payments on their end.', 'braintree-payments' ) 
				), 
				'paypal_credit_donation_button' => array (
						'type' => 'select', 
						'title' => __( 'PayPal Credit Button', 'braintree-payments' ), 
						'default' => 0, 
						'options' => $this->get_paypal_credit_button_options(), 
						'class' => '', 
						'value' => '', 
						'default' => 1, 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('paypal_credit_donation_enabled')}", 
								'data-show-if' => 'checked' 
						), 
						'tool_tip' => true, 
						'description' => __( 'When PayPal credit is offered as a payment option, this is the button that will appear on the checkout page.', 'braintree-payments' ) 
				), 
				'paypal_credit_donation_title' => array (
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
				) 
		);
	}

	public function output_options( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
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
		$buttons = bfwcd_get_paypal_buttons();
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
new Braintree_Gateway_Donation_Settings();