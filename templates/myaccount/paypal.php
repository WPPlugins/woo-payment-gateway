<?php 
/**
 * @version 2.6.9
 */
$button = bwc_get_paypal_button();

braintree_nonce_field( 'paypal_payment_method_nonce' );

braintree_device_data_field( 'braintree_paypal_device_data' );
?>
<div id="braintree-paypal-container">
	<input type="hidden" id="paypal_events_initialized" />
	<div class="braintree-paypal-button">
		<?php include $button['html']?>
	</div>
	<div id="braintree-paypal-tokenized"></div>
</div>