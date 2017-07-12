jQuery(function($){
	
	if(typeof braintree_applepay_vars === 'undefined'){
		return;
	}
	
	var applepay = {
			warning_container: '#braintree-applepay-warning',
			applepay_button: '#braintree-applepay-button',
			container: '.payment_method_' + braintree_applepay_vars.gateway_id,
			nonce_selector: '.bfwc-nonce-value',
			token_selector: '.bfwc-payment-method-token',
			init: function(){
				
				this.assign_parent_class();
				
				setInterval(this.check_setup, 1500);
				
				//checkout page functionality.
				$(applepay.container).closest('form').on('checkout_place_order_' + braintree_applepay_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('updated_checkout', this.check_setup);
				
				$(document.body).on('checkout_error', this.checkout_error);
				
				//other page form functionality.
				$(applepay.container).closest('form').on('woocommerce_form_submit_' + braintree_applepay_vars.gateway_id, this.woocommerce_form_submit);
				
				$(document.body).on('bfwc_display_new_payment_method_container', this.maybe_show_warning);
				
				this.setup_applepay();
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
			setup_applepay: function(){

				if(!$(applepay.container).length){
					return;
				}
				
				if(typeof braintree_applepay_client_token === 'undefined'){
					this.submit_error({
						code: 'INVALID_CLIENT_TOKEN'
					});
					return;
				}
				
				if(this.can_initialize_applepay()){
					braintree.client.create({
						authorization: braintree_applepay_client_token
					}, function(err, clientInstance){
						if(err){
							applepay.submit_error(err);
							return;
						}
						braintree.applePay.create({
							client: clientInstance
						}, function(err, applePayInstance){
							if(err){
								applepay.submit_error(err);
								return;
							}
							applepay.applePayInstance = applePayInstance;
			
							var promise = ApplePaySession.canMakePaymentsWithActiveCard(applePayInstance.merchantIdentifier);
							
							promise.then(function(canMakePaymentsWithActiveCard){
								$(document.body).on('click', applepay.applepay_button, applepay.tokenize_method);
							});
							
						})
					});
				}else{
					applepay.show_warning();
				}
			},
			tokenize_method: function(e){
				
				e.preventDefault();
				
				if(!applepay.can_initialize_applepay()){
					return;
				}
				
				var paymentRequest = applepay.applePayInstance.createPaymentRequest({
					total: {
						label: braintree_applepay_vars.store_name,
						amount: braintree_applepay_vars.order_total
					}
				});

				try{
					var applePaySession = new ApplePaySession(1, paymentRequest);
				}catch(err){
					applepay.submit_error(err);
					return;
				}
				applePaySession.onvalidatemerchant  = function(event){
					applepay.applePayInstance.performValidation({
						validationURL: event.validationURL,
						displayName: braintree_applepay_vars.store_name
					}, function(err, merchantSession){
						if(err){
							applepay.submit_error(err);
							applePaySession.abort();
							return;
						}
						applePaySession.completeMerchantValidation(merchantSession);
					})
				}
				applePaySession.onpaymentauthorized = function(event){
					applepay.applePayInstance.tokenize({
						token: event.payment.token
					}, function(err, response){
						if(err){
							applepay.submit_error(err);
							applePaySession.completePayment(ApplePaySession.STATUS_FAILURE);
							return;
						}
						applePaySession.completePayment(ApplePaySession.STATUS_SUCCESS);
	
						$(applepay.container).find(applepay.nonce_selector).val(response.nonce);
						applepay.payment_method_received = true;
						$(applepay.container).closest('form').submit();
					})
				}
				
				//open the payments sheet.
				applePaySession.begin();
			},
			can_initialize_applepay: function(){
				return window.ApplePaySession && ApplePaySession.canMakePayments();
			},
			check_setup: function(e){
				applepay.maybe_show_warning();
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: applepay.container});
			},
			woocommerce_form_submit: function(){
				if(applepay.is_payment_method_selected()){
					return true;
				}else{
					if(applepay.payment_method_received){
						return true;
					}else{
						return false;
					}
				}
			},
			is_payment_method_selected: function(){
				if($(applepay.container).find(applepay.token_selector).length > 0){
					if($(applepay.container).find(applepay.token_selector).val() !== ''){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			},
			checkout_error: function(){
				applepay.payment_method_received = false;
			},
			show_warning: function(){
				$(applepay.applepay_button).off();
				
				$(applepay.applepay_button).on('click', function(e){
					e.preventDefault();
				})
				
				$(applepay.applepay_button).slideUp(300, function(){
					$(applepay.warning_container).slideDown(300);
				})
			},
			maybe_show_warning: function(){
				if(!applepay.can_initialize_applepay()){
					
					applepay.show_warning();
				}else{
					applepay.display_button();
				}
			},
			display_button: function(){
				$(applepay.warning_container).slideUp(300, function(){
					$(applepay.applepay_button).slideDown(300);
				})
			}
	}
	applepay.init();
})