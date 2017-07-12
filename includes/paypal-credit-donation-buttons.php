<?php
return apply_filters( 'bfwc_paypal_credit_buttons', array (
		0 => array (
				'name' => __( 'Credit Pill Small', 'braintree-payments' ), 
				'css' => '', 
				'html' => 'donations/paypal/paypal-button-credit-pill-small.php' 
		), 
		1 => array (
				'name' => __( 'Credit Pill Medium', 'braintree-payments' ), 
				'css' => '', 
				'html' => 'donations/paypal/paypal-button-credit-pill-medium.php' 
		), 
		2 => array (
				'name' => __( 'Credit Rectangle Small', 'braintree-payments' ), 
				'css' => '', 
				'html' => 'donations/paypal/paypal-button-credit-rect-small.php' 
		), 
		3 => array (
				'name' => __( 'Credit Rectangle Medium', 'braintree-payments' ), 
				'css' => '', 
				'html' => 'donations/paypal/paypal-button-credit-rect-medium.php' 
		) 
) );