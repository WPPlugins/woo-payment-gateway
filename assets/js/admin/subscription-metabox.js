jQuery(document).ready(
		function($) {

			var metabox = {
				init : function() {
					this.initialize_select2();

					this.update_billing_frequency();					
					
					$('select#_subscription_plan').on('change',
							this.update_billing_frequency);
					
					$('[name^="_subscription_trial_length"]').on('paste keyup', this.update_trial_period_text);
					
					$('[name^="_subscription_plan"]').on('change', this.update_subscription_length);
				},
				initialize_select2 : function() {
					$('.bfwc-admin-select2').select2({
							width: "100%",
							placeholder : bfwc_subscription_vars.plan_placeholder,
							allowClear : true
					});
				},
				update_billing_frequency : function() {
					var plan, frequency, text;
					plan = $('select#_subscription_plan').val();
					frequency = bfwc_subscription_vars.plans[plan].billingFrequency;
					text = bfwc_subscription_vars.billing_frequency_text.intervals[frequency];
					$('#billing_frequency').text(text);
				},
				update_trial_period: function(){
					var length, type, trial_period;
					length = $(this).val();
					trial_period = $('[name="_subscription_trial_period"]').val();
					text = bfwc_subscription_vars.trial_text[length > 1 ? 'plural' : 'singular'][trial_period];
					$('#_subscription_trial_period').text(text).trigger('change');
				},
				update_trial_period_text: function(e){
					var val = $(this).val(),
					integer = parseInt(val),
					type = '',
					$trial_periods = $('#_subscription_trial_period');
					
					if(integer <= 1){
						type = 'singular';
					}else{
						type = 'plural';
					}
					$($trial_periods).find('option').each(function(){
						$(this).text(bfwc_subscription_vars.trial_text[type][$(this).val()]);
					})
					$trial_periods.select2();
				},
				update_subscription_length: function(e){
					var plan = $('#_subscription_plan').val(),
					frequency = bfwc_subscription_vars.plans[plan].billingFrequency;
					
					$('#_subscription_length').find('option').each(function(){
						var value = parseInt($(this).val());
						
						if((value % frequency) !== 0){
							$(this).prop('disabled', true);
						}else{
							$(this).prop('disabled', false);
						}
					});
					$('#_subscription_length').select2();
				}
			}
			metabox.init();
		})