jQuery(document).ready(function($){
	
	var form = {
			init: function(){
				$(document.body).on('braintree_card_type_change', this.card_type_change);
				
				$(document.body).on('bfwc_container_size_check', this.container_size_check);
			},
			card_type_change: function(e, event){
				if(event.cards.length === 1){
					$('.bfwc-card-form').addClass(event.cards[0].type);
					form.current_card_type = event.cards[0].type;
				}else{
					$('.bfwc-card-form').removeClass(form.current_card_type);
				}
			},
			container_size_check: function(e, fields){
				if($(fields.number.selector).width() < 240){
					$(fields.number.selector).addClass('bfwc-card-small-width');
				}else{
					$(fields.number.selector).removeClass('bfwc-card-small-width');
				}
				if($('.bfwc-card-form').width() < 341){
					$('.bfwc-card-form').addClass('bfwc-small-form');
				}else{
					$('.bfwc-card-form').removeClass('bfwc-small-form');
				}
			}
	}
	form.init();
})