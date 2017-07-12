<main id="webhook-main">
	<section>
		<div class="container">
			<div class="row">
				<div class="col s12">
					<h1 class="thin"><?php _e('Webhook Test', 'braintree-payments')?></h1>
					<p>
						<?php 
							_e('On this page you can perform webhooks tests to ensure you have configured webhooks correctly.', 'braintree-payments' );
						?>
						<ul class="">
							<ol class="collection-item"><?php _e('Select the type of webhook you wish to trigger.', 'braintree-payments' )?></ol>
							<ol class="collection-item"><?php _e('Enter the value in the dropdown field. For example, for a subscription, enter the subscription Id.')?></ol>
							<ol class="collection-item"><?php _e('Click Test Connection. If successful, you will receive a success message.', 'braintree-payments' )?></ol>
						</ul>
					</p>
				</div>
			</div>
			<div class="row">
				<div class="col s12">
					<div class="card-panel">
						<label><?php _e('Copy and paste this url when creating a webhook within Braintree.')?></label>
					<input type="text" value="<?php echo get_rest_url(null, bt_manager()->api->get_path())?>">
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="container">
		<div class="card">
			<div class="card-content">
				<div class="row">
					<form class="col s12" id="webhook-form" method="post">
						<?php wp_nonce_field('webhook-test', '_bfwc_webhook_test')?>
						<div class="row">
							<div class="input-field col s12">
								<label for="braintree_admin_webhooks"><?php _e('Webhook To Test')?></label>
								<select class="bfwc-select2" id="bfwc_admin_webhook" name="bfwc_admin_webhook">
									<?php foreach(bwc_admin_webhooks() as $id => $webhook){?>
										<option value="<?php echo $id?>"><?php echo $webhook?></option>
									<?php }?>
								</select>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<label for="subscription_id"><?php _e('Subscription To Test')?></label>
								<input type="text" id="subscription_id" name="subscription_id">
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12">
								<label for="transaction_order"><?php _e('Order to Test')?></label>
								<input type="text" id="transaction_order" name="transaction_order">
							</div>
						</div>
						<div class="row">
							<div class="col s12">
								<button class="webhook-button waves-effect waves-light btn save-btn braintree-green">
									<?php _e('Test Webhook', 'braintree-payments' )?>
									<?php //include bt_manager()->plugin_admin_path() . 'html-helpers/pre-loader.php'?>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>