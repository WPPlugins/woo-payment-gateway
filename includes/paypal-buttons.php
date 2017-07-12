<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

return apply_filters( 'braintree_paypal_design_options', array (
		0 => array (
				'name' => __( 'Pill Button', 'braintree-payments' ), 
				'css' => bt_manager()->plugin_assets_path() . 'css/paypal/paypal-button-pill.css', 
				'html' => 'paypal/paypal-button-pill.php' 
		), 
		1 => array (
				'name' => __( 'Standard Button', 'braintree-payments' ), 
				'css' => bt_manager()->plugin_assets_path() . 'css/paypal/paypal-button-standard.css', 
				'html' => 'paypal/paypal-button-standard.php' 
		), 
		2 => array (
				'name' => __( 'Yellow Button', 'braintree-payments' ), 
				'css' => bt_manager()->plugin_assets_path() . 'css/paypal/paypal-button-yellow.css', 
				'html' => 'paypal/paypal-button-yellow.php' 
		), 
		3 => array (
				'name' => __( 'Logo Button', 'braintree-payments' ), 
				'css' => bt_manager()->plugin_assets_path() . 'css/paypal/paypal-button-logo_h.css', 
				'html' => 'paypal/paypal-button-logo_h.php' 
		)
) );