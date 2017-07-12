jQuery(function($){
	
	if(typeof braintree_dropin_vars === 'undefined'){
		return;
	}
	
	var dropin = {
			braintree:{},
			container: '.payment_method_' + braintree_dropin_vars.gateway_id,
			new_method_container: '.bfwc-new-payment-method-container',
			token_selector: '.bfwc-payment-method-token',
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			saved_method_container: '.bfwc-payment-method-container',
			dropin_id: '#braintree-dropin-form',
			init: function(){
				
				this.assign_parent_class();
				
				setInterval(this.check_dropin_form, 2000);
				setInterval(this.remove_extra_frames, 500);
				
				
				$(document.body).on('change', 'input[name="payment_method"]', dropin.payment_gateway_change)
				
				//checkout functionality.
				$(dropin.container).closest('form').on('checkout_place_order_' + braintree_dropin_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('updated_checkout', this.check_dropin_form);
				$(document.body).on('checkout_error', this.checkout_error);
				$(document.body).on('updated_checkout', this.maybe_update_vars);
				
				$(document.body).on('bfwc_display_new_payment_method_container', dropin.display_dropin);
				$(document.body).on('bfwc_display_saved_methods', dropin.display_saved_methods);
				
				//other form submit functionality.
				$(dropin.container).closest('form').on('woocommerce_form_submit_' + braintree_dropin_vars.gateway_id, this.woocommerce_form_submit);
				$(dropin.container).closest('form').on('woocommerce_form_submit_' + braintree_dropin_vars.gateway_id, this.set_device_data);
				$(document.body).on('bfwc_pre_form_submit_' + braintree_dropin_vars.gateway_id, this.set_device_data);
				
				//add payment method functionality.
				$( document.body ).on( 'init_add_payment_method', this.initialize_dropin);
				
				if(!this.has_payment_methods()){
					this.initialize_dropin();
				}
				this.initialize_3d_secure();
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
			initialize_dropin: function(){
				if(! $(dropin.container).length){
					return;
				}
				var options = {
						container: 'braintree-dropin-form',
						onReady: function(integration){
							dropin.braintree.integration = integration;
						},
						onError: function(err){
							if(dropin.is_gateway_selected()){
								dropin.submit_error(err);
							}
						},
						onPaymentMethodReceived: function(response){
							if(response.type !== 'PayPalAccount' && braintree_dropin_vars._3ds.active){
								dropin.process_3ds_response(response);
							}else{
								dropin.on_payment_method_received(response);
							}
						}
					}
				if(braintree_dropin_vars.advanced_fraud.enabled){
					options.dataCollector = {
							kount: {environment: braintree_dropin_vars.environment},
							paypal: true
					}
				}
				
				braintree.setup(braintree_dropin_client_token, 'dropin', options);
			},
			initialize_3d_secure: function(){
				if(braintree_dropin_vars._3ds.enabled){
					dropin.braintree.client = new braintree.api.Client({
						clientToken: braintree_dropin_client_token
					});
				}
			},
			teardown_dropin: function(callback){
				if(dropin.braintree.integration){
					if(dropin.braintree.teardown_called){
						return;
					}
					dropin.braintree.teardown_called = true;
					try{
						dropin.braintree.integration.teardown(function(){
							dropin.braintree.teardown_called = false;
							if(callback){
								callback();
							}
						});
					}catch(err){
						dropin.braintree.teardown_called = false;
					}
				}
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: dropin.container});
			},
			on_payment_method_received: function(response){
				dropin.payment_method_received = true;
				$(dropin.container).find(dropin.nonce_selector).val(response.nonce);
				$(dropin.container).closest('form').submit();
			},
			process_3ds_response: function(response){
				dropin.braintree.client.verify3DS({
					amount: $('#bfwc_cart_total').length ? $('#bfwc_cart_total').val() : braintree_dropin_vars.order_total,
					creditCard: response.nonce,
					onUserClose: function(){
						dropin.unblock_form();
						dropin.teardown_dropin(function(){
							dropin.initialize_dropin(); //dropin needs to be reinitialized after
							//3ds pop is closed.
						});
					}
				}, function(err, response){
					if(err){
						dropin.submit_error(err);
					}else{
						dropin.on_payment_method_received(response);
					}
				});
			},
			process_3dsecure_vaulted: function(){
				dropin.block_form();
				$.when(dropin.payment_nonce_request(dropin.get_payment_token())).done(function(response){
					if(response.success){
						dropin.process_3ds_response({nonce: response.data});
					}else{
						dropin.submit_error(response.data);
						dropin.unblock_form();
					}
				}).fail(function( jqXHR, textStatus, errorThrown ){
					dropin.submit_error({message: errorThrown});
					dropin.unblock_form();
				});
			},
			woocommerce_form_submit: function(e){
				if(dropin.is_payment_method_selected()){
					if(braintree_dropin_vars._3ds.active && braintree_dropin_vars._3ds.verify_vault){
						if(dropin.payment_method_received){
							return true;
						}else{
							dropin.process_3dsecure_vaulted();
							return false;
						}
					}else{
						return true;
					}
				}else{
					if(dropin.payment_method_received){
						return true;
					}else{
						return false;
					}
				}
			},
			check_dropin_form: function(){
				if(!dropin.is_gateway_selected()){
					//braintree not selected so exit.
					return;
				}
				
				dropin.assign_parent_class();
				
				if(dropin.has_payment_methods() && dropin.is_payment_method_selected()){
					//payment methods exist so exit.
					return;
				}
				var frame = $(dropin.dropin_id).find('iFrame');
				if(!frame.length){
					dropin.initialize_dropin();
				}
			},
			remove_extra_frames: function(){
				$.each($(dropin.dropin_id).find('iFrame'), function(index){
					if(index > 0){
						$(this).remove();
					}
				});
			},
			has_payment_methods: function(){
				return $(dropin.container).find(dropin.saved_method_container).length > 0;
			},
			is_payment_method_selected: function(){
				if($(dropin.container).find(dropin.token_selector).length > 0){
					if($(dropin.container).find(dropin.token_selector).val() !== ''){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			},
			display_dropin: function(e){
				e.preventDefault();
				dropin.initialize_dropin();
			},
			display_saved_methods: function(e){
				e.preventDefault();
				dropin.teardown_dropin();
			},
			payment_gateway_change: function(e){
				if($('#payment_method_' + braintree_dropin_vars.gateway_id).is(':checked')){
					if(dropin.has_payment_methods()){
						dropin.display_saved_methods(e);
					}else{
						dropin.display_dropin(e);
					}
				}else{
					if(dropin.braintree.integration){
						dropin.teardown_dropin();
					}
				}
			},
			is_gateway_selected: function(){
				return $('input[name="payment_method"]:checked').val() === braintree_dropin_vars.gateway_id;
			},
			block_form: function(){
				$(dropin.container).closest('form').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			},
			unblock_form: function(){
				$(dropin.container).closest('form').unblock();
			},
			get_payment_token: function(){
				return $(dropin.container).find(dropin.token_selector).val();
			},
			checkout_error: function(){
				dropin.payment_method_received = false;
				if($(dropin.container).css('display') !== 'none'){
					dropin.teardown_dropin(function(){
						dropin.initialize_dropin();
					});
				}
			},
			maybe_update_vars: function(e){
				//update vars for 3D Secure
				if(braintree_dropin_vars._3ds.enabled){
					dropin.block_form();
					var data = {
							bfwc_handle: 'dropin', 
							security:braintree_dropin_vars.update_checkout_nonce,
							billing_country: $('#billing_country').val(),
							shipping_country: $('#shipping_country').val()
					};
					$.ajax({
						type: 'POST',
						url: braintree_dropin_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'bfwc_updated_checkout' ),
						data: data,
						success: function(response){
							dropin.unblock_form();
							if(response.success){
								braintree_dropin_vars = response.data;
							}
						},
						error: function(jqXHR, textStatus, errorThrown){
							dropin.unblock_form();
						}
					});
				}
			},
			payment_nonce_request: function(token){
				return $.ajax({
					type: 'POST',
					url: braintree_dropin_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'generate_payment_nonce' ),
					data: {security: braintree_dropin_vars.payment_method_nonce, bfwc_payment_token: token},
				});
			},
			set_device_data: function(){
				if(dropin.braintree.integration && braintree_dropin_vars.advanced_fraud.enabled){
					$(dropin.container).find(dropin.device_data_selector).val(dropin.braintree.integration.deviceData);
				}
				return true;
			}
	}
	dropin.init();
});