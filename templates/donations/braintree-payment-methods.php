<?php
/**
 * @version 2.6.9
 */
braintree_payment_token_field($token_id, $default_method['token'])?>
<div id="braintree_payment_methods" class="bfwc-payment-method-container">

	<?php bfwc_get_template('donations/new-method-button.php', array('text' => $button_text))?>
	<?php bfwc_get_template('donations/payment-method-dropdown.php', array('id'=>'bfwc-selected-card', 'methods'=>$methods, 'default_method'=>$default_method))?>
</div>