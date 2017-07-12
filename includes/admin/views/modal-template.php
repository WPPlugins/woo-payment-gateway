<div id="modal-<?php echo $env ?>" class="modal hide fade in"
	role="dialog" aria-labelledby="myModalLabel" aria-hidden="false"
	tabindex="-1">
	<div class="modal-header">
		<button type="button" class="close bfwc-close" data-dismiss="modal"
			aria-hidden="true"></button>
		<h3 id="myModalLabel"><?php echo $title?></h3>
	</div>
	<div class="modal-body" style="height: auto;">
		<div id="modal-content-<?php echo $env ?>"></div>
	</div>
	<div class="modal-footer">
		<button class="btn bfwc-tokenize button-primary button-large"
			data-dismiss="modal" value="tokenize" aria-hidden="true"><?php _e('Submit', 'braintree-payments')?></button>
		<button class="btn bfwc-close button"
			data-dismiss="modal" value="Close" aria-hidden="true"><?php _e('Close', 'braintree-payments')?></button>
	</div>
</div>