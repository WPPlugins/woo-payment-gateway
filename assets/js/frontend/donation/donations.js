jQuery(document).ready(function($){
	var donation = {
			container: '.braintree-payment-gateway',
			new_method_container: '.bfwc-new-payment-method-container',
			saved_method_container: '.bfwc-payment-method-container',
			token_selector: '#payment_method_token',
			nonce_selector: '.bfwc-nonce-value',
			init: function(){
				
				this.form = $('form.braintree-donation');
				
				this.form.on('submit', this.process_donation);
				
				$(document.body).on('bfwc_submit_error', this.handle_braintree_error);
				
				$(document.body).on('bfwc_submit_error', this.hide_processing);
				
				this.determine_loader_element();
				
				$(document.body).on('click', 'input[name="payment_gateway"]', this.payment_gateway_selected);
				//$(document.body).on('donation_payment_form_displayed', this.payment_gateway_selected);
				
				$('.bfwc-cancel-saved').on('click', this.use_new_payment_method);
				$('.bfwc-saved-methods').on('click', this.use_saved_payment_method);
				
				//select fields
				$('select.bfwc-select2').select2();
				
				$('.bfwcd-date-picker').datepicker({
					dateFormat: 'mm-dd-yy'
				});
				
				$('.bfwc-selected-payment-method').select2({
					templateResult: donation.format_result,
					templateSelection: donation.format_selection,
					escapeMarkup: function(m){return m},
					width: '100%'
				});
				
				$('select.bfwc-selected-payment-method').on('change', this.update_payment_method);
				
				$('select#billing_country').on('change', this.update_region);
				
				$(document.body).on('country_to_state_change', this.select2_billing_state);
				
				$('select#billing_country').change();
				
				this.form_rendering();
				
				this.init_payment_gateways();
				
				setInterval(this.check_container_size, 1000);
				
				if(bfwcd_donation_vars.modal){
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
			init_payment_gateways: function(){
				var payment_gateways = $('input[name="payment_gateway"]');
				
				$(payment_gateways).eq(0).prop('checked', true);
				
				if(payment_gateways.length === 1){
					payment_gateways.eq(0).hide();
				}
				
				//hide all other gateways.
				
				
				payment_gateways.filter(':checked').eq(0).trigger('click');
			},
			payment_gateway_selected: function(){
				if(donation.is_payment_method_selected()){
					$('.bfwc-donation-payment-box').hide();
				}else{
					$('.bfwc-donation-payment-box').filter(':visible').slideUp();
				}
				
				var gateway = $('input[name="payment_gateway"]:checked');
				
				$(gateway).closest('li.payment_method').find('.bfwc-donation-payment-box').slideDown();
			},
			process_donation: function(e){
				donation.display_processing();
				
				$(document.body).trigger('bfwcd_before_process_donation');
				
				if(donation.is_payment_method_selected() || $(document.body).triggerHandler('bfwcd_process_donation_' +  donation.get_gateway()) !== false){
					var data = donation.form.serialize();
					$.ajax({
						dataType: 'json',
						method: 'POST',
						url : bfwcd_donation_vars.ajax_url,
						data: data,
						success: function(response){
							if(response.result === 'success'){
								window.location.href = response.redirect_url;
							}else{
								donation.hide_processing();
								donation.submit_error(response.messages);
								$(document.body).triggerHandler('bfwcd_processing_error');
							}
						},
						error: function(jqXHR, textStatus, errorThrown){
							donation.hide_processing();
							donation.submit_error(errorThrown);
						}
					})
				}
				return false;
			},
			determine_loader_element: function(){
				if(bfwcd_donation_vars.modal){
					donation.loader_element = $('.modal-content');
				}else{
					donation.loader_element = donation.form;
				}
			},
			display_processing: function(){
				$('.bfwc-payment-loader').fadeIn(200);
			},
			hide_processing: function(){
				$('.bfwc-payment-loader').fadeOut(200);
			},
			handle_braintree_error: function(e, data){
				var error = data.error;
				var code = donation.get_error_code(error);
				
				if(code){
					message = bfwcd_error_messages[code] ? bfwcd_error_messages[code] : error.message;
				}
				
				donation.submit_error(message);
			},
			get_error_code: function(error){
				if(error.code){
					return error.code;
				}else if(error.type){
					return error.type;
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
			},
			check_container_size: function(){
				if($('.payment_methods').width() < 475){
					$('div.braintree-payment-gateway').addClass('small-container');
				}else{
					$('div.braintree-payment-gateway').removeClass('small-container');
				}
				$(document.body).trigger('bfwcd_container_size_check');
			},
			get_gateway: function(){
				return $('input[name="payment_gateway"]').filter(':checked').val();
			}
	}
	donation.init();
	
	$.fn.braintreeInvalidField = function(){
		$(this).addClass('braintree-hosted-fields-invalid');
		
	}
})