<?php
/**
 * Admin scripts used for WC data editing.
 * @author Payment Plugins
 * @copyright 2016 Payment Plugins
 *
 */
class Braintree_Gateway_Admin_Assets
{

	public static function init()
	{
		add_action( 'admin_enqueue_scripts', __CLASS__ . '::enqueue_scripts' );
		add_action( 'admin_print_footer_scripts', __CLASS__ . '::localize_admin_settings', 999 );
	}

	public static function enqueue_scripts()
	{
		$screen = get_current_screen();
		$screen_id = $screen ? $screen->id : '';
		$js_admin = bt_manager()->plugin_assets_path() . 'js/admin/';
		$js_path = bt_manager()->plugin_assets_path() . 'js/';
		$css_admin = bt_manager()->plugin_assets_path() . 'css/admin/';
		$css_path = bt_manager()->plugin_assets_path() . 'css/';
		$version = bt_manager()->version;
		$page = preg_match( '/braintree-gateway.*?_page_(.*)/', $screen_id, $matches ) ? ( isset( $matches [ 1 ] ) ? $matches [ 1 ] : $screen_id ) : $screen_id;
		
		$plugin_activation = ! empty( $_GET [ 'plugin-activation' ] );
		
		define( 'BFWC_ADMIN_V3_CLIENT', 'https://js.braintreegateway.com/web/3.11.1/js/client.min.js' );
		define( 'BFWC_ADMIN_V3_DROPIN', 'https://js.braintreegateway.com/web/dropin/1.0.0-beta.6/js/dropin.min.js' );
		
		wp_register_script( 'bfwc-materialize-js', $js_admin . 'materialize.min.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-settings-js', $js_admin . 'admin-settings.js', array (
				'bfwc-materialize-js', 
				'bfwc-select2', 
				'underscore', 
				'backbone' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-notices', $js_admin . 'admin-notices.js', array (
				'bfwc-materialize-js' 
		), $version, true );
		
		wp_register_script( 'bfwc-donation-settings-js', $js_admin . 'donation-settings.js', array (
				'bfwc-settings-js', 
				'bfwc-admin-color-picker-script' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-edit-donation-js', $js_admin . 'edit-donation.js', array (
				'bfwc-materialize-js', 
				'bfwc-select2' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-logs-js', $js_admin . 'logs.js', array (
				'bfwc-materialize-js' 
		), $version, true );
		
		wp_register_script( 'bfwc-wc-order-js', $js_admin . 'order.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-wc-product-data-js', $js_admin . 'product-data.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-datatables-js', $js_admin . 'jquery/dataTables.min.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-datatables-buttons-js', $js_admin . 'jquery/dataTables.buttons.min.js', array (
				'bfwc-datatables-js' 
		), $version, true );
		
		wp_register_script( 'bfwc-buttons-flash-js', $js_admin . 'jquery/buttons.flash.min.js', array (
				'bfwc-datatables-buttons-js' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-color-picker-colors-js', $js_admin . 'tinyColorPicker-master/colors.js', array (), $version, true );
		
		wp_register_script( 'bfwc-admin-color-picker-script', $js_admin . 'tinyColorPicker-master/jqColorPicker.js', array (
				'bfwc-admin-color-picker-colors-js' 
		), $version, true );
		wp_register_script( 'bfwc-admin-hooks-js', $js_admin . 'webhook-test.js', array (
				'jquery' 
		), $version, true );
		wp_register_script( 'bfwc-admin-spin-js', $js_path . 'spin/spin.min.js', array (
				'jquery' 
		), $version, true );
		wp_register_script( 'bfwc-wc-subscription-metabox-js', $js_admin . 'subscription-metabox.js', array (
				'jquery' 
		), $version, true );
		wp_register_script( 'bfwc-admin-users', $js_admin . 'users.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-messages', $js_admin . 'admin-messages.js', array (
				'jquery', 
				'bfwc-select2', 
				'bfwc-settings-js' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-v3-client', BFWC_ADMIN_V3_CLIENT, null, $version, true );
		
		wp_register_script( 'bfwc-admin-v3-dropin', BFWC_ADMIN_V3_DROPIN, array (
				'bfwc-admin-v3-client' 
		), $version, true );
		
		wp_register_script( 'bfwc-admin-user-edit', $js_admin . 'users.js', array (
				'bfwc-admin-v3-dropin', 
				'bfwc-select2', 
				'underscore', 
				'backbone' 
		), $version, true );
		
		wp_register_script( 'bfwc-select2', $js_path . 'select2/select2.min.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_script( 'bfwc-help-widget', $js_admin . 'help-widget.js', array (
				'jquery' 
		), $version, true );
		
		wp_register_style( 'bfwc-admin-materialize-css', $css_admin . 'materialize.css', null, $version, null );
		wp_register_style( 'bfwc-admin-product-css', $css_admin . 'product-data.css', null, $version, null );
		wp_register_style( 'bfwc-admin-datatables-css', $css_admin . 'jquery/dataTables.css', null, $version, null );
		wp_register_style( 'bfwc-admin-materialize-graphics', 'https://fonts.googleapis.com/icon?family=Material+Icons', null, $version, null );
		wp_register_style( 'bfwc-admin-datatables-buttons-css', $css_admin . 'jquery/dataTables.buttons.min.css', null, $version, null );
		wp_register_style( 'bfwc-admin-subscriptions-css', $css_admin . 'subscriptions.css', null, $version, null );
		wp_register_style( 'bfwc-select2-css', $css_path . 'select2.css', null, $version, null );
		wp_register_style( 'bfwc-users-css', $css_admin . 'users.css', null, $version, null );
		
		$wc_product_screens = array (
				'product', 
				'edit-product' 
		);
		$wc_order_screens = array (
				'shop_order', 
				'bfwc_subscription', 
				'edit-bfwc_subscription' 
		);
		$donation_screens = array (
				'braintree_donation', 
				'bt_rc_donation' 
		);
		
		if ( in_array( $screen_id, $wc_product_screens ) ) {
			
			wp_enqueue_style( 'bfwc-admin-product-css' );
			wp_enqueue_script( 'bfwc-wc-product-data-js' );
			wp_localize_script( 'bfwc-wc-product-data-js', 'braintree_product_data', self::get_product_data() );
		}
		if ( in_array( $screen_id, $wc_order_screens ) ) {
			wp_enqueue_script( 'bfwc-wc-order-js' );
			wp_enqueue_style( 'bfwc-admin-subscriptions-css' );
			
			if ( $screen_id === 'bfwc_subscription' ) {
				wp_enqueue_script( 'bfwc-wc-subscription-metabox-js' );
				wp_localize_script( 'bfwc-wc-subscription-metabox-js', 'bfwc_subscription_vars', Braintree_Gateway_Admin_Subscription_Metabox::localize_data() );
			}
		}
		if ( in_array( $screen_id, $donation_screens ) ) {
			wp_enqueue_script( 'bfwc-admin-edit-donation-js' );
			wp_enqueue_script( 'bfwc-admin-notices' );
			wp_enqueue_script( 'bfwc-datatables-js' );
			wp_enqueue_style( 'bfwc-admin-materialize-css' );
			wp_enqueue_style( 'bfwc-admin-materialize-graphics' );
			wp_enqueue_style( 'bfwc-select2-css' );
		}
		
		$all_pages = bfwc_admin_pages();
		
		if ( in_array( $page, $all_pages ) ) {
			wp_enqueue_script( 'bfwc-settings-js' );
			wp_enqueue_script( 'bfwc-admin-notices' );
			wp_enqueue_script( 'bfwc-donation-settings-js' );
			wp_enqueue_script( 'bfwc-select2' );
			wp_enqueue_script( 'bfwc-help-widget' );
			wp_enqueue_style( 'bfwc-admin-materialize-css' );
			wp_enqueue_style( 'bfwc-admin-materialize-graphics' );
			wp_enqueue_style( 'bfwc-select2-css' );
			
			if ( preg_match( '/^braintree.*logs/', $page ) ) {
				wp_enqueue_script( 'bfwc-admin-logs-js' );
				wp_enqueue_script( 'bfwc-buttons-flash-js' );
				wp_localize_script( 'bfwc-admin-logs-js', 'braintree_log_vars', self::log_vars() );
			}
			if ( $page === 'braintree-webhook-test' ) {
				wp_enqueue_script( 'bfwc-admin-hooks-js' );
				wp_enqueue_script( 'bfwc-admin-spin-js' );
				wp_localize_script( 'bfwc-admin-hooks-js', 'braintree_webhook_vars', self::webhook_vars() );
			}
			if ( $page === 'braintree-messages-page' ) {
				wp_enqueue_script( 'bfwc-admin-messages' );
				wp_localize_script( 'bfwc-admin-messages', 'bfwc_admin_messages', Braintree_Gateway_Admin_Messages::localize_messages() );
			}
			
			if ( $plugin_activation ) {
				wp_enqueue_script( 'bfwc-plugin-activation', $js_admin . 'plugin-activation.js', array (
						'jquery', 
						'bfwc-materialize-js' 
				), $version, true );
				wp_localize_script( 'bfwc-plugin-activation', 'bfwc_admin_plugin_activated', self::localize_plugin_activation() );
			}
		
		}
		if ( $screen_id === 'profile' || $screen_id === 'user-edit' ) {
			wp_enqueue_script( 'bfwc-admin-user-edit' );
			wp_localize_script( 'bfwc-admin-user-edit', 'bfwc_user_params', Braintree_Gateway_Admin_User_Edit::user_params() );
			wp_enqueue_style( 'bfwc-users-css' );
		}
	
	}

	public static function get_product_data()
	{
		global $post;
		if ( ! $post ) {
			return array ();
		}
		$variations = get_posts( array (
				'post_type' => 'product_variation', 
				'post_status' => array (
						'private', 
						'publish' 
				), 
				'posts_per_page' => - 1, 
				'orderby' => array (
						'menu_order' => 'ASC', 
						'ID' => 'DESC' 
				), 
				'post_parent' => $post->ID 
		) );
		
		$data = array (
				'posts' => array (
						$post->ID => array (
								'frequency' => get_post_meta( $post->ID, '_subscription_period_interval', true ) 
						) 
				), 
				'enabled' => array (
						'wc_subscriptions_active' => bt_manager()->is_woocommerce_subscriptions_active() 
				), 
				'product_type' => 'braintree-subscription', 
				'products' => array (
						'braintree-subscription' => array (
								'html' => '<li class="product-plan"><a href="#" class="select2"></a><span>%desc</span><input type="hidden" name="_braintree_%env_plans[%curr]" id="" value="%value"></li>' 
						), 
						'braintree-variable-subscription' => array (
								'html' => '<li class="product-plan"><a href="#" class="select2"></a><span>%desc</span><input type="hidden" name="variable_braintree_%env_plans[%loop][%curr]" id="" value="%value"></li>' 
						), 
						'subscription' => array (
								'html' => '<li class="product-plan"><a href="#" class="select2"></a><span>%desc</span><input type="hidden" name="_braintree_%env_plans[%curr]" id="" value="%value"></li>' 
						), 
						'variable-subscription' => array (
								'html' => '<li class="product-plan"><a href="#" class="select2"></a><span>%desc</span><input type="hidden" name="variable_braintree_%env_plans[%loop][%curr]" id="" value="%value"></li>' 
						) 
				), 
				'environments' => array (
						'sandbox' => array (
								'plans' => get_option( 'braintree_wc_sandbox_plans', array () ), 
								'saved_plans' => get_post_meta( $post->ID, 'braintree_sandbox_plans', true ) 
						), 
						'production' => array (
								'active' => bt_manager()->get_license_status() === 'active' ? true : false, 
								'plans' => get_option( 'braintree_wc_production_plans', array () ), 
								'saved_plans' => get_post_meta( $post->ID, 'braintree_production_plans', true ) 
						) 
				), 
				'messages' => array (
						'duplicate' => __( 'Plan %s has already been assigned with currency %c. If you want to change the plan for currency %c please remove plan %s', 'braintree-payments' ), 
						'inactive_license' => __( 'You must purchase a license before adding Production Braintree Plans', 'braintree-payments' ), 
						'invalid_frequency' => __( 'Plan %s cannot be added. Plans associated with a subscription must have the same billing frequency.', 'braintree-payments' ) 
				), 
				'trial_period_singular' => bfwc_billing_periods_string( 'singular' ), 
				'trial_period_plural' => bfwc_billing_periods_string( 'plural' ) 
		);
		if ( $variations ) {
			foreach ( $variations as $variation ) {
				$data [ 'posts' ] [ $variation->ID ] = array (
						'frequency' => get_post_meta( $variation->ID, '_subscription_period_interval', true ) 
				);
			}
		}
		
		return $data;
	}

	public static function log_vars()
	{
		$vars = array (
				'search_placeholder' => __( 'Enter Search Criteria', 'braintree-payments' ) 
		);
		return $vars;
	}

	public static function webhook_vars()
	{
		$vars = array (
				'ajax_url' => admin_url() . 'admin-ajax.php?action=bfwc_admin_retrieve_payload', 
				'webhook_url' => add_query_arg( 'bfwc-admin-webhook-test', '', get_rest_url( null, bt_manager()->api->get_path() ) ) 
		);
		return $vars;
	}

	public static function localize_plugin_activation()
	{
		$data = array ();
		ob_start();
		include 'views/activation-modal.php';
		$data [ 'html' ] = ob_get_clean();
		return $data;
	}

	/**
	 *
	 * @since 2.6.7
	 */
	public static function localize_admin_settings()
	{
		echo '<script> var braintree_settings_vars = ' . json_encode( apply_filters( 'braintree_settings_localized_variables', array () ) ) . '</script>';
	}

}
Braintree_Gateway_Admin_Assets::init();