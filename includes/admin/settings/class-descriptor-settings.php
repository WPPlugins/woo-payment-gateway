<?php

class Braintree_Gateway_DynamicDescriptor_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->tab = 'checkout-settings';
		$this->id = 'checkout_settings';
		$this->title = array ( 
				'title' => __( 'Dynamic Descriptor Settings', 'braintree-payments' ), 
				'description' => __( 'Your dynamic descriptor settings affect what appears on your customer\'s credit card statement.', 'braintree-payments' ) 
		);
		add_action( "braintree_gateway_{$this->tab}_save_settings", array ( 
				$this, 
				'save' 
		) );
		add_filter( 'braintree_gateway_default_settings', array ( 
				$this, 
				'get_default_settings' 
		) );
		$this->add_validate_filters();
	}

	/**
	 *
	 * @param string $key 
	 * @param array $data 
	 */
	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}

	public function add_validate_filters()
	{
		$validate = array ( 
				'dynamic_descriptor_name' => 'validate_descriptor_name', 
				'dynamic_descriptor_phone' => 'validate_descriptor_phone', 
				'dynamic_descriptor_url' => 'validate_descriptor_url' 
		);
		if ( $this->get_field_value( 'dynamic_descriptors' ) === 'yes' ) {
			foreach ( $validate as $k => $method ) {
				add_filter( "braintree_gateway_validate_{$k}", array ( 
						$this, 
						$method 
				), 10, 2 );
			}
		}
	}

	public function settings()
	{
		return array ( 
				'dynamic_descriptors' => array ( 
						'type' => 'checkbox', 
						'title' => __( 'Dynamic Descriptors Enabled', 'braintree-payments' ), 
						'default' => '', 
						'value' => 'yes', 
						'class' => 'filled-in', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, your descriptors will be added to the transaction during WooCommerce order processing.', 'braintree-payments' ) 
				), 
				
				'dynamic_descriptor_name' => array ( 
						'type' => 'text', 
						'title' => __( 'Descriptor Name', 'braintree-payments' ), 
						'default' => '', 
						'value' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value in the business name field of a customer\'s statement. Company name/DBA section must be 
								either 3, 7 or 12 characters and the product descriptor can be up to 18, 14, or 9 characters respectively (with an * in between for a total descriptor name of 22 characters).', 'braintree-payments' ) 
				), 
				'dynamic_descriptor_phone' => array ( 
						'type' => 'text', 
						'title' => __( 'Phone', 'braintree-payments' ), 
						'value' => '', 
						'default' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value in the phone number field of a customer\'s statement. Phone must be 10-14 characters and can 
								only contain numbers, dashes, parentheses and periods.', 'braintree-payments' ) 
				), 
				
				'dynamic_descriptor_url' => array ( 
						'type' => 'text', 
						'title' => __( 'URL', 'braintree-payments' ), 
						'maxlength' => 13, 
						'value' => '', 
						'default' => '', 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'The value in the URL/web address field of a customer\'s statement. The URL must be 13 characters or shorter.', 'braintree-payments' ) 
				) 
		);
	}

	public function validate_descriptor_name( $value, $key )
	{
		if ( ! empty( $value ) ) {
			$length = strlen( $value );
			$messages = array ();
			if ( $number = preg_match_all( '/[^\w.+\-*\s]+/', $value, $matches ) ) { // look
			                                                                         // for
			                                                                         // illegal
			                                                                         // characters
				$chars = '';
				foreach ( $matches[ 0 ] as $match ) {
					$chars .= $match;
				}
				bt_manager()->add_admin_notice( 'error', sprintf( __( 'Dynamic Descriptor Error - The following illegal characters were used for your descriptor name: %s', 'braintree-payments' ), $chars ) );
				return false;
			}
			if ( ! preg_match( '/^[^\s].{2}\*|^[^\s].{6}\*|^[^\s].{11}\*/', $value ) ) { // look
			                                                                             // for
			                                                                             // incorrect
			                                                                             // company
			                                                                             // name
			                                                                             // length.
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an incorrect company name length. Valid values are 3, 7, and 12 characters long' ), 'braintree-payments' );
				return false;
			}
			if ( $length > 22 ) {
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - The descriptor length cannot be greater than 22 characters.', 'braintree-payments' ) );
				return false;
			}
			if ( $length < 22 ) { // add spaces to make length 22 characters.
				$diff = 22 - $length;
				for($i = 0; $i < $diff; $i ++) {
					$value .= ' ';
				}
			}
		}
		return $value;
	}

	public function validate_descriptor_phone( $value, $key )
	{
		if ( ! empty( $value ) ) { // validate the $value.
			$value = preg_replace( '/\s+/', '', $value ); // Get rid of all
			                                              // white space as
			                                              // it's not allowed
			                                              // by Braintree.
			$messages = array ();
			
			if ( preg_match( '/[^\d(\).\-]+/', $value ) ) { // 10-14 characters,
			                                                // only digits,
			                                                // ().-\s
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an invalid phone number. Please click the help link for examples on valid values', 'braintree-payments' ) );
				$value = false;
			}
			if ( strlen( $value ) > 14 ) {
				$value = false;
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - The phone number can have a maximum of 14 characters.', 'braintree-payments' ) );
			}
			if ( preg_match_all( '/[\d]+/', $value, $matches ) ) { // make sure
			                                                       // there are
			                                                       // 10 digits.
				$length = 0;
				foreach ( $matches[ 0 ] as $match ) {
					$length = $length + strlen( $match );
				}
				if ( $length != 10 ) {
					$value = false;
					bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an invalid phone number. The phone number must be exactly 10 digits for US based numbers.', 'braintree-payments' ) );
					$valid = false;
				}
			} else {
				$valid = false;
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an invalid phone number. The phone number must be exactly 10 digits for US based numbers.', 'braintree-payments' ) );
			}
		}
		return $value;
	}

	public function validate_descriptor_url( $value, $key )
	{
		if ( ! empty( $value ) ) {
			$messages = array ();
			$value = preg_replace( '/\s/', '', $value ); // replace any white
			                                             // space.
			if ( preg_match( '/[^\w.-_~:]+/', $value ) ) {
				$value = false;
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an invalid url. The URL can contain up to 13 characters.', 'braintree-payments' ) );
			}
			if ( strlen( $value ) > 13 ) {
				$value = false;
				bt_manager()->add_admin_notice( 'error', __( 'Dynamic Descriptor Error - You have entered an invalid url. The URL can contain up to 13 characters.', 'braintree-payments' ) );
			}
		}
		return $value;
	}
}