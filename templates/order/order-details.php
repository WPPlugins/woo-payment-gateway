<?php 
/**
 * @version 2.6.9
 */
?>
<h2><?php _e('Subscriptions', 'braintree-payments' )?></h2>
<table class="shop_table order_details">
	<thead>
		<tr>
			<th><?php _e('Subscription', 'braintree-payments' )?></th>
			<th><?php _e('Status', 'braintree-payments' )?></th>
			<th><?php _e('Next Payment', 'braintree-payments' )?></th>
			<th><?php _e('Total', 'braintree-payments' )?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($subscriptions as $subscription):?>
		<tr>
			<td>
				<a href="<?php echo $subscription->get_view_subscription_url()?>">#<?php echo $subscription->get_order_number()?></a>
			</td>
			<td><?php echo get_post_status_object(get_post_status(bwc_get_order_property('id', $subscription)))->label?></td>
			<td><?php echo $subscription->get_formatted_date('next_payment')?></td>
			<td><?php echo $subscription->get_formatted_total()?></td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>