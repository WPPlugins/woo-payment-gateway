<?php $merchant_accounts = bt_manager()->get_option($key)?>
<div class="row merchant-accounts">
	<div class="input-field col s12 m8 l8">
		<select class="currencies bfwc-select2" id="<?php echo $key?>_currency" style="width: 100%">
			<option value="" disabled selected><?php echo __('Merchant Account Currency', 'braintree-payments' )?></option>
			<?php
			
			if ( function_exists( 'get_woocommerce_currencies' ) ) {
				foreach ( get_woocommerce_currencies() as $symbol => $description ) {
					?>
    			<option value="<?php echo $symbol?>"><?php echo ucwords($description)?></option>
			<?php
				
				}
			} else {
				foreach ( braintree_get_currencies() as $symbol => $description ) {
					?>
    			<option value="<?php echo $symbol?>"><?php echo $description?></option>
			<?php }}?>
		</select>
	</div>
	<div class="input-field col s12 m4 l4">
		<a href="#" select-id="#<?php echo $key?>_currency"
			<?php braintree_get_html_field_attributes($data['attributes'], true)?>
			class="waves-effect waves-light btn light-blue lighten-2 add-merchant-account"><?php echo __('Add', 'braintree-payments' )?></a>
	</div>
	<?php
	
	if ( ! empty( $merchant_accounts ) ) {
		foreach ( $merchant_accounts as $currency => $account ) {
			?>
	  <div class="col s8">
		<label><?php echo sprintf($data['label'], $currency)?></label>
		<input type="text"
			name="<?php echo $field_key?>[<?php echo $currency?>]"
			value="<?php echo $account?>" />
		<i class="material-icons trash-merchant-account">delete</i>
	</div>
	<?php } }?>
</div>