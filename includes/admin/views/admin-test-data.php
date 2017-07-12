<div class="container">
	<div class="row">
		<div class="col s12">
			<h1 class="thin"><?php _e('Test Data / Instructions', 'braintree-payments' )?></h1>
			<p>
				<?php
				
				_e( 'To test this plugin, you can create a <a target="_blank" href="https://www.braintreepayments.com/sandbox">Braintree Sandbox</a> account. The sandbox environment 
						is exactly like production and will allow you to test all functionality that Braintree and this plugin offer.', 'braintree-payments' )?>
			</p>
		</div>
	</div>
	<h3 class="thin"><?php _e('Credit Card Numbers', 'braintree-payments' )?></h3>
	<div class="divider"></div>
	<p>
		<?php _e('The sandbox environment only accepts test credit card numbers. The following card numbers may be used to trigger specific responses:', 'braintree-payments' )?>
	</p>
	<h4 class="thin"><?php _e('No credit card errors', 'braintree-payments' )?></h4>
	<p>
		<?php _e('The following credit card values will not trigger specific credit card errors, but this does not mean that your test transaction will be successful. Values passed with the transaction (e.g. amount) can be used to trigger other types of gateway responses. See <a href="#test-amounts">Test Amounts</a> for more details.', 'braintree-payments' )?>
	</p>
	<section>
		<table class="bordered test-cards">
			<thead>
				<tr>
					<th><?php _e('Test Value', 'braintree-payments' )?></th>
					<th><?php _e('Card Type', 'braintree-payments' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">378282246310005</code></td>
					<td><span class="amex"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">371449635398431</code></td>
					<td><span class="amex"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">6011111111111117</code></td>
					<td><span class="discover"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">3530111333300000</code></td>
					<td><span class="jcb"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">6304000000000000</code></td>
					<td><span class="maestro"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">5555555555554444</code></td>
					<td><span class="master_card"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">2223000048400011</code></td>
					<td><span class="master_card"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4111111111111111</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4005519200000004</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4009348888881881</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4012000033330026</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4012000077777777</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4012888888881881</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4217651111111119</code></td>
					<td><span class="visa"></span></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4500600000000061</code></td>
					<td><span class="visa"></span></td>
				</tr>
			</tbody>
		</table>
	</section>
	<h3 class="thin"><?php _e('Unsuccessful credit card verification', 'braintree-payments' )?></h3>
	<p><?php _e('The following credit card numbers will simulate an unsuccessful card verification response. Verifying a card is different than creating a transaction. To trigger an unsuccessful transaction, adjust the amount of the transaction.', 'braintree-payments' )?></p>
	<div class="divider"></div>
	<section>
		<table class="bordered test-cards">
			<thead>
				<tr>
					<th><?php _e('Test Value', 'braintree-payments' )?></th>
					<th><?php _e('Card Type', 'braintree-payments' )?></th>
					<th><?php _e('Verification Response', 'braintree-payments' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">4000111111111115</code></td>
					<td><span class="visa"></span></td>
					<td><a target="_blank"
						href="https://developers.braintreepayments.com/reference/general/statuses#verification">processor
							declined</a></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">5105105105105100</code></td>
					<td><span class="master_card"></span></td>
					<td><a target="_blank"
						href="https://developers.braintreepayments.com/reference/general/statuses#verification">processor
							declined</a></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">378734493671000</code></td>
					<td><span class="amex"></span></td>
					<td><a target="_blank"
						href="https://developers.braintreepayments.com/reference/general/statuses#verification">processor
							declined</a></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">6011000990139424</code></td>
					<td><span class="discover"></span></td>
					<td><a target="_blank"
						href="https://developers.braintreepayments.com/reference/general/statuses#verification">processor
							declined</a></td>
				</tr>
				<tr>
					<td><code class="syntax-inline syntax-inline--theme">3566002020360505</code></td>
					<td><span class="jcb"></span></td>
					<td><a target="_blank"
						href="https://developers.braintreepayments.com/reference/general/statuses#verification">failed
							(3000)</a></td>
				</tr>
			</tbody>
		</table>
	</section>
	<h3 class="thin"><?php _e('Fraud tools', 'braintree-payments' )?></h3>
	<p><?php _e('This will only trigger a fraud response if you have fraud protection enabled in your sandbox and you have enabled fraud tools on the checkout settings page of the plugin.')?></p>
	<section>
		<table class="bordered test-cards">
			<thead>
				<tr>
					<th><?php _e('Card Type', 'braintree-payments' )?></th>
					<th><?php _e('Test Value', 'braintree-payments' )?></th>
					<th><?php _e('Status', 'braintree-payments' )?></th>
					<th><?php _e('Reason', 'braintree-payments' )?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><span class="visa"></span></td>
					<td><code class="syntax-inline syntax-inline--theme">4000111111111511</code></td>
					<td>gateway_rejected</td>
					<td>fraud</td>
				</tr>
			</tbody>
		</table>
	</section>
	<h3 id="test-amounts" class="thin"><?php _e('Test Amounts', 'braintree-payments' )?></h3>
	<p><?php
	_e( 'When working with transactions, you can pass specific amounts to simulate different processor responses. 
			If using WooCommerce, change your products proce to to trigger one of these validation errors.', 'braintree-payments' )?>
	</p>
	<table class="bordered">
		<thead>
			<tr>
				<th><?php _e('Amount', 'braintree-payments' )?></th>
				<th><?php _e('Authorization Response', 'braintree-payments' )?></th>
				<th><?php _e('Settlement Response', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>$0.01 - $1999.99</td>
				<td><em>Authorized</em></td>
				<td><em>Settled</em></td>
			</tr>
			<tr>
				<td>$2000.00 - $3000.99</td>
				<td><em>Processor Declined</em> with a <a
					href="/reference/general/processor-responses/authorization-responses">processor
						response</a> equal to the amount</td>
				<td>n/a</td>
			</tr>
			<tr>
				<td>$3001.00 - $4000.99</td>
				<td><em>Authorized</em></td>
				<td><em>Settled</em></td>
			</tr>
			<tr>
				<td>$4001.00 - $4001.99</td>
				<td><em>Authorized</em></td>
				<td><em>Settlement Declined</em> on <a
					href="https://developers.braintreepayments.com/reference/general/statuses#settlement-declined">certain
						transaction types</a> with a <a
					href="https://developers.braintreepayments.com/reference/general/processor-responses/settlement-responses">processor
						response</a> equal to the amount; <em>Settled</em> on all others</td>
			</tr>
			<tr>
				<td>$4002.00 - $4002.99</td>
				<td><em>Authorized</em></td>
				<td><em>Settlement Pending</em> on PayPal transactions with a <a
					href="https://developers.braintreepayments.com/reference/general/processor-responses/settlement-responses">processor
						response</a> equal to the amount; <em>Settled</em> on all others</td>
			</tr>
			<tr>
				<td>$4003.00 - $5000.99</td>
				<td><em>Authorized</em></td>
				<td><em>Settlement Declined</em> on <a
					href="https://developers.braintreepayments.com/reference/general/statuses#settlement-declined">certain
						transaction types</a> with a <a
					href="https://developers.braintreepayments.com/reference/general/processor-responses/settlement-responses">processor
						response</a> equal to the amount; <em>Settled</em> on all others</td>
			</tr>
			<tr>
				<td>$5001.00</td>
				<td><em>Gateway Rejected</em> with a reason of Application
					Incomplete</td>
				<td>n/a</td>
			</tr>
			<tr>
				<td>$5001.01</td>
				<td><em>Processor Declined</em> with a 2038 <a
					href="https://developers.braintreepayments.com/reference/general/processor-responses/authorization-responses">processor
						response</a></td>
				<td>n/a</td>
			</tr>
			<tr>
				<td>$5002.00 and up</td>
				<td><em>Authorized</em></td>
				<td><em>Settled</em></td>
			</tr>
		</tbody>
	</table>
	<h3 class="thin"><?php _e('AVS and CVV/CID responses', 'braintree-payments' )?></h3>
	<p><?php
	
	_e( 'These will only trigger a fraud response if you have AVS and CVV rules enabled in your sandbox environment. Learn 
			<a href="https://articles.braintreepayments.com/guides/fraud-tools/basic#enabling-avs-and-cvv-rules">how to enable AVS and CVV rules</a> and see which <a href="https://articles.braintreepayments.com/guides/fraud-tools/basic#recommended-setup-options">rules Braintree recommends</a>.', 'braintree-payments' )?>
	</p>
	<table class="bordered">
		<thead>
			<tr>
				<th><?php _e('CVV', 'braintree-payments' )?></th>
				<th><?php _e('CID (Amex)', 'braintree-payments' )?></th>
				<th><?php _e('Response', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><code class="syntax-inline syntax-inline--theme">200</code></td>
				<td><code class="syntax-inline syntax-inline--theme">2000</code></td>
				<td>N (does not match)</td>
			</tr>
			<tr>
				<td><code class="syntax-inline syntax-inline--theme">201</code></td>
				<td><code class="syntax-inline syntax-inline--theme">2011</code></td>
				<td>U (not verified)</td>
			</tr>
			<tr>
				<td><code class="syntax-inline syntax-inline--theme">301</code></td>
				<td><code class="syntax-inline syntax-inline--theme">3011</code></td>
				<td>S (issuer does not participate)</td>
			</tr>
			<tr>
				<td>no value passed</td>
				<td>no value passed</td>
				<td>I (not provided)</td>
			</tr>
			<tr>
				<td>any other value</td>
				<td>any other value</td>
				<td>M (matches)</td>
			</tr>
		</tbody>
	</table>
	<h3 id="avs-postal-code-responses" class="thin"><?php _e('AVS postal code responses', 'braintree-payments' )?></h3>
	<p>
		<?php
		
		_e( 'Note: if testing using WooCommerce, the postal code entered on the billing postal code field of the checkout page will be used as the postal code for validation. 
			On pages such as the Add Payment Method page, the card form\'s postal code will be used.', 'braintree-payments' )?>
	</p>
	<table class="bordered">
		<thead>
			<tr>
				<th><?php _e('Billing Postal Code', 'braintree-payments' )?></th>
				<th><?php _e('Response', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><code class="syntax-inline syntax-inline--theme">20000</code></td>
				<td>N (does not match)</td>
			</tr>
			<tr>
				<td><code class="syntax-inline syntax-inline--theme">20001</code></td>
				<td>U (not verified)</td>
			</tr>
			<tr>
				<td>no value passed</td>
				<td>I (not provided)</td>
			</tr>
			<tr>
				<td>any other value</td>
				<td>M (matches)</td>
			</tr>
		</tbody>
	</table>
	<h3 class="thin"><?php _e('AVS street address responses', 'braintree-payments' )?></h3>
	<p>
		<?php _e('Note: if testing using WooCommerce, the billing street address field will be used for validation.', 'braintree-payments' )?>
	</p>
	<table class="bordered">
		<thead>
			<tr>
				<th><?php _e('Billing Street Address', 'braintree-payments' )?></th>
				<th><?php _e('Response', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>starts with <code class="syntax-inline syntax-inline--theme">200</code></td>
				<td>N (does not match)</td>
			</tr>
			<tr>
				<td>starts with <code class="syntax-inline syntax-inline--theme">201</code></td>
				<td>U (not verified)</td>
			</tr>
			<tr>
				<td>no value passed</td>
				<td>I (not provided)</td>
			</tr>
			<tr>
				<td>any other value</td>
				<td>M (matches)</td>
			</tr>
		</tbody>
	</table>
	<h3 class="thin"><?php _e('3D Secure Test Data', 'braintree-payments' )?></h3>
	<p>
		<?php _e('To trigger validation errors and successes using 3D Secure, use the following test cards.', 'braintree-payments' )?>
	</p>
	<table class="bordered">
		<thead>
			<tr>
				<th><?php _e('Scenario', 'braintree-payments' )?></th>
				<th><?php _e('Exp Date', 'braintree-payments' )?></th>
				<th><?php _e('Card #', 'braintree-payments' )?></th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><?php _e('Cardholder enrolled, successful authentication, successful signature verification.', 'braintree-payments' )?></td>
				<td><?php _e('01/20', 'braintree-payments' )?></td>
				<td><?php _e('4000000000000002', 'braintree-payments' )?></td>
			</tr>
			<tr>
				<td><?php _e('Cardholder enrolled, successful authentication, unsuccessful signature verification.', 'braintree-payments' )?></td>
				<td><?php _e('01/20', 'braintree-payments' )?></td>
				<td><?php _e('4000000000000010', 'braintree-payments' )?></td>
			</tr>
			<tr>
				<td><?php _e('Cardholder enrolled, unsuccessful authentication, successful signature verification.', 'braintree-payments' )?></td>
				<td><?php _e('01/20', 'braintree-payments' )?></td>
				<td><?php _e('4000000000000028', 'braintree-payments' )?></td>
			</tr>
		</tbody>
	</table>
	<h3 class="thin"><?php _e('Apple Pay Test Cards', 'braintree-payments' )?></h3>
	<p>
		<?php _e('In order to test Apple Pay, you must use specific test cards.', 'braintree-payments')?>
	</p>
	<p>
		<a href="https://developer.apple.com/support/apple-pay-sandbox/" target="_blank"><?php _e('Apple Pay Test Cards', 'braintree-payments')?></a>
	</p>
</div>