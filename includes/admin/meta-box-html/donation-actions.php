<div class="row">
	<div class="section">
		<select name="braintree_donation_actions"
			id="braintree_donation_actions" style="width: 100%">
			<option value="" selected><?php _e('Donation Actions', 'braintree-payments' )?></option>
			<?php foreach(apply_filters('braintree_donation_actions', array()) as $action => $title){?>
				<option value="<?php echo $action?>"><?php echo $title?></option>
			<?php }?>
		</select>
	</div>
	<section id="donation_refund_section" data-show-if="refund_donation">
		<div class="row">
			<div class="col s12">
				<label for="donation_refund_amount"><?php echo __('Refund Amount', 'braintree-payments' )?></label>
				<input type="text" id="donation_refund_amount"
					name="donation_refund_amount"
					value="<?php echo $donation->amount?>" />
			</div>
		</div>
	</section>
	<section id="donation_capture_section" data-show-if="capture_donation">
		<div class="row">
			<div class="col s12">
				<label for="donation_capture_amount"><?php echo __('Capture Amount', 'braintree-payments' )?></label>
				<input type="text" id="donation_capture_amount"
					name="donation_capture_amount"
					value="<?php echo $donation->amount?>" />
			</div>
		</div>
	</section>
	<div class="divider"></div>
	<div class="section">
		<button class="btn"><?php echo __('Save', 'braintree-payments' )?></button>
	</div>
</div>