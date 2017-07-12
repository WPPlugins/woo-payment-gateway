<?php
use Braintree\Exception;

/**
 *
 * @author Payment Plugins
 * @copyright Payment Plugins 2016
 */
class Braintree_Gateway_Donation_Shortcode
{
	
	private $scripts = array (
			'js' => array (), 
			'css' => array () 
	);
	
	private $_cookie;
	
	private $donation = '';
	
	public $is_transaction = false;
	
	private $ajax_action = 'process_braintree_donation';
	
	private $ajax_recurring_action = 'process_braintree_recurring_donation';
	
	private $_transient = '';
	
	private $messages = array ();
	
	private $is_modal_active;
	
	public $available_gateways;
	
	/**
	 * Is only one recurring donation value desired?
	 *
	 * @var bool
	 */
	public $is_single_recurring = false;

	public function __construct()
	{
		add_shortcode( 'braintree_donations', array (
				$this, 
				'output_transaction_page' 
		) );
		add_shortcode( 'braintree_recurring_donation', array (
				$this, 
				'output_recurring_page' 
		) );
		add_action( "wp_ajax_{$this->ajax_action}", array (
				$this, 
				'process_donation' 
		) );
		add_action( "wp_ajax_nopriv_{$this->ajax_action}", array (
				$this, 
				'process_donation' 
		) );
		add_action( "wp_ajax_{$this->ajax_recurring_action}", array (
				$this, 
				'process_recurring_donation' 
		) );
		add_action( "wp_ajax_nopriv_{$this->ajax_recurring_action}", array (
				$this, 
				'process_recurring_donation' 
		) );
		add_action( 'wp', array (
				$this, 
				'init_cookie' 
		) );
		add_action( 'wp_enqueue_scripts', array (
				$this, 
				'register_scripts' 
		) );
		add_action( 'wp_print_scripts', array (
				$this, 
				'enqueue_scripts' 
		) );
		add_action( 'bfwc_available_donation_gateways', __CLASS__ . '::recurring_donation_gateways', 10, 2 );
	}

	public function output( $args )
	{
		$this->load_gateways();
		
		extract( array (
				'args' => $args 
		) );
		ob_start();
		if ( bfwcd_modal_enabled() ) {
			
			bfwc_get_template( 'donations/modal-form.php', array (
					'shortcode' => $this, 
					'args' => $args 
			) );
		} else {
			
			bfwc_get_template( 'donations/inline-form.php', array (
					'shortcode' => $this, 
					'args' => $args 
			) );
		}
		return ob_get_clean();
	}

	/**
	 * Output the html for the one time donation page.
	 *
	 * @param array $args        	
	 */
	public function output_transaction_page( $args )
	{
		global $bfwcd_shortcode_args;
		$bfwcd_shortcode_args = $args;
		$this->is_transaction = true;
		return $this->output( $args );
	}

	/**
	 * Output html for a recurring donation.
	 *
	 * @param array $args        	
	 */
	public function output_recurring_page( $args )
	{
		global $bfwcd_shortcode_args;
		$bfwcd_shortcode_args = $args;
		$this->is_transaction = false;
		$this->is_single_recurring = ! empty( $args [ 'recurring_donation_plan' ] ) ? true : false;
		if ( ! $this->is_single_recurring ) {
			$this->get_braintree_plans();
		}
		return $this->output( $args );
	}

	public function process_donation()
	{
		$this->load_gateways();
		
		$enabled_fields = $this->get_enabled_fields();
		
		foreach ( $enabled_fields as $field_name => $field ) {
			$value = apply_filters( "braintree_donation_validate_field_{$field_name}", $this->get_request_parameter( $field_name ) );
			if ( empty( $value ) ) {
				bfwcd_add_notice( sprintf( __( 'Field %s cannot be blank.', 'braintree-payments' ), $field [ 'label' ] ), 'error' );
			}
		}
		do_action( 'bfwcd_before_donation_process', $this );
		
		if ( bfwcd_has_errors() ) {
			$this->return_error();
		}
		$donation_id = $this->create_donation();
		
		$payment_method = sanitize_text_field( $_POST [ 'payment_gateway' ] );
		
		$gateway = $this->available_gateways [ $payment_method ];
		
		$gateway->process_donation( $donation_id );
		
		// There are errors so exit the method.
		if ( bfwcd_has_errors() ) {
			$this->return_error();
		}
		delete_transient( $_COOKIE [ $this->_cookie ] );
		wp_send_json( array (
				'result' => 'success', 
				'redirect_url' => bt_manager()->get_option( 'donation_success_url' ) 
		) );
	
	}

	/**
	 * Process the Braintree Subscription.
	 */
	public function process_recurring_donation()
	{
		$this->load_gateways();
		
		$enabled_fields = $this->get_enabled_fields();
		
		foreach ( $enabled_fields as $field_name => $field ) {
			$value = apply_filters( "braintree_donation_validate_field_{$field_name}", $this->get_request_parameter( $field_name ) );
			if ( empty( $value ) ) {
				bfwcd_add_notice( sprintf( __( 'Field %s cannot be blank.', 'braintree-payments' ), $field [ 'label' ] ), 'error' );
			}
		}
		if ( bfwcd_has_errors() ) {
			$this->return_error();
		}
		$donation_id = $this->create_donation( 'bt_rc_donation' );
		
		$donation = bfwcd_get_donation( $donation_id );
		
		$payment_method = sanitize_text_field( $_POST [ 'payment_gateway' ] );
		
		$gateway = $this->available_gateways [ $payment_method ];
		
		$gateway->process_recurring_donation( $donation_id );
		
		if ( bfwcd_has_errors() ) {
			$this->return_error();
		}
		delete_transient( $_COOKIE [ $this->_cookie ] );
		wp_send_json( array (
				'result' => 'success', 
				'redirect_url' => bt_manager()->get_option( 'donation_success_url' ) 
		) );
	}

	/**
	 * Create a donation or retrieve an existing donation.
	 *
	 * @return int $donation_id
	 */
	public function create_donation( $type = 'braintree_donation' )
	{
		$args = array ();
		$transient = $_COOKIE [ $this->_cookie ];
		$donation_id = get_transient( $transient );
		foreach ( $this->get_enabled_fields() as $field_name => $field ) {
			$args [ $field_name ] = $this->get_request_parameter( $field_name );
		}
		$args [ 'user_id' ] = is_user_logged_in() ? wp_get_current_user()->ID : 0;
		$args [ 'currency' ] = bt_manager()->get_option( 'donation_currency' );
		if ( ! $donation_id ) {
			switch( $type ) {
				case 'braintree_donation' :
					$donation_id = bfwcd_create_donation( $args );
					break;
				case 'bt_rc_donation' :
					$donation_id = bfwcd_create_recurring_donation( $args );
					break;
			}
			set_transient( $transient, $donation_id, 0 );
		} else {
			bfwcd_update_donation( $donation_id, $args );
			
			// always update the post type in case customer has switched from
			// donation to recurring donation or vice versa.
			wp_update_post( array (
					'post_type' => $type, 
					'ID' => $donation_id 
			) );
		}
		return $donation_id;
	}

	private function return_error()
	{
		return wp_send_json( array (
				'result' => 'failure', 
				'messages' => bfwcd_get_error_messages() 
		) );
		exit();
	}

	/**
	 * Enqueue all scripts that have been registered.
	 */
	public function localize_scripts()
	{
		wp_localize_script( 'bfwcd-donation-js', 'bfwcd_donation_vars', $this->localized_vars() );
		wp_localize_script( 'bfwcd-donation-js', 'bfwcd_error_messages', bfwc_get_combined_error_messages() );
		wp_localize_script( 'bfwcd-donation-js', 'bfwc_field_vars', self::get_field_vars() );
	}

	/**
	 * Enqueue all scripts needed for the donation functionality to work.
	 */
	public function register_scripts()
	{
		if ( ! bfwcd_is_donation_page() ) {
			return;
		}
		global $wp_scripts;
		
		$jquery_ver = $wp_scripts->registered [ 'jquery-ui-core' ]->ver ? $wp_scripts->registered [ 'jquery-ui-core' ]->ver : '1.11.4';
		
		$js_path = bt_manager()->plugin_assets_path() . 'js/';
		
		wp_register_script( 'bfwcd-donation-js', $js_path . 'frontend/donation/donations.js', array (
				'jquery' 
		), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-hosted-internal', $js_path . 'frontend/donation/hosted-fields.js', array (
				'bfwcd-client', 
				'bfwcd-hosted-external', 
				'bfwcd-data-collector' 
		), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-dropin-internal', $js_path . 'frontend/donation/dropin.js', array (
				'bfwcd-dropin-external' 
		), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-paypal-internal', $js_path . 'frontend/donation/paypal.js', array (
				'bfwcd-paypal-external', 
				'bfwcd-client', 
				'bfwcd-data-collector' 
		), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-paypal-credit-internal', $js_path . 'frontend/donation/paypal-credit.js', array (
				'bfwcd-paypal-external', 
				'bfwcd-client', 
				'bfwcd-data-collector' 
		), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-jquery-block', bt_manager()->plugin_assets_path() . 'js/jquery/blockUI.min.js', array (
				'jquery' 
		), bt_manager()->version, true );
		wp_register_script( 'bfwcd-select2', bt_manager()->plugin_assets_path() . 'js/select2/select2.min.js', array (
				'bfwcd-donation-js' 
		), bt_manager()->version, true );
		
		wp_register_style( 'bfwcd-css', bt_manager()->plugin_assets_path() . 'css/braintree.css', null, bt_manager()->version, null );
		wp_register_style( 'bfwcd-css2', bt_manager()->plugin_assets_path() . 'css/donations.css', null, bt_manager()->version, null );
		wp_register_style( 'bfwcd-select2-css', bt_manager()->plugin_assets_path() . 'css/select2.css', null, bt_manager()->version, null );
		wp_register_style( 'bfwcd-paypal-credit-css', bt_manager()->plugin_assets_path() . 'css/paypal/paypal-credit.css', null, bt_manager()->version, null );
		
		wp_register_script( 'bfwcd-dropin-external', BRAINTREE_JS_DROPIN, array (), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-client', BRAINTREE_JS_V3_CLIENT, null, bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-hosted-external', BRAINTREE_JS_V3_HOSTED, array (), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-data-collector', BRAINTREE_V3_DATA_COLLECTOR, array (), bt_manager()->version, true );
		
		wp_register_script( 'bfwcd-paypal-external', BRAINTREE_PAYPAL_JS, array (), bt_manager()->version, true );
		
		$custom_form = bfwcd_get_custom_form();
		
		wp_register_style( 'bfwcd-custom-form-css', $custom_form [ 'css' ], null, bt_manager()->version, null );
		
		if ( ! empty( $custom_form [ 'external_css' ] ) ) {
			wp_register_style( 'bfwcd-custom-form-external-css', $custom_form [ 'external_css' ], null, bt_manager()->version, null );
		}
		if ( ! empty( $custom_form [ 'javascript' ] ) ) {
			wp_register_script( 'bfwcd-custom-form-js', $custom_form [ 'javascript' ], array (
					'bfwcd-donation-js' 
			), bt_manager()->version, true );
		}
		
		$paypal_button = bfwcd_get_paypal_button();
		
		if ( ! empty( $paypal_button [ 'css' ] ) ) {
			wp_register_style( 'bfwcd-paypal-button-css', $paypal_button [ 'css' ], null, bt_manager()->version, null );
		}
		
		wp_register_style( 'bfwcd-jquery-ui', 'https://code.jquery.com/ui/' . $jquery_ver . '/themes/smoothness/jquery-ui.css', null, bt_manager()->version );
	
	}

	public function localized_vars()
	{
		$vars = array (
				'client_token' => bt_manager()->get_client_token(), 
				'dropin_form' => array (
						'enabled' => bfwcd_dropin_enabled() 
				), 
				'gateways' => array (
						'cards' => 'bfwc_card_donation_gateway', 
						'paypal' => 'bfwc_paypal_donation_gateway', 
						'paypal_credit' => 'bfwc_paypal_credit_donation_gateway' 
				), 
				'environment' => bt_manager()->get_environment(), 
				'fraud' => array (
						'enabled' => bfwcd_advanced_fraud_enabled() 
				) 
		);
		if ( ! bfwcd_is_recurring_donation_page() ) {
			$vars [ 'ajax_url' ] = admin_url() . 'admin-ajax.php?action=' . $this->ajax_action;
		} else {
			$vars [ 'ajax_url' ] = admin_url() . 'admin-ajax.php?action=' . $this->ajax_recurring_action;
		}
		if ( bfwcd_modal_enabled() ) {
			$vars [ 'modal' ] = true;
		}
		
		if ( ! bfwcd_dropin_enabled() ) {
			$vars [ 'custom_form' ] = array (
					'styles' => json_decode( bt_manager()->get_option( 'donation_custom_form_styles' ), true ), 
					'fields' => $this->get_custom_form_fields() 
			);
		}
		if ( bfwcd_paypal_enabled() ) {
			$vars [ 'paypal' ] [ 'options' ] = $this->is_transaction ? array (
					'flow' => 'checkout', 
					'currency' => bfwcd_get_donation_currency(), 
					'displayName' => bt_manager()->get_option( 'paypal_donation_display_name' ) 
			) : array (
					'flow' => 'vault', 
					'displayName' => bt_manager()->get_option( 'paypal_donation_display_name' ) 
			);
		}
		if ( bfwcd_paypal_credit_enabled() ) {
			$vars [ 'paypal_credit' ] [ 'options' ] = array (
					'flow' => 'checkout', 
					'offerCredit' => true, 
					'currency' => bfwcd_get_donation_currency(), 
					'displayName' => bt_manager()->get_option( 'paypal_donation_display_name' ) 
			);
		}
		
		return $vars;
	}

	public function get_donation_fields()
	{
		global $bfwcd_shortcode_args;
		$fields = array (
				'billing_first_name' => array (
						'type' => 'text', 
						'label' => __( 'First Name', 'braintree-payments' ), 
						'class' => '', 
						'placeholder' => __( 'First Name', 'braintree-payments' ), 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_first_name' 
						), 
						'required' => true 
				), 
				'billing_last_name' => array (
						'type' => 'text', 
						'label' => __( 'Last Name', 'braintree-payments' ), 
						'class' => '', 
						'placeholder' => __( 'Last Name', 'braintree-payments' ), 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_last_name' 
						), 
						'required' => true 
				), 
				'billing_company' => array (
						'type' => 'text', 
						'label' => __( 'Company Name', 'braintree-payments' ), 
						'class' => '', 
						'placeholder' => __( 'Company Name', 'braintree-payments' ), 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_company' 
						), 
						'required' => true 
				), 
				'billing_address_1' => array (
						'type' => 'text', 
						'label' => __( 'Billing Address', 'braintree-payments' ), 
						'placeholder' => __( 'Address', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_address_1' 
						), 
						'required' => true 
				), 
				'billing_address_2' => array (
						'type' => 'text', 
						'label' => __( 'Billing Address 2', 'billing_address_2' ), 
						'placeholder' => __( 'Address2', 'data-bt-donation' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-stripe' => 'billing_address_2' 
						), 
						'required' => false 
				), 
				'billing_city' => array (
						'type' => 'text', 
						'label' => __( 'Billing City', 'braintree-payments' ), 
						'placeholder' => __( 'City', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'address_city' 
						), 
						'required' => true 
				), 
				'billing_country' => array (
						'type' => 'select', 
						'label' => __( 'Country', 'braintree-payments' ), 
						'placeholder' => __( 'Country', 'braintree-payments' ), 
						'options' => braintree_get_countries(), 
						'class' => 'bfwc-select2 billing-country', 
						'value' => 'US', 
						'attributes' => array (
								'data-bt-donation' => 'address_country' 
						), 
						'required' => true 
				), 
				'billing_state' => array (
						'type' => 'text', 
						'label' => __( 'State / Region', 'braintree-payments' ), 
						'placeholder' => __( 'State', 'braintree-payments' ), 
						'class' => 'billing-state', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_state' 
						), 
						'required' => false 
				), 
				'billing_postalcode' => array (
						'type' => 'text', 
						'label' => __( 'Postal Code', 'braintree-payments' ), 
						'placeholder' => __( 'Postal Code', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'billing_postalcode' 
						), 
						'required' => true 
				), 
				'email_address' => array (
						'type' => 'text', 
						'label' => __( 'Email Address', 'braintree-payments' ), 
						'placeholder' => __( 'Email Address', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'email_address' 
						), 
						'required' => true 
				), 
				'donation_message' => array (
						'type' => 'textarea', 
						'label' => __( 'Donation Message', 'braintree-payments' ), 
						'placeholder' => __( 'Donation Message', 'braintree-payments' ), 
						'class' => '', 
						'value' => '', 
						'attributes' => array (
								'data-bt-donation' => 'donation_message' 
						), 
						'required' => false 
				) 
		);
		if ( ! empty( $bfwcd_shortcode_args [ 'start_date' ] ) ) {
			$start_date = current_time( 'm-d-Y' );
			$fields [ 'bfwcd_recurring_start_date' ] = array (
					'type' => 'text', 
					'label' => __( 'Donation Date', 'braintree-payments' ), 
					'placeholder' => '', 
					'class' => 'bfwcd-date-picker', 
					'value' => $start_date, 
					'attributes' => array (), 
					'required' => true 
			);
		}
		return apply_filters( 'braintree_get_donation_fields', $fields, $this );
	}

	public function get_custom_form()
	{
		$form_name = bt_manager()->get_option( 'donation_custom_form_design' );
		return braintree_get_custom_donation_forms() [ $form_name ];
	}

	public function get_custom_form_fields()
	{
		return $fields = apply_filters( 'braintre_donations_custom_fields', array (
				'number' => array (
						'selector' => '#bfwc-card-number', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_number_placeholder' ), 'braintree-payments' ) 
				), 
				'cvv' => array (
						'selector' => '#bfwc-cvv', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_cvv_placeholder' ), 'braintree-payments' ) 
				), 
				'postalCode' => array (
						'selector' => '#bfwc-postal-code', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_postal_placeholder' ), 'braintree-payments' ) 
				), 
				'expirationDate' => array (
						'selector' => '#bfwc-expiration-date', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_expiration_date_placeholder' ), 'braintree-payments' ) 
				), 
				'expirationMonth' => array (
						'selector' => '#bfwc-expiration-month', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_expiration_month_placeholder' ), 'braintree-payments' ) 
				), 
				'expirationYear' => array (
						'selector' => '#bfwc-expiration-year', 
						'placeholder' => __( bt_manager()->get_option( 'donation_card_expiration_year_placeholder' ), 'braintree-payments' ) 
				) 
		) );
	}

	public function get_button_styles()
	{
		$styles = apply_filters( 'braintree_donation_button_styles', array (
				'background-color' => bt_manager()->get_option( 'donation_button_background' ), 
				'border-color' => bt_manager()->get_option( 'donation_button_border' ), 
				'color' => bt_manager()->get_option( 'donation_button_text_color' ) 
		) );
		$css = array ();
		
		foreach ( $styles as $style => $value ) {
			$css [] = $style . ':' . $value . ';';
		}
		return implode( ' ', $css );
	}

	public function get_modal_button_styles()
	{
		$styles = apply_filters( 'braintree_modal_button_styles', array (
				'background-color' => bt_manager()->get_option( 'donation_modal_button_background' ), 
				'border-color' => bt_manager()->get_option( 'donation_modal_button_border' ), 
				'color' => bt_manager()->get_option( 'donation_modal_button_text_color' ) 
		) );
		$css = array ();
		
		foreach ( $styles as $style => $value ) {
			$css [] = $style . ':' . $value . ';';
		}
		return implode( ' ', $css );
	}

	private function get_request_parameter( $name )
	{
		return isset( $_POST [ $name ] ) ? $_POST [ $name ] : '';
	}

	/**
	 * Get an existing donation cookie or create a new one.
	 * The cookie value points to a transient value in the database that
	 * contains an unfinished donation.
	 */
	public function init_cookie()
	{
		$this->_cookie = 'braintree_donation_' . COOKIEHASH;
		
		if ( bfwcd_is_donation_page() ) {
			if ( isset( $_COOKIE [ $this->_cookie ] ) ) {
				$this->donation = get_transient( $_COOKIE [ $this->_cookie ] );
			} else {
				setcookie( $this->_cookie, 'braintree_donation_' . md5( uniqid( '', true ) ), 0, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl() ? true : false );
			}
		}
	}

	public function get_enabled_fields()
	{
		$fields = array ();
		$enabled_fields = bt_manager()->get_option( 'donation_fields' );
		$donation_fields = $this->get_donation_fields();
		if ( ! empty( $enabled_fields ) ) {
			foreach ( $enabled_fields as $k ) {
				$fields [ $k ] = $donation_fields [ $k ];
			}
		}
		if ( isset( $donation_fields [ 'bfwcd_recurring_start_date' ] ) ) {
			$fields [ 'bfwcd_recurring_start_date' ] = $donation_fields [ 'bfwcd_recurring_start_date' ];
		}
		return $fields;
	}

	public function get_amount_input_field( $attrs )
	{
		if ( empty( $attrs ) ) {
			$input = array (
					'type' => 'text', 
					'label' => __( 'Donation Amount', 'braintree-payments' ), 
					'placeholder' => braintree_get_currency_symbol( bt_manager()->get_option( 'donation_currency' ) ) . ' ' . __( 'Amount', 'braintree-payments' ), 
					'value' => '', 
					'class' => '', 
					'required' => true, 
					'attributes' => array () 
			);
		} else {
			$input = array (
					'type' => 'select', 
					'label' => __( 'Donation Amount', 'braintree-payments' ), 
					'placeholder' => __( 'Amount', 'braintree-payments' ), 
					'value' => '', 
					'class' => 'bfwc-select2', 
					'required' => 'bfwc-select2', 
					'attributes' => array () 
			);
			foreach ( $attrs as $i => $amount ) {
				$currency_symbol = braintree_get_currency_symbol( bt_manager()->get_option( 'donation_currency' ) );
				$input [ 'options' ] [ $amount ] = sprintf( __( '%s %s' ), $currency_symbol, $amount );
			}
		}
		return $input;
	}

	public function get_recurring_plan_field( $attrs )
	{
		global $bfwcd_plans;
		if ( $this->is_single_recurring ) {
			$input = $this->get_amount_input_field( array () );
			$input [ 'label' ] = __( 'Monthly Donation', 'braintree-payments' );
		} else {
			$input = array (
					'type' => 'select', 
					'label' => __( 'Recurring Donation', 'braintree-payments' ), 
					'placeholder' => __( 'Donation', 'braintree-payments' ), 
					'value' => '', 
					'class' => 'bfwc-select2', 
					'required' => true, 
					'attributes' => array () 
			);
			foreach ( $bfwcd_plans as $plan ) {
				if ( in_array( $plan->id, $attrs ) ) {
					$input [ 'options' ] [ $plan->id ] = $plan->description;
				}
			}
		}
		return $input;
	}

	public function get_braintree_plans()
	{
		global $bfwcd_plans;
		try {
			$bfwcd_plans = Braintree_Plan::all();
		} catch( Exception $e ) {
			bt_manager()->error( sprintf( __( 'There was exception thrown while retrieving the Braintree Plans required for a recurring donation.', 'braintree-payments' ) ) );
		}
		return $bfwcd_plans;
	}

	public static function get_field_vars()
	{
		$vars = array (
				'states' => array (
						'US' => braintree_get_states() 
				) 
		);
		return apply_filters( 'bfwc_donation_localized_field_vars', $vars );
	}

	/**
	 * Return an array of donation gateways.
	 */
	public function available_gateways()
	{
		$gateways = array ();
		foreach ( $this->available_gateways as $gateway ) {
			if ( $gateway->available() ) {
				if ( $gateway->supports( 'donations' ) ) {
					$gateways [ $gateway->id ] = $gateway;
				}
			}
		}
		return apply_filters( 'bfwc_available_donation_gateways', $gateways, $this );
	}

	public function load_gateways()
	{
		$gateways = apply_filters( 'bfwc_load_donation_gateways', array () );
		foreach ( $gateways as $class ) {
			$gateway = new $class();
			if ( $gateway->available() ) {
				$this->available_gateways [ $gateway->id ] = $gateway;
			}
		}
	}

	public static function recurring_donation_gateways( $gateways, $shortcode )
	{
		if ( bfwcd_is_recurring_donation_page() ) {
			foreach ( $gateways as $id => $gateway ) {
				if ( ! $gateway->supports( 'recurring_donations' ) ) {
					unset( $gateways [ $id ] );
				}
			}
		}
		return $gateways;
	}

	public function enqueue_scripts()
	{
		if ( bfwcd_is_donation_page() ) {
			// scripts
			wp_enqueue_script( 'bfwcd-donation-js' );
			wp_enqueue_script( 'bfwcd-jquery-block' );
			wp_enqueue_script( 'bfwcd-select2' );
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'bfwcd-jquery-ui' );
			// styles
			wp_enqueue_style( 'bfwcd-css' );
			wp_enqueue_style( 'bfwcd-css2' );
			wp_enqueue_style( 'bfwcd-select2-css' );
			if ( bfwcd_custom_form_enabled() ) {
				wp_enqueue_script( 'bfwcd-hosted-internal' );
				wp_enqueue_script( 'bfwcd-custom-form-js' );
				wp_enqueue_style( 'bfwcd-custom-form-css' );
			} else {
				wp_enqueue_script( 'bfwcd-dropin-internal' );
			}
			if ( bfwcd_paypal_enabled() ) {
				// scripts
				wp_enqueue_script( 'bfwcd-paypal-internal' );
				
				// styles
				wp_enqueue_style( 'bfwcd-paypal-button-css' );
			}
			if ( bfwcd_paypal_credit_enabled() ) {
				wp_enqueue_script( 'bfwcd-paypal-credit-internal' );
				wp_enqueue_style( 'bfwcd-paypal-credit-css' );
			}
			$this->localize_scripts();
		}
	}

}
new Braintree_Gateway_Donation_Shortcode();