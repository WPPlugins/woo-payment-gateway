<?php
/**
 * @version 2.6.9
 */
bwc_payment_method_token_field( $token_id, $default_method [ 'token' ] );
?>
<div id="braintree_payment_methods"
	class="bfwc-payment-method-container">

	<?php bwc_get_template('new-method-button.php', array('text' => $button_text))?>
	
	<?php do_action('bfwc_before_braintree_payment_methods');?>
	
	<?php if(bwc_saved_payment_method_style() === 'inline'):?>
	
		<?php
		
foreach ( $methods as $key => $method ) :
			bwc_get_template( 'braintree-payment-method.php', array (
					'key' => $key, 
					'method' => $method, 
					'default_method' => $default_method 
			) );
		endforeach
		;
		?>
			
	<?php elseif (bwc_saved_payment_method_style() === 'dropdown'):?>
			<?php bwc_get_template('payment-method-dropdown.php', array('id'=>'bfwc-selected-card', 'methods'=>$methods, 'default_method'=>$default_method))?>
	<?php endif;?>
</div>