<?php
/**
 * @version 2.6.9
 */
$paypal_button = bfwcd_get_paypal_button();
?>
<div class="braintree-paypal-button">
	<?php bfwc_get_template( $paypal_button [ 'html' ] );?>
</div>
<div class="bfwc-tokenized-paypal-method"></div>