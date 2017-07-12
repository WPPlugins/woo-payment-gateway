<?php

class Braintree_Gateway_Donation_Merchant_Account_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'donation_settings';
		$this->tab = 'donation-settings';
		$this->title = array (
				'title' => __( 'Merchant Accounts', 'braintree-payments' ), 
				'description' => __( 'Your merchant accounts determine the currency that your donations are settled in. By adding merchant accounts, you will allow your donors the option to select the donation currency.', 'braintree-payments' ) 
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
		add_filter( 'braintree_gateway_validate_donation_production_merchant_account_id', array (
				$this, 
				'validate_merchant_accounts' 
		), 10, 2 );
		add_filter( 'braintree_gateway_validate_donation_sandbox_merchant_account_id', array (
				$this, 
				'validate_merchant_accounts' 
		), 10, 2 );
	}

	public function localize_vars( $vars )
	{
		$vars [ 'donation_merchant_accounts' ] = array (
				'sandbox' => array (
						'text' => __( 'Merchant Account (%s)', 'braintree-payments' ), 
						'html' => '<div class="col s8"><label>' . __( 'Sandbox Merchant Account (%currency%)', 'braintree-payments' ) . '</label><input type="text" name="%name%" value="" placeholder="' . __( 'Enter Merchant Account', 'braintree-payments' ) . '"/><i class="material-icons trash-merchant-account">delete</i></div>' 
				), 
				'production' => array (
						'text' => __( 'Merchant Account (%s)', 'braintree-payments' ), 
						'html' => '<div class="col s8"><label>' . __( 'Production Merchant Account (%currency%)', 'braintree-payments' ) . '</label><input type="text" name="%name%" value="" placeholder="' . __( 'Enter Merchant Account', 'braintree-payments' ) . '"/><i class="material-icons trash-merchant-account">delete</i></div>' 
				), 
				'messages' => array (
						'no_currency' => __( 'Please select a currency from the dropdown so you can add your merchant account.', 'braintree-payments' ), 
						'currency_exists' => __( 'You already have a merchant account configured for currency %currency%', 'braintree-payments' ) 
				) 
		);
		return $vars;
	}

	public function settings()
	{
		return array (
				'donation_production_merchant_account_id' => array (
						'type' => 'custom', 
						'title' => __( 'Production Merchant Accounts', 'braintree-payments' ), 
						'default' => '', 
						'function' => array (
								$this, 
								'output_merchant_account_settings' 
						), 
						'attributes' => array (
								'environment' => 'production', 
								'data-setting' => $this->get_field_key_name( 'donation_production_merchant_account_id' ) . '[%currency%]' 
						), 
						'label' => __( 'Production Merchant Account (%s)', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'Your merchant accounts determine the settlement currency of your donations. This settings is optional but if you want donors to have additional currency options than we suggest this option.', 'braintree-payments' ) 
				), 
				'donation_sandbox_merchant_account_id' => array (
						'type' => 'custom', 
						'title' => __( 'Sandbox Merchant Accounts', 'braintree-payments' ), 
						'default' => '', 
						'function' => array (
								$this, 
								'output_merchant_account_settings' 
						), 
						'attributes' => array (
								'environment' => 'sandbox', 
								'data-setting' => $this->get_field_key_name( 'donation_sandbox_merchant_account_id' ) . '[%currency%]' 
						), 
						'label' => __( 'Sandbox Merchant Account (%s)', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'Your merchant accounts determine the settlement currency of your donations. This settings is optional but if you want donors to have additional currency options than we suggest this option.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function output_merchant_account_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'views/merchant-accounts.php';
	}

	public function validate_merchant_accounts( $value, $key )
	{
		if ( empty( $value ) ) { // array of merchant accounts is empty so exit.
			return $value;
		}
		$environment = strpos( $key, 'production' ) ? 'production' : 'sandbox';
		if ( $environment === 'production' ) {
			bt_manager()->add_admin_notice( 'error', __( 'You cannot add production merchant accounts while your license status is not active.', 'braintree-payments' ) );
			return false;
		}
		foreach ( $value as $currency => $account ) {
			if ( empty( $account ) ) {
				bt_manager()->add_admin_notice( 'error', __( 'Values for merchant accounts cannot be blank', 'braintree-payments' ) );
				return false;
			}
			switch( $environment) {
				case 'production' :
					Braintree_Configuration::environment( 'production' );
					Braintree_Configuration::merchantId( bt_manager()->get_option( 'production_merchant_id' ) );
					Braintree_Configuration::privateKey( bt_manager()->get_option( 'production_private_key' ) );
					Braintree_Configuration::publicKey( bt_manager()->get_option( 'production_public_key' ) );
					break;
				case 'sandbox' :
					Braintree_Configuration::environment( 'sandbox' );
					Braintree_Configuration::merchantId( bt_manager()->get_option( 'sandbox_merchant_id' ) );
					Braintree_Configuration::privateKey( bt_manager()->get_option( 'sandbox_private_key' ) );
					Braintree_Configuration::publicKey( bt_manager()->get_option( 'sandbox_public_key' ) );
					break;
			}
			try {
				$merchant_account = Braintree_MerchantAccount::find( $account );
			} catch( Braintree\Exception\NotFound $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Merchant account %s could not be found in your %s environment.', 'braintree-payments' ), $account, $environment ) );
				return false;
			} catch( \Braintree\Exception $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error retrieving merchant account %s. Reason: %s', 'braintree-payments' ), $account, $e->getMessage() ) );
				return false;
			}
		}
		return $value;
	}
}