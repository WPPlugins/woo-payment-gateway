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
<?php foreach($recurring_carts as $cart_key => $cart){?>
	<tr>
		<th><?php _e('Subtotal', 'braintree-payments' )?></th>
		<td><?php echo bfwcs_cart_subtotal_string($cart)?></td>	
	</tr>
		<?php if($cart->shipping_total > 0){?>
		<tr>
			<th><?php _e('Shipping', 'braintree-payments' )?></th>
			<td><?php echo bfwcs_cart_shipping_total($cart)?></td>
		</tr>
		<?php }?>
		<?php if(wc_tax_enabled()){?>
			<?php foreach ( $cart->get_tax_totals() as $code => $tax ) {?>
				<tr>
					<th><?php echo esc_html( $tax->label )?></th>
					<td><?php echo bfwcs_cart_tax_total_html($tax, $cart)?></td>
				</tr>
			<?php }?>
		<?php }?>
	<tr>
		<th><?php _e('Total', 'braintree-payments' )?></th>
		<td><?php echo bfwcs_cart_recurring_total_html($cart)?></td>
	</tr>
<?php } ?>