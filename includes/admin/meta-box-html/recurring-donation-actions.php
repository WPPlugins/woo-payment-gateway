<?php
$sale_transactions = array_filter( $braintree_subscription->transactions, 'braintree_filter_sale_transactions' );
$credit_transactions = array_filter( $braintree_subscription->transactions, 'braintree_filter_credit_transactions' );
?>
<div class="row">
	<div class="section">
		<select name="braintree_recurring_donation_actions"
			id="braintree_donation_actions">
			<option value="" selected><?php echo __('Donation Actions', 'braintree-payments' )?></option>
			<?php foreach(apply_filters('braintree_recurring_donation_actions', array()) as $action => $title){?>
				<option value="<?php echo $action?>"><?php echo $title?></option>
			<?php }?>
		</select>
	</div>
	<section id="donation_refund_section" data-show-if="refund_donation">
		<div class="row">
			<div class="col s12">
				<label><?php echo __('Transactions', 'braintree-payments' )?></label>
				<select id="recurring_donation_transaction"
					name="recurring_donation_transaction">
					<?php foreach($sale_transactions as $transaction){?>
						<option value="<?php echo $transaction->id?>"><?php echo $transaction->id?></option>
					
					<?php }?>
				</select>
			</div>
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
				<label for="donation_capture_amount"><?php echo __('Capture Donation', 'braintree-payments' )?></label>
				<input type="text" id="donation_capture_amount"
					name="donation_capture_amount"
					value="<?php echo $donation->amount?>" />
			</div>
		</div>
	</section>
	<section id="donation_void_section" data-show-if="void_donation">
		<div class="row">
			<div class="col s12">
				<label><?php echo __('Void Donation', 'braintree-payments' )?></label>
				<select id="transaction_to_void" name="transaction_to_void">
					<optgroup label="<?php echo __('Sale Transactions', 'braintree-payments' )?>">
					<?php foreach($sale_transactions as $transaction){?>
						<option value="<?php echo $transaction->id?>"><?php echo $transaction->id ?></option>
					<?php }?>
					</optgroup>
					<optgroup
						label="<?php echo __('Credit Transactions', 'braintree-payments' )?>">
						<?php foreach($credit_transactions as $transaction){?>
						<option value="<?php echo $transaction->id?>"><?php echo $transaction->id ?></option>
					<?php }?>
					</optgroup>
				</select>
			</div>
		</div>
	</section>
	<div class="divider"></div>
	<div class="section">
		<button class="btn"><?php echo __('Save', 'braintree-payments' )?></button>
	</div>
</div>