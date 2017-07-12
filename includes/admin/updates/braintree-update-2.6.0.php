<?php
/**
 * Updates for version 2.6.0
 */
$configurations = get_option( 'YnJhaW50cmVlX3BheW1lbnRfc2V0dGluZ3M=', array () );

if ( preg_match( '/^[a-zA-Z0-9\/\r\n+]*={0,2}$/', $configurations ) ) {
	$new_config = maybe_unserialize( base64_decode( $configurations ) );
	update_option( 'braintree_payment_settings', $new_config );
	delete_option( 'YnJhaW50cmVlX3BheW1lbnRfc2V0dGluZ3M=' );
}