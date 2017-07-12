<?php 
global $current_tab;
bfwc_admin_get_template('views/admin-header.php', array('tabs' => $tabs))?>
<main>
	<?php do_action("braintree_gateway_{$current_tab}_title")?>
	<div class="container">
		<div class="card">
			<div class="card-content">
				<div class="row">
					<form class="col s12 m12 l12" method="POST" action="<?php echo add_query_arg(array('page'=>$current_page, 'tab' => $current_tab), admin_url('admin.php'))?>">
						<div class="row">
							<div class="col s12 m12 l12">
								<table class="braintree-settings-table">
									<tbody>
	                					<?php
										do_action( "output_braintree_{$current_tab}_settings_page", true );
										?>
	                				</tbody>
								</table>
							</div>
						</div>
						<div class="row">
							<div class="input-field col s12 m6 l4">
								<button class="waves-effect waves-light btn save-btn braintree-green">
									<?php bfwc_admin_get_template('html-helpers/pre-loader.php')?>
									<?php echo apply_filters('bfwc_admin_settings_button_text', __('Save', 'braintree-payments' ), $current_tab)?>
								</button>
							</div>
								<?php do_action('bfwc_admin_after_save_button', $current_tab)?>
						</div>
						<?php do_action('bfwc_admin_settings_end')?>
					</form>
				</div>
			</div>
		</div>
	</div>
</main>