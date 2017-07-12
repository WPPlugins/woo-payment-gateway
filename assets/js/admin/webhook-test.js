jQuery(document).ready(function($){
	
	var webhooks = {
			init: function(){
				$('.webhook-button').on('click', this.test_webhook);
				$('#webhook-main').prepend('<div class="webhook-overlay"></div>');
				$('#bfwc_admin_webhook').on('change', this.show_fields);
				this.show_fields();
			},
			show_fields: function(){
				var hook = $('#bfwc_admin_webhook').val();
				$.each(webhooks.field_regex, function(index, data){
					if(hook.match(data.regex)){
						$('[name^="' + data.name + '"]').closest('div.row').slideDown();
					}else{
						$('[name^="' + data.name + '"]').closest('div.row').slideUp();
					}
				})
			},
			field_regex: [{regex : /^subscription_/, name: 'subscription_'}, {regex : /^transaction_/, name: 'transaction_'}],
			test_webhook: function(e){
				e.preventDefault();
				var hook, id = '', result;
				hook = $('#bfwc_admin_webhook').val();
				if(hook.match(/^subscription_/)){
					id = $('#subscription_to_test').val();
				}
				webhooks.trigger_spinner();
				$.ajax({
					url: braintree_webhook_vars.ajax_url,
					dataType: 'json',
					method: 'POST',
					data: $('#webhook-form').serialize(),
					success: function(response){
						if(!response.success){
							webhooks.remove_spinner();
							webhooks.submit_error(response.message)
						}else{
							webhooks.send_payload(response.data);
						}
					},
					error: function(xhr, status, errorThrown){
						
					}
				})
			},
			send_payload: function(data){
				$.ajax({
					url: braintree_webhook_vars.webhook_url,
					dataType: 'json',
					method: 'POST',
					data: data,
					contentType: 'application/x-www-form-urlencoded',
					success: function(response){
						webhooks.remove_spinner();
						if(response.success){
							webhooks.submit_success(response.message);
						}else{
							webhooks.submit_error(response.message);
						}
					},
					error: function(xhr, status, errorThrown){
						webhooks.remove_spinner();
						webhooks.submit_error(xhr.responseJSON.message);
						//webhooks.submit_error(xhr.responseText);
					}
				})
			},
			submit_error: function(message){
				Materialize.toast(message, 7000, 'red lighten-2');
			},
			submit_success: function(message){
				Materialize.toast(message, 7000, 'admin-success-green');
			},
			trigger_spinner: function(){
				var content = $('#webhook-main')[0];
				$('.webhook-overlay').fadeIn(300, function(){
					webhooks.spinner = new Spinner(webhooks.spinner_opts).spin(content);
				});
			},
			remove_spinner: function(){
				$('.webhook-overlay').fadeOut(300, function(){
					webhooks.spinner.stop();
				})
			},
			spinner_opts: {
				  lines: 13 // The number of lines to draw
				, length: 28 // The length of each line
				, width: 14 // The line thickness
				, radius: 42 // The radius of the inner circle
				, scale: 1 // Scales overall size of the spinner
				, corners: 1 // Corner roundness (0..1)
				, color: '#fff' // #rgb or #rrggbb or array of colors
				, opacity: 0.25 // Opacity of the lines
				, rotate: 0 // The rotation offset
				, direction: 1 // 1: clockwise, -1: counterclockwise
				, speed: 1 // Rounds per second
				, trail: 60 // Afterglow percentage
				, fps: 20 // Frames per second when using setTimeout() as a fallback for CSS
				, zIndex: 2e9 // The z-index (defaults to 2000000000)
				, className: 'webhookSpinner' // The CSS class to assign to the spinner
				, top: '50%' // Top position relative to parent
				, left: '50%' // Left position relative to parent
				, shadow: false // Whether to render a shadow
				, hwaccel: false // Whether to use hardware acceleration
				, position: 'absolute' // Element positioning
			}
	}
	webhooks.init();
})