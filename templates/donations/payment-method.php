<?php
/**
 * @version 2.6.9
 */
?>
<li class="payment_method payment_method_<?php echo $gateway->id?>">
	<input name="payment_gateway" value="<?php echo $gateway->id?>"
		type="radio" />
	<label class="bfwc-donation-gateway-label">
		<?php echo $gateway->title?>
		<?php echo $gateway->get_icon()?>
	</label>
	<div class="bfwc-donation-payment-box payment_box_"<?php echo $gateway->id?>>
		<?php $gateway->payment_fields()?>
	</div>
</li>