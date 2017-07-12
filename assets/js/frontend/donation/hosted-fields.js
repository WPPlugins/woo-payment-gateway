jQuery(document).ready(function($){
	
	if(bfwcd_donation_vars === 'undefined'){
		return;
	}
	
	var hosted = {
			gateway_id: bfwcd_donation_vars.gateways.cards, 
			container: '.payment_method_' + bfwcd_donation_vars.gateways.cards,
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			init: function(){
				
				setInterval(this.check_hosted_fields, 1500);
				
				$(document.body).on('click', '#submit_donation', this.tokenize_method);
				
				$(document.body).on('bfwcd_process_donation_' + this.gateway_id, this.payment_method_tokenized);
				$(document.body).on('bfwcd_before_process_donation', this.set_device_data);
				
				$(document.body).trigger('bfwcd_container_size_check', this.trigger_container_check);
				
				this.initialize_functionality();
				
				$(document.body).on('braintree_card_type_change', this.card_type_change);
			},
			initialize_functionality: function(){
				if(!$(hosted.container).length){
					return;
				}
				$(document.body).trigger('braintree_before_form_initialize');
				braintree.client.create({
					authorization: bfwcd_donation_vars.client_token
				}, function(err, clientInstance){
					if(err){
						hosted.submit_error(err);
						return;
					}
					hosted.clientInstance = clientInstance;
					
					hosted.initialize_hosted_instance();
					
					hosted.initialize_fraud_tools();
					
					//hosted.initialize_3d_secure();
					
				})
			},
			initialize_hosted_instance: function(){
				if(!$(hosted.container).length){
					return;
				}
				if(hosted.clientInstance){
					braintree.hostedFields.create({
						client: hosted.clientInstance,
						styles: bfwcd_donation_vars.custom_form.styles,
						fields: hosted.get_hosted_fields()
					}, function(err, hostedFieldsInstance){
						if(err){
							if(err.code === 'HOSTED_FIELDS_FIELD_DUPLICATE_IFRAME'){
								return;
							}
							hosted.submit_error(err);
						}
						hosted.hostedFieldsInstance = hostedFieldsInstance;
						hosted.hostedFieldsInstance.on('validityChange', hosted.validity_change);
						hosted.hostedFieldsInstance = hostedFieldsInstance;
						$.each(hosted.events, function(index, value){
							hostedFieldsInstance.on(index, function(event){
								$(document.body).trigger(value, event);
							})
						});
					});
				}
			},
			initialize_fraud_tools: function(){
				if(bfwcd_donation_vars.fraud.enabled){
					braintree.dataCollector.create({
						client: hosted.clientInstance,
						kount: true,
						paypal: true
					}, function(err, dataCollectorInstance){
						if(err){
							hosted.submit_error(err);
							return;
						}
						hosted.dataCollectorInstance = dataCollectorInstance;
					})
				}
			},
			initialize_3d_secure: function(){
				if(bfwcd_donation_vars._3ds.enabled){
					braintree.threeDSecure.create({
						client: hosted.clientInstance
					}, function(err, threeDSecureInstance){
						if(err){
							hosted.threeds_error = err;
							hosted.submit_error(err);
							return;
						}
						hosted.threeDSecureInstance = threeDSecureInstance;
						$('body').addClass('bfwc-3ds');
					})
				}
			},
			check_hosted_fields: function(){
				
				var frames = $(hosted.container).find(hosted.new_method_container).find('iFrame');
				
				if(!frames.length){
					hosted.initialize_hosted_instance();
				}
			},
			payment_method_tokenized: function(){
				if(hosted.payment_method_received){
					return true;
				}else{
					return false;
				}
			},
			tokenize_method: function(e){
				if(hosted.is_payment_method_selected()){
					return;
				}
				e.preventDefault();
				$(document.body).trigger('braintree_checkout_initiated');
				hosted.hostedFieldsInstance.tokenize(function(err, payload){
					if(err){
						hosted.submit_error(err);
						hosted.handle_tokenization_error(err);
						return;
					}
					hosted.on_payment_method_received(payload);
				});
			},
			on_payment_method_received: function(response){
				hosted.payment_method_received = true;
				$(hosted.container).find(hosted.nonce_selector).val(response.nonce);
				$(hosted.container).closest('form').submit();
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: hosted.container, fields: hosted.get_hosted_fields()});
			},
			handle_tokenization_error: function(err){
				if(err.code === 'HOSTED_FIELDS_FIELDS_INVALID'){
					$.each(err.details.invalidFieldKeys, function(i, value){
						var field = hosted.get_hosted_fields()[value];
						$(field.selector).braintreeInvalidField();
					});
				}else if(err.code === 'HOSTED_FIELDS_FIELDS_EMPTY'){
					$.each(hosted.get_hosted_fields(), function(index, value){
						$(value.selector).braintreeInvalidField();
					})
				}
				$(document.body).trigger('braintree_tokenization_error', {err: err, fields: hosted.get_hosted_fields()});
			},
			events: {
				'validityChange':'braintree_field_validity_change',
				'cardTypeChange':'braintree_card_type_change',
				'empty':'braintree_field_empty',
				'notEmpty':'braintree_field_not_empty',
				'focus':'braintree_field_focus',
				'blur':'braintree_field_blur',
				'inputSubmitRequest':'braintree_card_input_submit_request'
			},
			card_type_change: function(e, event){
				if(event.cards.length === 1){
					$('.bfwc-card-type').addClass('open ' + event.cards[0].type);
					hosted.current_card_type = event.cards[0].type;
				}else{
					$('.bfwc-card-type').attr('class', 'bfwc-card-type');
				}
			},
			validity_change: function(event){
				var field = event.fields[event.emittedBy];
				if(field.isValid || (!field.isValid && !field.isPotentiallyValid)){
					$(field.container).removeClass('braintree-hosted-fields-focused');
				}else{
					$(field.container).addClass('braintree-hosted-fields-focused');
				}
			},
			get_hosted_fields: function(){
				if(!hosted.hosted_fields){
					hosted.hosted_fields = {};
					$.each(bfwcd_donation_vars.custom_form.fields, function(index, value){
						if($(value.selector).length){
							hosted.hosted_fields[index] = value;
						}
					});
				}
				return hosted.hosted_fields;
			},
			trigger_container_check: function(){
				$(document.body).trigger('bfwc_container_size_check', hosted.get_hosted_fields());
			},
			is_payment_method_selected: function(){
				return $('#payment_method_token').length && $('#payment_method_token').val() !== '';
			},
			set_device_data: function(){
				if(hosted.dataCollectorInstance){
					$(hosted.container).find(hosted.device_data_selector).val(hosted.dataCollectorInstance.deviceData);
				}
				return true;
			}
	}
	hosted.init();
})