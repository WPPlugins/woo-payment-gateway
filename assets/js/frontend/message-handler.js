jQuery(document).ready(function($){
	
	var handler = {
			init: function(){
				
				$(document.body).on('bfwc_submit_error', this.handle_error_message);
			},
			handle_error_message: function(e, data){
				handler.element = data.element;
				var message = data.error.message;
				var code = handler.get_code(data);
				
				if(code){
					message = braintree_message_handler_vars.messages[code] ? braintree_message_handler_vars.messages[code] : data.error.message;
				}
				
				handler.submit_error(message);
			},
			submit_error: function(message){
				$( '.woocommerce-error, .woocommerce-message' ).remove();
				$(handler.element).closest('form').prepend( '<div class="woocommerce-error">'+message+'</div>' );
				$(handler.elementd).closest('form').removeClass( 'processing' ).unblock();
				$(handler.element).closest('form').find( '.input-text, select, input:checkbox' ).blur();
				$( 'html, body' ).animate({
					scrollTop: ( $(handler.element).closest('form').offset().top - 100 )
				}, 1000 );
			},
			get_code: function(data){
				if(data.error.code){
					return data.error.code;
				}else if(data.error.type){
					return data.error.type;
				}else if(data.error.message){
					return false;
				}
			}
	}
	handler.init();
})