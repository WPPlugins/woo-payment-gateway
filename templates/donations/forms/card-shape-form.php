<?php
/**
 * @version 2.6.9
 */
$cvv_enabled = bt_manager()->is_active('donation_cvv_field_enabled');
$postal_enabled = bt_manager()->is_active('donation_postal_field_enabled');
?>
<div class="bfwc-card-form">
	<div class="bfwc-form-wrapper">
		<div class="bfwc-field-container">
			<label><?php _e(bt_manager()->get_option('donation_card_number_label'), 'braintree-payments')?></label>
			<div id="bfwc-card-number" class="hosted-field">
				<span class="bfwc-card-type"></span>
			</div>
		</div>
	</div>
	<div class="bfwc-form-wrapper">
			<div class="bfwc-field-container field-exp-month">
				<label><?php _e(bt_manager()->get_option('donation_card_expiration_date_label'), 'braintree-payments')?></label>
				<div id="bfwc-expiration-month" class="hosted-field"></div>
			</div>
			<div class="bfwc-field-container field-year">
				<div id="bfwc-expiration-year" class="hosted-field"></div>
			</div>
			<?php if($cvv_enabled):?>
			<div class="bfwc-field-container field-cvv">
				<label><?php _e(bt_manager()->get_option('donation_card_cvv_label'), 'braintree-payments')?></label>
				<div id="bfwc-cvv" class="hosted-field"></div>
			</div>
			<?php endif;?>
	</div>
	<?php if($postal_enabled):?>
		<div class="bfwc-form-wrapper">
			<div class="bfwc-form-wrapper">
				<div class="bfwc-field-container field-postal">
					<label><?php _e(bt_manager()->get_option('donation_card_postal_label'), 'braintree-payments')?></label>
					<div id="bfwc-postal-code" class="hosted-field"></div>
				</div>
			</div>
		</div>
	<?php endif;?>
</div>