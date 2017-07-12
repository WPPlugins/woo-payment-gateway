jQuery(document).ready(function($){
	
	var paypal = {
			gateway_id: bfwcd_donation_vars.gateways.paypal_credit,
			button_id: '#braintree_paypal_credit_button',
			container: '.payment_method_' + bfwcd_donation_vars.gateways.paypal_credit,
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			init: function(){
				
				$(document.body).on('click', this.button_id, this.tokenize_method);
				
				$(document.body).on('bfwcd_process_donation_' + this.gateway_id, this.payment_method_tokenized);
				$(document.body).on('bfwcd_before_process_donation', this.set_device_data);
				
				this.initialize_paypal();
			},
			initialize_paypal: function(){
				if(!bfwcd_donation_vars.client_token){
					return;
				}
				braintree.client.create({
					authorization: bfwcd_donation_vars.client_token
				}, function(err, clientInstance){
					if(err){
						paypal.submit_error(err);
						return;
					}
					paypal.clientInstance = clientInstance;
					
					paypal.initialize_fraud_tools();
					
					braintree.paypal.create({
						client: paypal.clientInstance
					}, function(err, paypalInstance){
						if(err){
							paypal.submit_error(err);
							return;
						}
						paypal.paypalInstance = paypalInstance;
					})
					
				})
			},
			initialize_fraud_tools: function(){
				braintree.dataCollector.create({
					client: paypal.clientInstance,
					paypal: true
				}, function(err, dataCollectorInstance){
					if(err){
						paypal.submit_error(err);
						return;
					}
					paypal.dataCollectorInstance = dataCollectorInstance;
				})
			},
			tokenize_method: function(e){
				e.preventDefault();
				paypal.paypalInstance.tokenize(paypal.get_options(), function(err, payload){
					if(err){
						if(err.code === 'PAYPAL_POPUP_CLOSED'){
							return;
						}
						paypal.submit_error(err);
						return;
					}
					paypal.on_payment_method_recieved(payload);
				})
			},
			on_payment_method_recieved: function(response){
				paypal.payment_method_received = true;
				$(paypal.container).find(paypal.nonce_selector).val(response.nonce);
				$(paypal.container).closest('form').submit();
			},
			payment_method_tokenized: function(){
				if(paypal.payment_method_received){
					return true;
				}else{
					return false;
				}
			},
			get_options: function(){
				var options = bfwcd_donation_vars.paypal_credit.options;
				options['amount'] = $('#donation_amount').val();
				return options;
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: paypal.container});
			},
			set_device_data: function(){
				if(paypal.dataCollectorInstance){
					$(paypal.container).find(paypal.device_data_selector).val(paypal.dataCollectorInstance.deviceData);
				}
				return true;
			}
	}
	paypal.init();
})