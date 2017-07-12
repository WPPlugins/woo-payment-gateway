<?php 
/**
 * @version 2.6.9
 */
?>
<div class="braintree-payment-gateway">
	<?php
	if ( ! bwc_payment_icons_outside() ) {
		bwc_get_template( 'checkout/payment-method-icons.php', array (
				'icons_inside' => true 
		) );
	}
	
	if ( bwc_is_custom_form() ) {
		bwc_get_template( 'custom-form.php', array (
				'has_methods' => $has_methods, 
				'gateway' => $gateway, 
				'custom_form' => $custom_form, 
				'loader' => $loader, 
				'show_payment_icons' => false,
				'icons_inside' => false
		) );
	} else {
		bwc_get_template( 'dropin-form.php', array (
				'has_methods' => $has_methods, 
				'gateway' => $gateway, 
				'show_payment_icons' => false,
				'icons_inside' => false
		) );
	}
	
	if ( $has_methods ) {
		bwc_get_template( 'braintree-payment-methods.php', array (
				'methods' => $methods, 
				'default_method' => $default_method, 
				'token_id' => $gateway::$token_id, 
				'button_text' => __( 'New Card', 'braintree-payments' ) 
		) );
	}
	?>	
</div>