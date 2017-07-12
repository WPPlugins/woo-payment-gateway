<?php 
/**
 * @version 2.6.9
 */
$button = bwc_get_applepay_button();

braintree_nonce_field( $gateway::$nonce_id );
?>

<div class="applepay-button-container bfwc-new-payment-method-container" id="applepay-button-container" <?php if($has_methods){echo 'style="display: none"';}?>>
	
	<?php if($has_methods):?>
		<?php bwc_get_template('saved-method-button.php', array('text'=>__('Apple Pay Methods', 'braintree-payments' )))?>
	<?php endif;?>
	
	<?php do_action('bfwc_before_applepay_button')?>
	
	<input id="braintree-applepay-button" type="image" src="<?php echo $button['src']?>" />
	<div id="braintree-applepay-warning" style="display:none">
	 	<div class="braintree-applepay-warning">
	 		<p><?php _e('Apple Pay is not compatible with your Browser/Device. Apple Pay requires an apple device such as iPhone, iPad, or Mac with Safari.', 'braintree-payments' ) ?></p>
	 	</div>
	</div>
</div>