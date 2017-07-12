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
<div id="braintree-dropin-container"
	class="bfwc-new-payment-method-container"
	<?php if($has_methods){echo 'style="display:none"';}?>>
	
    <?php if($has_methods):?>
		<?php bwc_get_template('saved-method-button.php', array('text'=>__('Saved Cards', 'braintree-payments' )))?>
	<?php endif;?>
	
    <div id="braintree-dropin-form"></div>
    <?php if(bwc_display_save_payment_method()):?>
    	<div class="bfwc-dropin-row">
		<div class="bfwc-dropin-column bfwc-dropin-card-label">
			<span class="bfwc-dropin-field-name"><?php echo bt_manager()->get_option('card_save_label')?></span>
			<input type="checkbox" id="bfwc_save_credit_card"
				name="bfwc_save_credit_card" />
			<label class="bfwc-dropin-save-label" for="bfwc_save_credit_card"></label>
		</div>
	</div>
    <?php endif;?>
</div>