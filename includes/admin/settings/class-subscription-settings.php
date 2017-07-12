<?php

class Braintree_Gateway_Subscription_Settings extends Braintree_Gateway_Settings_API
{

	public function __construct()
	{
		$this->page = 'braintree-gateway-settings';
		$this->id = 'subscription_settings';
		$this->tab = 'subscription-settings';
		$this->label = __( 'Subscriptions', 'braintree-payments' );
		$this->title = array (
				'title' => __( 'Subscription Settings', 'braintree-payments' ), 
				'description' => __( 'If you do not have the WooCommerce Subscriptions plugin then you can use this plugin\'s subscription functionality.', 'braintree-payments' ) 
		);
		add_filter( 'braintree_settings_localized_variables', array (
				$this, 
				'localize_vars' 
		) );
		
		add_action( 'init', array (
				$this, 
				'maybe_set_test_mode' 
		), 900 );
		
		parent::__construct();
	}

	public function localize_vars( $vars )
	{
		$vars [ 'subscriptions' ] = array (
				'wcs_active' => bt_manager()->is_woocommerce_subscriptions_active() 
		);
		return $vars;
	}

	public function settings()
	{
		$settings = array (
				'my_account_subscriptions' => array (
						'type' => 'checkbox', 
						'title' => 'My Account Subscriptions Link', 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, the WooCommerce My Account page will have a link to the customer\'s subscriptions. This option should be disabled if you don\'t sell subscriptions in your store.', 'braintree-payments' ) 
				), 
				'subscription_gateway_timezone' => array (
						'type' => 'select', 
						'title' => __( 'Gateway Timezone', 'braintree-payments' ), 
						'options' => bfwc_get_timezones(), 
						'default' => 'America/Chicago', 
						'tool_tip' => true, 
						'description' => __( 'This is the timezone of your Braintree gateway. This value is used to calculate the Braintree subscription start dates.' ), 
						'helper' => array (
								'enabled' => true, 
								'title' => __( 'Gateway Timezone', 'braintree-payments' ), 
								'description' => __( 'The gateway timezone is the timezone that your subscriptions are created in. To locate your gateay timezone, login to the Braintree control panel and navigate to the processing page and scroll to the bottom.', 'braintree-payments' ), 
								'type' => 'img', 
								'url' => 'https://wordpress.paymentplugins.com/woo-payment-gateway/assets/subscription_gateway_timezone.png' 
						) 
				), 
				'woocommerce_subscriptions_prefix' => array (
						'type' => 'text', 
						'value' => '', 
						'default' => '', 
						'title' => __( 'Subscription Prefix', 'braintree-payments' ), 
						'class' => '', 
						'tool_tip' => true, 
						'description' => __( 'If you would like the subscription order ID to contain an order prefix, you can add one here. If left blank, the order id within Braintree
						will match the WooCommerce order id.', 'braintree-payments' ) 
				), 
				'braintree_subscription_combine_same_products' => array (
						'type' => 'checkbox', 
						'title' => __( 'Combine Subscriptions', 'braintree-payments' ), 
						'default' => '', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, same products will be combined into one subscription instead of multiple subscriptions. For example, if a customer adds
								two of the same type of subscription, they will combined into one since they share the same billing schedule. Products with different currencies will not be combined.', 'braintree-payments' ) 
				), 
				'wcs_enable_test_mode' => array (
						'type' => 'checkbox', 
						'title' => __( 'Enable Test Mode', 'braintree-payments' ), 
						'default' => 'no', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If enabled, you will be able to test WooCommerce Subscriptions using the action scheduler.', 'braintree-payments' ) 
				), 
				'wcs_retry_after_exception' => array (
						'type' => 'checkbox', 
						'title' => __( 'Retry After Exception', 'braintree-payments' ), 
						'default' => 'yes', 
						'value' => 'yes', 
						'tool_tip' => true, 
						'description' => __( 'If WooCommerce Subscriptions is managing your recurring payments then this setting will cause a recurring payment to retry if an exception is thrown while payment is being processed. Exceptions can be thrown if your API keys are incorrect, there is a network error, etc.
								If not enabled, then the renewal order will be assigned a failed status and the subscription will be assigned an on hold status.', 'braintree-payments' ) 
				) 
		);
		
		if ( bt_manager()->is_woocommerce_subscriptions_active() ) {
			unset( $settings [ 'my_account_subscriptions' ] );
		}
		return $settings;
	}

	public function maybe_set_test_mode()
	{
		if ( is_admin() && bt_manager()->is_active( 'wcs_enable_test_mode' ) ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG === false ) {
				global $wp_post_types;
				if ( class_exists( 'ActionScheduler_wpPostStore' ) && isset( $wp_post_types [ ActionScheduler_wpPostStore::POST_TYPE ] ) ) {
					$post_type = $wp_post_types [ ActionScheduler_wpPostStore::POST_TYPE ];
					$args = array (
							'show_ui' => true, 
							'show_in_menu' => 'tools.php', 
							'show_in_admin_bar' => false 
					);
					foreach ( $args as $property_name => $value ) {
						$post_type->$property_name = $value;
					}
					$wp_post_types [ ActionScheduler_wpPostStore::POST_TYPE ] = $post_type;
				}
			}
		}
	}
}
new Braintree_Gateway_Subscription_Settings();