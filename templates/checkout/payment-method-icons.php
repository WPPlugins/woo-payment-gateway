<?php 
/**
 * @version 2.6.9
 */
$payment_methods = bt_manager()->get_option( 'payment_methods' );
if ( empty( $payment_methods ) ) {
	$payment_methods = array ();
}
if ( ! empty( $payment_methods ) ) :
	?>
<div
	class="braintree-accepted-payment-methods <?php if($icons_inside){echo 'bfwc-inside';}?>">
<?php foreach($payment_methods as $method):?>
		<?php if(bwc_payment_icons_enclosed_type()):?>
			<span class="bfwc-enclosed-method-icon <?php echo $method?>">
		<img src="<?php echo bwc_get_enclosed_icon_url($method)?>" />
	</span>
		<?php else:?>
			<span class="payment-method-icon <?php echo $method?>"></span>
		<?php endif;?>
<?php endforeach;?>
</div>
<?php endif;?>