<h1><?php printf(__('%s Options', 'braintree-payments' ), $gateway->method_title )?></h1>
<table class="form-table">
	<tbody>
		<tr>
			<th><label><?php _e('Settings Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-gateway-settings'?>"
				class="button"><?php _e('Settings Page')?></a>
				<p><?php _e('The settings page is where you enter your API keys, select your payment form style, and configure the behavior of the plugin.', 'braintree-payments' )?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('Logs Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-gateway-logs'?>"
				class="button"><?php _e('Logs Page')?></a>
				<p><?php _e('The logs page is useful when you want to view recorded activity on your site such as when a payment failed for a customer and why.', 'braintree-payments' )?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('TLS 1.2 Test Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-tls-test'?>"
				class="button"><?php _e('Logs Page')?></a>
				<p><?php _e('Starting in January of 2017, Braintree\'s servers will no longer accept https connections which use any protocol less than TLS 1.2. You can test your
						server to ensure your version of openssl is using TLS 1.2.', 'braintree-payments' )?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('Webhook Test Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-webhook-test'?>"
				class="button"><?php _e('Webhook Test Page')?></a>
				<p><?php _e('Webhooks are used to synchronize your Wordpress site with Braintree. You can test your webook settings by triggering different notifications on this page.', 'braintree-payments' )?>
			</td>
		</tr>
		<tr>
			<th><label><?php _e('Messages Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-messages-page'?>"
				class="button"><?php _e('Messages Page')?></a>
				<p><?php _e('On this page you can customize the standard Braintree messages that are returned by Braintree when payment fails, etc. By customizing these messages you can control the language and what is presented to your customers on the frontend.', 'braintree-payments' )?>
			</td>
		</tr>
			<tr>
			<th><label><?php _e('Donations Page')?></label></th>
			<td><a target="_blank"
				href="<?php echo admin_url() . 'admin.php?page=braintree-donations-page'?>"
				class="button"><?php _e('Donations Page')?></a>
				<p><?php _e('This is a link to your Donations. You can perform different actions related to the donations such as refunds, voids, captures, etc.', 'braintree-payments' )?>
			</td>
		</tr>
	</tbody>
</table>
<?php $gateway->admin_options_extended()?>
