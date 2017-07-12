<main>
	<?php do_action("braintree_gateway_{$current_tab}_title")?>
	<div class="container">
	<div class="row">
		<form method="post" class="col s12 m6 l4">
			<div class="card-panel white">
				<div class="promo center">
					<i class="material-icons">https</i>
					<p class="black-text">
							<?php _e('Test your TLS connection. If your server is using TLS 1.1 or 1.0 you will receive an error message.', 'braintree-payments' )?>
						</p>
					<button class="btn blue waves-effect" name="braintree_tls_test">
						<?php include bt_manager()->plugin_admin_path() . 'html-helpers/pre-loader.php'?>
						<?php _e('Test TLS', 'braintree-payments' )?>
					</button>
				</div>
			</div>
		</form>
	</div>
</div>
</main>