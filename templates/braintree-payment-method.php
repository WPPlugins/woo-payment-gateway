<?php 
/**
 * @version 2.6.9
 */
?>
<div
	class="braintree-payment-method <?php if($method['default'] || $default_method['token'] === $key){echo 'selected';}?>"
	data-token="<?php echo $key?>">
	<span
		class="payment-method-type <?php echo bwc_payment_icons_type() . ' ' .braintree_get_payment_method_class($method)?>"></span>
	<span class="payment-method-description"><?php echo braintree_get_payment_method_title_from_array($method)?></span>
</div>