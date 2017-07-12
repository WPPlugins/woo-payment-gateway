<?php
/**
 * @version 2.6.9
 */
?>
<button id="braintree_paypal_button" class="paypalButton-pill">
	<span class="paypal-button-content">
		<img
			src="<?php echo bt_manager()->plugin_assets_path() . 'img/paypal/paypal-logo.png'?>"
			alt="PayPal" />
		<span><?php echo __('Check out', 'braintree-payments' )?></span>
	</span>
	<br>
	<span class="paypal-button-tag-content"><?php echo __('The safer, easier way to pay.', 'braintree-payments' )?></span>
</button>