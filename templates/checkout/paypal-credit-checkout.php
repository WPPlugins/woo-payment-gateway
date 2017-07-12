<?php 
/**
 * @version 2.6.9
 */
?>
<div class="braintree-payment-gateway">
<?php
bwc_get_template( 'paypal-credit.php', array (
		'has_methods' => false, 
		'gateway' => $gateway 
) );
?>
</div>