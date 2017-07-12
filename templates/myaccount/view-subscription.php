<?php 
/**
 * @version 2.6.9
 */
?>
<h2><?php printf(__('Subscription #%s', 'braintree-payments' ), $subscription->get_order_number())?></h2>
<table>
	<tbody>
		<tr>
			<th><?php _e('Status', 'braintree-payments' )?></th>
			<td><?php echo get_post_status_object(bwc_get_order_property( 'post_status', $subscription ))->label?></td>
		</tr>
		<tr>
			<th><?php _e('Last Payment', 'braintree-payments' )?></th>
			<td><?php echo $subscription->get_formatted_date('last_payment')?></td>
		</tr>
		<tr>
			<th><?php _e('Next Payment', 'braintree-payments' )?></th>
			<td><?php echo $subscription->get_formatted_date('next_payment')?></td>
		</tr>
		<tr>
			<th><?php _e('End Date', 'braintree-payments' )?></th>
			<td><?php echo $subscription->get_formatted_date('end')?></td>
		</tr>
		<tr>
			<th><?php _e('Actions', 'braintree-payments' )?></th>
			<td>
				<?php foreach(bfwc_subscription_user_actions($subscription) as $k => $action):?>
					<a href="<?php echo $action['url']?>"
				class="button <?php echo $k?>"><?php echo $action['label']?></a>
				<?php endforeach;?>
			</td>
		</tr>
	</tbody>
</table>
<h2><?php _e('Subscription Totals', 'braintree-payments' )?></h2>
<table>
	<tbody>
		<tr>
			<td><?php _e('Payment Method', 'braintree-payments' )?></td>
			<td><?php echo bwc_get_order_property( 'payment_method_title', $subscription )?></td>
		</tr>
		<tr>
			<td><?php _e('Recurring Total', 'braintree-payments' )?></td>
			<td><?php echo $subscription->get_formatted_total()?></td>
		</tr>
	</tbody>
</table>
<h2><?php _e('Related Orders', 'braintree-payments' )?></h2>
<?php
$related_orders = bfwcs_get_related_orders( $subscription );
$has_orders = ( bool ) $related_orders;

if ( ! $has_orders ) :
	printf( __( 'There are no orders associated with this subscription.', 'braintree-payments' ) );
 else :
	?>
<table>
	<thead>
		<tr>
			<th><?php _e('Order', 'braintree-payments' )?></th>
			<th><?php _e('Date', 'braintree-payments' )?></th>
			<th><?php _e('Status', 'braintree-payments' )?></th>
			<th><?php _e('Total', 'braintree-payments' )?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($related_orders as $order):?>
		<tr>
			<td><a href="<?php echo $order->get_view_order_url()?>"><?php printf('#%s', $order->get_order_number())?></a></td>
			<td><?php echo date_i18n(get_option('date_format'), strtotime(bwc_get_order_property( 'order_date', $order )))?></td>
			<td><?php echo wc_get_order_status_name($order->get_status())?></td>
			<td><?php echo $order->get_formatted_order_total()?></td>
		</tr>
	<?php endforeach;?>
	</tbody>
</table>
<?php endif;?>