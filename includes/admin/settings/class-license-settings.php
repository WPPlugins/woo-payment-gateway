<?php

class Braintree_Gateway_License_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->tab = 'license-settings';
		$this->id = 'license_settings';
		$this->label = __( 'License', 'braintree-payments' );
		$this->title = array (
				'title' => __( 'License Settings' ), 
				'description' => __( 'Please download and activate <strong>Braintree For WooCommerce Pro</strong> to enter your license information.
						<p><a class="waves-effect waves-light btn braintree-grey white-text" target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/">' . __( 'Purchase Pro', 'braintree-payments' ) . '</a></p>', 'braintree-payments' ) 
		);
		add_filter( 'braintree_gateway_validate_license', array (
				$this, 
				'validate_license' 
		) );
		add_action( 'bfwc_admin_after_save_button', array (
				$this, 
				'output_buttons' 
		) );
		add_filter( 'bfwc_admin_settings_button_text', array (
				$this, 
				'settings_button_text' 
		), 10, 2 );
		parent::__construct();
	}

	/**
	 * Check if the user is trying to check an expired license first.
	 *
	 * {@inheritDoc}
	 *
	 * @see Stripe_Gateway_Settings_API::save()
	 */
	public function save()
	{
		if ( isset( $_POST [ 'bfwc_refresh_license' ] ) ) {
			$this->refresh_license( $this->get_field_value( 'license' ) );
		} elseif ( isset( $_POST [ 'bfwc_check_license' ] ) ) {
			$this->check_license();
		} else {
			parent::save();
		}
	}

	public function settings()
	{
		return array (
				'license' => array (
						'title' => __( 'License Key', 'braintree-payments' ), 
						'type' => 'text', 
						'default' => '', 
						'value' => '', 
						'attributes' => array (
								'disabled' => 'true' 
						), 
						'description' => __( 'In this field you enter your license key. To activate your license, enter the license key from your Payment Plugins order and save your settings.', 'braintree-payments' ), 
						'tool_tip' => true 
				), 
				'license_status_notice' => array (
						'title' => __( 'License Status', 'braintree-payments' ), 
						'type' => 'custom', 
						'function' => array (
								$this, 
								'output_license_notice' 
						), 
						'default' => '', 
						'class' => '', 
						'description' => __( 'In order for your license status to show as active, you must purchase a license and activate it.', 'braintree-payments' ), 
						'tool_tip' => true 
				) 
		);
	}

	public function output_license_notice( $key, $data )
	{
		$status = 'inactive';
		$class = '';
		switch( $status ) {
			case 'inactive' :
				$class = 'red-text text-lighten-2';
				break;
		}
		echo '<div class="row"><div class="input-field col s12"><h5 class="' . $class . '">' . bfwc_admin_status_name( $status ) . '</h5></div></div>';
	}

	public function validate_license( $license )
	{
		bt_manager()->add_admin_notice( 'error', __( 'In order to activate a license, you must purchase the Pro version of the plugin.', 'braintree-payments' ) );
		return $license;
	}

	public function refresh_license( $license )
	{
	
	}

	public function check_license()
	{
	
	}

	public function output_buttons( $current_tab )
	{
		if ( $this->tab === $current_tab ) {
			ob_start();
			echo '<div class="input-field col s12 m6 l4"><button disabled class="waves-effect waves-light btn teal darken-1" name="bfwc_refresh_license">' . __( 'Refresh License', 'braintree-payments' );
			bfwc_admin_get_template( 'html-helpers/pre-loader.php' );
			echo '</button></div>';
			
			echo '<div class="input-field col s12 m6 l4"><button disabled class="waves-effect waves-light btn light-blue darken-1" name="bfwc_check_license">' . __( 'Check License Status', 'braintree-payments' );
			bfwc_admin_get_template( 'html-helpers/pre-loader.php' );
			echo '</button></div>';
			
			echo ob_get_clean();
		}
	}

	public function settings_button_text( $text, $tab )
	{
		if ( $this->tab === $tab ) {
			$text = __( 'Activate License', 'braintree-payments' );
		}
		return $text;
	}
}
new Braintree_Gateway_License_Settings();