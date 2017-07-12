<?php 
/**
 * @version 2.6.9
 */
?>
<h2><?php _e('My Subscriptions', 'braintree-payments' )?></h2>
<?php if(!empty($subscriptions)):?>
<table class="woocommerce_subscriptions_table">
	<thead>
		<tr>
			<th><?php _e('Subscription', 'braintree-payments' )?></th>
			<th><?php _e('Status', 'braintree-payments' )?></th>
			<th><?php _e('Next Payment', 'braintree-payments' )?></th>
			<th><?php _e('Total', 'braintree-payments' )?></th>
			<th><?php _e('Actions', 'braintree-payments' )?></th>
		</tr>
	</thead>
	<tbody>
	<?php foreach($subscriptions as $subscription):?>
		<tr>
			<td><a href="<?php echo $subscription->get_view_subscription_url()?>">#<?php echo $subscription->get_order_number()?></a></td>
			<td><?php echo get_post_status_object(bwc_get_order_property( 'post_status', $subscription ))->label?></td>
			<td><?php echo $subscription->get_formatted_date('next_payment')?></td>
			<td><?php echo $subscription->get_formatted_total()?>
			<td>
				<?php foreach(bfwcs_get_subscription_actions($subscription) as $key => $action):?>
					<a class="button <?php echo $key?>" href="<?php echo $action['url']?>"><?php echo $action['label']?></a>
				<?php endforeach;?>
			</td>
		</tr>
		<?php endforeach;?>
	</tbody>
</table>
<?php endif;?>