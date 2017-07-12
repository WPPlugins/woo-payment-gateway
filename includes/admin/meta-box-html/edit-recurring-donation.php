<style type="text/css">
#post-body-content, #titlediv {
	display: none
}
</style>
<div class="donation-wrapper">
	<h5><?php echo __('General Details', 'braintree-payments' )?></h5>
</div>
<div class="row">
	<div class="col s12 m12 l12">
		<div>
			<label><?php echo __('Donation Status', 'braintree-payments' )?></label>
		</div>
		<select name="donation_status">
			<?php foreach(bfwcd_get_donation_statuses() as $status => $text){?>
				<option value="<?php echo $status?>"
				<?php selected($donation->get_status(), $status)?>><?php echo $text?></option>
			<?php }?>
		</select>
	</div>
	<?php
	
	if ( ! empty( $donation->user_id ) ):
		$user = get_user_by( 'id', $donation->user_id );
		?>
	<div class="col s12">
		<label><?php echo __('Customer', 'braintree-payments' )?></label>
		<div class="white">
			<span><?php echo $user->display_name?></span>
		</div>
	</div>
	<?php endif;?>
</div>
<div class="row">
	<div class="col s12 m6 l6">
		<div class="card-panel">
			<div class="row">
				<div class="col s12">
					<span class="">
						<strong><?php echo __('Billing Info', 'braintree-payments' )?></strong>
					</span>
					<a href="#" class="right edit_donation_address">
						<i class="material-icons">edit</i>
					</a>
					<a href="#" class="right close_edit_address">
						<i class="material-icons">close</i>
					</a>
				</div>
			</div>
			<div class="row donation_address">
				<div class="col s12">
					<label><?php echo __('Address', 'braintree-payments' )?></label>
					<div><?php echo $donation->get_formatted_billing_address()?></div>
				</div>
			</div>
			<div class="row edit_donation_address">
			<?php foreach($donation->get_billing_address_fields() as $key => $field){?>
				<div class="input-field col s12 m6 l6">
					<label for="<?php echo $key ?>"><?php echo $field['label']?></label>
					<?php if($field['type'] === 'select'){?>
						<select id="<?php echo $key?>" name="<?php echo $key?>">
							<?php foreach(braintree_get_countries() as $code => $country){?>
								<option value="<?php echo $code?>"
								<?php selected($donation->$key, $code)?>><?php echo $country?></option>
							<?php }?>
						</select>
					<?php }else{?>
						<input type="text" id="<?php echo $key?>" name="<?php echo $key?>"
							value="<?php echo $donation->$key?>">
					<?php }?>
				</div>
			<?php }?>
			</div>
		</div>
	</div>
	<div class="col s12 m6 l6">
		<div class="card-panel">
			<span class="">
				<strong><?php echo __('Donation Message', 'braintree-payments' )?></strong>
			</span>
			<p><?php echo $donation->donation_message?></p>
		</div>
	</div>
	<div class="row">
		<div class="col s12">
			<ul class="collection">
				<li class="collection-item">
					<div>
        				<?php echo __('Donation Amount', 'braintree-payments' )?>
        				<span class="secondary-content"><?php echo sprintf('%s %s / %s', braintree_get_currency_symbol($donation->currency), $donation->amount, __('Month', 'braintree-payments' ))?></span>
					</div>
				</li>
				<li class="collection-item">
					<div>
        				<?php echo __('Merchant Account', 'braintree-payments' )?>
        				<span class="secondary-content"><?php echo $donation->merchant_account_id?></span>
					</div>
				</li>
				<li class="collection-item">
					<div>
        				<?php echo __('Payment Method', 'braintree-payments' )?>
        				<span class="secondary-content"><?php echo $donation->payment_method_title?></span>
					</div>
				</li>
			</ul>
		</div>
	</div>
	<div class="row">
		<div class="col s12">
			<div class="card-panel">
				<strong><?php _e('Donation Notes', 'braintree-payments' )?></strong>
				<table id="donation-notes-table" class="dataTable bordered">
					<thead>
						<tr>
							<th><?php _e('Note Id', 'braintree-payments' )?></th>
							<th><?php _e('Message', 'braintree-payments' )?></th>
							<th><?php _e('Creation Date', 'braintree-payments' )?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($donation->get_notes() as $note):?>
							<tr>
								<td><?php echo $note->comment_ID?></td>
								<td><?php echo $note->comment_content?></td>
								<td><?php echo $note->comment_date?></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col s12">
			<div class="card-panel">
				<strong>
					<span><?php echo __('Transactions', 'braintree-payments' )?></span>
				</strong>
				<table id="transaction-table" class="dataTable bordered">
					<thead>
						<tr>
							<th><?php echo __('Transaction ID', 'braintree-payments' )?></th>
							<th><?php echo __('Amount', 'braintree-payments' )?></th>
							<th><?php echo __('Type', 'braintree-payments' )?></th>
							<th><?php echo __('Created On', 'braintree-payments' )?></th>
							<th><?php echo __('Status', 'braintree-payments' )?></th>
						</tr>
					</thead>
					<tbody>
    					<?php
		
		foreach ( $braintree_subscription->transactions as $transaction ) {
			if ( $transaction->type === Braintree_Transaction::SALE ) {
				?>
    						
    						<tr>
							<td><?php echo $transaction->id?></td>
							<td><?php echo sprintf('%s %s', braintree_get_currency_symbol($transaction->currencyIsoCode), $transaction->amount)?></td>
							<td><?php echo $transaction->type?></td>
							<td><?php echo $transaction->createdAt->format('Y-m-d H:i:s')?></td>
							<td><?php echo $transaction->status?></td>
						</tr>
    					<?php } }?>
    				</tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col s12">
			<div class="card-panel">
				<strong>
					<span><?php echo __('Refunds', 'braintree-payments' )?></span>
				</strong>
				<table id="refunds-table" class="dataTable bordered">
					<thead>
						<tr>
							<th><?php echo __('Transaction ID', 'braintree-payments' )?></th>
							<th><?php echo __('Amount', 'braintree-payments' )?></th>
							<th><?php echo __('Type', 'braintree-payments' )?></th>
							<th><?php echo __('Created On', 'braintree-payments' )?></th>
							<th><?php echo __('Status', 'braintree-payments' )?></th>
						</tr>
					</thead>
					<tbody>
    				<?php
		
		foreach ( $braintree_subscription->transactions as $transaction ) {
			if ( $transaction->type === Braintree_Transaction::CREDIT ) {
				?>
    					<tr class="collection-item">
							<td><?php echo $transaction->id?></td>
							<td><?php echo sprintf('%s %s', braintree_get_currency_symbol($donation->currency), $transaction->amount)?></td>
							<td><?php echo $transaction->type?></td>
							<td><?php echo $transaction->createdAt->format('Y-m-d H:i:s')?></td>
							<td><?php echo $transaction->status?></td>
						</tr>
    				<?php } }?>
    				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
