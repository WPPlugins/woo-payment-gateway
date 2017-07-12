jQuery(function($){
	
	if(typeof braintree_form_handler_vars === 'undefined'){
		return;
	}
	
	var handler = {
			init: function(){
				
				$(document.body).on('updated_checkout', this.maybe_unblock_checkout_payment);
				
				if(!$(document.body).hasClass('woocommerce-checkout')){
					
					$('input[name="payment_method"]').closest('form').on('submit', this.submit_form);;
					
				}else{
					this.order_button_text = $('#place_order').val();
					$(document.body).on('click', '[name="payment_method"]', this.payment_method_selected);
				}
				
				$(document.body).on('click', '#place_order', this.pre_form_submit);
				
			},
			submit_form: function(){
				var $form = $(this),
				payment_gateway = $('input[name="payment_method"]').length ? $('input[name="payment_method"]:checked').val() : '';
				
				if($form.triggerHandler('woocommerce_form_submit_' + payment_gateway) !== false){
					return true;
				}else{
					e.preventDefault();
					return false;
				}
			},
			maybe_unblock_checkout_payment: function(){
				if(!braintree_form_handler_vars.cart_fragments.refresh){
					$('.woocommerce-checkout-payment').unblock();
				}
			},
			pre_form_submit: function(e){
				$(document.body).triggerHandler('bfwc_pre_form_submit_' + handler.get_gateway());
			},
			get_gateway: function(){
				return $('input[name="payment_method"]:checked').val();
			},
			payment_method_selected: function(){
				var result = $(document.body).triggerHandler('bfwc_payment_method_selected', handler.get_gateway());
				if(result !== true){
					$('#place_order').val(handler.order_button_text);
				}
			}
	}
	
	handler.init();
});