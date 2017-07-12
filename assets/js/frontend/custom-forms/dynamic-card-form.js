jQuery(document).ready(function($){
	var DynamicForm = function(){
	};
	DynamicForm.CardTypeChange = function(e, event){
		if(event.cards.length == 1){
			$('#dynamic-card-form').removeClass().addClass(event.cards[0].type);
			$('.card-container header').addClass('header-slide');
			$('#card-image').removeClass().addClass(event.cards[0].type);
		}else{
			$('#dynamic-card-form').removeClass();
			$('.card-container header').removeClass();
			$('#card-image').removeClass()
		}
	};
	
	$(document.body).on('braintree_card_type_change', DynamicForm.CardTypeChange);
})