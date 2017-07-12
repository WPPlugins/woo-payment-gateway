jQuery(document).ready(function($){
	var BootstrapForm = function(){}
	
	BootstrapForm.validityChange = function(e, event){
		var field = event.fields[event.emittedBy];
		if (field.isValid) {
			$(field.container).parents('.form-group').removeClass('has-warning').addClass(
					'has-success');
			$(field.container).removeClass('braintree-hosted-fields-focused');
		} else if (field.isPotentiallyValid) {
			$(field.container).parents('.form-group').removeClass(
					'has-success');
			$(field.container).parents('.form-group').removeClass(
					'has-warning');
		} else {
			$(field.container).parents('.form-group').removeClass('has-success').addClass(
					'has-warning');
			$(field.container).removeClass('braintree-hosted-fields-focused');
		}
	}
	
	BootstrapForm.onTokenizationError = function(e, response){
		var err = response.err; var fields = response.fields;
		if(err.details && err.details.invalidFieldKeys.length > 0){
			for(var i=0;i<err.details.invalidFieldKeys.length;i++){
				var key = err.details.invalidFieldKeys[i];
				var field = fields[key];
				$(field.selector).parents('.form-group').addClass('has-warning');
			}
		}
	}
	
	BootstrapForm.updateContainerCSS = function(e){
		if($('#payment').width() < 450){
			$('#braintree_custom_form').addClass('small-container');
		}
	}
	
	$(document.body).on('braintree_custom_form_loaded', BootstrapForm.updateContainerCSS);
	$(document.body).on('braintree_field_validity_change', BootstrapForm.validityChange);
	$(document.body).on('braintree_tokenization_error', BootstrapForm.onTokenizationError);
	
});