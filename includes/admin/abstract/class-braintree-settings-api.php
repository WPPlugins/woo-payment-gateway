<?php

/**
 * Abstract class for rendering plugin settings and saving settings.
 * @author Payment Plugins
 * @copyright Payment Plugins 2016
 *
 */
abstract class Braintree_Gateway_Settings_API extends Braintree_Gateway_Page_API
{
	
	/**
	 * Array of configured settings.
	 *
	 * @var array
	 */
	public $settings = array ();
	
	/**
	 * The settings associated with this settings page.
	 *
	 * @var array
	 */
	private $default_settings;
	
	public $label = '';

	public function __construct()
	{
		add_filter( 'braintree_gateway_default_settings', array (
				$this, 
				'get_default_settings' 
		) );
		if ( $this->tab ) {
			add_action( "braintree_gateway_{$this->tab}_save_settings", array (
					$this, 
					'save' 
			) );
			add_action( "output_braintree_{$this->tab}_settings_page", array (
					$this, 
					'generate_settings_html' 
			) );
			add_filter( 'braintree_gateway_settings_tabs', array (
					$this, 
					'add_settings_tab' 
			) );
		}
		parent::__construct();
	}

	public function add_settings_tab( $tabs )
	{
		$tabs [] = array (
				'id' => $this->tab, 
				'label' => $this->label, 
				'page' => $this->page 
		);
		return $tabs;
	}

	public function get_default_settings( $default_settings = array() )
	{
		if ( $this->default_settings == null ) {
			$this->default_settings = $this->settings();
		}
		return array_merge( $this->default_settings, $default_settings );
	}

	/**
	 * Given an option name, return the option value associated with the option
	 * name.
	 *
	 * @param unknown $key        	
	 * @return mixed
	 */
	public function get_option( $key )
	{
		return bt_manager()->get_option( $key );
	}

	public function get_field_key_name( $key )
	{
		return "braintree_{$this->id}_{$key}";
	}

	public function generate_text_html( $key, $data )
	{
		$defaults = $this->get_default_text_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/text-html.php';
		return ob_get_clean();
	}

	public function generate_password_html( $key, $data )
	{
		$defaults = $this->get_default_text_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/password-html.php';
		return ob_get_clean();
	}

	public function generate_checkbox_html( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$defaults = $this->get_default_checkbox_html_args();
		$data = wp_parse_args( $data, $defaults );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/checkbox-html.php';
		return ob_get_clean();
	}

	public function generate_radio_html( $key, $data )
	{
		$defaults = $this->get_default_radio_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/radio-html.php';
		return ob_get_clean();
	}

	public function generate_textarea_html( $key, $data )
	{
		$data = wp_parse_args( $data, $this->get_default_radio_html_args() );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/textarea-html.php';
		return ob_get_clean();
	}

	/**
	 * display html for the select html object.
	 *
	 * @param unknown $option        	
	 */
	public function generate_select_html( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		
		$data = wp_parse_args( $data, $this->get_default_select_html_args() );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/select-html.php';
		return ob_get_clean();
	}

	/**
	 *
	 * @param string $key        	
	 * @param array $data        	
	 * @return string
	 */
	public function generate_multiselect_html( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$defaults = $this->get_default_multiselect_html_args();
		$data = wp_parse_args( $data, $defaults );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/multi-select.php';
		return ob_get_clean();
	}

	/**
	 * Generate custom html for the settings.
	 *
	 * @param unknown $option        	
	 */
	public function generate_custom_html( $key, $data = null )
	{
		$defaults = $this->get_default_custom_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/custom-html.php';
		return ob_get_clean();
	}

	public function generate_array_html( $key, $data = null )
	{
		$defaults = $this->get_default_custom_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/array-html.php';
		return ob_get_clean();
	}

	public function generate_button_html( $key, $data = null )
	{
		$defaults = $this->get_default_button_html_args();
		$data = wp_parse_args( $data, $defaults );
		$field_key = $this->get_field_key_name( $key );
		ob_start();
		include bt_manager()->plugin_admin_path() . 'html-helpers/button-html.php';
		return ob_get_clean();
	}

	/**
	 * Generate html for an option tool tip;
	 *
	 * @param unknown $data        	
	 * @return string
	 */
	public function get_tooltip_html( $data )
	{
		$html = '';
		if ( $data [ 'tool_tip' ] ) {
			$html .= '<i class="material-icons tooltipped right" data-position="right" data-delay="50" data-tooltip="' . __( $data [ 'description' ], 'braintree-payments' ) . '">help</i> ';
		}
		return $html;
	}

	public function display_link( $key, $data )
	{
		$html = '';
		if ( ! empty( $data [ 'link' ] ) ) {
			$html = '<div class="row option-link"><div class="col s12 m12 l12"><a target="_blank" href="' . $data [ 'link' ] [ 'url' ] . '">' . __( $data [ 'link' ] [ 'text' ], 'braintree-payments' ) . '</a></div></div>';
		}
		return $html;
	}

	/**
	 * Generates html for a modal helper.
	 *
	 * @param unknown $key        	
	 * @param unknown $data        	
	 */
	public function generate_helper_modal( $key, $data )
	{
		if ( $data [ 'helper' ] [ 'enabled' ] ) {
			$field_key = $this->get_field_key_name( $key );
			include bt_manager()->plugin_admin_path() . 'html-helpers/modal-helper.php';
		}
	}

	public function cleanse_text_field_data( $value )
	{
		return trim( sanitize_text_field( $value ) );
	}

	public function cleanse_checkbox_field_data( $value )
	{
		return sanitize_text_field( $value );
	}

	public function cleanse_select_field_data( $value )
	{
		return sanitize_text_field( $value );
	}

	public function cleanse_textarea_field_data( $value )
	{
		return trim( stripslashes( $value ) );
	}

	public function cleanse_radio_field_data( $value )
	{
		return sanitize_text_field( $value );
	}

	public function get_default_button_html_args()
	{
		return apply_filters( 'braintree_gateway_default_button_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'label' => '', 
				'pre_loader' => false, 
				'value' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'default' => '', 
				'attributes' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_select_html_args()
	{
		return apply_filters( 'braintree_gateway_default_select_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'options' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_multiselect_html_args()
	{
		return apply_filters( 'braintree_gateway_default_multiselect_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'options' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_textarea_html_args()
	{
		return apply_filters( 'braintree_gateway_default_textarea_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'helper' => array (
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_checkbox_html_args()
	{
		return apply_filters( 'braintree_gateway_default_input_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => 'filled-in', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'default' => 'yes', 
				'value' => 'yes', 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_radio_html_args()
	{
		return apply_filters( 'braintree_gateway_default_radio_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'default' => '', 
				'attributes' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_text_html_args()
	{
		return apply_filters( 'braintree_gateway_default_text_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'placeholder' => '', 
				'type' => 'text', 
				'tool_tip' => false, 
				'description' => '', 
				'default' => '', 
				'maxlength' => '', 
				'attributes' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_title_html_args()
	{
		return apply_filters( 'braintree_gateway_default_text_args', array (
				'title' => '', 
				'disabled' => false, 
				'class' => '', 
				'css' => '', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	public function get_default_custom_html_args()
	{
		return apply_filters( 'braintree_gateway_default_custom_args', array (
				'title' => '', 
				'function' => '', 
				'class' => '', 
				'save' => false, 
				'default' => '', 
				'tool_tip' => false, 
				'description' => '', 
				'attributes' => array (), 
				'helper' => array (
						'title' => '', 
						'enabled' => false, 
						'type' => '', 
						'url' => '', 
						'description' => '' 
				) 
		) );
	}

	/**
	 * Abstract method that is to be implemented by settings classes.
	 * Each class that implements
	 * the Stripe_Settings_API must include the settings options in this method.
	 * These settings are reqiured
	 * for the plugin to function.
	 */
	public abstract function settings();

	/**
	 * Save the settings for the page.
	 */
	public function save()
	{
		foreach ( $this->settings() as $key => $setting ) {
			if ( method_exists( $this, "get_default_{$setting['type']}_html_args" ) ) {
				$setting = wp_parse_args( $setting, $this->{"get_default_{$setting['type']}_html_args"}() );
			}
			if ( $setting [ 'type' ] === 'button' || ( $setting [ 'type' ] === 'custom' && ! $setting [ 'save' ] ) ) {
				continue;
			}
			$value = $this->get_field_value( $key );
			
			if ( method_exists( $this, "cleanse_{$setting['type']}_field_data" ) ) {
				$value = $this->{"cleanse_{$setting['type']}_field_data"}( $value );
			}
			
			$value = apply_filters( 'braintree_gateway_validate_' . $key, $value, $key );
			if ( $value !== false ) {
				$this->set_setting( $key, $value );
			}
		}
		bt_manager()->update_settings();
	}

	public function set_setting( $key, $value )
	{
		bt_manager()->set_option( $key, $value );
	}

	public function maybe_test_connection()
	{
		if ( isset( $_POST [ 'braintree_api_settings_production_connection_test' ] ) ) {
			bt_manager()->connection_test( 'production' );
		}
		if ( isset( $_POST [ 'braintree_api_settings_sandbox_connection_test' ] ) ) {
			bt_manager()->connection_test( 'sandbox' );
		}
	}

	/**
	 * Get the field value from the $_POST data.
	 *
	 * @param string $key        	
	 * @return string|unknown
	 */
	public function get_field_value( $key, $default = '' )
	{
		$key = $this->get_field_key_name( $key );
		return isset( $_POST [ $key ] ) ? $_POST [ $key ] : $default;
	}

	/**
	 * Generate the html for the gateway settings.
	 */
	public function generate_settings_html( $echo = false )
	{
		$html = '';
		foreach ( $this->settings() as $k => $v ) {
			$html .= $this->{"generate_{$v['type']}_html"}( $k, $v );
		}
		if ( $echo ) {
			echo $html;
		} else {
			return $html;
		}
	}

	/**
	 * Return true if the value is valid json.
	 *
	 * @param string $key        	
	 * @param string $value        	
	 */
	public function validate_json( $value, $key )
	{
		if ( empty( $value ) ) {
			return;
		}
		$array = json_decode( $value, true );
		if ( ! $array ) {
			bt_manager()->add_admin_notice( 'error', sprintf( __( 'Field %s contains invalid json. Please verify your entries.', 'braintree-payments' ), $key ) );
			$value = false;
		}
		return $value;
	}

	public function display_help_button( $page, $data )
	{
		if ( $page->tab === $this->tab ) {
			echo '<div class="col s12 m6 l4"><a href="#" class="bfwc-help-widget waves-effect waves-light btn braintree-grey white-text">' . __( 'Need Help?', 'braintree-payments' ) . '</a></div>';
		
		}
	}

	public function dispay_license_button( $page, $data )
	{
		if ( $page->tab === $this->tab ) {
			if ( bt_manager()->get_license_status() !== 'active' ) {
				echo '<div class="col s12 m6 l4"><a class="waves-effect waves-light btn braintree-grey white-text" target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/">' . __( 'Purchase License', 'braintree-payments' ) . '</a></div>';
			}
		}
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
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}
}
