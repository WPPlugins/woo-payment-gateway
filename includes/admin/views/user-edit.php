<?php
$sandbox_id = bt_manager()->get_customer_id_for_environment( $user->ID, 'sandbox' );
$prod_id = bt_manager()->get_customer_id_for_environment( $user->ID, 'production' );

$prod_active = bt_manager()->get_environment() === 'production';

$sandbox_methods = get_user_meta($user->ID, 'braintree_sandbox_payment_methods', true);
$sandbox_methods = empty($sandbox_methods) ? array() : $sandbox_methods;

$production_methods = get_user_meta($user->ID, 'braintree_production_payment_methods', true);
$production_methods = empty($production_methods) ? array() :$production_methods;
?>
<h2><?php _e('Braintree Fields', 'braintree-payments' )?></h2>
<table class="form-table">
	<tbody>
		<tr>
			<th>
				<label for="braintree_sandbox_customer_id"><?php _e('Sandbox Customer ID', 'braintree-payments' )?></label>
			</th>
			<td>
				<input type="text" class="regular-text"
					id="braintree_sandbox_customer_id"
					name="braintree_sandbox_customer_id"
					value="<?php echo $sandbox_id?>">
			</td>
		</tr>
		<tr>
			<th>
				<label for=""><?php _e('Sandbox Payment Methods', 'braintree-payments')?></label>
			</th>
			<td>
				<select class="bfwc-select2 regular-text" multiple name="braintree_sandbox_customer_payment_methods[]">
					<?php foreach($sandbox_saved_methods as $token => $method):?>
						<option value="<?php echo $token?>" <?php selected(true, true)?>><?php echo braintree_get_payment_method_title_from_array($method)?></option>
					<?php endforeach;?>
				</select>
			</td>
		</tr>
		<tr>
			<th>
				<label>
					<a href="#" class="bfwc-open-form button" data-environment="sandbox"><?php _e('Add Sandbox Payment Method', 'braintree-payments')?></a>
					<input type="hidden" id="bfwc_sandbox_payment_nonce" name="bfwc_sandbox_payment_nonce"/>
				</label>
			</th>
		</tr>
	</tbody>
</table>