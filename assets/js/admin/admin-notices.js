jQuery(document).ready(function($){
	var notices = {
			init: function(){
				this.maybe_show_admin_notices();
			},
			maybe_show_admin_notices: function(){
				if(!braintree_admin_notices){
					return;
				}
				if(braintree_admin_notices.error){
					$.each(braintree_admin_notices.error, function(i){
						Materialize.toast('<span>' + braintree_admin_notices.error[i] + '<i class="material-icons close">close</i></span>', 'stay on', 'red lighten-2');
					});
				}
				if(braintree_admin_notices.success){
					$.each(braintree_admin_notices.success, function(i){
						Materialize.toast('<span>' + braintree_admin_notices.success[i] + '<i class="material-icons close">close</i></span>', 'stay on', 'admin-success-green');
					})
				}
				$('.toast i.close').on('click', notices.close_admin_notice);
			},
			close_admin_notice: function(){
				$(this).closest('.toast').fadeOut(400, function(){$(this).remove()});
			}
	}
	notices.init();
})