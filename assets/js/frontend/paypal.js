jQuery(function($){
	
	if(typeof braintree_paypal_vars === 'undefined'){
		return;
	}
	
	var paypal = {
			container: '.payment_method_' + braintree_paypal_vars.gateway_id,
			button_id: '#braintree_paypal_button',
			token_selector: '.bfwc-payment-method-token',
			tokenized_selector: '.bfwc-tokenized-paypal-method',
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			init: function(){
				
				this.assign_parent_class();
				
				this.order_button_text = $('#place_order').val();
				
				$(document.body).on('bfwc_payment_method_selected' , paypal.change_order_button_text);
				
				$(document.body).on('click', paypal.button_id + ', .bfwc-paypal-tokenize', paypal.tokenize_method);
				
				$(document.body).on('click', '.bfwc-paypal-cancel', paypal.remove_method);
				
				$(document.body).on('bfwc_display_new_payment_method_container', this.display_new_method);
				$(document.body).on('bfwc_display_saved_methods', this.display_saved_methods);
				
				//checkout functionality
				$(document.body).on('checkout_error', this.checkout_error);
				$(paypal.container).closest('form').on('checkout_place_order_' + braintree_paypal_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('updated_checkout', this.maybe_update_vars);
				
				$(document.body).on('bfwc_pre_form_submit_' + braintree_paypal_vars.gateway_id, this.set_device_data);
				
				//other page functionality
				$(this.container).closest('form').on('woocommerce_form_submit_' + braintree_paypal_vars.gateway_id, this.woocommerce_form_submit);
				$(this.container).closest('form').on('woocommerce_form_submit_' + braintree_paypal_vars.gateway_id, this.set_device_data);
				
				this.initialize_paypal();
				
			},
			assign_parent_class: function(){
				//ensure container has proper classes.
				$('input[name="payment_method"]').each(function(){
					
					var payment_gateway = $(this).val();
					
					if( !$(this).closest('li').hasClass('payment_method_' + payment_gateway)){
						
						$(this).closest('li').addClass('payment_method_' + payment_gateway);
					}
				})
			},
			initialize_paypal: function(){
				if(typeof braintree_paypal_client_token === 'undefined'){
					this.submit_error({
						code: 'INVALID_CLIENT_TOKEN'
					});
					return;
				}
				braintree.client.create({
					authorization: braintree_paypal_client_token
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
				paypal.paypalInstance.tokenize(paypal.get_tokenization_options(), function(err, payload){
					if(err){
						if(err.code === 'PAYPAL_POPUP_CLOSED'){
							return;
						}else if(err.code === 'PAYPAL_INVALID_PAYMENT_OPTION'){
							if(err.details.originalError.details.originalError.paymentResource.errorDetails){
								err.code = err.code + '_ADDRESS';
							}
						}
						paypal.submit_error(err);
						return;
					}
					paypal.on_payment_method_recieved(payload);
				})
			},
			on_payment_method_recieved: function(response){
				paypal.payment_method_received = true;
				
				var container = $(paypal.container).find(paypal.tokenized_selector);
				
				var html = $(braintree_paypal_vars.html);
				
				$(html).find('.payment-method-description').text(response.details.email);
				
				container.append(html);
				
				$(paypal.container).find('.braintree-paypal-button').slideUp(400);
				
				container.slideDown(400);

				$(paypal.container).find(paypal.nonce_selector).val(response.nonce);
				
				$('#place_order').removeClass('bfwc-paypal-tokenize').val(paypal.order_button_text);
				
				if(braintree_paypal_vars.form.submit){
					$(paypal.container).closest('form').submit();
				}
			},
			get_tokenization_options: function(){
				var options = braintree_paypal_vars.options;
				if(options.enableShippingAddress && ($('[name^="shipping_"]').length || $('[name^="billing_"]').length)){
					var prefix = $('[name^="shipping_"]').length && $('[name="ship_to_different_address"]').is(':checked') ? '#shipping_' : '#billing_';
					options.shippingAddressOverride = {
							recipientName: $(prefix + 'first_name').val() + ' ' + $(prefix + 'last_name').val(),
							line1: $(prefix + 'address_1').val(),
							line2: $(prefix + 'address_2').val(),
							city: $(prefix + 'city').val(),
							countryCode: $(prefix + 'country').val(),
							postalCode: $(prefix + 'postcode').val(),
							state: $(prefix + 'state').val(),
							phone: $(prefix + 'phone').val(),
					};
				}
				options.amount = $('#bfwc_cart_total').length ? $('#bfwc_cart_total').val() : braintree_paypal_vars.order_total;
				return options;
			},
			is_payment_method_selected: function(){
				if($(paypal.container).find(paypal.token_selector).length){
					if($(paypal.container).find(paypal.token_selector).val() !== ''){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			},
			remove_method: function(){
				paypal.payment_method_received = false;
				paypal.change_order_button_text(null, braintree_paypal_vars.gateway_id);
				$(paypal.container).find(paypal.nonce_selector).val('');
				var container = $(paypal.container).find(paypal.tokenized_selector);
				container.slideUp(400, function(){
					$(this).empty();
					$(paypal.container).find('.braintree-paypal-button').slideDown(400);
				})
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: paypal.container});
			},
			checkout_error: function(){
				if(paypal.is_gateway_selected()){
					paypal.payment_method_received = false;
					paypal.remove_method();
				}
			},
			woocommerce_form_submit: function(e){
				if(paypal.is_payment_method_selected()){
					return true;
				}else{
					if(paypal.payment_method_received){
						return true;
					}else{
						return false;
					}
				}
			},
			is_gateway_selected: function(){
				return $('input[name="payment_method"]:checked').val() === braintree_paypal_vars.gateway_id;
			},
			is_checkout_flow: function(){
				return braintree_paypal_vars.options.flow === 'checkout';
			},
			set_device_data: function(){
				if(paypal.dataCollectorInstance){
					$(paypal.container).find(paypal.device_data_selector).val(paypal.dataCollectorInstance.deviceData);
				}
				return true;
			},
			change_order_button_text: function(e, payment_method){
				var $button = $('#place_order');
				if(payment_method === braintree_paypal_vars.gateway_id && !paypal.payment_method_received && !paypal.is_payment_method_selected()){
					$button.val(braintree_paypal_vars.order_button_text);
					$button.addClass('bfwc-paypal-tokenize');
					return true;
				}else{
					$button.removeClass('bfwc-paypal-tokenize');
				}
			},
			display_new_method: function(){
				if(paypal.is_gateway_selected()){
					paypal.change_order_button_text(null, braintree_paypal_vars.gateway_id);
				}
			},
			display_saved_methods: function(){
				if(paypal.is_gateway_selected()){
					var $button = $('#place_order');
					$button.val(paypal.order_button_text);
					$button.removeClass('bfwc-paypal-tokenize');
				}
			}
	}
	paypal.init();
});