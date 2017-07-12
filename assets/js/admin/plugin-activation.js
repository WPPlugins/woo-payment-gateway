jQuery(document).ready(function($){
	
	var plugin = {
			init: function(){
				$('.wp-admin').prepend(bfwc_admin_plugin_activated.html);
				
				$('#activation-modal').openModal();
			}
	}
	plugin.init();
})