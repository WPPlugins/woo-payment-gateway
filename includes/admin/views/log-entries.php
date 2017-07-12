<?php
bt_manager()->log->initialize_log_entries(); // Logs need to be loaded.
?>
<main>
	<section class="">
		<div class="container">
			<div class="row">
				<div class="input-field col s12 m12 l12">
					<h1 class="thin"><?php _e('Log Entries')?></h1>
					<p>
						<?php 
							_e('This page contains the log entries that keep track of all activity that occurs in the plugin such as transcations, refunds, etc.
                        	The logs are a great way to troubleshoot errors or for auditing purposes. You can export the logs for your records.', 'braintree-payments' )
						?>
					</p>
				</div>
			</div>
		</div>
	</section>
	<div class="container">
	<div class="card">
		<div class="card-content">
			<div class="row">
				<div class="col s12 m12 l12">
					<form method="post">
                			<?php wp_nonce_field('braintree-gateway-delete-logs')?>
                			<button class="btn right"
							name="braintree_gateway_delete_logs">
							<?php include bt_manager()->plugin_admin_path() . 'html-helpers/pre-loader.php'?>
							<?php echo __('Delete Logs', 'stripe_gateway')?></button>
					</form>
				</div>
			</div>
			<div class="row">
				<div class="col s12 m12 l12">
					<table class="bordered log-entries" id="log-entries">
						<thead class="grey lighten-3">
							<tr>
								<th><?php echo __('Type', 'stripe_gateway')?></th>
								<th><?php echo __('Time', 'stripe_gateway')?></th>
								<th><?php echo __('User ID', 'braintree-payments' )?></th>
								<th class="error-code"><?php echo __('Error Codes', 'braintree-payments' )?></th>
								<th><?php echo __('Message', 'stripe_gateway')?></th>
							</tr>
						</thead>
						<tbody>
                        	<?php																
							foreach ( bt_manager()->log->logs as $log ) {
								foreach ( $log as $entry ) {
									if ( ! empty( $entry ) ) {
										$type = $entry[ 'type' ]?>
                        		<tr>
									<td
									class="<?php if($type === 'success'){ echo 'green lighten-4';}elseif($type === 'error'){echo 'red lighten-4';}elseif($type === 'info'){echo 'blue lighten-4';}?>"><?php echo $entry['type']?></td>
									<td class="time-td"><?php echo $entry['time']?></td>
									<td><?php $entry['user_id'] ? printf('<a target="_blank" href="'.get_edit_user_link($entry['user_id']).'">%d</a>', $entry['user_id']) : _e('No User', 'braintree-payments' )?></td>
									<td><?php																	
									if ( $type === 'error' ) {
										if ( ! empty( $entry[ 'errors' ] ) ) {
											foreach ( $entry[ 'errors' ] as $code ) {
												?>
                            					<a
										href="https://developers.braintreepayments.com/reference/general/validation-errors/all/php"
										target="_blank"><?php echo $code?></a>
                            							 <?php
																
																}
															} else {
																echo __( 'n/a', 'braintree-payments' );
															}
														} else {
															echo __( 'n/a', 'braintree-payments' );
														}
														?>
                        						</td>
								<td>
									<div class="log-message"><?php echo $entry['message']?></div>
								</td>
							</tr>
                        			<?php															
											}
										}
									}
									?>
                        	</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
</main>