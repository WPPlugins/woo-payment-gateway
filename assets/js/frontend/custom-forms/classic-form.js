jQuery(document).ready(
		function($) {
			var ClassicForm = function() {

			}
			ClassicForm.notEmpty = function(e, event) {
				for ( var key in event.fields) {
					var field = event.fields[key];
					if (field.isEmpty) {
						$(field.container).prev().removeClass('active');
					} else {
						$(field.container).prev().addClass('active');
					}
				}
			};

			ClassicForm.empty = function(e, event) {
				var field = event.fields[event.emittedBy];
				$(field.container).prev().removeClass('active');
			}

			ClassicForm.onFocus = function(e, event) {
				var field = event.fields[event.emittedBy];
				if (event.emittedBy === 'cvv') {
					if (event.cards.length === 1) {
						if (event.cards[0].code.size === 3) {
							$(field.container).next('span').addClass(
									'cvv-image active');
						} else {
							$(field.container).next('span').addClass(
									'cvv-image-ax active');
						}
					} else {
						$(field.container).next('span').addClass(
								'cvv-image active');
					}

				} else {
					if(event.fields['cvv']){
						$(event.fields['cvv'].container).next('span').attr('class', 'cvv-image');
						$(field.container).parent('div').addClass('active');
					}
				}
			}

			ClassicForm.onBlur = function(e, event){
				var field = event.fields[event.emittedBy];
				$(field.container).parent('div').removeClass('active');
			}
			
			ClassicForm.validityChange = function(e, event) {
				var field = event.fields[event.emittedBy];
				if (field.isValid) {
					$(field.container).parent().removeClass('has-warning')
							.addClass('has-success');
				} else if (field.isPotentiallyValid) {
					$(field.container).parent().removeClass(
							'has-warning has-success');
				} else {
					$(field.container).parent().addClass('has-warning');
				}
			}

			ClassicForm.onTokenizationError = function(e, response){
				var err = response.err; var fields = response.fields;
				if(err.details && err.details.invalidFieldKeys.length > 0){
					for(var i=0;i<err.details.invalidFieldKeys.length;i++){
						var key = err.details.invalidFieldKeys[i];
						var field = fields[key];
						$(field.selector).parent('div').addClass('has-warning');
					}
				}else if(err.code === 'HOSTED_FIELDS_FIELDS_EMPTY'){
					$.each(fields, function(id, field){
						$(field.selector).parent('div').addClass('has-warning');
					})
				}
			}
			
			ClassicForm.updateContainerCSS = function(e){
				if($('#payment').width() < 545){
					$('#braintree_custom_form').addClass('small-container');
				}
			}
			
			$(document.body).on('braintree_field_not_empty',
					ClassicForm.notEmpty);
			$(document.body)
					.on('braintree_field_empty', ClassicForm.empty);
			$(document.body).on('braintree_card_type_change',
					ClassicForm.cardTypeChange);
			$(document.body).on('braintree_field_focus',
					ClassicForm.onFocus);
			$(document.body).on('braintree_field_validity_change',
					ClassicForm.validityChange);
			$(document.body).on('braintree_field_blur', ClassicForm.onBlur);
			$(document.body).on('braintree_tokenization_error', ClassicForm.onTokenizationError);
		});