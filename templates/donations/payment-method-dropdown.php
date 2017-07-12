<?php
/**
 * @version 2.6.9
 */
?>
<select class="bfwc-select2 bfwc-selected-payment-method">
				<?php foreach($methods as $key => $method):?>
					<option
		data-bfwc-cardType="<?php echo braintree_get_payment_method_class($method)?>"
		class="<?php echo braintree_get_payment_method_class($method)?>"
		value="<?php echo $key?>"
		<?php selected($default_method['token'], $method['token'])?>><?php echo braintree_get_payment_method_title_from_array($method)?></option>
				<?php endforeach;?>
</select>