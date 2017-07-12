<?php 
/**
 * @version 2.6.9
 */
?>
<h2><?php _e('Change Payment Method', 'braintree-payments' )?></h2>
<form id="order_review" method="post">
	<?php wp_nonce_field('change-payment-method', '_change_method_nonce')?>
	<input type="hidden" name="bfwc_subscription" value="<?php echo bwc_get_order_property( 'id', $subscription )?>">
	<table>
		<tbody>
			<tr>
				<th><?php _e('Subscription', 'braintree-payments' )?></th>
				<td><a href="<?php echo $subscription->get_view_subscription_url()?>"><?php printf(__('#%s', 'braintree-payments' ), $subscription->get_order_number())?></a></td>
			</tr>
			<tr>
				<th><?php _e('Payment Method', 'braintree-payments' )?></th>
				<td><?php echo $subscription->get_payment_method_to_display()?></td>
			</tr>
			<tr>
				<th><?php _e('Order Total', 'braintree-payments' )?></th>
				<td><?php echo $subscription->get_formatted_total()?></td>
			</tr>
		</tbody>
	</table>
	<div id="payment">
		<ul class="wc_payment_methods payment_methods methods">
			<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						if($gateway->supports('bfwcs_change_payment_method')){
							wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
						}
					}
				} else {
					echo '<li>' . apply_filters( 'woocommerce_no_available_payment_methods_message', __( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) ) . '</li>';
				}
			?>
		</ul>
	</div>
	<div class="form-row">
		<input type="submit" id="place_order" class="button alt" value="<?php _e('Change Payment Method', 'braintree-payments' )?>" data-value="<?php  _e('Change Payment Method', 'braintree-payments' )?>">
	</div>
</form>