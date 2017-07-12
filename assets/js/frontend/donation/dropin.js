jQuery(document).ready(function($){
	
	var dropin = {
			gateway_id: bfwcd_donation_vars.gateways.cards, 
			container: '.payment_method_' + bfwcd_donation_vars.gateways.cards,
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			init: function(){
				
				$(document.body).on('bfwcd_process_donation_' + this.gateway_id, this.payment_method_tokenized);
				$(document.body).on('bfwcd_before_process_donation', this.set_device_data);
				
				$(document.body).on('bfwcd_processing_error', this.processing_error);
				
				this.initialize_dropin_form();
			},
			initialize_dropin_form: function(){
				if(! $(dropin.container).length){
					return;
				}
				var options = {
						container: 'dropin-container',
						onReady: function(integration){
							dropin.integration = integration;
						},
						onError: function(err){
							if(dropin.is_payment_method_selected()){
								return;
							}
							dropin.submit_error(err);
						},
						onPaymentMethodReceived: function(response){
							dropin.on_payment_method_received(response);
						}
					}
				options.dataCollector = {
						kount: {environment: bfwcd_donation_vars.environment},
						paypal: true
				}
				
				braintree.setup(bfwcd_donation_vars.client_token, 'dropin', options);
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: dropin.container});
			},
			on_payment_method_received: function(response){
				dropin.payment_method_received = true;
				$(dropin.container).find(dropin.nonce_selector).val(response.nonce);
				$(dropin.container).closest('form').submit();
			},
			payment_method_tokenized: function(){
				if(dropin.is_payment_method_selected()){
					return true;
				}
				if(dropin.payment_method_received){
					return true;
				}else{
					return false;
				}
			},
			is_payment_method_selected: function(){
				return $('#payment_method_token').length && $('#payment_method_token').val() !== '';
			},
			teardown_dropin: function(){
				if(dropin.integration){
					dropin.integration.teardown();
				}
				dropin.initialize_dropin_form();
			},
			processing_error: function(){
				dropin.teardown_dropin();
				dropin.payment_method_received = false;
			},
			set_device_data: function(){
				if(dropin.integration){
					$(dropin.container).find(dropin.device_data_selector).val(dropin.integration.deviceData);
				}
				return;
			}
	}
	dropin.init();
})