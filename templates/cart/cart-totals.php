<?php 
/**
 * @version 2.6.9
 */
?>
<tr>
	<th colspan="2">
		<?php _e('Recurring Totals', 'braintree-payments' )?>
	</th>
</tr>
<?php foreach($recurring_carts as $key => $cart){?>
<tr>
	<th><?php _e('Subtotal', 'braintree-payments' )?></th>
	<td><?php echo bfwcs_cart_subtotal_string($cart)?></td>
</tr>
<tr>
	<?php if($cart->shipping_total && $cart->needs_shipping() && $cart->show_shipping()){?>
		<th><?php _e('Shipping', 'braintree-payments' )?></th>
	<td><?php echo bfwcs_cart_shipping_total($cart)?></td>
	<?php }?>
</tr>
<tr>
	<th><?php _e('Recurring Total', 'braintree-payments' )?></th>
	<td>
		<?php echo bfwcs_cart_recurring_total_html($cart)?>
		<div class="first-payment-date">
			<?php printf(__('First Payment Date: %s', 'braintree-payments' ), bfwcs_cart_formatted_date($cart->first_payment_date, $cart->subscription_time_zone))?>
		</div>
	</td>
</tr>
<?php }?>