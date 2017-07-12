jQuery(document).ready(function($){
	var donation = {
			container: '.braintree-payment-gateway',
			new_method_container: '.bfwc-new-payment-method-container',
			saved_method_container: '.bfwc-payment-method-container',
			token_selector: '#payment_method_token',
			nonce_selector: '.bfwc-nonce-value',
			init: function(){
				
				this.form = $('form.braintree-donation');
				
				this.determine_loader_element();
				
				$('.bfwc-cancel-saved').on('click', this.use_new_payment_method);
				$('.bfwc-saved-methods').on('click', this.use_saved_payment_method);
				
				//select fields
				$('select.bfwc-select2').select2();
				
				$('.bfwc-selected-payment-method').select2({
					templateResult: donation.format_result,
					templateSelection: donation.format_selection,
					escapeMarkup: function(m){return m},
					width: '100%'
				});
				
				$('select.bfwc-selected-payment-method').on('change', this.update_payment_method);
				
				$('select#billing_country').on('change', this.update_region);
				
				$(document.body).on('country_to_state_change', this.select2_billing_state);
				
				$(document.body).on('braintree_card_type_change', this.card_type_change);
				
				$('select#billing_country').change();
				
				this.form_rendering();
				
				if(braintree_donation_vars.dropin_form.enabled){
					$('#submit_donation').on('click', donation.donation_placed);
					$(document.body).on('donation_payment_form_displayed', this.initialize_dropin_form);
					$(document.body).on('donation_payment_form_hidden', this.teardown_dropin);
					if(!this.has_saved_payment_methods()){
						this.initialize_dropin_form();
					}
				}else{
					this.initialize_hosted_fields();
				}
				if(braintree_donation_vars.modal){
					this.message_container = $('.braintree-modal .modal-content');
					this.animate = '.braintree-modal';
					this.offset = 10;
					this.modal = $('.braintree-modal');
					$('#open_modal').on('click', this.open_modal);
					$('.modal-close').on('click', this.close_modal);
				}else{
					this.message_container = this.form;
					this.animate = 'html, body';
					this.offset = 100;
				}
			},
			initialize_dropin_form: function(){
				braintree.setup(braintree_donation_vars.client_token, 'dropin', {
					container: 'dropin-container',
					form: $('#dropin-container').closest('form')[0],
					onReady: function(integration){
						donation.dropin_integration = integration;
					},
					onError: function(err){
						donation.handle_braintree_error(err);
					},
					onPaymentMethodReceived: function(response){
						$(donation.nonce_selector).val(response.nonce);
						donation.payment_method_received;
						donation.process_donation(response);
					}
				});
			},
			initialize_hosted_fields: function(){
				this.custom_form = {};
				braintree.client.create({
					authorization : braintree_donation_vars.client_token
				}, function(err, clientInstance) {
					if (err) {
						donation.handle_braintree_error(err);
						return;
					}
					braintree.hostedFields.create({
						client : clientInstance,
						styles : braintree_donation_vars.custom_form.styles,
						fields : donation.get_custom_fields(),
					}, function(err, hostedFieldsInstance) {
						if (err){
							donation.handle_braintree_error(err);
							return;
						}
						hostedFieldsInstance.on('validityChange', donation.validity_change);
						$.each(donation.events, function(index, value){
							hostedFieldsInstance.on(index, function(event){
								$(document.body).trigger(value, event);
							})
						});
						var button = document.getElementById('submit_donation');
						button.addEventListener('click', function(e){
							e.preventDefault();
							if(donation.is_payment_method_selected()){
								donation.process_donation();
							}else{
								hostedFieldsInstance.tokenize(function(err, response){
									if(err){
										donation.handle_braintree_error(err);
										donation.handle_tokenization_error(err);
										return;
									}
									$(donation.nonce_selector).val(response.nonce);
									donation.process_donation();
								});
							}
						})
					});
				})
			
			},
			process_donation: function(){
				donation.display_processing();
				var data = donation.form.serialize();
				$.ajax({
					dataType: 'json',
					method: 'POST',
					url : braintree_donation_vars.ajax_url,
					data: data,
					success: function(response){
						if(response.result === 'success'){
							window.location.href = response.redirect_url;
						}else{
							donation.hide_processing();
							donation.submit_error(response.messages);
						}
					},
					error: function(jqXHR, textStatus, errorThrown){
						donation.hide_processing();
						donation.submit_error(errorThrown);
					}
				})
			},
			determine_loader_element: function(){
				if(braintree_donation_vars.modal){
					donation.loader_element = $('.modal-content');
				}else{
					donation.loader_element = donation.form;
				}
			},
			display_processing: function(){
				var loader = braintree_donation_vars.loader;
				if(loader.loader_enabled){
					donation.loader_element.block({
						message: loader.loader_html,
						css: loader.loader_css,
						overlayCSS: {
							background: '#fff',
							opacity: 0.8
						}
					})
				}
			},
			hide_processing: function(){
				donation.loader_element.unblock();
			},
			card_type_change: function(e, event){
				if(event.cards.length === 1){
					$('.bfwc-card-type').addClass(event.cards[0].type);
					donation.current_card_type = event.cards[0].type;
				}else{
					$('.bfwc-card-type').removeClass(donation.current_card_type);
				}
			},
			handle_tokenization_error: function(err){
				if(err.code === 'HOSTED_FIELDS_FIELDS_INVALID'){
					$.each(err.details.invalidFieldKeys, function(i, value){
						var field = donation.get_custom_fields()[value];
						$(field.selector).braintreeInvalidField();
					});
				}else if(err.code === 'HOSTED_FIELDS_FIELDS_EMPTY'){
					$.each(donation.get_custom_fields(), function(index, value){
						$(value.selector).braintreeInvalidField();
					})
				}
				$(document.body).trigger('braintree_tokenization_error', {err: err, fields: donation.get_custom_fields()});
			},
			handle_braintree_error: function(error){
				var message = error.message;
				var code = donation.get_error_code(error);
				
				if(code){
					message = braintree_error_messages[code] ? braintree_error_messages[code] : error.message;
				}
				
				donation.submit_error(message);
			},
			get_error_code: function(error){
				if(error.code){
					return error.code;
				}else if(error.type){
					return data.error.type;
				}else if(error.message){
					return false;
				}
			},
			submit_error: function(messages){
				var message = '';
				if(Array.isArray(messages)){
					$.each(messages, function(index, text){
						message += '<li>' + text + '</li>';
					});
				}else{
					message = '<li>' + messages + '</li>';
				}
				$( '.donation-error, .donation-message' ).remove();
				$(donation.message_container).prepend( '<ul class="donation-error">'+message+'</ul>' );
				$(donation.message_container).removeClass( 'processing' ).unblock();
				$(donation.message_container).find( '.input-text, select, input:checkbox' ).blur();
				$( donation.animate ).animate({
					scrollTop: ( $( donation.message_container ).offset().top - donation.offset )
				}, 1000 );
			},
			get_custom_fields: function(){
				var fields = {};
				if(! donation.custom_form.fields){
					$.each(braintree_donation_vars.custom_form.fields, function(index, value){
						if($(value.selector).length > 0){
							fields[index] = value;
						}
					});
					donation.custom_form.fields = fields;
				}
				return donation.custom_form.fields;
			},
			update_payment_method: function(){
				$(donation.token_selector).val($(this).val());
			},
			open_modal: function(e){
				e.preventDefault();
				//donation.modal.addClass('open');
				var overlay = $('<div class="donation-overlay"></div>');
				$('body').append(overlay);
				overlay.fadeIn();
				donation.modal.fadeIn();
				overlay.on('click', donation.close_modal);
				$('body').attr('style', 'overflow: hidden');
				
			},
			close_modal: function(e){
				$(this).removeClass('active');
				$('body').attr('style', '');
				donation.modal.fadeOut();
				$('.donation-overlay').fadeOut(400, function(){
					$('.donation-overlay').remove();
				});
			},
			donation_placed: function(e){
				if(donation.is_payment_method_selected()){
					e.preventDefault();
					donation.process_donation();
				}
			},
			use_new_payment_method: function(e){
				e.preventDefault();
				$(donation.token_selector).val('');
				
				$(donation.new_method_container).slideDown(400, function(){
					$(document.body).trigger('donation_payment_form_displayed');
					$(donation.saved_method_container).slideUp();
				});
			},
			use_saved_payment_method: function(e){
				e.preventDefault();
				$('select.bfwc-selected-payment-method').change();
				$(donation.new_method_container).slideUp(400, function(){
					$(document.body).trigger('donation_payment_form_hidden');
					$(donation.saved_method_container).slideDown();
				});
			},
			form_rendering: function(){
				if($(donation.saved_method_container).length > 0){
					$(donation.new_method_container).hide();
					$('select.bfwc-selected-payment-method').change();
				}
			},
			is_payment_method_selected: function(){
				return $('#payment_method_token').length && $('#payment_method_token').val() !== '';
			},
			has_saved_payment_methods: function(){
				return $('#braintree_payment_methods').length;
			},
			teardown_dropin: function(){
				if(donation.dropin_integration){
					donation.dropin_integration.teardown();
				}
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
			validity_change: function(event){
				var field = event.fields[event.emittedBy];
				if(field.isValid || (!field.isValid && !field.isPotentiallyValid)){
					$(field.container).removeClass('braintree-hosted-fields-focused');
				}else{
					$(field.container).addClass('braintree-hosted-fields-focused');
				}
			},
			update_region: function(){
				var country = $(this).val();
				
				//only proceed if state is defined
				if($('#billing_state').length){
					
					var $billing_state = $('#billing_state'),
					value = $billing_state.val(),
					name = $billing_state.attr('name'),
					id = $billing_state.attr('id'),
					classes = $billing_state.attr('class'),
					placeholder = $billing_state.attr('placeholder'),
					$billing_state = $('#billing_state');
					
					$billing_state.parent().find('.select2-container').remove();
					
					if(classes){
						classes = classes.replace(/.*?select2.*/g, '');
					}
					
					if(bfwc_field_vars.states[country]){
						var options = '';
						
						$.each(bfwc_field_vars.states[country], function(value, text){
							options += '<option value="' + value + '">' + text + '</option>';
						})
						
						$billing_state.replaceWith('<select class="' + classes + '" id="' + id + '" name="' + name + '"></select>');
						
						$billing_state = $('#billing_state');
						
						$billing_state.html(options);
					}else{
						
						$billing_state.replaceWith('<input type="text" class="' + classes + '" id="' + id + '" name="' + name + '">');
						
						$billing_state = $('#billing_state');
					}
					$billing_state.val(value);
					
					$billing_state.change();
					
					$(document.body).trigger('country_to_state_change');
				}
				
			},
			select2_billing_state: function(){
				$('select#billing_state').each(function(){
					$(this).select2();
				})
			},
			format_result: function(data, container){
				$(container).addClass('select2-bfwc-result-label');
				return '<span class="select2-cardType ' + $(data.element).attr('data-bfwc-cardType') + '"></span>' + data.text;
			},
			format_selection: function(object, container){
				$(container).addClass('select2-bfwc-chosen');
				return '<span class="select2-cardType ' + $(object.element).attr('data-bfwc-cardType') + '"></span>' + object.text;
			}
	}
	donation.init();
	
	$.fn.braintreeInvalidField = function(){
		$(this).addClass('braintree-hosted-fields-invalid');
		
	}
})