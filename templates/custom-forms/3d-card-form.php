<?php 
/**
 * @version 2.6.9
 */
?>
<div class="card-form">
	<div class="card-form__inner cf">
		<div class="card-form__element"
			data-input-text="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-card-number" data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>"
							class="card-form__input card-form__hosted-field">
							<span class="bfwc-card-type"></span>
						</div>
					</form>
				</li>
			</ul>
		</div>
		<?php if(bwc_cvv_field_enabled()):?>
			<div class="card-form__element half"
			data-input-text="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-cvv" data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>"
							class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php endif;?>
		<div class="card-form__element half"
			data-input-text="<?php bwc_custom_form_text('card_expiration_date_placeholder', __('MM / YY', 'braintree-payments'))?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-expiration-date" data-placeholder="<?php bwc_custom_form_text('card_expiration_date_placeholder', __('MM / YY', 'braintree-payments'))?>"
							class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php if(bwc_postal_code_enabled()):?>
		<div class="card-form__element"
			data-input-text="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>">
			<ul class="card-form__layers">
				<li class="card-form__layer">
					<form action="">
						<div id="bfwc-postal-code" data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>"
							class="card-form__input card-form__hosted-field"></div>
					</form>
				</li>
			</ul>
		</div>
		<?php endif;?>
	</div>
</div>
