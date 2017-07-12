<?php

class Braintree_Gateway_Donation_Modal_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->tab = 'donation-settings';
		$this->id = 'donation_settings';
		$this->title = array ( 
				'title' => __( 'Modal Options', 'braintree-payments' ), 
				'description' => __( 'These options pertain to the donation modal popup.', 'braintree-payments' ) 
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
				'donation_modal_button_text' => array ( 
						'type' => 'text', 
						'value' => 'Some Text', 
						'default' => __( 'Donate', 'braintree-payments' ), 
						'title' => __( 'Modal Button Text', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'If you have enabled the modal donation form, you can control the text that is displayed for the button that when clicked displays
						the modal donation form.', 'braintree-payments' ) 
				), 
				'donation_modal_button_background' => array ( 
						'title' => __( 'Background Color', 'braintree-payments' ), 
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#61D395', 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the the background color of the modal donation button by selecting a color from the color picker.', 'braintree-payments' ) 
				), 
				'donation_modal_button_border' => array ( 
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#61D395', 
						'title' => __( 'Border Color', 'braintree-payments' ), 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the border color of the modal donation button by selecting a color from the color picker.', 'braintree-payments' ) 
				), 
				'donation_modal_button_text_color' => array ( 
						'type' => 'text', 
						'value' => '#61D395', 
						'default' => '#ffffff', 
						'title' => __( 'Text Color', 'braintree-payments' ), 
						'class' => 'color-option', 
						'tool_tip' => true, 
						'description' => __( 'You can customize the color of the text with this option.', 'braintree-payments' ) 
				) 
		);
	}

	public function output_settings( $key, $data )
	{
		$field_key = $this->get_field_key_name( $key );
		$data = wp_parse_args( $data, $this->get_default_custom_html_args() );
		include bt_manager()->plugin_admin_path() . 'html-helpers/settings-modal.php';
	}
}