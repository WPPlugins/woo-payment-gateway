<?php
/**
 * @version 2.6.9
 */
?>
<div class="card-form">
	<div class="card-form__inner cf">
		<div class="card-form__element"
			data-input-text="<?php _e(bt_manager()->get_option('donation_card_number_placeholder'), 'braintree-payments')?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-card-number"
							class="card-form__input card-form__hosted-field"><span class="bfwc-card-type"></span>
						</div>
					</form>
				</li>
			</ul>
		</div>
		<?php if(bwc_cvv_field_enabled()){?>
			<div class="card-form__element half"
			data-input-text="<?php _e(bt_manager()->get_option('donation_card_cvv_placeholder'), 'braintree-payments')?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-cvv" class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php }?>
		<div class="card-form__element half"
			data-input-text="<?php _e(bt_manager()->get_option('donation_card_expiration_date_placeholder'), 'braintree-payments')?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-expiration-date"
							class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php if(bwc_postal_code_enabled()){?>
			<div class="card-form__element"
			data-input-text="<?php _e(bt_manager()->get_option('donation_card_postal_placeholder'), 'braintree-payments')?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-postal-code"
							class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php }?>
	</div>
</div>
