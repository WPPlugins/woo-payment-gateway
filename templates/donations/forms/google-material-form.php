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
				<?php _e(bt_manager()->get_option('donation_card_number_label'), 'braintree-payments')?> </label>
			<div id="bfwc-card-number" class="hosted-field"><span class="bfwc-card-type"></span></div>
		</div>

		<div class="textfield--float-label exp--date-float">
			<label class="hosted-field--label" for="bfwc-expiration-date">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
						<path
							d="M9 11H7v2h2v-2zm4 0h-2v2h2v-2zm4 0h-2v2h2v-2zm2-7h-1V2h-2v2H8V2H6v2H5c-1.11 0-1.99.9-1.99 2L3 20c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z" /></svg>
				</span> <?php _e(bt_manager()->get_option('donation_card_expiration_date_label'), 'braintree-payments')?>
			</label>
			<div id="bfwc-expiration-date" class="hosted-field"></div>
		</div>

		<?php if(bt_manager()->is_active('donation_cvv_field_enabled')){?>
			<div class="textfield--float-label cvv--float">
			<label class="hosted-field--label" for="bfwc-cvv">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
							<path
							d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z" /></svg>
				</span> <?php _e(bt_manager()->get_option('donation_card_cvv_label'), 'braintree-payments')?>
				</label>
			<div id="bfwc-cvv" class="hosted-field"></div>
		</div>
		<?php }?>
		<?php if(bt_manager()->is_active('donation_postal_field_enabled')){?>
			<div class="textfield--float-label postal--float">

			<label class="hosted-field--label" for="bfwc-postal-code">
				<span class="icon">
					<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
						viewBox="0 0 24 24">
	    					<path
							d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" /></svg>
				</span> <?php _e(bt_manager()->get_option('donation_card_postal_label'), 'braintree-payments')?>
				</label>
			<div id="bfwc-postal-code" class="hosted-field"></div>
		</div>
		<?php }?>
	</div>
</div>