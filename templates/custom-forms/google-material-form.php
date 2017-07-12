<?php 
/**
 * @version 2.6.9
 */
?>
<div class="panel">
	<header class="panel__header">
		<h1><?php _e('Card Payment', 'braintree-payments' )?></h1>
	</header>
	<div class="panel__content">
		<div class="textfield--float-label card--number-float">
			<label class="hosted-field--label" for="bfwc-card-number">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
						<path d="M0 0h24v24H0z" fill="none" />
						<path
							d="M20 4H4c-1.11 0-1.99.89-1.99 2L2 18c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V6c0-1.11-.89-2-2-2zm0 14H4v-6h16v6zm0-10H4V6h16v2z" /></svg>
				</span>
				<?php bwc_custom_form_text('card_number_label', __('Card Number', 'braintree-payments'))?> </label>
			<div id="bfwc-card-number" data-placeholder="<?php bwc_custom_form_text('card_number_placeholder', __('Card Number', 'braintree-payments'))?>" class="hosted-field">
				<span class="bfwc-card-type"></span>
			</div>
		</div>

		<div class="textfield--float-label exp--date-float">
			<label class="hosted-field--label" for="bfwc-expiration-date">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
						<path
							d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" /></svg>
				</span> <?php bwc_custom_form_text('card_expiration_date_label', __('Exp Date', 'braintree-payments'))?>
			</label>
			<div id="bfwc-expiration-date" data-placeholder="<?php bwc_custom_form_text('card_expiration_date_placeholder', __('MM / YY', 'braintree-payments'))?>" class="hosted-field"></div>
		</div>

		<?php if(bwc_cvv_field_enabled()):?>
			<div class="textfield--float-label cvv--float">
			<label class="hosted-field--label" for="bfwc-cvv">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
							<path
							d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" /></svg>
				</span> <?php bwc_custom_form_text('card_cvv_label', __('CVV', 'braintree-payments'))?>
				</label>
			<div id="bfwc-cvv" data-placeholder="<?php bwc_custom_form_text('card_cvv_placeholder', __('CVV', 'braintree-payments'))?>" class="hosted-field"></div>
		</div>
		<?php endif;?>
		<?php if(bwc_postal_code_enabled()):?>
			<div class="textfield--float-label postal--float">
			<label class="hosted-field--label" for="bfwc-postal-code">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
		    					<path
							d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" /></svg>
				</span> <?php bwc_custom_form_text('card_postal_label', __('Postal Code', 'braintree-payments'))?>
				</label>
			<div id="bfwc-postal-code" data-placeholder="<?php bwc_custom_form_text('card_postal_placeholder', __('Postal Code', 'braintree-payments'))?>" class="hosted-field"></div>
		</div>
		<?php endif;?>
		<?php if(bwc_display_save_payment_method()):?>
			<div class="textfield--float-label save--card-float">
			<label class="hosted-field--label" for="bfwc-card-number">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">>
						    <path
							d="M12 17c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zm6-9h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM8.9 6c0-1.71 1.39-3.1 3.1-3.1s3.1 1.39 3.1 3.1v2H8.9V6zM18 20H6V10h12v10z" />
						</svg>
				</span><?php bwc_custom_form_text('card_save_label', __('Save', 'braintree-payments'))?>
				</label>
			<div class="hosted-field">
				<input type="checkbox" id="bfwc_save_credit_card"
					name="bfwc_save_credit_card">
				<label class="bfwc-save-label" for="bfwc_save_credit_card"></label>
			</div>
		</div>
		<?php endif;?>
	</div>
</div>