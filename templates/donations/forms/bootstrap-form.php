<?php
/**
 * @version 2.6.9
 */
?>
<div class="row">
	<div class="form-group col-xs-8">
		<label class="control-label"><?php _e(bt_manager()->get_option('donation_card_number_label'), 'braintree-payments')?></label>
		<!--  Hosted Fields div container -->
		<div class="form-control" id="bfwc-card-number"><span class="bfwc-card-type"></span></div>
		<span class="helper-text"></span>
	</div>
	<div class="form-group col-xs-4">
		<div class="row">
			<label class="control-label col-xs-12"><?php _e(bt_manager()->get_option('donation_card_expiration_date_label'), 'braintree-payments')?></label>
			<div class="col-xs-6">
				<!--  Hosted Fields div container -->
				<div class="form-control" id="bfwc-expiration-month"></div>
			</div>
			<div class="col-xs-6">
				<!--  Hosted Fields div container -->
				<div class="form-control" id="bfwc-expiration-year"></div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<?php if(bwc_cvv_field_enabled()){?>
		<div class="form-group col-xs-6">
		<label class="control-label"><?php _e(bt_manager()->get_option('donation_card_cvv_label'), 'braintree-payments')?></label>
		<!--  Hosted Fields div container -->
		<div class="form-control" id="bfwc-cvv"></div>
	</div>
	<?php }?>
	<?php if(bwc_postal_code_enabled()){?>
		<div class="form-group col-xs-6">
		<label class="control-label"><?php _e(bt_manager()->get_option('donation_card_postal_label'), 'braintree-payments')?></label>
		<!--  Hosted Fields div container -->
		<div class="form-control" id="bfwc-postal-code"></div>
	</div>
	<?php }?>
</div>
