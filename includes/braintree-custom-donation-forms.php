<?php
return apply_filters( 'bfwc_get_custom_donation_forms', array ( 
		'bootstrap_form' => array ( 
				'description' => 'Bootstrap Form', 
				'html' => '/donations/forms/bootstrap-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/bootstrap-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/bootstrap-form.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"helvetica, tahoma, calibri, sans-serif"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'google_material_design' => array ( 
				'description' => 'Google Material Design', 
				'html' => '/donations/forms/google-material-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/google-material-design.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/google-material-design.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"roboto, verdana, sans-serif"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'dynamic_card_form' => array ( 
				'description' => 'Dynamic Card Form', 
				'html' => '/donations/forms/dynamic-card-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/dynamic-card-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/dynamic-card-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, "input.invalid":{"color":"#E53A40"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'3d_card_form' => array ( 
				'description' => '3D Card Form', 
				'html' => '/donations/forms/3d-card-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/3d-card-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/3d-card-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'classic_form' => array ( 
				'description' => 'Classic Form', 
				'html' => '/donations/forms/classic-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/classic-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/classic-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, ".invalid":{"color":"#D0021B"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'simple_form' => array ( 
				'description' => 'Simple Form', 
				'html' => '/donations/forms/simple-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/simple-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/simple-form.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"courier, monospace"}, ".valid":{"color":"rgb(94, 189, 128)"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		),
		'card_shape_form' => array(
				'description' => 'Card Shape Form',
				'html' => '/donations/forms/card-shape-form.php',
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/card-shape-form.css',
				'external_css' => '',
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/card-shape-form.js',
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"courier, monospace", "font-weight":"500"}}'
		)
) );