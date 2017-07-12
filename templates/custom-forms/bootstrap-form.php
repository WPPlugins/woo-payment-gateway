<?php
/**
 * @version 2.6.9
 */
?>
<div class="row">
	<div class="form-group col-xs-8">
		<label class="control-label"><?php bwc_custom_form_text('card_number_label', __('Card Number', 'braintree-payments'))?></label>
		<!--  Hosted Fields div container -->
		<div class="form-control" id="bfwc-card-number"
			data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>">
			<span class="bfwc-card-type"></span>
		</div>
		<span class="helper-text"></span>
	</div>
	<div class="form-group col-xs-4">
		<div class="row">
			<label class="control-label col-xs-12"><?php bwc_custom_form_text('card_expiration_date_label', __('Exp Date', 'braintree-payments'))?></label>
			<div class="col-xs-6">
				<!--  Hosted Fields div container -->
				<div class="form-control" id="bfwc-expiration-month" data-placeholder="<?php bwc_custom_form_text('card_expiration_month_placeholder', __('MM', 'braintree-payments'))?>"></div>
			</div>
			<div class="col-xs-6">
				<!--  Hosted Fields div container -->
				<div class="form-control" id="bfwc-expiration-year" data-placeholder="<?php bwc_custom_form_text('card_expiration_year_placeholder', __('YY', 'braintree-payments'))?>"></div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<?php if(bwc_cvv_field_enabled()):?>
		<div class="form-group col-xs-6">
		<label class="control-label"><?php bwc_custom_form_text('card_cvv_label', __('CVV', 'braintree-payments'))?></label>
		<div class="form-control" id="bfwc-cvv"
			data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>"></div>
	</div>
	<?php endif;?>
	<?php if(bwc_postal_code_enabled()):?>
		<div class="form-group col-xs-6">
		<label class="control-label"><?php bwc_custom_form_text('card_postal_label', __('Postal Code', 'braintree-payments'))?></label>
		<div class="form-control" id="bfwc-postal-code" data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>"></div>
	</div>
	<?php endif;?>
</div>
<?php if(bwc_display_save_payment_method()):?>
<div class="row">
	<div class="form-group col-xs-6">
		<label class="save-card-label"><?php bwc_custom_form_text('card_save_label', __('Save', 'braintree-payments'))?></label>
		<input type="checkbox" id="bfwc_save_credit_card"
			name="bfwc_save_credit_card">
		<label class="bfwc-save-label" for="bfwc_save_credit_card"></label>
	</div>
</div>
<?php endif;?>