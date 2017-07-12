<?php
/**
 * @version 2.6.9
 */
$enabled_fields = $shortcode->get_enabled_fields();
$fields = $shortcode->get_donation_fields();
$accepted_methods = bt_manager()->get_option( 'donation_payment_methods' );
$form_type = bt_manager()->get_option( 'donation_form_type' );
$payment_methods = braintree_get_user_payment_methods( wp_get_current_user()->ID );

?>
<div class="braintree-donation-container braintree-payment-gateway">
	<form method="post" class="braintree-donation">
		<div class="bfwc-payment-loader" style="display: none">
            	<?php bfwc_get_template(bfwcd_get_loader())?>
        </div>
    	<?php wp_nonce_field('braintree_donation')?>
            	<div class="braintree-donation-field-container">
            		<?php
					if ( ! empty( $fields ) ) {
						foreach ( $fields as $key => $data ) {
							if ( array_key_exists( $key, $enabled_fields ) ) {
								bfwc_get_template("donations/html-helpers/{$data['type']}-html.php", array('data' => $data, 'key' => $key));
							}
						}
					}
					if ( $shortcode->is_transaction ) {
						$key = 'donation_amount';
						$data = $shortcode->get_amount_input_field( $args );
						bfwc_get_template("donations/html-helpers/{$data['type']}-html.php", array('data' => $data, 'key' => $key));
					} else {
						$key = $shortcode->is_single_recurring ? 'bfwcd_amount' : 'recurring_donation_plan';
						$data = $shortcode->get_recurring_plan_field( $args );
						bfwc_get_template("donations/html-helpers/{$data['type']}-html.php", array('data' => $data, 'key' => $key));
						if($shortcode->is_single_recurring){
							echo '<input type="hidden" name="recurring_donation_plan" value="'.$args['recurring_donation_plan'] . '"/>';
						}
					}
					?>
        		</div>
		<div id="braintree_donation_payment_form" class="braintree-donation-payment-form bfwc-new-payment-method-container">
			<?php if(!empty($payment_methods)):?>
            	<?php bwc_get_template('saved-method-button.php', array('text'=>__('Saved Methods', 'braintree-payments' )))?>
            <?php endif;?>
            		<?php
					bfwc_get_template( 'donations/payment-gateways.php', array (
																'gateways' => $shortcode->available_gateways()
														) );
					?>
            		<div>
			</div>
		</div>
        		<?php if(!empty($payment_methods)):?>
            		<?php bfwc_get_template('donations/braintree-payment-methods.php', array(
            				'methods' => $payment_methods,
            				'default_method' => braintree_get_default_method($payment_methods),
            				'token_id' => 'payment_method_token',
            				'button_text' => __('New Method', 'braintree-payments' )
            		))?>
            	<?php endif;?>
            <div class="bfwc-row">
           		<button class="bfwc-donation-button" id="submit_donation" class="inline-submit" style="<?php echo $shortcode->get_button_styles()?>" id="submit_donation"><?php echo bt_manager()->get_option('donation_button_text')?></button>
    		</div>
    </form>
</div>
