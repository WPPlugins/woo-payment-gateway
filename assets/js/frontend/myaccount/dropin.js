jQuery(document).ready(function($){
	
	var dropin = {
			container_id: '#braintree-dropin-container',
			braintree: {},
			init: function(){
				setInterval(this.check_dropin_form, 2000);
				setInterval(this.remove_extra_frames, 500);
				$(document.body).on('init_add_payment_method', this.check_dropin_form);
				this.initialize_events();
			},
			initialize_events: function(){
				$('input[name="payment_method"]').on('change', dropin.payment_gateway_change);
				$('#braintree_events_initialized').val('true');
			},
			initialize_dropin: function(){
				if(! $(dropin.container_id).length){
					return;
				}
				var options = {
						container: 'braintree-dropin-form',
						onReady: function(integration){
							dropin.braintree.integration = integration;
							if(braintree_dropin_vars.advanced_fraud.enabled){
								dropin.initialize_advanced_fraud(integration);
							}
							
						},
						onError: function(err){
							dropin.submit_error(err.message);
						},
						onPaymentMethodReceived: function(response){
							dropin.on_payment_method_received(response);
						}
					};
				if(braintree_dropin_vars.advanced_fraud.enabled){
					options.dataCollector = {
							kount: {environment: braintree_dropin_vars.environment},
							paypal: true
					}
				}
				braintree.setup(braintree_dropin_vars.client_token, 'dropin', options);
			},
			initialize_advanced_fraud: function(integration){
				dropin.braintree.deviceData = integration.deviceData;
				$('#braintree_device_data').val(dropin.braintree.deviceData);
			},
			check_dropin_form: function(){
				if(!dropin.is_gateway_selected()){
					//braintree not selected so exit.
					return;
				}
				if(!dropin.are_events_initialized()){
					//form has been replaced through update_checkout so re-initialize element events.
					dropin.initialize_events();
				}
				var frame = $('#braintree-dropin-form').children('iFrame');
				if(!frame.length){
					dropin.initialize_dropin();
				}
			},
			remove_extra_frames: function(){
				$.each($('#braintree-dropin-form').find('iFrame'), function(index){
					if(index > 0){
						$(this).remove();
					}
				});
			},
			on_payment_method_received: function(response){
				dropin.payment_method_received = true;
				$('#payment_method_nonce').val(response.nonce);
				$(dropin.container_id).closest('form').submit();
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
			payment_gateway_change: function(e){
				if($('#payment_method_' + braintree_dropin_vars.gateway_id).is(':checked')){
						dropin.display_dropin(e);
				}else{
					if(dropin.braintree.integration){
						dropin.teardown_dropin();
					}
				}
			},
			display_dropin: function(e){
				e.preventDefault();
				dropin.initialize_dropin();
			},
			submit_error: function(message){
				$( '.woocommerce-error, .woocommerce-message' ).remove();
				$(dropin.container_id).closest('form').prepend( '<div class="woocommerce-error">'+message+'</div>' );
				$(dropin.container_id).closest('form').removeClass( 'processing' ).unblock();
				$(dropin.container_id).closest('form').find( '.input-text, select, input:checkbox' ).blur();
				$( 'html, body' ).animate({
					scrollTop: ( $(dropin.container_id).closest('form').offset().top - 100 )
				}, 1000 );
			},
			is_gateway_selected: function(){
				return $('#payment_method_' + braintree_dropin_vars.gateway_id).is(':checked');
			},
			are_events_initialized: function(){
				return ($('#braintree_events_initialized').val() === 'true');
			},
	}
	dropin.init();
})