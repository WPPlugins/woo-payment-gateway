jQuery(document).ready(function($){
	
	var order = {
			init: function(){
				$('select').on('change', this.display_meta_box);
				this.display_meta_box();
			},
			display_meta_box: function(e){
				var value = $('select[name="wc_order_action"]').val();
				switch(value){
				case 'braintree_submit_for_settlement':
					$('#braintree-woocommerce-settlement-amount').slideDown();
					break;
					default:
						$('#braintree-woocommerce-settlement-amount').slideUp();
						break;
				}
			}
	};
	order.init();
})