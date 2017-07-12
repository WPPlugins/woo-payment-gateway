<?php 
/**
 * @version 2.6.9
 */
$button = bwc_get_paypal_credit_button();

braintree_nonce_field( $gateway::$nonce_id );
braintree_device_data_field( $gateway::$device_data_id );
?>
<div id="braintree-paypal-credit-container"
	class="bfwc-new-payment-method-container">
	<div class="braintree-paypal-button">
    		<?php bwc_get_template( $button['html'] )?>
    </div>
	<div class="bfwc-tokenized-paypal-method"></div>
</div>