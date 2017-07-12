jQuery(document).ready(function($){
	
	messages = {
			init: function(){
				
				$('#bfwc_message_code').on('change', this.change_message);
				
				$('#bfwc_message').on('paste, keyup', this.update_message);
				
				$('#bfwc_save_message').on('click', this.parse_messages);
				
				$('.bfwc-messages-select2').select2();
				
				this.change_message();
			},
			change_message: function(){
				var code, message;
				code = $('#bfwc_message_code').val();
				message = bfwc_admin_messages[code];
				$('#bfwc_message').val(message);
			},
			update_message: function(e){
				var message = $(this).val();
				var code = $('#bfwc_message_code').val();
				bfwc_admin_messages[code] = message;
			},
			parse_messages: function(e){
				$('[name="bfwc_admin_messages"]').val(JSON.stringify(bfwc_admin_messages));
			}
	}
	messages.init();
})