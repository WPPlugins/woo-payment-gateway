<?php 
/**
 * @version 2.6.9
 */
$subscription = wc_get_order( absint( $_GET[ 'change_payment_method' ] ) );

bwc_payment_method_token_field($token_field_id, $default_method);
?>
<div id="braintree_payment_methods"
	class="braintree-payment-gateway braintree-payment-methods">
<?php foreach($methods as $key => $method){?>
		<div
		class="braintree-payment-method <?php if(bwc_get_order_property( 'payment_method_token', $subscription ) === $key){echo 'selected';}?>"
		data-token="<?php echo $key?>">
		<span
			class="payment-method-type <?php echo str_replace(' ', '', $method['method_type'])?>"></span>
		<span class="payment-method-description"><?php echo braintree_get_payment_method_title_from_array($method)?></span>
	</div>
	<?php } ?>
</div>