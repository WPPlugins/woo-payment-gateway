jQuery(document).ready(function($){
	var log = {
			init: function(){
				$('#log-entries').DataTable({
					dom: '<"top"Brlfip<"clear">>rt<"bottom"ip<"clear">>',
					buttons: ['csv', 'excel', 'pdf']
				});
				$('div.dt-buttons a.dt-button').addClass('waves-effect waves-light btn braintree-grey');
				$('.dataTables_length select').show();
				$('div.dataTables_paginate').addClass('pagination');
				$('.dataTables_filter input[type="search"]').attr('placeholder', braintree_log_vars.search_placeholder);
			}
	}
	log.init();
})