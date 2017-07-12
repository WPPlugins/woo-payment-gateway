<?php
/**
 * @version 2.6.9
 */
?>
<div class="classic-form-container">
	<div class="card-number-wrapper field-container">
		<span class="field-label"><?php _e( bt_manager()->get_option('donation_card_number_label'), 'braintree-payments')?></span>
		<div id="bfwc-card-number" class="hosted-field"><span class="bfwc-card-type"></span></div>
	</div>
	<div class="form-group-wrapper multi-fields">
		<div class="exp-date-field field-container">
			<span class="field-label"><?php _e( bt_manager()->get_option('donation_card_expiration_date_label'), 'braintree-payments')?></span>
			<div id="bfwc-expiration-date" class="hosted-field"></div>
		</div>
		<?php if(bt_manager()->is_active('donation_cvv_field_enabled')){?>
			<div class="cvv-field field-container">
				<span class="field-label"><?php _e( bt_manager()->get_option('donation_card_cvv_label'), 'braintree-payments')?></span>
				<div id="bfwc-cvv" class="hosted-field"></div>
				<span class="cvv-image"></span>
			</div>
		<?php }?>
	</div>
		<?php if(bt_manager()->is_active('donation_postal_field_enabled')){?>
			<div class="form-group-wrapper multi-fields">
				<div class="bfwc-postal-field field-container">
					<span class="field-label"><?php _e( bt_manager()->get_option('donation_card_postal_label'), 'braintree-payments')?></span>
					<div id="bfwc-postal-code" class="hosted-field"></div>
				</div>
			</div>
		<?php }?>
</div>