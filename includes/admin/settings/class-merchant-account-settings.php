<?php

class Braintree_Gateway_Merchant_Account_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'checkout_settings';
		$this->tab = 'checkout-settings';
		$this->title = array (
				'title' => __( 'Merchant Accounts', 'braintree-payments' ), 
				'description' => __( 'Your merchant accounts determine the currency that your transactions are settled in. 
                       If you use a currency switcher, then the merchant account will be chose based on the order\'s currency. By default, all of your merchant accounts are loaded when you save your API keys.', 'braintree-payments' ) 
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
		add_filter( 'braintree_gateway_validate_woocommerce_braintree_production_merchant_account_id', array (
				$this, 
				'validate_merchant_accounts' 
		), 10, 2 );
		add_filter( 'braintree_gateway_validate_woocommerce_braintree_sandbox_merchant_account_id', array (
				$this, 
				'validate_merchant_accounts' 
		), 10, 2 );
	}

	public function localize_vars( $vars )
	{
		$envs = array (
				'production', 
				'sandbox' 
		);
		$currencies = function_exists( 'get_woocommerce_currencies' ) ? get_woocommerce_currencies() : braintree_get_currencies();
		foreach ( $envs as $env ) {
			
			$vars [ 'templates' ] [ $env ] [ 'merchant_accounts' ] = array (
					'container' => bfwc_admin_backbone_template( 'merchant-container', true, array (
							'key' => $this->get_field_key_name( "woocommerce_braintree_{$env}_merchant_account_id" ), 
							'env' => $env, 
							'button_text' => $env === 'production' ? __( 'Add Production Merchant Account', 'braintree-payments' ) : __( 'Add Sandbox Merchant Account', 'braintree-payments' ), 
							'title1' => $env === 'production' ? __( 'Production Merchant Account ID', 'braintree-payments' ) : __( 'Sandbox Merchant Account ID', 'braintree-payments' ) 
					) ), 
					'merchant_account' => bfwc_admin_backbone_template( 'merchant-account', true, array (
							'key' => $this->get_field_key_name( "woocommerce_braintree_{$env}_merchant_account_id" ), 
							'env' => $env, 
							'currencies' => $currencies 
					) ) 
			);
			
			$merchant_accounts = $this->get_option( "woocommerce_braintree_{$env}_merchant_account_id" );
			$merchant_accounts = $merchant_accounts ? $merchant_accounts : array ();
			foreach ( $merchant_accounts as $currency => $id ) {
				$vars [ 'merchant_accounts' ] [ $env ] [] = array (
						'currency' => $currency, 
						'id' => $id 
				);
			}
		}
		return $vars;
	}

	public function save()
	{
		$envs = array (
				'production', 
				'sandbox' 
		);
		foreach ( $envs as $env ) {
			$key = "woocommerce_braintree_{$env}_merchant_account_id";
			$merchants = $this->get_field_value( $key, array () );
			$new_merchants = array ();
			foreach ( $merchants as $merchant ) {
				$new_merchants [ $merchant [ 'currency' ] ] = $merchant [ 'merchant_account' ];
			}
			
			$new_merchants = apply_filters( 'braintree_gateway_validate_' . $key, $new_merchants, $key );
			
			if ( $new_merchants !== false ) {
				$this->set_setting( $key, $new_merchants );
			}
			$new_merchants = array ();
		}
		parent::save();
	}

	public function settings()
	{
		return array (
				/* 'woocommerce_braintree_production_merchant_account_id' => array (
						'type' => 'custom', 
						'title' => __( 'Production Merchant Accounts', 'braintree-payments' ), 
						'save' => true, 
						'default' => array (), 
						'function' => array (
								$this, 
								'output_merchant_account_settings' 
						), 
						'attributes' => array (
								'environment' => 'production', 
								'data-setting' => $this->get_field_key_name( 'woocommerce_braintree_production_merchant_account_id' ) . '[%currency%]' 
						), 
						'label' => __( 'Production Merchant Account (%s)', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'Your merchant accounts determine the settlement currency of your transactions. If using a currency switcher, the merchant account will be determined based on the order\'s currency.', 'braintree-payments' ) 
				), 
				'woocommerce_braintree_sandbox_merchant_account_id' => array (
						'type' => 'custom', 
						'title' => __( 'Sandbox Merchant Accounts', 'braintree-payments' ), 
						'save' => true, 
						'default' => array (), 
						'function' => array (
								$this, 
								'output_merchant_account_settings' 
						), 
						'attributes' => array (
								'environment' => 'sandbox', 
								'data-setting' => $this->get_field_key_name( 'woocommerce_braintree_sandbox_merchant_account_id' ) . '[%currency%]' 
						), 
						'label' => __( 'Sandbox Merchant Account (%s)', 'braintree-payments' ), 
						'tool_tip' => true, 
						'description' => __( 'Your merchant accounts determine the settlement currency of your transactions. If using a currency switcher, the merchant account will be determined based on the order\'s currency.', 'braintree-payments' ) 
				) */ 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		$data [ 'extra_html' ] = '<div id="bfwc-sandbox-merchant-container" class="row"></div>';
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function validate_merchant_accounts( $value, $key )
	{
		if ( empty( $value ) ) { // array of merchant accounts is empty so exit.
			return $value;
		}
		$environment = strpos( $key, 'production' ) ? 'production' : 'sandbox';
		if ( $environment === 'production' ) {
			if ( bt_manager()->get_license_status() !== 'active' ) { // If license
			                                                         // is
			                                                         // inactive
			                                                         // or
			                                                         // expired,
			                                                         // don't
			                                                         // save.
				bt_manager()->add_admin_notice( 'error', __( 'You cannot add production merchant accounts while your license status is not active.', 'braintree-payments' ) );
				return false;
			}
		}
		// compare values, maybe an API call isn't needed if the vales haven't changed.
		$old_value = bt_manager()->get_option( $key );
		
		if ( $old_value == $value ) {
			return $value;
		}
		if ( in_array( "", $value ) ) {
			bt_manager()->add_admin_notice( 'error', __( 'Merchant accounts values cannot be empty.', 'braintre-payments' ) );
			return false;
		}
		$new_value = null;
		foreach ( $value as $currency => $account ) {
			if ( empty( $account ) ) {
				bt_manager()->add_admin_notice( 'error', __( 'Values for merchant accounts cannot be blank', 'braintree-payments' ) );
				return false;
			}
			Braintree_Configuration::environment( $environment );
			Braintree_Configuration::merchantId( bt_manager()->get_option( "{$environment}_merchant_id" ) );
			Braintree_Configuration::privateKey( bt_manager()->get_option( "{$environment}_private_key" ) );
			Braintree_Configuration::publicKey( bt_manager()->get_option( "{$environment}_public_key" ) );
			try {
				$merchant_account = Braintree_MerchantAccount::find( $account );
				$iso_code = @$merchant_account->currencyIsoCode ? $merchant_account->currencyIsoCode : $currency;
				$new_value [ $iso_code ] = $account;
			} catch( Braintree\Exception\NotFound $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Merchant account %s could not be found in your %s environment.', 'braintree-payments' ), $account, $environment ) );
				return false;
			} catch( \Braintree\Exception $e ) {
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'There was an error retrieving merchant account %s. Reason: %s', 'braintree-payments' ), $account, $e->getMessage() ) );
				return false;
			}
		}
		$value = $new_value ? $new_value : $value;
		bt_manager()->initialize_braintree();
		return $value;
	}
}