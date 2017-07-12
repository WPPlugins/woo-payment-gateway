jQuery(document).ready(function($){
	
	var product = {
			init: function(){
				
				$('.bt-add-plan').on('click', this.add_plan);
				
				$('[name^="_braintree_subscription"]').on('change', this.update_billing_period);
				
				this.add_plan_events();
				
				$('#woocommerce-product-data').on('woocommerce_variations_loaded', this.update_billing_period);
				
				this.add_variation_events();
				
				$('#woocommerce-product-data').on('woocommerce_variations_loaded', this.show_subscription_fields);
				
				$('#woocommerce-product-data').on('woocommerce_variations_loaded', this.maybe_show_options);
				
				$('select#product-type').on('change', this.show_subscription_fields);
				
				$(document.body).find('a.select2').on('click', this.remove_plan);
				
				if(!braintree_product_data.enabled.wc_subscriptions_active){
					$('#_subscription_trial_length, [name^="variable_subscription_trial_length"]').on('paste keyup', this.update_trial_period);
				}
				
				this.update_billing_period();
				
				this.show_subscription_fields();
				
				$('[name="_braintree_subscription"]').on('change', this.maybe_show_options);
				
				this.maybe_show_options();
			},
			add_variation_events: function(){
				$(document.body).on('change', '[name^="variable_braintree_subscription"]', product.update_billing_period);
				if(!braintree_product_data.enabled.wc_subscriptions_active){
					$(document.body).on('paste keyup', '#_subscription_trial_length', product.update_trial_period);
					$(document.body).on('paste keyup', '[name^="variable_subscription_trial_length"]', product.update_trial_period)
				}
				
				$(document.body).on('change', '[name^="variable_braintree_subscription"]', product.maybe_show_options);
				
				product.maybe_show_options();
			},
			add_plan_events: function(){
				$(document.body).on('click', '#variable_product_options .bt-add-plan', product.add_plan);
				$(document.body).on('click', '#variable_product_options a.select2', product.remove_plan);
			},
			add_plan: function(e){
				e.preventDefault();
				var environment, plans, html, plan, selected_plan, name, input, product_type, loop, invalid, message;
				environment = $(this).attr('data-environment');
				plans = braintree_product_data.environments[environment].plans;
				selected_plan = $(this).closest('div.options_group').find('select.braintree-plans').val();
				product_type = $('select#product-type').val();
				plan = plans[selected_plan];
				if(!plan){
					return;
				}
				html = braintree_product_data.products[product_type].html;
				if(product_type === 'subscription' || product_type === 'braintree-subscription'){
					html = html.replace(/%env/g, environment).replace(/%curr/g, plan.currencyIsoCode).replace(/%value/g, plan.id);
				}else if(product_type === 'variable-subscription' || product_type === 'braintree-variable-subscription'){
					loop = $(this).attr('data-loop');
					html = html.replace(/%env/g, environment).replace(/%curr/g, plan.currencyIsoCode).replace(/%value/g, plan.id).replace(/%loop/g, loop);
				}
				html = html.replace(/%desc/g, plan.name + '( ' + plan.currencyIsoCode + ' )');
				name = $(html).find('input').attr('name');
				input = $('input[name="'+name+'"]');
				if(input.length){
					if(input.attr('name').match(/\[[a-zA-Z]{3}\]/)){
						plan = plans[input.val()];
						window.alert(braintree_product_data.messages.duplicate.replace(/%s/g, plan.name).replace(/%c/g, plan.currencyIsoCode));
						return;
					}
				}
				$(this).closest('div.options_group').find('.product-plan input[type="hidden"]').each(function(){
					saved_plan = plans[$(this).val()];
					if(plan.billingFrequency !== saved_plan.billingFrequency){
						message = braintree_product_data.messages.invalid_frequency.replace(/%s/g, plan.name);
						invalid = true;
						return false;
					}
				});
				if(invalid){
					window.alert(message);
					return;
				}
				product.hide_invalid_intervals(plan, $(this));
				if(product.is_simple_subscription()){
					$('#_subscription_period_interval').val(plan.billingFrequency);
				}else{
					$(this).closest('.woocommerce_variable_attributes').find('[name^="variable_subscription_period_interval"]').val(plan.billingFrequency);
				}
				$(this).closest('div.options_group').find('ul.ul-choices.' + environment).append(html);
				$(this).closest('div.options_group').find('a.select2').on('click', product.remove_plan);
				
				product.variation_changed();
				
				$(this).closest('.woocommerce_options_panel, .woocommerce_variation').find('[name*="_subscription_length"]').find('option').each(function(){
					var freq = parseInt($(this).val());
					if(freq % plan.billingFrequency !== 0){
						$(this).hide();
					}else{
						$(this).show();
					}
				
				})
				
				$(this).closest('.woocommerce_options_panel, .woocommerce_variation').find('[name*="_subscription_period"]').find('option').each(function(){
					if($(this).val() !== 'month'){
						$(this).hide();
					}
				
				})
			},
			remove_plan: function(e){
				e.preventDefault();
				var $container = $(this).closest('ul.ul-choices');
				$(this).closest('li').remove();
				product.variation_changed();
				
				if($container.find('li').length === 0){
					
					$container.closest('.woocommerce_options_panel, .woocommerce_variation').find('[name*="_subscription_length"]').find('option').each(function(){
						$(this).show();
					});
					
					$container.closest('.woocommerce_options_panel, .woocommerce_variation').find('[name*="subscription_period_interval"]').find('option').each(function(){
						$(this).show();
					})
					
					$container.closest('.woocommerce_options_panel, .woocommerce_variation').find('[name*="_subscription_period"]').find('option').each(function(){
						$(this).show();
					})
				}
		
			},
			variation_changed: function(element){
				
				$('.woocommerce_variation').addClass('variation-needs-update');

				$( 'button.cancel-variation-changes, button.save-variation-changes' ).removeAttr( 'disabled' );

				$( '#variable_product_options' ).trigger( 'woocommerce_variations_input_changed' );
			},
			update_billing_period: function(e){
				$('select#product-type, [name^="_braintree_subscription"], [name^="variable_braintree_subscription"]').each(function(){
					var name = $(this).attr('name'), 
					matches = $(this).attr('name').match(/\[([\d+])\]/), billing_selector, billing_interval, 
					frequency, trial_period_selector, checkbox, post_id, is_product_type = false;
					if(matches){
						billing_selector = '[name="variable_subscription_period[' + matches[1] + ']"]';
						billing_interval = '[name="variable_subscription_period_interval[' + matches[1] + ']"]';
						trial_period_selector = '[name="variable_subscription_trial_period[' + matches[1] + ']"]';
						checkbox = '[name="variable_braintree_subscription[' + matches[1] + ']"]';
						post_id = $('[name="variable_post_id['+matches[1] + ']"]').val();
					}else{
						if($('select#product-type').val() === 'braintree-subscription' || $('select#product-type').val() === 'braintree-variable-subscription'){
							is_product_type = true;
						}
						billing_selector = '#_subscription_period';
						billing_interval = '#_subscription_period_interval';
						trial_period_selector = '#_subscription_trial_period';
						checkbox = '#braintree_subscription';
						post_id = $('#post_ID').val();
					}
					length = $(checkbox).length;
					if($(checkbox).is(':checked') || is_product_type){
						$(billing_selector).val('month');
						$(billing_selector + ' option').each(function(){
							if($(this).val() !== 'month'){
								$(this).hide();
							}
						})
						$(trial_period_selector  + ' option').each(function(){
							if($(this).val() !== 'day' && $(this).val() !== 'month'){
								$(this).hide();
							}
						})
						$(billing_interval + ' option').each(function(){
							frequency = braintree_product_data.posts[post_id];
							if(frequency){
								if($(this).val() !== frequency){
									$(this).hide();
								}else{
									$(this).show();
								}
							}
						})
						//$(billing_selector + ',' + trial_period_selector).trigger('change');
					}else{
						$(billing_selector  + ' option').each(function(){
							$(this).show();
						});
						$(trial_period_selector  + ' option').each(function(){
							$(this).show();
						})
						$(billing_interval + ' option').each(function(){
							$(this).show();
						})
					}
				})
			},
			hide_invalid_intervals: function(plan, element){
				if(product.is_simple_subscription()){
					$('#_subscription_period_interval option').each(function(){
						if($(this).val() !== plan.billingFrequency){
							$(this).hide();
						}
					})
				}else{
					if(element){
						$(element).closest('.woocommerce_variable_attributes').find('[name^="variable_subscription_period_interval"] option').each(function(){
							if($(this).val() !== plan.billingFrequency){
								$(this).hide();
							}
						})
					}
				}
			},
			is_simple_subscription: function(){
				var product_type = $('#product-type').val();
				return product_type === 'subscription' || product_type === 'braintree-subscription';
			},
			update_trial_period: function(){
				$('[name="_subscription_trial_length"], [name^="variable_subscription_trial_length"]').each(function(){
					var trialLengthElement = $(this), val = $(this).val(), type, matches, selector, texts;
					val = parseInt(val);
					matches = $(this).attr('name').match(/variable[\w]*\[([\d+])\]/);
					if(matches){
						selector = '[name="variable_subscription_trial_period['+matches[1] + ']"]';
					}else{
						selector = '[name="_subscription_trial_period"]';
					}
					if(val <= 1){
						texts = braintree_product_data.trial_period_singular;
					}else if (val > 1){
						texts = braintree_product_data.trial_period_plural;
					}else{
						return;
					}
					$(selector + ' option').each(function(){
						$(this).text(texts[$(this).val()]);
					})
				})
			},
			show_subscription_fields: function(e){
				var product_type = $('select#product-type').val();
				if(product_type === 'braintree-subscription'){
					$('.show_if_simple').show();
					$('.options_group.pricing ._regular_price_field ').hide();
				}else if(product_type === 'braintree-variable-subscription'){
					$('.show_if_variable').show();
					$('.show_if_braintree-variable-subscription').show();
					$('.variable_pricing [name^="variable_regular_price"]').closest('p.form-row').hide();
				}else{
					$('.options_group.pricing ._regular_price_field ').show();
					$('.variable_pricing [name^="variable_regular_price"]').closest('p.form-row').show();
					$('.options_group').each(function(){
						if($(this).attr('class').match(/show_if_braintree-/)){
							$(this).hide();
						}
					})
				}
			},
			maybe_show_options: function(){
				$('[name="_braintree_subscription"], [name^="variable_braintree_subscription"]').each(function(){
					if($(this).is(':checked')){
						$(this).closest('.bfwc-subscription-options').find('.show_if_braintree_subscription_checked').show()
					}else{
						$(this).closest('.bfwc-subscription-options').find('.show_if_braintree_subscription_checked').hide()
					}
				})
			}
	}
	product.init();
	
})