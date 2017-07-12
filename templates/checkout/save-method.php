<?php
/**
 * @version 2.6.9
 */
if ( bt_manager()->is_active( 'save_payment_methods' ) ) :
	?>
<div>
	<p class="form-row">
		<input type="checkbox" class="input-checkbox"
			id="bfwc_save_payment_method" name="bfwc_save_payment_method">
		<label for="bfwc_save_payment_method"><?php _e('Save Payment Method', 'braintree-payments' )?></label>
	</p>
</div>
<?php endif;?>