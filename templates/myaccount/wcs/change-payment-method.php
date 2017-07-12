<?php 
/**
 * @version 2.6.9
 */
$methods = bwc_get_user_payment_methods( wp_get_current_user()->ID );
$has_methods = ( bool ) $methods;
if ( $has_methods ) {
	bwc_get_template( 'myaccount/wcs/payment-methods.php', array ( 
			'methods' => $methods 
	) );
} else {
	$messages[] = sprintf( __( 'There are no saved payment methods for this gateway. Please add a payment method on the <a href="%s">Payment Method Page</a>', 'braintree-payments' ), esc_url( wc_get_account_endpoint_url( 'add-payment-method' ) ) );
	wc_get_template( 'notices/error.php', array ( 
			'messages' => $messages 
	) );
}