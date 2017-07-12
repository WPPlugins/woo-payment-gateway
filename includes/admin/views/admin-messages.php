<section>
	<div class="container">
		<div class="row">
			<div class="col s12">
				<h1 class="thin"><?php _e('Customize Messages', 'braintree-payments' )?></h1>
				<p>
					<?php _e('On this page you can customize the error message text and control what your customers see on the frontend when payments fail, cards are declined, etc.
							Every error code that Braintree can return is included here. Most codes that a customer would receive will be related to <strong>Gateway Rejected</strong>, <strong>Tokenization</strong> & <strong>Process Declined</strong> codes.', 'braintree-payments' )?>
				</p>
			</div>
		</div>
	</div>
</section>
<div class="container">
	<div class="card">
		<div class="card-content">
			<div class="row">
				<form method="post" class="col s12">
					<input type="hidden" name="bfwc_admin_messages">
					<div class="row">
						<div class="col s12 m12 l12">
							<table class="braintree-settings-table">
								<tbody>
									<tr>
										<th class="title-description"><?php _e('Message Code', 'braintree-payments' )?></th>
										<td>
											<select class="bfwc-messages-select2" id="bfwc_message_code">
												<?php foreach(bfwc_get_error_messages() as $type => $messages):?>
												<optgroup label="<?php echo bfwc_error_code_type_nicename($type)?>">
													<?php foreach($messages as $code => $text):?>
														<option value="<?php echo $code?>"><?php printf('%s - %s', $code, $text)?></option>
													<?php endforeach;?>
												</optgroup>
												<?php endforeach;?>
											</select>
										</td>
									</tr>
									<tr>
										<th class="title-description"><?php _e('Message Text', 'braintree-payments' )?></th>
										<td><textarea id="bfwc_message" style="width: 100%"></textarea></td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
					<div class="row">
						<div class="col s12 m6 l4">
							<button id="bfwc_save_message" name="bfwc_save_message" class="waves-effect waves-light btn save-btn braintree-green">
								<?php bfwc_admin_get_template('html-helpers/pre-loader.php')?>
								<?php _e('Save Changes', 'braintree-payments' )?>
							</button>
						</div>
						<div class="col s12 m6 l4">
							<button id="bfwc_reset_messages" name="bfwc_reset_messages" class="waves-effect waves-light btn save-btn light-blue darken-3">
								<?php bfwc_admin_get_template('html-helpers/pre-loader.php')?>
								<?php _e('Reset Messages', 'braintree-payments' )?>
							</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>