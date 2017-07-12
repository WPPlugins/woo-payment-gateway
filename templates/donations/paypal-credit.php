<?php
/**
 * @version 2.6.9
 */
$button = bfwcd_get_paypal_credit_button();
?>
<div class="braintree-paypal-button">
	<?php bfwc_get_template( $button [ 'html' ] );?>
</div>
<div class="bfwc-tokenized-paypal-method"></div>