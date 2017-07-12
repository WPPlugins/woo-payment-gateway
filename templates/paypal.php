<?php 
/**
 * @version 2.6.9
 */
$button = bwc_get_paypal_button();

braintree_nonce_field( $gateway::$nonce_id );
braintree_device_data_field( $gateway::$device_data_id );
?>
<div id="braintree-paypal-container" class="bfwc-new-payment-method-container"
	<?php if($has_methods){ echo 'style="display: none"';}?>>
	
    	<?php if($has_methods):?>
    		<?php bwc_get_template('saved-method-button.php', array('text'=>__('PayPal Accounts', 'braintree-payments' )))?>
		<?php endif;?>
		
	<div class="braintree-paypal-button">
    	<?php bwc_get_template($button['html'])?>
    </div>
	<div class="bfwc-tokenized-paypal-method"></div>
</div>