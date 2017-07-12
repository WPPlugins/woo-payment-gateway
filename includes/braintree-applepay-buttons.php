<?php
/**
 * Apple Pay buttons.
 */

return apply_filters( 'bwc_applepay_buttons', array ( 
		'black_logo' => array ( 
				'label' => __( 'Black Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__black_logo_@2x.png' 
		), 
		'black_textLogo' => array ( 
				'label' => __( 'Black Text Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__black_textLogo_@2x.png' 
		), 
		'white_logo' => array ( 
				'label' => __( 'White Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__white_logo_@2x.png' 
		), 
		'white_textLogo' => array ( 
				'label' => __( 'White Text Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__white_textLogo_@2x.png' 
		), 
		'whiteLine_logo' => array ( 
				'label' => __( 'White Line Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__whiteLine_logo_@2x.png' 
		), 
		'whiteLine_textLogo' => array ( 
				'label' => __( 'White Line Text Logo', 'braintree-payments' ), 
				'src' => BRAINTREE_GATEWAY_ASSETS . 'img/applepay/ApplePayBTN_32pt__whiteLine_textLogo_@2x.png' 
		) 
) );