<?php
use Braintree\PaymentInstrumentType;
/**
 * Settings page for the checkout options.
 *
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 */
class Braintree_Gateway_Checkout_Settings extends Braintree_Gateway_Settings_API
{
	
	/**
	 *
	 * @var Braintree_Gateway_Custom_Form_Settings
	 */
	public $custom_form_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_PayPal_Settings
	 */
	public $paypal_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_ApplePay_Settings
	 */
	public $applepay_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_DynamicDescriptor_Settings
	 */
	public $descriptor_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_Merchant_Account_Settings
	 */
	public $merchant_account_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_Dropin_Settings
	 */
	public $dropin_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_ThreeDS_Settings
	 */
	public $threeDS_settings;
	
	/**
	 *
	 * @var Braintree_Gateway_Icon_Settings
	 */
	public $icon_settings;
	
	public $fee_settings;

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->tab = 'checkout-settings';
		$this->label = __( 'Checkout', 'braintree-payments' );
		$this->title = array (
				'title' => 'Checkout Settings', 
				'description' => sprintf( __( 'On this page you can configure all the settings that affect the WooCommerce checkout page of your Wordpress site. 
                        If you have enabled sandbox mode and want to test transactions, you must use <a target="_blank" href="%s">Braintree Test Cards</a>.', 'braintree-payments' ), esc_url( admin_url() . 'admin.php?page=braintree-test-data-page' ) ) 
		);
		add_action( 'bfwc_settings_title_after_description', array (
				$this, 
				'display_help_button' 
		), 9, 2 );
		add_action( 'bfwc_settings_title_after_description', array (
				$this, 
				'dispay_license_button' 
		), 10, 2 );
		add_action( 'braintree_gateway_before_save_settings', array (
				$this, 
				'wpml_dynamic_texts' 
		) );
		
		include_once 'class-custom-form-settings.php';
		include_once 'class-paypal-settings.php';
		include_once 'class-descriptor-settings.php';
		include_once 'class-merchant-account-settings.php';
		include_once 'class-applepay-settings.php';
		include_once 'class-threeds-settings.php';
		$this->custom_form_settings = new Braintree_Gateway_Custom_Form_Settings();
		$this->paypal_settings = new Braintree_Gateway_PayPal_Settings();
		$this->descriptor_settings = new Braintree_Gateway_DynamicDescriptor_Settings();
		$this->merchant_account_settings = new Braintree_Gateway_Merchant_Account_Settings();
		$this->applepay_settings = new Braintree_Gateway_ApplePay_Settings();
		$this->threeDS_settings = new Braintree_Gateway_ThreeDS_Settings();
		$this->dropin_settings = new Braintree_Gateway_Dropin_Settings();
		$this->icon_settings = new Braintree_Gateway_Icon_Settings();
		$this->fee_settings = new Braintree_Gateway_Fee_Settings();
		parent::__construct();
	
	}

	public function settings()
	{
		return array (
				'enabled' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => 'yes', 
						'title' => __( 'Enable Braintree Payments', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If Braintree Payments is enabled, you can process credit cards, PayPal, and Apple Pay payments using your Braintree account.', 'braintree-payments' ) 
				), 
				'checkout_form' => array (
						'type' => 'select', 
						'title' => __( 'Checkout Form', 'braintree-payments' ), 
						'default' => 'custom_form', 
						'value' => '', 
						'options' => array (
								'custom_form' => __( 'Custom Form', 'braintree-payments' ), 
								'dropin_form' => __( 'Dropin Form', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'You have the option of selecting to use a custom form or the Braintree dropin form. If the custom form is selected, you can customize all of the options associated with custom forms.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Checkout Form', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'You can select the type of payment form that you wish to appear on the checkout page, add payment method page, etc. You can also customize the existing custom forms by copying the plugin\'s templates folder to your themes folder. To customize a form, create a directory
										inside your active theme called <strong>woo-payment-gateway</strong>. Then copy the contents of the templates folder inside the plugin to the new directory in your theme.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/checkout_form.png' 
						) 
				), 
				'dropin_form_design' => array (
						'type' => 'custom', 
						'title' => __( 'Dropin Options', 'braintree-payments' ), 
						'function' => array (
								$this->dropin_settings, 
								'output_settings' 
						), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('checkout_form')}", 
								'data-show-if' => 'dropin_form' 
						), 
						'tool_tip' => true, 
						'description' => __( 'If you select the dropin form, you will have configuration options you cna maintain specific to the dropin.', 'braintree-payments' ) 
				), 
				'custom_form_design' => array (
						'type' => 'custom', 
						'title' => __( 'Custom Form Design', 'braintree-payments' ), 
						'default' => '', 
						'function' => array (
								$this->custom_form_settings, 
								'output_settings' 
						), 
						'attributes' => array (
								'data-parent-setting' => "{$this->get_field_key_name('checkout_form')}", 
								'data-show-if' => 'custom_form' 
						), 
						'tool_tip' => true, 
						'description' => __( 'If you have selected the custom form, then this option allows you to configure all of the settings associated with a custom form such as the label text, placeholders, etc.', 'braintree-payments' ) 
				), 
				'paypal_options' => array (
						'type' => 'custom', 
						'title' => __( 'PayPal Options' ), 
						'default' => '', 
						'function' => array (
								$this->paypal_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'By clicking the PayPal settings link, you can configure options that pertain to PayPal.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'PayPal Setup', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'In order to accept PayPal using your Braintree account, you must enable PayPal. To do this, login to your Braintree account 
										and navigation to <strong>Settings</strong> > <strong>Processing</strong>. PayPal is enabled by default in Braintree\'s sandbox environment.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/paypal_options.png' 
						) 
				), 
				'applepay_options' => array (
						'type' => 'custom', 
						'title' => __( 'Apple Pay Options', 'braintree-payments' ), 
						'default' => '', 
						'function' => array (
								$this->applepay_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'By clicking the Apple Pay settings link, you can configure options that pertain to Apple Pay.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Apple Pay Setup', 'braintree-payments' ), 
								'type' => 'video', 
								'description' => __( 'In order to accept Apple Pay on your WooCommerce site, you must setup Apple Pay within your Braintree account. Please watch our video which demonstrates how to integrate Apple Pay.
										<p class="small">You can view our step by step guide on our <a target="_blank" href="https://support.paymentplugins.com/hc/en-us/articles/115001439527-Apple-Pay-Integration">Help Center</a></p>', 'braintree-payments' ), 
								'url' => 'https://www.youtube.com/embed/LzXAv3jqkpI?vq=hd480' 
						) 
				), 
				'creditcard_format' => array (
						'title' => __( 'Credit Card Display', 'braintree-payments' ), 
						'type' => 'select', 
						'options' => $this->get_credit_card_options(), 
						'value' => '', 
						'default' => 'type_last4', 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to customize how the credit card will display for your customers on orders, subscriptions, etc.' ) 
				), 
				'saved_payment_methods_style' => array (
						'type' => 'select', 
						'title' => __( 'Saved Credit Cards Style', 'braintree-payments' ), 
						'default' => 'dropdown', 
						'options' => apply_filters( 'bfwc_saved_payment_method_style_options', array (
								'dropdown' => __( 'Dropdown Style', 'braintree-payments' ), 
								'inline' => __( 'Inline Style', 'braintree-payments' ) 
						) ), 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to set how you want the customer\'s saved payment methods to be displayed. Dropdown will display the saved payment methods in an enhanced dropdown and inline will show all available payment methods at one time.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Saved Payment Display Style', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'This setting determines how your customer\'s saved payment methods will be displayed on the checkout page.', 'braintree-payments' ), 
								'url' => array (
										array (
												'title' => __( 'Dropdown Style', 'braintree-payments' ), 
												'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/saved_payment_methods_style-dropdown.png' 
										), 
										array (
												'title' => __( 'Inline Style', 'braintree-payments' ), 
												'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/saved_payment_methods_style-inline.png' 
										) 
								) 
						) 
				), 
				'dynamic_descriptor_settings' => array (
						'type' => 'custom', 
						'title' => __( 'Dynamic Descriptor Options', 'braintree-payments' ), 
						'function' => array (
								$this->descriptor_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to enable and configure your dynamic descriptor. Descriptors affect what appears on your customer\'s credit card statement.', 'braintree-payments' ) 
				), 
				'title_text' => array (
						'type' => 'text', 
						'title' => __( 'Title Text', 'braintree-payments' ), 
						'value' => '', 
						'default' => 'Braintree Payments', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The title text is the text that will be displayed on the checkout page. Common values are Credit Card / PayPal.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Gateway Title', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'This is the text that will appear for the payment gateway on the checkout page.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/title_text.png' 
						) 
				), 
				'order_status' => array (
						'type' => 'select', 
						'title' => __( 'Order Status', 'braintree-payments' ), 
						'default' => 'default', 
						'options' => $this->get_order_statuses(), 
						'tool_tip' => true, 
						'description' => __( 'This is the status of the order once payment is complete. If default is selected, then WooCommerce will set the order status automatically. Default is the recommended setting as it allows standard WooCommerce code to process the order status.', 'braintree-payments' ) 
				), 
				'order_prefix' => array (
						'type' => 'text', 
						'title' => __( 'Order Prefix', 'braintree-payments' ), 
						'value' => '', 
						'default' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The order prefix is prepended to the WooCommerce order id and will appear within Braintree as the Order ID. This settings can be helpful if you want to distinguish
						orders that came from this particular site or plugin.', 'braintree-payments' ) 
				), 
				'payment_methods' => array (
						'type' => 'multiselect', 
						'title' => __( 'Accepted Payment Methods', 'braintree-payments' ), 
						'class' => '', 
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
						'tool_tip' => true, 
						'description' => __( 'When selected, an icon will appear on the checkout page showing the accepted payment method.', 'braintree-payments' ) 
				), 
				'icon_settings' => array (
						'type' => 'custom', 
						'title' => __( 'Icon Settings', 'braintree-payments' ), 
						'function' => array (
								$this->icon_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'Settings pertaining to the icon styles for payment methods.', 'braintree-payments' ) 
				), 
				'fee_settings' => array (
						'type' => 'custom', 
						'title' => __( 'Fee Settings', 'braintree-payments' ), 
						'function' => array (
								$this->fee_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'Settings pertaining to fees that you can create such as a convenience fee for accepting credit cards.', 'braintree-payments' ) 
				), 
				'advanced_fraud_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Advanced Fraud Tools', 'braintree-payments' ), 
						'default' => '', 
						'value' => 'yes', 
						'description' => __( 'You must enable advanced fraud tools in your Braintree Control Panel. When this options is enabled, additional data will be collected
                                at checkout for purposes of fraud detection.', 'braintree-payments' ), 
						'tool_tip' => true, 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Advanced Fraud', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'If enabled, Braintree will perform a series of fraud checks using your customers device data when a transaction is being processed which helps catch fraudulent activity before the request ever reaches the customer\'s bank.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/advanced_fraud_enabled.png' 
						) 
				), 
				'fail_on_duplicate' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => '', 
						'title' => __( 'Fail On Duplicate Payment Methods', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, duplicate credit cards cannot be added to the vault. For instance, if the VISA card <strong>4111111111111111</strong> already exists in the vault and
						another customer tries to add that card, there will be an error message presented to the customer informing them that that card already exists in the vault.', 'braintree-payments' ) 
				), 
				'authorize_transaction' => array (
						'type' => 'checkbox', 
						'title' => __( 'Authorize Amount Only', 'braintree-payments' ), 
						'default' => '', 
						'class' => 'filled-in', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enable, your orders will be authorized for the order amount but not automatically settled. You will need to capture the amount for the order by going to the admin
						order page. This setting is not recommend for merchants that want their orders to be settled automatically.', 'braintree-payments' ) 
				), 
				'threeds_options' => array (
						'type' => 'custom', 
						'title' => __( '3D Secure Settings', 'braintree-payments' ), 
						'function' => array (
								$this->threeDS_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'These are settings which pertain to 3D Secure.', 'braintree-payments' ) 
				), 
				'save_payment_methods' => array (
						'type' => 'checkbox', 
						'value' => 'yes', 
						'default' => 'yes', 
						'title' => __( 'Allow Save Payment Method', 'braintree-payments' ), 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, a checkbox will be available on the checkout page allowing your customer\'s to save their payment method. The payment methods are stored securely in Braintree\'s vault and never touch your server. Note: if the cart contains a subscription, there will be no checkbox because the payment method will be saved automatically. There will also be no checkbox for guest checkout as a user must be logged in to save a payment method.', 'braintree-payments' ) 
				), 
				'refresh_payment_fragments' => array (
						'title' => __( 'Refresh Payment Fragments', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => '', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the payment form will be refreshed everytime WooCommerce makes an update call to the server on the checkout page. For user experience, it\'s recommend that you leave this option disabled.', 'braintree-payments' ) 
				), 
				'merchant_accounts' => array (
						'type' => 'custom', 
						'save' => false, 
						'title' => __( 'Merchant Accounts', 'braintree-payments' ), 
						'function' => array (
								$this->merchant_account_settings, 
								'output_settings' 
						), 
						'tool_tip' => true, 
						'description' => __( 'Your merchant accounts determine the settlement currency of your transactions. This setting is optional but if you use a currency switcher it\'s
                                recommended that you configure your merchant accounts.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Merchant Accounts', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'To locate your merchant accounts, login to Braintree and navigate to <strong>Settings</strong> > <strong>Processing</strong> and scroll to the bottom of the page.
										<p class="small">Merchant accounts are used by Braintree to determine the settlement currency. If you use a currency switcher with WooCommerce, you should configure your merchant accounts.</p>', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/merchant_accounts.png' 
						) 
				) 
		);
	}

	public function get_credit_card_options()
	{
		$patterns = braintree_get_payment_method_formats() [ PaymentInstrumentType::CREDIT_CARD ];
		$formats = array ();
		foreach ( $patterns as $k => $v ) {
			$formats [ $k ] = $v [ 'example' ];
		}
		return $formats;
	}

	public function get_order_statuses()
	{
		if ( function_exists( 'wc_get_order_statuses' ) ) {
			foreach ( wc_get_order_statuses() as $status => $value ) {
				$statuses [ $status ] = wc_get_order_status_name( $status );
			}
			$statuses [ 'default' ] = __( 'Default', 'braintree-payments' );
		} else {
			$statuses = array (
					'default' => __( 'Default', 'braintree-payments' ), 
					'wc-processing' => __( 'Processing', 'braintree-payments' ), 
					'wc-completed' => __( 'Completed', 'braintree-payments' ) 
			);
		}
		return $statuses;
	}

	/**
	 * Method that is used to update any dynamic texts used by WPML.
	 *
	 * @since 2.6.7
	 */
	public function wpml_dynamic_texts()
	{
		if ( function_exists( 'icl_register_string' ) && function_exists( 'WC' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			foreach ( bwc_get_payment_gateways() as $id ) {
				$gateway = $gateways [ $id ];
				switch( $gateway->id ) {
					case WC_Braintree_Payment_Gateway::ID :
						$key = 'title_text';
						break;
					case WC_PayPal_Payment_Gateway::ID :
						$key = 'paypal_gateway_title';
						break;
					case WC_PayPal_Credit_Payment_Gateway::ID :
						$key = 'paypal_credit_title';
						break;
					case WC_Applepay_Payment_Gateway::ID :
						$key = 'applepay_gateway_title';
						break;
				}
				$new_title = $this->get_field_value( $key );
				$old_title = bt_manager()->get_option( $key );
				
				if ( $new_title !== $old_title ) {
					icl_unregister_string( 'woocommerce', $id . '_gateway_title' );
					icl_register_string( 'woocommerce', $id . '_gateway_title', $new_title );
				}
			}
		}
	}

}

class Braintree_Gateway_Dropin_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->tab = 'checkout-settings';
		$this->label = __( 'Checkout', 'braintree-payments' );
		$this->title = array (
				'title' => 'Dropin Settings', 
				'description' => sprintf( __( 'These settings allow you to configure the dropin form. When V2 of the dropin form is enabled, PayPal will appear as part of the credit card form if enabled within your Braintree account.' ), esc_url( admin_url() . 'admin.php?page=braintree-test-data-page' ) ) 
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
				'dropin_form_version' => array (
						'title' => __( 'Dropin Version', 'braintree-payments' ), 
						'type' => 'select', 
						'default' => 'v2', 
						'options' => array (
								'v2' => __( 'Version 2', 'braintree-payments' ), 
								'v3' => __( 'Version 3', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'This option allows you to select the dropin version. V3 is a different style than V2.', 'braintree-payments' ) 
				), 
				'dropin_postal_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Is Postal Enabled', 'braintree-payments' ), 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'Click the checkbox if you have enabled postal code validations in your Braintree control panel. This will ensure the postal code from the dropin form us used for validations and not the postal billing field from WooCommerce.', 'braintree-payments' ) 
				) 
		);
	}
}

class Braintree_Gateway_Icon_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->tab = 'checkout-settings';
		$this->label = __( 'Checkout', 'braintree-payments' );
		$this->title = array (
				'title' => 'Icon Settings', 
				'description' => sprintf( __( 'These settings allow you to configure how payment icons appear throughout your site.', 'braintree-payments' ) ) 
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
				'payment_method_location' => array (
						'type' => 'select', 
						'title' => __( 'Icons Location', 'braintree-payments' ), 
						'value' => '', 
						'default' => 'outside', 
						'options' => array (
								'inside' => __( 'Inside', 'braintree-payments' ), 
								'outside' => __( 'Outside', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'This setting determines if the payment icons appear on the payment form or on the outside next to the gateway selection radio button.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Icons Location', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'This setting determines the location of the payment method icons if you have selected any accepted payment methods to display.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/payment_method_location.png' 
						) 
				), 
				'method_icon_style' => array (
						'title' => __( 'Icon Style', 'braintree-payments' ), 
						'type' => 'select', 
						'default' => 'enclosed', 
						'options' => array (
								'enclosed' => __( 'Enclosed Icons', 'braintree-payments' ), 
								'open' => __( 'Open Icons', 'braintree-payments' ) 
						), 
						'tool_tip' => true, 
						'description' => __( 'The style of payment method icon used on your site.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Icons Style', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'The icon style for payment methods shown throughout the site.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/method_icon_style.png' 
						) 
				), 
				'display_icons_on_payment_methods' => array (
						'title' => __( 'Icons on Payment Methods Page', 'braintree-payments' ), 
						'type' => 'checkbox', 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'Allow icons to be shown next to saved payment methods on the My Account > Payment Methods page.', 'braintree-payments' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Icons Location', 'braintree-payments' ), 
								'type' => 'img', 
								'description' => __( 'If enabled, a payment method icon will appear next to the payment method.', 'braintree-payments' ), 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/display_icons_on_payment_methods.png' 
						) 
				) 
		);
	}
}

class Braintree_Gateway_Fee_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->tab = 'checkout-settings';
		$this->label = __( 'Checkout', 'braintree-payments' );
		$this->title = array (
				'title' => 'Fee Settings', 
				'description' => sprintf( __( 'You can add a fee to the customer\'s order such as a convenience fee for accepting credit cards. <a target="_blank" href="https://support.paymentplugins.com/hc/en-us/articles/115006411368-Fees">Fee Guide & Examples</a>' ) ) 
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
	}

	/**
	 *
	 * @since 2.6.7
	 * @param array $vars        	
	 */
	public function localize_vars( $vars )
	{
		$vars [ 'gateways' ] = $this->get_localized_gateways();
		$vars [ 'templates' ] [ 'fees' ] = array (
				'container' => bfwc_admin_backbone_template( 'fee-container', true ), 
				'fee' => bfwc_admin_backbone_template( 'fee', true, array (
						'gateways' => $this->get_localized_gateways(), 
						'key' => $this->get_field_key_name( 'checkout_fees' ) 
				) ) 
		);
		$vars [ 'fees' ] = $this->get_option( 'checkout_fees' );
		return $vars;
	}

	public function settings()
	{
		return array (
				'checkout_fee_enabled' => array (
						'type' => 'checkbox', 
						'title' => __( 'Checkout Fee Enabled', 'braintree-payments' ), 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled you can enter a fee calculation that will be applied to the order.', 'braintree-payments' ) 
				) 
		);
	}

	/**
	 *
	 * @since 2.6.7
	 * {@inheritDoc}
	 *
	 * @see Braintree_Gateway_Settings_API::save()
	 */
	public function save()
	{
		$value = $this->get_field_value( 'checkout_fees', array () );
		$fees = array ();
		foreach ( $value as $fee ) {
			if ( in_array( "", $fee ) ) {
				bt_manager()->add_admin_notice( 'error', __( 'Fee cannot have empty values.', 'braintree-payments' ) );
			} else {
				$fees [] = $fee;
			}
		}
		$this->set_setting( 'checkout_fees', $fees );
		parent::save();
	}

	/**
	 *
	 * @since 2.6.7
	 * @param string $key        	
	 * @param array $data        	
	 */
	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		$data [ 'extra_html' ] = '<div id="bfwc-fee-container" class="row"></div>';
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function get_localized_gateways()
	{
		$accepted_gateways = array ();
		if ( function_exists( 'WC' ) ) {
			$gateways = WC()->payment_gateways()->payment_gateways();
			foreach ( $gateways as $gateway ) {
				if ( $gateway->supports( 'bfwc_fees' ) ) {
					$accepted_gateways [ $gateway->id ] = array (
							'id' => $gateway->id, 
							'title' => $gateway->get_title() 
					);
				}
			}
		}
		return $accepted_gateways;
	}
}

new Braintree_Gateway_Checkout_Settings();