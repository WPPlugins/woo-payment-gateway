jQuery(function($){
	
	if(typeof braintree_fees_vars === 'undefined'){
		return;
	}
	
	var fees = {
			init: function(){
				if(braintree_fees_vars.fees.enabled){
					$(document.body).on('change', 'input[name="payment_method"]', this.maybe_update_fee);
				}
			},
			maybe_update_fee: function(){
				$( document.body ).trigger( 'update_checkout' );
			}
	}
	fees.init();
});