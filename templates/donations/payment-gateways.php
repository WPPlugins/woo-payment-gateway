<?php
/**
 * @version 2.6.9
 */
?>
<div class="bfwc-donation-gateways">
	<ul class="payment_methods">
		<?php foreach($gateways as $gateway):?>
			<?php bfwc_get_template('donations/payment-method.php', array('gateway'=>$gateway))?>
		<?php endforeach;?>
	</ul>
</div>
