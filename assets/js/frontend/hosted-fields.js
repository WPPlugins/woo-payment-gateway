jQuery(function($){
	
	if(typeof braintree_hosted_fields_vars === 'undefined'){
		return;
	}
	
	var hosted = {
			container: '.payment_method_' + braintree_hosted_fields_vars.gateway_id,
			new_method_container: '.bfwc-new-payment-method-container',
			token_selector: '.bfwc-payment-method-token',
			nonce_selector: '.bfwc-nonce-value',
			device_data_selector: '.bfwc-device-data',
			icon_style: braintree_hosted_fields_vars.dynamic_card_display.icon_style,
			init: function(){
				
				this.assign_parent_class();
				
				setInterval(this.check_hosted_fields, 1500);
				
				//checkout page functionality.
				$(hosted.container).closest('form').on('checkout_place_order_' + braintree_hosted_fields_vars.gateway_id, this.woocommerce_form_submit);
				$(document.body).on('updated_checkout', this.check_hosted_fields);
				$(document.body).on('updated_checkout', this.maybe_update_vars);
				
				//other form submit functionality.
				$(hosted.container).closest('form').on('woocommerce_form_submit_' + braintree_hosted_fields_vars.gateway_id, this.woocommerce_form_submit);
				$(hosted.container).closest('form').on('woocommerce_form_submit_' + braintree_hosted_fields_vars.gateway_id, this.maybe_set_device_data);
				$(document.body).on('bfwc_pre_form_submit_' + braintree_hosted_fields_vars.gateway_id, this.maybe_set_device_data);
				
				$(document.body).on('checkout_error', this.checkout_error);

				$(document.body).on('braintree_checkout_initiated', this.display_loader);
				$(document.body).on('braintree_tokenization_error', this.remove_loader);
				$(document.body).on('checkout_error', this.remove_loader);
				
				if(braintree_hosted_fields_vars.dynamic_card_display.enabled){
					$(document.body).on('braintree_card_type_change', this.card_type_change);
				}
				
				$(document.body).on('click', '#place_order', this.submit_payment_method);
				
				$(document.body).on('bfwc_get_hosted_fields_instance', this.get_instance_of_hosted_fields);
				
				this.initialize_functionality();
				
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
			initialize_functionality: function(){
				
				if(typeof braintree_hosted_fields_client_token === 'undefined'){
					this.submit_error({
						code: 'INVALID_CLIENT_TOKEN'
					});
					return;
				}
				
				$(document.body).trigger('braintree_before_form_initialize');
				braintree.client.create({
					authorization: braintree_hosted_fields_client_token
				}, function(err, clientInstance){
					if(err){
						hosted.submit_error(err);
						return;
					}
					hosted.clientInstance = clientInstance;
					
					hosted.initialize_hosted_instance();
					
					hosted.initialize_fraud_tools();
					
					hosted.initialize_3d_secure();
					
				})
			},
			initialize_hosted_instance: function(){
				if(!$(hosted.container).length){
					return;
				}
				if(hosted.clientInstance){
					braintree.hostedFields.create({
						client: hosted.clientInstance,
						styles: braintree_hosted_fields_vars.form_styles,
						fields: hosted.get_hosted_fields()
					}, function(err, hostedFieldsInstance){
						if(err){
							if(err.code === 'HOSTED_FIELDS_FIELD_DUPLICATE_IFRAME'){
								return;
							}
							hosted.submit_error(err);
							return;
						}
						hosted.hostedFieldsInstance = hostedFieldsInstance;
						hosted.hostedFieldsInstance.on('validityChange', hosted.validity_change);
						hosted.hostedFieldsInstance = hostedFieldsInstance;
						$.each(hosted.events, function(index, value){
							hostedFieldsInstance.on(index, function(event){
								$(document.body).triggerHandler(value, event);
							})
						});
					});
				}
			},
			initialize_3d_secure: function(){
				if(braintree_hosted_fields_vars._3ds.enabled){
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
			submit_payment_method: function(e){
				if(hosted.is_gateway_selected()){
					if(!hosted.is_payment_method_selected()){
						e.preventDefault();
						hosted.tokenize_method();
					}else{
						$(document.body).triggerHandler('braintree_checkout_initiated');
						if(braintree_hosted_fields_vars._3ds.active && braintree_hosted_fields_vars._3ds.verify_vault){
							e.preventDefault();
							hosted.process_3dsecure_vaulted();
						}else{
							return true;
						}
					}
				}
			},
			tokenize_method: function(e){
				$(document.body).triggerHandler('braintree_checkout_initiated');
				hosted.hostedFieldsInstance.tokenize(function(err, payload){
					if(err){
						hosted.submit_error(err);
						hosted.handle_tokenization_error(err);
						return;
					}
					if(braintree_hosted_fields_vars._3ds.active ){
						hosted.process_3dsecure(payload);
					}else{
						hosted.on_payment_method_received(payload);
					}
				});
			},
			submit_error: function(error){
				$(document.body).triggerHandler('bfwc_submit_error', {error: error, element: hosted.container, fields: hosted.get_hosted_fields()});
			},
			process_3dsecure: function(response){
				if(hosted.threeDSecureInstance){
					hosted.threeDSecureInstance.verifyCard({
						amount: $('#bfwc_cart_total').length ? $('#bfwc_cart_total').val() : braintree_hosted_fields_vars.order_total,
						nonce: response.nonce,
						addFrame: hosted.add_3ds_frame,
						removeFrame: hosted.remove_3ds_frame
					}, function(err, payload){
						if(err){
							hosted.submit_error(err);
							hosted.remove_loader();
							return;
						}
						hosted.remove_3ds_frame();
						hosted.on_payment_method_received(payload);
						hosted.display_loader();
					});
				}else{
					hosted.submit_error(hosted.threeds_error);
				}
			},
			process_3dsecure_vaulted: function(){
				hosted.block_form();
				$.when(hosted.payment_nonce_request(hosted.get_payment_token())).done(function(response){
					if(response.success){
						hosted.process_3dsecure({nonce: response.data});
					}else{
						hosted.submit_error(response.data);
						hosted.unblock_form();
					}
				}).fail(function( jqXHR, textStatus, errorThrown ){
					hosted.submit_error({message: errorThrown});
					hosted.unblock_form();
				});
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
			initialize_fraud_tools: function(){
				if(braintree_hosted_fields_vars.advanced_fraud.enabled){
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
			on_payment_method_received: function(response){
				hosted.payment_method_received = true;
				$(hosted.container).find(hosted.nonce_selector).val(response.nonce);
				$(hosted.container).closest('form').submit();
			},
			check_hosted_fields: function(){
				
				hosted.assign_parent_class();
				
				var frames = $(hosted.container).find(hosted.new_method_container).find('iFrame');
				
				if(!frames.length){
					hosted.initialize_hosted_instance();
				}
				
				hosted.check_container_size();
			},
			check_container_size: function(){
				if($('.payment_methods').width() < 475){
					$('div.braintree-payment-gateway').addClass('small-container');
				}else{
					$('div.braintree-payment-gateway').removeClass('small-container');
				}
				$(document.body).trigger('bfwc_container_size_check', hosted.get_hosted_fields());
			},
			get_hosted_fields: function(){
				if(!hosted.hosted_fields){
					hosted.hosted_fields = {};
					$.each(braintree_hosted_fields_vars.custom_fields, function(index, value){
						if($(value.selector).length){
							value.placeholder = $(value.selector).attr('data-placeholder');
							hosted.hosted_fields[index] = value;
						}
					});
				}
				return hosted.hosted_fields;
			},
			checkout_error: function(){
				hosted.payment_method_received = false;
			},
			is_payment_method_selected: function(){
				if($(hosted.container).find(hosted.token_selector).length > 0){
					if($(hosted.container).find(hosted.token_selector).val() !== ''){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			},
			is_gateway_selected: function(){
				return $('input[name="payment_method"]:checked').val() === braintree_hosted_fields_vars.gateway_id;
			},
			get_payment_token: function(){
				return $(hosted.container).find(hosted.token_selector).val();
			},
			teardown: function(callback){
				if(hosted.hostedFieldsInstance){
					try{
						hosted.hostedFieldsInstance.teardown(function(){
							hosted.payment_method_received = false;
							if(callback){
								callback();
							}
						})
					}catch(err){
						hosted.payment_method_received = false;
						if(callback){
							callback();
						}
					}
					
				}
			},
			woocommerce_form_submit: function(){
				if(hosted.is_payment_method_selected()){
					return true;
				}else{
					if(hosted.payment_method_received){
						return true;
					}else{
						return false;
					}
				}
			},
			maybe_update_vars: function(e){
				//update vars for 3D Secure
				if(braintree_hosted_fields_vars._3ds.enabled){
					hosted.block_form();
					var data = {
							bfwc_handle: 'hosted-fields',
							security: braintree_hosted_fields_vars.update_checkout_nonce,
							billing_country: $('#billing_country').val(),
							shipping_country: $('#shipping_country').val()
					};
					$.ajax({
						type: 'POST',
						url: braintree_hosted_fields_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'bfwc_updated_checkout' ),
						data: data,
						success: function(response){
							hosted.unblock_form();
							if(response.success){
								braintree_hosted_fields_vars = response.data;
							}
						},
						error: function(jqXHR, textStatus, errorThrown ){
							hosted.unblock_form();
						}
					});
				}
			},
			payment_nonce_request: function(token){
				return $.ajax({
					type: 'POST',
					url: braintree_hosted_fields_vars.wc_ajax_url.toString().replace( '%%endpoint%%', 'generate_payment_nonce' ),
					data: {security: braintree_hosted_fields_vars.payment_method_nonce, bfwc_payment_token: token},
				});
			},
			maybe_set_device_data: function(){
				if(hosted.dataCollectorInstance){
					$(hosted.container).find(hosted.device_data_selector).val(hosted.dataCollectorInstance.deviceData);
				}
				return true;
			},
			add_3ds_frame: function(err, iFrame){
				if(err){
					hosted.submit_error(err);
					return;
				}
				hosted.remove_loader();
				$('body').prepend('<div class="braintree-3ds-overlay"></div>');
				$('body .braintree-3ds-overlay').fadeIn(400);
				$('body').prepend(braintree_hosted_fields_vars._3ds.modal_html);
				$('.threeDS-frame-body').append(iFrame);
				$('#braintree_3ds_hosted_modal').fadeIn(400);
				$('.threeDS-cancel').on('click', hosted.remove_3ds_frame);
			},
			remove_3ds_frame: function(err){
				$('body .braintree-3ds-overlay').fadeOut(400, function(){
					$(this).remove();
				})
				$('body .threeDS-hosted-modal').fadeOut(400, function(){
					$(this).remove();
				});
				hosted.unblock_form();
				hosted.threeDSecureInstance.cancelVerifyCard(function(err, payload){
					if(err){
						hosted.submit_error(err);
						return;
					}
				});
			},
			display_loader: function(){
				if(braintree_hosted_fields_vars.loader.enabled){
					$('.bfwc-payment-loader').fadeIn(200);
				}
			},
			remove_loader: function(){
				$('.bfwc-payment-loader').fadeOut(200);
			},
			block_form: function(){
				$(hosted.container).closest('form').block({
					message: null,
					overlayCSS: {
						background: '#fff',
						opacity: 0.6
					}
				});
			},
			unblock_form: function(){
				$(hosted.container).closest('form').unblock();
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
					$('.bfwc-card-type').attr('class', 'bfwc-card-type').addClass(hosted.icon_style + ' ' + event.cards[0].type);
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
			get_instance_of_hosted_fields: function(){
				return hosted.hostedFieldsInstance;
			}
	}
	hosted.init();
	
	$.fn.braintreeInvalidField = function(){
		$(this).addClass('braintree-hosted-fields-invalid');
	}
});