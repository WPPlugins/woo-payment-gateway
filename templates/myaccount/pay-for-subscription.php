<?php 
/**
 * @version 2.6.9
 */
?>
<h1><?php _e('Pay For Subscription', 'braintree-payments' )?></h1>
<form method="post">
	<?php wp_nonce_field('pay-for-subscription')?>
	<input type="hidden" name="bfwc_subscription" value="<?php echo bwc_get_order_property( 'id', $subscription )?>">
	<table>
		<tbody>
			<tr>
				<th><?php _e('Subscription', 'braintree-payments' )?></th>
				<td><a href="<?php echo $subscription->get_view_subscription_url()?>"><?php printf('#%s', $subscription->get_order_number())?></a></td>
			</tr>
			<tr>
				<th><?php _e('Recurring Total', 'braintree-payments' )?></th>
				<td><?php echo $subscription->get_formatted_total()?></td>
			</tr>
			<tr>
				<th><?php _e('Status', 'braintree-payments' )?></th>
				<td><?php echo get_post_status_object(bwc_get_order_property( 'post_status', $subscription ))->label?></td>
			</tr>
		</tbody>
	</table>
	<div id="payment">
		<ul class="wc_payment_methods payment_methods methods">
			<?php if(!empty($available_gateways)):
					foreach ($available_gateways as $id => $gateway):
						wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
					endforeach;?>
				<?php else:
					echo '<li>' . apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>';
				endif;?>
		</ul>
	</div>
	<div class="form-row">
		<input type="submit" id="place_order" class="button alt" value="<?php _e('Pay', 'braintree-payments' )?>" data-value="<?php  _e('Pay', 'braintree-payments' )?>">
	</div>
</form>