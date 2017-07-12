<?php 
/**
 * @version 2.6.9
 */
$methods = bwc_get_user_paypal_payment_methods( wp_get_current_user()->ID );

bwc_get_template( 'myaccount/wcs/paypal-payment-methods.php', array ( 
		'methods' => $methods 
) );