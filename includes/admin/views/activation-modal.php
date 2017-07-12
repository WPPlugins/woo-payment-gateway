<div id="activation-modal" class="modal">
	<div class="modal-content">
		<h4 class="thin"><?php _e('Braintree Plugin Instructions', 'braintree-payments' )?></h4>
		<p>
			<?php
			_e( 'Thank you for trying our plugin! This is the Free version which will allow you to test all of the functionality available using Braintree\'s <a target="_blank" href="https://www.braintreepayments.com/sandbox">Sandbox Environment</a>. When you are ready to accept live payments, you will need to purchase a license from <a target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/"><strong>Payment Plugins</strong></a>.', 'braintree-payments' )?>
		</p>
		<p>
			<?php _e('', 'braintree-payments' )?>
		</p>
		<p><?php printf(__('There are configuration videos and helper text next to each setting. Our <a target="_blank" href="https://support.paymentplugins.com"><strong>Help Center</strong></a> contains documentation on how to configure and setup the plugin should you have additional questions.', 'braintree-payments' ))?></p>
		<h5 class="thin"><?php _e('First Steps', 'braintree-payments' )?></h5>
		<div class="row">
			<div class="col s12">
				<ol class="">
					<li class="collection-item">
						<h5 class="thin"><?php _e('Configure API Keys')?></h5>
						<p>
							<?php
							printf( __( 'In order for your Wordpress site to communicate with Braintree, you must configure your API keys. 
									To do this, navigate to the <a target="_blank" href="%s">API Settings page</a>. You can watch our helper video on that page which guides you through where to find your API keys.', 'braintree-payments' ), esc_url( admin_url() . 'admin.php?page=braintree-gateway-settings' ) )?>
						</p>
					</li>
					<li class="collection-item">
						<h5 class="thin"><?php _e('Test Connection', 'braintree-payments' )?></h5>
						<p>
							<?php
							_e( 'Once you have configured your API keys, you can test your connection to ensure your site can communicate with Braintree. If setup correctly, you will receive
							a success message.', 'braintree-payments' )?>
						</p>
					</li>
					<?php if(bt_manager()->is_woocommerce_active()):?>
					<li class="collection-item">
						<h5 class="thin"><?php _e('Configure WooCommerce', 'braintree-payments' )?></h5>
						<p><?php printf(__('You can configure options related to <a target="blank" href="%s">WooCommerce</a> such as how the plugin displays on the checkout page, select your form design, activate PayPal and Apple Pay, etc.', 'braintree-payments' ), esc_url(admin_url() . 'admin.php?page=braintree-gateway-settings&tab=checkout-settings'))?></p>
					</li>
					<?php endif;?>
					<li class="collection-item">
						<h5 class="thin"><?php _e('Test Using Sandbox', 'braintree-payments' )?></h5>
						<p><?php printf(__('You can test this plugin using a <a target="_blank" href="https://www.braintreepayments.com/sandbox">Braintree Sandbox</a> account for as long as you want for free. Test credit card numbers and test instructions can be found on the <a target="_blank" href="%s">Test Data</a> page.', 'braintree-payments' ), esc_url(admin_url() . 'admin.php?page=braintree-test-data-page'))?></p>
					</li>
					<li class="collection-item">
						<h5 class="thin"><?php _e('Purchasing A License', 'braintree-payments' )?></h5>
						<p><?php _e('We hope you like the functionality this plugin offers enough to purchase a license! If you want to accept live payments on your site, you will need to purchase a license from <a target="_blank" href="https://wordpress.paymentplugins.com/product-category/braintree-plugins/">Payment Plugins</a>.')?></p>
					</li>
				</ol>
			</div>
		</div>
	</div>
	<div class="modal-footer">
		<a href="#!"
			class="modal-action modal-close waves-effect waves-green btn-flat "><?php _e('Got it!', 'braintree-payments' )?></a>
	</div>
</div>