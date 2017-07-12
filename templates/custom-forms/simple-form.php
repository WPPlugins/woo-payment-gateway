<?php 
/**
 * @version 2.6.9
 */
?>
<div class="simple-form">
	<div class="form-group">
		<label><?php bwc_custom_form_text('card_number_label', __('Card Number', 'braintree-payments'))?></label>
		<div id="bfwc-card-number" data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>" class="hosted-field">
			<span class="bfwc-card-type"></span>
		</div>
	</div>
	<div class="form-group">
		<label><?php bwc_custom_form_text('card_expiration_date_label', __('Exp Date', 'braintree-payments'))?></label>
		<div id="bfwc-expiration-date" data-placeholder="<?php bwc_custom_form_text('card_expiration_date_placeholder', __('MM / YY', 'braintree-payments'))?>" class="hosted-field"></div>
	</div>
	<?php if(bwc_cvv_field_enabled()):?>
	<div class="form-group">
		<label><?php bwc_custom_form_text('card_cvv_label', __('CVV', 'braintree-payments'))?></label>
		<div id="bfwc-cvv" data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>" class="hosted-field"></div>
	</div>
	<?php endif;?>
	<?php if(bwc_postal_code_enabled()):?>
	<div class="form-group">
		<label><?php bwc_custom_form_text('card_postal_label', __('Postal Code', 'braintree-payments'))?></label>
		<div id="bfwc-postal-code" data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>" class="hosted-field"></div>
	</div>
	<?php endif?>
	<?php if(bwc_display_save_payment_method()):?>
	<div class="form-group">
		<label><?php bwc_custom_form_text('card_save_label', __('Save', 'braintree-payments'))?></label>
		<div class="hosted-field save-card-field">
			<input type="checkbox" id="bfwc_save_credit_card"
				name="bfwc_save_credit_card">
			<label class="bfwc-save-label" for="bfwc_save_credit_card"></label>
		</div>
	</div>
	<?php endif;?>
</div>