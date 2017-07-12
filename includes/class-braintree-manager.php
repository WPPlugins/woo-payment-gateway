<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit(); // Exit if accessed directly
}

use Braintree\Exception;
use Braintree\Exception\NotFound;

/**
 * Braintree Manager class that performs loading functionality as well as
 * perform boiler plate operations such has fetching options.
 *
 * @author Payment Plugins
 * @since 3/12/16
 */
class Braintree_Gateway_Manager
{
	public static $_instance = null;
	private $required_settings = null;
	public $debug;
	public $log;
	public $version = '2.6.14';
	public $settings = array ();
	public $default_settings = array ();
	private $settings_name = 'braintree_payment_settings';
	private $_data = array ();
	private $woocommerce_active = false;

	/**
	 * Creates and instance of the Braintree_Manager class and loads all
	 * necessary data.
	 */
	public function __construct()
	{
		$this->set_version();
		$this->includes();
		$this->add_actions();
		$this->init_settings();
	}

	public function __get( $key )
	{
		return isset( $this->_data [ $key ] ) ? $this->_data [ $key ] : null;
	}

	public function __set( $key, $value )
	{
		$this->_data [ $key ] = $value;
	}

	/**
	 * Return the instance of the Braintree_Manager
	 */
	public static function instance()
	{
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function add_actions()
	{
		if ( $this->add_actions_called ) {
			return; // prevent duplicate actions being added.
		}
		
		$this->add_actions_called = true;
		
		add_action( "activate_{$this->plugin_name()}", array (
				$this, 
				'plugin_activation' 
		) );
		add_action( "deactivate_{$this->plugin_name()}", array (
				$this, 
				'plugin_deactivation' 
		) );
		add_action( 'admin_init', array (
				$this, 
				'admin_init' 
		) );
		add_action( 'plugins_loaded', array (
				$this, 
				'init_default_settings' 
		) );
		add_action( 'woocommerce_loaded', array (
				$this, 
				'woocommerce_loaded' 
		) );
		add_action( 'init', array (
				$this, 
				'init' 
		) );
		add_action( 'admin_print_footer_scripts', array (
				$this, 
				'localize_admin_notices' 
		) );
		add_action( 'plugins_loaded', array (
				$this, 
				'wc_includes' 
		), 1000 );
		add_action( 'user_register', array (
				$this, 
				'register_user' 
		) );
		add_action( 'admin_notices', array (
				$this, 
				'active_license_notice' 
		) );
		add_action( 'bfwc_admin_after_plugin_update', array (
				$this, 
				'update_latest_version_settings' 
		) );
		add_action( 'admin_notices', 'bfwc_print_admin_notices' );
	}

	public function set_version()
	{
		if ( $plugin_data = get_file_data( $this->plugin_path() . 'braintree-payments.php', array (
				'Version' => 'Version' 
		) ) ) {
			$this->version = isset( $plugin_data [ 'Version' ] ) ? $plugin_data [ 'Version' ] : $this->version;
		}
	}

	public function plugin_name()
	{
		return BFWC_PLUGIN_NAME;
	}

	public function slug_name()
	{
		preg_match( '/(.*)\/.*/', $this->plugin_name(), $matches );
		return isset( $matches [ 1 ] ) ? $matches [ 1 ] : 'woo-payment-gateway';
	}

	/**
	 * Load the settings values using the wordpress function get_options.
	 */
	private function init_settings()
	{
		$this->settings = get_option( $this->settings_name, array () );
	}

	public function init_default_settings()
	{
		// for performance reasons, the default settings are always loaded from the options table.
		$this->default_settings = get_option( 'bfwc_default_settings', array () );
	}

	public function update_settings()
	{
		update_option( $this->settings_name, $this->settings );
	}

	public function woocommerce_loaded()
	{
		$this->set_woocommerce_active();
	}

	public function set_woocommerce_active()
	{
		$this->woocommerce_active = true;
	}

	/**
	 * Include all required classes and functions for the plugin.
	 */
	public function includes()
	{
		// Admin path.
		include_once ( $this->plugin_admin_path() . 'abstract/class-braintree-page-api.php' );
		include_once ( $this->plugin_admin_path() . 'abstract/class-braintree-settings-api.php' );
		include_once ( $this->plugin_admin_path() . 'class-braintree-post-types.php' );
		include_once ( $this->plugin_admin_path() . 'settings/class-api-settings.php' );
		include_once ( $this->plugin_admin_path() . 'settings/class-checkout-settings.php' );
		include_once ( $this->plugin_admin_path() . 'settings/class-subscription-settings.php' );
		include_once ( $this->plugin_admin_path() . 'settings/class-donation-settings.php' );
		include_once ( $this->plugin_admin_path() . 'settings/class-license-settings.php' );
		include_once ( $this->plugin_admin_path() . 'class-braintree-update.php' );
		
		if ( is_admin() ) {
			include_once ( $this->plugin_admin_path() . 'class-admin-menus.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-settings.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-logs.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-tls.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-donation.php' );
			include_once ( $this->plugin_admin_path() . 'settings/class-webhook-settings.php' );
			include_once ( $this->plugin_admin_path() . 'class-donation-meta-box.php' );
			include_once ( $this->plugin_admin_path() . 'class-wc-admin-order-actions.php' );
			include_once ( $this->plugin_admin_path() . 'class-wc-admin-order-metabox.php' );
			include_once ( $this->plugin_admin_path() . 'class-wc-admin-subscription-metabox.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-user-edit.php' );
			include_once ( $this->plugin_admin_path() . 'bwc-admin-functions.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-assets.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-webhooks.php' );
			include_once ( $this->plugin_admin_path() . 'class-admin-messages.php' );
		}
		
		// Include Path
		include_once ( $this->plugin_include_path() . 'class-braintree-logger.php' );
		include_once ( $this->plugin_include_path() . 'class-braintree-donation.php' );
		include_once ( $this->plugin_include_path() . 'class-recurring-donation.php' );
		include_once ( $this->plugin_include_path() . 'shortcodes/class-braintree-donation-shortcode.php' );
		include_once ( $this->plugin_include_path() . 'class-braintree-countries.php' );
		include_once ( $this->plugin_include_path() . 'braintree-core-functions.php' );
		include_once ( $this->plugin_include_path() . 'braintree-helper-functions.php' );
		include_once ( $this->plugin_include_path() . 'class-braintree-frontend-scripts.php' );
		include_once ( $this->plugin_include_path() . 'api/class-api-controller.php' );
		include_once ( $this->plugin_include_path() . 'api/class-braintree-api.php' );
		include_once ( $this->plugin_include_path() . 'api/class-test-api-controller.php' );
		include_once ( $this->plugin_include_path() . 'braintree-wc-functions.php' );
		include_once ( $this->plugin_include_path() . 'braintree-bfwc-functions.php' );
		include_once ( $this->plugin_include_path() . 'braintree-bfwc-message-functions.php' );
		
		// Donations
		include_once ( $this->plugin_include_path() . 'braintree-donation-functions.php' );
		include_once ( $this->plugin_include_path() . 'class-bfwc-donation-gateway.php' );
		include_once ( $this->plugin_include_path() . 'class-bfwc-card-donation-gateway.php' );
		include_once ( $this->plugin_include_path() . 'class-bfwc-paypal-donation-gateway.php' );
		include_once ( $this->plugin_include_path() . 'class-bfwc-paypal-credit-donation-gateway.php' );
	}

	/**
	 * Include all WooCommerce dependancies.
	 */
	public function wc_includes()
	{
		if ( $this->is_woocommerce_active() ) {
			
			/**
			 * *** classes, functions****
			 */
			include_once ( $this->plugin_include_path() . 'class-wc-braintree-gateway.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-paypal-gateway.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-paypal-credit-gateway.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-applepay-gateway.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-message-handler.php' );
			include_once ( $this->plugin_include_path() . 'braintree-wcs-functions.php' );
			include_once ( $this->plugin_include_path() . 'class-braintree-product-subscription.php' );
			include_once ( $this->plugin_include_path() . 'class-braintree-product-variable-subscription.php' );
			include_once ( $this->plugin_include_path() . 'class-braintree-product-subscription-variation.php' );
			include_once ( $this->plugin_include_path() . 'api/class-wc-order-controller.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-query.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-ajax.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-3ds-validation.php' );
			include_once ( $this->plugin_include_path() . 'class-wc-cart-calculations.php' );
			$this->query = new Braintree_Gateway_WC_Query();
			
			if ( ! $this->is_woocommerce_subscriptions_active() ) {
				/**
				 * *** Admin ****
				 */
				include_once ( $this->plugin_admin_path() . 'class-braintree-subscription-admin.php' );
				
				/**
				 * *** Classes ****
				 */
				include_once ( $this->plugin_include_path() . 'class-wc-subscriptions-cart.php' );
				include_once ( $this->plugin_include_path() . 'class-wc-subscriptions-checkout.php' );
				include_once ( $this->plugin_include_path() . 'class-wc-braintree-subscription.php' );
				include_once ( $this->plugin_include_path() . 'class-wc-subscriptions-order.php' );
				include_once ( $this->plugin_include_path() . 'class-braintree-subscriptions.php' );
				include_once ( $this->plugin_include_path() . 'api/class-braintree-subscription-controller.php' );
			} else {
				include_once ( $this->plugin_include_path() . 'class-wc-subscriptions.php' );
				include_once ( $this->plugin_admin_path() . 'class-wc-admin-subscription-data.php' );
				include_once ( $this->plugin_include_path() . 'api/class-wcs-controller.php' );
			}
		}
	}

	/**
	 * Init for the plugin.
	 * Actions are triggered so other plugins can load dependant functionality.
	 */
	public function init()
	{
		do_action( 'braintree_wc_before_init' );
		// init settings again in case any plugin hook into the options filter.
		$this->init_settings();
		$this->log = new Braintree_Gateway_Logger();
		$this->log->set_debug( $this->is_active( 'enable_debug' ) );
		$this->initialize_braintree();
		
		$this->maybe_create_braintree_customer();
		$this->update_payment_methods_from_braintree();
		
		$this->register_wc_post_types();
		
		$this->api = new Braintree_Gateway_API_Controller();
		
		do_action( 'braintree_wc_after_init' );
	}

	public function admin_init()
	{
		if ( $redirect = get_transient( 'bfwc_activation_redirect' ) ) {
			delete_transient( 'bfwc_activation_redirect' );
			/*
			 * $this->add_admin_notice( 'success', __( 'Thank you for activating Braintree For WooCommerce! To get started,
			 * we recommend that you first configure your API keys so your Wordpress site can communicate with Braintree.
			 * You can watch our tutorial on this page if you need help finding your API keys. You can test the plugin as much
			 * as you want! Just sign up for a <a target="_blank" href="https://www.braintreepayments.com/sandbox">Braintree Sandbox</a> account and activate sandbox mode.', 'braintree-payments' ) );
			 */
			wp_redirect( admin_url() . 'admin.php?page=braintree-gateway-settings&plugin-activation=true' );
			exit();
		}
	}

	public function plugin_activation()
	{
		if ( is_plugin_active( 'woo-payment-gateway-pro/braintree-payments.php' ) ) {
			deactivate_plugins( 'woo-payment-gateway-pro/braintree-payments.php' );
		}
		
		$this->register_wc_post_types();
		
		if ( ! class_exists( 'Braintree_Gateway_WC_Query' ) ) {
			include_once $this->plugin_include_path() . 'class-wc-query.php';
		}
		// adds rewrite rules on plugin activation.
		$this->query = new Braintree_Gateway_WC_Query();
		$this->query->add_endpoints();
		
		// flush the rules.
		flush_rewrite_rules();
		
		// add redirect transient.
		set_transient( 'bfwc_activation_redirect', true );
		
		// save default settings to options table.
		$default_settings = apply_filters( 'braintree_gateway_default_settings', array () );
		update_option( 'bfwc_default_settings', $default_settings );
	}

	public function plugin_deactivation()
	{
		// unregister to prevent warning messages on admin dashboard.
		if ( function_exists( 'unregister_post_type' ) ) {
			unregister_post_type( 'bfwc_subscription' );
		}
	}

	public function register_wc_post_types()
	{
		if ( $this->is_woocommerce_active() && ! $this->is_woocommerce_subscriptions_active() ) {
			wc_register_order_type( 'bfwc_subscription', array (
					'label' => __( 'Subscription', 'braintree-payments' ), 
					'labels' => array (
							'name' => __( 'Subscriptions', 'woocommerce' ), 
							'singular_name' => _x( 'Subscription', 'shop_order post type singular name', 'woocommerce' ), 
							'add_new' => __( 'Add Subscription', 'woocommerce' ), 
							'add_new_item' => __( 'Add New Subscription', 'woocommerce' ), 
							'edit' => __( 'Edit', 'woocommerce' ), 
							'edit_item' => __( 'Edit Subscription', 'woocommerce' ), 
							'new_item' => __( 'New Subscription', 'woocommerce' ), 
							'view' => __( 'View Subscription', 'woocommerce' ), 
							'view_item' => __( 'View Subscription', 'woocommerce' ), 
							'search_items' => __( 'Search Subscriptions', 'woocommerce' ), 
							'not_found' => __( 'No Subscriptions found', 'woocommerce' ), 
							'not_found_in_trash' => __( 'No Subscriptions found in trash', 'woocommerce' ), 
							'parent' => __( 'Parent Orders', 'woocommerce' ), 
							'menu_name' => _x( 'Subscriptions', 'Admin menu name', 'woocommerce' ), 
							'filter_items_list' => __( 'Filter subscriptions', 'woocommerce' ), 
							'items_list_navigation' => __( 'Subscriptions navigation', 'woocommerce' ), 
							'items_list' => __( 'Subscriptions list', 'woocommerce' ) 
					), 
					'capabilities' => array (
							'create_posts' => true 
					), 
					'description' => __( 'Subscription made through the Braintree Gateway.', 'braintree-payments' ), 
					'public' => false, 
					'show_ui' => true, 
					'capability_type' => 'shop_order', 
					'map_meta_cap' => true, 
					'publicly_queryable' => false, 
					'exclude_from_search' => true, 
					'show_in_menu' => current_user_can( 'manage_woocommerce' ) ? 'woocommerce' : true, 
					'hierarchical' => false, 
					'show_in_nav_menus' => false, 
					'rewrite' => false, 
					'query_var' => false, 
					'supports' => array (
							'title', 
							'comments', 
							'custom-fields' 
					), 
					'has_archive' => false, 
					
					// wc_register_order_type() params
					'exclude_from_orders_screen' => true, 
					'add_order_meta_boxes' => true, 
					'exclude_from_order_count' => true, 
					'exclude_from_order_views' => true, 
					'exclude_from_order_webhooks' => true, 
					'exclude_from_order_reports' => true, 
					'exclude_from_order_sales_reports' => true, 
					'class_name' => 'Braintree_Gateway_WC_Subscription' 
			) );
			
			if ( ! $this->is_woocommerce_subscriptions_active() ) {
				if ( ! function_exists( 'bfwcs_get_subscription_statuses' ) ) {
					include_once ( $this->plugin_include_path() . 'braintree-bfwc-functions.php' );
				}
				$wc_post_statuses = bfwcs_get_subscription_statuses();
				
				foreach ( $wc_post_statuses as $status => $values ) {
					bfwc_register_subscription_status( $status, $values );
				}
			}
		}
	}

	public function initialize_braintree()
	{
		try {
			Braintree_Configuration::environment( "93bc63e0b4f48fbbff568d9fc0dc3def" );
			Braintree_Configuration::merchantId( $this->get_option( "sandbox_merchant_id" ) );
			Braintree_Configuration::privateKey( $this->get_option( "sandbox_private_key" ) );
			Braintree_Configuration::publicKey( $this->get_option( "sandbox_public_key" ) );
		} catch( Exception $e ) {
		}
	}

	/**
	 * Return the plugin path for the Braintree Gateway.
	 *
	 * @return string
	 */
	public function plugin_path()
	{
		return BRAINTREE_GATEWAY_PATH;
	}

	public function plugin_include_path()
	{
		return $this->plugin_path() . 'includes/';
	}

	public function plugin_admin_path()
	{
		return trailingslashit( $this->plugin_include_path() ) . 'admin/';
	}

	/**
	 * Retrieve the template path.
	 * This is not an absolute path.
	 *
	 * @return string
	 */
	public function template_path()
	{
		return trailingslashit( 'woo-payment-gateway' );
	}

	/**
	 * Return the assets url.
	 *
	 * @return string
	 */
	public function plugin_assets_path()
	{
		return BRAINTREE_GATEWAY_ASSETS;
	}

	public function get_option( $key )
	{
		if ( isset( $this->settings [ $key ] ) ) {
			$value = $this->settings [ $key ];
		} else {
			$value = isset( $this->default_settings [ $key ] [ 'default' ] ) ? $this->default_settings [ $key ] [ 'default' ] : '';
		}
		$this->settings [ $key ] = $value;
		return $value;
	}

	public function set_option( $key, $value = '' )
	{
		$this->settings [ $key ] = $value;
	}

	public function get_environment()
	{
		return "sandbox";
	}

	/**
	 * Return true if the option is set to 'yes'.
	 *
	 * @param string $option        	
	 * @return boolean
	 */
	public function is_active( $option )
	{
		return $this->get_option( $option ) === 'yes';
	}

	/**
	 * Return a request parameter.
	 *
	 * @param string $key        	
	 * @return string|unknown
	 */
	public function get_request_parameter( $key )
	{
		return isset( $_REQUEST [ $key ] ) ? $_REQUEST [ $key ] : '';
	}

	public function delete_admin_notices()
	{
		delete_transient( 'braintree_gateway_admin_notices' );
	}

	/**
	 * adds a message to the admin notices.
	 *
	 * @param string $type        	
	 * @param string $message        	
	 */
	public function add_admin_notice( $type, $message )
	{
		$messages = $this->get_admin_notices();
		$messages [] = array (
				'type' => $type, 
				'message' => $message 
		);
		set_transient( 'braintree_gateway_admin_notices', $messages );
	}

	/**
	 * Return true of there are admin notices.
	 *
	 * @return boolean
	 */
	public function has_admin_notices()
	{
		$messages = $this->get_admin_notices();
		return ! empty( $messages );
	}

	public function get_admin_notices()
	{
		$messages = get_transient( 'braintree_gateway_admin_notices' );
		return $messages != null ? $messages : array ();
	}

	/**
	 * Add an error message to the log.
	 *
	 * @param string $message        	
	 */
	public function error( $message )
	{
		$this->log->error( $message );
	}

	/**
	 * Add a success message to the log.
	 *
	 * @param string $message        	
	 */
	public function success( $message )
	{
		$this->log->success( $message );
	}

	/**
	 * Add an info message to the log.
	 *
	 * @param string $message        	
	 */
	public function info( $message )
	{
		$this->log->info( $message );
	}

	public function get_license_status()
	{
		return 'inactive';
	}

	/**
	 * Return true if the WC plugin is active.
	 */
	public function is_woocommerce_active()
	{
		$plugins = get_option( 'active_plugins', true );
		
		return $this->woocommerce_active || did_action( 'woocommerce_loaded' ) || in_array( 'woocommerce/woocommerce.php', $plugins );
	}

	/**
	 * Return true if the WooCommerce Subscriptions plugin is active.
	 * WooCommerce must also be
	 * active in order for this method to return true;
	 *
	 * @return boolean
	 */
	public function is_woocommerce_subscriptions_active()
	{
		$plugins = get_option( 'active_plugins', true );
		
		// WC must be active.
		if ( ! $this->is_woocommerce_active() ) {
			return false;
		}
		return has_action( 'init', 'WC_Subscriptions::maybe_activate_woocommerce_subscriptions' ) || in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', $plugins );
	}

	/**
	 * Output the admin notices in a script tag.
	 */
	public function localize_admin_notices()
	{
		$this->localize_admin_called = true;
		$notices = $this->get_admin_notices();
		$messages = array (
				'success' => array (), 
				'error' => array () 
		);
		foreach ( $notices as $notice ) {
			switch( $notice [ 'type' ] ) {
				case 'success' :
					$messages [ 'success' ] [] = $notice [ 'message' ];
					break;
				case 'error' :
					$messages [ 'error' ] [] = $notice [ 'message' ];
					break;
			}
		}
		$this->delete_admin_notices();
		echo '<script>var braintree_admin_notices = ' . json_encode( $messages ) . '</script>';
	}

	public function get_partner_code()
	{
		return 'PaymentPlugins_BT';
	}

	/**
	 * Generate and return a Braintree ClientToken.
	 * You can include in the params array the following.
	 * <code>$params = array('customerId' => 'd24trht', 'merchantAccountId' =>
	 * 'test_account_EUR');
	 *
	 * @param array $params        	
	 * @return string $client_token;
	 */
	public function get_client_token( $params = array() )
	{
		$client_token = null;
		try {
			$client_token = Braintree_ClientToken::generate( $params );
		} catch( Exception $e ) {
			$this->error( __( 'Braintree client token could not be generated. Check your api keys.', 'braintree-payments' ) );
		}
		return $client_token;
	}

	/**
	 * Return a Braintree customer ID for the current active environment and the
	 * given user Id.
	 * If the customer ID does not exists,
	 * this method returns an empty string.
	 *
	 * @param int $user_id        	
	 * @return mixed|boolean|string|unknown
	 */
	public function get_customer_id( $user_id )
	{
		return get_user_meta( $user_id, "braintree_{$this->get_environment()}_vault_id", true );
	}

	/**
	 * Return a Braintree customer ID for the given environment and user Id.
	 * If the customer ID does not exists,
	 * this method returns an empty string.
	 *
	 * @param int $user_id        	
	 * @param $environment production|sandbox        	
	 * @return mixed|boolean|string|unknown
	 */
	public function get_customer_id_for_environment( $user_id, $environment )
	{
		return get_user_meta( $user_id, "braintree_{$environment}_vault_id", true );
	}

	/**
	 * Save the braintree customer Id for the given user Id.
	 *
	 * @param int $user_id        	
	 * @param string $customer_id        	
	 */
	public function save_customer_id( $user_id, $customer_id )
	{
		update_user_meta( $user_id, "braintree_{$this->get_environment()}_vault_id", $customer_id );
	}

	/**
	 * Method that calls Braintree and updates the user's meta data with payment
	 * methods stored
	 * in the Braintree vault.
	 */
	public function update_payment_methods_from_braintree()
	{
		if ( ! is_user_logged_in() ) { // No user so exit.
			return;
		}
		$user_id = wp_get_current_user()->ID;
		$next_check = get_user_meta( $user_id, 'braintree_next_payment_update', true );
		
		if ( empty( $next_check ) || $next_check < time() ) {
			// We are due to update the users payment methods.
			
			if ( ! $customer_id = $this->get_customer_id( $user_id ) ) {
				return; // No customer ID so don't continue.
			}
			
			try {
				$customer = Braintree_Customer::find( $customer_id );
				
				$payment_methods = $customer->paymentMethods;
				
				braintree_delete_user_payment_methods( $user_id ); // Delete data so
				                                                   // it's
				                                                   // fresh.
				
				if ( $payment_methods ) {
					$methods = array ();
					
					foreach ( $payment_methods as $payment_method ) {
						braintree_save_user_payment_method( $user_id, $payment_method );
					}
				}
			} catch( NotFound $e ) {
				$this->error( sprintf( __( 'Customer ID %s could not be found for Wordpress user %s.', 'braintree-payments' ), $customer_id, $user_id ) );
			} catch( Exception $e ) {
				$this->error( sprintf( __( 'An exception was thrown while fetching customer ID %s for Wordpress user %s', 'braintree-payments' ), $customer_id, $user_id ) );
			} catch( InvalidArgumentException $e ) {
				$this->error( sprintf( __( 'An exception was thrown while fetching customer ID %s for Wordpress user %s', 'braintree-payments' ), $customer_id, $user_id ) );
			}
			update_user_meta( $user_id, 'braintree_next_payment_update', time() + DAY_IN_SECONDS * 2 ); // Only update payment methods once
				                                                                                            // every days.
		}
	}

	/**
	 * If a customer does not exists for a user, then create the customer.
	 */
	public function maybe_create_braintree_customer()
	{
		if ( ! is_user_logged_in() ) {
			return;
		}
		$user_id = wp_get_current_user()->ID;
		$customer_id = $this->get_customer_id( $user_id );
		
		if ( empty( $customer_id ) ) {
			try {
				$attribs = array ();
				$user = wp_get_current_user();
				$result = Braintree_Customer::create( array (
						'firstName' => $user->first_name, 
						'lastName' => $user->last_name, 
						'email' => $user->user_email, 
						'phone' => get_user_meta( $user_id, 'billing_phone', true ), 
						'company' => get_user_meta( $user_id, 'billing_company', true ) 
				) );
				if ( $result->success ) {
					$this->save_customer_id( $user_id, $result->customer->id );
					do_action( 'braintree_customer_creation_success', $result );
				} else {
					do_action( 'braintree_customer_creation_error', $result );
				}
			} catch( \Braintree\Exception $e ) {
				$this->error( sprintf( __( 'There was an error creating a braintree customer in %s environment.', 'braintree-payments' ), $this->get_environment() ) );
			}
		}
	}

	public function register_user( $user_id )
	{
		$customer_id = $this->get_customer_id( $user_id );
		if ( ! empty( $customer_id ) ) {
			return;
		}
		$user = get_user_by( 'id', $user_id );
		try {
			$result = Braintree_Customer::create( array (
					'firstName' => $user->first_name, 
					'lastName' => $user->last_name, 
					'email' => $user->user_email, 
					'phone' => get_user_meta( $user_id, 'billing_phone', true ), 
					'company' => get_user_meta( $user_id, 'billing_company', true ) 
			) );
			if ( $result->success ) {
				$this->save_customer_id( $user_id, $result->customer->id );
				do_action( 'braintree_customer_creation_success', $result );
			} else {
				do_action( 'braintree_customer_creation_error', $result );
			}
		} catch( \Braintree\Exception $e ) {
			$this->error( sprintf( __( 'There was an error creating a braintree customer in %s environment.', 'braintree-payments' ), $this->get_environment() ) );
		}
	}

	/**
	 * Return an array of Braintree_Plans.
	 *
	 * @param
	 *        	Braintree_Plan[]
	 */
	public function get_braintree_plans()
	{
		$plans = array ();
		try {
			$plans = Braintree_Plan::all();
		} catch( Exception $e ) {
			$this->error( sprintf( __( 'There was exception thrown while retrieving the Braintree Plans required for a recurring donation.', 'braintree-payments' ) ) );
		}
		return $plans;
	}

	public function get_braintree_plan( $plan_id, $plans = array() )
	{
		foreach ( $plans as $plan ) {
			if ( $plan->id === $plan_id ) {
				return $plan;
			}
		}
		return null;
	}

	public function active_license_notice()
	{
		$license = $this->get_option( 'license' );
		// There is a license for this site.
		if ( $license ) {
			echo '<div class="notice notice-warning">';
			echo '<p style="font-size: 18px">' . __( 'ATTENTION: Please download the Pro version of Braintree For WooCommerce. 
					Payment Plugins is now hosting the Pro version of the plugin on their own servers and Wordpress.org hosts the free version. <a target="_blank" href="https://s3-us-west-1.amazonaws.com/woo-payment-gateway/woo-payment-gateway-pro.zip">Download Pro Here</a>', 'braintree-payments' ) . '</p>';
			echo '</div>';
		}
	}

	/**
	 * During update, capture any new settings that have been added and save them to the options table.
	 */
	public function update_latest_version_settings()
	{
		// save the default settings as the latest version may have new settings to save in the database.
		$default_settings = apply_filters( 'braintree_gateway_default_settings', array () );
		update_option( 'bfwc_default_settings', $default_settings );
	}
}

/**
 * Function that returns an instance of the Braintree_Manager class.
 * If there is no instance, then a new instance is
 * instantiated and asigned to the static variable $_instance of class
 * Braintree_Manager.
 *
 * @return Braintree_Gateway_Manager
 */
function bt_manager()
{
	return Braintree_Gateway_Manager::instance();
}
bt_manager(); //call function to initialize the plugin.