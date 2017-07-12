<?php 
/**
 * @version 2.6.9
 */
$methods = bwc_get_user_applepay_payment_methods ( wp_get_current_user ()->ID );
$has_methods = ( bool ) $methods;

$default_method = $has_methods ? bwc_get_default_method ( $methods ) : false;
?>
<div class="braintree-applepay-container braintree-payment-gateway"
	id="braintree-applepay-container">
	
	<?php
	bwc_get_template ( 'applepay.php', array (
			'has_methods' => $has_methods,
			'gateway' => $gateway
	) );
	
	if ($has_methods) :
		bwc_get_template ( 'braintree-payment-methods.php', array (
				'methods' => $methods,
				'default_method' => $default_method,
				'token_id' => $gateway::$token_id,
				'button_text' => __('New Payment Method', 'braintree-payments' )
		) );
	
	endif;
	?>
	
</div>