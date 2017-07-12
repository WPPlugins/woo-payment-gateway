<?php
/**
 * @version 2.6.9
 */
?>
<div class="bfwc-card-form">
	<!-- <h3 class="bfwc-card-title">
		<?php _e('Payment Details', 'braintree-payments' )?>
	</h3>-->
	<div class="bfwc-form-wrapper">
		<div class="bfwc-field-container card-field-container">
			<label><?php bwc_custom_form_text('card_number_label', __('Card Number', 'braintree-payments'))?></label>
			<div id="bfwc-card-number" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>">
				<span class="bfwc-card-type"></span>
			</div>
		</div>
	</div>
	<div class="bfwc-form-wrapper">
		<div class="bfwc-field-container field-exp-month">
			<label><?php bwc_custom_form_text('card_expiration_date_label', __('Exp Date', 'braintree-payments'))?></label>
			<div id="bfwc-expiration-month" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_expiration_month_placeholder', __('MM', 'braintree-payments'))?>"></div>
		</div>
		<div class="bfwc-field-container field-year">
			<div id="bfwc-expiration-year" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_expiration_year_placeholder', __('YY', 'braintree-payments'))?>"></div>
		</div>
			<?php if(bwc_cvv_field_enabled()):?>
			<div class="bfwc-field-container field-cvv">
			<label><?php bwc_custom_form_text('card_cvv_label', __('CVV', 'braintree-payments'))?></label>
			<div id="bfwc-cvv" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>"></div>
		</div>
			<?php endif;?>
	</div>
	<?php if(bwc_postal_code_enabled() || bwc_display_save_payment_method()):?>
		<div class="bfwc-form-wrapper">
			<?php if(bwc_postal_code_enabled()):?>
				<div class="bfwc-field-container field-postal">
			<label><?php bwc_custom_form_text('card_postal_label', __('Postal Code', 'braintree-payments'))?></label>
			<div id="bfwc-postal-code" class="hosted-field"
				data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>"></div>
		</div>
			<?php endif;?>
			<?php if(bwc_display_save_payment_method()):?>
				<div class="bfwc-field-container field-save">
			<label><?php bwc_custom_form_text('card_save_label', __('Save', 'braintree-payments'))?></label>
			<input type="checkbox" id="bfwc_save_credit_card"
				name="bfwc_save_credit_card">
			<label class="bfwc-save-label" for="bfwc_save_credit_card"></label>
		</div>
		<?php endif;?>
		</div>
	<?php endif;?>
</div>