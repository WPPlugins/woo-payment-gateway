<?php
/**
 * @version 2.6.9
 */
braintree_nonce_field( $gateway::$nonce_id );
braintree_device_data_field( $gateway::$device_data_id );

if ( $show_payment_icons ) {
	bwc_get_template( 'checkout/payment-method-icons.php', array (
			'icons_inside' => $icons_inside 
	) );
}
?>

<div id="braintree-hostedfields-container" class="bfwc-new-payment-method-container" style="<?php $has_methods ? printf('display: none') : printf('')?>">
	
	<?php if(bwc_payment_loader_enabled()):?>
		<div class="bfwc-payment-loader" style="display: none">
			<?php bwc_get_template($loader)?>
		</div>
	<?php endif;?>
	
	<?php if($has_methods):?>
		<?php bwc_get_template('saved-method-button.php', array('text'=>__('Saved Cards', 'braintree-payments' )))?>
	<?php endif;?>
	
    <?php bwc_get_template($custom_form['html'])?>
</div>
