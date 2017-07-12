jQuery(document).ready(function($){
	var payment_method = {
			token_selector: '.bfwc-payment-method-token',
			container: '.braintree-payment-gateway',
			new_method_container: '.bfwc-new-payment-method-container',
			saved_method_container: '.bfwc-payment-method-container',
			init: function(){
				if(this.has_payment_methods()){
					
					setInterval(this.check_elements, 2000);
					
					$(document.body).on('updated_checkout', this.check_elements);
					
					$(document.body).on('click', '.bfwc-payment-method-buttons .bfwc-cancel-saved', payment_method.display_new_payment_method_container);
					$(document.body).on('click', '.bfwc-saved-methods', payment_method.display_saved_methods);
					$(document.body).on('click', '.braintree-payment-method', payment_method.payment_method_selected);
					
					$(document.body).on('change', 'select.bfwc-selected-payment-method', payment_method.update_card_from_select);
					
					this.initialize_elements();
				
				}
			},
			initialize_elements: function(){
				
				if($().select2){
					if(braintree_payment_methods_vars.wc['3.0.0']){
						if($('.bfwc-selected-payment-method').length && !$('.bfwc-selected-payment-method').hasClass('select2-hidden-accessible')){
							$('.bfwc-selected-payment-method').select2({
								width: "100%",
								templateResult: payment_method.template_result,
								templateSelection: payment_method.template_selection,
							});
						}
					}else{
						if($('.bfwc-selected-payment-method').length && !$('.select2-container.bfwc-select2-initialized').length){
							$('.bfwc-selected-payment-method').select2({
								containerCssClass: 'bfwc-select2-initialized',
								formatResult: payment_method.format_result,
								formatSelection: payment_method.format_selection
							});
						}
					}
				}
				
			},
			check_elements: function(){
				payment_method.initialize_elements();
			},
			payment_method_selected: function(e){
				
				if(payment_method.is_inline()){
					
					$(this).closest(payment_method.container).find('.braintree-payment-method').removeClass('selected');
					
					$(this).addClass('selected');
					
					$(this).closest(payment_method.container).find(payment_method.token_selector).val($(this).attr('data-token'));
				}
			},
			set_payment_method: function($element){
				
				if(payment_method.is_inline()){
					var token = $element.closest(payment_method.container).find('.braintree-payment-method.selected').attr('data-token');
					$element.closest(payment_method.container).find(payment_method.token_selector).val(token);
				}else if(payment_method.is_dropdown()){
					var token = $element.closest(payment_method.container).find('select.bfwc-selected-payment-method').val();
					$element.closest(payment_method.container).find(payment_method.token_selector).val(token);
				}
			},
			remove_selected_method: function($element){
				$element.closest(payment_method.container).find(payment_method.token_selector).val('');
			},
			has_payment_methods: function(){
				return $('.bfwc-payment-method-container').length;
			},
			display_saved_methods: function(e){
				e.preventDefault();
				
				payment_method.set_payment_method($(this));
				
				var element = $(this).closest(payment_method.container);
				
				$(this).closest(payment_method.container).find('select.bfwc-selected-payment-method').change();
				
				$(this).closest(payment_method.container).find(payment_method.new_method_container).slideUp(400, function(){
					$(this).closest(payment_method.container).find(payment_method.saved_method_container).slideDown(400);
					$(document.body).trigger('bfwc_display_saved_methods');
				});
			},
			display_new_payment_method_container: function(e){
				e.preventDefault();
				
				var element = $(this).closest(payment_method.container);
				
				payment_method.remove_selected_method($(this));
				
				$(this).closest(payment_method.container).find(payment_method.saved_method_container).slideUp(400, function(){
					$(this).closest(payment_method.container).find(payment_method.new_method_container).slideDown(400);
					$(document.body).trigger('bfwc_display_new_payment_method_container');
				});
			},
			update_card_from_select: function(){
				$(this).closest(payment_method.container).find(payment_method.token_selector).val($(this).val());
			},
			is_inline: function(){
				return braintree_payment_methods_vars.style === 'inline';
			},
			is_dropdown: function(){
				return braintree_payment_methods_vars.style === 'dropdown';
			},
			format_result: function(data, container){
				$(container).addClass('select2-bfwc-result-label');
				return '<span class="select2-cardType ' + braintree_payment_methods_vars.icon_style + ' ' + $(data.element).attr('data-bfwc-cardType') + '"></span>' + data.text;
			},
			format_selection: function(data, container){
				$(container).addClass('select2-bfwc-chosen');
				return '<span class="select2-cardType ' + braintree_payment_methods_vars.icon_style + ' '  + $(data.element).attr('data-bfwc-cardType') + '"></span>' + '<span>' + data.text + '</span>';
			},
			template_result: function(data, container){
				$(container).addClass('select2-bfwc-result-label');
				var html = '<span class="select2-cardType ' + braintree_payment_methods_vars.icon_style + ' '  + $(data.element).attr('data-bfwc-cardType') + '"></span>' + data.text;
				return $.parseHTML(html);
			},
			template_selection: function(data, container){
				$(container).addClass('select2-bfwc-chosen');
				$(container).closest('.select2-container').addClass('bfwc-select2-4');
				var html = '<span class="select2-cardType ' + braintree_payment_methods_vars.icon_style + ' '  + $(data.element).attr('data-bfwc-cardType') + '"></span>' + '<span>' + data.text + '</span>';
				return $.parseHTML(html);
			}
	}
	payment_method.init();
})