<div class="woocommerce_order_items_wrapper">
	<table class="woocommerce_order_items">
		<thead>
			<tr>
				<th><?php _e('Order Number', 'braintree-payments' )?></th>
				<th><?php _e('Status', 'braintree-payments' )?></th>
				<th><?php _e('Relationship', 'braintree-payments' )?></th>
				<th><?php _e('Total', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
		<?php foreach($orders as $order):?>
			<tr>
				<td>
					<a
						href="<?php echo get_edit_post_link(bwc_get_order_property('id', $order))?>">#<?php echo $order->get_order_number()?></a>
				</td>
				<td><?php echo get_post_status_object(get_post_status(bwc_get_order_property('id', $order)))->label?></td>
				<td><?php bwc_get_order_property('id', $subscription->get_order()) === bwc_get_order_property('id', $order) ? _e('Parent Order', 'braintree-payments' ) : _e('Renewal Order', 'braintree-payments' )?></td>
				<td><?php echo $order->get_formatted_order_total()?></td>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
</div>