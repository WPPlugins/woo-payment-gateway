<?php
return apply_filters( 'bwc_get_custom_forms', array ( 
		'bootstrap_form' => array ( 
				'label' => 'Bootstrap Form', 
				'html' => 'custom-forms/bootstrap-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/bootstrap-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/bootstrap-form.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"helvetica, tahoma, calibri, sans-serif"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'google_material_design' => array ( 
				'label' => 'Google Material Design', 
				'html' => 'custom-forms/google-material-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/google-material-design.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/google-material-design.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"roboto, verdana, sans-serif"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'dynamic_card_form' => array ( 
				'label' => 'Dynamic Card Form', 
				'html' => 'custom-forms/dynamic-card-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/dynamic-card-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/dynamic-card-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, "input.invalid":{"color":"#E53A40"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'3d_card_form' => array ( 
				'label' => '3D Card Form', 
				'html' => 'custom-forms/3d-card-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/3d-card-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/3d-card-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'classic_form' => array ( 
				'label' => 'Classic Form', 
				'html' => 'custom-forms/classic-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/classic-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/classic-form.js', 
				'default_styles' => '{"input":{"font-size":"16px"}, ".invalid":{"color":"#D0021B"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		), 
		'simple_form' => array ( 
				'label' => 'Simple Form', 
				'html' => 'custom-forms/simple-form.php', 
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/simple-form.css', 
				'external_css' => '', 
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/simple-form.js', 
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"courier, monospace"}, ".valid":{"color":"rgb(94, 189, 128)"}, "@media screen and (max-width: 375px)":{"input":{"font-size":"14px"}}}' 
		),
		'card_shape_form' => array(
				'label' => 'Card Shape Form',
				'html' => 'custom-forms/card-shape-form.php',
				'css' => BRAINTREE_GATEWAY_ASSETS . 'css/custom-forms/card-shape-form.css',
				'external_css' => '',
				'javascript' => BRAINTREE_GATEWAY_ASSETS . 'js/frontend/custom-forms/card-shape-form.js',
				'default_styles' => '{"input":{"font-size":"16px", "font-family":"courier, monospace", "font-weight":"500"}}'
		)
) );