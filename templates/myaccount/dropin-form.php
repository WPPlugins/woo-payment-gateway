<?php 
/**
 * @version 2.6.9
 */
braintree_nonce_field();

if ( bwc_is_advanced_fraud_tools() ) {
	braintree_device_data_field();
}
?>
<div class="braintree-payment-gateway">
	<?php
	
	if ( ! bwc_payment_icons_outside() ) {
		bwc_get_template( 'checkout/payment-method-icons.php' );
	}
	?>
	<input type="hidden" id="braintree_events_initialized" value="false" />
	<div id="braintree-dropin-container">
		<div id="braintree-dropin-form"></div>
	</div>
</div>