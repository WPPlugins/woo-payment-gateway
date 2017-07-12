jQuery(function($){
	
	if(typeof braintree_dropin_v3_vars === 'undefined'){
		return;
	}
	
	var dropin = {
			container: '.payment_method_' + braintree_dropin_v3_vars.gateway_id,
			new_method_container: '.bfwc-new-payment-method-container',
			token_selector: '.bfwc-payment-method-token',
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			saved_method_container: '.bfwc-payment-method-container',
			dropin_id: '#braintree-dropin-form',
			init: function(){
				
				setInterval(this.check_dropin_form, 1500);
				
				$(document.body).on('click', '#place_order', this.submit_payment_method);
				
				//checkout page functionality.
				$(dropin.container).closest('form').on('checkout_place_order_' + braintree_dropin_v3_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('updated_checkout', this.check_dropin_form);
				$(document.body).on('updated_checkout', this.maybe_update_vars);
				
				//other form submit functionality.
				$(dropin.container).closest('form').on('woocommerce_form_submit_' + braintree_dropin_v3_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('bfwc_pre_form_submit_' + braintree_dropin_v3_vars.gateway_id, this.maybe_set_device_data);
				
				$(document.body).on('checkout_error', this.checkout_error);
				
				this.initialize_dropin();
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
			check_dropin_form: function(){
				dropin.assign_parent_class();
				
				var frames = $(dropin.container).find(dropin.dropin_id).find('iFrame');
				
				if(!frames.length){
					dropin.initialize_dropin();
				}
			},
			initialize_dropin: function(){
				if(! $(dropin.container).length){
					return;
				}
				
				if(typeof braintree_dropin_v3_client_token === 'undefined'){
					this.submit_error({
						code: 'INVALID_CLIENT_TOKEN'
					});
					return;
				}
				
				braintree.dropin.create({
					authorization: braintree_dropin_v3_client_token,
					selector: dropin.dropin_id,
					locale: braintree_dropin_v3_vars.locale
				}, function(err, dropinInstance){
					if(err){
						if(err.message.match(/empty DOM node/)){
							return;
						}
						dropin.submit_error(err);
						return;
					}
					dropin.dropinInstance = dropinInstance;
					
					dropin.initialize_fraud_tools();
					
					dropin.initialize_3d_secure();
				})
			},
			initialize_fraud_tools: function(){
				if(braintree_dropin_v3_vars.advanced_fraud.enabled){
					braintree.dataCollector.create({
						client: dropin.dropinInstance._client,
						kount: true,
						paypal: true 
					}, function(err, dataCollectorInstance){
						if(err){
							dropin.submit_error(err);
							return;
						}
						dropin.dataCollectorInstance = dataCollectorInstance;
					})
				}
			},
			initialize_3d_secure: function(){
				if(braintree_dropin_v3_vars._3ds.enabled){
					braintree.threeDSecure.create({
						client: dropin.dropinInstance._client
					}, function(err, threeDSecureInstance){
						if(err){
							dropin.threeds_error = err;
							dropin.submit_error(err);
							return;
						}
						dropin.threeDSecureInstance = threeDSecureInstance;
						$('body').addClass('bfwc-3ds');
					})
				}
			},
			submit_payment_method: function(e){
				if(dropin.is_gateway_selected()){
					if(!dropin.is_payment_method_selected()){
						e.preventDefault();
						dropin.tokenize_method();
					}else{
						$(document.body).triggerHandler('braintree_checkout_initiated');
						if(braintree_dropin_v3_vars._3ds.active && braintree_dropin_v3_vars._3ds.verify_vault){
							e.preventDefault();
							dropin.process_3dsecure_vaulted();
						}else{
							return true;
						}
					}
				}
			},
			tokenize_method: function(){
				$(document.body).triggerHandler('braintree_checkout_initiated');
				if(dropin.dropinInstance){
					dropin.dropinInstance.requestPaymentMethod(function(err, payload){
						if(err){
							dropin.submit_error(err);
							return;
						}
						if(braintree_dropin_v3_vars._3ds.active ){
							dropin.process_3dsecure(payload);
						}else{
							dropin.on_payment_method_received(payload);
						}
					})
				}
				
			},
			process_3dsecure: function(response){
				if(dropin.threeDSecureInstance){
					dropin.threeDSecureInstance.verifyCard({
						amount: $('#bfwc_cart_total').length ? $('#bfwc_cart_total').val() : braintree_dropin_v3_vars.order_total,
						nonce: response.nonce,
						addFrame: dropin.add_3ds_frame,
						removeFrame: dropin.remove_3ds_frame
					}, function(err, payload){
						if(err){
							dropin.submit_error(err);
							return;
						}
						dropin.remove_3ds_frame();
						dropin.on_payment_method_received(payload);
					});
				}else{
					dropin.submit_error(dropin.threeds_error);
				}
			},
			process_3dsecure_vaulted: function(){
				dropin.block_form();
				$.when(dropin.payment_nonce_request(dropin.get_payment_token())).done(function(response){
					if(response.success){
						dropin.process_3dsecure({nonce: response.data});
					}else{
						dropin.submit_error(response.data);
						dropin.unblock_form();
					}
				}).fail(function( jqXHR, textStatus, errorThrown ){
					dropin.submit_error({message: errorThrown});
					dropin.unblock_form();
				});
			},
			on_payment_method_received: function(response){
				dropin.payment_method_received = true;
				$(dropin.container).find(dropin.nonce_selector).val(response.nonce);
				$(dropin.container).closest('form').submit();
			},
			woocommerce_form_submit: function(){
				if(dropin.is_payment_method_selected()){
					return true;
				}else{
					if(dropin.payment_method_received){
						return true;
					}else{
						return false;
					}
				}
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
			is_gateway_selected: function(){
				return $('input[name="payment_method"]:checked').val() === braintree_dropin_v3_vars.gateway_id;
			},
			get_payment_token: function(){
				return $(dropin.container).find(dropin.token_selector).val();
			},
			checkout_error: function(){
				if(dropin.dropinInstance){
					dropin.dropinInstance.teardown(function(){
						dropin.payment_method_received = false;
					});
				}
			},
			teardown: function(callback){
				if(dropin.dropinInstance){
					dropin.dropinInstance.teardown(callback ? callback : function(){});
				}
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: dropin.container});
			},
			add_3ds_frame: function(err, iFrame){
				if(err){
					dropin.submit_error(err);
					return;
				}
				$('body').prepend('<div class="braintree-3ds-overlay"></div>');
				$('body .braintree-3ds-overlay').fadeIn(400);
				$('body').prepend(braintree_dropin_v3_vars._3ds.modal_html);
				$('.threeDS-frame-body').append(iFrame);
				$('#braintree_3ds_hosted_modal').fadeIn(400);
				$('.threeDS-cancel').on('click', dropin.cancel_3ds);
			},
			remove_3ds_frame: function(err){
				$('body .braintree-3ds-overlay').fadeOut(400, function(){
					$(this).remove();
				})
				$('body .threeDS-hosted-modal').fadeOut(400, function(){
					$(this).remove();
				});
				dropin.threeDSecureInstance.cancelVerifyCard(function(err, payload){
					if(err){
						dropin.submit_error(err);
						return;
					}
				});
			},
			cancel_3ds: function(){
				dropin.unblock_form();
				dropin.remove_3ds_frame();
				dropin.teardown();
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
			maybe_update_vars: function(e){
				//update vars for 3D Secure
				if(braintree_dropin_v3_vars._3ds.enabled){
					dropin.block_form();
					var data = {
							bfwc_handle: 'dropin-v3',
							security: braintree_dropin_v3_vars.update_checkout_nonce,
							billing_country: $('#billing_country').val(),
							shipping_country: $('#shipping_country').val()
					};
					$.ajax({
						type: 'POST',
						url: braintree_dropin_v3_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'bfwc_updated_checkout' ),
						data: data,
						success: function(response){
							dropin.unblock_form();
							if(response.success){
								braintree_dropin_v3_vars = response.data;
							}
						},
						error: function(jqXHR, textStatus, errorThrown ){
							dropin.unblock_form();
						}
					});
				}
			},
			payment_nonce_request: function(token){
				return $.ajax({
					type: 'POST',
					url: braintree_dropin_v3_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'generate_payment_nonce' ),
					data: {security: braintree_dropin_v3_vars.payment_method_nonce, bfwc_payment_token: token},
				});
			},
			maybe_set_device_data: function(){
				if(dropin.dataCollectorInstance){
					$(dropin.container).find(dropin.device_data_selector).val(dropin.dataCollectorInstance.deviceData);
				}
				return true;
			},
	}
	dropin.init();
})