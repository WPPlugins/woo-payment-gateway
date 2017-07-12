<?php
/**
 * @version 2.6.9
 */
?>
<div class="simple-form">
	<div class="form-group">
		<label><?php _e(bt_manager()->get_option('donation_card_number_label'), 'braintree-payments')?></label>
		<div id="bfwc-card-number" class="hosted-field"><span class="bfwc-card-type"></span></div>
	</div>
	<div class="form-group">
		<label><?php _e(bt_manager()->get_option('donation_card_expiration_date_label'), 'braintree-payments')?></label>
		<div id="bfwc-expiration-date" class="hosted-field"></div>
	</div>
	<?php if(bt_manager()->is_active('donation_cvv_field_enabled')){?>
		<div class="form-group">
		<label><?php _e(bt_manager()->get_option('donation_card_cvv_label'), 'braintree-payments')?></label>
		<div id="bfwc-cvv" class="hosted-field"></div>
	</div>
	<?php }?>
	<?php if(bt_manager()->is_active('donation_postal_field_enabled')){?>
		<div class="form-group">
		<label><?php _e(bt_manager()->get_option('donation_card_postal_label'), 'braintree-payments')?></label>
		<div id="bfwc-postal-code" class="hosted-field"></div>
	</div>
	<?php }?>
</div>