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
		<span><?php _e('Check out', 'braintree-payments' )?></span>
	</span>
	<span class="paypal-button-tag-content"><?php _e('The safer, easier way to pay.', 'braintree-payments' )?></span>
</button>