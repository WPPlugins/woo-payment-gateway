<div class="row">
	<div class="col s12 m12 l12">
		<ul class="collection">
			<li class="collection-item">
					<?php _e('Create an Apple Pay Merchant ID', 'braintree-payments' )?>
						<p class="thin">
								<?php _e('Sign in to your <a target="_blank" href="https://developer.apple.com/account/">Apple Pay Account</a> and click <strong>Certificates, IDs & Profiles.</strong>')?>
							</p>
				<p class="thin">
								<?php _e('On the left navigation bar, click <strong>Merchant IDs</strong>')?>
						</p>
				<p class="thin">
								<?php _e('On the upper right hand corner of the page, click the <i class="material-icons grey-text">add</i> button.', 'braintree-payments' )?>
						</p>
				<p class="thin">
							<?php _e('Add a description and ID and click <strong>continue</strong> and then click <strong>Register</strong>.', 'braintree-payments' )?>
						</p>
			</li>
			<li class="collection-item">
				<?php _e('Create An Apple Pay Certificate', 'braintree-payments' )?>
				<p class="thin">
					<?php _e('On the left navigation bar, under Certificates, click <strong>Production</strong>.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('In the upper right hand corner, click the <i class="material-icons grey-text">add</i> button.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Under the <strong>Production</strong> section, scroll near the bottom and select the radio button <strong>Apple Pay Certificate</strong> and click <strong>continue</strong>.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Select the Merchant ID that you created, and click <strong>continue</strong> then <strong>Create Certificate</strong>.', 'braintree-payments' )?>
				<p>
			</li>
			<li class="collection-item">
				<?php _e('Download Certificate Signing Request', 'braintree-payments' )?>
				<p class="thin">
					<?php _e('Sign in to your Braintree account and navigate to <strong>Settings</strong> > <strong>Processing</strong>. Click enable under the Accept Apple Pay header.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Click the <strong>Download a Certificate Signing Request</strong> link. Once downloaded, navigate back to the Apple Developer page.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Choose the downloaded CSR from Braintree and upload it on the Apple Developer page and click <strong>continue</strong>.')?>
				</p>
				<p class="thin">
					<?php _e('Download the new Apple Pay certifite and navigate back to the Braintree Control Panel.')?>
				</p>
			</li>
			<li class="collection-item">
				<?php _e('Upload Apple Pay Certificate', 'braintree-payments' )?>
				<p class="thin">
					<?php _e('Choose the Apple Pay certificate you downloaded and then upload it on the Braintree Apple Pay page.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Enter the domain name of your website that will be using Apple Pay.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php _e('Download the <strong>Domain Association File</strong>.', 'braintree-payments' )?>
				</p>
				<p class="thin">
					<?php printf(__('On the server that hosts your Wordpress site, add a directory called well-known and add the file there. It should be accessible to <strong>%s</strong>.', 'braintree-payments' ), trailingslashit(get_site_url()) . '.well-known/apple-developer-merchantid-domain-association')?>
				</p>
			</li>
		</ul>
	</div>
</div>