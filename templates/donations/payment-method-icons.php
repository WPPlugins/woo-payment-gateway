<?php
/**
 * @version 2.6.9
 */

$payment_methods = bt_manager()->get_option( 'donation_payment_methods' );
if ( empty( $payment_methods ) ) {
	$payment_methods = array ();
}
if ( ! empty( $payment_methods ) ) {
	?>
<div class="braintree-accepted-payment-methods">
	<?php foreach($payment_methods as $method):?>
		<span class="payment-method-icon <?php echo $method?>"></span>
	<?php endforeach;?>
</div>
<?php }?>