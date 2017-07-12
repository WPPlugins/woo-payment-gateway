jQuery(document).ready(function($){
	var donation = {
			init: function(){
				//$('select').material_select();
				$('select').select2();
				
				$('.modal-trigger').leanModal();
				
				$('.bfwc-close-modal').on('click', this.close_modal);
				
				$('a.edit_donation_address').on('click', this.display_edit_address);
				
				$('a.close_edit_address').on('click', this.close_edit_address);
				
				$('#braintree_donation_actions').on('change', this.donation_action_change);
				
				$('#transaction-table').dataTable();
				
				$('.dataTable').dataTable();
				
				this.donation_action_change();
			},
			display_edit_address: function(e){
				e.preventDefault();
				$(this).closest('.card-panel').find('div.donation_address').hide();
				$(this).closest('.card-panel').find('div.edit_donation_address').show();
				$(this).hide();
				$(this).next().show();
			},
			close_edit_address: function(e){
				e.preventDefault();
				$(this).closest('.card-panel').find('div.edit_donation_address').hide();
				$(this).closest('.card-panel').find('div.donation_address').show();
				$(this).hide();
				$(this).prev().show();
			},
			donation_action_change: function(e){
				var val = $('#braintree_donation_actions').val();
				$('[data-show-if]').each(function(){
					if($(this).attr('data-show-if') === val){
						$(this).slideDown();
					}else{
						$(this).slideUp();
					}
				})
			},
			output_error: function(message){
				Materialize.toast(message, 4000, 'red lighten-2');
			},
			output_success: function(message){
				
			},
			close_modal: function(){
				$(this).closest('.modal').closeModal();
			}
	}
	donation.init();
})