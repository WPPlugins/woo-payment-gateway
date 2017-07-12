<?php
/**
 * @version 2.6.9
 */
?>
<div class="classic-form-container">
	<div class="card-number-wrapper field-container">
		<span class="field-label"><?php bwc_custom_form_text('card_number_label', __('Card Number', 'braintree-payments'))?></span>
		<div id="bfwc-card-number" class="hosted-field"
			data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>">
			<span class="bfwc-card-type"></span>
		</div>
		<span class="bfwc-error"></span>
	</div>
	<div class="form-group-wrapper multi-fields">
		<div class="exp-date-field field-container">
			<span class="field-label"><?php bwc_custom_form_text('card_expiration_date_label', __('Exp Date', 'braintree-payments'))?></span>
			<div id="bfwc-expiration-date" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_expiration_date_placeholder', __('MM / YY', 'braintree-payments'))?>"></div>
			<span class="bfwc-error"></span>
		</div>
		<?php if(bwc_cvv_field_enabled()):?>
			<div class="cvv-field field-container">
			<span class="field-label"><?php bwc_custom_form_text('card_cvv_label', __('CVV', 'braintree-payments'))?></span>
			<div id="bfwc-cvv" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>"></div>
			<span class="cvv-image"></span>
			<span class="bfwc-error"></span>
		</div>
		<?php endif;?>
	</div>
		<?php if(bwc_postal_code_enabled() || bwc_display_save_payment_method()):?>
			<div class="form-group-wrapper multi-fields">
				<?php if(bwc_postal_code_enabled()):?>
				<div class="postal-field field-container">
			<span class="field-label"><?php bwc_custom_form_text('card_postal_label', __('Postal Code', 'braintree-payments'))?></span>
			<div id="bfwc-postal-code" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>"></div>
			<span class="bfwc-error"></span>
		</div>
				<?php endif;?>
				<?php if(bwc_display_save_payment_method()):?>
				<div class="save-card-field field-container">
			<span class="field-label active"><?php bwc_custom_form_text('card_save_label', __('Save', 'braintree-payments'))?></span>
			<input type="checkbox" id="bfwc_save_credit_card"
				name="bfwc_save_credit_card">
			<label class="bfwc-save-label" for="bfwc_save_credit_card"></label>
		</div>
				<?php endif;?>
			</div>
		<?php endif;?>
</div>